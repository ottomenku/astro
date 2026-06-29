<?php

namespace App\Services;

use App\Models\User;
use App\Support\HoroscopePython;
use Symfony\Component\Process\Process;

class HoroscopeTransitService
{
    /**
     * Egyszerű tranzit-lekérdezés: adott bolygó pozíciója MOST (UTC).
     *
     * @return array{planet:string, datetime_utc:string, longitude:float, sign:string, sign_degree:float, house:int}
     */
    public function getTransitNow(User $user, string $planet): array
    {
        if ($user->current_lat === null || $user->current_lon === null) {
            throw new \RuntimeException('Hiányzik a jelenlegi hely (lat/lon) a profilból.');
        }

        $now = now()->utc();

        $payload = [
            'natal' => [
                // az ASC/house számításhoz a hely kell; a natál itt "dummy".
                'datetime_utc' => $now->toISOString(),
                'lat' => (float) $user->current_lat,
                'lon' => (float) $user->current_lon,
            ],
            'transit' => [
                'datetime_utc' => $now->toISOString(),
                'lat' => (float) $user->current_lat,
                'lon' => (float) $user->current_lon,
            ],
            'sidereal' => false,
            'ayanamsa' => 'lahiri',
            'house_system' => 'placidus',
        ];

        $data = $this->runPythonCalculator($payload);
        $planets = $data['transit']['planets'] ?? [];
        foreach ($planets as $p) {
            if (strcasecmp((string) ($p['name'] ?? ''), $planet) === 0) {
                return [
                    'planet' => (string) $p['name'],
                    'datetime_utc' => (string) ($data['transit']['datetime_utc'] ?? $now->toISOString()),
                    'longitude' => (float) ($p['longitude'] ?? 0),
                    'sign' => (string) ($p['sign'] ?? ''),
                    'sign_degree' => (float) ($p['sign_degree'] ?? 0),
                    'house' => (int) ($p['house'] ?? 0),
                ];
            }
        }

        throw new \RuntimeException('Ismeretlen bolygó: '.$planet);
    }

    /**
     * @return array<string,mixed>
     */
    private function runPythonCalculator(array $payload): array
    {
        $script = base_path('python/horoscope_calc.py');
        $python = HoroscopePython::binary();

        $process = new Process([$python, $script]);
        $process->setTimeout(30);
        $process->setInput(json_encode($payload));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('Tranzit számítás sikertelen: '.$process->getErrorOutput());
        }

        $data = json_decode($process->getOutput(), true);
        if (! is_array($data)) {
            throw new \RuntimeException('Érvénytelen python válasz (nem JSON).');
        }
        return $data;
    }
}
