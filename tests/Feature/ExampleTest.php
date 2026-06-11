<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root path redirects guests to the login screen.
     */
    public function test_the_application_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        // Root redirects to the dashboard, which in turn gates guests to login.
        $response->assertRedirect(route('dashboard'));
        $this->followingRedirects()->get('/')->assertSee('Sign in to your account');
    }
}
