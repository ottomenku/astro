<?php

namespace App\Enums;

enum ZodiacSign: string
{
    case Aries = 'Aries';
    case Taurus = 'Taurus';
    case Gemini = 'Gemini';
    case Cancer = 'Cancer';
    case Leo = 'Leo';
    case Virgo = 'Virgo';
    case Libra = 'Libra';
    case Scorpio = 'Scorpio';
    case Sagittarius = 'Sagittarius';
    case Capricorn = 'Capricorn';
    case Aquarius = 'Aquarius';
    case Pisces = 'Pisces';

    public function element(): string
    {
        return match ($this) {
            self::Aries, self::Leo, self::Sagittarius => 'fire',
            self::Taurus, self::Virgo, self::Capricorn => 'earth',
            self::Gemini, self::Libra, self::Aquarius => 'air',
            self::Cancer, self::Scorpio, self::Pisces => 'water',
        };
    }

    public function modality(): string
    {
        return match ($this) {
            self::Aries, self::Cancer, self::Libra, self::Capricorn => 'cardinal',
            self::Taurus, self::Leo, self::Scorpio, self::Aquarius => 'fixed',
            self::Gemini, self::Virgo, self::Sagittarius, self::Pisces => 'mutable',
        };
    }

    /** Yang (+) vs yin (-) polaritás. */
    public function polarity(): string
    {
        return match ($this) {
            self::Aries, self::Gemini, self::Leo, self::Libra, self::Sagittarius, self::Aquarius => 'positive',
            self::Taurus, self::Cancer, self::Virgo, self::Scorpio, self::Capricorn, self::Pisces => 'negative',
        };
    }

    public static function tryFromName(string $name): ?self
    {
        return self::tryFrom($name);
    }
}
