<?php

namespace Tests\Unit\Enums;

use App\Enums\AttendanceMode;
use PHPUnit\Framework\TestCase;

class AttendanceModeTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('office', AttendanceMode::Office->value);
        $this->assertSame('wfh', AttendanceMode::Wfh->value);
        $this->assertSame('business_trip', AttendanceMode::BusinessTrip->value);
    }

    public function test_label_returns_human_readable_text(): void
    {
        $this->assertSame('Office', AttendanceMode::Office->label());
        $this->assertSame('Work From Home', AttendanceMode::Wfh->label());
        $this->assertSame('Business Trip', AttendanceMode::BusinessTrip->label());
    }

    public function test_badge_returns_daisyui_modifier(): void
    {
        $this->assertSame('badge-primary', AttendanceMode::Office->badge());
        $this->assertSame('badge-accent', AttendanceMode::Wfh->badge());
        $this->assertSame('badge-secondary', AttendanceMode::BusinessTrip->badge());
    }

    public function test_only_office_requires_geofence(): void
    {
        $this->assertTrue(AttendanceMode::Office->requiresOfficeGeofence());
        $this->assertFalse(AttendanceMode::Wfh->requiresOfficeGeofence());
        $this->assertFalse(AttendanceMode::BusinessTrip->requiresOfficeGeofence());
    }
}
