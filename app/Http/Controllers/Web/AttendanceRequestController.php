<?php

namespace App\Http\Controllers\Web;

use App\Enums\AttendanceMode;
use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest\RejectAttendanceRequestRequest;
use App\Http\Requests\AttendanceRequest\StoreAttendanceRequestRequest;
use App\Models\AttendanceRequest;
use App\Services\AttendanceRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceRequestController extends Controller
{
    public function __construct(private readonly AttendanceRequestService $requests) {}

    public function me(Request $request): View
    {
        $employee = $request->user()->employeeProfile;

        $requests = $employee
            ? AttendanceRequest::where('employee_id', $employee->id)->latest()->paginate(10)
            : null;

        $modes = [
            AttendanceMode::Wfh->value => AttendanceMode::Wfh->label(),
            AttendanceMode::BusinessTrip->value => AttendanceMode::BusinessTrip->label(),
        ];

        return view('work-arrangements.me', compact('employee', 'requests', 'modes'));
    }

    public function store(StoreAttendanceRequestRequest $request): RedirectResponse
    {
        $employee = $request->user()->employeeProfile;
        abort_unless($employee !== null, 403, 'Your account is not linked to an employee profile.');

        $this->authorize('create', AttendanceRequest::class);
        $this->requests->apply($employee, $request->validated());

        return redirect()->route('work-arrangements.me')->with('success', 'Request submitted.');
    }

    public function approvals(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRequest::class);

        $pending = AttendanceRequest::with(['employee.manager'])
            ->where('company_id', $request->user()->company_id)
            ->where('status', RequestStatus::Pending)
            ->latest()
            ->get()
            ->filter(fn (AttendanceRequest $ar) => $request->user()->can('approve', $ar))
            ->values();

        return view('work-arrangements.approvals', compact('pending'));
    }

    public function approve(Request $request, AttendanceRequest $attendanceRequest): RedirectResponse
    {
        $this->authorize('approve', $attendanceRequest);

        try {
            $this->requests->approve($attendanceRequest, $request->user());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Request approved.');
    }

    public function reject(RejectAttendanceRequestRequest $request, AttendanceRequest $attendanceRequest): RedirectResponse
    {
        $this->authorize('reject', $attendanceRequest);

        $this->requests->reject($attendanceRequest, $request->user(), $request->validated('rejection_reason'));

        return back()->with('success', 'Request rejected.');
    }

    public function cancel(AttendanceRequest $attendanceRequest): RedirectResponse
    {
        $this->authorize('cancel', $attendanceRequest);

        try {
            $this->requests->cancel($attendanceRequest);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Request cancelled.');
    }
}
