<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicNoteController extends Controller
{
    public function index()
    {
        $notes = Note::with('user')
            ->withCount([
                'publicViews',
                'comments',
                'reactions as likes_count' => fn ($query) => $query->where('reaction', 'like'),
                'reactions as dislikes_count' => fn ($query) => $query->where('reaction', 'dislike'),
            ])
            ->where('is_public', true)
            ->latest()
            ->paginate(12);

        return view('public-notes.index', compact('notes'));
    }

    public function show(Request $request, Note $note)
    {
        abort_unless($note->is_public, 404);

        if ($request->user()) {
            $viewerKey = 'user:'.$request->user()->getKey();
        } else {
            $viewerId = $request->session()->get('public_note_viewer_id');

            if (! is_string($viewerId)) {
                $viewerId = (string) Str::uuid();
                $request->session()->put('public_note_viewer_id', $viewerId);
            }

            $viewerKey = 'guest:'.$viewerId;
        }

        $note->publicViews()->firstOrCreate(['viewer_key' => $viewerKey]);
        $note->load([
            'user',
            'comments.user',
        ])->loadCount('publicViews');
        $note->loadCount([
            'reactions as likes_count' => fn ($query) => $query->where('reaction', 'like'),
            'reactions as dislikes_count' => fn ($query) => $query->where('reaction', 'dislike'),
        ]);
        $userReaction = $request->user()
            ? $note->reactions()->where('user_id', $request->user()->id)->value('reaction')
            : null;
        $note->setRelation('rootComments', $note->buildCommentTree());

        return view('public-notes.show', compact('note', 'userReaction'));
    }
}