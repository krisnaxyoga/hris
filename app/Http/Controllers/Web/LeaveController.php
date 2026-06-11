<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(private readonly LeaveService $leave) {}

    public function me(Request $request): View
    {
        $employee = $request->user()->employeeProfile;

        $requests = $employee
            ? LeaveRequest::with('leaveType')->where('employee_id', $employee->id)->latest()->paginate(10)
            : null;

        $balances = $employee
            ? LeaveBalance::with('leaveType')->where('employee_id', $employee->id)->where('year', (int) now()->year)->get()
            : collect();

        $leaveTypes = LeaveType::where('company_id', $request->user()->company_id)->orderBy('name')->pluck('name', 'id')->all();

        return view('leave.me', compact('employee', 'requests', 'balances', 'leaveTypes'));
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $employee = $request->user()->employeeProfile;
        abort_unless($employee !== null, 403, 'Your account is not linked to an employee profile.');

        $this->authorize('create', LeaveRequest::class);

        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        try {
            $this->leave->apply($employee, $data);
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('leave.me')->with('success', 'Leave request submitted.');
    }

    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('cancel', $leaveRequest);

        try {
            $this->leave->cancel($leaveRequest);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Leave request cancelled.');
    }
}
