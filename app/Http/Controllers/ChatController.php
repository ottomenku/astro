<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use App\Models\ChatThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private const DEFAULT_MODEL = 'gpt-4o-mini';

    public function index()
    {
        return view('chat');
    }

    public function models()
    {
        // MVP: fix modell lista
        return response()->json([
            'models' => [
                (string) config('services.openai.model', self::DEFAULT_MODEL),
            ],
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'thread_id' => ['nullable', 'integer'],
            'prompt' => ['required', 'string', 'max:4000'],
            'model' => ['nullable', 'string', 'max:255'],
        ]);

        $model = $validated['model'] ?: self::DEFAULT_MODEL;
        $prompt = $validated['prompt'];

        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            // Thread: ha nincs megadva, létrehozunk egyet
            $thread = null;
            if (! empty($validated['thread_id'])) {
                $thread = ChatThread::query()->where('id', $validated['thread_id'])->where('user_id', $user->id)->first();
            }
            if (! $thread) {
                $thread = ChatThread::create([
                    'user_id' => $user->id,
                    'title' => 'Új beszélgetés',
                ]);
            }

            $result = app(ChatService::class)->sendToThread($user, $thread, $prompt, $model);
            $answer = $result['answer'];

            return response()->json([
                'thread_id' => $thread->id,
                'response' => $answer,
            ]);
        } catch (\Throwable $error) {
            Log::error('Chat send failed', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => $error->getMessage() ?: 'Hiba történt a chat hívás során.',
            ], 500);
        }
    }
}
