<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartScore extends Model
{
    protected $fillable = [
        'natal_chart_id',
        'scoring_profile_id',
        'polarity_positive',
        'polarity_negative',
        'polarity_balance',
        'element_fire',
        'element_earth',
        'element_air',
        'element_water',
        'modality_cardinal',
        'modality_fixed',
        'modality_mutable',
        'total_score',
        'rating_label',
        'breakdown',
        'calculated_at',
    ];

    protected $casts = [
        'polarity_positive' => 'float',
        'polarity_negative' => 'float',
        'polarity_balance' => 'float',
        'element_fire' => 'float',
        'element_earth' => 'float',
        'element_air' => 'float',
        'element_water' => 'float',
        'modality_cardinal' => 'float',
        'modality_fixed' => 'float',
        'modality_mutable' => 'float',
        'total_score' => 'float',
        'breakdown' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function natalChart(): BelongsTo
    {
        return $this->belongsTo(NatalChart::class);
    }

    public function scoringProfile(): BelongsTo
    {
        return $this->belongsTo(ScoringProfile::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toContextArray(): array
    {
        $this->loadMissing('scoringProfile');

        $base = [
            'engine' => $this->scoringProfile?->engine,
            'profile' => $this->scoringProfile?->name,
            'rating' => $this->rating_label,
            'total_score' => $this->total_score,
            'polarity' => [
                'positive' => $this->polarity_positive,
                'negative' => $this->polarity_negative,
                'balance' => $this->polarity_balance,
            ],
            'elements' => [
                'fire' => $this->element_fire,
                'earth' => $this->element_earth,
                'air' => $this->element_air,
                'water' => $this->element_water,
            ],
            'modalities' => [
                'cardinal' => $this->modality_cardinal,
                'fixed' => $this->modality_fixed,
                'mutable' => $this->modality_mutable,
            ],
            'breakdown' => $this->breakdown,
        ];

        if ($this->scoringProfile?->isAstroMotto() && is_array($this->breakdown)) {
            $base['activity_index'] = $this->breakdown['activity_index'] ?? null;
            $base['polarity_classification'] = $this->breakdown['polarity_classification'] ?? null;
            $base['element_classification'] = $this->breakdown['element_classification'] ?? null;
            $base['modality_classification'] = $this->breakdown['modality_classification'] ?? null;
        }

        return $base;
    }
}
