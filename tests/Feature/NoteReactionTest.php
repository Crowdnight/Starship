<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteReaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_other_users_can_like_or_dislike_public_notes_and_toggle_their_reaction(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $note = $this->createNote($creator);

        $this->actingAs($reader)
            ->post(route('note-reactions.store', $note), ['reaction' => 'like'])
            ->assertRedirect(route('public-notes.show', $note));

        $this->assertDatabaseHas('note_reactions', [
            'note_id' => $note->id,
            'user_id' => $reader->id,
            'reaction' => 'like',
        ]);

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('aria-label="Suka catatan, 1 suka"', false)
            ->assertSee('aria-pressed="true"', false);

        $this->post(route('note-reactions.store', $note), ['reaction' => 'dislike'])
            ->assertRedirect(route('public-notes.show', $note));

        $this->assertDatabaseHas('note_reactions', [
            'note_id' => $note->id,
            'user_id' => $reader->id,
            'reaction' => 'dislike',
        ]);
        $this->assertDatabaseCount('note_reactions', 1);

        $this->post(route('note-reactions.store', $note), ['reaction' => 'dislike']);
        $this->assertDatabaseMissing('note_reactions', [
            'note_id' => $note->id,
            'user_id' => $reader->id,
        ]);
    }

    public function test_only_authenticated_non_creators_can_react_to_public_notes(): void
    {
        $creator = User::factory()->create();
        $note = $this->createNote($creator);

        $this->post(route('note-reactions.store', $note), ['reaction' => 'like'])
            ->assertRedirect(route('login'));

        $this->actingAs($creator)
            ->post(route('note-reactions.store', $note), ['reaction' => 'like'])
            ->assertForbidden();

        $note->update(['is_public' => false]);
        $this->actingAs(User::factory()->create())
            ->post(route('note-reactions.store', $note), ['reaction' => 'like'])
            ->assertNotFound();

        $this->assertDatabaseCount('note_reactions', 0);
    }

    public function test_dashboard_totals_include_only_reactions_on_the_creators_notes(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $ownNote = $this->createNote($creator);
        $otherNote = $this->createNote($reader);

        NoteReaction::query()->create([
            'note_id' => $ownNote->id,
            'user_id' => $reader->id,
            'reaction' => 'like',
        ]);
        NoteReaction::query()->create([
            'note_id' => $ownNote->id,
            'user_id' => User::factory()->create()->id,
            'reaction' => 'dislike',
        ]);
        NoteReaction::query()->create([
            'note_id' => $otherNote->id,
            'user_id' => $creator->id,
            'reaction' => 'like',
        ]);

        $this->actingAs($creator)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('<h2 data-total-likes>1</h2>', false)
            ->assertSee('<h2 data-total-dislikes>1</h2>', false);
    }

    public function test_public_note_list_shows_like_and_dislike_counts(): void
    {
        $creator = User::factory()->create();
        $note = $this->createNote($creator);
        NoteReaction::query()->create([
            'note_id' => $note->id,
            'user_id' => User::factory()->create()->id,
            'reaction' => 'like',
        ]);

        $this->get(route('public-notes.index'))
            ->assertOk()
            ->assertSee('1 suka')
            ->assertSee('0 tidak suka');
    }

    private function createNote(User $creator): Note
    {
        return $creator->notes()->create([
            'title' => 'Catatan publik',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
    }
}
