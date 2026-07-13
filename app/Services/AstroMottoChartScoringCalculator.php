<?php

namespace App\Services;

use App\Enums\ZodiacSign;
use App\Models\NatalChart;
use App\Models\ScoringProfile;

class AstroMottoChartScoringCalculator
{
    /**
     * @return array{
     *   polarity_positive: float,
     *   polarity_negative: float,
     *   polarity_balance: float,
     *   element_fire: float,
     *   element_earth: float,
     *   element_air: float,
     *   element_water: float,
     *   modality_cardinal: float,
     *   modality_fixed: float,
     *   modality_mutable: float,
     *   total_score: float,
     *   rating_label: string,
     *   breakdown: array<string, mixed>
     * }
     */
    public function calculate(NatalChart $natalChart, ScoringProfile $profile): array
    {
        $config = $profile->config ?? [];
        $objectWeights = (array) ($config['object_weights'] ?? []);
        $houseMultipliers = (array) ($config['house_multipliers'] ?? []);
        $dignityMultipliers = (array) ($config['dignity_multipliers'] ?? []);
        $dignities = (array) ($config['dignities'] ?? []);
        $aspectWeights = (array) ($config['aspect_weights'] ?? []);
        $aspectOrbs = (array) ($config['aspect_orbs'] ?? []);
        $thresholds = (array) ($config['classification_thresholds'] ?? []);
        $settings = (array) ($config['settings'] ?? []);

        $natalChart->loadMissing(['placements', 'aspects']);

        $elements = ['fire' => 0.0, 'earth' => 0.0, 'air' => 0.0, 'water' => 0.0];
        $modalities = ['cardinal' => 0.0, 'fixed' => 0.0, 'mutable' => 0.0];
        $positiveScore = 0.0;
        $negativeScore = 0.0;
        $placementBreakdown = [];
        $aspectBreakdown = [];
        $activityTotal = 0.0;

        foreach ($natalChart->placements as $placement) {
            $objectKey = $this->normalizeObjectKey((string) $placement->object->value ?? (string) $placement->object);
            $weight = (float) ($objectWeights[$objectKey] ?? 5);
            $houseMultiplier = (float) ($houseMultipliers[(string) $placement->house] ?? 1.0);
            $dignityKey = $this->resolveDignity($objectKey, $placement->sign, $dignities);
            $dignityMultiplier = (float) ($dignityMultipliers[$dignityKey] ?? 1.0);
            $points = round($weight * $houseMultiplier * $dignityMultiplier, 4);
            $activityTotal += $points;

            $sign = $placement->sign instanceof ZodiacSign
                ? $placement->sign
                : ZodiacSign::tryFromName((string) $placement->sign);

            if ($sign) {
                $elements[$sign->element()] += $points;
                $modalities[$sign->modality()] += $points;
            }

            $placementBreakdown[] = [
                'object' => $objectKey,
                'sign' => $sign?->value,
                'house' => $placement->house,
                'dignity' => $dignityKey,
                'points' => $points,
            ];
        }

        foreach ($natalChart->aspects as $aspect) {
            $type = (string) ($aspect->aspect_type->value ?? $aspect->aspect_type);
            $body1 = $this->normalizeObjectKey((string) ($aspect->body1->value ?? $aspect->body1));
            $body2 = $this->normalizeObjectKey((string) ($aspect->body2->value ?? $aspect->body2));
            $w1 = (float) ($objectWeights[$body1] ?? 5);
            $w2 = (float) ($objectWeights[$body2] ?? 5);
            $aspectWeight = (float) ($aspectWeights[$type] ?? 0);
            $maxOrb = (float) ($aspectOrbs[$type] ?? 8.0);

            if ($this->isLuminary($body1) || $this->isLuminary($body2)) {
                $maxOrb += (float) ($aspectOrbs['luminary_bonus'] ?? 0);
            }

            $orb = min((float) $aspect->orb, max($maxOrb, 0.001));
            $orbFactor = pow(1 - ($orb / $maxOrb), 2);
            $contribution = sqrt($w1 * $w2) * $aspectWeight * $orbFactor;

            if ($contribution >= 0) {
                $positiveScore += $contribution;
            } else {
                $negativeScore += abs($contribution);
            }

            $aspectBreakdown[] = [
                'body1' => $body1,
                'body2' => $body2,
                'type' => $type,
                'orb' => $orb,
                'contribution' => round($contribution, 4),
            ];
        }

        $elementTotal = array_sum($elements) ?: 1.0;
        $modalityTotal = array_sum($modalities) ?: 1.0;
        $elementShares = array_map(fn ($v) => $v / $elementTotal, $elements);
        $modalityShares = array_map(fn ($v) => $v / $modalityTotal, $modalities);

        $activityIndex = round($activityTotal / 10, 3);
        $polarityValue = round($positiveScore - $negativeScore, 4);
        $polarityHarmony = $this->harmonyRatio($positiveScore, $negativeScore);
        $elementHarmony = $this->distributionHarmony($elementShares, (float) ($settings['element_neutral_share'] ?? 0.25));
        $modalityHarmony = $this->distributionHarmony($modalityShares, (float) ($settings['modality_neutral_share'] ?? (1 / 3)));

        $polarityClassification = $this->classifyPolarity($polarityValue, $polarityHarmony, $thresholds);
        $elementClassification = $this->classifyDistribution($elementShares, $thresholds, 'element');
        $modalityClassification = $this->classifyDistribution($modalityShares, $thresholds, 'modality');

        return [
            'polarity_positive' => round($positiveScore, 4),
            'polarity_negative' => round($negativeScore, 4),
            'polarity_balance' => $polarityValue,
            'element_fire' => round($elements['fire'], 3),
            'element_earth' => round($elements['earth'], 3),
            'element_air' => round($elements['air'], 3),
            'element_water' => round($elements['water'], 3),
            'modality_cardinal' => round($modalities['cardinal'], 3),
            'modality_fixed' => round($modalities['fixed'], 3),
            'modality_mutable' => round($modalities['mutable'], 3),
            'total_score' => $activityIndex,
            'rating_label' => $polarityClassification,
            'breakdown' => [
                'engine' => ScoringProfile::ENGINE_ASTRO_MOTTO,
                'activity_index' => $activityIndex,
                'positive_score' => round($positiveScore, 4),
                'negative_score' => round($negativeScore, 4),
                'polarity_value' => $polarityValue,
                'polarity_harmony' => $polarityHarmony,
                'polarity_classification' => $polarityClassification,
                'element_harmony' => $elementHarmony,
                'element_classification' => $elementClassification,
                'element_shares' => $elementShares,
                'modality_harmony' => $modalityHarmony,
                'modality_classification' => $modalityClassification,
                'modality_shares' => $modalityShares,
                'placements' => $placementBreakdown,
                'aspects' => $aspectBreakdown,
                'calculation_version' => $profile->version ?? '1.0.0',
            ],
        ];
    }

