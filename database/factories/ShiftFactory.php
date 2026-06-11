<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Regular Shift', 'Morning Shift', 'Evening Shift']),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'grace_period_minutes' => 15,
        ];
    }
}
