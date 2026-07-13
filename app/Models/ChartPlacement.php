<?php

namespace App\Models;

use App\Enums\AstrologyObject;
use App\Enums\ZodiacSign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartPlacement extends Model
{
    protected $fillable = [
        'natal_chart_id',
        'object',
        'sign',
        'house',
        'longitude',
        'sign_degree',
    ];

    protected $casts = [
        'house' => 'integer',
        'longitude' => 'float',
        'sign_degree' => 'float',
        'object' => AstrologyObject::class,
        'sign' => ZodiacSign::class,
    ];

    public function natalChart(): BelongsTo
    {
        return $this->belongsTo(NatalChart::class);
    }
}
