<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NatalChart extends Model
{
    protected $fillable = [
        'user_id',
        'birth_chart_id',
        'user_horoscope_id',
        'datetime_utc',
        'lat',
        'lon',
        'sidereal',
        'ayanamsa',
        'house_system',
    ];

    protected $casts = [
        'datetime_utc' => 'datetime',
        'lat' => 'float',
        'lon' => 'float',
        'sidereal' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function birthChart(): BelongsTo
    {
        return $this->belongsTo(BirthChart::class);
    }

    public function userHoroscope(): BelongsTo
    {
        return $this->belongsTo(UserHoroscope::class);
    }

    public function placements(): HasMany
    {
        return $this->hasMany(ChartPlacement::class);
    }

    public function aspects(): HasMany
    {
        return $this->hasMany(ChartAspect::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ChartScore::class);
    }

    public function latestScore(): HasOne
    {
        return $this->hasOne(ChartScore::class)->latestOfMany('calculated_at');
    }
}
