<?php

namespace App\Services;

use App\Enums\TimesheetStatus;
use App\Models\EmployeeProfile;
use App\Models\Timesheet;
use App\Repositories\Contracts\TimesheetRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimesheetService
{
    public function __construct(private readonly TimesheetRepositoryInterface $timesheets) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Timesheet>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->timesheets->paginate($filters, $perPage, ['employee']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(EmployeeProfile $employee, array $data): Timesheet
    {
        return DB::transaction(fn () => Timesheet::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'work_date' => $data['work_date'],
            'project_name' => $data['project_name'],
            'task_name' => $data['task_name'],
            'hours_spent' => $data['hours_spent'],
            'notes' => $data['notes'] ?? null,
            'status' => TimesheetStatus::Draft,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Timesheet $timesheet, array $data): Timesheet
    {
        if ($timesheet->status !== TimesheetStatus::Draft) {
            throw ValidationException::withMessages(['status' => 'Only draft timesheets can be edited.']);
        }

        $timesheet->update($data);

        return $timesheet->refresh();
    }

    public function submit(Timesheet $timesheet): Timesheet
    {
        if ($timesheet->status !== TimesheetStatus::Draft) {
            throw ValidationException::withMessages(['status' => 'Only draft timesheets can be submitted.']);
        }

        $timesheet->update(['status' => TimesheetStatus::Submitted]);

        return $timesheet->refresh();
    }

    public function approve(Timesheet $timesheet): Timesheet
    {
        $this->assertSubmitted($timesheet);

        $timesheet->update(['status' => TimesheetStatus::Approved]);

        return $timesheet->refresh();
    }

    public function reject(Timesheet $timesheet): Timesheet
    {
        $this->assertSubmitted($timesheet);

        $timesheet->update(['status' => TimesheetStatus::Rejected]);

        return $timesheet->refresh();
    }

    private function assertSubmitted(Timesheet $timesheet): void
    {
        if ($timesheet->status !== TimesheetStatus::Submitted) {
            throw ValidationException::withMessages(['status' => 'Only submitted timesheets can be reviewed.']);
        }
    }
}
