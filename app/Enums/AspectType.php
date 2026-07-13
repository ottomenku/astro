<?php

namespace App\Enums;

enum AspectType: string
{
    case Conjunction = 'conjunction';
    case Sextile = 'sextile';
    case Square = 'square';
    case Trine = 'trine';
    case Opposition = 'opposition';

    public function angle(): int
    {
        return match ($this) {
            self::Conjunction => 0,
            self::Sextile => 60,
            self::Square => 90,
            self::Trine => 120,
            self::Opposition => 180,
        };
    }

    public static function tryFromName(string $name): ?self
    {
        return self::tryFrom($name);
    }
}
