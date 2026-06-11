<?php

namespace Tests\Unit\Enums;

use App\Enums\WorkArrangement;
use PHPUnit\Framework\TestCase;

class WorkArrangementTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('office', WorkArrangement::Office->value);
        $this->assertSame('hybrid', WorkArrangement::Hybrid->value);
        $this->assertSame('remote', WorkArrangement::Remote->value);
    }

    public function test_label_capitalises_value(): void
    {
        $this->assertSame('Office', WorkArrangement::Office->label());
        $this->assertSame('Hybrid', WorkArrangement::Hybrid->label());
        $this->assertSame('Remote', WorkArrangement::Remote->label());
    }
}
