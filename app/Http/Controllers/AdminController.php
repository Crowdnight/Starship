<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Note;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pendingSupportQuery = $this->pendingSupportConversations();
        $activities = collect();

        User::query()->latest('created_at')->limit(3)->get()->each(
            fn (User $user) => $activities->push([
                'label' => 'Pengguna baru: '.$user->name,
                'detail' => $user->email,
                'url' => route('admin.users.show', $user),
                'created_at' => $user->created_at,
            ])
        );

        Note::query()->where('is_public', true)->latest('created_at')->limit(3)->get()->each(
            fn (Note $note) => $activities->push([
                'label' => 'Catatan publik: '.$note->title,
                'detail' => 'Catatan baru dibagikan ke komunitas',
                'url' => route('public-notes.show', $note),
                'created_at' => $note->created_at,
            ])
        );

        Comment::query()
            ->with('note')
            ->whereHas('note', fn ($query) => $query->where('is_public', true))
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->each(fn (Comment $comment) => $activities->push([
                'label' => 'Komentar pada: '.$comment->note->title,
                'detail' => \Illuminate\Support\Str::limit($comment->content, 90),
                'url' => route('public-notes.show', $comment->note),
                'created_at' => $comment->created_at,
            ]));

        $latestActivities = $activities->sortByDesc('created_at')->take(6)->values();

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'publicNoteCount' => Note::where('is_public', true)->count(),
            'publicCommentCount' => $this->publicComments()->count(),
            'pendingSupportCount' => (clone $pendingSupportQuery)->count('support_conversations.id'),
            'latestActivities' => $latestActivities,
        ]);
    }

    public function statistics(Request $request)
    {
        $validated = $request->validate([
            'days' => ['sometimes', 'integer', Rule::in([7, 14, 30])],
        ]);
        $days = (int) ($validated['days'] ?? 14);
        $now = now();
        $start = $now->copy()->startOfDay()->subDays($days - 1);
        $driver = DB::connection()->getDriverName();
        $dateFunction = $driver === 'sqlite' ? 'strftime' : ($driver === 'pgsql' ? 'to_char' : 'DATE_FORMAT');
        $dateFormat = $driver === 'pgsql' ? 'YYYY-MM-DD' : '%Y-%m-%d';
        $bucketExpression = $driver === 'sqlite' ? 'strftime(?, created_at)' : "$dateFunction(created_at, ?)";

        $userCounts = DB::table('users')
            ->whereBetween('created_at', [$start, $now])
            ->selectRaw("$bucketExpression as bucket, COUNT(*) as total", [$dateFormat])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');
        $noteCounts = DB::table('notes')
            ->where('is_public', true)
            ->whereBetween('created_at', [$start, $now])
            ->selectRaw("$bucketExpression as bucket, COUNT(*) as total", [$dateFormat])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $chart = collect(range(0, $days - 1))->map(function (int $offset) use (
            $start,
            $userCounts,
            $noteCounts
        ): array {
            $date = CarbonImmutable::instance($start)->addDays($offset);
            $key = $date->format('Y-m-d');

            return [
                'label' => $date->locale('id')->translatedFormat('d M'),
                'users' => (int) ($userCounts[$key] ?? 0),
                'notes' => (int) ($noteCounts[$key] ?? 0),
            ];
        });

        $incomingMessages = SupportMessage::query()
            ->join('support_conversations', 'support_conversations.id', '=', 'support_messages.support_conversation_id')
            ->whereColumn('support_messages.sender_id', 'support_conversations.user_id');

        return view('admin.statistics', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'onlineUsers' => User::where('is_active', true)
                ->where('last_seen_at', '>=', $now->copy()->subMinutes(5))
                ->count(),
            'publicNoteCount' => Note::where('is_public', true)->count(),
            'publicCommentCount' => $this->publicComments()->count(),
            'incomingMessageCount' => (clone $incomingMessages)->count(),
            'days' => $days,
            'chart' => $chart,
            'userChartMax' => max(1, (int) $chart->max('users')),
            'noteChartMax' => max(1, (int) $chart->max('notes')),
        ]);
    }

    public function users(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($validated['search'] ?? '');
        $users = User::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        $adminCount = User::where('role', 'admin')->count();

        return view('admin.users', compact('users', 'adminCount', 'search'));
    }

    public function showUser(User $user)
    {
        return view('admin.users.show', [
            'user' => $user,
            'publicNoteCount' => $user->notes()->where('is_public', true)->count(),
            'publicCommentCount' => $user->comments()
                ->whereHas('note', fn ($query) => $query->where('is_public', true))
                ->count(),
        ]);
    }

    public function sendPasswordResetLink(User $user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Tautan reset kata sandi dikirim ke '.$user->email.'.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function updateRole(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 403, 'Role akun sendiri tidak dapat diubah.');

        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'admin'])],
        ]);

        if ($user->isAdmin() && $validated['role'] !== 'admin') {
            $this->ensureAnotherAdminExists($user);
        }

        $user->role = $validated['role'];
        $user->save();

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function toggleUser(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 403, 'Akun sendiri tidak dapat dinonaktifkan.');
        abort_if($user->isAdmin(), 403, 'Akun admin tidak dapat dinonaktifkan dari halaman ini.');

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active
            ? 'Akun pengguna berhasil diaktifkan.'
            : 'Akun pengguna berhasil dinonaktifkan.');
    }

    public function destroyUser(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 403, 'Akun sendiri tidak dapat dihapus dari halaman ini.');
        abort_if($user->isAdmin(), 403, 'Akun admin tidak dapat dihapus dari halaman ini.');

        $user->delete();

        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function content()
    {
        $notes = Note::with('user')->withCount('comments')
            ->where('is_public', true)
            ->latest()
            ->paginate(20, ['*'], 'notes_page');
        $comments = Comment::with(['user', 'note'])
            ->whereHas('note', fn ($query) => $query->where('is_public', true))
            ->latest()
            ->paginate(20, ['*'], 'comments_page');

        return view('admin.content', compact('notes', 'comments'));
    }

    public function publicNotes()
    {
        $notes = Note::query()
            ->with('user')
            ->withCount('comments')
            ->where('is_public', true)
            ->latest('created_at')
            ->paginate(20);

        return view('admin.public-notes', compact('notes'));
    }

    public function destroyNote(Note $note)
    {
        abort_unless($note->is_public, 404);
        $note->delete();

        return back()->with('success', 'Catatan publik berhasil dimoderasi dan dihapus.');
    }

    public function destroyComment(Comment $comment)
    {
        abort_unless($comment->note?->is_public, 404);
        $comment->delete();

        return back()->with('success', 'Komentar berhasil dimoderasi dan dihapus.');
    }

    private function ensureAnotherAdminExists(User $user): void
    {
        $anotherAdminExists = User::where('role', 'admin')
            ->whereKeyNot($user->id)
            ->exists();

        abort_unless($anotherAdminExists, 403, 'Tidak dapat menghapus role admin terakhir.');
    }

    private function publicComments()
    {
        return Comment::query()->whereHas(
            'note',
            fn ($query) => $query->where('is_public', true)
        );
    }

    private function pendingSupportConversations()
    {
        $latestMessages = SupportMessage::query()
            ->selectRaw('support_conversation_id, MAX(id) as latest_message_id')
            ->groupBy('support_conversation_id');

        return SupportConversation::query()
            ->joinSub($latestMessages, 'latest_messages', function (JoinClause $join): void {
                $join->on('support_conversations.id', '=', 'latest_messages.support_conversation_id');
            })
            ->join('support_messages as latest_message', 'latest_message.id', '=', 'latest_messages.latest_message_id')
            ->whereColumn('latest_message.sender_id', '=', 'support_conversations.user_id')
            ->select('support_conversations.*')
            ->with('user')
            ->latest('support_conversations.updated_at');
    }
}
