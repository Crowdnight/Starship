<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_and_upload_a_public_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => $user->email,
                'avatar_color' => '#ab12cd',
                'avatar' => UploadedFile::fake()->createWithContent(
                    'profile.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC', true)
                ),
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('#AB12CD', $user->avatar_color);
        $this->assertNotNull($user->avatar_path);
        $this->assertSame(asset('storage/'.$user->avatar_path), $user->avatar_url);
        Storage::disk('public')->assertExists($user->avatar_path);

        $note = $user->notes()->create([
            'title' => 'Catatan publik',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
        Comment::create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'content' => 'Komentar publik',
        ]);

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee($user->avatar_url, false)
            ->assertSee('user-avatar-with-photo', false)
            ->assertSee('Nama Baru');

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertDontSee('name="email"', false)
            ->assertSee('profile-hero', false)
            ->assertSee('PROFIL STARSHIP')
            ->assertSee('is-selected', false)
            ->assertSee('aria-pressed=', false)
            ->assertSee('data-avatar-upload-trigger', false)
            ->assertSee('class="profile-avatar-file"', false)
            ->assertSee('data-avatar-crop-dialog', false)
            ->assertSee('data-crop-stage', false)
            ->assertSee('data-crop-zoom', false)
            ->assertSee('data-crop-apply', false)
            ->assertSee('Geser foto ke arah mana saja', false)
            ->assertSee('canvas.toBlob', false)
            ->assertDontSee('<label for="avatar">Foto Profil</label>', false)
            ->assertSee('user-avatar-with-photo', false);
    }

    public function test_email_is_displayed_but_cannot_be_changed_in_profile_settings(): void
    {
        $user = User::factory()->create(['email' => 'tetap@example.com']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('tetap@example.com')
            ->assertDontSee('name="email"', false)
            ->assertDontSee('id="email"', false);

        $this->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'diubah@example.com',
            'avatar_color' => '#4775BF',
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('tetap@example.com', $user->fresh()->email);
    }

    public function test_user_can_remove_their_avatar_and_use_their_color(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'avatar_color' => '#4775BF',
            'avatar_path' => 'avatars/old.png',
        ]);
        Storage::disk('public')->put($user->avatar_path, 'old avatar');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_color' => '#00AA55',
                'remove_avatar' => '1',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertNull($user->avatar_path);
        $this->assertSame('#00AA55', $user->avatar_color);
        Storage::disk('public')->assertMissing('avatars/old.png');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('#00AA55');
    }

    public function test_invalid_profile_image_and_avatar_color_are_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_color' => 'blue; background-image: url(example)',
                'avatar' => UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors(['avatar_color', 'avatar']);

        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_profile_settings_are_opened_from_the_sidebar_profile(): void
    {
        $user = User::factory()->create(['name' => 'Siapa Ya']);

        $this->actingAs($user)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('aria-label="Pengaturan profil Siapa Ya"', false)
            ->assertDontSee('>⚙ Pengaturan Profil</a>', false);
    }

    public function test_user_can_delete_their_account_and_related_notes_comments_and_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar_path' => 'avatars/user.png']);
        $otherUser = User::factory()->create();
        Storage::disk('public')->put($user->avatar_path, 'avatar');

        $ownedNote = $user->notes()->create([
            'title' => 'Catatan saya',
            'content' => 'Catatan ini ikut dihapus',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
        $ownedComment = $ownedNote->comments()->create([
            'user_id' => $otherUser->id,
            'content' => 'Komentar pada catatan saya',
        ]);

        $otherNote = $otherUser->notes()->create([
            'title' => 'Catatan orang lain',
            'content' => 'Tetap ada',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
        $userComment = $otherNote->comments()->create([
            'user_id' => $user->id,
            'content' => 'Komentar saya pada catatan orang lain',
        ]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'))
            ->assertRedirect(route('public-notes.index'))
            ->assertSessionHas('success', 'Akun dan semua catatanmu berhasil dihapus.');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('notes', ['id' => $ownedNote->id]);
        $this->assertDatabaseMissing('comments', ['id' => $ownedComment->id]);
        $this->assertDatabaseMissing('comments', ['id' => $userComment->id]);
        $this->assertDatabaseHas('notes', ['id' => $otherNote->id]);
        Storage::disk('public')->assertMissing('avatars/user.png');

        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }
}
