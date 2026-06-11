<?php

namespace App\Services;

use App\Models\DailyWorkLog;
use App\Models\EmployeeProfile;
use App\Repositories\Contracts\DailyWorkLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DailyWorkLogService
{
    public function __construct(private readonly DailyWorkLogRepositoryInterface $logs) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, DailyWorkLog>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->logs->paginate($filters, $perPage, ['employee', 'attendance']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(EmployeeProfile $employee, array $data): DailyWorkLog
    {
        return DB::transaction(fn () => DailyWorkLog::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'attendance_id' => $data['attendance_id'] ?? null,
            'task' => $data['task'],
            'description' => $data['description'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
        ]));
    }
}
