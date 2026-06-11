<?php

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();
        $start = fake()->dateTimeBetween('now', '+1 month');
        $end = (clone $start)->modify('+2 days');

        return [
            'company_id' => $company,
            'employee_id' => EmployeeProfile::factory()->for($company),
            'leave_type_id' => LeaveType::factory()->for($company),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_days' => 3,
            'reason' => fake()->sentence(),
            'status' => LeaveStatus::PendingManager,
        ];
    }

    public function pendingHr(): static
    {
        return $this->state(fn (array $attributes) => ['status' => LeaveStatus::PendingHr]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => LeaveStatus::Approved]);
    }
}
