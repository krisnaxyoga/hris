<?php

namespace App\Models;

use App\Enums\AttendanceMode;
use App\Enums\AttendanceStatus;
use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'employee_id', 'shift_id', 'attendance_location_id', 'attendance_date',
    'check_in_time', 'check_out_time', 'check_in_latitude', 'check_in_longitude',
    'check_out_latitude', 'check_out_longitude', 'check_in_photo', 'check_out_photo',
    'attendance_status', 'late_minutes', 'working_minutes', 'notes',
    'attendance_mode', 'check_in_ip_address', 'check_in_user_agent', 'work_location',
])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
            'check_in_latitude' => 'float',
            'check_in_longitude' => 'float',
            'check_out_latitude' => 'float',
            'check_out_longitude' => 'float',
            'attendance_status' => AttendanceStatus::class,
            'attendance_mode' => AttendanceMode::class,
            'late_minutes' => 'integer',
            'working_minutes' => 'integer',
        ];
    }

    public function getWorkingHoursAttribute(): float
    {
        return round($this->working_minutes / 60, 2);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<EmployeeProfile, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @return BelongsTo<AttendanceLocation, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class, 'attendance_location_id');
    }

    /**
     * @return HasMany<DailyWorkLog, $this>
     */
    public function dailyWorkLogs(): HasMany
    {
        return $this->hasMany(DailyWorkLog::class);
    }

    /**
     * @param  Builder<Attendance>  $query
     * @return Builder<Attendance>
     */
    public function scopeBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('attendance_date', [$from, $to]);
    }
}
