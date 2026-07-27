<?php

namespace App\Support;

class BirthTimezoneOptions
{
    /**
     * Common birth-timezone choices (offset still stored in DB).
     *
     * @return list<array{offset: float, zone: string, abbr: string}>
     */
    public static function all(): array
    {
        return [
            ['offset' => -12, 'zone' => 'Pacific/Kwajalein', 'abbr' => 'UTC-12'],
            ['offset' => -8, 'zone' => 'America/Los_Angeles', 'abbr' => 'PST'],
            ['offset' => -7, 'zone' => 'America/Los_Angeles', 'abbr' => 'PDT'],
            ['offset' => -5, 'zone' => 'America/New_York', 'abbr' => 'EST'],
            ['offset' => -4, 'zone' => 'America/New_York', 'abbr' => 'EDT'],
            ['offset' => 0, 'zone' => 'Europe/London', 'abbr' => 'GMT'],
            ['offset' => 1, 'zone' => 'Europe/London', 'abbr' => 'BST'],
            ['offset' => 0, 'zone' => 'Europe/Lisbon', 'abbr' => 'WET'],
            ['offset' => 1, 'zone' => 'Europe/Paris', 'abbr' => 'CET'],
            ['offset' => 2, 'zone' => 'Europe/Paris', 'abbr' => 'CEST'],
            ['offset' => 1, 'zone' => 'Europe/Budapest', 'abbr' => 'CET'],
            ['offset' => 2, 'zone' => 'Europe/Budapest', 'abbr' => 'CEST'],
            ['offset' => 2, 'zone' => 'Europe/Helsinki', 'abbr' => 'EET'],
            ['offset' => 3, 'zone' => 'Europe/Helsinki', 'abbr' => 'EEST'],
            ['offset' => 3, 'zone' => 'Europe/Moscow', 'abbr' => 'MSK'],
            ['offset' => 4, 'zone' => 'Asia/Dubai', 'abbr' => 'GST'],
            ['offset' => 5.5, 'zone' => 'Asia/Kolkata', 'abbr' => 'IST'],
            ['offset' => 8, 'zone' => 'Asia/Shanghai', 'abbr' => 'CST'],
            ['offset' => 9, 'zone' => 'Asia/Tokyo', 'abbr' => 'JST'],
            ['offset' => 10, 'zone' => 'Australia/Sydney', 'abbr' => 'AEST'],
            ['offset' => 11, 'zone' => 'Australia/Sydney', 'abbr' => 'AEDT'],
        ];
    }

    public static function formatUtcOffset(float $offset): string
    {
        if ($offset == 0.0) {
            return 'UTC';
        }

        $sign = $offset > 0 ? '+' : '-';
        $abs = abs($offset);
        $hours = (int) floor($abs);
        $minutes = (int) round(($abs - $hours) * 60);

        if ($minutes === 0) {
            return sprintf('UTC%s%d', $sign, $hours);
        }

        return sprintf('UTC%s%d:%02d', $sign, $hours, $minutes);
    }

    public static function label(array $option): string
    {
        $zone = str_replace('_', ' ', $option['zone']);

        return sprintf(
            '%s · %s (%s)',
            $zone,
            $option['abbr'],
            self::formatUtcOffset((float) $option['offset']),
        );
    }

    public static function optionValue(array $option): string
    {
        return self::formatOffsetValue((float) $option['offset']);
    }

    public static function formatOffsetValue(float $offset): string
    {
        return rtrim(rtrim(number_format($offset, 2, '.', ''), '0'), '.');
    }

    public static function defaultOffset(): float
    {
        return (float) self::defaultOption()['offset'];
    }

    /**
     * @return array{offset: float, zone: string, abbr: string}
     */
    public static function defaultOption(): array
    {
        return ['offset' => 1, 'zone' => 'Europe/Budapest', 'abbr' => 'CET'];
    }

    public static function isDefaultOption(array $option): bool
    {
        $default = self::defaultOption();

        return $option['zone'] === $default['zone'] && $option['abbr'] === $default['abbr'];
    }

    /**
     * Pick the timezone option to show as selected (handles duplicate offsets).
     */
    public static function matchesSelected(array $option, float $selectedOffset, bool $useDefaultOption): bool
    {
        if ($useDefaultOption) {
            return self::isDefaultOption($option);
        }

        if ((float) $option['offset'] !== $selectedOffset) {
            return false;
        }

        $candidates = array_values(array_filter(
            self::all(),
            fn (array $candidate) => (float) $candidate['offset'] === $selectedOffset,
        ));

        if (count($candidates) === 1) {
            return $option['zone'] === $candidates[0]['zone'] && $option['abbr'] === $candidates[0]['abbr'];
        }

        $preferred = array_values(array_filter(
            $candidates,
            fn (array $candidate) => self::isDefaultOption($candidate),
        ));

        $pick = $preferred[0] ?? $candidates[0];

        return $option['zone'] === $pick['zone'] && $option['abbr'] === $pick['abbr'];
    }
}
