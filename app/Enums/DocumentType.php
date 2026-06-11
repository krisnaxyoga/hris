<?php

namespace App\Enums;

enum DocumentType: string
{
    case NationalId = 'national_id';
    case TaxId = 'tax_id';
    case Resume = 'resume';
    case Contract = 'contract';
    case Certificate = 'certificate';
    case Other = 'other';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
