<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_reset_link_and_reset_their_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Kirim tautan reset');

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            }
        );

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]))
            ->assertOk()
            ->assertSee('Buat password baru');

        $newPassword = 'new-secure-password';
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Password berhasil diperbarui. Silakan masuk dengan password baru.');

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => $newPassword,
        ])->assertRedirect(route('notes.index'));
    }

    public function test_reset_request_does_not_reveal_whether_email_is_registered(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'registered@example.com']);

        $registeredResponse = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'registered@example.com']);
        $unknownResponse = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'unknown@example.com']);

        $this->assertSame(
            $registeredResponse->getSession()->get('status'),
            $unknownResponse->getSession()->get('status')
        );

        Notification::assertSentToTimes(
            User::where('email', 'registered@example.com')->first(),
            ResetPassword::class,
            1
        );
    }

    public function test_invalid_reset_token_does_not_change_the_password(): void
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertSame($oldPassword, $user->fresh()->password);
    }
}
