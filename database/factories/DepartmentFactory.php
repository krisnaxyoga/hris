<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['HR', 'IT', 'Finance', 'Marketing', 'Operations', 'Sales']);

        return [
            'company_id' => Company::factory(),
            'code' => Str::upper(Str::substr($name, 0, 3)).fake()->unique()->numberBetween(10, 99),
            'name' => $name,
            'description' => fake()->sentence(),
        ];
    }
}
