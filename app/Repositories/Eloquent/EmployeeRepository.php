<?php

namespace App\Repositories\Eloquent;

use App\Models\EmployeeProfile;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<EmployeeProfile>
 */
class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(EmployeeProfile $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['company_id'] ?? null, fn (Builder $query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['department_id'] ?? null, fn (Builder $query, int $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['position_id'] ?? null, fn (Builder $query, int $positionId) => $query->where('position_id', $positionId))
            ->when($filters['employment_status'] ?? null, fn (Builder $query, string $status) => $query->where('employment_status', $status))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%");
                });
            });
    }
}
