<?php

namespace Tests\Unit\Models;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\EmployeeProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_working_hours_accessor_converts_minutes_to_hours(): void
    {
        $attendance = new Attendance(['working_minutes' => 535]);

        $this->assertSame(8.92, $attendance->working_hours);
    }

    public function test_working_hours_accessor_rounds_to_two_decimals(): void
    {
        $attendance = new Attendance(['working_minutes' => 90]);

        $this->assertSame(1.5, $attendance->working_hours);
    }

    public function test_status_and_mode_are_cast_to_enums(): void
    {
        $attendance = Attendance::factory()->late()->create();

        $this->assertInstanceOf(AttendanceStatus::class, $attendance->attendance_status);
        $this->assertSame(AttendanceStatus::Late, $attendance->attendance_status);
    }

    public function test_belongs_to_employee(): void
    {
        $attendance = Attendance::factory()->create();

        $this->assertInstanceOf(EmployeeProfile::class, $attendance->employee);
    }

    public function test_scope_between_filters_by_date_range(): void
    {
        $employee = EmployeeProfile::factory()->create();

        Attendance::factory()->for($employee, 'employee')->create(['attendance_date' => '2026-01-10']);
        Attendance::factory()->for($employee, 'employee')->create(['attendance_date' => '2026-02-15']);
        Attendance::factory()->for($employee, 'employee')->create(['attendance_date' => '2026-03-20']);

        $count = Attendance::query()->between('2026-01-01', '2026-02-28')->count();

        $this->assertSame(2, $count);
    }
}
