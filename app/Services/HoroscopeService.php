<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserHoroscope;
use Symfony\Component\Process\Process;

class HoroscopeService
{
    /**
     * Natál képlet számítása és mentése.
     */
    public function calculateAndStoreNatal(User $user, array $options = []): UserHoroscope
    {
        $payload = $this->buildNatalPayloadFromUser($user, $options);
        $data = $this->runPythonCalculator($payload);

        // a python output most: sidereal/ayanamsa/house_system + natal + transit
        // nekünk a natál rész kell adatbázisba.
        $store = [
            'asc' => $data['natal']['asc'] ?? null,
            'mc' => $data['natal']['mc'] ?? null,
            'houses' => $data['natal']['houses'] ?? null,
            'planets' => $data['natal']['planets'] ?? null,
            // aspects: később kerül bele (python bővítés után)
            'aspects' => $data['natal']['aspects'] ?? [],
        ];

        return UserHoroscope::create([
            'user_id' => $user->id,
            'kind' => 'natal',
            'label' => 'Natál',
            'sidereal' => (bool) ($data['sidereal'] ?? false),
            'ayanamsa' => $data['ayanamsa'] ?? null,
            'house_system' => (string) ($data['house_system'] ?? ($options['house_system'] ?? 'placidus')),
            'data' => $store,
            'calculated_at' => now(),
        ]);
    }

    /**
     * @return array{sidereal:bool, ayanamsa:?string, house_system:string, natal:array, transit:array}
     */
    private function runPythonCalculator(array $payload): array
    {
        $script = base_path('python/horoscope_calc.py');
        $defaultPython = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $pythonFromEnv = (string) env('HOROSCOPE_PYTHON_BIN', '');

        if (PHP_OS_FAMILY === 'Windows' && ($pythonFromEnv === '' || $pythonFromEnv === 'python3')) {
            $python = 'python';
        } else {
            $python = $pythonFromEnv !== '' ? $pythonFromEnv : $defaultPython;
        }

        $process = new Process([$python, $script]);
        $process->setTimeout(30);
        $process->setInput(json_encode($payload));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('Horoszkóp számítás sikertelen: '.$process->getErrorOutput());
        }

        $data = json_decode($process->getOutput(), true);
        if (! is_array($data)) {
            throw new \RuntimeException('Érvénytelen python válasz (nem JSON).');
        }
        return $data;
    }

    private function buildNatalPayloadFromUser(User $user, array $options): array
    {
        $chart = $user->defaultBirthChart();

        if (! $chart || ! $chart->birth_datetime_utc || $chart->birth_lat === null || $chart->birth_lon === null) {
            throw new \RuntimeException('Hiányos születési adat – nem számolható natál képlet.');
        }

        $sidereal = (bool) ($options['sidereal'] ?? false);
        $houseSystem = (string) ($options['house_system'] ?? 'placidus');

        // A python két chartot vár (natal+transit). Natálnál transit = natal (mert most csak natált mentünk)
        $entry = [
            'datetime_utc' => $chart->birth_datetime_utc->utc()->toIso8601String(),
            'lat' => (float) $chart->birth_lat,
            'lon' => (float) $chart->birth_lon,
        ];

        return [
            'natal' => $entry,
            'transit' => $entry,
            'sidereal' => $sidereal,
            'ayanamsa' => $sidereal ? (string) ($options['ayanamsa'] ?? 'lahiri') : 'lahiri',
            'house_system' => $houseSystem,
        ];
    }
}
