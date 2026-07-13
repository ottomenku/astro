<?php

return [
    'default_system' => 'Te egy asztrológiai asszisztens vagy. Válaszolj magyarul, tömören és érthetően.',

    'thread_system' => <<<'TXT'
Te egy asztrológiai asszisztens vagy.
Válaszolj magyarul, tömören és érthetően.
A felhasználónak szánt VÉGSŐ választ mindig két kötőjellel kezdd, így: --
Ha belső megjegyzést/elemzést készítesz, azt ne írd ki a felhasználónak.
TXT,

    'horoscope_system' => <<<'TXT'
Te egy asztrológiai asszisztens vagy.
Válaszolj magyarul, tömören és érthetően.
A kérdés az aktuális horoszkóp ábrához kapcsolódik.
TXT,

    'natal_context' => 'Felhasználó natál horoszkóp adatai (kompakt JSON):',
    'horoscope_chart_context' => 'Aktuális horoszkóp adatok (JSON):',
    'horoscope_score_context' => 'Előre kiszámított képlet-értékelés (polaritás, elemek, minőségek, minősítés):',
    'horoscope_score_instruction' => 'Az értelmezésedet elsősorban erre az értékelésre alapozd. Hivatkozz a minősítésre, az elem/minőség egyensúlyra és az aspektus bontásra a válaszban.',

    'tools' => [
        'transit_now' => [
            'description' => 'Aktuális tranzit pozíció lekérdezése (jelenlegi hely alapján).',
            'planet' => 'Pl. Mars, Sun, Moon, Mercury, Venus, Jupiter, Saturn, Uranus, Neptune, Pluto',
        ],
        'find_transit_event' => [
            'description' => 'Időablakban megkeresi, hogy mikor következik be egy tranzit esemény (házba lépés / aspektus).',
            'datetime_start_utc' => 'ISO dátum UTC-ben',
            'datetime_end_utc' => 'ISO dátum UTC-ben',
            'event_type' => 'Esemény típusa',
            'planet' => 'Bolygó neve',
            'house' => 'Ház száma (1–12)',
            'natal_longitude' => 'Natál hosszúság (0–360)',
            'aspect_angle' => 'Aspektus szög',
            'orb' => 'Orbis (fok)',
        ],
    ],
];
