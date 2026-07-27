<?php

namespace App\Support;

class BirthPlaceDefaults
{
    public const CITY = 'Budapest';

    public const COUNTRY_CODE = 'hu';

    public const LAT = 47.497912;

    public const LON = 19.040235;

    public static function city(): string
    {
        return self::CITY;
    }

    public static function countryCode(): string
    {
        return self::COUNTRY_CODE;
    }

    public static function lat(): float
    {
        return self::LAT;
    }

    public static function lon(): float
    {
        return self::LON;
    }

    public static function placeLabel(): string
    {
        return self::CITY.', '.__('countries.codes.'.self::COUNTRY_CODE);
    }
}
