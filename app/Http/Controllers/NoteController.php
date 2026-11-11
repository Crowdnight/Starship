<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Comment;
use App\Models\PublicNoteView;
use App\Models\NoteReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $search = $request->query('search');

        $query = $user->notes();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $notes = $query
            ->withCount(['publicViews', 'comments'])
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $totalNotes = $user->notes()->count();
        $noteIds = $user->notes()->select('id');
        $totalComments = Comment::query()
            ->whereIn('note_id', $noteIds)
            ->count();
        $totalViews = PublicNoteView::query()
            ->whereIn('note_id', $noteIds)
            ->count();
        $totalLikes = NoteReaction::query()
            ->whereIn('note_id', $noteIds)
            ->where('reaction', 'like')
            ->count();
        $totalDislikes = NoteReaction::query()
            ->whereIn('note_id', $noteIds)
            ->where('reaction', 'dislike')
            ->count();

        $categories = $user->notes()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return view('notes.index', compact(
            'notes',
            'search',
            'totalNotes',
            'totalComments',
            'totalViews',
            'totalLikes',
            'totalDislikes',
            'categories'
        ));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
    'title' => 'required|string|max:255',
    'content' => 'required|string',
    'category' => 'required|string|max:100',
    'note_color' => ['required', 'regex:/\A#[0-9a-fA-F]{6}\z/'],
]);

$validated['note_color'] = strtoupper($validated['note_color']);
$validated['is_public'] = $request->boolean('is_public');

$request->user()->notes()->create($validated);

        return redirect()
            ->route('notes.index')
            ->with('success', 'Catatan berhasil ditambahkan!');
    }

    public function show(Request $request, Note $note)
    {
        $this->authorizeOwner($request, $note);

        $note->load('comments.user');
        $note->setRelation('rootComments', $note->buildCommentTree());

        return view('notes.show', compact('note'));
    }

    public function edit(Request $request, Note $note)
    {
        $this->authorizeOwner($request, $note);

        return view('notes.edit', compact('note'));
    }

    public function update(Request $request, Note $note)
    {
        $this->authorizeOwner($request, $note);

        $validated = $request->validate([
    'title' => 'required|string|max:255',
    'content' => 'required|string',
    'category' => 'required|string|max:100',
    'note_color' => ['required', 'regex:/\A#[0-9a-fA-F]{6}\z/'],
]);

$validated['note_color'] = strtoupper($validated['note_color']);
$validated['is_public'] = $request->boolean('is_public');

$note->fill($validated);
$note->edited_at = now();
$note->save();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Catatan berhasil diperbarui!');
    }

    public function destroy(Request $request, Note $note)
    {
        $this->authorizeOwner($request, $note);

        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Catatan berhasil dihapus!');
    }

    private function authorizeOwner(Request $request, Note $note): void
    {
        abort_unless(
            $note->user_id === $request->user()->id,
            403,
            'Kamu tidak memiliki akses ke catatan ini.'
        );
    }

    public function export(Request $request)
    {
        $notes = $request->user()->notes()
            ->latest()
            ->get();

        $filename = 'starship-' . now()->format('Y-m-d-His') . '.csv';

        return Response::streamDownload(function () use ($notes) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas Excel
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Judul',
                'Kategori',
                'Isi Catatan',
                'Tanggal Dibuat',
            ]);

            foreach ($notes as $note) {
                fputcsv($output, [
                    $note->title,
                    $note->category,
                    $note->content,
                    $note->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}