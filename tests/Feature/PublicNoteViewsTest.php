<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNoteViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_views_are_counted_once_per_note_and_shown_in_public_pages(): void
    {
        $note = $this->createPublicNote();

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('1 pembaca');

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('1 pembaca');

        $this->assertDatabaseCount('public_note_views', 1);

        $this->get(route('public-notes.index'))
            ->assertOk()
            ->assertSee('1 views')
            ->assertSee('0 komentar');
    }

    public function test_each_authenticated_user_is_counted_once_per_note(): void
    {
        $note = $this->createPublicNote();
        $reader = User::factory()->create();

        $this->actingAs($reader)
            ->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('1 pembaca');

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('1 pembaca');

        $this->assertDatabaseCount('public_note_views', 1);
    }

    public function test_private_notes_do_not_record_public_views(): void
    {
        $note = $this->createPublicNote();
        $note->update(['is_public' => false]);

        $this->get(route('public-notes.show', $note))
            ->assertNotFound();

        $this->assertDatabaseCount('public_note_views', 0);
    }

    public function test_note_cards_show_view_and_comment_counts_on_both_indexes(): void
    {
        $creator = User::factory()->create();
        $note = $creator->notes()->create([
            'title' => 'Catatan publik',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
        $note->comments()->create([
            'user_id' => User::factory()->create()->id,
            'content' => 'Komentar pembaca',
        ]);

        $this->get(route('public-notes.show', $note))
            ->assertOk();

        $this->get(route('public-notes.index'))
            ->assertOk()
            ->assertSee('1 views')
            ->assertSee('1 komentar')
            ->assertSee('data-note-stats', false);

        $this->actingAs($creator)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('1 views')
            ->assertSee('1 komentar')
            ->assertSee('data-note-stats', false);
    }

    private function createPublicNote(): Note
    {
        return User::factory()->create()->notes()->create([
            'title' => 'Catatan publik',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
    }
}
