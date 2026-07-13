<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyHoroscopeMessage extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'forecast_date',
        'locale',
        'status',
        'chart_datetime_utc',
        'chart_payload',
        'score_payload',
        'scoring_profile_name',
        'motto',
        'summary',
        'health',
        'money',
        'relationships',
        'work',
        'generated_by_user_id',
        'generated_at',
        'published_at',
        'approved_by_user_id',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'chart_datetime_utc' => 'datetime',
        'chart_payload' => 'array',
        'score_payload' => 'array',
        'generated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
