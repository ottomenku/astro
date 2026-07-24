<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'prompt',
        'response',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array{prompt_tokens: ?int, completion_tokens: ?int, total_tokens: ?int}
     */
    public function tokenUsage(): array
    {
        $usage = (array) data_get($this->meta, 'openai.usage', []);

        return [
            'prompt_tokens' => isset($usage['prompt_tokens']) ? (int) $usage['prompt_tokens'] : null,
            'completion_tokens' => isset($usage['completion_tokens']) ? (int) $usage['completion_tokens'] : null,
            'total_tokens' => isset($usage['total_tokens']) ? (int) $usage['total_tokens'] : null,
        ];
    }

    public function totalTokens(): ?int
    {
        return $this->tokenUsage()['total_tokens'];
    }
}
