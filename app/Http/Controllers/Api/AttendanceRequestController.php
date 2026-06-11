<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest\RejectAttendanceRequestRequest;
use App\Http\Requests\AttendanceRequest\StoreAttendanceRequestRequest;
use App\Http\Resources\AttendanceRequestResource;
use App\Models\AttendanceRequest;
use App\Models\EmployeeProfile;
use App\Services\AttendanceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class AttendanceRequestController extends Controller
{
    public function __construct(private readonly AttendanceRequestService $requests) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AttendanceRequest::class);

        $filters = $request->only(['status', 'attendance_mode', 'employee_id']);
        $filters['company_id'] = $request->user()->company_id;

        if (! $request->user()->can('employee.view')) {
            $filters['employee_id'] = $this->employee($request)->id;
        }

        return AttendanceRequestResource::collection($this->requests->paginate($filters));
    }

    public function store(StoreAttendanceRequestRequest $request): JsonResponse
    {
        $this->authorize('create', AttendanceRequest::class);

        $attendanceRequest = $this->requests->apply($this->employee($request), $request->validated());

        return AttendanceRequestResource::make($attendanceRequest)->response()->setStatusCode(201);
    }

    public function approve(Request $request, AttendanceRequest $attendanceRequest): AttendanceRequestResource
    {
        $this->authorize('approve', $attendanceRequest);

        return AttendanceRequestResource::make($this->requests->approve($attendanceRequest, $request->user()));
    }

    public function reject(RejectAttendanceRequestRequest $request, AttendanceRequest $attendanceRequest): AttendanceRequestResource
    {
        $this->authorize('reject', $attendanceRequest);

        return AttendanceRequestResource::make(
            $this->requests->reject($attendanceRequest, $request->user(), $request->validated('rejection_reason'))
        );
    }

    public function cancel(AttendanceRequest $attendanceRequest): AttendanceRequestResource
    {
        $this->authorize('cancel', $attendanceRequest);

        return AttendanceRequestResource::make($this->requests->cancel($attendanceRequest));
    }

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
