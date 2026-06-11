<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\AttendanceRequest;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Repositories\Contracts\AttendanceRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceRequestService
{
    public function __construct(private readonly AttendanceRequestRepositoryInterface $requests) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, AttendanceRequest>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->requests->paginate($filters, $perPage, ['employee', 'approver']);
    }

    /**
     * Employee submits a WFH / business-trip request.
     *
     * @param  array<string, mixed>  $data
     */
    public function apply(EmployeeProfile $employee, array $data): AttendanceRequest
    {
        return DB::transaction(fn () => AttendanceRequest::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'attendance_date' => $data['attendance_date'],
            'attendance_mode' => $data['attendance_mode'],
            'work_location' => $data['work_location'] ?? null,
            'reason' => $data['reason'] ?? null,
            'status' => RequestStatus::Pending,
        ]));
    }

    public function approve(AttendanceRequest $request, User $approver): AttendanceRequest
    {
        $this->assertPending($request);

        return DB::transaction(function () use ($request, $approver): AttendanceRequest {
            $request->update([
                'status' => RequestStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $request->refresh();
        });
    }

    public function reject(AttendanceRequest $request, User $approver, string $reason): AttendanceRequest
    {
        $this->assertPending($request);

        return DB::transaction(function () use ($request, $approver, $reason): AttendanceRequest {
            $request->update([
                'status' => RequestStatus::Rejected,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $request->refresh();
        });
    }

    public function cancel(AttendanceRequest $request): AttendanceRequest
    {
        $this->assertPending($request);

        $request->update(['status' => RequestStatus::Cancelled]);

        return $request->refresh();
    }

    private function assertPending(AttendanceRequest $request): void
    {
        if ($request->status !== RequestStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'This request has already been processed.',
            ]);
        }
    }
}
