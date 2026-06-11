<?php

namespace App\Policies;

use App\Models\Timesheet;
use App\Models\User;

class TimesheetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Timesheet $timesheet): bool
    {
        if ($user->company_id !== $timesheet->company_id) {
            return false;
        }

        return $this->owns($user, $timesheet) || $user->can('employee.view');
    }

    public function create(User $user): bool
    {
        return $user->employeeProfile !== null;
    }

    public function update(User $user, Timesheet $timesheet): bool
    {
        return $this->owns($user, $timesheet);
    }

    public function submit(User $user, Timesheet $timesheet): bool
    {
        return $this->owns($user, $timesheet);
    }

    public function review(User $user, Timesheet $timesheet): bool
    {
        return $user->company_id === $timesheet->company_id
            && ($user->hasRole('Manager') || $user->hasRole('HR') || $user->can('employee.update'));
    }

    private function owns(User $user, Timesheet $timesheet): bool
    {
        return $timesheet->employee->user_id === $user->id;
    }
}
