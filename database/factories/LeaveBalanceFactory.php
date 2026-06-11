<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveBalance>
 */
class LeaveBalanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();

        return [
            'company_id' => $company,
            'employee_id' => EmployeeProfile::factory()->for($company),
            'leave_type_id' => LeaveType::factory()->for($company),
            'year' => (int) date('Y'),
            'entitled_days' => 12,
            'used_days' => 0,
        ];
    }
}
