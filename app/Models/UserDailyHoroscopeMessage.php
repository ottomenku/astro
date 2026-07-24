<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyHoroscopeMessage extends Model
{
    protected $fillable = [
        'user_id',
        'forecast_date',
        'locale',
        'period_type',
        'period_start',
        'period_end',
        'chart_datetime_utc',
        'chart_payload',
        'period_context',
        'score_payload',
        'attached_chart_payload',
        'scoring_profile_name',
        'motto',
        'summary',
        'health',
        'money',
        'relationships',
        'work',
        'generated_at',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'chart_datetime_utc' => 'datetime',
        'chart_payload' => 'array',
        'period_context' => 'array',
        'score_payload' => 'array',
        'attached_chart_payload' => 'array',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
