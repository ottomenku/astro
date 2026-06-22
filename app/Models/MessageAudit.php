<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageAudit extends Model
{
    protected $fillable = [
        'thread_id',
        'user_id',
        'sender',
        'sender_name',
        'recipient',
        'message',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
