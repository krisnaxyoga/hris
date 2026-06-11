<?php

namespace Tests\Unit\Enums;

use App\Enums\LeaveStatus;
use PHPUnit\Framework\TestCase;

class LeaveStatusTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('pending_manager', LeaveStatus::PendingManager->value);
        $this->assertSame('pending_hr', LeaveStatus::PendingHr->value);
        $this->assertSame('approved', LeaveStatus::Approved->value);
        $this->assertSame('rejected', LeaveStatus::Rejected->value);
        $this->assertSame('cancelled', LeaveStatus::Cancelled->value);
    }

    public function test_label_returns_human_readable_text(): void
    {
        $this->assertSame('Pending Manager', LeaveStatus::PendingManager->label());
        $this->assertSame('Pending HR', LeaveStatus::PendingHr->label());
        $this->assertSame('Approved', LeaveStatus::Approved->label());
        $this->assertSame('Rejected', LeaveStatus::Rejected->label());
        $this->assertSame('Cancelled', LeaveStatus::Cancelled->label());
    }

    public function test_color_maps_each_status_to_a_badge(): void
    {
        $this->assertSame('badge-warning', LeaveStatus::PendingManager->color());
        $this->assertSame('badge-warning', LeaveStatus::PendingHr->color());
        $this->assertSame('badge-success', LeaveStatus::Approved->color());
        $this->assertSame('badge-error', LeaveStatus::Rejected->color());
        $this->assertSame('badge-neutral', LeaveStatus::Cancelled->color());
    }

    public function test_is_final_only_for_terminal_states(): void
    {
        $this->assertFalse(LeaveStatus::PendingManager->isFinal());
        $this->assertFalse(LeaveStatus::PendingHr->isFinal());
        $this->assertTrue(LeaveStatus::Approved->isFinal());
        $this->assertTrue(LeaveStatus::Rejected->isFinal());
        $this->assertTrue(LeaveStatus::Cancelled->isFinal());
    }
}
