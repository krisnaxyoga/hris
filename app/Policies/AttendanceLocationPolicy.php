<?php

namespace App\Policies;

use App\Models\AttendanceLocation;
use App\Models\User;

class AttendanceLocationPolicy
{
    /**
     * Office geofences are managed by HR and admins (Super Admin passes via Gate::before).
     */
    private function manages(User $user): bool
    {
        return $user->hasRole('HR') || $user->can('company.update');
    }

    public function viewAny(User $user): bool
    {
        return $this->manages($user);
    }

    public function create(User $user): bool
    {
        return $this->manages($user);
    }

    public function update(User $user, AttendanceLocation $location): bool
    {
        return $user->company_id === $location->company_id && $this->manages($user);
    }

    public function delete(User $user, AttendanceLocation $location): bool
    {
        return $user->company_id === $location->company_id && $this->manages($user);
    }
}
