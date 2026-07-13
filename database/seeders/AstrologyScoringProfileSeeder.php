<?php

namespace Database\Seeders;

use App\Models\ScoringProfile;
use Illuminate\Database\Seeder;

class AstrologyScoringProfileSeeder extends Seeder
{
    public function run(): void
    {
        ScoringProfile::query()->update(['is_default' => false]);

        ScoringProfile::query()->updateOrCreate(
            ['slug' => 'integrated-default-v1'],
            [
                'name' => 'Integrált pontozás',
                'engine' => ScoringProfile::ENGINE_INTEGRATED,
                'version' => '1.0.0',
                'is_default' => false,
                'config' => [
                    'planet_weights' => [
                        'Sun' => 10,
                        'Moon' => 9,
                        'Asc' => 10,
                        'Mc' => 8,
                        'Mercury' => 7,
                        'Venus' => 7,
                        'Mars' => 7,
                        'Jupiter' => 6,
                        'Saturn' => 6,
                        'Uranus' => 4,
                        'Neptune' => 4,
                        'Pluto' => 4,
                        'True Node' => 3,
                    ],
                    'house_multipliers' => [
                        '1' => 1.5, '2' => 1.2, '3' => 1.0,
                        '4' => 1.5, '5' => 1.2, '6' => 1.0,
                        '7' => 1.5, '8' => 1.2, '9' => 1.0,
                        '10' => 1.5, '11' => 1.2, '12' => 1.0,
                    ],
                    'dignity_multipliers' => [
                        'domicile' => 1.3,
                        'exaltation' => 1.2,
                        'detriment' => 0.7,
                        'fall' => 0.6,
                        'neutral' => 1.0,
                    ],
                    'dignities' => [
                        'Sun' => ['domicile' => 'Leo', 'exaltation' => 'Aries', 'detriment' => 'Aquarius', 'fall' => 'Libra'],
                        'Moon' => ['domicile' => 'Cancer', 'exaltation' => 'Taurus', 'detriment' => 'Capricorn', 'fall' => 'Scorpio'],
                        'Mercury' => ['domicile' => 'Gemini', 'exaltation' => 'Virgo', 'detriment' => 'Sagittarius', 'fall' => 'Pisces'],
                        'Venus' => ['domicile' => 'Taurus', 'exaltation' => 'Pisces', 'detriment' => 'Scorpio', 'fall' => 'Virgo'],
                        'Mars' => ['domicile' => 'Aries', 'exaltation' => 'Capricorn', 'detriment' => 'Libra', 'fall' => 'Cancer'],
                        'Jupiter' => ['domicile' => 'Sagittarius', 'exaltation' => 'Cancer', 'detriment' => 'Gemini', 'fall' => 'Capricorn'],
                        'Saturn' => ['domicile' => 'Capricorn', 'exaltation' => 'Libra', 'detriment' => 'Cancer', 'fall' => 'Aries'],
                    ],
                    'aspect_values' => [
                        'conjunction' => 3,
                        'sextile' => 3,
                        'square' => -4,
                        'trine' => 4,
                        'opposition' => -3,
                    ],
                    'orb_limits' => [
                        'conjunction' => 8.0,
                        'sextile' => 6.0,
                        'square' => 6.0,
                        'trine' => 6.0,
                        'opposition' => 8.0,
                    ],
                    'rating_thresholds' => [
                        ['min' => 80, 'label' => 'kiváló'],
                        ['min' => 60, 'label' => 'erős'],
                        ['min' => 40, 'label' => 'kiegyensúlyozott'],
                        ['min' => 20, 'label' => 'vegyes'],
                        ['min' => -999, 'label' => 'kihívásokkal teli'],
                    ],
                ],
            ]
        );

        ScoringProfile::query()->updateOrCreate(
            ['slug' => 'astro-motto-default-v1'],
            [
                'name' => 'Astro Motto alap pontozás',
                'engine' => ScoringProfile::ENGINE_ASTRO_MOTTO,
                'version' => '1.0.0',
                'is_default' => true,
                'config' => [
                    'object_weights' => [
                        'sun' => 10,
                        'moon' => 10,
                        'asc' => 9,
                        'mars' => 8,
                        'mercury' => 7,
                        'venus' => 7,
                        'saturn' => 7,
                        'jupiter' => 6,
                        'mc' => 6,
                        'uranus' => 4,
                        'neptune' => 4,
                        'pluto' => 4,
                    ],
                    'house_multipliers' => [
                        '1' => 1.25, '2' => 1.00, '3' => 0.85,
                        '4' => 1.15, '5' => 1.00, '6' => 0.85,
                        '7' => 1.15, '8' => 1.00, '9' => 0.85,
                        '10' => 1.25, '11' => 1.00, '12' => 0.85,
                    ],
                    'dignity_multipliers' => [
                        'domicile' => 1.20,
                        'exaltation' => 1.15,
                        'neutral' => 1.00,
                        'detriment' => 0.85,
                        'fall' => 0.80,
                    ],
                    'dignities' => [
                        'sun' => ['domicile' => 'Leo', 'exaltation' => 'Aries', 'detriment' => 'Aquarius', 'fall' => 'Libra'],
                        'moon' => ['domicile' => 'Cancer', 'exaltation' => 'Taurus', 'detriment' => 'Capricorn', 'fall' => 'Scorpio'],
                        'mercury' => ['domicile' => 'Gemini', 'exaltation' => 'Virgo', 'detriment' => 'Sagittarius', 'fall' => 'Pisces'],
                        'venus' => ['domicile' => 'Taurus', 'exaltation' => 'Pisces', 'detriment' => 'Scorpio', 'fall' => 'Virgo'],
                        'mars' => ['domicile' => 'Aries', 'exaltation' => 'Capricorn', 'detriment' => 'Libra', 'fall' => 'Cancer'],
                        'jupiter' => ['domicile' => 'Sagittarius', 'exaltation' => 'Cancer', 'detriment' => 'Gemini', 'fall' => 'Capricorn'],
                        'saturn' => ['domicile' => 'Capricorn', 'exaltation' => 'Libra', 'detriment' => 'Cancer', 'fall' => 'Aries'],
                    ],
                    'aspect_weights' => [
                        'conjunction' => 0.00,
                        'semi_sextile' => 0.20,
                        'sextile' => 0.60,
                        'square' => -0.80,
                        'trine' => 1.00,
                        'opposition' => -1.00,
                    ],
                    'aspect_orbs' => [
                        'conjunction' => 8.0,
                        'semi_sextile' => 3.0,
                        'sextile' => 5.0,
                        'square' => 7.0,
                        'trine' => 7.0,
                        'opposition' => 8.0,
                        'luminary_bonus' => 2.0,
                    ],
                    'classification_thresholds' => [
                        'strong_activity' => 6.50,
                        'very_strong_activity' => 8.00,
                        'dominance' => 2.50,
                        'harmonic' => 1.50,
                        'disharmonic' => -2.50,
                        'element_dominance_share' => 0.40,
                        'element_balanced_max_share' => 0.34,
                        'modality_dominance_share' => 0.50,
                        'modality_balanced_max_share' => 0.42,
                    ],
                    'settings' => [
                        'score_min' => -10,
                        'score_max' => 10,
                        'orb_formula' => 'pow(1 - orb / max_orb, 2)',
                        'aspect_strength_formula' => 'sqrt(object_weight_1 * object_weight_2) * aspect_weight * orb_factor',
                        'element_neutral_share' => 0.25,
                        'modality_neutral_share' => 1 / 3,
                    ],
                ],
            ]
        );
    }
}
