<?php

namespace Database\Factories;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeProfile>
 */
class EmployeeProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();

        return [
            'company_id' => $company,
            'user_id' => User::factory()->for($company),
            'employee_code' => 'EMP-'.fake()->unique()->numerify('#####'),
            'national_id' => fake()->unique()->numerify('################'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(Gender::cases()),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-20 years'),
            'phone_number' => fake()->phoneNumber(),
            'personal_email' => fake()->unique()->safeEmail(),
            'join_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'employment_status' => fake()->randomElement(EmploymentStatus::cases()),
            'department_id' => null,
            'position_id' => null,
            'manager_id' => null,
            'profile_photo' => null,
        ];
    }
}
