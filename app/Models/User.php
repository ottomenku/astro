<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        // születési adatok
        'birth_datetime_utc',
        'birth_tz_offset',
        'birth_place_label',
        'birth_lat',
        'birth_lon',

        // jelenlegi hely (tranzitokhoz)
        'current_tz_offset',
        'current_place_label',
        'current_lat',
        'current_lon',

        'tier',
        'is_admin',
        'token_quota_total',
        'token_quota_used',
        'token_quota_reset_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'token_quota_reset_at' => 'datetime',

            'birth_datetime_utc' => 'datetime',
        ];
    }

    public function horoscopes(): HasMany
    {
        return $this->hasMany(UserHoroscope::class);
    }

    public function chatThreads(): HasMany
    {
        return $this->hasMany(ChatThread::class);
    }
}
