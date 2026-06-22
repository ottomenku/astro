<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HoroscopeTransitService;
use App\Services\TransitSearchService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(private readonly OpenAIClient $client, private readonly AuditService $audit) {}

    /**
     * Thread-alapú üzenetküldés: horoszkóp kontextussal + üzenet előzményekkel.
     *
     * @return array{thread: ChatThread, answer: string, usage: array}
     */
    public function sendToThread(User $user, ChatThread $thread, string $prompt, ?string $model = null): array
    {
        $this->ensureQuota($user);

        $apiKey = (string) config('services.openai.api_key');
        if (trim($apiKey) === '') {
            throw new \RuntimeException('Hiányzik az OPENAI_API_KEY (.env).');
        }

        // Mentjük a user üzenetét
        ChatMessage::create([
            'thread_id' => $thread->id,
            'role' => 'user',
            'content' => $prompt,
        ]);

        $this->audit->log($thread, $user, 'user', $prompt, [], 'llm', $user->name);

        // Horoszkóp kontextus (aktív thread horoszkóp, fallback a user natál)
        $horoscope = $thread->activeHoroscope()->first() ?: $user->horoscopes()->where('kind', 'natal')->latest('id')->first();
        $horoscopeData = $horoscope?->data;

        $system = implode("\n", [
            'Te egy asztrológiai asszisztens vagy.',
            'Válaszolj magyarul, tömören és érthetően.',
            'A felhasználónak szánt VÉGSŐ választ mindig két kötőjellel kezdd, így: --',
            'Ha belső megjegyzést/elemzést készítesz, azt ne írd ki a felhasználónak.',
        ]);

        $messages = [
            ['role' => 'system', 'content' => $system],
        ];

        if (is_array($horoscopeData)) {
            // Kompakt natál kontextus
            $messages[] = [
                'role' => 'system',
                'content' => "Felhasználó natál horoszkóp adatai (kompakt JSON):\n".json_encode($horoscopeData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        // Előzmények – az utolsó 20 már mentett üzenet (a mostani user üzenet is benne van)
        $history = ChatMessage::query()
            ->where('thread_id', $thread->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        foreach ($history as $msg) {
            $role = in_array($msg->role, ['user', 'assistant', 'system', 'tool'], true) ? $msg->role : 'user';
            $messages[] = ['role' => $role, 'content' => $msg->content];
        }

        // Toolok (MVP): tranzit most + esemény keresés
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'transit_now',
                    'description' => 'Aktuális tranzit pozíció lekérdezése (jelenlegi hely alapján).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'planet' => ['type' => 'string', 'description' => 'Pl. Mars, Sun, Moon, Mercury, Venus, Jupiter, Saturn, Uranus, Neptune, Pluto'],
                        ],
                        'required' => ['planet'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'find_transit_event',
                    'description' => 'Időablakban megkeresi, hogy mikor következik be egy tranzit esemény (házba lépés / aspektus).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'datetime_start_utc' => ['type' => 'string', 'description' => 'ISO dátum UTC-ben'],
                            'datetime_end_utc' => ['type' => 'string', 'description' => 'ISO dátum UTC-ben'],
                            'event' => [
                                'type' => 'object',
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['enter_house', 'aspect_to_natal']],
                                    'planet' => ['type' => 'string'],
                                    'house' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 12],
                                    'natal_longitude' => ['type' => 'number', 'minimum' => 0, 'maximum' => 360],
                                    'aspect_angle' => ['type' => 'number', 'enum' => [0, 60, 90, 120, 180]],
                                    'orb' => ['type' => 'number', 'minimum' => 0, 'maximum' => 10],
                                ],
                                'required' => ['type', 'planet'],
                            ],
                        ],
                        'required' => ['datetime_start_utc', 'datetime_end_utc', 'event'],
                    ],
                ],
            ],
        ];

        $response = $this->client->chat($messages, $model, [
            'tools' => $tools,
            'tool_choice' => 'auto',
        ]);

        $this->audit->log($thread, $user, 'app', 'OpenAI request sent', ['model' => $model], 'openai');

        if ($response->failed()) {
            Log::warning('OpenAI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            if ($response->status() === 401) {
                throw new \RuntimeException('OpenAI hitelesítés sikertelen (401). Ellenőrizd az OPENAI_API_KEY-t.');
            }
            if ($response->status() === 429) {
                throw new \RuntimeException('OpenAI rate limit / kvóta hiba (429).');
            }
            throw new \RuntimeException('OpenAI hívás sikertelen.');
        }

        $data = $response->json();
        $choice = Arr::get($data, 'choices.0.message', []);
        $answer = (string) ($choice['content'] ?? '');
        $usage = (array) ($data['usage'] ?? []);

        // Tool calls kezelése (1 körös MVP)
        $toolCalls = (array) ($choice['tool_calls'] ?? []);
        if ($answer === '' && ! empty($toolCalls)) {
            foreach ($toolCalls as $tc) {
                $toolCallId = (string) ($tc['id'] ?? '');
                $fn = (array) ($tc['function'] ?? []);
                $name = (string) ($fn['name'] ?? '');
                $args = json_decode((string) ($fn['arguments'] ?? '{}'), true);
                if (! is_array($args)) {
                    $args = [];
                }

                $toolResult = null;
                if ($name === 'transit_now') {
                    $planet = (string) ($args['planet'] ?? '');
                    $toolResult = app(HoroscopeTransitService::class)->getTransitNow($user, $planet);
                } elseif ($name === 'find_transit_event') {
                    $toolResult = app(TransitSearchService::class)->findEvent($user, $args);
                } else {
                    $toolResult = ['error' => 'Ismeretlen tool: '.$name];
                }

                $messages[] = [
                    'role' => 'assistant',
                    'tool_calls' => [$tc],
                ];
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCallId,
                    'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];
            }

            $response2 = $this->client->chat($messages, $model, [
                'tools' => $tools,
                'tool_choice' => 'auto',
            ]);

            if ($response2->failed()) {
                throw new \RuntimeException('OpenAI hívás (tool után) sikertelen.');
            }

            $data2 = $response2->json();
            $choice2 = Arr::get($data2, 'choices.0.message', []);
            $answer = (string) ($choice2['content'] ?? '');
            $usage2 = (array) ($data2['usage'] ?? []);
            // usage összeadás
            $usage = [
                'prompt_tokens' => (int) (($usage['prompt_tokens'] ?? 0) + ($usage2['prompt_tokens'] ?? 0)),
                'completion_tokens' => (int) (($usage['completion_tokens'] ?? 0) + ($usage2['completion_tokens'] ?? 0)),
                'total_tokens' => (int) (($usage['total_tokens'] ?? 0) + ($usage2['total_tokens'] ?? 0)),
            ];
            $data = $data2;
        }

        ChatMessage::create([
            'thread_id' => $thread->id,
            'role' => 'assistant',
            'content' => $answer,
            'meta' => [
                'openai' => $data,
            ],
        ]);

        $this->audit->log($thread, $user, 'llm', $answer, ['model' => $model], $user->name, 'OpenAI');

        // Megtartjuk a régi logolást is (admin felülethez)
        Conversation::create([
            'user_id' => $user->id,
            'model' => $model ?: (string) config('services.openai.model', 'gpt-4o-mini'),
            'prompt' => $prompt,
            'response' => $answer,
            'meta' => [
                'openai' => $data,
                'thread_id' => $thread->id,
                'horoscope_id' => $horoscope?->id,
            ],
        ]);

        $thread->touch();

        $this->applyUsageToUser($user, $usage);

        return [
            'thread' => $thread,
            'answer' => $answer,
            'usage' => $usage,
        ];
    }

    /**
     * @return array{conversation: Conversation, answer: string, usage: array}
     */
    public function send(User $user, string $prompt, ?string $model = null): array
    {
        $this->ensureQuota($user);

        $apiKey = (string) config('services.openai.api_key');
        if (trim($apiKey) === '') {
            throw new \RuntimeException('Hiányzik az OPENAI_API_KEY (.env).');
        }

        $response = $this->client->chat([
            ['role' => 'system', 'content' => 'Te egy asztrológiai asszisztens vagy. Válaszolj magyarul, tömören és érthetően.'],
            ['role' => 'user', 'content' => $prompt],
        ], $model);

        if ($response->failed()) {
            Log::warning('OpenAI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // adjon értelmezhetőbb hibát a kliensnek
            if ($response->status() === 401) {
                throw new \RuntimeException('OpenAI hitelesítés sikertelen (401). Ellenőrizd az OPENAI_API_KEY-t.');
            }
            if ($response->status() === 429) {
                throw new \RuntimeException('OpenAI rate limit / kvóta hiba (429).');
            }
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
