<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aplikasi akan online: pendaftaran umum harus TERTUTUP.
 * Akun baru hanya dibuat lewat menu Pengguna oleh orang yang sudah masuk.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_closed(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Penyusup', 'email' => 'x@example.com', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertStatus(404);

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }
}
