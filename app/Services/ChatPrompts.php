<?php

namespace App\Services;

class ChatPrompts
{
    public static function defaultSystem(?string $locale = null): string
    {
        return self::translate('default_system', $locale);
    }

    public static function threadSystem(?string $locale = null): string
    {
        return self::translate('thread_system', $locale);
    }

    /**
     * @param  array<string, mixed>|null  $chart
     * @param  array<string, mixed>|null  $score
     */
    public static function horoscopeSystem(?array $chart = null, ?array $score = null, ?string $locale = null): string
    {
        $system = self::translate('horoscope_system', $locale);

        if ($chart !== null && $chart !== []) {
            $system .= "\n\n".self::translate('horoscope_chart_context', $locale)."\n"
                .json_encode($chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($score !== null && $score !== []) {
            $system .= "\n\n".self::translate('horoscope_score_context', $locale)."\n"
                .json_encode($score, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $system .= "\n".self::translate('horoscope_score_instruction', $locale);
        }

        return $system;
    }

    /**
     * @param  array<string, mixed>|null  $chartData
     * @param  array<string, mixed>|null  $score
     */
    public static function natalContext(?array $chartData = null, ?array $score = null, ?string $locale = null): string
    {
        $parts = [];

        if ($chartData !== null && $chartData !== []) {
            $parts[] = self::translate('natal_context', $locale)."\n"
                .json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($score !== null && $score !== []) {
            $parts[] = self::translate('horoscope_score_context', $locale)."\n"
                .json_encode($score, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $parts[] = self::translate('horoscope_score_instruction', $locale);
        }

        return implode("\n\n", $parts);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function tools(?string $locale = null): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'transit_now',
                    'description' => self::translate('tools.transit_now.description', $locale),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'planet' => [
                                'type' => 'string',
                                'description' => self::translate('tools.transit_now.planet', $locale),
                            ],
                        ],
                        'required' => ['planet'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'find_transit_event',
                    'description' => self::translate('tools.find_transit_event.description', $locale),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'datetime_start_utc' => [
                                'type' => 'string',
                                'description' => self::translate('tools.find_transit_event.datetime_start_utc', $locale),
                            ],
                            'datetime_end_utc' => [
                                'type' => 'string',
                                'description' => self::translate('tools.find_transit_event.datetime_end_utc', $locale),
                            ],
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
    }

    public static function dailyHoroscopeSystemInstructions(?string $locale = null): string
    {
        return self::translateDaily('system_instructions', $locale);
    }

    public static function dailyHoroscopeSystemOutputFormat(?string $locale = null): string
    {
        return self::translateDaily('system_output_format', $locale);
    }

    public static function dailyHoroscopeResponseLanguage(?string $locale = null): string
    {
        return self::translateDaily('response_language', $locale);
    }

    public static function dailyHoroscopeSystem(?string $locale = null): string
    {
        return self::dailyHoroscopeSystemInstructions($locale)
            ."\n\n"
            .self::dailyHoroscopeSystemOutputFormat($locale);
    }

    public static function dailyHoroscopeUserPromptTemplate(?string $locale = null): string
    {
        return self::translateDaily('user_prompt', $locale);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @param  array<string, mixed>  $scoreContext
     */
    public static function dailyHoroscopeUserPrompt(array $chartPayload, array $scoreContext, ?string $locale = null): string
    {
        return app(DailyHoroscopePromptBuilder::class)
            ->userPrompt($locale ?? app()->getLocale(), $chartPayload, $scoreContext);
    }

    private static function translateDaily(string $key, ?string $locale): string
    {
        $locale = $locale ?? app()->getLocale();
        $previous = app()->getLocale();

        if ($locale !== $previous) {
            app()->setLocale($locale);
        }

        $value = (string) __("daily.{$key}");

        if ($locale !== $previous) {
            app()->setLocale($previous);
        }

        return $value;
    }

    private static function translate(string $key, ?string $locale): string
    {
        $locale = $locale ?? app()->getLocale();
        $previous = app()->getLocale();

        if ($locale !== $previous) {
            app()->setLocale($locale);
        }

        $value = (string) __("chat.{$key}");

        if ($locale !== $previous) {
            app()->setLocale($previous);
        }

        return $value;
    }
}
