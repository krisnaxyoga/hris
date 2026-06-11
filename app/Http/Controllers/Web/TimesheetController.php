<?php

namespace App\Http\Controllers\Web;

use App\Enums\TimesheetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Timesheet\StoreTimesheetRequest;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TimesheetController extends Controller
{
    public function __construct(private readonly TimesheetService $timesheets) {}

    public function index(Request $request): View
    {
        $employee = $request->user()->employeeProfile;

        $mine = $employee
            ? Timesheet::where('employee_id', $employee->id)->latest('work_date')->paginate(10)
            : null;

        // Reviewers see submitted timesheets across the company.
        $review = $request->user()->can('employee.update')
            ? Timesheet::with('employee')
                ->where('company_id', $request->user()->company_id)
                ->where('status', TimesheetStatus::Submitted)
                ->latest('work_date')
                ->get()
            : collect();

        return view('timesheets.index', compact('employee', 'mine', 'review'));
    }

    public function store(StoreTimesheetRequest $request): RedirectResponse
    {
        $employee = $request->user()->employeeProfile;
        abort_unless($employee !== null, 403, 'Your account is not linked to an employee profile.');

        $this->authorize('create', Timesheet::class);
        $this->timesheets->create($employee, $request->validated());

        return redirect()->route('timesheets.index')->with('success', 'Timesheet saved as draft.');
    }

    public function submit(Timesheet $timesheet): RedirectResponse
    {
        $this->authorize('submit', $timesheet);

        return $this->run(fn () => $this->timesheets->submit($timesheet), 'Timesheet submitted.');
    }

    public function approve(Timesheet $timesheet): RedirectResponse
    {
        $this->authorize('review', $timesheet);

        return $this->run(fn () => $this->timesheets->approve($timesheet), 'Timesheet approved.');
    }

    public function reject(Timesheet $timesheet): RedirectResponse
    {
        $this->authorize('review', $timesheet);

        return $this->run(fn () => $this->timesheets->reject($timesheet), 'Timesheet rejected.');
    }

    private function run(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', $message);
    }
}
