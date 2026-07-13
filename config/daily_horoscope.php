<?php

return [
    'timezone' => env('DAILY_HOROSCOPE_TZ', 'Europe/Budapest'),

    'location' => [
        'label' => env('DAILY_HOROSCOPE_PLACE', 'Budapest'),
        'lat' => (float) env('DAILY_HOROSCOPE_LAT', 47.497913),
        'lon' => (float) env('DAILY_HOROSCOPE_LON', 19.040236),
        'tz_offset' => (float) env('DAILY_HOROSCOPE_TZ_OFFSET', 2),
    ],

    'house_system' => env('DAILY_HOROSCOPE_HOUSE_SYSTEM', 'placidus'),
    'zodiac_mode' => env('DAILY_HOROSCOPE_ZODIAC_MODE', 'tropical'),
];
