<?php

namespace App\Http\Controllers;

use App\Models\HoroscopeChartExplanation;
use App\Models\HoroscopeDailyMessage;
use App\Services\AspectInterpretationService;
use App\Services\AstrologyChartScoringService;
use App\Services\AstrologyKnowledgeService;
use App\Services\ChatPrompts;
use App\Services\ChatService;
use App\Services\DailyHoroscopeService;
use App\Support\HoroscopeGenerationOptions;
use App\Support\HoroscopePeriod;
use Illuminate\Database\QueryException;
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
                'gender' => $chart->gender,
            ])->values(),
        ]);
    }

    public function geocode(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $country = strtolower(trim((string) $request->query('country', '')));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        if (! \App\Support\CountryList::isValid($country)) {
            return response()->json(['results' => []], 422);
        }

        try {
            $params = [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 8,
            ];

            if ($country !== '') {
                $params['countrycodes'] = $country;
            }

            $response = Http::withHeaders([
                'User-Agent' => self::DEFAULT_USER_AGENT,
            ])->get(self::NOMINATIM_URL, $params);

            if ($response->failed()) {
                return response()->json(['results' => []], 502);
            }

            $results = collect($response->json())
                ->map(fn ($item) => $this->mapGeocodeResult(is_array($item) ? $item : []))
                ->filter(fn ($item) => $item['display_name'] && $item['lat'] && $item['lon'])
                ->values();

            return response()->json(['results' => $results]);
        } catch (\Throwable $error) {
            Log::warning('Nominatim geocode failed', ['error' => $error->getMessage()]);

            return response()->json(['results' => []], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapGeocodeResult(array $item): array
    {
        $address = is_array($item['address'] ?? null) ? $item['address'] : [];
        $city = $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? $address['hamlet']
            ?? $address['suburb']
            ?? '';
        $countryName = (string) ($address['country'] ?? '');
        $countryCode = strtoupper((string) ($address['country_code'] ?? ''));
        $displayName = (string) ($item['display_name'] ?? '');
        $label = $city !== ''
            ? trim($city.($countryName !== '' ? ', '.$countryName : ''))
            : $displayName;

        return [
            'display_name' => $displayName,
            'label' => $label,
            'city' => (string) $city,
            'country' => $countryName,
            'country_code' => $countryCode,
            'lat' => $item['lat'] ?? null,
            'lon' => $item['lon'] ?? null,
        ];
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
                'error' => $this->jsonErrorMessage($error, 'Az elem leírása nem érhető el.'),
            ], 500);
        }
    }

    public function aspectInfo(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:natal,transit,synastry'],
            'aspect' => ['required', 'string', 'max:32'],
            'body1' => ['required', 'array'],
            'body1.name' => ['required', 'string', 'max:64'],
            'body1.sign' => ['nullable', 'string', 'max:32'],
            'body1.house' => ['nullable', 'integer', 'min:1', 'max:12'],
            'body1.sign_degree' => ['nullable', 'numeric'],
            'body1.owner' => ['nullable', 'string', 'max:32'],
            'body1.gender' => ['nullable', 'string', 'in:male,female'],
            'body1.retrograde' => ['nullable', 'boolean'],
            'body2' => ['required', 'array'],
            'body2.name' => ['required', 'string', 'max:64'],
            'body2.sign' => ['nullable', 'string', 'max:32'],
            'body2.house' => ['nullable', 'integer', 'min:1', 'max:12'],
            'body2.sign_degree' => ['nullable', 'numeric'],
            'body2.owner' => ['nullable', 'string', 'max:32'],
            'body2.gender' => ['nullable', 'string', 'in:male,female'],
            'body2.retrograde' => ['nullable', 'boolean'],
            'meta' => ['nullable', 'array'],
            'meta.chart_a_id' => ['nullable', 'integer'],
            'meta.chart_b_id' => ['nullable', 'integer'],
            'meta.side_a_is_now' => ['nullable', 'boolean'],
            'meta.side_b_is_now' => ['nullable', 'boolean'],
        ]);

        try {
            $result = app(AspectInterpretationService::class)->resolve(
                $request->user(),
                $validated,
                app()->getLocale(),
            );

            return response()->json($result);
        } catch (\InvalidArgumentException $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        } catch (\Throwable $error) {
            Log::error('Horoscope aspect info failed', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => $this->jsonErrorMessage($error, 'A fényszög leírása nem érhető el.'),
            ], 500);
        }
    }

    public function dailyMessage(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:single,dual'],
            'period' => ['nullable', 'string', 'in:daily,weekly,monthly'],
            'birth_chart_id' => ['nullable', 'integer'],
            'birth_chart_id_a' => ['nullable', 'integer'],
            'birth_chart_id_b' => ['nullable', 'integer'],
            'user_focus' => ['nullable', 'string', 'max:5000'],
            'detail_level' => ['nullable', 'string', 'in:short,normal,detailed'],
            'topics' => ['nullable', 'array'],
            'topics.*' => ['string', 'in:health,money,relationships,work'],
        ]);

        try {
            $service = app(DailyHoroscopeService::class);
            $locale = app()->getLocale();
            $period = HoroscopePeriod::normalize($validated['period'] ?? null);
            $options = HoroscopeGenerationOptions::fromRequest(
                $validated['user_focus'] ?? null,
                $validated['detail_level'] ?? null,
                $validated['topics'] ?? null,
            );

            if ($validated['mode'] === 'single') {
                if (empty($validated['birth_chart_id'])) {
                    return response()->json([
                        'error' => __('horoscope.daily_select_birth_chart'),
                    ], 422);
                }

                $message = $service->personalForBirthChart(
                    $request->user(),
                    (int) $validated['birth_chart_id'],
                    $locale,
                    $period,
                    $options,
                );
            } else {
                if (empty($validated['birth_chart_id_a']) || empty($validated['birth_chart_id_b'])) {
                    return response()->json([
                        'error' => __('horoscope.daily_select_two_birth_charts'),
                    ], 422);
                }

                $message = $service->partnershipForBirthCharts(
                    $request->user(),
                    (int) $validated['birth_chart_id_a'],
                    (int) $validated['birth_chart_id_b'],
                    $locale,
                    $period,
                    $options,
                );
            }

            return response()->json(array_merge(
                $this->horoscopeMessageJson($message),
                ['tokens_used' => $service->lastTokensUsed()],
            ));
        } catch (\InvalidArgumentException $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        } catch (\Throwable $error) {
            Log::error('Horoscope daily message failed', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => $this->jsonErrorMessage($error, __('horoscope.daily_error')),
            ], 500);
        }
    }

    public function dailyMessageExplanation(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:single,dual'],
            'birth_chart_id' => ['nullable', 'integer'],
            'birth_chart_id_a' => ['nullable', 'integer'],
            'birth_chart_id_b' => ['nullable', 'integer'],
            'is_now' => ['nullable', 'boolean'],
            'chart' => ['nullable', 'array'],
            'chart.datetime_utc' => ['required_with:is_now', 'date'],
            'chart.lat' => ['required_with:is_now', 'numeric', 'between:-90,90'],
            'chart.lon' => ['required_with:is_now', 'numeric', 'between:-180,180'],
            'user_focus' => ['nullable', 'string', 'max:5000'],
            'detail_level' => ['nullable', 'string', 'in:short,normal,detailed'],
            'topics' => ['nullable', 'array'],
            'topics.*' => ['string', 'in:health,money,relationships,work'],
        ]);

        try {
            $service = app(DailyHoroscopeService::class);
            $locale = app()->getLocale();
            $options = HoroscopeGenerationOptions::fromRequest(
                $validated['user_focus'] ?? null,
                $validated['detail_level'] ?? null,
                $validated['topics'] ?? null,
            );

            if ($validated['mode'] === 'single') {
                if (! empty($validated['is_now'])) {
                    $explanation = $service->personalNowChartExplanation(
                        $request->user(),
                        (string) $validated['chart']['datetime_utc'],
                        (float) $validated['chart']['lat'],
                        (float) $validated['chart']['lon'],
                        $locale,
                        $options,
                    );
                } elseif (! empty($validated['birth_chart_id'])) {
                    $explanation = $service->personalChartExplanation(
                        $request->user(),
                        (int) $validated['birth_chart_id'],
                        $locale,
                        $options,
                    );
                } else {
                    return response()->json([
                        'error' => __('horoscope.daily_select_birth_chart'),
                    ], 422);
                }
            } else {
                if (empty($validated['birth_chart_id_a']) || empty($validated['birth_chart_id_b'])) {
                    return response()->json([
                        'error' => __('horoscope.daily_select_two_birth_charts'),
                    ], 422);
                }

                $explanation = $service->partnershipChartExplanation(
                    $request->user(),
                    (int) $validated['birth_chart_id_a'],
                    (int) $validated['birth_chart_id_b'],
                    $locale,
                    $options,
                );
            }

            return response()->json(array_merge(
                $this->horoscopeExplanationJson($explanation),
                ['tokens_used' => $service->lastTokensUsed()],
            ));
        } catch (\InvalidArgumentException $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        } catch (\Throwable $error) {
            Log::error('Horoscope message explanation failed', ['error' => $error->getMessage()]);

            return response()->json([
                'error' => $this->jsonErrorMessage($error, __('horoscope.explanation_error')),
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function horoscopeExplanationJson(HoroscopeChartExplanation $explanation): array
    {
        $explanation->load(['birthChart', 'birthChartA', 'birthChartB']);

        if ($explanation->kind === HoroscopeChartExplanation::KIND_PARTNERSHIP) {
            $chartMeta = __('horoscope.explanation_partnership_meta', [
                'name_a' => $explanation->birthChartA?->name ?? 'A',
                'name_b' => $explanation->birthChartB?->name ?? 'B',
            ]);
        } elseif ($explanation->birthChart) {
            $chartMeta = __('horoscope.explanation_personal_meta', [
                'name' => $explanation->birthChart->name,
            ]);
        } else {
            $datetimeUtc = data_get($explanation->context_payload, 'datetime_utc');
            $formatted = $datetimeUtc
                ? \Illuminate\Support\Carbon::parse($datetimeUtc)->timezone(config('app.timezone', 'Europe/Budapest'))->format('Y.m.d H:i')
                : __('horoscope.now');
            $chartMeta = __('horoscope.explanation_now_meta', [
                'datetime' => $formatted,
            ]);
        }

        return [
            'kind' => $explanation->kind,
            'badge' => $explanation->kind === HoroscopeChartExplanation::KIND_PARTNERSHIP
                ? __('daily.horoscope_partnership_chart_badge')
                : __('daily.horoscope_birth_chart_badge'),
            'chart_meta' => $chartMeta,
            'explanation' => $explanation->explanation,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function horoscopeMessageJson(HoroscopeDailyMessage $message): array
    {
        $period = HoroscopePeriod::normalize($message->period_type);
        $periodStart = $message->period_start ?? $message->forecast_date;
        $periodEnd = $message->period_end ?? $message->forecast_date;

        return [
            'id' => $message->id,
            'kind' => $message->kind,
            'period' => $period,
            'badge' => $message->kind === HoroscopeDailyMessage::KIND_PARTNERSHIP
                ? __('daily.horoscope_partnership_badge')
                : __('daily.horoscope_personal_badge'),
            'forecast_date' => $message->forecast_date->format('Y.m.d'),
            'chart_meta' => $period === HoroscopePeriod::DAILY
                ? __('daily.chart_meta', [
                    'place' => config('daily_horoscope.location.label'),
                    'date' => $periodStart->format('Y.m.d'),
                ])
                : __('daily.period_meta', [
                    'place' => config('daily_horoscope.location.label'),
                    'start' => $periodStart->format('Y.m.d'),
                    'end' => $periodEnd->format('Y.m.d'),
                ]),
            'summary_title' => __('daily.summary_title_'.$period),
            'motto' => $message->motto,
            'summary' => $message->summary,
            'health' => $message->health,
            'money' => $message->money,
            'relationships' => $message->relationships,
            'work' => $message->work,
            'has_explanation' => trim((string) ($message->explanation ?? '')) !== '',
        ];
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

    private function jsonErrorMessage(\Throwable $error, string $fallback): string
    {
        if ($error instanceof QueryException) {
            $details = $error->getMessage();
            if (str_contains($details, 'horoscope_daily_messages')
                || str_contains($details, 'period_type')
                || str_contains($details, 'period_start')) {
                Log::error('Horoscope schema mismatch', ['error' => $details]);

                return __('horoscope.schema_outdated');
            }

            return $fallback;
        }

        $message = trim($error->getMessage());

        return $message !== '' ? $message : $fallback;
    }
}