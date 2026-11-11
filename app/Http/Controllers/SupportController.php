<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $conversations = $request->user()->supportConversations()
            ->with(['messages' => fn ($query) => $query->latest()->limit(1)])
            ->latest('updated_at')
            ->paginate(15);

        return view('support.index', compact('conversations'));
    }

    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(SupportConversation::CATEGORIES))],
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = DB::transaction(function () use ($request, $validated) {
            $conversation = $request->user()->supportConversations()->create([
                'subject' => $validated['subject'],
                'category' => $validated['category'],
            ]);

            $conversation->messages()->create([
                'sender_id' => $request->user()->id,
                'body' => $validated['body'],
            ]);

            return $conversation;
        });

        return redirect()->route('support.show', $conversation)
            ->with('success', 'Pesan berhasil dikirim ke Admin.');
    }

    public function show(Request $request, SupportConversation $conversation)
    {
        abort_unless((int) $conversation->user_id === (int) $request->user()->id, 404);

        $conversation->load(['messages.sender']);

        return view('support.show', compact('conversation'));
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        abort_unless((int) $conversation->user_id === (int) $request->user()->id, 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);
        $conversation->touch();

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function adminIndex()
    {
        $conversations = SupportConversation::with('user')
            ->with(['messages' => fn ($query) => $query->latest('id')->limit(1)])
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.support.index', compact('conversations'));
    }

    public function adminShow(SupportConversation $conversation)
    {
        $conversation->load(['user', 'messages.sender']);

        return view('support.show', compact('conversation'));
    }

    public function adminReply(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);
        $conversation->touch();

        return back()->with('success', 'Balasan berhasil dikirim ke user.');
    }
}
