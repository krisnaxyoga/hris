<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceLocation;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private AttendanceLocation $location;

    private EmployeeProfile $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);

        $this->company = Company::factory()->create();
        $this->location = AttendanceLocation::factory()->for($this->company)->create([
            'latitude' => -8.6705,
            'longitude' => 115.2126,
            'radius_meter' => 150,
        ]);

        $shift = Shift::factory()->for($this->company)->create([
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'grace_period_minutes' => 15,
        ]);

        $user = User::factory()->for($this->company)->create();
        $user->assignRole('Employee');
        $this->employee = EmployeeProfile::factory()
            ->for($this->company)
            ->create(['user_id' => $user->id, 'shift_id' => $shift->id]);

        Sanctum::actingAs($user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_check_in_within_radius_succeeds_and_marks_present(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 08:05:00'));

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -8.6705,
            'longitude' => 115.2126,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attendance_status', 'present')
            ->assertJsonPath('data.late_minutes', 0);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_status' => 'present',
        ]);
    }

    public function test_check_in_outside_radius_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 08:05:00'));

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -6.2000,
            'longitude' => 106.8166,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrorFor('location');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_late_check_in_is_flagged(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 09:00:00'));

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -8.6705,
            'longitude' => 115.2126,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attendance_status', 'late')
            ->assertJsonPath('data.late_minutes', 45);
    }

    public function test_double_check_in_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 08:05:00'));

        $payload = ['latitude' => -8.6705, 'longitude' => 115.2126];
        $this->postJson('/api/v1/attendance/check-in', $payload)->assertCreated();
        $this->postJson('/api/v1/attendance/check-in', $payload)->assertUnprocessable();
    }

    public function test_check_out_computes_working_minutes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 08:00:00'));
        $this->postJson('/api/v1/attendance/check-in', [
            'latitude' => -8.6705,
            'longitude' => 115.2126,
        ])->assertCreated();

        Carbon::setTestNow(Carbon::parse('2026-06-11 17:00:00'));
        $response = $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => -8.6705,
            'longitude' => 115.2126,
        ]);

        $response->assertOk()->assertJsonPath('data.working_minutes', 540);
    }

    public function test_check_out_without_check_in_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 17:00:00'));

        $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => -8.6705,
            'longitude' => 115.2126,
        ])->assertUnprocessable();
    }
}
