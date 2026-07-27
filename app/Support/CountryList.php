<?php

namespace App\Support;

class CountryList
{
    /**
     * ISO 3166-1 alpha-2 codes (empty string = no country filter).
     *
     * @var list<string>
     */
    public const CODES = [
        '',
        'hu',
        'at',
        'sk',
        'ro',
        'hr',
        'si',
        'de',
        'pl',
        'cz',
        'ua',
        'rs',
        'gb',
        'fr',
        'it',
        'es',
        'nl',
        'be',
        'ch',
        'se',
        'no',
        'dk',
        'fi',
        'ie',
        'pt',
        'gr',
        'bg',
        'lt',
        'lv',
        'ee',
        'us',
        'ca',
        'au',
        'nz',
        'jp',
        'cn',
        'in',
        'br',
        'mx',
        'ar',
        'za',
        'tr',
        'il',
        'ae',
        'sg',
        'kr',
        'th',
        'vn',
    ];

    /**
     * @return array<string, string>
     */
    public static function options(?string $locale = null): array
    {
        if ($locale !== null) {
            app()->setLocale($locale);
        }

        $options = [];
        foreach (self::CODES as $code) {
            $options[$code] = $code === ''
                ? __('countries.all')
                : __('countries.codes.'.$code);
        }

        return $options;
    }

    public static function isValid(?string $code): bool
    {
        if ($code === null || $code === '') {
            return true;
        }

        return in_array(strtolower($code), self::CODES, true);
    }
}
