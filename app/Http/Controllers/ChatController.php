<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
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
            'prompt' => ['required', 'string', 'max:4000'],
            'model' => ['nullable', 'string', 'max:255'],
        ]);

        $model = $validated['model'] ?: self::DEFAULT_MODEL;
        $prompt = $validated['prompt'];

        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $result = app(ChatService::class)->send($user, $prompt, $model);
            $conversation = $result['conversation'];
            $answer = $result['answer'];

            return response()->json([
                'id' => $conversation->id,
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
