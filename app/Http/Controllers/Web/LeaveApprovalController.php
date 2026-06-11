<?php

namespace App\Http\Controllers\Web;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\RejectLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    public function __construct(private readonly LeaveService $leave) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $user = $request->user();
        $employee = $user->employeeProfile;

        // Pending items this user can act on: manager stage for their reports, HR stage for HR/Super Admin.
        $pending = LeaveRequest::with(['employee.manager', 'leaveType'])
            ->where('company_id', $user->company_id)
            ->whereIn('status', [LeaveStatus::PendingManager, LeaveStatus::PendingHr])
            ->latest()
            ->get()
            ->filter(fn (LeaveRequest $lr) => $user->can('reject', $lr))
            ->values();

        return view('leave.approvals', compact('pending'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        try {
            match ($leaveRequest->status) {
                LeaveStatus::PendingManager => $this->approveManager($request, $leaveRequest),
                LeaveStatus::PendingHr => $this->approveHr($request, $leaveRequest),
                default => throw ValidationException::withMessages(['status' => 'Not awaiting approval.']),
            };
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(RejectLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        $this->leave->reject($leaveRequest, $request->user(), $request->validated('rejection_reason'));

        return back()->with('success', 'Leave request rejected.');
    }

    private function approveManager(Request $request, LeaveRequest $leaveRequest): void
    {
        $this->authorize('approveAsManager', $leaveRequest);
        $this->leave->managerApprove($leaveRequest, $request->user());
    }

    private function approveHr(Request $request, LeaveRequest $leaveRequest): void
    {
        $this->authorize('approveAsHr', $leaveRequest);
        $this->leave->hrApprove($leaveRequest, $request->user());
    }
}
