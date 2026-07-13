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
        'chart_datetime_utc',
        'chart_payload',
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
        'chart_datetime_utc' => 'datetime',
        'chart_payload' => 'array',
        'score_payload' => 'array',
        'attached_chart_payload' => 'array',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
