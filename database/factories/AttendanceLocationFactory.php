<?php

namespace Database\Factories;

use App\Models\AttendanceLocation;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceLocation>
 */
class AttendanceLocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Head Office', 'Branch Office', 'Warehouse']),
            'latitude' => fake()->latitude(-8.8, -8.6),
            'longitude' => fake()->longitude(115.1, 115.3),
            'radius_meter' => 100,
            'is_active' => true,
        ];
    }
}
