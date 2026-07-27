<?php

namespace App\Models;

use App\Support\ChartDisplaySettings;
use Illuminate\Database\Eloquent\Model;

class ChartDisplayDefault extends Model
{
    protected $fillable = [
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'settings' => ChartDisplaySettings::defaults(),
        ]);
    }
}
