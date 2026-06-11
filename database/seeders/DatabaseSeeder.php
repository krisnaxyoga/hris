<?php

namespace Database\Seeders;

use App\Models\AttendanceLocation;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $company = Company::firstOrCreate(
            ['email' => 'hello@hris.local'],
            [
                'name' => 'HRIS Demo Company',
                'phone' => '+62 800 0000 0000',
                'address' => 'Jakarta, Indonesia',
                'subscription_plan' => 'enterprise',
                'is_active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@hris.local'],
            [
                'company_id' => $company->id,
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles('Super Admin');

        $departments = [
            'HR' => 'Human Resources',
            'IT' => 'Information Technology',
            'FIN' => 'Finance',
            'MKT' => 'Marketing',
        ];

        /** @var array<string, Department> $createdDepartments */
        $createdDepartments = [];

        foreach ($departments as $code => $name) {
            $createdDepartments[$code] = Department::firstOrCreate(
                ['company_id' => $company->id, 'code' => $code],
                ['name' => $name, 'description' => "{$name} department"]
            );
        }

        $positions = [
            ['code' => 'HR-MGR', 'name' => 'HR Manager', 'department' => 'HR'],
            ['code' => 'SWE', 'name' => 'Software Engineer', 'department' => 'IT'],
            ['code' => 'FIN-MGR', 'name' => 'Finance Manager', 'department' => 'FIN'],
            ['code' => 'MKT-EXE', 'name' => 'Marketing Executive', 'department' => 'MKT'],
        ];

        foreach ($positions as $position) {
            $department = $createdDepartments[$position['department']];

            Position::firstOrCreate(
                ['company_id' => $company->id, 'code' => $position['code']],
                [
                    'department_id' => $department->id,
                    'name' => $position['name'],
                    'description' => $position['name'],
                ]
            );
        }

        // Attendance setup: default shift + office location.
        $shift = Shift::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Regular Shift'],
            ['start_time' => '08:00:00', 'end_time' => '17:00:00', 'grace_period_minutes' => 15]
        );

        AttendanceLocation::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Head Office'],
            ['latitude' => -8.6705, 'longitude' => 115.2126, 'radius_meter' => 200, 'is_active' => true],
        );

        // Demo employee with a login account (employee@hris.local / password).
        $employeeUser = User::firstOrCreate(
            ['email' => 'employee@hris.local'],
            [
                'company_id' => $company->id,
                'name' => 'Demo Employee',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $employeeUser->syncRoles('Employee');

        EmployeeProfile::firstOrCreate(
            ['company_id' => $company->id, 'user_id' => $employeeUser->id],
            [
                'employee_code' => 'EMP-0001',
                'first_name' => 'Demo',
                'last_name' => 'Employee',
                'join_date' => now()->subYear(),
                'employment_status' => 'permanent',
                'department_id' => $createdDepartments['IT']->id,
                'shift_id' => $shift->id,
            ]
        );

        // Leave types.
        $leaveTypes = [
            ['code' => 'ANNUAL', 'name' => 'Annual Leave', 'annual_quota' => 12, 'is_paid' => true, 'requires_attachment' => false],
            ['code' => 'SICK', 'name' => 'Sick Leave', 'annual_quota' => 12, 'is_paid' => true, 'requires_attachment' => true],
            ['code' => 'MATERNITY', 'name' => 'Maternity Leave', 'annual_quota' => 90, 'is_paid' => true, 'requires_attachment' => true],
            ['code' => 'PERMISSION', 'name' => 'Permission Leave', 'annual_quota' => 0, 'is_paid' => false, 'requires_attachment' => false],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['company_id' => $company->id, 'code' => $leaveType['code']],
                $leaveType,
            );
        }
    }
}
