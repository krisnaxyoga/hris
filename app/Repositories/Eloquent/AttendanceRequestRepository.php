<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceRequest;
use App\Repositories\Contracts\AttendanceRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<AttendanceRequest>
 */
class AttendanceRequestRepository extends BaseRepository implements AttendanceRequestRepositoryInterface
{
    public function __construct(AttendanceRequest $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['company_id'] ?? null, fn (Builder $query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['attendance_mode'] ?? null, fn (Builder $query, string $mode) => $query->where('attendance_mode', $mode));
    }
}
