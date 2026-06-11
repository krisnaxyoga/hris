<?php

namespace Tests\Unit\Services;

use App\Enums\LeaveStatus;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveRequestStatusChanged;
use App\Notifications\LeaveRequestSubmitted;
use App\Services\LeaveService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeaveServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeaveService $service;

    private Company $company;

    private LeaveType $leaveType;

    private EmployeeProfile $manager;

    private EmployeeProfile $staff;

    private User $hrUser;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->seed(RolePermissionSeeder::class);

        $this->service = app(LeaveService::class);
        $this->company = Company::factory()->create();
        $this->leaveType = LeaveType::factory()->for($this->company)->create(['annual_quota' => 12]);

        $managerUser = User::factory()->for($this->company)->create();
        $managerUser->assignRole('Manager');
        $this->manager = EmployeeProfile::factory()->for($this->company)->create(['user_id' => $managerUser->id]);

        $staffUser = User::factory()->for($this->company)->create();
        $staffUser->assignRole('Employee');
        $this->staff = EmployeeProfile::factory()->for($this->company)->create([
            'user_id' => $staffUser->id,
            'manager_id' => $this->manager->id,
        ]);

        $this->hrUser = User::factory()->for($this->company)->create();
        $this->hrUser->assignRole('HR');
    }

    private function applyThreeDays(): LeaveRequest
    {
        return $this->service->apply($this->staff, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-03',
            'reason' => 'Family event',
        ]);
    }

    public function test_apply_computes_total_days_inclusive(): void
    {
        $leave = $this->applyThreeDays();

        $this->assertSame(3, $leave->total_days);
        $this->assertSame(LeaveStatus::PendingManager, $leave->status);
    }

    public function test_apply_notifies_manager_when_present(): void
    {
        $this->applyThreeDays();

        Notification::assertSentTo($this->manager->user, LeaveRequestSubmitted::class);
    }

    public function test_apply_without_manager_goes_straight_to_hr(): void
    {
        $soloUser = User::factory()->for($this->company)->create();
        $soloUser->assignRole('Employee');
        $solo = EmployeeProfile::factory()->for($this->company)->create(['user_id' => $soloUser->id]);

        $leave = $this->service->apply($solo, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-02',
        ]);

        $this->assertSame(LeaveStatus::PendingHr, $leave->status);
        Notification::assertSentTo($this->hrUser, LeaveRequestSubmitted::class);
    }

    public function test_apply_rejects_end_before_start(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->apply($this->staff, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-05',
            'end_date' => '2026-07-01',
        ]);
    }

    public function test_apply_rejects_when_balance_insufficient(): void
    {
        LeaveBalance::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->staff->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => 2026,
            'entitled_days' => 12,
            'used_days' => 11,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->apply($this->staff, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
        ]);
    }

    public function test_manager_approve_advances_to_pending_hr(): void
    {
        $leave = $this->applyThreeDays();

        $approved = $this->service->managerApprove($leave, $this->manager->user);

        $this->assertSame(LeaveStatus::PendingHr, $approved->status);
        $this->assertSame($this->manager->user->id, $approved->manager_approved_by);
        Notification::assertSentTo($this->hrUser, LeaveRequestSubmitted::class);
    }

    public function test_manager_approve_rejects_wrong_stage(): void
    {
        $leave = LeaveRequest::factory()->pendingHr()->for($this->staff, 'employee')->create([
            'company_id' => $this->company->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->managerApprove($leave, $this->manager->user);
    }

    public function test_hr_approve_consumes_balance(): void
    {
        $leave = $this->applyThreeDays();
        $this->service->managerApprove($leave, $this->manager->user);

        $approved = $this->service->hrApprove($leave->refresh(), $this->hrUser);

        $this->assertSame(LeaveStatus::Approved, $approved->status);
        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $this->staff->id,
            'leave_type_id' => $this->leaveType->id,
            'used_days' => 3,
        ]);
        Notification::assertSentTo($this->staff->user, LeaveRequestStatusChanged::class);
    }

    public function test_hr_approve_rejects_already_final(): void
    {
        $leave = LeaveRequest::factory()->approved()->for($this->staff, 'employee')->create([
            'company_id' => $this->company->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->hrApprove($leave, $this->hrUser);
    }

    public function test_reject_stores_reason_and_notifies_employee(): void
    {
        $leave = $this->applyThreeDays();

        $rejected = $this->service->reject($leave, $this->hrUser, 'Peak season');

        $this->assertSame(LeaveStatus::Rejected, $rejected->status);
        $this->assertSame('Peak season', $rejected->rejection_reason);
        Notification::assertSentTo($this->staff->user, LeaveRequestStatusChanged::class);
    }

    public function test_reject_rejects_when_already_final(): void
    {
        $leave = LeaveRequest::factory()->approved()->for($this->staff, 'employee')->create([
            'company_id' => $this->company->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->reject($leave, $this->hrUser, 'Too late');
    }

    public function test_cancel_sets_cancelled_status(): void
    {
        $leave = $this->applyThreeDays();

        $cancelled = $this->service->cancel($leave);

        $this->assertSame(LeaveStatus::Cancelled, $cancelled->status);
    }

    public function test_cancel_rejects_when_already_final(): void
    {
        $leave = LeaveRequest::factory()->approved()->for($this->staff, 'employee')->create([
            'company_id' => $this->company->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->cancel($leave);
    }
}
