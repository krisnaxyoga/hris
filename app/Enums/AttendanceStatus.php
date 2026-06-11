<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Leave = 'leave';
    case Absent = 'absent';
    case Holiday = 'holiday';

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
            self::Present => 'badge-success',
            self::Late => 'badge-warning',
            self::Leave => 'badge-info',
            self::Absent => 'badge-error',
            self::Holiday => 'badge-neutral',
        };
    }
}
