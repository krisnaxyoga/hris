<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Permission Leave']);

        return [
            'company_id' => Company::factory(),
            'code' => Str::upper(Str::random(4)),
            'name' => $name,
            'annual_quota' => fake()->randomElement([12, 14, 30]),
            'is_paid' => true,
            'requires_attachment' => false,
        ];
    }
}
