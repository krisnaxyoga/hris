<?php

namespace App\Models;

use Database\Factories\AttendanceLocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['company_id', 'name', 'latitude', 'longitude', 'radius_meter', 'is_active'])]
class AttendanceLocation extends Model
{
    /** @use HasFactory<AttendanceLocationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meter' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Distance in meters from this location to the given coordinates (Haversine).
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        $earthRadius = 6_371_000;

        $latFrom = deg2rad($this->latitude);
        $latTo = deg2rad($latitude);
        $latDelta = deg2rad($latitude - $this->latitude);
        $lonDelta = deg2rad($longitude - $this->longitude);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Whether the given coordinates fall within this location's geofence radius.
     */
    public function covers(float $latitude, float $longitude): bool
    {
        return $this->distanceTo($latitude, $longitude) <= $this->radius_meter;
    }
}
