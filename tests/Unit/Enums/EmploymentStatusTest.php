<?php

namespace Tests\Unit\Enums;

use App\Enums\EmploymentStatus;
use PHPUnit\Framework\TestCase;

class EmploymentStatusTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('permanent', EmploymentStatus::Permanent->value);
        $this->assertSame('contract', EmploymentStatus::Contract->value);
        $this->assertSame('probation', EmploymentStatus::Probation->value);
        $this->assertSame('intern', EmploymentStatus::Intern->value);
        $this->assertSame('resigned', EmploymentStatus::Resigned->value);
    }

    public function test_label_capitalises_value(): void
    {
        $this->assertSame('Permanent', EmploymentStatus::Permanent->label());
        $this->assertSame('Intern', EmploymentStatus::Intern->label());
    }

    public function test_color_maps_each_status_to_a_badge(): void
    {
        $this->assertSame('badge-success', EmploymentStatus::Permanent->color());
        $this->assertSame('badge-info', EmploymentStatus::Contract->color());
        $this->assertSame('badge-warning', EmploymentStatus::Probation->color());
        $this->assertSame('badge-neutral', EmploymentStatus::Intern->color());
        $this->assertSame('badge-error', EmploymentStatus::Resigned->color());
    }
}
