<?php

use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AttendanceRequestController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\LeaveApprovalController;
use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\TimesheetController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('companies', CompanyController::class)->except('show');
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('positions', PositionController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');

    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])
        ->name('employees.documents.store');

    // Attendance
    Route::get('attendance/me', [AttendanceController::class, 'me'])->name('attendance.me');
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // Leave management
    Route::get('leave/me', [LeaveController::class, 'me'])->name('leave.me');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::post('leave/{leaveRequest}/cancel', [LeaveController::class, 'cancel'])->name('leave.cancel');
    Route::get('leave/approvals', [LeaveApprovalController::class, 'index'])->name('leave.approvals');
    Route::post('leave/approvals/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])->name('leave.approve');
    Route::post('leave/approvals/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])->name('leave.reject');

    // Work arrangements (WFH / Business Trip)
    Route::get('work-arrangements/me', [AttendanceRequestController::class, 'me'])->name('work-arrangements.me');
    Route::post('work-arrangements', [AttendanceRequestController::class, 'store'])->name('work-arrangements.store');
    Route::post('work-arrangements/{attendanceRequest}/cancel', [AttendanceRequestController::class, 'cancel'])->name('work-arrangements.cancel');
    Route::get('work-arrangements/approvals', [AttendanceRequestController::class, 'approvals'])->name('work-arrangements.approvals');
    Route::post('work-arrangements/{attendanceRequest}/approve', [AttendanceRequestController::class, 'approve'])->name('work-arrangements.approve');
    Route::post('work-arrangements/{attendanceRequest}/reject', [AttendanceRequestController::class, 'reject'])->name('work-arrangements.reject');

    // Timesheets
    Route::get('timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::post('timesheets', [TimesheetController::class, 'store'])->name('timesheets.store');
    Route::post('timesheets/{timesheet}/submit', [TimesheetController::class, 'submit'])->name('timesheets.submit');
    Route::post('timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])->name('timesheets.approve');
    Route::post('timesheets/{timesheet}/reject', [TimesheetController::class, 'reject'])->name('timesheets.reject');
});
