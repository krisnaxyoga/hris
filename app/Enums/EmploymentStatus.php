<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case Permanent = 'permanent';
    case Contract = 'contract';
    case Probation = 'probation';
    case Intern = 'intern';
    case Resigned = 'resigned';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * DaisyUI badge color modifier for this status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Permanent => 'badge-success',
            self::Contract => 'badge-info',
            self::Probation => 'badge-warning',
            self::Intern => 'badge-neutral',
            self::Resigned => 'badge-error',
        };
    }
}
