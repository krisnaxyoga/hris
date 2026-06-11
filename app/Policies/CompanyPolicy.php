<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('company.view');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('company.view') && $this->belongsToCompany($user, $company);
    }

    public function create(User $user): bool
    {
        return $user->can('company.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('company.update') && $this->belongsToCompany($user, $company);
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->can('company.delete') && $this->belongsToCompany($user, $company);
    }

    public function restore(User $user, Company $company): bool
    {
        return $user->can('company.delete');
    }

    private function belongsToCompany(User $user, Company $company): bool
    {
        return $user->company_id === $company->id;
    }
}
