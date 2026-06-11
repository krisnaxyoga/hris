<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => EmployeeProfile::factory(),
            'document_type' => fake()->randomElement(DocumentType::cases()),
            'file_path' => 'employee-documents/'.fake()->uuid().'.pdf',
            'uploaded_at' => now(),
        ];
    }
}
