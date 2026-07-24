<?php

namespace App\Services;

use App\Support\HoroscopePeriod;
use Illuminate\Support\Carbon;

class PeriodHoroscopeContextBuilder
{
    /** @var list<string> */
    private const RETROGRADE_PLANETS = [
        'Mercury',
        'Venus',
        'Mars',
        'Jupiter',
        'Saturn',
        'Uranus',
        'Neptune',
        'Pluto',
    ];

    public function __construct(
        private readonly HoroscopeCalculator $calculator,
        private readonly DailyHoroscopeLlmContextBuilder $llmContext,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(string $periodType, Carbon $periodStart, Carbon $periodEnd, string $locale): array
    {
        $periodType = HoroscopePeriod::normalize($periodType);
        $openingAt = $this->noonUtcForDate($periodStart);
        $closingAt = $this->noonUtcForDate($periodEnd);
        $openingPayload = $this->calculateChart($openingAt);
        $closingPayload = $this->calculateChart($closingAt);

        return [
            'period_type' => $periodType,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'opening' => [
                'datetime_utc' => $openingAt->toIso8601String(),
                'positions' => $this->compactPositions($openingPayload),
                'chart_context' => $this->llmContext->buildChartContext($openingPayload, $locale),
            ],
            'closing' => [
                'datetime_utc' => $closingAt->toIso8601String(),
                'positions' => $this->compactPositions($closingPayload),
                'chart_context' => $this->llmContext->buildChartContext($closingPayload, $locale),
            ],
            'retrograde_windows' => $this->detectRetrogradeWindows($periodStart, $periodEnd),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function detectRetrogradeWindows(Carbon $periodStart, Carbon $periodEnd): array
    {
        $windows = [];
        $states = $this->planetRetrogradeStates($periodStart);

        foreach (self::RETROGRADE_PLANETS as $planet) {
            if ($states[$planet] ?? false) {
                $windows[] = [
                    'planet' => $planet,
                    'starts_at' => null,
                    'ends_at' => null,
                    '_open' => true,
                ];
            }
        }

        if ($periodStart->equalTo($periodEnd)) {
            return $this->finalizeRetrogradeWindows($windows, $periodStart, $periodEnd);
        }

        $daySpan = (int) $periodStart->diffInDays($periodEnd);
        $step = $daySpan <= 7 ? 1 : 2;

        $cursor = $periodStart->copy()->addDays($step);
        while ($cursor->lte($periodEnd)) {
            $dayStates = $this->planetRetrogradeStates($cursor);

            foreach (self::RETROGRADE_PLANETS as $planet) {
                $previous = (bool) ($states[$planet] ?? false);
                $current = (bool) ($dayStates[$planet] ?? false);

                if ($previous === $current) {
                    continue;
                }

                if ($current) {
                    $windows[] = [
                        'planet' => $planet,
                        'starts_at' => $cursor->toDateString(),
                        'ends_at' => null,
                        '_open' => true,
                    ];
                } else {
                    $this->closeRetrogradeWindow($windows, $planet, $cursor->toDateString());
                }
            }

            $states = $dayStates;
            $cursor->addDays($step);
        }

        if (! $periodEnd->equalTo($periodStart) && ($daySpan % $step) !== 0) {
            $endStates = $this->planetRetrogradeStates($periodEnd);
            foreach (self::RETROGRADE_PLANETS as $planet) {
                $previous = (bool) ($states[$planet] ?? false);
                $current = (bool) ($endStates[$planet] ?? false);
                if ($previous === $current) {
                    continue;
                }
                if ($current) {
                    $windows[] = [
                        'planet' => $planet,
                        'starts_at' => $periodEnd->toDateString(),
                        'ends_at' => null,
                        '_open' => true,
                    ];
                } else {
                    $this->closeRetrogradeWindow($windows, $planet, $periodEnd->toDateString());
                }
            }
        }

        return $this->finalizeRetrogradeWindows($windows, $periodStart, $periodEnd);
    }

    /**
     * @param  list<array<string, mixed>>  $windows
     * @return list<array<string, mixed>>
     */
    private function finalizeRetrogradeWindows(array $windows, Carbon $periodStart, Carbon $periodEnd): array
    {
        $final = [];

        foreach ($windows as $window) {
            unset($window['_open']);

            if ($window['starts_at'] === null && $window['ends_at'] === null) {
                $window['starts_at'] = $periodStart->toDateString();
                $window['ends_at'] = $periodEnd->toDateString();
            } elseif ($window['ends_at'] === null) {
                $window['ends_at'] = $periodEnd->toDateString();
            }

            $final[] = $window;
        }

        return $final;
    }

    /**
     * @param  list<array<string, mixed>>  $windows
     */
    private function closeRetrogradeWindow(array &$windows, string $planet, string $endsAt): void
    {
        for ($index = count($windows) - 1; $index >= 0; $index--) {
            if (($windows[$index]['planet'] ?? '') !== $planet) {
                continue;
            }

            if (($windows[$index]['_open'] ?? false) !== true) {
                continue;
            }

            $windows[$index]['ends_at'] = $endsAt;
            $windows[$index]['_open'] = false;

            return;
        }
    }

    /**
     * @return array<string, bool>
     */
    private function planetRetrogradeStates(Carbon $date): array
    {
        $payload = $this->calculateChart($this->noonUtcForDate($date));
        $chart = $payload['transit'] ?? $payload['natal'] ?? [];
        $states = [];

        foreach ((array) ($chart['planets'] ?? []) as $planet) {
            $name = (string) ($planet['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $states[$name] = (bool) ($planet['retrograde'] ?? false);
        }

        return $states;
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @return list<array<string, mixed>>
     */
    private function compactPositions(array $chartPayload): array
    {
        $chart = $chartPayload['transit'] ?? $chartPayload['natal'] ?? [];
        $positions = [];

        foreach ((array) ($chart['planets'] ?? []) as $planet) {
            $name = (string) ($planet['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $positions[] = [
                'planet' => $name,
                'sign' => (string) ($planet['sign'] ?? ''),
                'sign_degree' => round((float) ($planet['sign_degree'] ?? 0), 2),
                'retrograde' => (bool) ($planet['retrograde'] ?? false),
            ];
        }

        return $positions;
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateChart(Carbon $chartDatetimeUtc): array
    {
        $location = config('daily_horoscope.location');
        $entry = [
            'datetime_utc' => $chartDatetimeUtc->toIso8601String(),
            'lat' => (float) ($location['lat'] ?? 47.497913),
            'lon' => (float) ($location['lon'] ?? 19.040236),
        ];

        $sidereal = config('daily_horoscope.zodiac_mode') === 'sidereal';

        return $this->calculator->calculate([
            'natal' => $entry,
            'transit' => $entry,
            'sidereal' => $sidereal,
            'ayanamsa' => 'lahiri',
            'house_system' => (string) config('daily_horoscope.house_system', 'placidus'),
        ]);
    }

    private function noonUtcForDate(Carbon $forecastDate): Carbon
    {
        $timezone = (string) config('daily_horoscope.timezone', 'Europe/Budapest');

        return $forecastDate->copy()
            ->timezone($timezone)
            ->setTime(12, 0, 0)
            ->utc();
    }
}
