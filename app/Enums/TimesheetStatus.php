<?php

namespace App\Enums;

enum TimesheetStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'badge-neutral',
            self::Submitted => 'badge-warning',
            self::Approved => 'badge-success',
            self::Rejected => 'badge-error',
        };
    }
}
