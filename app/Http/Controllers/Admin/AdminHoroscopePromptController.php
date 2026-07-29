<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HoroscopePromptPreviewService;
use App\Support\HoroscopeLlmConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminHoroscopePromptController extends Controller
{
    public function show(Request $request, HoroscopePromptPreviewService $preview): JsonResponse
    {
        $validated = $request->validate([
            'context' => ['required', 'string', Rule::in(app(HoroscopeLlmConfig::class)->promptKeys())],
            'locale' => ['nullable', 'string', Rule::in(['hu', 'en'])],
            'mode' => ['nullable', 'string', Rule::in(['single', 'dual'])],
            'period' => ['nullable', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
            'birth_chart_id' => ['nullable', 'integer'],
            'birth_chart_id_a' => ['nullable', 'integer'],
            'birth_chart_id_b' => ['nullable', 'integer'],
        ]);

        $locale = Str::lower(trim($validated['locale'] ?? app()->getLocale()));

        return response()->json($preview->preview(
            user: $request->user(),
            context: $validated['context'],
            locale: $locale,
            mode: $validated['mode'] ?? 'single',
            birthChartId: isset($validated['birth_chart_id']) ? (int) $validated['birth_chart_id'] : null,
            birthChartIdA: isset($validated['birth_chart_id_a']) ? (int) $validated['birth_chart_id_a'] : null,
            birthChartIdB: isset($validated['birth_chart_id_b']) ? (int) $validated['birth_chart_id_b'] : null,
            periodType: $validated['period'] ?? null,
        ));
    }

    public function update(Request $request, HoroscopeLlmConfig $config): JsonResponse
    {
        $validated = $request->validate([
            'context' => ['required', 'string', Rule::in($config->promptKeys())],
            'locale' => ['required', 'string', Rule::in(['hu', 'en'])],
            'prompt' => ['nullable', 'string', 'max:50000'],
        ]);

        $locale = Str::lower(trim($validated['locale']));
        $context = $validated['context'];
        $prompt = trim((string) ($validated['prompt'] ?? ''));

        $config->updatePrompt($context, $locale, $prompt !== '' ? $prompt : null);

        return response()->json([
            'ok' => true,
            'instructions_prompt' => $config->prompt($context, $locale),
            'default_instructions_prompt' => $config->defaultPrompt($context, $locale),
            'has_override' => trim($config->promptOverride($context, $locale)) !== '',
        ]);
    }
}
