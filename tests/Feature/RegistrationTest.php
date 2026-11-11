<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_to_login_without_authenticating_the_user(): void
    {
        $password = 'secure-password';

        $this->post(route('register.post'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Akun berhasil dibuat. Silakan masuk dengan akun baru Anda.');

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->assertGuest();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Akun berhasil dibuat. Silakan masuk dengan akun baru Anda.');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => $password,
        ])
            ->assertRedirect(route('notes.index'));

        $this->assertAuthenticatedAs($user);
    }
}
