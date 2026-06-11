<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\WorkArrangement;
use Database\Factories\EmployeeProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'company_id', 'user_id', 'employee_code', 'national_id', 'first_name', 'last_name',
    'gender', 'date_of_birth', 'phone_number', 'personal_email', 'join_date',
    'employment_status', 'department_id', 'position_id', 'manager_id', 'shift_id', 'profile_photo',
    'work_arrangement',
])]
class EmployeeProfile extends Model
{
    /** @use HasFactory<EmployeeProfileFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'join_date' => 'date',
            'gender' => Gender::class,
            'employment_status' => EmploymentStatus::class,
            'work_arrangement' => WorkArrangement::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'employee_code', 'first_name', 'last_name', 'employment_status',
                'department_id', 'position_id', 'manager_id',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @return BelongsTo<EmployeeProfile, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'manager_id');
    }

    /**
     * @return HasMany<EmployeeProfile, $this>
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(EmployeeProfile::class, 'manager_id');
    }

    /**
     * @return HasOne<EmployeeAddress, $this>
     */
    public function address(): HasOne
    {
        return $this->hasOne(EmployeeAddress::class, 'employee_id');
    }

    /**
     * @return HasMany<EmployeeDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    /**
     * @return HasMany<LeaveRequest, $this>
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    /**
     * @return HasMany<LeaveBalance, $this>
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    /**
     * @return HasMany<AttendanceRequest, $this>
     */
    public function attendanceRequests(): HasMany
    {
        return $this->hasMany(AttendanceRequest::class, 'employee_id');
    }

    /**
     * @return HasMany<DailyWorkLog, $this>
     */
    public function dailyWorkLogs(): HasMany
    {
        return $this->hasMany(DailyWorkLog::class, 'employee_id');
    }

    /**
     * @return HasMany<Timesheet, $this>
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class, 'employee_id');
    }
}
