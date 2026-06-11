<?php

namespace App\Http\Controllers\Web;

use App\Enums\AttendanceMode;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $stats = [
            'employees' => EmployeeProfile::where('company_id', $companyId)->count(),
            'departments' => Department::where('company_id', $companyId)->count(),
            'positions' => Position::where('company_id', $companyId)->count(),
            'users' => User::where('company_id', $companyId)->count(),
        ];

        $today = CarbonImmutable::now()->toDateString();
        $todayAttendance = Attendance::where('company_id', $companyId)->whereDate('attendance_date', $today);
        $headcount = EmployeeProfile::where('company_id', $companyId)->count();
        $presentToday = (clone $todayAttendance)->whereNotNull('check_in_time')->count();

        $widgets = [
            'office_today' => (clone $todayAttendance)->where('attendance_mode', AttendanceMode::Office)->count(),
            'wfh_today' => (clone $todayAttendance)->where('attendance_mode', AttendanceMode::Wfh)->count(),
            'business_trip_today' => (clone $todayAttendance)->where('attendance_mode', AttendanceMode::BusinessTrip)->count(),
            'late_today' => (clone $todayAttendance)->where('attendance_status', AttendanceStatus::Late)->count(),
            'absent_today' => max(0, $headcount - $presentToday),
        ];

        $recentEmployees = EmployeeProfile::with(['department', 'position'])
            ->where('company_id', $companyId)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'widgets', 'recentEmployees'));
    }
}
