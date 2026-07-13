<?php

namespace App\Models;

use App\Models\ScoringProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringProfile extends Model
{
    public const ENGINE_INTEGRATED = 'integrated';

    public const ENGINE_ASTRO_MOTTO = 'astro_motto';

    protected $fillable = [
        'name',
        'slug',
        'engine',
        'version',
        'is_default',
        'config',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'config' => 'array',
    ];

    public function chartScores(): HasMany
    {
        return $this->hasMany(ChartScore::class);
    }

    public static function defaultProfile(): ?self
    {
        return static::query()->where('is_default', true)->first()
            ?? static::query()->orderBy('id')->first();
    }

    public static function forUser(?User $user): ?self
    {
        if ($user?->scoring_profile_id) {
            $selected = static::query()->find($user->scoring_profile_id);
            if ($selected) {
                return $selected;
            }
        }

        return static::defaultProfile();
    }

    public function isAstroMotto(): bool
    {
        return $this->engine === self::ENGINE_ASTRO_MOTTO;
    }

    public function isIntegrated(): bool
    {
        return $this->engine === self::ENGINE_INTEGRATED;
    }
}
