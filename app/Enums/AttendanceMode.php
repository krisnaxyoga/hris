<?php

namespace App\Enums;

enum AttendanceMode: string
{
    case Office = 'office';
    case Wfh = 'wfh';
    case BusinessTrip = 'business_trip';

    public function label(): string
    {
        return match ($this) {
            self::Office => 'Office',
            self::Wfh => 'Work From Home',
            self::BusinessTrip => 'Business Trip',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Office => 'badge-primary',
            self::Wfh => 'badge-accent',
            self::BusinessTrip => 'badge-secondary',
        };
    }

    /**
     * Whether this mode enforces the office GPS geofence on check-in.
     */
    public function requiresOfficeGeofence(): bool
    {
        return $this === self::Office;
    }
}
