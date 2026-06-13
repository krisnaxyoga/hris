<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceLocation;
use App\Models\AttendanceRequest;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\Timesheet;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkArrangementApiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private EmployeeProfile $employee;

    private User $hrUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);
        $this->company = Company::factory()->create();

        AttendanceLocation::factory()->for($this->company)->create([
            'latitude' => -8.6705,
            'longitude' => 115.2126,
            'radius_meter' => 150,
        ]);

        $user = User::factory()->for($this->company)->create();
        $user->assignRole('Employee');
        $this->employee = EmployeeProfile::factory()->for($this->company)->create(['user_id' => $user->id]);

        $this->hrUser = User::factory()->for($this->company)->create();
        $this->hrUser->assignRole('HR');
    }

    public function test_approved_wfh_check_in_skips_geofence_and_records_metadata(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-12 08:05:00'));

        // An approved WFH request must exist for the day to bypass the office geofence.
        AttendanceRequest::factory()->approved()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-06-12',
            'attendance_mode' => 'wfh',
        ]);

        Sanctum::actingAs($this->employee->user);

        // Coordinates far from any office — allowed because WFH is approved.
        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'attendance_mode' => 'wfh',
            'work_location' => 'Home',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_mode' => 'wfh',
            'work_location' => 'Home',
        ]);
        $this->assertNotNull($this->employee->attendances()->first()->check_in_ip_address);

        Carbon::setTestNow();
    }

    public function test_unapproved_wfh_is_downgraded_to_office_and_geofenced(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-12 08:05:00'));
        Sanctum::actingAs($this->employee->user);

        // WFH requested but no approved request -> treated as office -> outside radius -> rejected.
        $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'attendance_mode' => 'wfh',
        ])->assertUnprocessable()->assertJsonValidationErrorFor('location');

        // Inside the office radius, an unapproved WFH check-in is accepted but recorded as office.
        $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -8.6705,
            'longitude' => 115.2126,
            'attendance_mode' => 'wfh',
        ])->assertCreated()->assertJsonPath('data.attendance_status', 'present');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_mode' => 'office',
        ]);

        Carbon::setTestNow();
    }

    public function test_office_check_in_still_enforces_geofence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-12 08:05:00'));
        Sanctum::actingAs($this->employee->user);

        $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'attendance_mode' => 'office',
        ])->assertUnprocessable()->assertJsonValidationErrorFor('location');

        Carbon::setTestNow();
    }

    public function test_employee_can_request_wfh_and_hr_approves(): void
    {
        Sanctum::actingAs($this->employee->user);
        $create = $this->postJson('/api/v1/attendance-requests', [
            'attendance_date' => '2026-06-20',
            'attendance_mode' => 'wfh',
            'work_location' => 'Home',
            'reason' => 'Focus day',
        ]);
        $create->assertCreated()->assertJsonPath('data.status', 'pending');

        $id = AttendanceRequest::firstOrFail()->id;

        Sanctum::actingAs($this->hrUser);
        $this->putJson("/api/v1/attendance-requests/{$id}/approve")
            ->assertOk()->assertJsonPath('data.status', 'approved');
    }

    public function test_office_mode_is_rejected_for_attendance_request(): void
    {
        Sanctum::actingAs($this->employee->user);

        $this->postJson('/api/v1/attendance-requests', [
            'attendance_date' => '2026-06-20',
            'attendance_mode' => 'office',
        ])->assertUnprocessable()->assertJsonValidationErrorFor('attendance_mode');
    }

    public function test_daily_work_log_can_be_created(): void
    {
        Sanctum::actingAs($this->employee->user);

        $this->postJson('/api/v1/daily-work-logs', [
            'task' => 'Implement feature',
            'description' => 'Built the reporting screen',
        ])->assertCreated();

        $this->assertDatabaseHas('daily_work_logs', [
            'employee_id' => $this->employee->id,
            'task' => 'Implement feature',
        ]);
    }

    public function test_timesheet_draft_submit_and_approve_flow(): void
    {
        Sanctum::actingAs($this->employee->user);
        $this->postJson('/api/v1/timesheets', [
            'work_date' => '2026-06-12',
            'project_name' => 'Apollo',
            'task_name' => 'API work',
            'hours_spent' => 4.5,
        ])->assertCreated()->assertJsonPath('data.status', 'draft');

        $timesheet = Timesheet::firstOrFail();

        $this->putJson("/api/v1/timesheets/{$timesheet->id}/submit")
            ->assertOk()->assertJsonPath('data.status', 'submitted');

        Sanctum::actingAs($this->hrUser);
        $this->putJson("/api/v1/timesheets/{$timesheet->id}/approve")
            ->assertOk()->assertJsonPath('data.status', 'approved');
    }

    public function test_employee_cannot_approve_own_timesheet(): void
    {
        $timesheet = Timesheet::factory()->submitted()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
        ]);

        Sanctum::actingAs($this->employee->user);
        $this->putJson("/api/v1/timesheets/{$timesheet->id}/approve")->assertForbidden();
    }
}
