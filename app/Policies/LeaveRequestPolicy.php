<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->company_id !== $leaveRequest->company_id) {
            return false;
        }

        return $this->owns($user, $leaveRequest) || $user->can('employee.view');
    }

    public function create(User $user): bool
    {
        return $user->employeeProfile !== null;
    }

    public function approveAsManager(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->company_id === $leaveRequest->company_id
            && $leaveRequest->employee->manager?->user_id === $user->id;
    }

    public function approveAsHr(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->company_id === $leaveRequest->company_id
            && ($user->hasRole('HR') || $user->can('employee.update'));
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->approveAsManager($user, $leaveRequest) || $this->approveAsHr($user, $leaveRequest);
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->owns($user, $leaveRequest);
    }

    private function owns(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->employee->user_id === $user->id;
    }
}
