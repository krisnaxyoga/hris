<?php

namespace App\Http\Controllers\Web;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\EmployeeProfile;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendances) {}

    /**
     * Self-service check-in / check-out page.
     */
    public function me(Request $request): View
    {
        $employee = $request->user()->employeeProfile;

        $today = $employee
            ? Attendance::with(['shift', 'location'])
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', CarbonImmutable::now()->toDateString())
                ->first()
            : null;

        $recent = $employee
            ? Attendance::where('employee_id', $employee->id)->latest('attendance_date')->take(10)->get()
            : collect();

        // Office geofences for the OpenStreetMap (Leaflet) check-in map.
        $locations = AttendanceLocation::query()
            ->where('company_id', $request->user()->company_id)
            ->where('is_active', true)
            ->get(['id', 'name', 'latitude', 'longitude', 'radius_meter']);

        return view('attendance.me', compact('employee', 'today', 'recent', 'locations'));
    }

    public function checkIn(CheckInRequest $request): RedirectResponse
    {
        $employee = $this->requireEmployee($request);

        try {
            $this->attendances->checkIn($employee, $request->checkInData());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Checked in successfully.');
    }

    public function checkOut(CheckOutRequest $request): RedirectResponse
    {
        $employee = $this->requireEmployee($request);

        try {
            $this->attendances->checkOut($employee, $request->validated());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Checked out successfully.');
    }

    /**
     * Company-wide attendance log + report (HR / Manager).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $filters = $request->only(['employee_id', 'status', 'from', 'to']);
        $filters['company_id'] = $request->user()->company_id;

        $attendances = $this->attendances->paginate($filters);

        $base = Attendance::where('company_id', $request->user()->company_id)
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('attendance_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('attendance_date', '<=', $to));

        $summary = [
            'present' => (clone $base)->where('attendance_status', AttendanceStatus::Present)->count(),
            'late' => (clone $base)->where('attendance_status', AttendanceStatus::Late)->count(),
            'absent' => (clone $base)->where('attendance_status', AttendanceStatus::Absent)->count(),
            'total_hours' => round((clone $base)->sum('working_minutes') / 60, 1),
        ];

        return view('attendance.index', compact('attendances', 'summary'));
    }

    private function requireEmployee(Request $request): EmployeeProfile
    {
        $employee = $request->user()->employeeProfile;

        abort_unless($employee !== null, 403, 'Your account is not linked to an employee profile.');

        return $employee;
    }
}
