<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AstrologyEntry extends Model
{
    protected $fillable = [
        'type',
        'key',
        'locale',
        'title',
        'question',
        'answer',
        'created_by_user_id',
        'click_count',
        'first_clicked_by_user_id',
        'first_clicked_at',
        'last_clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'click_count' => 'integer',
            'first_clicked_at' => 'datetime',
            'last_clicked_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function firstClickedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_clicked_by_user_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'sign' => 'Jegy',
            'planet' => 'Bolygó',
            'fixed_star' => 'Fix csillag',
            'aspect' => 'Fényszög',
            default => $this->type,
        };
    }
}
