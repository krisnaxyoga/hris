<?php

namespace Tests\Unit\Services;

use App\Enums\DocumentType;
use App\Enums\EmploymentStatus;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Services\EmployeeService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeService $service;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->service = app(EmployeeService::class);
        $this->company = Company::factory()->create();
    }

    private function baseData(array $overrides = []): array
    {
        return array_merge([
            'company_id' => $this->company->id,
            'employee_code' => 'EMP-001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'join_date' => '2026-01-01',
            'employment_status' => EmploymentStatus::Permanent->value,
            'account' => [
                'email' => 'budi@example.com',
                'password' => 'secret123',
                'role' => 'Employee',
            ],
        ], $overrides);
    }

    public function test_create_makes_user_profile_and_assigns_role(): void
    {
        $employee = $this->service->create($this->baseData());

        $this->assertSame('Budi', $employee->first_name);
        $this->assertNotNull($employee->user);
        $this->assertSame('budi@example.com', $employee->user->email);
        $this->assertTrue($employee->user->hasRole('Employee'));
        $this->assertTrue(Hash::check('secret123', $employee->user->password));
    }

    public function test_create_derives_user_name_from_first_and_last(): void
    {
        $employee = $this->service->create($this->baseData());

        $this->assertSame('Budi Santoso', $employee->user->name);
    }

    public function test_create_persists_address_when_provided(): void
    {
        $employee = $this->service->create($this->baseData([
            'address' => [
                'address' => 'Jl. Merdeka 1',
                'city' => 'Denpasar',
                'province' => 'Bali',
                'postal_code' => '80000',
                'country' => 'Indonesia',
            ],
        ]));

        $this->assertDatabaseHas('employee_addresses', [
            'employee_id' => $employee->id,
            'city' => 'Denpasar',
        ]);
    }

    public function test_update_changes_profile_and_account(): void
    {
        $employee = $this->service->create($this->baseData());

        $updated = $this->service->update($employee, [
            'first_name' => 'Budiman',
            'account' => ['email' => 'new@example.com'],
        ]);

        $this->assertSame('Budiman', $updated->first_name);
        $this->assertSame('new@example.com', $updated->user->fresh()->email);
    }

    public function test_update_syncs_role(): void
    {
        $employee = $this->service->create($this->baseData());

        $this->service->update($employee, [
            'account' => ['role' => 'Manager'],
        ]);

        $this->assertTrue($employee->user->fresh()->hasRole('Manager'));
        $this->assertFalse($employee->user->fresh()->hasRole('Employee'));
    }

    public function test_delete_soft_deletes_employee(): void
    {
        $employee = $this->service->create($this->baseData());

        $this->assertTrue($this->service->delete($employee));
        $this->assertSoftDeleted($employee);
    }

    public function test_store_document_saves_file_and_record(): void
    {
        Storage::fake('public');
        $employee = EmployeeProfile::factory()->for($this->company)->create();

        $document = $this->service->storeDocument(
            $employee,
            DocumentType::Contract,
            UploadedFile::fake()->create('contract.pdf', 10),
        );

        $this->assertSame(DocumentType::Contract, $document->document_type);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_paginate_returns_company_employees(): void
    {
        EmployeeProfile::factory()->count(2)->for($this->company)->create();

        $this->assertSame(2, $this->service->paginate()->total());
    }
}
