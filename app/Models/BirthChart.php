<?php

namespace App\Models;

use Database\Factories\BirthChartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class BirthChart extends Model
{
    /** @use HasFactory<BirthChartFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'birth_datetime_utc',
        'birth_tz_offset',
        'birth_place_label',
        'birth_lat',
        'birth_lon',
        'time_accuracy',
        'corrected_datetime_utc',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'birth_datetime_utc' => 'datetime',
            'corrected_datetime_utc' => 'datetime',
            'birth_lat' => 'float',
            'birth_lon' => 'float',
            'birth_tz_offset' => 'float',
            'time_accuracy' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lokális születési dátum és idő a tárolt UTC és offset alapján.
     *
     * @return array{date: string, time: string}
     */
    public function localBirthParts(): array
    {
        return self::utcToLocalParts($this->birth_datetime_utc, (float) $this->birth_tz_offset);
    }

    /**
     * @return array{date: string, time: string}|null
     */
    public function localCorrectedParts(): ?array
    {
        if (! $this->corrected_datetime_utc) {
            return null;
        }

        return self::utcToLocalParts($this->corrected_datetime_utc, (float) $this->birth_tz_offset);
    }

    /**
     * @return array{date: string, time: string}
     */
    public static function utcToLocalParts(Carbon $utc, float $offsetHours): array
    {
        $local = $utc->copy()->utc()->addMinutes((int) round($offsetHours * 60));

        return [
            'date' => $local->format('Y-m-d'),
            'time' => $local->format('H:i'),
        ];
    }

    public static function localPartsToUtc(string $date, string $time, float $offsetHours): Carbon
    {
        $local = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'UTC');

        return $local->subMinutes((int) round($offsetHours * 60));
    }
}
