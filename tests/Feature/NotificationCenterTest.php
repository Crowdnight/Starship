<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_is_notified_about_comments_and_can_open_the_note_from_notifications(): void
    {
        $creator = User::factory()->create(['name' => 'Kreator Note']);
        $reader = User::factory()->create(['name' => 'Pembaca Aktif']);
        $note = $this->createPublicNote($creator);

        $this->actingAs($reader)
            ->post(route('comments.store', $note), [
                'content' => 'Catatan ini sangat membantu',
            ])
            ->assertRedirect(route('public-notes.show', $note));

        $notification = $creator->notifications()->firstOrFail();
        $this->assertSame('comment', $notification->data['activity']);
        $this->assertSame('Pembaca Aktif', $notification->data['actor_name']);
        $this->assertSame('Catatan Publik', $notification->data['note_title']);
        $this->assertSame('Catatan ini sangat membantu', $notification->data['comment_content']);
        $this->assertNull($notification->read_at);

        $response = $this->actingAs($creator)->get(route('notifications.index'));
        $response
            ->assertOk()
            ->assertSee('href="'.route('notifications.index').'"', false)
            ->assertSee('Pembaca Aktif')
            ->assertSee('berkomentar pada catatanmu')
            ->assertSee('Catatan ini sangat membantu')
            ->assertSee('Catatan Publik')
            ->assertSee('topbar-notification-button', false)
            ->assertSee('topbar-notification-count', false)
            ->assertSee('<svg viewBox="0 0 24 24" aria-hidden="true">', false);

        $topbarStart = strpos($response->getContent(), 'class="topbar-actions"');
        $topbarEnd = strpos($response->getContent(), '</header>', $topbarStart);
        $topbarActions = substr($response->getContent(), $topbarStart, $topbarEnd - $topbarStart);

        $this->assertLessThan(
            strpos($topbarActions, 'class="topbar-notification-button'),
            strpos($topbarActions, 'class="theme-toggle')
        );

        $this->post(route('notifications.open', $notification->id))
            ->assertRedirect(route('notes.show', $note));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_button_is_not_shown_to_guests(): void
    {
        $this->get(route('public-notes.index'))
            ->assertOk()
            ->assertDontSee('topbar-notification-button', false);
    }

    public function test_creator_is_notified_about_like_and_dislike_actions(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create(['name' => 'Pemberi Reaksi']);
        $note = $this->createPublicNote($creator);

        $this->actingAs($reader)
            ->post(route('note-reactions.store', $note), ['reaction' => 'like'])
            ->assertRedirect(route('public-notes.show', $note));

        $this->post(route('note-reactions.store', $note), ['reaction' => 'dislike'])
            ->assertRedirect(route('public-notes.show', $note));

        $notifications = $creator->notifications()->oldest()->get();
        $this->assertSame(['like', 'dislike'], $notifications->pluck('data.activity')->all());
        $this->assertSame(['Pemberi Reaksi', 'Pemberi Reaksi'], $notifications->pluck('data.actor_name')->all());

        $this->post(route('note-reactions.store', $note), ['reaction' => 'dislike']);
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_creators_do_not_receive_notifications_for_their_own_comments(): void
    {
        $creator = User::factory()->create();
        $note = $this->createPublicNote($creator);

        $this->actingAs($creator)
            ->post(route('comments.store', $note), ['content' => 'Catatan saya sendiri'])
            ->assertRedirect(route('public-notes.show', $note));

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_users_cannot_open_another_users_notification(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = $this->createPublicNote($creator);

        $this->actingAs($reader)
            ->post(route('comments.store', $note), ['content' => 'Komentar'])
            ->assertRedirect(route('public-notes.show', $note));

        $notificationId = $creator->notifications()->value('id');
        $this->actingAs($otherUser)
            ->post(route('notifications.open', $notificationId))
            ->assertNotFound();
    }

    public function test_mark_all_read_clears_the_unread_notification_count(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $creator->notify(new \App\Notifications\NoteActivityNotification(
            'like',
            $reader->id,
            $reader->name,
            $note->id,
            $note->title,
        ));

        $this->actingAs($creator)
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'));

        $this->assertSame(0, $creator->unreadNotifications()->count());
    }

    public function test_user_can_dismiss_their_notification(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $creator->notify(new \App\Notifications\NoteActivityNotification(
            'like',
            $reader->id,
            $reader->name,
            $note->id,
            $note->title,
        ));
        $notification = $creator->notifications()->firstOrFail();

        $this->actingAs($creator)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Dismiss notifikasi', false)
            ->assertSee(route('notifications.dismiss', $notification->id), false);

        $this->delete(route('notifications.dismiss', $notification->id))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Notifikasi berhasil dihapus.');

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Belum ada notifikasi');
    }

    public function test_user_cannot_dismiss_another_users_notification(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = $this->createPublicNote($creator);
        $creator->notify(new \App\Notifications\NoteActivityNotification(
            'comment',
            $reader->id,
            $reader->name,
            $note->id,
            $note->title,
            'Isi komentar',
        ));
        $notification = $creator->notifications()->firstOrFail();

        $this->actingAs($otherUser)
            ->delete(route('notifications.dismiss', $notification->id))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    private function createPublicNote(User $creator): Note
    {
        return $creator->notes()->create([
            'title' => 'Catatan Publik',
            'content' => 'Isi catatan',
            'category' => 'Pribadi',
            'is_public' => true,
        ]);
    }
}
