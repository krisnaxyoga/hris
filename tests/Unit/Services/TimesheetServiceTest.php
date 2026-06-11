<?php

namespace Tests\Unit\Services;

use App\Enums\TimesheetStatus;
use App\Models\EmployeeProfile;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TimesheetServiceTest extends TestCase
{
    use RefreshDatabase;

    private TimesheetService $service;

    private EmployeeProfile $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TimesheetService::class);
        $this->employee = EmployeeProfile::factory()->create();
    }

    public function test_create_starts_as_draft(): void
    {
        $timesheet = $this->service->create($this->employee, [
            'work_date' => '2026-06-01',
            'project_name' => 'Apollo',
            'task_name' => 'Build API',
            'hours_spent' => 6,
        ]);

        $this->assertSame(TimesheetStatus::Draft, $timesheet->status);
        $this->assertSame($this->employee->id, $timesheet->employee_id);
        $this->assertSame($this->employee->company_id, $timesheet->company_id);
    }

    public function test_update_changes_draft_fields(): void
    {
        $timesheet = Timesheet::factory()->for($this->employee, 'employee')->create();

        $updated = $this->service->update($timesheet, ['task_name' => 'Refactor']);

        $this->assertSame('Refactor', $updated->task_name);
    }

    public function test_update_rejects_non_draft(): void
    {
        $timesheet = Timesheet::factory()->submitted()->for($this->employee, 'employee')->create();

        $this->expectException(ValidationException::class);
        $this->service->update($timesheet, ['task_name' => 'Nope']);
    }

    public function test_submit_moves_draft_to_submitted(): void
    {
        $timesheet = Timesheet::factory()->for($this->employee, 'employee')->create();

        $submitted = $this->service->submit($timesheet);

        $this->assertSame(TimesheetStatus::Submitted, $submitted->status);
    }

    public function test_submit_rejects_non_draft(): void
    {
        $timesheet = Timesheet::factory()->submitted()->for($this->employee, 'employee')->create();

        $this->expectException(ValidationException::class);
        $this->service->submit($timesheet);
    }

    public function test_approve_moves_submitted_to_approved(): void
    {
        $timesheet = Timesheet::factory()->submitted()->for($this->employee, 'employee')->create();

        $approved = $this->service->approve($timesheet);

        $this->assertSame(TimesheetStatus::Approved, $approved->status);
    }

    public function test_approve_rejects_draft(): void
    {
        $timesheet = Timesheet::factory()->for($this->employee, 'employee')->create();

        $this->expectException(ValidationException::class);
        $this->service->approve($timesheet);
    }

    public function test_reject_moves_submitted_to_rejected(): void
    {
        $timesheet = Timesheet::factory()->submitted()->for($this->employee, 'employee')->create();

        $rejected = $this->service->reject($timesheet);

        $this->assertSame(TimesheetStatus::Rejected, $rejected->status);
    }

    public function test_reject_rejects_non_submitted(): void
    {
        $timesheet = Timesheet::factory()->for($this->employee, 'employee')->create();

        $this->expectException(ValidationException::class);
        $this->service->reject($timesheet);
    }

    public function test_paginate_returns_company_timesheets(): void
    {
        Timesheet::factory()->count(3)->for($this->employee, 'employee')->create();

        $this->assertSame(3, $this->service->paginate()->total());
    }
}
