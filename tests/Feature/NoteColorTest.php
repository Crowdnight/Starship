<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteColorTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_note_color_is_saved_and_used_in_private_and_public_views(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('notes.store'), [
                'title' => 'Catatan berwarna',
                'content' => 'Isi catatan',
                'category' => 'Pribadi',
                'note_color' => '#123abc',
                'is_public' => '1',
            ])
            ->assertRedirect(route('notes.index'));

        $note = Note::query()->where('title', 'Catatan berwarna')->firstOrFail();

        $this->assertSame('#123ABC', $note->note_color);
        $this->assertSame('#ffffff', $note->foreground_color);

        $this->actingAs($user)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('--note-background: #123ABC');

        $this->actingAs($user)
            ->get(route('notes.show', $note))
            ->assertOk()
            ->assertSee('--note-background: #123ABC');

        $this->get(route('public-notes.index'))
            ->assertOk()
            ->assertSee('--note-background: #123ABC')
            ->assertSee('public-feed-heading', false)
            ->assertSee('Catatan terbaru')
            ->assertSee('Baca catatan');

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('--note-background: #123ABC')
            ->assertSee('public-note-detail', false)
            ->assertSee('note-content', false)
            ->assertSee('Diskusi')
            ->assertSee('Kreator catatan')
            ->assertDontSee('class="edited-indicator">Edited</span>', false);
    }

    public function test_invalid_note_color_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('notes.store'), [
                'title' => 'Catatan tanpa warna valid',
                'content' => 'Isi catatan',
                'category' => 'Pribadi',
                'note_color' => 'red; background-image: url(example)',
            ])
            ->assertSessionHasErrors('note_color');

        $this->assertDatabaseCount('notes', 0);
    }

    public function test_color_picker_is_available_when_creating_and_editing_notes(): void
    {
        $user = User::factory()->create();
        $note = $user->notes()->create([
            'title' => 'Catatan untuk diedit',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'note_color' => '#112233',
            'is_public' => true,
        ]);

        $this->actingAs($user)
            ->get(route('notes.create'))
            ->assertOk()
            ->assertSee('name="note_color"', false)
            ->assertSee('data-color="#292B27"', false);

        $this->get(route('notes.edit', $note))
            ->assertOk()
            ->assertSee('value="#112233"', false)
            ->assertSee('value="Catatan untuk diedit"', false)
            ->assertSee('Isi catatan</textarea>', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('name="is_public"', false)
            ->assertSee('data-note-preview', false)
            ->assertSee('data-preview-title', false);

        $this->put(route('notes.update', $note), [
            'title' => $note->title,
            'content' => $note->content,
            'category' => $note->category,
            'note_color' => '#AABBCC',
            'is_public' => '1',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'note_color' => '#AABBCC',
        ]);
        $this->assertNotNull($note->fresh()->edited_at);

        $this->actingAs($user)
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee('class="edited-indicator">Edited</span>', false);

        $this->get(route('notes.show', $note))
            ->assertOk()
            ->assertSee('class="edited-indicator">Edited</span>', false);

        $this->get(route('public-notes.index'))
            ->assertOk()
            ->assertSee('class="edited-indicator">Edited</span>', false);

        $this->get(route('public-notes.show', $note))
            ->assertOk()
            ->assertSee('class="edited-indicator">Edited</span>', false);
    }

    public function test_create_note_form_includes_live_preview_and_clear_visibility_options(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('notes.create'))
            ->assertOk()
            ->assertSee('Buat Catatan Baru')
            ->assertSee('data-note-preview', false)
            ->assertSee('data-preview-title', false)
            ->assertSee('data-preview-content', false)
            ->assertSee('Bagikan ke publik')
            ->assertSee('name="is_public"', false)
            ->assertSee('Simpan Catatan');
    }
}
