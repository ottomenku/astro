<?php

namespace App\Services;

use App\Enums\ZodiacSign;

class DailyHoroscopeLlmContextBuilder
{
    private const ASPECT_DEFS = [
        ['type' => 'conjunction', 'angle' => 0, 'orb' => 8.0, 'harmonic' => null],
        ['type' => 'sextile', 'angle' => 60, 'orb' => 6.0, 'harmonic' => true],
        ['type' => 'square', 'angle' => 90, 'orb' => 6.0, 'harmonic' => false],
        ['type' => 'trine', 'angle' => 120, 'orb' => 6.0, 'harmonic' => true],
        ['type' => 'opposition', 'angle' => 180, 'orb' => 8.0, 'harmonic' => false],
    ];

    /** @var array<string, array<string, string>> */
    private const DIGNITIES = [
        'Sun' => ['domicile' => 'Leo', 'exaltation' => 'Aries'],
        'Moon' => ['domicile' => 'Cancer', 'exaltation' => 'Taurus'],
        'Mercury' => ['domicile' => 'Gemini', 'exaltation' => 'Virgo'],
        'Venus' => ['domicile' => 'Taurus', 'exaltation' => 'Pisces'],
        'Mars' => ['domicile' => 'Aries', 'exaltation' => 'Capricorn'],
        'Jupiter' => ['domicile' => 'Sagittarius', 'exaltation' => 'Cancer'],
        'Saturn' => ['domicile' => 'Capricorn', 'exaltation' => 'Libra'],
    ];

    /** @var array<string, string> */
    private const PLANET_ELEMENTS = [
        'Sun' => 'fire',
        'Moon' => 'water',
        'Mercury' => 'air',
        'Venus' => 'earth',
        'Mars' => 'fire',
        'Jupiter' => 'fire',
        'Saturn' => 'earth',
        'Uranus' => 'air',
        'Neptune' => 'water',
        'Pluto' => 'water',
    ];

