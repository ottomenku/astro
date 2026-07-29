<?php

namespace App\Services;

use App\Models\BirthChart;
use App\Models\User;
use App\Models\UserDailyHoroscopeSetting;
use App\Support\HoroscopeGenerationOptions;
use App\Support\HoroscopeLlmConfig;
use App\Support\HoroscopePeriod;

class HoroscopePromptPreviewService
{
    public function __construct(
        private readonly DailyHoroscopeService $dailyHoroscope,
        private readonly DailyHoroscopePromptBuilder $promptBuilder,
        private readonly PeriodHoroscopeContextBuilder $periodContext,
        private readonly HoroscopeLlmConfig $llmConfig,
    ) {}

    /**
     * @return array{
     *     context: string,
     *     label: string,
     *     system_prompt: string,
     *     user_prompt: string,
     *     instructions_prompt: string,
     *     default_instructions_prompt: string,
     *     has_override: bool,
     *     preview_note: string|null
     * }
     */
    public function preview(
        User $user,
        string $context,
        string $locale,
        string $mode = 'single',
        ?int $birthChartId = null,
        ?int $birthChartIdA = null,
        ?int $birthChartIdB = null,
        ?string $periodType = null,
    ): array {
        $locale = strtolower(trim($locale));
        $options = HoroscopeGenerationOptions::fromRequest(null, HoroscopeGenerationOptions::DETAIL_NORMAL, null);
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['scoringProfile']);
        $periodType = HoroscopePeriod::normalize($periodType);
        $bounds = HoroscopePeriod::bounds($periodType);
        $preview = $this->dailyHoroscope->previewPayload($locale);
        $chartPayload = $preview['chart_payload'];
        $scoreContext = $this->dailyHoroscope->resolvePreviewScoreContext($locale, $preview['scores']);
        $periodContext = $this->periodContext->build(
            $bounds['type'],
            $bounds['start'],
            $bounds['end'],
            $locale,
        );

        $previewNote = null;
        $systemPrompt = '';
        $userPrompt = '';

        if ($context === HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE) {
            $attached = $this->dailyHoroscope->previewAttachedPayloadForUser($user, $birthChartId);
            $systemPrompt = $this->promptBuilder->horoscopeCompactMessageSystemPrompt($settings, $locale, $bounds['type'], $options, false);
            $userPrompt = $this->promptBuilder->horoscopePersonalUserPromptForPeriod(
                $settings,
                $locale,
                $bounds['type'],
                $chartPayload,
                $scoreContext,
                $attached['payload'],
                $periodContext,
                $options,
            );
            $previewNote = $attached['note'];
        } elseif ($context === HoroscopeLlmConfig::PROMPT_PARTNERSHIP_MESSAGE) {
            $pair = $this->dailyHoroscope->previewPartnershipAttachedPayloadsForUser(
                $user,
                $birthChartIdA,
                $birthChartIdB,
            );
            $systemPrompt = $this->promptBuilder->horoscopeCompactMessageSystemPrompt($settings, $locale, $bounds['type'], $options, true);
            $userPrompt = $this->promptBuilder->horoscopePartnershipUserPromptForPeriod(
                $locale,
                $bounds['type'],
                $chartPayload,
                $scoreContext,
                null,
                $periodContext,
                $options,
                $pair['payload_a'],
                $pair['payload_b'],
            );
            $previewNote = $pair['note'];
        } elseif ($context === HoroscopeLlmConfig::PROMPT_PERSONAL_EXPLANATION) {
            $attached = $this->dailyHoroscope->previewAttachedPayloadForUser($user, $birthChartId);
            $systemPrompt = $this->promptBuilder->horoscopePersonalProfileExplanationSystemPrompt($settings, $locale, $options);
            $userPrompt = $this->promptBuilder->horoscopePersonalProfileExplanationUserPrompt(
                $attached['payload'] ?? [],
                $locale,
                $options,
            );
            $previewNote = $attached['note'];
        } elseif ($context === HoroscopeLlmConfig::PROMPT_PARTNERSHIP_EXPLANATION) {
            $pair = $this->dailyHoroscope->previewPartnershipAttachedPayloadsForUser(
                $user,
                $birthChartIdA,
                $birthChartIdB,
            );
            $systemPrompt = $this->promptBuilder->horoscopePartnershipProfileExplanationSystemPrompt($locale, $options);
            $userPrompt = $this->promptBuilder->horoscopePartnershipProfileExplanationUserPrompt(
                $pair['payload_a'] ?? [],
                $pair['payload_b'] ?? [],
                null,
                $locale,
                $options,
            );
            $previewNote = $pair['note'];
        }

        if ($mode === 'dual' && in_array($context, [HoroscopeLlmConfig::PROMPT_PERSONAL_MESSAGE, HoroscopeLlmConfig::PROMPT_PERSONAL_EXPLANATION], true)) {
            $previewNote = __('horoscope.prompt_preview_dual_context_note', [], $locale);
        }

        return [
            'context' => $context,
            'label' => $this->llmConfig->promptLabel($context, $locale),
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'instructions_prompt' => $this->llmConfig->prompt($context, $locale),
            'default_instructions_prompt' => $this->llmConfig->defaultPrompt($context, $locale),
            'has_override' => trim($this->llmConfig->promptOverride($context, $locale)) !== '',
            'preview_note' => $previewNote,
        ];
    }
}
