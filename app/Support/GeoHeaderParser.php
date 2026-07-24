<?php

namespace App\Support;

class GeoHeaderParser
{
    /** @var array<string, string> */
    private const COUNTRY_NAMES = [
        'HU' => 'Magyarország',
        'AT' => 'Ausztria',
        'DE' => 'Németország',
        'SK' => 'Szlovákia',
        'RO' => 'Románia',
        'HR' => 'Horvátország',
        'SI' => 'Szlovénia',
        'RS' => 'Szerbia',
        'UA' => 'Ukrajna',
        'PL' => 'Lengyelország',
        'CZ' => 'Csehország',
        'GB' => 'Egyesült Királyság',
        'US' => 'Egyesült Államok',
        'FR' => 'Franciaország',
        'IT' => 'Olaszország',
        'ES' => 'Spanyolország',
        'NL' => 'Hollandia',
        'BE' => 'Belgium',
        'CH' => 'Svájc',
    ];

    /**
     * @return array{
     *     country_code: ?string,
     *     country_name: ?string,
     *     region: ?string,
     *     city: ?string,
     *     timezone: ?string
     * }
     */
    public function fromRequestHeaders(array $headers): array
    {
        $countryCode = $this->firstHeader($headers, [
            'CF-IPCountry',
            'CloudFront-Viewer-Country',
            'X-AppEngine-Country',
            'X-Country-Code',
        ]);

        if ($countryCode !== null) {
            $countryCode = strtoupper(substr($countryCode, 0, 2));
        }

        $region = $this->firstHeader($headers, [
            'CF-Region',
            'CF-Region-Code',
            'CloudFront-Viewer-Country-Region',
            'X-AppEngine-Region',
        ]);

        $city = $this->firstHeader($headers, [
            'CF-IPCity',
            'CloudFront-Viewer-City',
            'X-AppEngine-City',
        ]);

        $timezone = $this->firstHeader($headers, [
            'CF-Timezone',
            'CloudFront-Viewer-Time-Zone',
        ]);

        return [
            'country_code' => $countryCode,
            'country_name' => $countryCode ? (self::COUNTRY_NAMES[$countryCode] ?? $countryCode) : null,
            'region' => $region,
            'city' => $city,
            'timezone' => $timezone,
        ];
    }

    /**
     * @param  array<string, mixed>  $headers
     * @param  array<int, string>  $names
     */
    private function firstHeader(array $headers, array $names): ?string
    {
        foreach ($names as $name) {
            $value = $headers[$name] ?? $headers[strtolower($name)] ?? null;

            if (is_array($value)) {
                $value = $value[0] ?? null;
            }

            $value = trim((string) $value);

            if ($value !== '' && strtoupper($value) !== 'XX' && strtoupper($value) !== 'T1') {
                return $value;
            }
        }

        return null;
    }
}
