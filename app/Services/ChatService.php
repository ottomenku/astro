<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(private readonly OpenAIClient $client) {}

    /**
     * @return array{conversation: Conversation, answer: string, usage: array}
     */
    public function send(User $user, string $prompt, ?string $model = null): array
    {
        $this->ensureQuota($user);

        $response = $this->client->chat([
            ['role' => 'system', 'content' => 'Te egy asztrológiai asszisztens vagy. Válaszolj magyarul, tömören és érthetően.'],
            ['role' => 'user', 'content' => $prompt],
        ], $model);

        if ($response->failed()) {
            Log::warning('OpenAI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('OpenAI hívás sikertelen.');
        }

        $data = $response->json();
        $answer = (string) Arr::get($data, 'choices.0.message.content', '');
        $usage = (array) ($data['usage'] ?? []);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'model' => $model ?: (string) config('services.openai.model', 'gpt-4o-mini'),
            'prompt' => $prompt,
            'response' => $answer,
            'meta' => [
                'openai' => $data,
            ],
        ]);

        $this->applyUsageToUser($user, $usage);

        return [
            'conversation' => $conversation,
            'answer' => $answer,
            'usage' => $usage,
        ];
    }

    private function ensureQuota(User $user): void
    {
        // ha nincs keret beállítva, akkor “végtelen” (admin beállítja később)
        if ($user->token_quota_total <= 0) {
            return;
        }

        if ($user->token_quota_used >= $user->token_quota_total) {
            throw new \RuntimeException('Elfogyott a token kereted.');
        }
    }

    /**
     * @param  array{prompt_tokens?:int, completion_tokens?:int, total_tokens?:int}  $usage
     */
    private function applyUsageToUser(User $user, array $usage): void
    {
        $total = (int) ($usage['total_tokens'] ?? 0);
        if ($total <= 0) {
            return;
        }

        $user->increment('token_quota_used', $total);
    }
}
