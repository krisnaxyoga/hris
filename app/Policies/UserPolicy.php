<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('user.view') && $user->company_id === $model->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('user.update') && $user->company_id === $model->company_id;
    }

    public function delete(User $user, User $model): bool
    {
        // Cannot delete self.
        return $user->can('user.delete')
            && $user->id !== $model->id
            && $user->company_id === $model->company_id;
    }
}
