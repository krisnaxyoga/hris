<?php

namespace Tests\Unit\Services;

use App\Models\Attendance;
use App\Models\EmployeeProfile;
use App\Services\DailyWorkLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyWorkLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private DailyWorkLogService $service;

    private EmployeeProfile $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DailyWorkLogService::class);
        $this->employee = EmployeeProfile::factory()->create();
    }

    public function test_create_persists_log_for_employee(): void
    {
        $log = $this->service->create($this->employee, [
            'task' => 'Write tests',
            'description' => 'Unit coverage',
        ]);

        $this->assertSame('Write tests', $log->task);
        $this->assertSame($this->employee->id, $log->employee_id);
        $this->assertSame($this->employee->company_id, $log->company_id);
    }

    public function test_create_links_attendance_when_provided(): void
    {
        $attendance = Attendance::factory()->for($this->employee, 'employee')->create();

        $log = $this->service->create($this->employee, [
            'task' => 'Standup',
            'attendance_id' => $attendance->id,
        ]);

        $this->assertSame($attendance->id, $log->attendance_id);
    }

    public function test_paginate_returns_logs(): void
    {
        $this->service->create($this->employee, ['task' => 'A']);
        $this->service->create($this->employee, ['task' => 'B']);

        $this->assertSame(2, $this->service->paginate()->total());
    }
}
