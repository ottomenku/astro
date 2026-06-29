<?php

namespace App\Support;

class HoroscopePython
{
    public static function binary(): string
    {
        $fromConfig = (string) config('horoscope.python_bin', '');
        $default = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';

        if (PHP_OS_FAMILY === 'Windows' && ($fromConfig === '' || $fromConfig === 'python3')) {
            return 'python';
        }

        return $fromConfig !== '' ? $fromConfig : $default;
    }
}
