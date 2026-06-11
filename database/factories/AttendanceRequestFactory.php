<?php

namespace Database\Factories;

use App\Enums\AttendanceMode;
use App\Enums\RequestStatus;
use App\Models\AttendanceRequest;
use App\Models\Company;
use App\Models\EmployeeProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRequest>
 */
class AttendanceRequestFactory extends Factory
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
            'attendance_date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'attendance_mode' => fake()->randomElement([AttendanceMode::Wfh, AttendanceMode::BusinessTrip]),
            'work_location' => fake()->city(),
            'reason' => fake()->sentence(),
            'status' => RequestStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => RequestStatus::Approved]);
    }
}
