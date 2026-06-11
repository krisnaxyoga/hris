<?php

namespace Tests\Unit\Enums;

use App\Enums\Gender;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('male', Gender::Male->value);
        $this->assertSame('female', Gender::Female->value);
    }

    public function test_label_returns_human_readable_text(): void
    {
        $this->assertSame('Male', Gender::Male->label());
        $this->assertSame('Female', Gender::Female->label());
    }
}
