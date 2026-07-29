<?php

namespace App\Services;

use App\Enums\ZodiacSign;
use App\Support\HoroscopeTopicCatalog;

class DailyHoroscopeLlmContextBuilder
{
    public const DEFAULT_MAX_SIGNALS = 6;
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
        $starConjunctions = $this->detectStarConjunctions($planets, $fixedStars);
        $patterns = $this->detectPatterns($planetAspects, $planets);
        $aspectSignals = $this->buildAspectSignals(
            $planetAspects,
            $starConjunctions,
            $bodiesById,
            $patterns,
            $locale,
        );

        return [
            'datetime_utc' => $chart['datetime_utc'] ?? null,
            'significant_placements' => $this->buildSignificantPlacements($planets, $locale),
            'patterns' => $this->translatePatterns($patterns, $locale),
            'aspect_signals' => $aspectSignals,
            'meta' => [
                'houses_excluded' => true,
                'placement_rule' => 'domicile, exaltation, or planet in matching element',
                'fixed_star_aspects' => 'conjunction only',
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
            'gender' => $attachedPayload['gender'] ?? null,
            'chart' => $this->buildChartContext($chart, $locale),
            'score_summary' => $this->buildScoreSummary($score),
        ];
    }

    /**
     * @param  array<string, mixed>  $chartPayloadA
     * @param  array<string, mixed>  $chartPayloadB
     * @return array<string, mixed>
     */
    public function buildSynastryContext(array $chartPayloadA, array $chartPayloadB, string $locale): array
    {
        $planetsA = $this->normalizePlanets((array) ($this->resolveDailyChart($chartPayloadA)['planets'] ?? []));
        $planetsB = $this->normalizePlanets((array) ($this->resolveDailyChart($chartPayloadB)['planets'] ?? []));

        $aspects = $this->detectAspects($planetsA, $planetsB);

        return [
            'pairwise' => array_map(fn (array $aspect) => [
                'body1' => $aspect['body1_id'],
                'body2' => $aspect['body2_id'],
                'type' => $aspect['type'],
                'harmonic' => $aspect['harmonic'],
                'orb' => $aspect['orb'],
            ], $aspects),
            'count' => count($aspects),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $chartPayloadA
     * @param  array<string, mixed>|null  $chartPayloadB
     * @return array<string, mixed>|null
     */
    public function buildPartnershipContext(?array $chartPayloadA, ?array $chartPayloadB, string $locale): ?array
    {
        if ($chartPayloadA === null || $chartPayloadB === null) {
            return null;
        }

        $chartA = (array) ($chartPayloadA['chart'] ?? []);
        $chartB = (array) ($chartPayloadB['chart'] ?? []);

        return [
            'chart_a' => $this->buildAttachedContext($chartPayloadA, $locale),
            'chart_b' => $this->buildAttachedContext($chartPayloadB, $locale),
            'synastry' => $this->buildSynastryContext($chartA, $chartB, $locale),
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
     * @param  array<string, mixed>  $scoreContext
     * @param  list<string>  $topics
     * @return array<string, mixed>
     */
    public function buildCompactChartContext(
        array $chartPayload,
        array $scoreContext,
        string $locale,
        array $topics = [],
        int $maxSignals = self::DEFAULT_MAX_SIGNALS,
    ): array {
        $full = $this->buildChartContext($chartPayload, $locale);
        $pairwise = (array) data_get($full, 'aspect_signals.pairwise', []);
        $topics = HoroscopeTopicCatalog::normalizeTopics($topics);

        return [
            'datetime_utc' => $full['datetime_utc'] ?? null,
            'score_summary' => $this->buildScoreSummary($scoreContext),
            'top_signals' => $this->selectTopSignals($pairwise, $topics, $maxSignals),
            'topics' => array_map(
                fn (string $topic) => HoroscopeTopicCatalog::label($topic, $locale),
                $topics,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $attachedPayload
     * @param  list<string>  $topics
     * @return array<string, mixed>|null
     */
    public function buildCompactAttachedContext(
        ?array $attachedPayload,
        string $locale,
        array $topics = [],
        int $maxSignals = self::DEFAULT_MAX_SIGNALS,
    ): ?array {
        if ($attachedPayload === null || $attachedPayload === []) {
            return null;
        }

        $chart = (array) ($attachedPayload['chart'] ?? $attachedPayload);
        $score = (array) ($attachedPayload['score'] ?? []);

        return [
            'label' => $attachedPayload['label'] ?? null,
            'gender' => $attachedPayload['gender'] ?? null,
            'chart' => $this->buildCompactChartContext($chart, $score, $locale, $topics, $maxSignals),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $chartPayloadA
     * @param  array<string, mixed>|null  $chartPayloadB
     * @param  list<string>  $topics
     * @return array<string, mixed>|null
     */
    public function buildCompactPartnershipContext(
        ?array $chartPayloadA,
        ?array $chartPayloadB,
        string $locale,
        array $topics = [],
        int $maxSignals = self::DEFAULT_MAX_SIGNALS,
    ): ?array {
        if ($chartPayloadA === null || $chartPayloadB === null) {
            return null;
        }

        return [
            'chart_a' => $this->buildCompactAttachedContext($chartPayloadA, $locale, $topics, $maxSignals),
            'chart_b' => $this->buildCompactAttachedContext($chartPayloadB, $locale, $topics, $maxSignals),
            'synastry_top_signals' => $this->buildCompactSynastrySignals($chartPayloadA, $chartPayloadB, $locale, $topics, $maxSignals),
        ];
    }

    /**
     * @param  array<string, mixed>  $chartPayloadA
     * @param  array<string, mixed>  $chartPayloadB
     * @param  list<string>  $topics
     * @return list<array<string, mixed>>
     */
    public function buildCompactSynastrySignals(
        array $chartPayloadA,
        array $chartPayloadB,
        string $locale,
        array $topics = [],
        int $maxSignals = self::DEFAULT_MAX_SIGNALS,
    ): array {
        $chartA = (array) ($chartPayloadA['chart'] ?? $chartPayloadA);
        $chartB = (array) ($chartPayloadB['chart'] ?? $chartPayloadB);
        $planetsA = $this->normalizePlanets((array) ($this->resolveDailyChart($chartA)['planets'] ?? []));
        $planetsB = $this->normalizePlanets((array) ($this->resolveDailyChart($chartB)['planets'] ?? []));
        $aspects = $this->detectAspects($planetsA, $planetsB);
        $bodiesById = $this->indexBodies(array_merge($planetsA, $planetsB));
        $scored = $this->scoreAspects($aspects, $bodiesById, []);
        $pairwise = $this->buildPairwiseAspects($scored, $bodiesById, $locale);

        return $this->selectTopSignals($pairwise, HoroscopeTopicCatalog::normalizeTopics($topics), $maxSignals);
    }

    /**
     * @param  array<int, array<string, mixed>>  $pairwise
     * @param  list<string>  $topics
     * @return list<array<string, mixed>>
     */
    private function selectTopSignals(array $pairwise, array $topics, int $maxSignals): array
    {
        if ($pairwise === []) {
            return [];
        }

        $topics = HoroscopeTopicCatalog::normalizeTopics($topics);
        $specificTopics = count($topics) < count(HoroscopeTopicCatalog::ALL);

        foreach ($pairwise as $index => $signal) {
            $body1 = (string) data_get($signal, 'body1.id', '');
            $body2 = (string) data_get($signal, 'body2.id', '');
            $topicScore = 0;

            if ($specificTopics) {
                $matchesA = HoroscopeTopicCatalog::bodyMatchesTopics($body1, $topics);
                $matchesB = HoroscopeTopicCatalog::bodyMatchesTopics($body2, $topics);

                if ($matchesA && $matchesB) {
                    $topicScore = 3;
                } elseif ($matchesA || $matchesB) {
                    $topicScore = 2;
                }
            }

            $pairwise[$index]['_rank'] = ($topicScore * 1000)
                + ((5 - (int) ($signal['priority'] ?? 4)) * 100)
                - (float) ($signal['orb'] ?? 0);
        }

        usort($pairwise, fn (array $left, array $right): int => ($right['_rank'] ?? 0) <=> ($left['_rank'] ?? 0));

        if ($specificTopics) {
            $matching = array_values(array_filter(
                $pairwise,
                static fn (array $signal): bool => ($signal['_rank'] ?? 0) >= 2000,
            ));
            $selected = array_slice($matching, 0, $maxSignals);

            if (count($selected) < $maxSignals) {
                $selectedKeys = array_map(static fn (array $signal): string => (string) ($signal['description'] ?? ''), $selected);
                foreach ($pairwise as $signal) {
                    $description = (string) ($signal['description'] ?? '');
                    if (in_array($description, $selectedKeys, true)) {
                        continue;
                    }
                    $selected[] = $signal;
                    $selectedKeys[] = $description;
                    if (count($selected) >= $maxSignals) {
                        break;
                    }
                }
            }

            $pairwise = $selected;
        } else {
            $pairwise = array_slice($pairwise, 0, $maxSignals);
        }

        return array_values(array_map(static fn (array $signal): array => [
            'description' => $signal['description'] ?? null,
            'priority' => $signal['priority'] ?? null,
            'type' => $signal['type'] ?? null,
            'orb' => $signal['orb'] ?? null,
        ], $pairwise));
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
                'retrograde' => (bool) ($planet['retrograde'] ?? false),
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
     * Állócsillagokkal csak együttállás.
     *
     * @param  array<int, array<string, mixed>>  $planets
     * @param  array<int, array<string, mixed>>  $stars
     * @return array<int, array<string, mixed>>
     */
    private function detectStarConjunctions(array $planets, array $stars): array
    {
        $aspects = [];

        foreach ($planets as $planet) {
            foreach ($stars as $star) {
                $delta = $this->angleDelta((float) $planet['longitude'], (float) $star['longitude']);
                $orb = abs($delta - 0.0);
                if ($orb <= 8.0) {
                    $aspects[] = [
                        'body1_id' => $planet['id'],
                        'body2_id' => $star['id'],
                        'body1_kind' => 'planet',
                        'body2_kind' => 'fixed_star',
                        'type' => 'conjunction',
                        'angle' => 0,
                        'orb' => round($orb, 3),
                        'harmonic' => null,
                    ];
                }
            }
        }

        return $aspects;
    }

    /**
     * @param  array<int, array<string, mixed>>  $planetAspects
     * @param  array<int, array<string, mixed>>  $starConjunctions
     * @param  array<string, array<string, mixed>>  $bodiesById
     * @param  array<int, array<string, mixed>>  $patterns
     * @return array<string, mixed>
     */
    private function buildAspectSignals(
        array $planetAspects,
        array $starConjunctions,
        array $bodiesById,
        array $patterns,
        string $locale,
    ): array {
        $allAspects = array_merge($planetAspects, $starConjunctions);
        $scoredAspects = $this->scoreAspects($allAspects, $bodiesById, $patterns);

        return [
            'participation_summary' => $this->buildParticipationSummary($scoredAspects, $bodiesById),
            'conjunction_groups' => $this->buildConjunctionGroups($scoredAspects, $bodiesById, $locale),
            'anchor_configurations' => $this->buildAnchorConfigurations($scoredAspects, $bodiesById, $locale),
            'pairwise' => $this->buildPairwiseAspects($scoredAspects, $bodiesById, $locale),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $aspects
     * @param  array<string, array<string, mixed>>  $bodiesById
     * @param  array<int, array<string, mixed>>  $patterns
     * @return array<int, array<string, mixed>>
     */
    private function scoreAspects(array $aspects, array $bodiesById, array $patterns): array
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

        $scored = [];

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

            $scored[] = array_merge($aspect, [
                'body1' => $body1,
                'body2' => $body2,
                'priority' => $priority,
                'priority_reason' => $priorityReason,
                'weight' => round($this->aspectWeight($priority, (float) $aspect['orb']), 3),
            ]);
        }

        return $scored;
    }

    private function aspectWeight(int $priority, float $orb): float
    {
        $priorityWeight = match ($priority) {
            1 => 1.0,
            2 => 0.75,
            3 => 0.5,
            default => 0.25,
        };
        $orbTightness = max(0.25, 1 - min($orb, 8.0) / 8.0);

        return $priorityWeight * $orbTightness;
    }

    /**
     * @param  array<int, array<string, mixed>>  $scoredAspects
     * @param  array<string, array<string, mixed>>  $bodiesById
     * @return array<string, mixed>
     */
    private function buildParticipationSummary(array $scoredAspects, array $bodiesById): array
    {
        $polarity = ['positive' => 0.0, 'negative' => 0.0];
        $elements = ['fire' => 0.0, 'earth' => 0.0, 'air' => 0.0, 'water' => 0.0];
        $modalities = ['cardinal' => 0.0, 'fixed' => 0.0, 'mutable' => 0.0];
        $counted = [];

        foreach ($scoredAspects as $aspect) {
            foreach (['body1', 'body2'] as $slot) {
                $body = $aspect[$slot];
                $bodyKey = (string) $body['id'];
                if (isset($counted[$bodyKey])) {
                    continue;
                }
                $counted[$bodyKey] = true;

                $weight = (float) $aspect['weight'];
                $sign = ZodiacSign::tryFromName((string) $body['sign']);
                if (! $sign) {
                    continue;
                }

                $elements[$sign->element()] += $weight;
                $modalities[$sign->modality()] += $weight;
                if ($sign->polarity() === 'positive') {
                    $polarity['positive'] += $weight;
                } else {
                    $polarity['negative'] += $weight;
                }
            }
        }

        foreach ($elements as $key => $value) {
            $elements[$key] = round($value, 2);
        }
        foreach ($modalities as $key => $value) {
            $modalities[$key] = round($value, 2);
        }
        foreach ($polarity as $key => $value) {
            $polarity[$key] = round($value, 2);
        }

        return [
            'polarity' => $polarity,
            'elements' => $elements,
            'modalities' => $modalities,
            'dominant' => [
                'polarity' => $this->dominantKey($polarity),
                'element' => $this->dominantKey($elements),
                'modality' => $this->dominantKey($modalities),
            ],
        ];
    }

    /**
     * @param  array<string, float>  $values
     */
    private function dominantKey(array $values): ?string
    {
        if ($values === []) {
            return null;
        }

        arsort($values);

        return (string) array_key_first($values);
    }

    /**
     * @param  array<int, array<string, mixed>>  $scoredAspects
     * @param  array<string, array<string, mixed>>  $bodiesById
     * @return array<int, array<string, mixed>>
     */
    private function buildConjunctionGroups(array $scoredAspects, array $bodiesById, string $locale): array
    {
        $conjunctions = array_values(array_filter(
            $scoredAspects,
            fn (array $aspect) => ($aspect['type'] ?? '') === 'conjunction',
        ));

        if ($conjunctions === []) {
            return [];
        }

        $parent = [];
        foreach ($bodiesById as $id => $_body) {
            $parent[$id] = $id;
        }

        $find = function (string $id) use (&$parent, &$find): string {
            if ($parent[$id] !== $id) {
                $parent[$id] = $find($parent[$id]);
            }

            return $parent[$id];
        };

        $union = function (string $left, string $right) use (&$parent, $find): void {
            $rootLeft = $find($left);
            $rootRight = $find($right);
            if ($rootLeft !== $rootRight) {
                $parent[$rootRight] = $rootLeft;
            }
        };

        foreach ($conjunctions as $aspect) {
            $union((string) $aspect['body1_id'], (string) $aspect['body2_id']);
        }

        $groups = [];
        foreach ($conjunctions as $aspect) {
            $root = $find((string) $aspect['body1_id']);
            $groups[$root]['members'][(string) $aspect['body1_id']] = $aspect['body1'];
            $groups[$root]['members'][(string) $aspect['body2_id']] = $aspect['body2'];
            $groups[$root]['priority'] = min($groups[$root]['priority'] ?? 99, (int) $aspect['priority']);
            $groups[$root]['max_orb'] = max($groups[$root]['max_orb'] ?? 0, (float) $aspect['orb']);
        }

        $result = [];
        foreach ($groups as $group) {
            $members = array_values($group['members']);
            if (count($members) < 2) {
                continue;
            }

            $memberLabels = array_map(function (array $body) use ($locale): string {
                if ($body['kind'] === 'fixed_star') {
                    return $this->translateFixedStar((string) $body['name'], $locale);
                }

                return sprintf(
                    '%s (%s)',
                    $this->translatePlanet((string) $body['name'], $locale),
                    $this->translateSign((string) $body['sign'], $locale),
                );
            }, $members);

            $result[] = [
                'priority' => $group['priority'],
                'member_count' => count($members),
                'members' => array_map(fn (array $body) => $this->compactBody($body, $locale), $members),
                'description' => implode(' + ', $memberLabels),
            ];
        }

        usort($result, fn (array $a, array $b) => [$a['priority'], -$a['member_count']] <=> [$b['priority'], -$b['member_count']]);

        return $result;
    }

    /**
     * @param  array<int, array<string, mixed>>  $scoredAspects
     * @param  array<string, array<string, mixed>>  $bodiesById
     * @return array<int, array<string, mixed>>
     */
    private function buildAnchorConfigurations(array $scoredAspects, array $bodiesById, string $locale): array
    {
        $byAnchor = [];

        foreach ($scoredAspects as $aspect) {
            if (($aspect['body1_kind'] ?? '') !== 'planet') {
                continue;
            }

            $anchorId = (string) $aspect['body1_id'];
            $byAnchor[$anchorId]['anchor'] = $aspect['body1'];
            $byAnchor[$anchorId]['relations'][] = [
                'aspect' => $aspect,
                'other' => $aspect['body2'],
                'other_slot' => 'body2',
            ];

            if (($aspect['body2_kind'] ?? '') === 'planet') {
                $reverseId = (string) $aspect['body2_id'];
                $byAnchor[$reverseId]['anchor'] = $aspect['body2'];
                $byAnchor[$reverseId]['relations'][] = [
                    'aspect' => $aspect,
                    'other' => $aspect['body1'],
                    'other_slot' => 'body1',
                ];
            }
        }

        $configurations = [];

        foreach ($byAnchor as $anchorId => $data) {
            $relations = (array) ($data['relations'] ?? []);
            if ($relations === []) {
                continue;
            }

            $anchor = (array) $data['anchor'];
            $priority = min(array_map(fn (array $rel) => (int) $rel['aspect']['priority'], $relations));
            $formattedRelations = [];
            $relationIndex = 2;
            $descriptionParts = [];

            $anchorLabel = sprintf(
                '%s (%s)',
                $this->translatePlanet((string) $anchor['name'], $locale),
                $this->translateSign((string) $anchor['sign'], $locale),
            );

            usort($relations, function (array $left, array $right): int {
                if ($left['aspect']['priority'] !== $right['aspect']['priority']) {
                    return $left['aspect']['priority'] <=> $right['aspect']['priority'];
                }

                return ($left['aspect']['orb'] ?? 99) <=> ($right['aspect']['orb'] ?? 99);
            });

            foreach ($relations as $relation) {
                $aspect = $relation['aspect'];
                $other = $relation['other'];
                $slot = $relationIndex;

                $entry = [
                    'body'.$slot => $this->bodyId((string) $other['name']),
                    'type'.$slot => (string) $aspect['type'],
                    'orb'.$slot => $aspect['orb'],
                    'priority'.$slot => $aspect['priority'],
                    'kind'.$slot => $other['kind'],
                ];

                if ($other['kind'] === 'planet') {
                    $entry['sign'.$slot] = $this->translateSign((string) $other['sign'], $locale);
                }

                $formattedRelations[] = $entry;
                $relationIndex++;

                $otherLabel = $other['kind'] === 'fixed_star'
                    ? $this->translateFixedStar((string) $other['name'], $locale)
                    : sprintf(
                        '%s (%s)',
                        $this->translatePlanet((string) $other['name'], $locale),
                        $this->translateSign((string) $other['sign'], $locale),
                    );

                $descriptionParts[] = sprintf(
                    '%s %s',
                    $this->translateAspectType((string) $aspect['type'], $locale),
                    $otherLabel,
                );
            }

            $configuration = [
                'body1' => $this->bodyId((string) $anchor['name']),
                'sign1' => $this->translateSign((string) $anchor['sign'], $locale),
                'priority' => $priority,
                'relation_count' => count($formattedRelations),
                'description' => $anchorLabel.' — '.implode('; ', $descriptionParts),
            ];

            foreach ($formattedRelations as $relationEntry) {
                $configuration = array_merge($configuration, $relationEntry);
            }

            $configurations[] = $configuration;
        }

        usort($configurations, function (array $left, array $right): int {
            if ($left['priority'] !== $right['priority']) {
                return $left['priority'] <=> $right['priority'];
            }

            return ($right['relation_count'] ?? 0) <=> ($left['relation_count'] ?? 0);
        });

        return array_values(array_filter(
            $configurations,
            function (array $config): bool {
                $relationCount = 0;
                for ($index = 2; $index <= 12; $index++) {
                    if (isset($config['body'.$index])) {
                        $relationCount++;
                    }
                }

                if ($relationCount >= 2) {
                    return true;
                }

                if (($config['priority'] ?? 99) <= 3) {
                    return true;
                }

                for ($index = 2; $index <= 12; $index++) {
                    if (($config['kind'.$index] ?? '') === 'fixed_star') {
                        return true;
                    }
                }

                return false;
            },
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $scoredAspects
     * @return array<int, array<string, mixed>>
     */
    private function buildPairwiseAspects(array $scoredAspects, array $bodiesById, string $locale): array
    {
        $pairwise = [];

        foreach ($scoredAspects as $aspect) {
            $body1 = $aspect['body1'];
            $body2 = $aspect['body2'];

            $pairwise[] = [
                'priority' => $aspect['priority'],
                'priority_reason' => $aspect['priority_reason'],
                'type' => $this->translateAspectType((string) $aspect['type'], $locale),
                'type_key' => $aspect['type'],
                'orb' => $aspect['orb'],
                'harmonic' => $aspect['harmonic'],
                'body1' => $this->compactBody($body1, $locale),
                'body2' => $this->compactBody($body2, $locale),
                'description' => $this->aspectDescription($body1, $body2, (string) $aspect['type'], $locale),
            ];
        }

        usort($pairwise, function (array $left, array $right): int {
            if ($left['priority'] !== $right['priority']) {
                return $left['priority'] <=> $right['priority'];
            }

            return ($left['orb'] ?? 99) <=> ($right['orb'] ?? 99);
        });

        return $pairwise;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function compactBody(array $body, string $locale): array
    {
        if ($body['kind'] === 'fixed_star') {
            return [
                'id' => $this->bodyId((string) $body['name']),
                'name' => $this->translateFixedStar((string) $body['name'], $locale),
                'kind' => 'fixed_star',
                'sign' => $this->translateSign((string) $body['sign'], $locale),
            ];
        }

        return [
            'id' => $this->bodyId((string) $body['name']),
            'name' => $this->translatePlanet((string) $body['name'], $locale),
            'kind' => 'planet',
            'sign' => $this->translateSign((string) $body['sign'], $locale),
            'retrograde' => (bool) ($body['retrograde'] ?? false),
        ];
    }

    private function bodyId(string $name): string
    {
        return strtolower(str_replace(' ', '_', $name));
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
                'retrograde' => (bool) ($planet['retrograde'] ?? false),
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
