<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Note;
use App\Models\SupportConversation;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminAndSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_message_admin_and_admin_can_reply(): void
    {
        $user = User::factory()->create();
        $admin = $this->createAdmin();

        $this->actingAs($user)
            ->post(route('support.store'), [
                'category' => 'question',
                'subject' => 'Butuh bantuan',
                'body' => 'Saya perlu bantuan menggunakan aplikasi.',
            ])
            ->assertRedirect();

        $conversation = SupportConversation::firstOrFail();
        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => 'Saya perlu bantuan menggunakan aplikasi.',
        ]);
        $this->assertSame('question', $conversation->category);

        $this->actingAs($user)
            ->get(route('support.index'))
            ->assertOk()
            ->assertSee('Riwayat Feedback')
            ->assertSee('Pertanyaan')
            ->assertSee('Butuh bantuan');

        $this->get(route('support.create'))
            ->assertOk()
            ->assertSee('Kirim Feedback')
            ->assertSee('Laporan Bug')
            ->assertSee(route('support.index'));

        $this->actingAs($admin)
            ->get(route('admin.support.index'))
            ->assertOk()
            ->assertSee('Pertanyaan')
            ->assertSee($user->email)
            ->assertSee('Menunggu balasan')
            ->assertSee('Saya perlu bantuan menggunakan aplikasi.');

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)
            ->get(route('support.show', $conversation))
            ->assertNotFound();
        $this->post(route('support.reply', $conversation), ['body' => 'Balasan tanpa izin'])
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.support.show', $conversation))
            ->assertOk()
            ->assertSee($user->email);

        $this->post(route('admin.support.reply', $conversation), [
            'body' => 'Tentu, kami siap membantu.',
        ])->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.support.index'))
            ->assertOk()
            ->assertSee('Sudah dibalas');

        $this->actingAs($user)
            ->get(route('support.show', $conversation))
            ->assertOk()
            ->assertSee('Tentu, kami siap membantu.');
    }

    public function test_feedback_requires_a_message_and_uses_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->from(route('support.create'))
            ->post(route('support.store'), [
                'category' => 'bug',
                'subject' => 'Tidak bisa menyimpan',
                'body' => '   ',
                'user_id' => $otherUser->id,
            ])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('support_conversations', 0);

        $this->post(route('support.store'), [
            'category' => 'bug',
            'subject' => 'Tidak bisa menyimpan',
            'body' => 'Tombol simpan tidak merespons.',
            'user_id' => $otherUser->id,
        ])->assertRedirect();

        $conversation = SupportConversation::firstOrFail();
        $this->assertSame($user->id, $conversation->user_id);
        $this->assertSame('bug', $conversation->category);

        $this->actingAs($otherUser)
            ->get(route('support.show', $conversation))
            ->assertNotFound();
    }

    public function test_legacy_conversations_without_a_category_remain_openable(): void
    {
        $user = User::factory()->create();
        $admin = $this->createAdmin();
        $conversation = SupportConversation::create([
            'user_id' => $user->id,
            'subject' => 'Percakapan lama',
        ]);
        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => 'Pesan sebelum kategori tersedia.',
        ]);

        $this->assertSame('other', $conversation->fresh()->category);

        $this->actingAs($user)
            ->get(route('support.show', $conversation))
            ->assertOk()
            ->assertSee('Lainnya')
            ->assertSee('Pesan sebelum kategori tersedia.');

        $this->actingAs($admin)
            ->post(route('admin.support.reply', $conversation), [
                'body' => 'Balasan untuk percakapan lama.',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('support.show', $conversation))
            ->assertOk()
            ->assertSee('Balasan untuk percakapan lama.');
    }

    public function test_non_admin_cannot_access_admin_pages_and_admin_can_manage_users(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();
        $managedUser = User::factory()->create();
        $admin = $this->createAdmin();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
        $this->get(route('admin.statistics'))->assertForbidden();
        $this->get(route('admin.public-notes.index'))->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.statistics'))->assertOk();
        $this->get(route('admin.users.show', $target))->assertOk();
        $this->get(route('admin.content.index'))->assertOk();
        $this->get(route('admin.public-notes.index'))->assertOk();
        $this->get(route('admin.support.index'))->assertOk();

        $this->put(route('admin.users.role', $target), ['role' => 'admin'])
            ->assertRedirect();
        $this->assertTrue($target->fresh()->isAdmin());

        $this->patch(route('admin.users.status', $managedUser))->assertRedirect();
        $this->assertFalse($managedUser->fresh()->is_active);

        $this->actingAs($managedUser->fresh())
            ->get(route('support.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_moderate_public_notes_and_comments(): void
    {
        $admin = $this->createAdmin();
        $owner = User::factory()->create();
        $note = Note::create([
            'user_id' => $owner->id,
            'title' => 'Catatan publik',
            'content' => 'Isi catatan publik.',
            'category' => 'Umum',
            'is_public' => true,
        ]);
        $comment = Comment::create([
            'note_id' => $note->id,
            'user_id' => $owner->id,
            'content' => 'Komentar tidak pantas.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.content.index'))
            ->assertOk()
            ->assertSee('Catatan publik')
            ->assertSee('Komentar tidak pantas.');

        $this->delete(route('admin.comments.destroy', $comment))->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);

        $this->delete(route('admin.notes.destroy', $note))->assertRedirect();
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_admin_dashboard_shows_public_activity_and_pending_support_only(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => 'Anggota Starship']);
        $publicNote = Note::create([
            'user_id' => $user->id,
            'title' => 'Catatan untuk komunitas',
            'content' => 'Isi yang dibagikan.',
            'category' => 'Umum',
            'is_public' => true,
        ]);
        $privateNote = Note::create([
            'user_id' => $user->id,
            'title' => 'Catatan pribadi rahasia',
            'content' => 'Konten ini tidak boleh muncul di dashboard admin.',
            'category' => 'Pribadi',
            'is_public' => false,
        ]);
        $publicNote->comments()->create([
            'user_id' => $user->id,
            'content' => 'Komentar komunitas terbaru.',
        ]);
        $privateNote->comments()->create([
            'user_id' => $user->id,
            'content' => 'Komentar pribadi yang tidak boleh tampil.',
        ]);

        $pendingConversation = SupportConversation::create([
            'user_id' => $user->id,
            'subject' => 'Masukan menunggu admin',
        ]);
        $pendingConversation->messages()->create([
            'sender_id' => $user->id,
            'body' => 'Saya punya masukan.',
        ]);
        $answeredConversation = SupportConversation::create([
            'user_id' => $user->id,
            'subject' => 'Masukan sudah dibalas',
        ]);
        $answeredConversation->messages()->create([
            'sender_id' => $user->id,
            'body' => 'Mohon bantuan.',
        ]);
        $answeredConversation->messages()->create([
            'sender_id' => $admin->id,
            'body' => 'Kami akan menindaklanjuti.',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Halo,')
            ->assertSee('Catatan publik: Catatan untuk komunitas')
            ->assertSee('Komentar pada: Catatan untuk komunitas')
            ->assertSee('Pesan Belum Dibalas')
            ->assertDontSee('Catatan pribadi rahasia')
            ->assertDontSee('Konten ini tidak boleh muncul di dashboard admin.')
            ->assertDontSee('Komentar pribadi yang tidak boleh tampil.')
            ->assertDontSee('Masukan menunggu admin')
            ->assertDontSee('Masukan sudah dibalas')
            ->assertDontSee('Aktivitas komunitas');

        $this->assertNotNull($admin->fresh()->last_seen_at);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'last_seen_at' => null]);
    }

    public function test_admin_statistics_use_database_data_and_allow_supported_periods(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $publicNote = Note::create([
            'user_id' => $user->id,
            'title' => 'Catatan statistik publik',
            'content' => 'Isi publik.',
            'category' => 'Umum',
            'is_public' => true,
        ]);
        Note::create([
            'user_id' => $user->id,
            'title' => 'Catatan privat',
            'content' => 'Tidak dihitung sebagai publik.',
            'category' => 'Pribadi',
            'is_public' => false,
        ]);
        $publicNote->comments()->create([
            'user_id' => $user->id,
            'content' => 'Komentar publik statistik.',
        ]);
        $conversation = SupportConversation::create([
            'user_id' => $user->id,
            'subject' => 'Pesan statistik',
        ]);
        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => 'Pesan masuk yang dihitung.',
        ]);
        $conversation->messages()->create([
            'sender_id' => $admin->id,
            'body' => 'Balasan admin tidak dihitung sebagai pesan masuk.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.statistics', ['days' => 7]));

        $response->assertOk()
            ->assertSee('Statistik komunitas')
            ->assertSee('Total Pesan Masuk');
        $this->assertSame(1, $response->viewData('publicNoteCount'));
        $this->assertSame(1, $response->viewData('publicCommentCount'));
        $this->assertSame(1, $response->viewData('incomingMessageCount'));
        $this->assertSame(7, $response->viewData('chart')->count());
        $this->assertSame(2, $response->viewData('chart')->sum('users'));
        $this->assertSame(1, $response->viewData('chart')->sum('notes'));

        foreach ([14, 30] as $days) {
            $this->get(route('admin.statistics', ['days' => $days]))
                ->assertOk();
        }
    }

    public function test_admin_can_search_users_view_details_and_browse_public_notes(): void
    {
        $admin = $this->createAdmin();
        $target = User::factory()->create([
            'name' => 'Fahri Target',
            'email' => 'fahri.target@example.com',
        ]);
        $otherUser = User::factory()->create([
            'name' => 'Other Account',
            'email' => 'other.account@example.com',
        ]);
        $publicNote = Note::create([
            'user_id' => $target->id,
            'title' => 'Catatan publik terdaftar',
            'content' => 'Konten publik.',
            'category' => 'Harian',
            'is_public' => true,
        ]);
        Note::create([
            'user_id' => $target->id,
            'title' => 'Catatan privat tidak ditampilkan',
            'content' => 'Konten privat.',
            'category' => 'Pribadi',
            'is_public' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'fahri.target']))
            ->assertOk()
            ->assertSee($target->email)
            ->assertDontSee($otherUser->email);

        $this->get(route('admin.users.show', $target))
            ->assertOk()
            ->assertSee('Detail Pengguna')
            ->assertSee($target->email)
            ->assertDontSee('Catatan privat tidak ditampilkan');

        $this->get(route('admin.public-notes.index'))
            ->assertOk()
            ->assertSee('Catatan publik terdaftar')
            ->assertDontSee('Catatan privat tidak ditampilkan');

        $this->assertDatabaseHas('notes', ['id' => $publicNote->id, 'is_public' => true]);
    }

    public function test_admin_can_send_a_password_reset_link_to_a_user(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.password-reset', $user))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_admin_cannot_demote_the_last_admin(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->put(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertForbidden();
    }

    public function test_artisan_command_can_promote_an_existing_user_to_admin(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0, Artisan::call('app:make-admin', ['email' => $user->email]));
        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_admin_seeder_creates_a_login_ready_admin_without_storing_plaintext_password(): void
    {
        config([
            'admin.email' => 'abyanradin@example.com',
            'admin.password' => 'dummy-pass-123',
        ]);

        Artisan::call('db:seed', ['--class' => AdminUserSeeder::class]);

        $admin = User::where('email', 'abyanradin@example.com')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('dummy-pass-123', $admin->password));
        $this->assertNotSame('dummy-pass-123', $admin->password);

        $this->post(route('login.post'), [
            'email' => 'abyanradin@example.com',
            'password' => 'dummy-pass-123',
        ])->assertRedirect(route('notes.index'));

        $this->get(route('admin.users.index'))->assertOk();
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        return $admin;
    }
}
