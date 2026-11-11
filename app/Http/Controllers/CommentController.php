<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Note;
use App\Notifications\NoteActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request, Note $note)
    {
        abort_unless($note->is_public, 404);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'parent_comment_id' => ['nullable', 'integer', 'exists:comments,id'],
            'redirect_to' => ['nullable', Rule::in(['notes.show', 'public-notes.show'])],
        ]);

        $parentComment = null;
        if (! empty($validated['parent_comment_id'])) {
            $parentComment = Comment::findOrFail($validated['parent_comment_id']);
            abort_unless((int) $parentComment->note_id === (int) $note->id, 404);
        }

        if (($validated['redirect_to'] ?? null) === 'notes.show') {
            $this->authorizeNoteOwner($request, $note);
        }

        $comment = $note->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_comment_id' => $parentComment?->id,
        ]);

        if ((int) $note->user_id !== (int) $request->user()->id) {
            $note->user?->notify(new NoteActivityNotification(
                'comment',
                (int) $request->user()->id,
                $request->user()->name,
                (int) $note->id,
                $note->title,
                $comment->content,
            ));
        }

        return $this->redirectAfterAction(
            $request,
            $note,
            $validated['redirect_to'] ?? null,
            $parentComment ? 'Balasan berhasil dikirim!' : 'Komentar berhasil dikirim!'
        );
    }

    public function update(Request $request, Comment $comment)
    {
        $note = $comment->note;
        $this->authorizeCommentAccess($request, $note);
        abort_unless(
            (int) $comment->user_id === (int) $request->user()->id,
            403,
            'Kamu tidak memiliki izin mengubah komentar ini.'
        );

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'redirect_to' => ['nullable', Rule::in(['notes.show', 'public-notes.show'])],
        ]);

        $comment->content = $validated['content'];
        $comment->edited_at = now();
        $comment->save();

        return $this->redirectAfterAction(
            $request,
            $note,
            $validated['redirect_to'] ?? null,
            'Komentar berhasil diperbarui.'
        );
    }

    public function destroy(Request $request, Comment $comment)
    {
        $note = $comment->note;
        $this->authorizeCommentAccess($request, $note);
        $validated = $request->validate([
            'redirect_to' => ['nullable', Rule::in(['notes.show', 'public-notes.show'])],
        ]);
        abort_unless(
            (int) $comment->user_id === (int) $request->user()->id,
            403,
            'Kamu tidak memiliki izin menghapus komentar ini.'
        );

        $comment->delete();

        return $this->redirectAfterAction(
            $request,
            $note,
            $validated['redirect_to'] ?? null,
            'Komentar berhasil dihapus!'
        );
    }

    private function authorizeCommentAccess(Request $request, Note $note): void
    {
        abort_unless(
            $note->is_public || (int) $note->user_id === (int) $request->user()->id,
            404
        );
    }

    private function authorizeNoteOwner(Request $request, Note $note): void
    {
        abort_unless(
            (int) $note->user_id === (int) $request->user()->id,
            403,
            'Kamu tidak memiliki akses ke catatan ini.'
        );
    }

    private function redirectAfterAction(
        Request $request,
        Note $note,
        ?string $destination,
        string $message
    ) {
        if ($destination === 'notes.show') {
            $this->authorizeNoteOwner($request, $note);

            return redirect()
                ->route('notes.show', $note)
                ->with('success', $message);
        }

        return redirect()
            ->route('public-notes.show', $note)
            ->with('success', $message);
    }
}
