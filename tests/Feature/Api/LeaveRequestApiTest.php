<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveRequestStatusChanged;
use App\Notifications\LeaveRequestSubmitted;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveRequestApiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private LeaveType $annualLeave;

    private EmployeeProfile $manager;

    private EmployeeProfile $staff;

    private User $hrUser;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->seed(RolePermissionSeeder::class);
        $this->company = Company::factory()->create();

        $this->annualLeave = LeaveType::factory()->for($this->company)->create([
            'name' => 'Annual Leave',
            'annual_quota' => 12,
        ]);

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

    private function applyAsStaff(): LeaveRequest
    {
        Sanctum::actingAs($this->staff->user);

        $response = $this->postJson('/api/v1/leave-requests', [
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-03',
            'reason' => 'Family event',
        ]);

        $response->assertCreated()->assertJsonPath('data.status', 'pending_manager');

        return LeaveRequest::firstOrFail();
    }

    public function test_employee_applies_and_manager_is_notified(): void
    {
        $leave = $this->applyAsStaff();

        $this->assertSame(3, $leave->total_days);
        Notification::assertSentTo($this->manager->user, LeaveRequestSubmitted::class);
    }

    public function test_full_manager_then_hr_approval_consumes_balance(): void
    {
        $leave = $this->applyAsStaff();

        Sanctum::actingAs($this->manager->user);
        $this->postJson("/api/v1/leave-requests/{$leave->id}/approve")
            ->assertOk()->assertJsonPath('data.status', 'pending_hr');

        Sanctum::actingAs($this->hrUser);
        $this->postJson("/api/v1/leave-requests/{$leave->id}/approve")
            ->assertOk()->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $this->staff->id,
            'leave_type_id' => $this->annualLeave->id,
            'used_days' => 3,
        ]);
        Notification::assertSentTo($this->staff->user, LeaveRequestStatusChanged::class);
    }

    public function test_manager_cannot_skip_to_final_approval(): void
    {
        $leave = $this->applyAsStaff();

        // The staff member cannot approve their own request as a manager.
        Sanctum::actingAs($this->staff->user);
        $this->postJson("/api/v1/leave-requests/{$leave->id}/approve")->assertForbidden();
    }

    public function test_insufficient_balance_is_rejected(): void
    {
        LeaveBalance::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->staff->id,
            'leave_type_id' => $this->annualLeave->id,
            'year' => 2026,
            'entitled_days' => 12,
            'used_days' => 11,
        ]);

        Sanctum::actingAs($this->staff->user);
        $this->postJson('/api/v1/leave-requests', [
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
            'reason' => 'Trip',
        ])->assertUnprocessable()->assertJsonValidationErrorFor('leave_type_id');
    }

    public function test_hr_can_reject_with_reason(): void
    {
        $leave = $this->applyAsStaff();

        Sanctum::actingAs($this->hrUser);
        $this->postJson("/api/v1/leave-requests/{$leave->id}/reject", [
            'rejection_reason' => 'Peak season',
        ])->assertOk()->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'rejected',
            'rejection_reason' => 'Peak season',
        ]);
    }

    public function test_employee_without_manager_goes_straight_to_hr(): void
    {
        $soloUser = User::factory()->for($this->company)->create();
        $soloUser->assignRole('Employee');
        $solo = EmployeeProfile::factory()->for($this->company)->create(['user_id' => $soloUser->id]);

        Sanctum::actingAs($solo->user);
        $this->postJson('/api/v1/leave-requests', [
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
        ])->assertCreated()->assertJsonPath('data.status', 'pending_hr');
    }
}
