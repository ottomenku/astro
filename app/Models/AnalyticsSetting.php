<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSetting extends Model
{
    public const MIN_RETENTION_DAYS = 1;

    public const MAX_RETENTION_DAYS = 365;

    protected $fillable = [
        'retention_days',
    ];

    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
        ];
    }

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting) {
            return $setting;
        }

        return static::query()->create([
            'retention_days' => (int) config('analytics.default_retention_days', 7),
        ]);
    }
}
