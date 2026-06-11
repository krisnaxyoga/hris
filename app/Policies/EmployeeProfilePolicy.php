<?php

namespace App\Policies;

use App\Models\EmployeeProfile;
use App\Models\User;

class EmployeeProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employee.view');
    }

    public function view(User $user, EmployeeProfile $employee): bool
    {
        return $user->can('employee.view') && $user->company_id === $employee->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('employee.create');
    }

    public function update(User $user, EmployeeProfile $employee): bool
    {
        return $user->can('employee.update') && $user->company_id === $employee->company_id;
    }

    public function delete(User $user, EmployeeProfile $employee): bool
    {
        return $user->can('employee.delete') && $user->company_id === $employee->company_id;
    }

    public function restore(User $user, EmployeeProfile $employee): bool
    {
        return $user->can('employee.delete');
    }
}