    private function normalizeObjectKey(string $object): string
    {
        return match (strtolower(str_replace(' ', '_', $object))) {
            'true_node', 'true node' => 'true_node',
            'asc', 'ascendant' => 'asc',
            'mc', 'midheaven' => 'mc',
            default => strtolower($object),
        };
    }

    private function isLuminary(string $objectKey): bool
    {
        return in_array($objectKey, ['sun', 'moon'], true);
    }

    /**
     * @param  array<string, array<string, string>>  $dignities
     */
    private function resolveDignity(string $objectKey, ZodiacSign|string $sign, array $dignities): string
    {
        $signName = $sign instanceof ZodiacSign ? $sign->value : (string) $sign;
        $rules = $dignities[$objectKey] ?? $dignities[ucfirst($objectKey)] ?? null;

        if (! is_array($rules)) {
            return 'neutral';
        }

        foreach (['domicile', 'exaltation', 'detriment', 'fall'] as $key) {
            if (($rules[$key] ?? null) === $signName) {
                return $key;
            }
        }

        return 'neutral';
    }

    private function harmonyRatio(float $positive, float $negative): float
    {
        $total = $positive + $negative;
        if ($total <= 0) {
            return 0.0;
        }

        return round(1 - (abs($positive - $negative) / $total), 3);
    }

    /**
     * @param  array<string, float>  $shares
     */
    private function distributionHarmony(array $shares, float $neutralShare): float
    {
        $max = max($shares);
        if ($max <= $neutralShare) {
            return 1.0;
        }

        return round(max(0, 1 - (($max - $neutralShare) / (1 - $neutralShare))), 3);
    }

    /**
     * @param  array<string, float|int>  $thresholds
     */
    private function classifyPolarity(float $value, float $harmony, array $thresholds): string
    {
        if ($value >= (float) ($thresholds['harmonic'] ?? 1.5)) {
            return 'harmonikus';
        }
        if ($value <= (float) ($thresholds['disharmonic'] ?? -2.5)) {
            return 'diszharmonikus';
        }
        if ($harmony >= 0.75) {
            return 'kiegyensúlyozott';
        }

        return 'vegyes';
    }

    /**
     * @param  array<string, float>  $shares
     * @param  array<string, float|int>  $thresholds
     */
    private function classifyDistribution(array $shares, array $thresholds, string $prefix): string
    {
        $dominanceKey = "{$prefix}_dominance_share";
        $balancedKey = "{$prefix}_balanced_max_share";
        $maxKey = array_search(max($shares), $shares, true) ?: 'unknown';

        if (max($shares) >= (float) ($thresholds[$dominanceKey] ?? 0.4)) {
            return "domináns: {$maxKey}";
        }

        if (max($shares) <= (float) ($thresholds[$balancedKey] ?? 0.34)) {
            return 'kiegyensúlyozott';
        }

        return 'vegyes';
    }
}
