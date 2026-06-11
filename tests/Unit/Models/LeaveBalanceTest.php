<?php

namespace Tests\Unit\Models;

use App\Models\LeaveBalance;
use PHPUnit\Framework\TestCase;

class LeaveBalanceTest extends TestCase
{
    public function test_remaining_days_subtracts_used_from_entitled(): void
    {
        $balance = new LeaveBalance(['entitled_days' => 12, 'used_days' => 5]);

        $this->assertSame(7.0, $balance->remaining_days);
    }

    public function test_remaining_days_can_be_zero(): void
    {
        $balance = new LeaveBalance(['entitled_days' => 12, 'used_days' => 12]);

        $this->assertSame(0.0, $balance->remaining_days);
    }

    public function test_remaining_days_supports_fractional_values(): void
    {
        $balance = new LeaveBalance(['entitled_days' => 12, 'used_days' => 2.5]);

        $this->assertSame(9.5, $balance->remaining_days);
    }
}
