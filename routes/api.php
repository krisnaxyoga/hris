<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttendanceRequestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DailyWorkLogController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::apiResource('companies', CompanyController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('positions', PositionController::class);
        Route::apiResource('employees', EmployeeController::class);

        Route::post('employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])
            ->name('employees.documents.store');

        // Attendance
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/today', [AttendanceController::class, 'today'])->name('attendance.today');
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');

        // Leave management
        Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::post('leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
        Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
        Route::get('leave-balances', [LeaveRequestController::class, 'balances'])->name('leave-balances.index');

        // Work arrangements: WFH / Business Trip requests
        Route::get('attendance-requests', [AttendanceRequestController::class, 'index'])->name('attendance-requests.index');
        Route::post('attendance-requests', [AttendanceRequestController::class, 'store'])->name('attendance-requests.store');
        Route::put('attendance-requests/{attendanceRequest}/approve', [AttendanceRequestController::class, 'approve'])->name('attendance-requests.approve');
        Route::put('attendance-requests/{attendanceRequest}/reject', [AttendanceRequestController::class, 'reject'])->name('attendance-requests.reject');
        Route::put('attendance-requests/{attendanceRequest}/cancel', [AttendanceRequestController::class, 'cancel'])->name('attendance-requests.cancel');

        // Daily work logs (WFH activity)
        Route::get('daily-work-logs', [DailyWorkLogController::class, 'index'])->name('daily-work-logs.index');
        Route::post('daily-work-logs', [DailyWorkLogController::class, 'store'])->name('daily-work-logs.store');

        // Timesheets
        Route::get('timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
        Route::post('timesheets', [TimesheetController::class, 'store'])->name('timesheets.store');
        Route::put('timesheets/{timesheet}/submit', [TimesheetController::class, 'submit'])->name('timesheets.submit');
        Route::put('timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])->name('timesheets.approve');
        Route::put('timesheets/{timesheet}/reject', [TimesheetController::class, 'reject'])->name('timesheets.reject');
    });
});
