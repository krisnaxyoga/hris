<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DailyWorkLog;
use App\Models\EmployeeProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyWorkLog>
 */
class DailyWorkLogFactory extends Factory
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
            'attendance_id' => null,
            'task' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_time' => now()->setTime(9, 0),
            'end_time' => now()->setTime(11, 0),
        ];
    }
}
