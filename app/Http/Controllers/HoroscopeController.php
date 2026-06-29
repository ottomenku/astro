<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HoroscopeController extends Controller
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';
    private const DEFAULT_USER_AGENT = 'VS_Cline Horoszkop/1.0 (demo)';

    public function index()
    {
        $user = auth()->user();

        $birthCharts = $user?->birthCharts()->orderByDesc('is_default')->orderBy('name')->get() ?? collect();

        return view('horoscope', [
            'birthCharts' => $birthCharts,
            'birthChartsJson' => $birthCharts->map(fn ($chart) => [
                'id' => $chart->id,
                'name' => $chart->name,
                'datetime_utc' => $chart->birth_datetime_utc?->utc()->toIso8601String(),
                'offset' => $chart->birth_tz_offset,
                'label' => $chart->birth_place_label,
                'lat' => $chart->birth_lat,
                'lon' => $chart->birth_lon,
                'is_default' => $chart->is_default,
            ])->values(),
        ]);
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
            'ayanamsa' => $validated['ayanamsa'] ?? 'lahiri',
            'house_system' => $validated['house_system'] ?? 'placidus',
        ];

        try {
            $data = app(\App\Services\HoroscopeCalculator::class)->calculate($payload);

            return response()->json($data);
        } catch (\Throwable $error) {
            Log::error('Horoscope calc exception', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => 'A horoszkóp számítás sikertelen.',
                'details' => $error->getMessage(),
            ], 500);
        }
    }
}