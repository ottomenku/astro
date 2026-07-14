<?php

namespace App\Services;

use App\Models\DailyHoroscopeSetting;
use App\Models\UserDailyHoroscopeSetting;

class DailyHoroscopePromptBuilder
{
    public function __construct(
        private readonly DailyHoroscopeLlmContextBuilder $llmContext,
    ) {}

    public function globalSystemInstructions(string $locale): string
    {
        $setting = DailyHoroscopeSetting::forLocale($locale);
        $custom = trim((string) ($setting->system_prompt ?? ''));

        if ($custom !== '') {
            return $custom;
        }

        return ChatPrompts::dailyHoroscopeSystemInstructions($locale);
    }

    public function globalSystemOutputFormat(string $locale): string
    {
        return ChatPrompts::dailyHoroscopeSystemOutputFormat($locale)
            ."\n\n"
            .ChatPrompts::dailyHoroscopeResponseLanguage($locale);
    }

    public function globalSystemPrompt(string $locale): string
    {
        return $this->globalSystemInstructions($locale)
            ."\n\n"
            .$this->globalSystemOutputFormat($locale);
    }

    public function userSystemPrompt(UserDailyHoroscopeSetting $setting, string $locale): string
    {
        $custom = trim((string) ($setting->system_prompt ?? ''));

        if ($custom !== '') {
            return $custom."\n\n".$this->globalSystemOutputFormat($locale);
        }

        return $this->globalSystemPrompt($locale);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $attachedChartPayload
     */
    public function globalUserPrompt(
        string $locale,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload = null,
    ): string {
        $setting = DailyHoroscopeSetting::forLocale($locale);
        $template = trim((string) ($setting->user_prompt_template ?? ''));

        if ($template === '') {
            $template = ChatPrompts::dailyHoroscopeUserPromptTemplate($locale);
        }

        $append = trim((string) ($setting->user_prompt_append ?? ''));
        if ($append !== '') {
            $template = rtrim($template)."\n\n".$append;
        }

        return $this->assembleUserPrompt($locale, $template, $chartPayload, $scoreContext, $attachedChartPayload);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $attachedChartPayload
     */
    public function userUserPrompt(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload = null,
    ): string {
        $template = trim((string) ($setting->user_prompt_template ?? ''));

        if ($template === '') {
            return $this->globalUserPrompt($locale, $chartPayload, $scoreContext, $attachedChartPayload);
        }

        return $this->assembleUserPrompt($locale, $template, $chartPayload, $scoreContext, $attachedChartPayload);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $attachedChartPayload
     */
    private function assembleUserPrompt(
        string $locale,
        string $template,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload,
    ): string {
        $chartForLlm = $this->llmContext->buildChartContext($chartPayload, $locale);
        $scoreForLlm = $this->llmContext->buildScoreSummary($scoreContext);
        $attachedForLlm = $this->llmContext->buildAttachedContext($attachedChartPayload, $locale);

        $chartJson = $this->encodeJson($chartForLlm);
        $scoreJson = $this->encodeJson($scoreForLlm);
        $attachedJson = $attachedForLlm !== null && $attachedForLlm !== []
            ? $this->encodeJson($attachedForLlm)
            : '';

        $hasChartPlaceholder = str_contains($template, ':chart_json');
        $hasScorePlaceholder = str_contains($template, ':score_json');
        $hasAttachedPlaceholder = str_contains($template, ':attached_chart_json');

        $prompt = str_replace(
            [':chart_json', ':score_json', ':attached_chart_json'],
            [$chartJson, $scoreJson, $attachedJson],
            $template,
        );

        $appendBlocks = [];

        if (! $hasChartPlaceholder) {
            $appendBlocks[] = $this->dataBlockLabel('chart', $locale)."\n".$chartJson;
        }

        if (! $hasScorePlaceholder) {
            $appendBlocks[] = $this->dataBlockLabel('score', $locale)."\n".$scoreJson;
        }

        if ($attachedJson !== '' && ! $hasAttachedPlaceholder) {
            $appendBlocks[] = $this->dataBlockLabel('attached', $locale)."\n".$attachedJson;
        }

        if ($appendBlocks !== []) {
            $prompt = rtrim($prompt)."\n\n".implode("\n\n", $appendBlocks);
        }

        return $prompt;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function encodeJson(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
    }

    private function dataBlockLabel(string $type, string $locale): string
    {
        $labels = [
            'hu' => [
                'chart' => 'Szignifikáns képlet-adatok (JSON, automatikusan csatolva):',
                'score' => 'Pontozási összesítő (JSON, automatikusan csatolva):',
                'attached' => 'Csatolt mentett horoszkóp (JSON, automatikusan csatolva):',
            ],
            'en' => [
                'chart' => 'Significant chart data (JSON, appended automatically):',
                'score' => 'Scoring summary (JSON, appended automatically):',
                'attached' => 'Attached saved chart (JSON, appended automatically):',
            ],
        ];

        return $labels[$locale][$type] ?? $labels['en'][$type];
    }

    /** @deprecated Use globalSystemPrompt() */
    public function systemPrompt(string $locale): string
    {
        return $this->globalSystemPrompt($locale);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     *
     * @deprecated Use globalUserPrompt()
     */
    public function userPrompt(string $locale, array $chartPayload, array $scoreContext): string
    {
        return $this->globalUserPrompt($locale, $chartPayload, $scoreContext);
    }
}
