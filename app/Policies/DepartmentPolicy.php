<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('department.view');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->can('department.view') && $user->company_id === $department->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('department.create');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can('department.update') && $user->company_id === $department->company_id;
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->can('department.delete') && $user->company_id === $department->company_id;
    }

    public function restore(User $user, Department $department): bool
    {
        return $user->can('department.delete');
    }
}
