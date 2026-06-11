<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\EmployeeProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
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
            'attendance_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'check_in_time' => now()->setTime(8, 5),
            'check_out_time' => now()->setTime(17, 0),
            'check_in_latitude' => fake()->latitude(-8.8, -8.6),
            'check_in_longitude' => fake()->longitude(115.1, 115.3),
            'attendance_status' => AttendanceStatus::Present,
            'late_minutes' => 0,
            'working_minutes' => 535,
        ];
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_status' => AttendanceStatus::Late,
            'late_minutes' => fake()->numberBetween(10, 60),
        ]);
    }
}
