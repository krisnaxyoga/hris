<?php

namespace Tests\Unit\Models;

use App\Models\AttendanceLocation;
use PHPUnit\Framework\TestCase;

class AttendanceLocationTest extends TestCase
{
    private function location(float $lat, float $lng, int $radius = 100): AttendanceLocation
    {
        return new AttendanceLocation([
            'latitude' => $lat,
            'longitude' => $lng,
            'radius_meter' => $radius,
        ]);
    }

    public function test_distance_to_same_point_is_zero(): void
    {
        $location = $this->location(-8.65, 115.21);

        $this->assertEqualsWithDelta(0.0, $location->distanceTo(-8.65, 115.21), 0.001);
    }

    public function test_distance_to_known_point_uses_haversine(): void
    {
        // One degree of latitude is ~111.19 km at the equator-ish scale used here.
        $location = $this->location(0.0, 0.0);

        $this->assertEqualsWithDelta(111_195, $location->distanceTo(1.0, 0.0), 50);
    }

    public function test_covers_returns_true_inside_radius(): void
    {
        $location = $this->location(-8.65, 115.21, 100);

        // ~11 meters away (0.0001 deg latitude).
        $this->assertTrue($location->covers(-8.6501, 115.21));
    }

    public function test_covers_returns_false_outside_radius(): void
    {
        $location = $this->location(-8.65, 115.21, 100);

        // ~1.1 km away (0.01 deg latitude) — well beyond the 100m fence.
        $this->assertFalse($location->covers(-8.66, 115.21));
    }

    public function test_covers_is_inclusive_at_the_boundary(): void
    {
        $location = $this->location(0.0, 0.0, 111_195);

        $this->assertTrue($location->covers(1.0, 0.0));
    }
}
