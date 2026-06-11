<?php

namespace App\Policies;

use App\Models\DailyWorkLog;
use App\Models\User;

class DailyWorkLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DailyWorkLog $log): bool
    {
        if ($user->company_id !== $log->company_id) {
            return false;
        }

        return $log->employee->user_id === $user->id || $user->can('employee.view');
    }

    public function create(User $user): bool
    {
        return $user->employeeProfile !== null;
    }
}
