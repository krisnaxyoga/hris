<?php

namespace App\Policies;

use App\Models\AttendanceRequest;
use App\Models\User;

class AttendanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AttendanceRequest $request): bool
    {
        if ($user->company_id !== $request->company_id) {
            return false;
        }

        return $this->owns($user, $request) || $user->can('employee.view');
    }

    public function create(User $user): bool
    {
        return $user->employeeProfile !== null;
    }

    public function approve(User $user, AttendanceRequest $request): bool
    {
        if ($user->company_id !== $request->company_id) {
            return false;
        }

        $isManager = $request->employee->manager?->user_id === $user->id;

        return $isManager || $user->hasRole('HR') || $user->can('employee.update');
    }

    public function reject(User $user, AttendanceRequest $request): bool
    {
        return $this->approve($user, $request);
    }

    public function cancel(User $user, AttendanceRequest $request): bool
    {
        return $this->owns($user, $request);
    }

    private function owns(User $user, AttendanceRequest $request): bool
    {
        return $request->employee->user_id === $user->id;
    }
}
