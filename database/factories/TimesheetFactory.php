<?php

namespace Database\Factories;

use App\Enums\TimesheetStatus;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\Timesheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timesheet>
 */
class TimesheetFactory extends Factory
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
            'work_date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
            'project_name' => fake()->randomElement(['Apollo', 'Helios', 'Orion']),
            'task_name' => fake()->sentence(3),
            'hours_spent' => fake()->randomFloat(1, 1, 8),
            'notes' => fake()->optional()->sentence(),
            'status' => TimesheetStatus::Draft,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => ['status' => TimesheetStatus::Submitted]);
    }
}
