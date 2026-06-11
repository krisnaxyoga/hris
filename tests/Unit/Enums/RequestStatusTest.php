<?php

namespace Tests\Unit\Enums;

use App\Enums\RequestStatus;
use PHPUnit\Framework\TestCase;

class RequestStatusTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('pending', RequestStatus::Pending->value);
        $this->assertSame('approved', RequestStatus::Approved->value);
        $this->assertSame('rejected', RequestStatus::Rejected->value);
        $this->assertSame('cancelled', RequestStatus::Cancelled->value);
    }

    public function test_label_capitalises_value(): void
    {
        $this->assertSame('Pending', RequestStatus::Pending->label());
        $this->assertSame('Approved', RequestStatus::Approved->label());
    }

    public function test_color_maps_each_status_to_a_badge(): void
    {
        $this->assertSame('badge-warning', RequestStatus::Pending->color());
        $this->assertSame('badge-success', RequestStatus::Approved->color());
        $this->assertSame('badge-error', RequestStatus::Rejected->color());
        $this->assertSame('badge-neutral', RequestStatus::Cancelled->color());
    }

    public function test_is_final_for_everything_except_pending(): void
    {
        $this->assertFalse(RequestStatus::Pending->isFinal());
        $this->assertTrue(RequestStatus::Approved->isFinal());
        $this->assertTrue(RequestStatus::Rejected->isFinal());
        $this->assertTrue(RequestStatus::Cancelled->isFinal());
    }
}
