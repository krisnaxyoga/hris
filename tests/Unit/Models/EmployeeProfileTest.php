<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_joins_first_and_last_name(): void
    {
        $employee = new EmployeeProfile(['first_name' => 'Ayu', 'last_name' => 'Lestari']);

        $this->assertSame('Ayu Lestari', $employee->full_name);
    }

    public function test_full_name_trims_when_last_name_missing(): void
    {
        $employee = new EmployeeProfile(['first_name' => 'Ayu', 'last_name' => null]);

        $this->assertSame('Ayu', $employee->full_name);
    }

    public function test_belongs_to_user(): void
    {
        $employee = EmployeeProfile::factory()->create();

        $this->assertInstanceOf(User::class, $employee->user);
    }

    public function test_manager_and_subordinates_relationship(): void
    {
        $manager = EmployeeProfile::factory()->create();
        $report = EmployeeProfile::factory()->for($manager->company)->create(['manager_id' => $manager->id]);

        $this->assertTrue($manager->subordinates->contains($report));
        $this->assertTrue($report->manager->is($manager));
    }

    public function test_has_many_attendances_and_leave_requests(): void
    {
        $employee = EmployeeProfile::factory()->create();
        Attendance::factory()->for($employee, 'employee')->create();
        LeaveRequest::factory()->for($employee, 'employee')->create(['company_id' => $employee->company_id]);

        $this->assertCount(1, $employee->attendances);
        $this->assertCount(1, $employee->leaveRequests);
    }
}
