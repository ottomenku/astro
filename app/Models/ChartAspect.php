<?php

namespace App\Models;

use App\Enums\AspectType;
use App\Enums\AstrologyObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartAspect extends Model
{
    protected $fillable = [
        'natal_chart_id',
        'body1',
        'body2',
        'aspect_type',
        'orb',
        'strength',
    ];

    protected $casts = [
        'orb' => 'float',
        'strength' => 'float',
        'body1' => AstrologyObject::class,
        'body2' => AstrologyObject::class,
        'aspect_type' => AspectType::class,
    ];

    public function natalChart(): BelongsTo
    {
        return $this->belongsTo(NatalChart::class);
    }
}
