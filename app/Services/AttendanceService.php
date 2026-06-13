<?php

namespace App\Services;

use App\Enums\AttendanceMode;
use App\Enums\AttendanceStatus;
use App\Enums\RequestStatus;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\AttendanceRequest;
use App\Models\EmployeeProfile;
use App\Models\Shift;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(private readonly AttendanceRepositoryInterface $attendances) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Attendance>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->attendances->paginate($filters, $perPage, ['employee', 'shift', 'location']);
    }

    /**
     * Record a check-in for the given employee.
     *
     * @param  array<string, mixed>  $data
     */
    public function checkIn(EmployeeProfile $employee, array $data): Attendance
    {
        return DB::transaction(function () use ($employee, $data): Attendance {
            $date = CarbonImmutable::now()->toDateString();

            $existing = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $date)
                ->first();

            if ($existing && $existing->check_in_time) {
                throw ValidationException::withMessages([
                    'attendance' => 'You have already checked in today.',
                ]);
            }

            $latitude = (float) $data['latitude'];
            $longitude = (float) $data['longitude'];

            // Mode-aware geofence: WFH / business trip skip the radius ONLY when backed by an
            // approved attendance request for the day; otherwise we fall back to office + geofence.
            $requestedMode = AttendanceMode::tryFrom($data['attendance_mode'] ?? AttendanceMode::Office->value)
                ?? AttendanceMode::Office;

            $mode = $this->resolveEffectiveMode($employee, $date, $requestedMode);

            $location = $mode->requiresOfficeGeofence()
                ? $this->requireOfficeWithinRadius($employee, $latitude, $longitude)
                : null;

            $shift = $employee->shift;
            $checkInTime = CarbonImmutable::now();
            $lateMinutes = $this->lateMinutes($shift, $checkInTime);

            $payload = [
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'shift_id' => $shift?->id,
                'attendance_location_id' => $location?->id,
                'attendance_date' => $date,
                'check_in_time' => $checkInTime,
                'check_in_latitude' => $latitude,
                'check_in_longitude' => $longitude,
                'check_in_photo' => $this->storePhoto($data['photo'] ?? null),
                'attendance_status' => $lateMinutes > 0 ? AttendanceStatus::Late : AttendanceStatus::Present,
                'late_minutes' => $lateMinutes,
                'notes' => $data['notes'] ?? null,
                'attendance_mode' => $mode,
                'check_in_ip_address' => $data['ip_address'] ?? null,
                'check_in_user_agent' => $data['user_agent'] ?? null,
                'work_location' => $data['work_location'] ?? null,
            ];

            if ($existing) {
                $existing->fill($payload)->save();

                return $existing->refresh();
            }

            return Attendance::create($payload);
        });
    }

    /**
     * Record a check-out and compute worked minutes.
     *
     * @param  array<string, mixed>  $data
     */
    public function checkOut(EmployeeProfile $employee, array $data): Attendance
    {
        return DB::transaction(function () use ($employee, $data): Attendance {
            $date = CarbonImmutable::now()->toDateString();

            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $date)
                ->first();

            if (! $attendance || ! $attendance->check_in_time) {
                throw ValidationException::withMessages([
                    'attendance' => 'You have not checked in today.',
                ]);
            }

            if ($attendance->check_out_time) {
                throw ValidationException::withMessages([
                    'attendance' => 'You have already checked out today.',
                ]);
            }

            $checkOutTime = CarbonImmutable::now();

            $attendance->fill([
                'check_out_time' => $checkOutTime,
                'check_out_latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
                'check_out_longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
                'check_out_photo' => $this->storePhoto($data['photo'] ?? null) ?? $attendance->check_out_photo,
                'working_minutes' => $attendance->check_in_time->diffInMinutes($checkOutTime),
            ])->save();

            return $attendance->refresh();
        });
    }

    /**
     * Determine the mode actually applied on check-in.
     *
     * WFH / business-trip is honoured only when an approved attendance request exists for the
     * same employee, date, and mode. Otherwise the check-in is downgraded to office (geofenced).
     */
    private function resolveEffectiveMode(EmployeeProfile $employee, string $date, AttendanceMode $requestedMode): AttendanceMode
    {
        if ($requestedMode->requiresOfficeGeofence()) {
            return AttendanceMode::Office;
        }

        $hasApproval = AttendanceRequest::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $date)
            ->where('attendance_mode', $requestedMode)
            ->where('status', RequestStatus::Approved)
            ->exists();

        return $hasApproval ? $requestedMode : AttendanceMode::Office;
    }

    /**
     * Find an active office location covering the coordinates, or fail.
     */
    private function requireOfficeWithinRadius(EmployeeProfile $employee, float $latitude, float $longitude): AttendanceLocation
    {
        $locations = AttendanceLocation::where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->get();

        if ($locations->isEmpty()) {
            throw ValidationException::withMessages([
                'location' => 'No attendance location is configured for your company.',
            ]);
        }

        $match = $locations->first(fn (AttendanceLocation $location) => $location->covers($latitude, $longitude));

        if (! $match) {
            throw ValidationException::withMessages([
                'location' => 'You are outside the allowed office radius.',
            ]);
        }

        return $match;
    }

    /**
     * Minutes late relative to the shift start plus its grace period.
     */
    private function lateMinutes(?Shift $shift, CarbonImmutable $checkInTime): int
    {
        if (! $shift) {
            return 0;
        }

        $allowedStart = CarbonImmutable::parse("{$checkInTime->toDateString()} {$shift->start_time}")
            ->addMinutes($shift->grace_period_minutes);

        return $checkInTime->greaterThan($allowedStart)
            ? (int) $allowedStart->diffInMinutes($checkInTime)
            : 0;
    }

    private function storePhoto(?UploadedFile $photo): ?string
    {
        return $photo?->store('attendance-photos', 'public');
    }
}
