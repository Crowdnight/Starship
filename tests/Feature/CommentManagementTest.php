<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_owner_can_edit_their_comment_from_the_public_note(): void
    {
        $creator = User::factory()->create(['name' => 'Pemilik Catatan']);
        $commenter = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $note->comments()->create([
            'user_id' => $creator->id,
            'content' => 'Komentar kreator',
        ]);
        $comment = $note->comments()->create([
            'user_id' => $commenter->id,
            'content' => 'Komentar lama',
        ]);

        $this->actingAs($commenter)
            ->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('Pemilik Catatan')
            ->assertSee('class="creator-badge">Kreator</span>', false)
            ->assertSee('Opsi komentar')
            ->assertSee('class="comment-reply-action"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('Balas')
            ->assertSee('Edit komentar')
            ->assertSee('Hapus komentar')
            ->assertSee('id="delete-confirm-dialog"', false)
            ->assertSee('Item yang dihapus tidak dapat dikembalikan.')
            ->assertSee('data-confirm-delete', false)
            ->assertDontSee('class="edited-indicator">Edited</span>', false)
            ->assertDontSee("confirm('Hapus komentar ini?')");

        $this->actingAs($commenter)
            ->put(route('comments.update', $comment), [
                'content' => 'Komentar yang diperbarui',
            ])
            ->assertRedirect(route('public-notes.show', $note));

        $this->assertSame('Komentar yang diperbarui', $comment->fresh()->content);
        $this->assertNotNull($comment->fresh()->edited_at);

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('Komentar yang diperbarui')
            ->assertSee('class="edited-indicator">Edited</span>', false);
    }

    public function test_user_sees_ellipsis_menu_only_on_comments_they_own(): void
    {
        $creator = User::factory()->create();
        $commenter = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $creatorComment = $note->comments()->create([
            'user_id' => $creator->id,
            'content' => 'Komentar kreator',
        ]);
        $userComment = $note->comments()->create([
            'user_id' => $commenter->id,
            'content' => 'Komentar saya',
        ]);

        $response = $this->actingAs($commenter)
            ->get(route('public-notes.show', $note));

        $response
            ->assertOk()
            ->assertSee('class="comment-reply-action"', false)
            ->assertSee('data-comment-edit="'.$userComment->id.'"', false)
            ->assertSee('data-confirm-delete', false)
            ->assertDontSee('data-comment-edit="'.$creatorComment->id.'"', false);

        $this->assertSame(
            1,
            substr_count($response->getContent(), 'aria-label="Opsi komentar"')
        );
    }

    public function test_note_owner_can_view_comments_from_their_all_notes_detail_page(): void
    {
        $creator = User::factory()->create(['name' => 'Pemilik Catatan']);
        $commenter = User::factory()->create(['name' => 'Pembaca']);
        $note = $this->createPublicNote($creator);
        $note->comments()->create([
            'user_id' => $commenter->id,
            'content' => 'Komentar yang terlihat dari catatan saya',
        ]);

        $this->actingAs($creator)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('Baca selengkapnya');

        $this->get(route('notes.show', $note))
            ->assertOk()
            ->assertSee('id="note-discussion-title"', false)
            ->assertSee('Komentar yang terlihat dari catatan saya')
            ->assertSee('Pembaca')
            ->assertSee('class="user-avatar note-comment-avatar', false)
            ->assertSee('aria-label="1 komentar"', false);
    }

    public function test_note_owner_can_reply_to_a_comment_from_their_notes_detail_page(): void
    {
        $creator = User::factory()->create(['name' => 'Pemilik Catatan']);
        $reader = User::factory()->create(['name' => 'Pembaca']);
        $note = $this->createPublicNote($creator);
        $comment = $note->comments()->create([
            'user_id' => $reader->id,
            'content' => 'Pertanyaan pembaca',
        ]);

        $this->actingAs($creator)
            ->post(route('comments.store', $note), [
                'content' => 'Jawaban kreator',
                'parent_comment_id' => $comment->id,
                'redirect_to' => 'notes.show',
            ])
            ->assertRedirect(route('notes.show', $note))
            ->assertSessionHas('success', 'Balasan berhasil dikirim!');

        $reply = $note->comments()->where('parent_comment_id', $comment->id)->firstOrFail();
        $this->assertSame($creator->id, $reply->user_id);

        $this->get(route('notes.show', $note))
            ->assertOk()
            ->assertSee('Pertanyaan pembaca')
            ->assertSee('Jawaban kreator')
            ->assertSee('Kreator')
            ->assertSee('class="user-avatar note-comment-avatar', false);

        $this->get(route('notes.show', $note))
            ->assertSee('class="note-reply-button"', false)
            ->assertSee('data-note-reply="'.$reply->id.'"', false);

        $this->actingAs($reader)
            ->post(route('comments.store', $note), [
                'content' => 'Tanggapan untuk jawaban kreator',
                'parent_comment_id' => $reply->id,
            ])
            ->assertRedirect(route('public-notes.show', $note));

        $this->assertDatabaseHas('comments', [
            'parent_comment_id' => $reply->id,
            'content' => 'Tanggapan untuk jawaban kreator',
        ]);

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('Jawaban kreator')
            ->assertSee('Tanggapan untuk jawaban kreator');
    }

    public function test_reply_can_target_any_comment_on_the_same_note_but_not_other_notes(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $otherNote = $this->createPublicNote($creator);
        $comment = $note->comments()->create([
            'user_id' => $reader->id,
            'content' => 'Komentar utama',
        ]);
        $reply = $note->comments()->create([
            'user_id' => $creator->id,
            'parent_comment_id' => $comment->id,
            'content' => 'Balasan awal',
        ]);
        $otherNoteComment = $otherNote->comments()->create([
            'user_id' => $reader->id,
            'content' => 'Komentar catatan lain',
        ]);

        $this->actingAs($creator)
            ->post(route('comments.store', $note), [
                'content' => 'Balasan silang catatan',
                'parent_comment_id' => $otherNoteComment->id,
            ])
            ->assertNotFound();

        $this->post(route('comments.store', $note), [
            'content' => 'Balasan untuk balasan',
            'parent_comment_id' => $reply->id,
        ])->assertRedirect(route('public-notes.show', $note));

        $this->assertDatabaseCount('comments', 4);
        $this->assertDatabaseHas('comments', [
            'note_id' => $note->id,
            'parent_comment_id' => $reply->id,
            'content' => 'Balasan untuk balasan',
        ]);

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('Balasan untuk balasan')
            ->assertSee('data-comment-reply="'.$reply->id.'"', false);
    }

    public function test_users_cannot_edit_another_users_comment(): void
    {
        $creator = User::factory()->create();
        $commenter = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $comment = $note->comments()->create([
            'user_id' => $commenter->id,
            'content' => 'Komentar pemilik',
        ]);

        $this->actingAs($otherUser)
            ->put(route('comments.update', $comment), [
                'content' => 'Perubahan tidak sah',
            ])
            ->assertForbidden();

        $this->assertSame('Komentar pemilik', $comment->fresh()->content);
    }

    public function test_comment_updates_require_valid_content(): void
    {
        $creator = User::factory()->create();
        $commenter = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $comment = $note->comments()->create([
            'user_id' => $commenter->id,
            'content' => 'Komentar tetap',
        ]);

        $this->actingAs($commenter)
            ->put(route('comments.update', $comment), [
                'content' => '',
            ])
            ->assertSessionHasErrors('content');

        $this->assertSame('Komentar tetap', $comment->fresh()->content);
    }

    public function test_comment_owner_can_delete_their_comment(): void
    {
        $creator = User::factory()->create();
        $commenter = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $comment = $note->comments()->create([
            'user_id' => $commenter->id,
            'content' => 'Komentar yang akan dihapus',
        ]);

        $this->actingAs($commenter)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('public-notes.show', $note))
            ->assertSessionHas('success', 'Komentar berhasil dihapus!');

        $this->actingAs($commenter)
            ->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('Komentar berhasil dihapus!')
            ->assertSee('class="alert-success app-toast"', false)
            ->assertSee('data-toast', false);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    private function createPublicNote(User $creator): Note
    {
        return $creator->notes()->create([
            'title' => 'Catatan publik',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
    }
}
