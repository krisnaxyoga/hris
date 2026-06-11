<?php

namespace App\Providers;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\AttendanceRequestRepositoryInterface;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\DailyWorkLogRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\TimesheetRepositoryInterface;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Eloquent\AttendanceRequestRepository;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\DailyWorkLogRepository;
use App\Repositories\Eloquent\DepartmentRepository;
use App\Repositories\Eloquent\EmployeeRepository;
use App\Repositories\Eloquent\LeaveRequestRepository;
use App\Repositories\Eloquent\PositionRepository;
use App\Repositories\Eloquent\TimesheetRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Map of repository contracts to their Eloquent implementations.
     * Laravel automatically registers these bindings from the public property.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        CompanyRepositoryInterface::class => CompanyRepository::class,
        DepartmentRepositoryInterface::class => DepartmentRepository::class,
        PositionRepositoryInterface::class => PositionRepository::class,
        EmployeeRepositoryInterface::class => EmployeeRepository::class,
        AttendanceRepositoryInterface::class => AttendanceRepository::class,
        LeaveRequestRepositoryInterface::class => LeaveRequestRepository::class,
        AttendanceRequestRepositoryInterface::class => AttendanceRequestRepository::class,
        DailyWorkLogRepositoryInterface::class => DailyWorkLogRepository::class,
        TimesheetRepositoryInterface::class => TimesheetRepository::class,
    ];
}
