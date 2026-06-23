<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class HoroscopeController extends Controller
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';
    private const DEFAULT_USER_AGENT = 'VS_Cline Horoszkop/1.0 (demo)';

    public function index()
    {
        return view('horoscope');
    }

    public function geocode(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 3) {
            return response()->json(['results' => []]);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => self::DEFAULT_USER_AGENT,
            ])->get(self::NOMINATIM_URL, [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 5,
            ]);

            if ($response->failed()) {
                return response()->json(['results' => []], 502);
            }

            $results = collect($response->json())
                ->map(fn ($item) => [
                    'display_name' => $item['display_name'] ?? '',
                    'lat' => $item['lat'] ?? null,
                    'lon' => $item['lon'] ?? null,
                ])
                ->filter(fn ($item) => $item['display_name'] && $item['lat'] && $item['lon'])
                ->values();

            return response()->json(['results' => $results]);
        } catch (\Throwable $error) {
            Log::warning('Nominatim geocode failed', ['error' => $error->getMessage()]);

            return response()->json(['results' => []], 500);
        }
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:4000'],
            'chart' => ['nullable', 'array'],
        ]);

        try {
            $system = implode("\n", [
                'Te egy asztrológiai asszisztens vagy.',
                'Válaszolj magyarul, tömören és érthetően.',
                'A kérdés az aktuális horoszkóp ábrához kapcsolódik.',
            ]);

            if (! empty($validated['chart'])) {
                $system .= "\n\nAktuális horoszkóp adatok (JSON):\n"
                    .json_encode($validated['chart'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $result = app(ChatService::class)->sendWithSystem(
                $request->user(),
                $validated['prompt'],
                $system
            );

            return response()->json([
                'response' => $result['answer'],
            ]);
        } catch (\Throwable $error) {
            Log::error('Horoscope chat failed', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => $error->getMessage() ?: 'A chat hívás sikertelen.',
            ], 500);
        }
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'natal' => ['required', 'array'],
            'natal.datetime_utc' => ['required', 'date'],
            'natal.lat' => ['required', 'numeric'],
            'natal.lon' => ['required', 'numeric'],
            'transit' => ['required', 'array'],
            'transit.datetime_utc' => ['required', 'date'],
            'transit.lat' => ['required', 'numeric'],
            'transit.lon' => ['required', 'numeric'],
            'sidereal' => ['sometimes', 'boolean'],
            'ayanamsa' => ['sometimes', 'string', 'in:lahiri'],
            'house_system' => ['sometimes', 'string', 'in:whole_sign,placidus'],
        ]);

        $payload = [
            'natal' => $validated['natal'],
            'transit' => $validated['transit'],
            'sidereal' => (bool) ($validated['sidereal'] ?? false),
            // csak akkor értelmes, ha sidereal=true
            'ayanamsa' => $validated['ayanamsa'] ?? 'lahiri',
            'house_system' => $validated['house_system'] ?? 'placidus',
        ];

        $script = base_path('python/horoscope_calc.py');
        $defaultPython = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $pythonFromEnv = (string) env('HOROSCOPE_PYTHON_BIN', '');
        // Windows alatt gyakran van külön "python3" shim (pl. Store), amiben nincs telepítve a swisseph.
        // Ha a .env-ben nincs explicit bin megadva, vagy valaki python3-at állított be, akkor is inkább a "python"-t használjuk.
        if (PHP_OS_FAMILY === 'Windows' && ($pythonFromEnv === '' || $pythonFromEnv === 'python3')) {
            $python = 'python';
        } else {
            $python = $pythonFromEnv !== '' ? $pythonFromEnv : $defaultPython;
        }
        $process = new Process([$python, $script]);
        $process->setTimeout(30);
        $process->setInput(json_encode($payload));

        try {
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('Horoscope calc failed', [
                    'python' => $python,
                    'script' => $script,
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                ]);

                return response()->json([
                    'error' => 'A horoszkóp számítás sikertelen.',
                    'details' => $process->getErrorOutput(),
                    'python' => $python,
                ], 500);
            }

            $data = json_decode($process->getOutput(), true);
            if (! is_array($data)) {
                return response()->json([
                    'error' => 'Érvénytelen válasz a számítóból.',
                    'details' => $process->getOutput(),
                ], 500);
            }

            return response()->json($data);
        } catch (\Throwable $error) {
            Log::error('Horoscope calc exception', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => 'Hiba történt a számítás során.',
            ], 500);
        }
    }
}