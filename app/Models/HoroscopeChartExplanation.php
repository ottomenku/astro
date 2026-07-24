<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoroscopeChartExplanation extends Model
{
    public const KIND_PERSONAL = 'personal';

    public const KIND_PARTNERSHIP = 'partnership';

    protected $fillable = [
        'user_id',
        'locale',
        'kind',
        'cache_key',
        'birth_chart_id',
        'birth_chart_id_a',
        'birth_chart_id_b',
        'context_payload',
        'explanation',
        'generated_at',
    ];

    protected $casts = [
        'context_payload' => 'array',
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
