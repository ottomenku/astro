<?php

namespace App\Services;

use App\Models\BirthChart;
use App\Models\DailyHoroscopeMessage;
use App\Models\DailyHoroscopeSetting;
use App\Models\ScoringProfile;
use App\Models\User;
use App\Models\UserDailyHoroscopeMessage;
use App\Models\UserDailyHoroscopeSetting;
use App\Models\UserHoroscope;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DailyHoroscopeService
{
    public function __construct(
        private readonly HoroscopeCalculator $calculator,
        private readonly AstrologyChartScoringService $scoring,
        private readonly DailyHoroscopePromptBuilder $promptBuilder,
        private readonly OpenAIClient $openAi,
    ) {}

    /**
     * Nyitólap: publikált globális vagy személyes napi üzenet.
     */
    public function forHomepage(?User $user = null, ?string $locale = null): DailyHoroscopeMessage|UserDailyHoroscopeMessage|null
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));

        if ($user) {
            $personalSettings = UserDailyHoroscopeSetting::forUser($user);
            if ($personalSettings->use_personal_daily) {
                return $this->personalForToday($user, $locale);
            }
        }

        return $this->publishedForToday($locale, $user);
    }

    public function publishedForToday(?string $locale = null, ?User $user = null): ?DailyHoroscopeMessage
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $forecastDate = $this->forecastDate();

        $published = DailyHoroscopeMessage::query()
            ->where('forecast_date', $forecastDate)
            ->where('locale', $locale)
            ->where('status', DailyHoroscopeMessage::STATUS_PUBLISHED)
            ->first();

        if ($published) {
            return $published;
        }

        $setting = DailyHoroscopeSetting::forLocale($locale);
        if (! $setting->auto_publish) {
            return null;
        }

        $lockKey = sprintf('daily-horoscope:%s:%s', $forecastDate->toDateString(), $locale);

        return Cache::lock($lockKey, 120)->block(30, function () use ($forecastDate, $locale, $user, $setting) {
            $existing = DailyHoroscopeMessage::query()
                ->where('forecast_date', $forecastDate)
                ->where('locale', $locale)
                ->where('status', DailyHoroscopeMessage::STATUS_PUBLISHED)
                ->first();

            if ($existing) {
                return $existing;
            }

            return $this->generateGlobal($forecastDate, $locale, $user, publish: true);
        });
    }

    public function draftForToday(string $locale): ?DailyHoroscopeMessage
    {
        $forecastDate = $this->forecastDate();

        return DailyHoroscopeMessage::query()
            ->where('forecast_date', $forecastDate)
            ->where('locale', $locale)
            ->first();
    }

    public function regenerateGlobal(string $locale, User $admin): DailyHoroscopeMessage
    {
        $forecastDate = $this->forecastDate();
        $setting = DailyHoroscopeSetting::forLocale($locale);

        return $this->generateGlobal(
            $forecastDate,
            $locale,
            $admin,
            publish: (bool) $setting->auto_publish,
            force: true,
        );
    }

    /**
     * @param  array<string, string>  $content
     */
    public function updateDraft(string $locale, array $content): DailyHoroscopeMessage
    {
        $message = $this->draftForToday($locale)
            ?? throw new \RuntimeException('Nincs mai piszkozat – előbb generálj.');

        $message->update([
            'motto' => trim($content['motto']),
            'summary' => trim($content['summary']),
            'health' => trim($content['health']),
            'money' => trim($content['money']),
            'relationships' => trim($content['relationships']),
            'work' => trim($content['work']),
        ]);

        return $message->fresh();
    }

    public function publishDraft(string $locale, User $admin): DailyHoroscopeMessage
    {
        $message = $this->draftForToday($locale)
            ?? throw new \RuntimeException('Nincs mai piszkozat a publikáláshoz.');

        $message->update([
            'status' => DailyHoroscopeMessage::STATUS_PUBLISHED,
            'published_at' => now(),
            'approved_by_user_id' => $admin->id,
        ]);

        return $message->fresh();
    }

    public function personalForToday(User $user, ?string $locale = null): UserDailyHoroscopeMessage
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $forecastDate = $this->forecastDate();

        $cached = UserDailyHoroscopeMessage::query()
            ->where('user_id', $user->id)
            ->where('forecast_date', $forecastDate)
            ->where('locale', $locale)
            ->first();

        if ($cached) {
            return $cached;
        }

        $lockKey = sprintf('user-daily-horoscope:%d:%s:%s', $user->id, $forecastDate->toDateString(), $locale);

        return Cache::lock($lockKey, 120)->block(30, function () use ($user, $forecastDate, $locale) {
            $existing = UserDailyHoroscopeMessage::query()
                ->where('user_id', $user->id)
                ->where('forecast_date', $forecastDate)
                ->where('locale', $locale)
                ->first();

            if ($existing) {
                return $existing;
            }

            return $this->generatePersonal($user, $forecastDate, $locale);
        });
    }

    /**
     * @deprecated Use forHomepage() or publishedForToday()
     */
    public function forToday(?User $user = null, ?string $locale = null): DailyHoroscopeMessage
    {
        $message = $this->publishedForToday($locale, $user);

        if ($message) {
            return $message;
        }

        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $forecastDate = $this->forecastDate();

        return $this->generateGlobal($forecastDate, $locale, $user, publish: true);
    }

    private function generateGlobal(
        Carbon $forecastDate,
        string $locale,
        ?User $user,
        bool $publish,
        bool $force = false,
    ): DailyHoroscopeMessage {
        $chartDatetimeUtc = $this->noonUtcForDate($forecastDate);
        $chartPayload = $this->calculateChart($chartDatetimeUtc);
        $setting = DailyHoroscopeSetting::forLocale($locale);
        $allScores = $this->scoring->scoreAllPayloads($chartPayload);
        $scoreContext = $this->resolveScoreContext($setting, $allScores);
        $profileName = (string) ($scoreContext['profile'] ?? '');

        $generated = $this->generateWithLlm(
            system: $this->promptBuilder->globalSystemPrompt($locale),
            userPrompt: $this->promptBuilder->globalUserPrompt($locale, $chartPayload, $scoreContext),
            user: $user,
        );

        $attributes = [
            'chart_datetime_utc' => $chartDatetimeUtc,
            'chart_payload' => $chartPayload,
            'score_payload' => $scoreContext,
            'scoring_profile_name' => $profileName,
            'motto' => $generated['motto'],
            'summary' => $generated['summary'],
            'health' => $generated['health'],
            'money' => $generated['money'],
            'relationships' => $generated['relationships'],
            'work' => $generated['work'],
            'generated_by_user_id' => $user?->id,
            'generated_at' => now(),
            'status' => $publish ? DailyHoroscopeMessage::STATUS_PUBLISHED : DailyHoroscopeMessage::STATUS_DRAFT,
            'published_at' => $publish ? now() : null,
            'approved_by_user_id' => $publish ? $user?->id : null,
        ];

        $existing = DailyHoroscopeMessage::query()
            ->where('forecast_date', $forecastDate->toDateString())
            ->where('locale', $locale)
            ->first();

        if ($existing && ! $force) {
            return $existing;
        }

        try {
            if ($existing) {
                $existing->update($attributes);

                return $existing->fresh();
            }

            return DailyHoroscopeMessage::create([
                'forecast_date' => $forecastDate->toDateString(),
                'locale' => $locale,
                ...$attributes,
            ]);
        } catch (QueryException $error) {
            if ($this->isUniqueConstraintViolation($error)) {
                return DailyHoroscopeMessage::query()
                    ->where('forecast_date', $forecastDate)
                    ->where('locale', $locale)
                    ->firstOrFail();
            }

            throw $error;
        }
    }

    private function generatePersonal(User $user, Carbon $forecastDate, string $locale): UserDailyHoroscopeMessage
    {
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['birthChart', 'userHoroscope', 'scoringProfile']);

        $chartDatetimeUtc = $this->noonUtcForDate($forecastDate);
        $chartPayload = $this->calculateChart($chartDatetimeUtc);
        $attachedPayload = $this->buildAttachedChartPayload($user, $settings);

        $profile = $settings->resolvedScoringProfile();
        $scoreContext = $profile
            ? $this->scoring->scorePayloadForProfile($chartPayload, $profile)
            : [];

        $generated = $this->generateWithLlm(
            system: $this->promptBuilder->userSystemPrompt($settings, $locale),
            userPrompt: $this->promptBuilder->userUserPrompt(
                $settings,
                $locale,
                $chartPayload,
                $scoreContext,
                $attachedPayload,
            ),
            user: $user,
        );

        try {
            return UserDailyHoroscopeMessage::create([
                'user_id' => $user->id,
                'forecast_date' => $forecastDate->toDateString(),
                'locale' => $locale,
                'chart_datetime_utc' => $chartDatetimeUtc,
                'chart_payload' => $chartPayload,
                'score_payload' => $scoreContext,
                'attached_chart_payload' => $attachedPayload,
                'scoring_profile_name' => (string) ($scoreContext['profile'] ?? ''),
                'motto' => $generated['motto'],
                'summary' => $generated['summary'],
                'health' => $generated['health'],
                'money' => $generated['money'],
                'relationships' => $generated['relationships'],
                'work' => $generated['work'],
                'generated_at' => now(),
            ]);
        } catch (QueryException $error) {
            if ($this->isUniqueConstraintViolation($error)) {
                return UserDailyHoroscopeMessage::query()
                    ->where('user_id', $user->id)
                    ->where('forecast_date', $forecastDate)
                    ->where('locale', $locale)
                    ->firstOrFail();
            }

            throw $error;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildAttachedChartPayload(User $user, UserDailyHoroscopeSetting $settings): ?array
    {
        if ($settings->birth_chart_id) {
            $birthChart = BirthChart::query()
                ->where('user_id', $user->id)
                ->whereKey($settings->birth_chart_id)
                ->first();

            if (! $birthChart) {
                return null;
            }

            $payload = $this->buildPayloadFromBirthChart($birthChart, $user);
            $calculated = $this->calculator->calculate($payload);
            $profile = $settings->resolvedScoringProfile();
            $score = $profile ? $this->scoring->scorePayloadForProfile($calculated, $profile) : [];

            return [
                'source' => 'birth_chart',
                'label' => $birthChart->name,
                'chart' => $calculated,
                'score' => $score,
            ];
        }

        if ($settings->user_horoscope_id) {
            $horoscope = UserHoroscope::query()
                ->where('user_id', $user->id)
                ->whereKey($settings->user_horoscope_id)
                ->first();

            if (! $horoscope) {
                return null;
            }

            $calculated = [
                'sidereal' => (bool) $horoscope->sidereal,
                'house_system' => (string) $horoscope->house_system,
                'ayanamsa' => $horoscope->ayanamsa,
                'natal' => (array) ($horoscope->data ?? []),
            ];

            $profile = $settings->resolvedScoringProfile();
            $score = $profile ? $this->scoring->scorePayloadForProfile($calculated, $profile) : [];

            return [
                'source' => 'user_horoscope',
                'label' => $horoscope->label,
                'chart' => $calculated,
                'score' => $score,
            ];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayloadFromBirthChart(BirthChart $birthChart, User $user): array
    {
        $datetime = $birthChart->corrected_datetime_utc ?? $birthChart->birth_datetime_utc;
        if (! $datetime || $birthChart->birth_lat === null || $birthChart->birth_lon === null) {
            throw new \RuntimeException('A csatolt születési képlet adatai hiányosak.');
        }

        $sidereal = ($user->zodiac_mode ?? 'tropical') === 'sidereal';
        $entry = [
            'datetime_utc' => $datetime->utc()->toIso8601String(),
            'lat' => (float) $birthChart->birth_lat,
            'lon' => (float) $birthChart->birth_lon,
        ];

        return [
            'natal' => $entry,
            'transit' => $entry,
            'sidereal' => $sidereal,
            'ayanamsa' => 'lahiri',
            'house_system' => (string) ($user->house_system ?? 'placidus'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateChart(Carbon $chartDatetimeUtc): array
    {
        $location = config('daily_horoscope.location');
        $entry = [
            'datetime_utc' => $chartDatetimeUtc->toIso8601String(),
            'lat' => (float) ($location['lat'] ?? 47.497913),
            'lon' => (float) ($location['lon'] ?? 19.040236),
        ];

        $sidereal = config('daily_horoscope.zodiac_mode') === 'sidereal';

        return $this->calculator->calculate([
            'natal' => $entry,
            'transit' => $entry,
            'sidereal' => $sidereal,
            'ayanamsa' => 'lahiri',
            'house_system' => (string) config('daily_horoscope.house_system', 'placidus'),
        ]);
    }

    private function forecastDate(): Carbon
    {
        $timezone = (string) config('daily_horoscope.timezone', 'Europe/Budapest');

        return Carbon::now($timezone)->startOfDay();
    }

    private function noonUtcForDate(Carbon $forecastDate): Carbon
    {
        $timezone = (string) config('daily_horoscope.timezone', 'Europe/Budapest');

        return $forecastDate->copy()
            ->timezone($timezone)
            ->setTime(12, 0, 0)
            ->utc();
    }

    /**
     * @return array{motto: string, summary: string, health: string, money: string, relationships: string, work: string}
     */
    private function generateWithLlm(string $system, string $userPrompt, ?User $user): array
    {
        $apiKey = (string) config('services.openai.api_key');
        if (trim($apiKey) === '') {
            throw new \RuntimeException('Hiányzik az OPENAI_API_KEY (.env).');
        }

        $response = $this->openAi->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $userPrompt],
        ], null, [
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->failed()) {
            Log::warning('Daily horoscope LLM failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('A napi horoszkóp szöveg generálása sikertelen.');
        }

        $content = (string) ($response->json('choices.0.message.content') ?? '');
        $parsed = json_decode($content, true);

        if (! is_array($parsed)) {
            throw new \RuntimeException('Az LM válasza nem értelmezhető JSON.');
        }

        if ($user) {
            $usage = (array) ($response->json('usage') ?? []);
            $total = (int) ($usage['total_tokens'] ?? 0);
            if ($total > 0 && $user->token_quota_total > 0) {
                $user->increment('token_quota_used', $total);
            }
        }

        $generated = [
            'motto' => trim((string) ($parsed['motto'] ?? '')),
            'summary' => trim((string) ($parsed['summary'] ?? '')),
            'health' => trim((string) ($parsed['health'] ?? '')),
            'money' => trim((string) ($parsed['money'] ?? '')),
            'relationships' => trim((string) ($parsed['relationships'] ?? '')),
            'work' => trim((string) ($parsed['work'] ?? '')),
        ];

        foreach (['motto', 'summary', 'health', 'money', 'relationships', 'work'] as $field) {
            if ($generated[$field] === '') {
                throw new \RuntimeException("Hiányzó mező az LM válaszában: {$field}");
            }
        }

        return $generated;
    }

    /**
     * @return array{
     *     forecast_date: string,
     *     chart_datetime_utc: string,
     *     chart_payload: array<string, mixed>,
     *     scores: array<int, array<string, mixed>>
     * }
     */
    public function previewPayload(string $locale): array
    {
        $forecastDate = $this->forecastDate();
        $chartDatetimeUtc = $this->noonUtcForDate($forecastDate);
        $chartPayload = $this->calculateChart($chartDatetimeUtc);

        return [
            'forecast_date' => $forecastDate->toDateString(),
            'chart_datetime_utc' => $chartDatetimeUtc->toIso8601String(),
            'chart_payload' => $chartPayload,
            'scores' => $this->scoring->scoreAllPayloads($chartPayload),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $allScores
     * @return array<string, mixed>
     */
    private function resolveScoreContext(DailyHoroscopeSetting $setting, array $allScores): array
    {
        $profileId = $setting->scoring_profile_id;

        if ($profileId && isset($allScores[$profileId])) {
            return $allScores[$profileId];
        }

        $profile = $setting->scoringProfile ?? ScoringProfile::defaultProfile();
        if ($profile && isset($allScores[$profile->id])) {
            return $allScores[$profile->id];
        }

        return reset($allScores) ?: [];
    }

    private function isUniqueConstraintViolation(QueryException $error): bool
    {
        $code = (string) $error->getCode();

        return str_contains($error->getMessage(), 'Duplicate entry')
            || str_contains($error->getMessage(), 'UNIQUE constraint failed')
            || $code === '23000';
    }
}
