<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OpenAIClient
{
    public function chat(array $messages, ?string $model = null, array $options = []): Response
    {
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $model = $model ?: (string) config('services.openai.model', 'gpt-4o-mini');
        $timeout = (int) ($options['timeout'] ?? 60);
        unset($options['timeout']);

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $options);

        return Http::withToken((string) config('services.openai.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->post($baseUrl.'/chat/completions', $payload);
    }
}
