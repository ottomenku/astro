<?php

namespace App\Services;

use App\Models\DailyHoroscopeSetting;
use App\Models\HoroscopeDailyMessage;
use App\Models\UserDailyHoroscopeSetting;
use App\Support\HoroscopeGenerationOptions;
use App\Support\HoroscopeLlmConfig;
use App\Support\HoroscopePeriod;

class DailyHoroscopePromptBuilder
{
    public function __construct(
        private readonly DailyHoroscopeLlmContextBuilder $llmContext,
        private readonly HoroscopeLlmConfig $llmConfig,
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

    public function globalSystemOutputFormat(string $locale, string $periodType = HoroscopePeriod::DAILY): string
    {
        $base = ChatPrompts::dailyHoroscopeSystemOutputFormat($locale)
            ."\n\n"
            .ChatPrompts::dailyHoroscopeResponseLanguage($locale);

        return match (HoroscopePeriod::normalize($periodType)) {
            HoroscopePeriod::WEEKLY => $base."\n\n".__('daily.weekly_output_length', [], $locale),
            HoroscopePeriod::MONTHLY => $base."\n\n".__('daily.monthly_output_length', [], $locale),
            default => $base,
        };
    }

    public function globalSystemPrompt(string $locale, string $periodType = HoroscopePeriod::DAILY): string
    {
        $prompt = $this->globalSystemInstructions($locale)
            ."\n\n"
            .$this->globalSystemOutputFormat($locale, $periodType);

        return match (HoroscopePeriod::normalize($periodType)) {
            HoroscopePeriod::WEEKLY => $prompt."\n\n".__('daily.weekly_system_instructions', [], $locale),
            HoroscopePeriod::MONTHLY => $prompt."\n\n".__('daily.monthly_system_instructions', [], $locale),
            default => $prompt,
        };
    }

    public function userSystemPrompt(UserDailyHoroscopeSetting $setting, string $locale): string
    {
        return $this->userSystemPromptForPeriod($setting, $locale, HoroscopePeriod::DAILY);
    }

    public function userSystemPromptForPeriod(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        string $periodType = HoroscopePeriod::DAILY,
    ): string {
        $custom = trim((string) ($setting->system_prompt ?? ''));

        $base = $custom !== ''
            ? $custom."\n\n".$this->globalSystemOutputFormat($locale, $periodType)
            : $this->globalSystemPrompt($locale, $periodType);

        return $base;
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
     * @param  array<string, mixed>  $periodContext
     */
    public function globalUserPromptForPeriod(
        string $locale,
        string $periodType,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload,
        array $periodContext,
    ): string {
        $prompt = $this->globalUserPrompt($locale, $chartPayload, $scoreContext, $attachedChartPayload);

        return $this->appendPeriodContext($prompt, $locale, $periodType, $periodContext);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $attachedChartPayload
     * @param  array<string, mixed>  $periodContext
     */
    public function userUserPromptForPeriod(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        string $periodType,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload,
        array $periodContext,
    ): string {
        $prompt = $this->userUserPrompt($setting, $locale, $chartPayload, $scoreContext, $attachedChartPayload);

        return $this->appendPeriodContext($prompt, $locale, $periodType, $periodContext);
    }

    /**
     * @param  array<string, mixed>  $periodContext
     */
    private function appendPeriodContext(
        string $prompt,
        string $locale,
        string $periodType,
        array $periodContext,
    ): string {
        $periodType = HoroscopePeriod::normalize($periodType);
        $append = match ($periodType) {
            HoroscopePeriod::WEEKLY => trim((string) __('daily.weekly_user_append', [], $locale)),
            HoroscopePeriod::MONTHLY => trim((string) __('daily.monthly_user_append', [], $locale)),
            default => trim((string) __('daily.daily_user_append', [], $locale)),
        };

        $blocks = [rtrim($prompt)];

        if ($append !== '') {
            $blocks[] = $append;
        }

        $blocks[] = $this->periodContextLabel($locale)."\n".$this->encodeJson($periodContext);

        return implode("\n\n", $blocks);
    }

    private function periodContextLabel(string $locale): string
    {
        return (string) __('daily.period_context_label', [], $locale);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $attachedChartPayload
     */
    public function horoscopePersonalUserPrompt(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload = null,
    ): string {
        return $this->horoscopePersonalUserPromptForPeriod(
            $setting,
            $locale,
            HoroscopePeriod::DAILY,
            $chartPayload,
            $scoreContext,
            $attachedChartPayload,
            [],
        );
    }

    public function horoscopePersonalSystemPrompt(UserDailyHoroscopeSetting $setting, string $locale): string
    {
        return $this->horoscopePersonalSystemPromptForPeriod($setting, $locale, HoroscopePeriod::DAILY);
    }

    public function horoscopePersonalSystemPromptForPeriod(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        string $periodType = HoroscopePeriod::DAILY,
        ?HoroscopeGenerationOptions $options = null,
    ): string {
        $detailLevel = $options?->detailLevel ?? HoroscopeGenerationOptions::DETAIL_NORMAL;
        $sentenceCount = $this->llmConfig->sentenceCount('message', $detailLevel, $locale);

        return $this->userSystemPromptForPeriod($setting, $locale, $periodType)
            ."\n\n"
            .$this->llmConfig->prompt(HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE, $locale)
            ."\n\n"
            .$this->llmConfig->messageSummaryLengthInstruction($sentenceCount, $locale)
            ."\n\n"
            .__('daily.horoscope_personal_period_instructions', [
                'period' => __('daily.period_'.HoroscopePeriod::normalize($periodType), [], $locale),
            ], $locale);
    }

    public function horoscopePartnershipSystemPrompt(string $locale): string
    {
        return $this->horoscopePartnershipSystemPromptForPeriod($locale, HoroscopePeriod::DAILY);
    }

    public function horoscopePartnershipSystemPromptForPeriod(
        string $locale,
        string $periodType = HoroscopePeriod::DAILY,
        ?HoroscopeGenerationOptions $options = null,
    ): string {
        $detailLevel = $options?->detailLevel ?? HoroscopeGenerationOptions::DETAIL_NORMAL;
        $sentenceCount = $this->llmConfig->sentenceCount('message', $detailLevel, $locale);

        return $this->globalSystemPrompt($locale, $periodType)
            ."\n\n"
            .$this->llmConfig->prompt(HoroscopeLlmConfig::PROMPT_PARTNERSHIP_MESSAGE, $locale)
            ."\n\n"
            .$this->llmConfig->messageSummaryLengthInstruction($sentenceCount, $locale)
            ."\n\n"
            .__('daily.horoscope_partnership_period_instructions', [
                'period' => __('daily.period_'.HoroscopePeriod::normalize($periodType), [], $locale),
            ], $locale);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $attachedChartPayload
     * @param  array<string, mixed>  $periodContext
     */
    public function horoscopePersonalUserPromptForPeriod(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        string $periodType,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload,
        array $periodContext,
        ?HoroscopeGenerationOptions $options = null,
    ): string {
        if ($options !== null) {
            return $this->buildCompactGenerationUserPrompt(
                $locale,
                $periodType,
                $chartPayload,
                $scoreContext,
                $attachedChartPayload,
                null,
                $periodContext,
                $options,
            );
        }

        $prompt = $this->userUserPrompt($setting, $locale, $chartPayload, $scoreContext, $attachedChartPayload);

        if ($periodContext === []) {
            return $prompt;
        }

        return $this->appendPeriodContext($prompt, $locale, $periodType, $periodContext);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $partnershipContext
     */
    public function horoscopePartnershipUserPrompt(
        string $locale,
        array $chartPayload,
        array $scoreContext,
        ?array $partnershipContext = null,
    ): string {
        return $this->horoscopePartnershipUserPromptForPeriod(
            $locale,
            HoroscopePeriod::DAILY,
            $chartPayload,
            $scoreContext,
            $partnershipContext,
            [],
        );
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $partnershipContext
     * @param  array<string, mixed>  $periodContext
     */
    public function horoscopePartnershipUserPromptForPeriod(
        string $locale,
        string $periodType,
        array $chartPayload,
        array $scoreContext,
        ?array $partnershipContext,
        array $periodContext,
        ?HoroscopeGenerationOptions $options = null,
        ?array $attachedChartA = null,
        ?array $attachedChartB = null,
    ): string {
        if ($options !== null) {
            return $this->buildCompactGenerationUserPrompt(
                $locale,
                $periodType,
                $chartPayload,
                $scoreContext,
                null,
                $this->llmContext->buildCompactPartnershipContext(
                    $attachedChartA,
                    $attachedChartB,
                    $locale,
                    $options->normalizedTopics(),
                ),
                $periodContext,
                $options,
            );
        }

        $template = trim((string) __('daily.horoscope_partnership_user_prompt', [], $locale));
        $prompt = $this->assembleUserPrompt($locale, $template, $chartPayload, $scoreContext, null);

        if ($partnershipContext !== null && $partnershipContext !== []) {
            $partnershipJson = $this->encodeJson($partnershipContext);
            $label = $locale === 'hu'
                ? 'Párkapcsolati képletek és szinasztria (JSON, automatikusan csatolva):'
                : 'Partnership charts and synastry (JSON, appended automatically):';

            $prompt = rtrim($prompt)."\n\n".$label."\n".$partnershipJson;
        }

        if ($periodContext === []) {
            return $prompt;
        }

        return $this->appendPeriodContext($prompt, $locale, $periodType, $periodContext);
    }

    public function horoscopePersonalExplanationSystemPrompt(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        string $periodType = HoroscopePeriod::DAILY,
    ): string {
        return $this->horoscopePersonalSystemPromptForPeriod($setting, $locale, $periodType)
            ."\n\n"
            .__('daily.horoscope_explanation_output_format', [], $locale)
            ."\n\n"
            .__('daily.horoscope_personal_explanation_instructions', [], $locale);
    }

    public function horoscopePartnershipExplanationSystemPrompt(
        string $locale,
        string $periodType = HoroscopePeriod::DAILY,
    ): string {
        return $this->horoscopePartnershipSystemPromptForPeriod($locale, $periodType)
            ."\n\n"
            .__('daily.horoscope_explanation_output_format', [], $locale)
            ."\n\n"
            .__('daily.horoscope_partnership_explanation_instructions', [], $locale);
    }

    public function horoscopePersonalExplanationUserPrompt(HoroscopeDailyMessage $message, string $locale): string
    {
        $blocks = [
            __('daily.horoscope_explanation_user_intro', [], $locale),
            __('daily.horoscope_explanation_message_label', [], $locale)."\n".$this->encodeJson([
                'period_type' => $message->period_type,
                'period_start' => $message->period_start?->toDateString(),
                'period_end' => $message->period_end?->toDateString(),
                'motto' => $message->motto,
                'summary' => $message->summary,
                'health' => $message->health,
                'money' => $message->money,
                'relationships' => $message->relationships,
                'work' => $message->work,
            ]),
        ];

        if (is_array($message->context_payload) && $message->context_payload !== []) {
            $blocks[] = __('daily.horoscope_explanation_context_label', [], $locale)."\n".$this->encodeJson($message->context_payload);
        }

        if (is_array($message->period_context) && $message->period_context !== []) {
            $blocks[] = $this->periodContextLabel($locale)."\n".$this->encodeJson($message->period_context);
        }

        return implode("\n\n", $blocks);
    }

    public function horoscopePartnershipExplanationUserPrompt(HoroscopeDailyMessage $message, string $locale): string
    {
        return $this->horoscopePersonalExplanationUserPrompt($message, $locale)
            ."\n\n"
            .__('daily.horoscope_partnership_explanation_user_append', [], $locale);
    }

    public function horoscopePersonalProfileExplanationSystemPrompt(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        ?HoroscopeGenerationOptions $options = null,
    ): string {
        $detailLevel = $options?->detailLevel ?? HoroscopeGenerationOptions::DETAIL_NORMAL;
        $sentenceCount = $this->llmConfig->sentenceCount('explanation', $detailLevel, $locale);

        return $this->globalSystemInstructions($locale)
            ."\n\n"
            .ChatPrompts::dailyHoroscopeResponseLanguage($locale)
            ."\n\n"
            .$this->llmConfig->profileExplanationOutputFormat($sentenceCount, $locale)
            ."\n\n"
            .$this->llmConfig->prompt(HoroscopeLlmConfig::PROMPT_PERSONAL_EXPLANATION, $locale);
    }

    public function horoscopePartnershipProfileExplanationSystemPrompt(
        string $locale,
        ?HoroscopeGenerationOptions $options = null,
    ): string {
        $detailLevel = $options?->detailLevel ?? HoroscopeGenerationOptions::DETAIL_NORMAL;
        $sentenceCount = $this->llmConfig->sentenceCount('explanation', $detailLevel, $locale);

        return $this->globalSystemInstructions($locale)
            ."\n\n"
            .ChatPrompts::dailyHoroscopeResponseLanguage($locale)
            ."\n\n"
            .$this->llmConfig->profileExplanationOutputFormat($sentenceCount, $locale)
            ."\n\n"
            .$this->llmConfig->prompt(HoroscopeLlmConfig::PROMPT_PARTNERSHIP_EXPLANATION, $locale);
    }

    public function horoscopeCompactMessageSystemPrompt(
        UserDailyHoroscopeSetting $setting,
        string $locale,
        string $periodType,
        HoroscopeGenerationOptions $options,
        bool $partnership = false,
    ): string {
        $sentenceCount = $this->llmConfig->sentenceCount('message', $options->detailLevel, $locale);
        $promptKey = $partnership
            ? HoroscopeLlmConfig::PROMPT_PARTNERSHIP_MESSAGE
            : HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE;

        return $this->globalSystemInstructions($locale)
            ."\n\n"
            .$this->globalSystemOutputFormat($locale, $periodType)
            ."\n\n"
            .ChatPrompts::dailyHoroscopeResponseLanguage($locale)
            ."\n\n"
            .$this->llmConfig->prompt($promptKey, $locale)
            ."\n\n"
            .$this->llmConfig->messageSummaryLengthInstruction($sentenceCount, $locale)
            ."\n\n"
            .__('daily.horoscope_personal_period_instructions', [
                'period' => __('daily.period_'.HoroscopePeriod::normalize($periodType), [], $locale),
            ], $locale);
    }

    public function appendUserFocus(string $userPrompt, ?HoroscopeGenerationOptions $options, string $locale, string $type = 'explanation'): string
    {
        $block = $this->llmConfig->userFocusBlock($options?->userFocus, $locale, $type);
        if ($block === '') {
            return $userPrompt;
        }

        return rtrim($userPrompt)."\n\n".$block;
    }

    /**
     * @param  array<string, mixed>  $attachedPayload
     */
    public function horoscopePersonalProfileExplanationUserPrompt(
        array $attachedPayload,
        string $locale,
        ?HoroscopeGenerationOptions $options = null,
    ): string {
        $context = $options !== null
            ? $this->llmContext->buildCompactAttachedContext(
                $attachedPayload,
                $locale,
                $options->normalizedTopics(),
            )
            : $this->llmContext->buildAttachedContext($attachedPayload, $locale);

        return implode("\n\n", [
            __('daily.horoscope_personal_profile_explanation_user_intro', [], $locale),
            __('daily.horoscope_explanation_context_label', [], $locale)."\n".$this->encodeJson($context ?? []),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attachedA
     * @param  array<string, mixed>  $attachedB
     * @param  array<string, mixed>|null  $partnershipContext
     */
    public function horoscopePartnershipProfileExplanationUserPrompt(
        array $attachedA,
        array $attachedB,
        ?array $partnershipContext,
        string $locale,
        ?HoroscopeGenerationOptions $options = null,
    ): string {
        $context = $options !== null
            ? $this->llmContext->buildCompactPartnershipContext(
                $attachedA,
                $attachedB,
                $locale,
                $options->normalizedTopics(),
            )
            : [
                'chart_a' => $this->llmContext->buildAttachedContext($attachedA, $locale),
                'chart_b' => $this->llmContext->buildAttachedContext($attachedB, $locale),
                'partnership' => $partnershipContext,
            ];

        return implode("\n\n", [
            __('daily.horoscope_partnership_profile_explanation_user_intro', [], $locale),
            __('daily.horoscope_explanation_context_label', [], $locale)."\n".$this->encodeJson($context ?? []),
            __('daily.horoscope_partnership_explanation_user_append', [], $locale),
        ]);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     * @param  array<string, mixed>|null  $attachedChartPayload
     * @param  array<string, mixed>|null  $partnershipContext
     * @param  array<string, mixed>  $periodContext
     */
    private function buildCompactGenerationUserPrompt(
        string $locale,
        string $periodType,
        array $chartPayload,
        array $scoreContext,
        ?array $attachedChartPayload,
        ?array $partnershipContext,
        array $periodContext,
        HoroscopeGenerationOptions $options,
    ): string {
        $payload = [
            'forecast' => $this->llmContext->buildCompactChartContext(
                $chartPayload,
                $scoreContext,
                $locale,
                $options->normalizedTopics(),
            ),
        ];

        if ($attachedChartPayload !== null && $attachedChartPayload !== []) {
            $payload['birth_chart'] = $this->llmContext->buildCompactAttachedContext(
                $attachedChartPayload,
                $locale,
                $options->normalizedTopics(),
            );
        }

        if ($partnershipContext !== null && $partnershipContext !== []) {
            $payload['partnership'] = $partnershipContext;
        }

        if ($periodContext !== []) {
            $payload['period'] = $periodContext;
        }

        $blocks = [
            __('daily.horoscope_compact_context_intro', [], $locale),
            $this->encodeJson($payload),
        ];

        return implode("\n\n", $blocks);
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
