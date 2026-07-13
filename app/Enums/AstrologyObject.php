<?php

namespace App\Enums;

enum AstrologyObject: string
{
    case Sun = 'Sun';
    case Moon = 'Moon';
    case Mercury = 'Mercury';
    case Venus = 'Venus';
    case Mars = 'Mars';
    case Jupiter = 'Jupiter';
    case Saturn = 'Saturn';
    case Uranus = 'Uranus';
    case Neptune = 'Neptune';
    case Pluto = 'Pluto';
    case TrueNode = 'True Node';
    case Asc = 'Asc';
    case Mc = 'Mc';

    public static function tryFromName(string $name): ?self
    {
        return match ($name) {
            'True Node' => self::TrueNode,
            'Asc', 'ASC' => self::Asc,
            'Mc', 'MC' => self::Mc,
            default => self::tryFrom($name),
        };
    }
}
