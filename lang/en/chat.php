<?php

return [
    'default_system' => 'You are an astrology assistant. Reply in English, concisely and clearly.',

    'thread_system' => <<<'TXT'
You are an astrology assistant.
Reply in English, concisely and clearly.
Always begin your final answer to the user with two hyphens: --
Do not show internal notes or analysis to the user.
TXT,

    'horoscope_system' => <<<'TXT'
You are an astrology assistant.
Reply in English, concisely and clearly.
The question relates to the current horoscope chart.
TXT,

    'natal_context' => 'User natal chart data (compact JSON):',
    'horoscope_chart_context' => 'Current chart data (JSON):',
    'horoscope_score_context' => 'Pre-calculated chart evaluation (polarity, elements, modalities, rating):',
    'horoscope_score_instruction' => 'Base your interpretation primarily on this evaluation. Refer to the rating, element/modality balance, and aspect breakdown when answering.',

    'tools' => [
        'transit_now' => [
            'description' => 'Fetch the current transit position (based on the user’s current location).',
            'planet' => 'E.g. Mars, Sun, Moon, Mercury, Venus, Jupiter, Saturn, Uranus, Neptune, Pluto',
        ],
        'find_transit_event' => [
            'description' => 'Find when a transit event occurs within a time window (house ingress / aspect).',
            'datetime_start_utc' => 'ISO datetime in UTC',
            'datetime_end_utc' => 'ISO datetime in UTC',
            'event_type' => 'Event type',
            'planet' => 'Planet name',
            'house' => 'House number (1–12)',
            'natal_longitude' => 'Natal longitude (0–360)',
            'aspect_angle' => 'Aspect angle',
            'orb' => 'Orb (degrees)',
        ],
    ],
];
