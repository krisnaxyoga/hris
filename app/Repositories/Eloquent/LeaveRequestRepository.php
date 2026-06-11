<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequest;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<LeaveRequest>
 */
class LeaveRequestRepository extends BaseRepository implements LeaveRequestRepositoryInterface
{
    public function __construct(LeaveRequest $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['company_id'] ?? null, fn (Builder $query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['leave_type_id'] ?? null, fn (Builder $query, int $typeId) => $query->where('leave_type_id', $typeId));
    }
}
