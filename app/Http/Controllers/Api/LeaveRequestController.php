<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\RejectLeaveRequestRequest;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveService $leave) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $filters = $request->only(['status', 'leave_type_id', 'employee_id']);
        $filters['company_id'] = $request->user()->company_id;

        // Without team-view permission, users only see their own requests.
        if (! $request->user()->can('employee.view')) {
            $filters['employee_id'] = $this->employee($request)->id;
        }

        return LeaveRequestResource::collection($this->leave->paginate($filters));
    }

    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $leaveRequest = $this->leave->apply($this->employee($request), $data);

        return LeaveRequestResource::make($leaveRequest->load(['employee', 'leaveType']))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Approve the request at its current stage (manager → HR).
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $updated = match ($leaveRequest->status) {
            LeaveStatus::PendingManager => $this->managerApprove($request, $leaveRequest),
            LeaveStatus::PendingHr => $this->hrApprove($request, $leaveRequest),
            default => throw ValidationException::withMessages(['status' => 'This request is not awaiting approval.']),
        };

        return LeaveRequestResource::make($updated->load(['employee', 'leaveType']));
    }

    private function managerApprove(Request $request, LeaveRequest $leaveRequest): LeaveRequest
    {
        $this->authorize('approveAsManager', $leaveRequest);

        return $this->leave->managerApprove($leaveRequest, $request->user());
    }

    private function hrApprove(Request $request, LeaveRequest $leaveRequest): LeaveRequest
    {
        $this->authorize('approveAsHr', $leaveRequest);

        return $this->leave->hrApprove($leaveRequest, $request->user());
    }

    public function reject(RejectLeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $this->authorize('reject', $leaveRequest);

        $updated = $this->leave->reject($leaveRequest, $request->user(), $request->validated('rejection_reason'));

        return LeaveRequestResource::make($updated->load(['employee', 'leaveType']));
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $this->authorize('cancel', $leaveRequest);

        return LeaveRequestResource::make($this->leave->cancel($leaveRequest)->load(['employee', 'leaveType']));
    }

    public function balances(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', (int) now()->year)
            ->get()
            ->map(fn (LeaveBalance $balance) => [
                'leave_type' => $balance->leaveType->name,
                'entitled_days' => $balance->entitled_days,
                'used_days' => $balance->used_days,
                'remaining_days' => $balance->remaining_days,
            ]);

        return response()->json(['data' => $balances]);
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
