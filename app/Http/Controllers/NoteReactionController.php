<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Notifications\NoteActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NoteReactionController extends Controller
{
    public function store(Request $request, Note $note)
    {
        abort_unless($note->is_public, 404);
        abort_unless(
            (int) $note->user_id !== (int) $request->user()->id,
            403,
            'Kreator tidak dapat memberi reaksi pada catatannya sendiri.'
        );

        $validated = $request->validate([
            'reaction' => ['required', Rule::in(['like', 'dislike'])],
        ]);

        $storedReaction = DB::transaction(function () use ($request, $note, $validated): ?string {
            $reaction = $note->reactions()
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if ($reaction?->reaction === $validated['reaction']) {
                $reaction->delete();

                return null;
            }

            $note->reactions()->updateOrCreate(
                ['user_id' => $request->user()->id],
                ['reaction' => $validated['reaction']]
            );

            return $validated['reaction'];
        });

        if ($storedReaction !== null) {
            $note->user?->notify(new NoteActivityNotification(
                $storedReaction,
                (int) $request->user()->id,
                $request->user()->name,
                (int) $note->id,
                $note->title,
            ));
        }

        return redirect()
            ->route('public-notes.show', $note)
            ->with('success', 'Reaksi catatan berhasil diperbarui.');
    }
}
