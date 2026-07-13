<?php

namespace App\Http\Controllers;

use App\Services\AstrologyChartScoringService;
use App\Services\AstrologyKnowledgeService;
use App\Services\ChatPrompts;
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

    public function elementInfo(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:sign,planet,fixed_star'],
            'key' => ['required', 'string', 'max:64'],
            'title' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $result = app(AstrologyKnowledgeService::class)->resolve(
                $request->user(),
                $validated['type'],
                $validated['key'],
                app()->getLocale(),
                (string) ($validated['title'] ?? $validated['key'])
            );

            return response()->json($result);
        } catch (\InvalidArgumentException $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        } catch (\Throwable $error) {
            Log::error('Horoscope element info failed', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => $error->getMessage() ?: 'Az elem leírása nem érhető el.',
            ], 500);
        }
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:4000'],
            'chart' => ['nullable', 'array'],
            'birth_chart_id' => ['nullable', 'integer'],
        ]);

        try {
            $user = $request->user();
            $scoreContext = null;

            if (! empty($validated['chart'])) {
                $chartScore = app(AstrologyChartScoringService::class)->scoreFromCalculatedData(
                    $user,
                    $validated['chart'],
                    $validated['birth_chart_id'] ?? null,
                );
                $scoreContext = $chartScore?->toContextArray();
            } elseif (! empty($validated['birth_chart_id'])) {
                $existing = app(AstrologyChartScoringService::class)
                    ->findScoreForBirthChart((int) $validated['birth_chart_id'], $user);
                $scoreContext = $existing?->toContextArray();
            }

            $result = app(ChatService::class)->sendWithSystem(
                $user,
                $validated['prompt'],
                ChatPrompts::horoscopeSystem($validated['chart'] ?? null, $scoreContext)
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
            'birth_chart_id' => ['nullable', 'integer'],
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

            if ($request->user()) {
                try {
                    app(AstrologyChartScoringService::class)->scoreFromCalculatedData(
                        $request->user(),
                        $data,
                        isset($validated['birth_chart_id']) ? (int) $validated['birth_chart_id'] : null,
                    );
                } catch (\Throwable $scoreError) {
                    Log::warning('Horoscope scoring skipped', ['error' => $scoreError->getMessage()]);
                }
            }

            return response()->json($data);
        } catch (\Throwable $error) {
            Log::error('Horoscope calc exception', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => 'A horoszkóp számítás sikertelen.',
                'details' => trim($error->getMessage()) ?: null,
            ], 500);
        }
    }
}