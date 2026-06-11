<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\EmployeeProfile;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendances) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Attendance::class);

        $filters = $request->only(['employee_id', 'status', 'from', 'to']);
        $filters['company_id'] = $request->user()->company_id;

        // Employees without team-view permission only see their own records.
        if (! $request->user()->can('employee.view')) {
            $filters['employee_id'] = $this->employee($request)->id;
        }

        return AttendanceResource::collection($this->attendances->paginate($filters));
    }

    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $attendance = $this->attendances->checkIn($this->employee($request), $request->checkInData());

        return AttendanceResource::make($attendance)->response()->setStatusCode(201);
    }

    public function checkOut(CheckOutRequest $request): AttendanceResource
    {
        $attendance = $this->attendances->checkOut($this->employee($request), $request->validated());

        return AttendanceResource::make($attendance);
    }

    public function today(Request $request): JsonResponse
    {
        $attendance = Attendance::with(['shift', 'location'])
            ->where('employee_id', $this->employee($request)->id)
            ->whereDate('attendance_date', CarbonImmutable::now()->toDateString())
            ->first();

        return response()->json([
            'data' => $attendance ? AttendanceResource::make($attendance) : null,
        ]);
    }

    /**
     * Resolve the employee profile of the authenticated user.
     */
    private function employee(Request $request): EmployeeProfile
    {
        $employee = $request->user()->employeeProfile;

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => 'Your account is not linked to an employee profile.',
            ]);
        }

        return $employee;
    }
}
