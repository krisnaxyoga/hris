<?php

namespace App\Repositories\Eloquent;

use App\Models\DailyWorkLog;
use App\Repositories\Contracts\DailyWorkLogRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<DailyWorkLog>
 */
class DailyWorkLogRepository extends BaseRepository implements DailyWorkLogRepositoryInterface
{
    public function __construct(DailyWorkLog $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['company_id'] ?? null, fn (Builder $query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['attendance_id'] ?? null, fn (Builder $query, int $attendanceId) => $query->where('attendance_id', $attendanceId));
    }
}
