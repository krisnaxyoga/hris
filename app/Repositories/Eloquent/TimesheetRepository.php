<?php

namespace App\Repositories\Eloquent;

use App\Models\Timesheet;
use App\Repositories\Contracts\TimesheetRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<Timesheet>
 */
class TimesheetRepository extends BaseRepository implements TimesheetRepositoryInterface
{
    public function __construct(Timesheet $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['company_id'] ?? null, fn (Builder $query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['project_name'] ?? null, fn (Builder $query, string $project) => $query->where('project_name', 'like', "%{$project}%"));
    }
}
