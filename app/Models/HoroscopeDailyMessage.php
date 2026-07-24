<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoroscopeDailyMessage extends Model
{
    public const KIND_PERSONAL = 'personal';

    public const KIND_PARTNERSHIP = 'partnership';

    protected $fillable = [
        'user_id',
        'forecast_date',
        'locale',
        'period_type',
        'period_start',
        'period_end',
        'kind',
        'cache_key',
        'birth_chart_id',
        'birth_chart_id_a',
        'birth_chart_id_b',
        'chart_datetime_utc',
        'chart_payload',
        'score_payload',
        'context_payload',
        'period_context',
        'scoring_profile_name',
        'motto',
        'summary',
        'health',
        'money',
        'relationships',
        'work',
        'explanation',
        'generated_at',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'chart_datetime_utc' => 'datetime',
        'chart_payload' => 'array',
        'score_payload' => 'array',
        'context_payload' => 'array',
        'period_context' => 'array',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function birthChart(): BelongsTo
    {
        return $this->belongsTo(BirthChart::class);
    }

    public function birthChartA(): BelongsTo
    {
        return $this->belongsTo(BirthChart::class, 'birth_chart_id_a');
    }

    public function birthChartB(): BelongsTo
    {
        return $this->belongsTo(BirthChart::class, 'birth_chart_id_b');
    }
}
