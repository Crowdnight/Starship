<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, string $notification)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($notification);
        $note = $user->notes()->findOrFail($notification->data['note_id'] ?? 0);

        $notification->markAsRead();

        return redirect()->route('notes.show', $note);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function dismiss(Request $request, string $notification)
    {
        $request->user()
            ->notifications()
            ->findOrFail($notification)
            ->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notifikasi berhasil dihapus.');
    }
}
