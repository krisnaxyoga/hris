<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private DepartmentService $service;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DepartmentService::class);
        $this->company = Company::factory()->create();
    }

    public function test_create_persists_department(): void
    {
        $department = $this->service->create([
            'company_id' => $this->company->id,
            'code' => 'ENG10',
            'name' => 'Engineering',
        ]);

        $this->assertDatabaseHas('departments', ['code' => 'ENG10']);
        $this->assertSame('Engineering', $department->name);
    }

    public function test_find_returns_department(): void
    {
        $department = Department::factory()->for($this->company)->create();

        $this->assertTrue($this->service->find($department->id)->is($department));
    }

    public function test_find_throws_when_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->find(999);
    }

    public function test_update_changes_attributes(): void
    {
        $department = Department::factory()->for($this->company)->create();

        $updated = $this->service->update($department, ['name' => 'People Ops']);

        $this->assertSame('People Ops', $updated->name);
    }

    public function test_delete_soft_deletes_department(): void
    {
        $department = Department::factory()->for($this->company)->create();

        $this->assertTrue($this->service->delete($department));
        $this->assertSoftDeleted($department);
    }

    public function test_paginate_returns_departments(): void
    {
        Department::factory()->count(2)->for($this->company)->create();

        $this->assertSame(2, $this->service->paginate()->total());
    }
}
