<?php

namespace Tests\Unit\Services;

use App\Enums\AttendanceMode;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceLocation;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\Shift;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceService $service;

    private Company $company;

    private Shift $shift;

    private EmployeeProfile $employee;

    private const OFFICE_LAT = -8.65;

    private const OFFICE_LNG = 115.21;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AttendanceService::class);
        $this->company = Company::factory()->create();
        $this->shift = Shift::factory()->for($this->company)->create([
            'start_time' => '08:00:00',
            'grace_period_minutes' => 15,
        ]);
        $this->employee = EmployeeProfile::factory()->for($this->company)->create([
            'shift_id' => $this->shift->id,
        ]);

        AttendanceLocation::factory()->for($this->company)->create([
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
            'radius_meter' => 100,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function freezeTime(string $time): void
    {
        Carbon::setTestNow(CarbonImmutable::parse($time));
    }

    private function officePayload(array $overrides = []): array
    {
        return array_merge([
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
            'attendance_mode' => AttendanceMode::Office->value,
        ], $overrides);
    }

    public function test_check_in_on_time_is_present(): void
    {
        $this->freezeTime('2026-06-10 08:05:00');

        $attendance = $this->service->checkIn($this->employee, $this->officePayload());

        $this->assertSame(AttendanceStatus::Present, $attendance->attendance_status);
        $this->assertSame(0, $attendance->late_minutes);
        $this->assertNotNull($attendance->attendance_location_id);
    }

    public function test_check_in_after_grace_period_is_late(): void
    {
        $this->freezeTime('2026-06-10 08:30:00');

        $attendance = $this->service->checkIn($this->employee, $this->officePayload());

        $this->assertSame(AttendanceStatus::Late, $attendance->attendance_status);
        $this->assertSame(15, $attendance->late_minutes);
    }

    public function test_check_in_outside_radius_is_rejected(): void
    {
        $this->freezeTime('2026-06-10 08:00:00');

        $this->expectException(ValidationException::class);
        $this->service->checkIn($this->employee, $this->officePayload([
            'latitude' => -8.70,
            'longitude' => 115.30,
        ]));
    }

    public function test_wfh_check_in_skips_geofence(): void
    {
        $this->freezeTime('2026-06-10 08:00:00');

        $attendance = $this->service->checkIn($this->employee, [
            'latitude' => -8.70,
            'longitude' => 115.30,
            'attendance_mode' => AttendanceMode::Wfh->value,
        ]);

        $this->assertSame(AttendanceMode::Wfh, $attendance->attendance_mode);
        $this->assertNull($attendance->attendance_location_id);
    }

    public function test_double_check_in_is_rejected(): void
    {
        $this->freezeTime('2026-06-10 08:00:00');
        $this->service->checkIn($this->employee, $this->officePayload());

        $this->expectException(ValidationException::class);
        $this->service->checkIn($this->employee, $this->officePayload());
    }

    public function test_check_out_computes_working_minutes(): void
    {
        $this->freezeTime('2026-06-10 08:00:00');
        $this->service->checkIn($this->employee, $this->officePayload());

        $this->freezeTime('2026-06-10 17:00:00');
        $attendance = $this->service->checkOut($this->employee, [
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
        ]);

        $this->assertSame(540, $attendance->working_minutes);
        $this->assertNotNull($attendance->check_out_time);
    }

    public function test_check_out_without_check_in_is_rejected(): void
    {
        $this->freezeTime('2026-06-10 17:00:00');

        $this->expectException(ValidationException::class);
        $this->service->checkOut($this->employee, []);
    }

    public function test_double_check_out_is_rejected(): void
    {
        $this->freezeTime('2026-06-10 08:00:00');
        $this->service->checkIn($this->employee, $this->officePayload());

        $this->freezeTime('2026-06-10 17:00:00');
        $this->service->checkOut($this->employee, []);

        $this->expectException(ValidationException::class);
        $this->service->checkOut($this->employee, []);
    }

    public function test_check_in_without_shift_is_never_late(): void
    {
        $this->freezeTime('2026-06-10 10:00:00');
        $employee = EmployeeProfile::factory()->for($this->company)->create(['shift_id' => null]);

        $attendance = $this->service->checkIn($employee, $this->officePayload());

        $this->assertSame(0, $attendance->late_minutes);
        $this->assertSame(AttendanceStatus::Present, $attendance->attendance_status);
    }

    public function test_check_in_fails_when_no_location_configured(): void
    {
        $this->freezeTime('2026-06-10 08:00:00');
        $company = Company::factory()->create();
        $employee = EmployeeProfile::factory()->for($company)->create();

        $this->expectException(ValidationException::class);
        $this->service->checkIn($employee, $this->officePayload());
    }
}
