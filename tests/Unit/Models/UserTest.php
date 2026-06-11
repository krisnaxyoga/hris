<?php

namespace Tests\Unit\Models;

use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_hidden_from_array(): void
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    public function test_password_is_cast_to_hashed(): void
    {
        $user = User::factory()->create();

        // Hashed casts never store the raw value.
        $this->assertNotSame('password', $user->password);
        $this->assertTrue(password_verify('password', $user->password));
    }

    public function test_whatsapp_route_returns_employee_phone_number(): void
    {
        $user = User::factory()->create();
        EmployeeProfile::factory()->for($user->company)->create([
            'user_id' => $user->id,
            'phone_number' => '+628123456789',
        ]);

        $this->assertSame('+628123456789', $user->fresh()->routeNotificationForWhatsapp());
    }

    public function test_whatsapp_route_is_null_without_employee_profile(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->routeNotificationForWhatsapp());
    }

    public function test_has_one_employee_profile(): void
    {
        $user = User::factory()->create();
        $profile = EmployeeProfile::factory()->for($user->company)->create(['user_id' => $user->id]);

        $this->assertTrue($user->employeeProfile->is($profile));
    }
}
