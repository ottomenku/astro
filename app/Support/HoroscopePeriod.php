<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

class HoroscopePeriod
{
    public const DAILY = 'daily';

    public const WEEKLY = 'weekly';

    public const MONTHLY = 'monthly';

    /** @var list<string> */
    public const TYPES = [self::DAILY, self::WEEKLY, self::MONTHLY];

    public static function normalize(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        if (! in_array($type, self::TYPES, true)) {
            return self::DAILY;
        }

        return $type;
    }

    /**
     * @return array{
     *     type: string,
     *     start: \Illuminate\Support\Carbon,
     *     end: \Illuminate\Support\Carbon,
     *     forecast_date: \Illuminate\Support\Carbon
     * }
     */
    public static function bounds(?string $type = null, ?Carbon $reference = null): array
    {
        $type = self::normalize($type);
        $timezone = (string) config('daily_horoscope.timezone', 'Europe/Budapest');
        $now = ($reference ?? now($timezone))->copy()->timezone($timezone)->startOfDay();

        return match ($type) {
            self::WEEKLY => [
                'type' => self::WEEKLY,
                'start' => $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                'end' => $now->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay(),
                'forecast_date' => $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
            ],
            self::MONTHLY => [
                'type' => self::MONTHLY,
                'start' => $now->copy()->startOfMonth()->startOfDay(),
                'end' => $now->copy()->endOfMonth()->startOfDay(),
                'forecast_date' => $now->copy()->startOfMonth()->startOfDay(),
            ],
            self::DAILY => [
                'type' => self::DAILY,
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->startOfDay(),
                'forecast_date' => $now->copy()->startOfDay(),
            ],
            default => throw new InvalidArgumentException('Ismeretlen időszak típus.'),
        };
    }
}
