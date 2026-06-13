<?php

namespace Tests\Feature\Web;

use App\Models\AttendanceLocation;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceZoneTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->company = Company::factory()->create();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->for($this->company)->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_hr_can_create_an_attendance_zone(): void
    {
        $hr = $this->userWithRole('HR');

        $this->actingAs($hr)->get(route('attendance-locations.create'))->assertOk();

        $this->actingAs($hr)->post(route('attendance-locations.store'), [
            'name' => 'Branch Office',
            'latitude' => -8.65,
            'longitude' => 115.21,
            'radius_meter' => 250,
            'is_active' => 1,
        ])->assertRedirect(route('attendance-locations.index'));

        $this->assertDatabaseHas('attendance_locations', [
            'company_id' => $this->company->id,
            'name' => 'Branch Office',
            'radius_meter' => 250,
        ]);
    }

    public function test_admin_can_widen_the_radius(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $zone = AttendanceLocation::factory()->for($this->company)->create(['radius_meter' => 100]);

        $this->actingAs($admin)->put(route('attendance-locations.update', $zone), [
            'name' => $zone->name,
            'latitude' => $zone->latitude,
            'longitude' => $zone->longitude,
            'radius_meter' => 1500,
            'is_active' => 1,
        ])->assertRedirect(route('attendance-locations.index'));

        $this->assertDatabaseHas('attendance_locations', [
            'id' => $zone->id,
            'radius_meter' => 1500,
        ]);
    }

    public function test_regular_employee_cannot_manage_zones(): void
    {
        $employee = $this->userWithRole('Employee');

        $this->actingAs($employee)->get(route('attendance-locations.index'))->assertForbidden();
        $this->actingAs($employee)->post(route('attendance-locations.store'), [
            'name' => 'Nope',
            'latitude' => -8.65,
            'longitude' => 115.21,
            'radius_meter' => 200,
        ])->assertForbidden();
    }
}
