<?php

namespace Tests\Unit\Enums;

use App\Enums\AttendanceStatus;
use PHPUnit\Framework\TestCase;

class AttendanceStatusTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('present', AttendanceStatus::Present->value);
        $this->assertSame('late', AttendanceStatus::Late->value);
        $this->assertSame('leave', AttendanceStatus::Leave->value);
        $this->assertSame('absent', AttendanceStatus::Absent->value);
        $this->assertSame('holiday', AttendanceStatus::Holiday->value);
    }

    public function test_label_capitalises_value(): void
    {
        $this->assertSame('Present', AttendanceStatus::Present->label());
        $this->assertSame('Late', AttendanceStatus::Late->label());
        $this->assertSame('Holiday', AttendanceStatus::Holiday->label());
    }

    public function test_color_maps_each_status_to_a_badge(): void
    {
        $this->assertSame('badge-success', AttendanceStatus::Present->color());
        $this->assertSame('badge-warning', AttendanceStatus::Late->color());
        $this->assertSame('badge-info', AttendanceStatus::Leave->color());
        $this->assertSame('badge-error', AttendanceStatus::Absent->color());
        $this->assertSame('badge-neutral', AttendanceStatus::Holiday->color());
    }
}
