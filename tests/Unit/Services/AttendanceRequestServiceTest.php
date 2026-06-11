<?php

namespace Tests\Unit\Services;

use App\Enums\AttendanceMode;
use App\Enums\RequestStatus;
use App\Models\AttendanceRequest;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Services\AttendanceRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttendanceRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceRequestService $service;

    private EmployeeProfile $employee;

    private User $approver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AttendanceRequestService::class);
        $this->employee = EmployeeProfile::factory()->create();
        $this->approver = User::factory()->for($this->employee->company)->create();
    }

    public function test_apply_creates_pending_request(): void
    {
        $request = $this->service->apply($this->employee, [
            'attendance_date' => '2026-06-20',
            'attendance_mode' => AttendanceMode::Wfh->value,
            'work_location' => 'Home',
            'reason' => 'Focus day',
        ]);

        $this->assertSame(RequestStatus::Pending, $request->status);
        $this->assertSame($this->employee->id, $request->employee_id);
        $this->assertSame('Home', $request->work_location);
    }

    public function test_approve_sets_approver_and_timestamp(): void
    {
        $request = AttendanceRequest::factory()->for($this->employee, 'employee')->create();

        $approved = $this->service->approve($request, $this->approver);

        $this->assertSame(RequestStatus::Approved, $approved->status);
        $this->assertSame($this->approver->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
    }

    public function test_reject_stores_reason(): void
    {
        $request = AttendanceRequest::factory()->for($this->employee, 'employee')->create();

        $rejected = $this->service->reject($request, $this->approver, 'Coverage needed');

        $this->assertSame(RequestStatus::Rejected, $rejected->status);
        $this->assertSame('Coverage needed', $rejected->rejection_reason);
    }

    public function test_cancel_sets_cancelled_status(): void
    {
        $request = AttendanceRequest::factory()->for($this->employee, 'employee')->create();

        $cancelled = $this->service->cancel($request);

        $this->assertSame(RequestStatus::Cancelled, $cancelled->status);
    }

    public function test_cannot_approve_already_processed_request(): void
    {
        $request = AttendanceRequest::factory()->approved()->for($this->employee, 'employee')->create();

        $this->expectException(ValidationException::class);
        $this->service->approve($request, $this->approver);
    }

    public function test_cannot_reject_already_processed_request(): void
    {
        $request = AttendanceRequest::factory()->approved()->for($this->employee, 'employee')->create();

        $this->expectException(ValidationException::class);
        $this->service->reject($request, $this->approver, 'Too late');
    }

    public function test_paginate_returns_requests(): void
    {
        AttendanceRequest::factory()->count(2)->for($this->employee, 'employee')->create();

        $this->assertSame(2, $this->service->paginate()->total());
    }
}