    /**
     * @param  array<string, mixed>  $chartPayload
     * @return array<string, mixed>
     */
    public function buildChartContext(array $chartPayload, string $locale): array
    {
        $chart = $this->resolveDailyChart($chartPayload);
        if ($chart === []) {
            return ['note' => 'No chart data'];
        }

        $planets = $this->normalizePlanets((array) ($chart['planets'] ?? []));
        $fixedStars = $this->normalizeFixedStars((array) ($chart['fixed_stars'] ?? []));
        $bodiesById = $this->indexBodies(array_merge($planets, $fixedStars));
        $planetAspects = $this->detectAspects($planets, $planets);
        $starAspects = $this->detectAspects($planets, $fixedStars);
        $allAspects = array_merge($planetAspects, $starAspects);
        $patterns = $this->detectPatterns($planetAspects, $planets);
        $rankedAspects = $this->rankAspects($allAspects, $bodiesById, $patterns, $locale);

        return [
            'datetime_utc' => $chart['datetime_utc'] ?? null,
            'significant_placements' => $this->buildSignificantPlacements($planets, $locale),
            'patterns' => $this->translatePatterns($patterns, $locale),
            'aspects' => $rankedAspects,
            'meta' => [
                'houses_excluded' => true,
                'placement_rule' => 'domicile, exaltation, or planet in matching element',
                'aspect_priority' => [
                    1 => 'grand pattern (grand trine, grand cross, sextile triangle)',
                    2 => 'both bodies dignified (domicile or exaltation)',
                    3 => 'one body dignified',
                    4 => 'other aspects within orb',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $attachedPayload
     * @return array<string, mixed>|null
     */
    public function buildAttachedContext(?array $attachedPayload, string $locale): ?array
    {
        if ($attachedPayload === null || $attachedPayload === []) {
            return null;
        }

        $chart = (array) ($attachedPayload['chart'] ?? $attachedPayload);
        $score = (array) ($attachedPayload['score'] ?? []);

        return [
            'source' => $attachedPayload['source'] ?? null,
            'label' => $attachedPayload['label'] ?? null,
            'chart' => $this->buildChartContext($chart, $locale),
            'score_summary' => $this->buildScoreSummary($score),
        ];
    }

    /**
     * @param  array<string, mixed>  $scoreContext
     * @return array<string, mixed>
     */
    public function buildScoreSummary(array $scoreContext): array
    {
        if ($scoreContext === []) {
            return [];
        }

        $summary = [
            'profile' => $scoreContext['profile'] ?? null,
            'engine' => $scoreContext['engine'] ?? null,
            'rating' => $scoreContext['rating_label'] ?? $scoreContext['rating'] ?? null,
            'total_score' => $scoreContext['total_score'] ?? null,
            'polarity' => [
                'positive' => $scoreContext['polarity_positive'] ?? ($scoreContext['polarity']['positive'] ?? null),
                'negative' => $scoreContext['polarity_negative'] ?? ($scoreContext['polarity']['negative'] ?? null),
                'balance' => $scoreContext['polarity_balance'] ?? ($scoreContext['polarity']['balance'] ?? null),
            ],
            'elements' => [
                'fire' => $scoreContext['element_fire'] ?? ($scoreContext['elements']['fire'] ?? null),
                'earth' => $scoreContext['element_earth'] ?? ($scoreContext['elements']['earth'] ?? null),
                'air' => $scoreContext['element_air'] ?? ($scoreContext['elements']['air'] ?? null),
                'water' => $scoreContext['element_water'] ?? ($scoreContext['elements']['water'] ?? null),
            ],
            'modalities' => [
                'cardinal' => $scoreContext['modality_cardinal'] ?? ($scoreContext['modalities']['cardinal'] ?? null),
                'fixed' => $scoreContext['modality_fixed'] ?? ($scoreContext['modalities']['fixed'] ?? null),
                'mutable' => $scoreContext['modality_mutable'] ?? ($scoreContext['modalities']['mutable'] ?? null),
            ],
        ];

        $breakdown = (array) ($scoreContext['breakdown'] ?? []);
        if ($breakdown !== []) {
            $summary['activity_index'] = $breakdown['activity_index'] ?? null;
            $summary['polarity_classification'] = $breakdown['polarity_classification'] ?? null;
            $summary['element_classification'] = $breakdown['element_classification'] ?? null;
            $summary['modality_classification'] = $breakdown['modality_classification'] ?? null;
        }

        return array_filter($summary, fn ($value) => $value !== null && $value !== []);
    }

    /**
     * @param  array<string, mixed>  $chartPayload
     * @return array<string, mixed>
     */
    private function resolveDailyChart(array $chartPayload): array
    {
        if (isset($chartPayload['transit']) && is_array($chartPayload['transit'])) {
            return $chartPayload['transit'];
        }

        if (isset($chartPayload['natal']) && is_array($chartPayload['natal'])) {
            return $chartPayload['natal'];
        }

        if (isset($chartPayload['planets'])) {
            return $chartPayload;
        }

        return [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $planets
     * @return array<int, array<string, mixed>>
     */
    private function normalizePlanets(array $planets): array
    {
        $normalized = [];

        foreach ($planets as $planet) {
            $name = (string) ($planet['name'] ?? '');
            if ($name === '' || in_array($name, ['Asc', 'MC', 'Mc'], true)) {
                continue;
            }

            $sign = (string) ($planet['sign'] ?? '');
            if ($sign === '') {
                continue;
            }

            $normalized[] = [
                'id' => $name,
                'name' => $name,
                'kind' => 'planet',
                'sign' => $sign,
                'sign_degree' => round((float) ($planet['sign_degree'] ?? 0), 2),
                'longitude' => (float) ($planet['longitude'] ?? 0),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array<string, mixed>>  $stars
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFixedStars(array $stars): array
    {
        $normalized = [];

        foreach ($stars as $star) {
            $name = (string) ($star['name'] ?? $star['id'] ?? '');
            $sign = (string) ($star['sign'] ?? '');
            if ($name === '' || $sign === '') {
                continue;
            }

            $normalized[] = [
                'id' => $name,
                'name' => $name,
                'kind' => 'fixed_star',
                'sign' => $sign,
                'sign_degree' => round((float) ($star['sign_degree'] ?? 0), 2),
                'longitude' => (float) ($star['longitude'] ?? 0),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array<string, mixed>>  $leftBodies
     * @param  array<int, array<string, mixed>>  $rightBodies
     * @return array<int, array<string, mixed>>
     */
    private function detectAspects(array $leftBodies, array $rightBodies): array
    {
        $aspects = [];

        foreach ($leftBodies as $i => $left) {
            foreach ($rightBodies as $j => $right) {
                if ($left['kind'] === 'planet' && $right['kind'] === 'planet' && $i >= $j) {
                    continue;
                }

                if ($left['id'] === $right['id']) {
                    continue;
                }

                $delta = $this->angleDelta((float) $left['longitude'], (float) $right['longitude']);

                foreach (self::ASPECT_DEFS as $definition) {
                    $orb = abs($delta - (float) $definition['angle']);
                    if ($orb <= (float) $definition['orb']) {
                        $aspects[] = [
                            'body1_id' => $left['id'],
                            'body2_id' => $right['id'],
                            'body1_kind' => $left['kind'],
                            'body2_kind' => $right['kind'],
                            'type' => $definition['type'],
                            'angle' => $definition['angle'],
                            'orb' => round($orb, 3),
                            'harmonic' => $definition['harmonic'],
                        ];
                        break;
                    }
                }
            }
        }

        return $aspects;
    }

    /**
     * @param  array<int, array<string, mixed>>  $planets
     * @return array<int, array<string, mixed>>
     */
    private function buildSignificantPlacements(array $planets, string $locale): array
    {
        $placements = [];

        foreach ($planets as $planet) {
            $reasons = $this->powerReasons((string) $planet['name'], (string) $planet['sign']);
            if ($reasons === []) {
                continue;
            }

            $placements[] = [
                'planet' => $this->translatePlanet((string) $planet['name'], $locale),
                'sign' => $this->translateSign((string) $planet['sign'], $locale),
                'sign_degree' => $planet['sign_degree'],
                'strength' => $reasons,
            ];
        }

        return $placements;
    }

    /**
     * @return array<int, string>
     */
    private function powerReasons(string $planetName, string $signName): array
    {
        $reasons = [];
        $dignity = $this->resolveDignity($planetName, $signName);

        if ($dignity === 'domicile') {
            $reasons[] = 'domicile';
        } elseif ($dignity === 'exaltation') {
            $reasons[] = 'exaltation';
        }

        $sign = ZodiacSign::tryFromName($signName);
        $planetElement = self::PLANET_ELEMENTS[$planetName] ?? null;
        if ($sign && $planetElement && $sign->element() === $planetElement && ! in_array('domicile', $reasons, true)) {
            $reasons[] = 'own_element';
        }

        return $reasons;
    }

    private function resolveDignity(string $planetName, string $signName): string
    {
        $rules = self::DIGNITIES[$planetName] ?? null;
        if (! $rules) {
            return 'neutral';
        }

        if (($rules['domicile'] ?? null) === $signName) {
            return 'domicile';
        }

        if ($planetName === 'Mercury' && $signName === 'Virgo') {
            return 'domicile';
        }

        if (($rules['exaltation'] ?? null) === $signName) {
            return 'exaltation';
        }

        return 'neutral';
    }

    private function isDignified(string $planetName, string $signName): bool
    {
        $dignity = $this->resolveDignity($planetName, $signName);

        return in_array($dignity, ['domicile', 'exaltation'], true);
    }

    /**
     * @param  array<int, array<string, mixed>>  $planetAspects
     * @param  array<int, array<string, mixed>>  $planets
     * @return array<int, array<string, mixed>>
     */
    private function detectPatterns(array $planetAspects, array $planets): array
    {
        $patterns = [];
        $planetNames = array_map(fn (array $planet) => (string) $planet['name'], $planets);
        $aspectMap = $this->aspectPairMap($planetAspects);

        $patterns = array_merge($patterns, $this->detectMutualAspectTriangles($planetNames, $aspectMap, 'trine', 'grand_trine'));
        $patterns = array_merge($patterns, $this->detectMutualAspectTriangles($planetNames, $aspectMap, 'sextile', 'sextile_triangle'));
        $patterns = array_merge($patterns, $this->detectGrandCrosses($planetNames, $aspectMap));

        return $patterns;
    }

    /**
     * @param  array<int, string>  $planetNames
     * @param  array<string, string>  $aspectMap
     * @return array<int, array<string, mixed>>
     */
    private function detectMutualAspectTriangles(array $planetNames, array $aspectMap, string $aspectType, string $patternType): array
    {
        $patterns = [];
        $count = count($planetNames);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                for ($k = $j + 1; $k < $count; $k++) {
                    $a = $planetNames[$i];
                    $b = $planetNames[$j];
                    $c = $planetNames[$k];

                    if (
                        $this->pairAspect($aspectMap, $a, $b) === $aspectType
                        && $this->pairAspect($aspectMap, $b, $c) === $aspectType
                        && $this->pairAspect($aspectMap, $a, $c) === $aspectType
                    ) {
                        $patterns[] = [
                            'type' => $patternType,
                            'planets' => [$a, $b, $c],
                            'aspect' => $aspectType,
                        ];
                    }
                }
            }
        }

        return $patterns;
    }

    /**
     * @param  array<int, string>  $planetNames
     * @param  array<string, string>  $aspectMap
     * @return array<int, array<string, mixed>>
     */
    private function detectGrandCrosses(array $planetNames, array $aspectMap): array
    {
        $patterns = [];
        $count = count($planetNames);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                for ($k = 0; $k < $count; $k++) {
                    for ($l = $k + 1; $l < $count; $l++) {
                        $names = [$planetNames[$i], $planetNames[$j], $planetNames[$k], $planetNames[$l]];
                        if (count(array_unique($names)) !== 4) {
                            continue;
                        }

                        [$a, $b, $c, $d] = $names;

                        if (
                            $this->pairAspect($aspectMap, $a, $b) === 'opposition'
                            && $this->pairAspect($aspectMap, $c, $d) === 'opposition'
                            && $this->pairAspect($aspectMap, $a, $c) === 'square'
                            && $this->pairAspect($aspectMap, $a, $d) === 'square'
                            && $this->pairAspect($aspectMap, $b, $c) === 'square'
                            && $this->pairAspect($aspectMap, $b, $d) === 'square'
                        ) {
                            $patterns[] = [
                                'type' => 'grand_cross',
                                'planets' => $names,
                                'oppositions' => [[$a, $b], [$c, $d]],
                            ];
                        }
                    }
                }
            }
        }

        return $patterns;
    }

    /**
     * @param  array<int, array<string, mixed>>  $aspects
     * @return array<string, string>
     */
    private function aspectPairMap(array $aspects): array
    {
        $map = [];

        foreach ($aspects as $aspect) {
            $key = $this->pairKey((string) $aspect['body1_id'], (string) $aspect['body2_id']);
            $map[$key] = (string) $aspect['type'];
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $aspectMap
     */
    private function pairAspect(array $aspectMap, string $left, string $right): ?string
    {
        return $aspectMap[$this->pairKey($left, $right)] ?? null;
    }

    private function pairKey(string $left, string $right): string
    {
        $sorted = [$left, $right];
        sort($sorted);

        return implode('|', $sorted);
    }

    /**
     * @param  array<int, array<string, mixed>>  $bodies
     * @return array<string, array<string, mixed>>
     */
    private function indexBodies(array $bodies): array
    {
        $indexed = [];

        foreach ($bodies as $body) {
            $indexed[(string) $body['id']] = $body;
        }

        return $indexed;
    }

    /**
     * @param  array<int, array<string, mixed>>  $aspects
     * @param  array<string, array<string, mixed>>  $bodiesById
     * @param  array<int, array<string, mixed>>  $patterns
     * @return array<int, array<string, mixed>>
     */
    private function rankAspects(array $aspects, array $bodiesById, array $patterns, string $locale): array
    {
        $patternPairs = [];
        foreach ($patterns as $pattern) {
            $members = (array) ($pattern['planets'] ?? []);
            for ($i = 0; $i < count($members); $i++) {
                for ($j = $i + 1; $j < count($members); $j++) {
                    $patternPairs[$this->pairKey((string) $members[$i], (string) $members[$j])] = (string) ($pattern['type'] ?? 'pattern');
                }
            }
        }

        $ranked = [];

        foreach ($aspects as $aspect) {
            $body1 = $bodiesById[(string) $aspect['body1_id']] ?? null;
            $body2 = $bodiesById[(string) $aspect['body2_id']] ?? null;

            if (! $body1 || ! $body2) {
                continue;
            }

            $pairKey = $this->pairKey((string) $body1['id'], (string) $body2['id']);
            $priority = 4;
            $priorityReason = 'standard';

            if (isset($patternPairs[$pairKey])) {
                $priority = 1;
                $priorityReason = $patternPairs[$pairKey];
            } else {
                $body1Dignified = $body1['kind'] === 'planet' && $this->isDignified((string) $body1['name'], (string) $body1['sign']);
                $body2Dignified = $body2['kind'] === 'planet' && $this->isDignified((string) $body2['name'], (string) $body2['sign']);

                if ($body1Dignified && $body2Dignified) {
                    $priority = 2;
                    $priorityReason = 'both_dignified';
                } elseif ($body1Dignified || $body2Dignified) {
                    $priority = 3;
                    $priorityReason = 'one_dignified';
                }
            }

            $ranked[] = [
                'priority' => $priority,
                'priority_reason' => $priorityReason,
                'type' => $this->translateAspectType((string) $aspect['type'], $locale),
                'type_key' => $aspect['type'],
                'orb' => $aspect['orb'],
                'harmonic' => $aspect['harmonic'],
                'description' => $this->aspectDescription($body1, $body2, (string) $aspect['type'], $locale),
                'body1' => $this->bodyLabel($body1, $locale),
                'body2' => $this->bodyLabel($body2, $locale),
            ];
        }

        usort($ranked, function (array $left, array $right): int {
            if ($left['priority'] !== $right['priority']) {
                return $left['priority'] <=> $right['priority'];
            }

            return ($left['orb'] ?? 99) <=> ($right['orb'] ?? 99);
        });

        return $ranked;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function bodyLabel(array $body, string $locale): array
    {
        if ($body['kind'] === 'fixed_star') {
            return [
                'name' => $this->translateFixedStar((string) $body['name'], $locale),
                'kind' => 'fixed_star',
            ];
        }

        return [
            'name' => $this->translatePlanet((string) $body['name'], $locale),
            'sign' => $this->translateSign((string) $body['sign'], $locale),
            'kind' => 'planet',
        ];
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private function aspectDescription(array $left, array $right, string $type, string $locale): string
    {
        $leftLabel = $left['kind'] === 'fixed_star'
            ? $this->translateFixedStar((string) $left['name'], $locale)
            : sprintf('%s (%s)', $this->translatePlanet((string) $left['name'], $locale), $this->translateSign((string) $left['sign'], $locale));

        $rightLabel = $right['kind'] === 'fixed_star'
            ? $this->translateFixedStar((string) $right['name'], $locale)
            : sprintf('%s (%s)', $this->translatePlanet((string) $right['name'], $locale), $this->translateSign((string) $right['sign'], $locale));

        return sprintf(
            '%s %s %s',
            $leftLabel,
            $this->translateAspectType($type, $locale),
            $rightLabel,
        );
    }

    private function translatePlanet(string $name, string $locale): string
    {
        if ($locale !== 'hu') {
            return $name;
        }

        return (string) (__('horoscope.js.planets.'.$name, [], $locale) ?: $name);
    }

    private function translateFixedStar(string $name, string $locale): string
    {
        if ($locale !== 'hu') {
            return $name;
        }

        $translated = __('horoscope.js.fixed_stars.'.$name, [], $locale);

        return $translated === 'horoscope.js.fixed_stars.'.$name ? $name : (string) $translated;
    }

    private function translateSign(string $sign, string $locale): string
    {
        if ($locale !== 'hu') {
            return $sign;
        }

        $signs = (array) __('horoscope.js.signs', [], $locale);
        $english = [
            'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
            'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces',
        ];
        $index = array_search($sign, $english, true);

        return $index === false ? $sign : (string) ($signs[$index] ?? $sign);
    }

    private function translateAspectType(string $type, string $locale): string
    {
        $labels = [
            'hu' => [
                'conjunction' => 'együttállásban áll',
                'sextile' => 'szextilben áll',
                'square' => 'kvadrátban áll',
                'trine' => 'trigonban áll',
                'opposition' => 'szemben áll',
            ],
            'en' => [
                'conjunction' => 'conjunct',
                'sextile' => 'sextile',
                'square' => 'square',
                'trine' => 'trine',
                'opposition' => 'opposite',
            ],
        ];

        return $labels[$locale][$type] ?? $labels['en'][$type] ?? $type;
    }

    private function angleDelta(float $left, float $right): float
    {
        $delta = abs(fmod($left, 360) - fmod($right, 360));

        return min($delta, 360 - $delta);
    }

    /**
     * @param  array<int, array<string, mixed>>  $patterns
     * @return array<int, array<string, mixed>>
     */
    private function translatePatterns(array $patterns, string $locale): array
    {
        $labels = [
            'hu' => [
                'grand_trine' => 'nagy trigon',
                'grand_cross' => 'nagy kereszt',
                'sextile_triangle' => 'szextil-háromszög',
            ],
            'en' => [
                'grand_trine' => 'grand trine',
                'grand_cross' => 'grand cross',
                'sextile_triangle' => 'sextile triangle',
            ],
        ];

        return array_map(function (array $pattern) use ($locale, $labels): array {
            $type = (string) ($pattern['type'] ?? '');
            $pattern['label'] = $labels[$locale][$type] ?? $labels['en'][$type] ?? $type;
            $pattern['planets'] = array_map(
                fn (string $name) => $this->translatePlanet($name, $locale),
                (array) ($pattern['planets'] ?? []),
            );

            return $pattern;
        }, $patterns);
    }
}
