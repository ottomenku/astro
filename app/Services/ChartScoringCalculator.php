<?php

namespace App\Services;

use App\Enums\AspectType;
use App\Enums\AstrologyObject;
use App\Enums\ZodiacSign;
use App\Models\ChartAspect;
use App\Models\ChartPlacement;
use App\Models\ChartScore;
use App\Models\NatalChart;
use App\Models\ScoringProfile;
use Illuminate\Support\Collection;

class ChartScoringCalculator
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
        $planetWeights = (array) ($config['planet_weights'] ?? []);
        $houseMultipliers = (array) ($config['house_multipliers'] ?? []);
        $dignityMultipliers = (array) ($config['dignity_multipliers'] ?? []);
        $dignities = (array) ($config['dignities'] ?? []);
        $aspectValues = (array) ($config['aspect_values'] ?? []);
        $orbLimits = (array) ($config['orb_limits'] ?? []);
        $thresholds = (array) ($config['rating_thresholds'] ?? []);

        $natalChart->loadMissing(['placements', 'aspects']);

        $elements = ['fire' => 0.0, 'earth' => 0.0, 'air' => 0.0, 'water' => 0.0];
        $modalities = ['cardinal' => 0.0, 'fixed' => 0.0, 'mutable' => 0.0];
        $polarityPositive = 0.0;
        $polarityNegative = 0.0;
        $placementBreakdown = [];
        $aspectBreakdown = [];
        $placementTotal = 0.0;
        $aspectTotal = 0.0;

        foreach ($natalChart->placements as $placement) {
            $objectName = $this->objectKey($placement->object);
            $weight = (float) ($planetWeights[$objectName] ?? 5);
            $houseMultiplier = (float) ($houseMultipliers[(string) $placement->house] ?? 1.0);
            $dignityKey = $this->resolveDignity($objectName, $placement->sign, $dignities);
            $dignityMultiplier = (float) ($dignityMultipliers[$dignityKey] ?? 1.0);
            $points = round($weight * $houseMultiplier * $dignityMultiplier, 2);

            $sign = $placement->sign instanceof ZodiacSign
                ? $placement->sign
                : ZodiacSign::tryFromName((string) $placement->sign);

            if ($sign) {
                $elements[$sign->element()] += $points;
                $modalities[$sign->modality()] += $points;

                if ($sign->polarity() === 'positive') {
                    $polarityPositive += $points;
                } else {
                    $polarityNegative += $points;
                }
            }

            $placementTotal += $points;
            $placementBreakdown[] = [
                'object' => $objectName,
                'sign' => $sign?->value,
                'house' => $placement->house,
                'dignity' => $dignityKey,
                'points' => $points,
            ];
        }

        foreach ($natalChart->aspects as $aspect) {
            $type = $aspect->aspect_type instanceof AspectType
                ? $aspect->aspect_type->value
                : (string) $aspect->aspect_type;
            $baseValue = (float) ($aspectValues[$type] ?? 0);
            $orbLimit = (float) ($orbLimits[$type] ?? 8.0);
            $strength = $orbLimit > 0
                ? max(0.0, 1.0 - ((float) $aspect->orb / $orbLimit))
                : (float) $aspect->strength;
            $points = round($baseValue * $strength, 2);
            $aspectTotal += $points;

            $aspectBreakdown[] = [
                'body1' => $this->objectKey($aspect->body1),
                'body2' => $this->objectKey($aspect->body2),
                'type' => $type,
                'orb' => (float) $aspect->orb,
                'points' => $points,
            ];
        }

        $totalScore = round($placementTotal + $aspectTotal, 2);
        $polarityBalance = round($polarityPositive - $polarityNegative, 2);
        $ratingLabel = $this->resolveRating($totalScore, $thresholds);

        return [
            'polarity_positive' => round($polarityPositive, 2),
            'polarity_negative' => round($polarityNegative, 2),
            'polarity_balance' => $polarityBalance,
            'element_fire' => round($elements['fire'], 2),
            'element_earth' => round($elements['earth'], 2),
            'element_air' => round($elements['air'], 2),
            'element_water' => round($elements['water'], 2),
            'modality_cardinal' => round($modalities['cardinal'], 2),
            'modality_fixed' => round($modalities['fixed'], 2),
            'modality_mutable' => round($modalities['mutable'], 2),
            'total_score' => $totalScore,
            'rating_label' => $ratingLabel,
            'breakdown' => [
                'engine' => ScoringProfile::ENGINE_INTEGRATED,
                'placements' => $placementBreakdown,
                'aspects' => $aspectBreakdown,
                'placement_total' => round($placementTotal, 2),
                'aspect_total' => round($aspectTotal, 2),
            ],
        ];
    }

    /**
     * @param  array<string, array<string, string>>  $dignities
     */
    private function resolveDignity(string $objectName, ZodiacSign|string $sign, array $dignities): string
    {
        $signName = $sign instanceof ZodiacSign ? $sign->value : (string) $sign;
        $rules = $dignities[$objectName] ?? null;

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

    /**
     * @param  list<array{min: int|float, label: string}>  $thresholds
     */
    private function resolveRating(float $totalScore, array $thresholds): string
    {
        usort($thresholds, fn (array $a, array $b) => ($b['min'] ?? 0) <=> ($a['min'] ?? 0));

        foreach ($thresholds as $threshold) {
            if ($totalScore >= (float) ($threshold['min'] ?? 0)) {
                return (string) ($threshold['label'] ?? 'ismeretlen');
            }
        }

        return 'ismeretlen';
    }

    private function objectKey(AstrologyObject|string|null $object): string
    {
        if ($object instanceof AstrologyObject) {
            return $object->value;
        }

        return (string) $object;
    }
}
