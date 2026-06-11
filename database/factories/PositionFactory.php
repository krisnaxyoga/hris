<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->jobTitle();

        return [
            'company_id' => Company::factory(),
            'department_id' => Department::factory(),
            'code' => 'POS-'.Str::upper(Str::random(5)),
            'name' => $name,
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Attach this position to an existing department (and its company).
     */
    public function forDepartment(Department $department): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $department->company_id,
            'department_id' => $department->id,
        ]);
    }
}
