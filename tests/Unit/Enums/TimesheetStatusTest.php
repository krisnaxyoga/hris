<?php

namespace Tests\Unit\Enums;

use App\Enums\TimesheetStatus;
use PHPUnit\Framework\TestCase;

class TimesheetStatusTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('draft', TimesheetStatus::Draft->value);
        $this->assertSame('submitted', TimesheetStatus::Submitted->value);
        $this->assertSame('approved', TimesheetStatus::Approved->value);
        $this->assertSame('rejected', TimesheetStatus::Rejected->value);
    }

    public function test_label_capitalises_value(): void
    {
        $this->assertSame('Draft', TimesheetStatus::Draft->label());
        $this->assertSame('Submitted', TimesheetStatus::Submitted->label());
    }

    public function test_color_maps_each_status_to_a_badge(): void
    {
        $this->assertSame('badge-neutral', TimesheetStatus::Draft->color());
        $this->assertSame('badge-warning', TimesheetStatus::Submitted->color());
        $this->assertSame('badge-success', TimesheetStatus::Approved->color());
        $this->assertSame('badge-error', TimesheetStatus::Rejected->color());
    }
}
