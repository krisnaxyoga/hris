<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employee.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->company_id !== $attendance->company_id) {
            return false;
        }

        // Employees may view their own attendance; managers/HR may view team attendance.
        if ($user->employeeProfile?->id === $attendance->employee_id) {
            return true;
        }

        return $user->can('employee.view');
    }
}
