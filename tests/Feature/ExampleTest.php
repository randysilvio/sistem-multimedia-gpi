<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_health_check_reports_ok_without_session(): void
    {
        $this->get('/up')->assertOk()->assertJson(['status' => 'ok', 'database' => true])
            ->assertCookieMissing(config('session.cookie'));
    }
}
