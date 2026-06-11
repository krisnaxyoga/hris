<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CompanyService::class);
    }

    public function test_create_persists_company(): void
    {
        $company = $this->service->create([
            'name' => 'Acme Corp',
            'email' => 'hello@acme.test',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('companies', ['name' => 'Acme Corp']);
        $this->assertTrue($company->is_active);
    }

    public function test_find_returns_company(): void
    {
        $company = Company::factory()->create();

        $this->assertTrue($this->service->find($company->id)->is($company));
    }

    public function test_find_throws_when_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->find(999);
    }

    public function test_update_changes_attributes(): void
    {
        $company = Company::factory()->create();

        $updated = $this->service->update($company, ['name' => 'Renamed']);

        $this->assertSame('Renamed', $updated->name);
    }

    public function test_delete_soft_deletes_company(): void
    {
        $company = Company::factory()->create();

        $this->assertTrue($this->service->delete($company));
        $this->assertSoftDeleted($company);
    }

    public function test_paginate_returns_companies(): void
    {
        Company::factory()->count(3)->create();

        $this->assertSame(3, $this->service->paginate()->total());
    }
}
