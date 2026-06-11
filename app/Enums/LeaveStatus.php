<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case PendingManager = 'pending_manager';
    case PendingHr = 'pending_hr';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingManager => 'Pending Manager',
            self::PendingHr => 'Pending HR',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingManager, self::PendingHr => 'badge-warning',
            self::Approved => 'badge-success',
            self::Rejected => 'badge-error',
            self::Cancelled => 'badge-neutral',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected, self::Cancelled], true);
    }
}
