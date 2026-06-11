<?php

namespace Tests\Unit\Enums;

use App\Enums\DocumentType;
use PHPUnit\Framework\TestCase;

class DocumentTypeTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('national_id', DocumentType::NationalId->value);
        $this->assertSame('tax_id', DocumentType::TaxId->value);
        $this->assertSame('resume', DocumentType::Resume->value);
        $this->assertSame('contract', DocumentType::Contract->value);
        $this->assertSame('certificate', DocumentType::Certificate->value);
        $this->assertSame('other', DocumentType::Other->value);
    }

    public function test_label_titlecases_and_replaces_underscores(): void
    {
        $this->assertSame('National Id', DocumentType::NationalId->label());
        $this->assertSame('Tax Id', DocumentType::TaxId->label());
        $this->assertSame('Resume', DocumentType::Resume->label());
        $this->assertSame('Other', DocumentType::Other->label());
    }
}
