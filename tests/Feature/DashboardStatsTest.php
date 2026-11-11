<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\PublicNoteView;
use App\Models\NoteReaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_total_comments_and_views_for_the_users_notes(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $note = $this->createNote($creator, 'Catatan publik', true);
        $secondNote = $this->createNote($creator, 'Catatan kedua', true);
        $otherUserNote = $this->createNote($reader, 'Catatan orang lain', true);

        $note->comments()->create([
            'user_id' => $reader->id,
            'content' => 'Komentar pertama',
        ]);
        $note->comments()->create([
            'user_id' => $creator->id,
            'parent_comment_id' => $note->comments()->first()->id,
            'content' => 'Balasan kreator',
        ]);
        $secondNote->comments()->create([
            'user_id' => $reader->id,
            'content' => 'Komentar catatan kedua',
        ]);

        foreach ([$note, $note, $secondNote, $otherUserNote] as $viewedNote) {
            PublicNoteView::query()->create([
                'note_id' => $viewedNote->id,
                'viewer_key' => 'viewer:'.$viewedNote->id.':'.PublicNoteView::query()
                    ->where('note_id', $viewedNote->id)
                    ->count(),
            ]);
        }
        NoteReaction::query()->create([
            'note_id' => $note->id,
            'user_id' => $reader->id,
            'reaction' => 'like',
        ]);
        NoteReaction::query()->create([
            'note_id' => $secondNote->id,
            'user_id' => User::factory()->create()->id,
            'reaction' => 'dislike',
        ]);

        $this->actingAs($creator)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('Total Komentar')
            ->assertSee('Total Views')
            ->assertSee('<h2 data-total-comments>3</h2>', false)
            ->assertSee('<h2 data-total-views>3</h2>', false)
            ->assertSee('<h2 data-total-likes>1</h2>', false)
            ->assertSee('<h2 data-total-dislikes>1</h2>', false);
    }

    private function createNote(User $owner, string $title, bool $isPublic): Note
    {
        return $owner->notes()->create([
            'title' => $title,
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => $isPublic,
        ]);
    }
}
