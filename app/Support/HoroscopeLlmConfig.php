<?php

namespace App\Support;

use App\Models\DailyHoroscopeSetting;
use Illuminate\Support\Str;

class HoroscopeLlmConfig
{
    public const PROMPT_PERSONAL_MESSAGE = 'personal_message';

    public const PROMPT_PARTNERSHIP_MESSAGE = 'partnership_message';

    public const PROMPT_PERSONAL_EXPLANATION = 'personal_explanation';

    public const PROMPT_PARTNERSHIP_EXPLANATION = 'partnership_explanation';

    /** @var array<string, array{column: string, lang: string}> */
    private const PROMPT_MAP = [
        self::PROMPT_PERSONAL_MESSAGE => [
            'column' => 'horoscope_prompt_personal_message',
            'lang' => 'daily.horoscope_personal_instructions',
        ],
        self::PROMPT_PARTNERSHIP_MESSAGE => [
            'column' => 'horoscope_prompt_partnership_message',
            'lang' => 'daily.horoscope_partnership_instructions',
        ],
        self::PROMPT_PERSONAL_EXPLANATION => [
            'column' => 'horoscope_prompt_personal_explanation',
            'lang' => 'daily.horoscope_personal_profile_explanation_instructions',
        ],
        self::PROMPT_PARTNERSHIP_EXPLANATION => [
            'column' => 'horoscope_prompt_partnership_explanation',
            'lang' => 'daily.horoscope_partnership_profile_explanation_instructions',
        ],
    ];

    public function setting(string $locale): DailyHoroscopeSetting
    {
        return DailyHoroscopeSetting::forLocale(Str::lower(trim($locale)));
    }

    public function sentenceCount(string $type, string $detailLevel, string $locale): int
    {
        $setting = $this->setting($locale);
        $prefix = $type === 'message' ? 'message_sentences_' : 'explanation_sentences_';

        $column = match ($detailLevel) {
            HoroscopeGenerationOptions::DETAIL_SHORT => $prefix.'short',
            HoroscopeGenerationOptions::DETAIL_DETAILED => $prefix.'detailed',
            default => $prefix.'normal',
        };

        $value = (int) ($setting->{$column} ?? 0);

        return $value > 0 ? $value : $this->defaultSentenceCount($type, $detailLevel);
    }

    public function prompt(string $key, string $locale): string
    {
        $override = trim($this->promptOverride($key, $locale));
        if ($override !== '') {
            return $override;
        }

        return $this->defaultPrompt($key, $locale);
    }

    public function defaultPrompt(string $key, string $locale): string
    {
        $map = self::PROMPT_MAP[$key] ?? null;
        if ($map === null) {
            return '';
        }

        return trim((string) __($map['lang'], [], $locale));
    }

    public function promptOverride(string $key, string $locale): string
    {
        $map = self::PROMPT_MAP[$key] ?? null;
        if ($map === null) {
            return '';
        }

        $setting = $this->setting($locale);

        return trim((string) ($setting->{$map['column']} ?? ''));
    }

    public function updatePrompt(string $key, string $locale, ?string $value): void
    {
        $map = self::PROMPT_MAP[$key] ?? null;
        if ($map === null) {
            return;
        }

        $this->setting($locale)->update([
            $map['column'] => trim((string) $value) !== '' ? trim((string) $value) : null,
        ]);
    }

    /**
     * @return list<string>
     */
    public function promptKeys(): array
    {
        return array_keys(self::PROMPT_MAP);
    }

    public function promptLabel(string $key, string $locale): string
    {
        return match ($key) {
            self::PROMPT_PERSONAL_MESSAGE => __('horoscope.prompt_label_personal_message', [], $locale),
            self::PROMPT_PARTNERSHIP_MESSAGE => __('horoscope.prompt_label_partnership_message', [], $locale),
            self::PROMPT_PERSONAL_EXPLANATION => __('horoscope.prompt_label_personal_explanation', [], $locale),
            self::PROMPT_PARTNERSHIP_EXPLANATION => __('horoscope.prompt_label_partnership_explanation', [], $locale),
            default => $key,
        };
    }

    public function profileExplanationOutputFormat(int $sentenceCount, string $locale): string
    {
        return __('daily.horoscope_profile_explanation_output_format_dynamic', [
            'count' => $sentenceCount,
        ], $locale);
    }

    public function messageSummaryLengthInstruction(int $sentenceCount, string $locale): string
    {
        return __('daily.horoscope_message_summary_length_instruction', [
            'count' => $sentenceCount,
        ], $locale);
    }

    public function userFocusBlock(?string $userFocus, string $locale, string $type = 'explanation'): string
    {
        $focus = trim((string) $userFocus);
        if ($focus === '') {
            return '';
        }

        $key = $type === 'message'
            ? 'horoscope.user_focus_message_block'
            : 'horoscope.user_focus_prompt_block';

        return __($key, [
            'focus' => $focus,
        ], $locale);
    }

    private function defaultSentenceCount(string $type, string $detailLevel): int
    {
        return match ($detailLevel) {
            HoroscopeGenerationOptions::DETAIL_SHORT => 20,
            HoroscopeGenerationOptions::DETAIL_DETAILED => 100,
            default => 50,
        };
    }
}
