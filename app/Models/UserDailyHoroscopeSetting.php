<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyHoroscopeSetting extends Model
{
    protected $fillable = [
        'user_id',
        'use_personal_daily',
        'system_prompt',
        'user_prompt_template',
        'scoring_profile_id',
        'birth_chart_id',
        'user_horoscope_id',
    ];

    protected $casts = [
        'use_personal_daily' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scoringProfile(): BelongsTo
    {
        return $this->belongsTo(ScoringProfile::class);
    }

    public function birthChart(): BelongsTo
    {
        return $this->belongsTo(BirthChart::class);
    }

    public function userHoroscope(): BelongsTo
    {
        return $this->belongsTo(UserHoroscope::class);
    }

    public static function forUser(User $user): self
    {
        return static::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['use_personal_daily' => false],
        );
    }

    public function resolvedScoringProfile(): ?ScoringProfile
    {
        if ($this->scoring_profile_id) {
            return $this->scoringProfile;
        }

        return ScoringProfile::forUser($this->user);
    }
}
