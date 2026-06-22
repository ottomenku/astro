<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\Process\Process;

class TransitSearchService
{
    /**
     * @param  array<string,mixed>  $validated
     * @return array{found:bool, datetime_utc:?string, meta:?array}
     */
    public function findEvent(User $user, array $validated): array
    {
        if ($user->current_lat === null || $user->current_lon === null) {
            throw new \RuntimeException('Hiányzik a jelenlegi hely (lat/lon) a profilból.');
        }

        $payload = [
            'datetime_start_utc' => $validated['datetime_start_utc'],
            'datetime_end_utc' => $validated['datetime_end_utc'],
            'lat' => (float) $user->current_lat,
            'lon' => (float) $user->current_lon,
            'sidereal' => (bool) ($validated['sidereal'] ?? false),
            'ayanamsa' => (string) ($validated['ayanamsa'] ?? 'lahiri'),
            'house_system' => (string) ($validated['house_system'] ?? 'placidus'),
            'step_hours' => (float) ($validated['step_hours'] ?? 6),
            'event' => $validated['event'],
        ];

        $data = $this->runPythonSearch($payload);

        return [
            'found' => (bool) ($data['found'] ?? false),
            'datetime_utc' => $data['datetime_utc'] ?? null,
            'meta' => is_array($data['meta'] ?? null) ? $data['meta'] : null,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function runPythonSearch(array $payload): array
    {
        $script = base_path('python/transit_search.py');
        $defaultPython = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $pythonFromEnv = (string) env('HOROSCOPE_PYTHON_BIN', '');

        if (PHP_OS_FAMILY === 'Windows' && ($pythonFromEnv === '' || $pythonFromEnv === 'python3')) {
            $python = 'python';
        } else {
            $python = $pythonFromEnv !== '' ? $pythonFromEnv : $defaultPython;
        }

        $process = new Process([$python, $script]);
        $process->setTimeout(60);
        $process->setInput(json_encode($payload));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('Esemény keresés sikertelen: '.$process->getErrorOutput());
        }

        $data = json_decode($process->getOutput(), true);
        if (! is_array($data)) {
            throw new \RuntimeException('Érvénytelen python válasz (nem JSON).');
        }
        return $data;
    }
}
