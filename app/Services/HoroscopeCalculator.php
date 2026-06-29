<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

class HoroscopeCalculator
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function calculate(array $payload): array
    {
        $script = base_path('python/horoscope_calc.py');
        $python = $this->resolvePythonBinary();

        $process = new Process([$python, $script]);
        $process->setTimeout(30);
        $process->setInput(json_encode($payload));
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Horoscope calc failed', [
                'python' => $python,
                'script' => $script,
                'error' => $process->getErrorOutput(),
                'output' => $process->getOutput(),
            ]);

            throw new RuntimeException($process->getErrorOutput() ?: 'A horoszkóp számítás sikertelen.');
        }

        $data = json_decode($process->getOutput(), true);
        if (! is_array($data)) {
            throw new RuntimeException('Érvénytelen válasz a számítóból.');
        }

        return $data;
    }

    protected function resolvePythonBinary(): string
    {
        $defaultPython = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $pythonFromEnv = (string) env('HOROSCOPE_PYTHON_BIN', '');

        if (PHP_OS_FAMILY === 'Windows' && ($pythonFromEnv === '' || $pythonFromEnv === 'python3')) {
            return 'python';
        }

        return $pythonFromEnv !== '' ? $pythonFromEnv : $defaultPython;
    }
}
