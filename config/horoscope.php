<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Horoscope Python binary
    |--------------------------------------------------------------------------
    |
    | Élesben config cache mellett csak config() olvasható — ne env()-et használj
    | az alkalmazás kódjában. Példa: /var/www/astro/.venv/bin/python
    |
    */

    'python_bin' => env('HOROSCOPE_PYTHON_BIN'),

];
