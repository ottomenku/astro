<?php

namespace App\Http\Controllers;

use App\Models\ChatThread;
use Illuminate\Http\Request;

class ChatThreadController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $threads = ChatThread::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'updated_at']);

        return response()->json([
            'threads' => $threads,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $thread = ChatThread::create([
            'user_id' => $user->id,
            'title' => $validated['title'] ?: 'Új beszélgetés',
        ]);

        return response()->json([
            'thread' => $thread,
        ], 201);
    }

    public function show(Request $request, ChatThread $thread)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        abort_unless($thread->user_id === $user->id, 403);

        $messages = $thread->messages()
            ->orderBy('id')
            ->get(['id', 'role', 'content', 'created_at']);

        return response()->json([
            'thread' => $thread->only(['id', 'title', 'updated_at']),
            'messages' => $messages,
        ]);
    }
}
