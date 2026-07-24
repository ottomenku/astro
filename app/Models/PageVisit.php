<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVisit extends Model
{
    protected $fillable = [
        'visited_at',
        'user_id',
        'user_name',
        'user_email',
        'ip_address',
        'route_name',
        'path',
        'page_label',
        'method',
        'status_code',
        'is_bot',
        'bot_name',
        'visitor_type',
        'user_agent',
        'referer',
        'accept_language',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'country_code',
        'country_name',
        'region',
        'city',
        'timezone',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'is_bot' => 'boolean',
            'status_code' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
