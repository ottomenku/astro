<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHoroscope extends Model
{
    protected $fillable = [
        'user_id',
        'kind',
        'label',
        'sidereal',
        'ayanamsa',
        'house_system',
        'data',
        'calculated_at',
    ];

    protected $casts = [
        'sidereal' => 'boolean',
        'data' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
