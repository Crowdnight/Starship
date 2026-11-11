<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteConfirmationUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_delete_actions_use_the_shared_confirmation_dialog(): void
    {
        $user = User::factory()->create();
        $note = $user->notes()->create([
            'title' => 'Catatan untuk dihapus',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => false,
        ]);

        $this->actingAs($user)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('id="delete-confirm-dialog"', false)
            ->assertSee('data-confirm-delete', false)
            ->assertSee('data-delete-label="catatan"', false)
            ->assertDontSee("confirm('Yakin ingin menghapus catatan ini?')");

        $this->get(route('notes.show', $note))
            ->assertOk()
            ->assertSee('id="delete-confirm-dialog"', false)
            ->assertSee('data-confirm-delete', false)
            ->assertSee('data-delete-label="catatan"', false)
            ->assertDontSee("confirm('Yakin ingin menghapus catatan ini?')");
    }

    public function test_removing_a_profile_photo_uses_the_shared_confirmation_dialog(): void
    {
        $user = User::factory()->create(['avatar_path' => 'avatars/profile.png']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('id="delete-confirm-dialog"', false)
            ->assertSee('data-confirm-when-checked="remove_avatar"', false)
            ->assertSee('data-delete-label="foto profil"', false);
    }

    public function test_deleting_an_account_uses_the_shared_confirmation_dialog(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('id="delete-confirm-dialog"', false)
            ->assertSee('action="'.route('profile.destroy').'"', false)
            ->assertSee('data-delete-label="akun"', false)
            ->assertSee('data-confirm-delete', false)
            ->assertSee('semua catatan, komentar, dan foto profilmu akan dihapus permanen.');
    }
}
