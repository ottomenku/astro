<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DailyHoroscopeSetting extends Model
{
    protected $fillable = [
        'locale',
        'system_prompt',
        'user_prompt_template',
        'user_prompt_append',
        'scoring_profile_id',
        'auto_publish',
        'explanation_sentences_short',
        'explanation_sentences_normal',
        'explanation_sentences_detailed',
        'message_sentences_short',
        'message_sentences_normal',
        'message_sentences_detailed',
        'horoscope_prompt_personal_message',
        'horoscope_prompt_partnership_message',
        'horoscope_prompt_personal_explanation',
        'horoscope_prompt_partnership_explanation',
    ];

    protected $casts = [
        'auto_publish' => 'boolean',
    ];

    public function scoringProfile(): BelongsTo
    {
        return $this->belongsTo(ScoringProfile::class);
    }

    public static function forLocale(string $locale): self
    {
        $locale = Str::lower(trim($locale));

        return static::query()->firstOrCreate(
            ['locale' => $locale],
            ['scoring_profile_id' => ScoringProfile::defaultProfile()?->id],
        );
    }
}
