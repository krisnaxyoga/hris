<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PositionService $service;

    private Company $company;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PositionService::class);
        $this->company = Company::factory()->create();
        $this->department = Department::factory()->for($this->company)->create();
    }

    public function test_create_persists_position(): void
    {
        $position = $this->service->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'code' => 'POS-ABC',
            'name' => 'Backend Engineer',
        ]);

        $this->assertDatabaseHas('positions', ['code' => 'POS-ABC']);
        $this->assertSame('Backend Engineer', $position->name);
    }

    public function test_find_returns_position(): void
    {
        $position = Position::factory()->forDepartment($this->department)->create();

        $this->assertTrue($this->service->find($position->id)->is($position));
    }

    public function test_find_throws_when_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->find(999);
    }

    public function test_update_changes_attributes(): void
    {
        $position = Position::factory()->forDepartment($this->department)->create();

        $updated = $this->service->update($position, ['name' => 'Senior Engineer']);

        $this->assertSame('Senior Engineer', $updated->name);
    }

    public function test_delete_soft_deletes_position(): void
    {
        $position = Position::factory()->forDepartment($this->department)->create();

        $this->assertTrue($this->service->delete($position));
        $this->assertSoftDeleted($position);
    }

    public function test_paginate_returns_positions(): void
    {
        Position::factory()->count(2)->forDepartment($this->department)->create();

        $this->assertSame(2, $this->service->paginate()->total());
    }
}
