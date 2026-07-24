<?php

namespace App\Services;

use App\Models\BirthChart;
use App\Models\DailyHoroscopeMessage;
use App\Models\DailyHoroscopeSetting;
use App\Models\HoroscopeChartExplanation;
use App\Models\HoroscopeDailyMessage;
use App\Models\ScoringProfile;
use App\Models\User;
use App\Models\UserDailyHoroscopeMessage;
use App\Models\UserDailyHoroscopeSetting;
use App\Models\UserHoroscope;
use App\Support\HoroscopePeriod;
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
        private readonly PeriodHoroscopeContextBuilder $periodContext,
        private readonly OpenAIClient $openAi,
    ) {}

    /**
     * Nyitólap: publikált globális vagy személyes üzenet (napi / heti / havi).
     */
    public function forHomepage(
        ?User $user = null,
        ?string $locale = null,
        ?string $periodType = null,
    ): DailyHoroscopeMessage|UserDailyHoroscopeMessage|null {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $bounds = HoroscopePeriod::bounds($periodType);

        $this->ensureGlobalGenerated($locale, $bounds, $user);

        if ($user) {
            $personalSettings = UserDailyHoroscopeSetting::forUser($user);
            if ($personalSettings->use_personal_daily) {
                return $this->personalForPeriod($user, $locale, $bounds);
            }
        }

        return $this->publishedGlobalForPeriod($locale, $bounds);
    }

    public function personalForBirthChart(
        User $user,
        int $birthChartId,
        ?string $locale = null,
        ?string $periodType = null,
    ): HoroscopeDailyMessage {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $bounds = HoroscopePeriod::bounds($periodType);
        $cacheKey = $this->personalCacheKey($birthChartId);
        $birthChart = $this->resolveUserBirthChart($user, $birthChartId);

        $cached = $this->findHoroscopeMessage($user->id, $locale, $cacheKey, $bounds);
        if ($cached) {
            return $cached;
        }

        $lockKey = sprintf(
            'horoscope-message:personal:%d:%s:%s:%s:%s',
            $user->id,
            $birthChartId,
            $bounds['type'],
            $bounds['start']->toDateString(),
            $locale,
        );

        return Cache::lock($lockKey, 180)->block(180, function () use ($user, $birthChart, $bounds, $locale, $cacheKey) {
            $existing = $this->findHoroscopeMessage($user->id, $locale, $cacheKey, $bounds);
            if ($existing) {
                return $existing;
            }

            return $this->generateHoroscopePersonal($user, $birthChart, $bounds, $locale, $cacheKey);
        });
    }

    public function partnershipForBirthCharts(
        User $user,
        int $birthChartIdA,
        int $birthChartIdB,
        ?string $locale = null,
        ?string $periodType = null,
    ): HoroscopeDailyMessage {
        if ($birthChartIdA === $birthChartIdB) {
            throw new \InvalidArgumentException('A párkapcsolati napi üzenethez két különböző születési adat kell.');
        }

        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $bounds = HoroscopePeriod::bounds($periodType);
        [$firstId, $secondId] = $this->sortedBirthChartPair($birthChartIdA, $birthChartIdB);
        $cacheKey = $this->partnershipCacheKey($firstId, $secondId);
        $chartA = $this->resolveUserBirthChart($user, $firstId);
        $chartB = $this->resolveUserBirthChart($user, $secondId);

        $cached = $this->findHoroscopeMessage($user->id, $locale, $cacheKey, $bounds);
        if ($cached) {
            return $cached;
        }

        $lockKey = sprintf(
            'horoscope-message:partnership:%d:%s:%s:%s:%s',
            $user->id,
            $cacheKey,
            $bounds['type'],
            $bounds['start']->toDateString(),
            $locale,
        );

        return Cache::lock($lockKey, 180)->block(180, function () use ($user, $chartA, $chartB, $bounds, $locale, $cacheKey, $firstId, $secondId) {
            $existing = $this->findHoroscopeMessage($user->id, $locale, $cacheKey, $bounds);
            if ($existing) {
                return $existing;
            }

            return $this->generateHoroscopePartnership($user, $chartA, $chartB, $bounds, $locale, $cacheKey, $firstId, $secondId);
        });
    }

    public function ensureHoroscopeExplanation(HoroscopeDailyMessage $message, User $user): string
    {
        if (trim((string) ($message->explanation ?? '')) !== '') {
            return (string) $message->explanation;
        }

        $lockKey = sprintf('horoscope-explanation:%d', $message->id);

        return Cache::lock($lockKey, 180)->block(180, function () use ($message, $user): string {
            $message->refresh();
            if (trim((string) ($message->explanation ?? '')) !== '') {
                return (string) $message->explanation;
            }

            $explanation = $this->generateHoroscopeExplanation($message, $user);
            $message->update(['explanation' => $explanation]);

            return $explanation;
        });
    }

    public function personalChartExplanation(User $user, int $birthChartId, ?string $locale = null): HoroscopeChartExplanation
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $cacheKey = $this->personalProfileCacheKey($birthChartId);
        $birthChart = $this->resolveUserBirthChart($user, $birthChartId);

        $cached = $this->findChartExplanation($user->id, $locale, $cacheKey);
        if ($cached) {
            return $cached;
        }

        $lockKey = sprintf(
            'horoscope-chart-explanation:personal:%d:%d:%s',
            $user->id,
            $birthChartId,
            $locale,
        );

        return Cache::lock($lockKey, 900)->block(900, function () use ($user, $birthChart, $locale, $cacheKey, $birthChartId) {
            $existing = $this->findChartExplanation($user->id, $locale, $cacheKey);
            if ($existing) {
                return $existing;
            }

            return $this->generatePersonalChartExplanation($user, $birthChart, $locale, $cacheKey, $birthChartId);
        });
    }

    public function personalNowChartExplanation(
        User $user,
        string $datetimeUtc,
        float $lat,
        float $lon,
        ?string $locale = null,
    ): HoroscopeChartExplanation {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $cacheKey = $this->personalNowProfileCacheKey($datetimeUtc, $lat, $lon);

        $cached = $this->findChartExplanation($user->id, $locale, $cacheKey);
        if ($cached) {
            return $cached;
        }

        $lockKey = sprintf(
            'horoscope-chart-explanation:personal-now:%d:%s:%s',
            $user->id,
            $cacheKey,
            $locale,
        );

        return Cache::lock($lockKey, 900)->block(900, function () use ($user, $datetimeUtc, $lat, $lon, $locale, $cacheKey) {
            $existing = $this->findChartExplanation($user->id, $locale, $cacheKey);
            if ($existing) {
                return $existing;
            }

            return $this->generatePersonalNowChartExplanation($user, $datetimeUtc, $lat, $lon, $locale, $cacheKey);
        });
    }

    public function partnershipChartExplanation(
        User $user,
        int $birthChartIdA,
        int $birthChartIdB,
        ?string $locale = null,
    ): HoroscopeChartExplanation {
        if ($birthChartIdA === $birthChartIdB) {
            throw new \InvalidArgumentException('A párkapcsolati kifejtéshez két különböző születési adat kell.');
        }

        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        [$firstId, $secondId] = $this->sortedBirthChartPair($birthChartIdA, $birthChartIdB);
        $cacheKey = $this->partnershipProfileCacheKey($firstId, $secondId);
        $chartA = $this->resolveUserBirthChart($user, $firstId);
        $chartB = $this->resolveUserBirthChart($user, $secondId);

        $cached = $this->findChartExplanation($user->id, $locale, $cacheKey);
        if ($cached) {
            return $cached;
        }

        $lockKey = sprintf(
            'horoscope-chart-explanation:partnership:%d:%s:%s',
            $user->id,
            $cacheKey,
            $locale,
        );

        return Cache::lock($lockKey, 900)->block(900, function () use ($user, $chartA, $chartB, $locale, $cacheKey, $firstId, $secondId) {
            $existing = $this->findChartExplanation($user->id, $locale, $cacheKey);
            if ($existing) {
                return $existing;
            }

            return $this->generatePartnershipChartExplanation($user, $chartA, $chartB, $locale, $cacheKey, $firstId, $secondId);
        });
    }

    public function publishedForToday(?string $locale = null, ?User $user = null): ?DailyHoroscopeMessage
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $bounds = HoroscopePeriod::bounds(HoroscopePeriod::DAILY);

        $this->ensureGlobalGenerated($locale, $bounds, $user);

        return $this->publishedGlobalForPeriod($locale, $bounds);
    }

    /**
     * Első nyitólap-betöltés időszakonként egyszer legenerálja a globális üzenetet (lockkal).
     *
     * @param  array{type: string, start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon, forecast_date: \Illuminate\Support\Carbon}  $bounds
     */
    public function ensureGlobalGenerated(?string $locale, array $bounds, ?User $user = null): void
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));

        if ($this->publishedGlobalForPeriod($locale, $bounds)) {
            return;
        }

        $existing = DailyHoroscopeMessage::query()
            ->where('locale', $locale)
            ->where('period_type', $bounds['type'])
            ->whereDate('period_start', $bounds['start'])
            ->first();

        if ($existing && ! $existing->isPublished()) {
            return;
        }

        $lockKey = sprintf(
            'period-horoscope:%s:%s:%s',
            $bounds['type'],
            $bounds['start']->toDateString(),
            $locale,
        );

        Cache::lock($lockKey, 180)->block(180, function () use ($bounds, $locale, $user): void {
            if ($this->publishedGlobalForPeriod($locale, $bounds)) {
                return;
            }

            $existing = DailyHoroscopeMessage::query()
                ->where('locale', $locale)
                ->where('period_type', $bounds['type'])
                ->whereDate('period_start', $bounds['start'])
                ->first();

            if ($existing) {
                return;
            }

            $this->generateGlobal($bounds, $locale, $user, publish: true);
        });
    }

    public function ensureGlobalDailyGenerated(?string $locale = null, ?User $user = null): void
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));
        $this->ensureGlobalGenerated($locale, HoroscopePeriod::bounds(HoroscopePeriod::DAILY), $user);
    }

    /**
     * @param  array{type: string, start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon, forecast_date: \Illuminate\Support\Carbon}  $bounds
     */
    private function publishedGlobalForPeriod(?string $locale, array $bounds): ?DailyHoroscopeMessage
    {
        $locale = Str::lower(trim($locale ?? app()->getLocale()));

        return DailyHoroscopeMessage::query()
            ->where('locale', $locale)
            ->where('period_type', $bounds['type'])
            ->whereDate('period_start', $bounds['start'])
            ->where('status', DailyHoroscopeMessage::STATUS_PUBLISHED)
            ->first();
    }

    private function publishedGlobalForToday(?string $locale = null, ?Carbon $forecastDate = null): ?DailyHoroscopeMessage
    {
        $forecastDate ??= $this->forecastDate();

        return $this->publishedGlobalForPeriod($locale, HoroscopePeriod::bounds(HoroscopePeriod::DAILY, $forecastDate));
    }

    public function draftForToday(string $locale): ?DailyHoroscopeMessage
    {
        $bounds = HoroscopePeriod::bounds(HoroscopePeriod::DAILY);

        return DailyHoroscopeMessage::query()
            ->where('locale', $locale)
            ->where('period_type', HoroscopePeriod::DAILY)
            ->whereDate('period_start', $bounds['start'])
            ->first();
    }

    public function regenerateGlobal(string $locale, User $admin): DailyHoroscopeMessage
    {
        $bounds = HoroscopePeriod::bounds(HoroscopePeriod::DAILY);
        $setting = DailyHoroscopeSetting::forLocale($locale);

        return $this->generateGlobal(
            $bounds,
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

        return $this->personalForPeriod($user, $locale, HoroscopePeriod::bounds(HoroscopePeriod::DAILY));
    }

    /**
     * @param  array{type: string, start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon, forecast_date: \Illuminate\Support\Carbon}  $bounds
     */
    public function personalForPeriod(User $user, string $locale, array $bounds): UserDailyHoroscopeMessage
    {
        $cached = UserDailyHoroscopeMessage::query()
            ->where('user_id', $user->id)
            ->where('locale', $locale)
            ->where('period_type', $bounds['type'])
            ->whereDate('period_start', $bounds['start'])
            ->first();

        if ($cached) {
            return $cached;
        }

        $lockKey = sprintf(
            'user-period-horoscope:%d:%s:%s:%s',
            $user->id,
            $bounds['type'],
            $bounds['start']->toDateString(),
            $locale,
        );

        return Cache::lock($lockKey, 180)->block(180, function () use ($user, $locale, $bounds) {
            $existing = UserDailyHoroscopeMessage::query()
                ->where('user_id', $user->id)
                ->where('locale', $locale)
                ->where('period_type', $bounds['type'])
                ->whereDate('period_start', $bounds['start'])
                ->first();

            if ($existing) {
                return $existing;
            }

            return $this->generatePersonal($user, $bounds, $locale);
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
        $bounds = HoroscopePeriod::bounds(HoroscopePeriod::DAILY);

        return $this->generateGlobal($bounds, $locale, $user, publish: true);
    }

    /**
     * @param  array{type: string, start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon, forecast_date: \Illuminate\Support\Carbon}  $bounds
     */
    private function generateGlobal(
        array $bounds,
        string $locale,
        ?User $user,
        bool $publish,
        bool $force = false,
    ): DailyHoroscopeMessage {
        $forecastDate = $bounds['forecast_date'];
        $chartDatetimeUtc = $this->noonUtcForDate($forecastDate);
        $chartPayload = $this->calculateChart($chartDatetimeUtc);
        $periodContext = $this->periodContext->build(
            $bounds['type'],
            $bounds['start'],
            $bounds['end'],
            $locale,
        );
        $setting = DailyHoroscopeSetting::forLocale($locale);
        $allScores = $this->scoring->scoreAllPayloads($chartPayload);
        $scoreContext = $this->resolveScoreContext($setting, $allScores);
        $profileName = (string) ($scoreContext['profile'] ?? '');

        $generated = $this->generateWithLlm(
            system: $this->promptBuilder->globalSystemPrompt($locale, $bounds['type']),
            userPrompt: $this->promptBuilder->globalUserPromptForPeriod(
                $locale,
                $bounds['type'],
                $chartPayload,
                $scoreContext,
                null,
                $periodContext,
            ),
            user: $user,
            options: $this->llmOptionsForPeriod($bounds['type']),
        );

        $attributes = [
            'forecast_date' => $forecastDate->toDateString(),
            'period_type' => $bounds['type'],
            'period_start' => $bounds['start']->toDateString(),
            'period_end' => $bounds['end']->toDateString(),
            'chart_datetime_utc' => $chartDatetimeUtc,
            'chart_payload' => $chartPayload,
            'period_context' => $periodContext,
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
            ->where('locale', $locale)
            ->where('period_type', $bounds['type'])
            ->whereDate('period_start', $bounds['start'])
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
                'locale' => $locale,
                ...$attributes,
            ]);
        } catch (QueryException $error) {
            if ($this->isUniqueConstraintViolation($error)) {
                return DailyHoroscopeMessage::query()
                    ->where('locale', $locale)
                    ->where('period_type', $bounds['type'])
                    ->whereDate('period_start', $bounds['start'])
                    ->firstOrFail();
            }

            throw $error;
        }
    }

    /**
     * @param  array{type: string, start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon, forecast_date: \Illuminate\Support\Carbon}  $bounds
     */
    private function generatePersonal(User $user, array $bounds, string $locale): UserDailyHoroscopeMessage
    {
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['birthChart', 'userHoroscope', 'scoringProfile']);

        $forecastDate = $bounds['forecast_date'];
        $chartDatetimeUtc = $this->noonUtcForDate($forecastDate);
        $chartPayload = $this->calculateChart($chartDatetimeUtc);
        $attachedPayload = $this->buildAttachedChartPayload($user, $settings);
        $periodContext = $this->periodContext->build(
            $bounds['type'],
            $bounds['start'],
            $bounds['end'],
            $locale,
        );

        $profile = $settings->resolvedScoringProfile();
        $scoreContext = $profile
            ? $this->scoring->scorePayloadForProfile($chartPayload, $profile)
            : [];

        $generated = $this->generateWithLlm(
            system: $this->promptBuilder->userSystemPromptForPeriod($settings, $locale, $bounds['type']),
            userPrompt: $this->promptBuilder->userUserPromptForPeriod(
                $settings,
                $locale,
                $bounds['type'],
                $chartPayload,
                $scoreContext,
                $attachedPayload,
                $periodContext,
            ),
            user: $user,
            options: $this->llmOptionsForPeriod($bounds['type']),
        );

        try {
            return UserDailyHoroscopeMessage::create([
                'user_id' => $user->id,
                'forecast_date' => $forecastDate->toDateString(),
                'period_type' => $bounds['type'],
                'period_start' => $bounds['start']->toDateString(),
                'period_end' => $bounds['end']->toDateString(),
                'locale' => $locale,
                'chart_datetime_utc' => $chartDatetimeUtc,
                'chart_payload' => $chartPayload,
                'period_context' => $periodContext,
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
                    ->where('locale', $locale)
                    ->where('period_type', $bounds['type'])
                    ->whereDate('period_start', $bounds['start'])
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

            return $this->buildAttachedPayloadFromBirthChart($user, $birthChart, $settings->resolvedScoringProfile());
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
    private function buildAttachedPayloadFromBirthChart(User $user, BirthChart $birthChart, ?ScoringProfile $profile = null): array
    {
        $payload = $this->buildPayloadFromBirthChart($birthChart, $user);
        $calculated = $this->calculator->calculate($payload);
        $score = $profile ? $this->scoring->scorePayloadForProfile($calculated, $profile) : [];

        return [
            'source' => 'birth_chart',
            'label' => $birthChart->name,
            'gender' => $birthChart->gender,
            'chart' => $calculated,
            'score' => $score,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAttachedPayloadFromChartEntry(
        User $user,
        string $datetimeUtc,
        float $lat,
        float $lon,
        ?ScoringProfile $profile = null,
        ?string $label = null,
    ): array {
        $sidereal = ($user->zodiac_mode ?? 'tropical') === 'sidereal';
        $entry = [
            'datetime_utc' => Carbon::parse($datetimeUtc)->utc()->toIso8601String(),
            'lat' => $lat,
            'lon' => $lon,
        ];
        $payload = [
            'natal' => $entry,
            'transit' => $entry,
            'sidereal' => $sidereal,
            'ayanamsa' => 'lahiri',
            'house_system' => (string) ($user->house_system ?? 'placidus'),
        ];
        $calculated = $this->calculator->calculate($payload);
        $score = $profile ? $this->scoring->scorePayloadForProfile($calculated, $profile) : [];

        return [
            'source' => 'now',
            'label' => $label ?? __('horoscope.now'),
            'gender' => null,
            'chart' => $calculated,
            'score' => $score,
        ];
    }

    private function generateHoroscopePersonal(
        User $user,
        BirthChart $birthChart,
        array $bounds,
        string $locale,
        string $cacheKey,
    ): HoroscopeDailyMessage {
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['scoringProfile']);

        $forecastDate = $bounds['forecast_date'];
        $chartDatetimeUtc = $this->noonUtcForDate($forecastDate);
        $chartPayload = $this->calculateChart($chartDatetimeUtc);
        $attachedPayload = $this->buildAttachedPayloadFromBirthChart($user, $birthChart, $settings->resolvedScoringProfile());
        $periodContext = $this->periodContext->build(
            $bounds['type'],
            $bounds['start'],
            $bounds['end'],
            $locale,
        );

        $profile = $settings->resolvedScoringProfile();
        $scoreContext = $profile
            ? $this->scoring->scorePayloadForProfile($chartPayload, $profile)
            : [];

        $generated = $this->generateWithLlm(
            system: $this->promptBuilder->horoscopePersonalSystemPromptForPeriod($settings, $locale, $bounds['type']),
            userPrompt: $this->promptBuilder->horoscopePersonalUserPromptForPeriod(
                $settings,
                $locale,
                $bounds['type'],
                $chartPayload,
                $scoreContext,
                $attachedPayload,
                $periodContext,
            ),
            user: $user,
            options: $this->llmOptionsForPeriod($bounds['type']),
        );

        return $this->storeHoroscopeDailyMessage([
            'user_id' => $user->id,
            'forecast_date' => $forecastDate->toDateString(),
            'locale' => $locale,
            'period_type' => $bounds['type'],
            'period_start' => $bounds['start']->toDateString(),
            'period_end' => $bounds['end']->toDateString(),
            'kind' => HoroscopeDailyMessage::KIND_PERSONAL,
            'cache_key' => $cacheKey,
            'birth_chart_id' => $birthChart->id,
            'birth_chart_id_a' => null,
            'birth_chart_id_b' => null,
            'chart_datetime_utc' => $chartDatetimeUtc,
            'chart_payload' => $chartPayload,
            'score_payload' => $scoreContext,
            'context_payload' => ['attached' => $attachedPayload],
            'period_context' => $periodContext,
            'scoring_profile_name' => (string) ($scoreContext['profile'] ?? ''),
            ...$generated,
            'generated_at' => now(),
        ]);
    }

    private function generateHoroscopePartnership(
        User $user,
        BirthChart $chartA,
        BirthChart $chartB,
        array $bounds,
        string $locale,
        string $cacheKey,
        int $firstId,
        int $secondId,
    ): HoroscopeDailyMessage {
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['scoringProfile']);
        $profile = $settings->resolvedScoringProfile();

        $forecastDate = $bounds['forecast_date'];
        $chartDatetimeUtc = $this->noonUtcForDate($forecastDate);
        $chartPayload = $this->calculateChart($chartDatetimeUtc);
        $attachedA = $this->buildAttachedPayloadFromBirthChart($user, $chartA, $profile);
        $attachedB = $this->buildAttachedPayloadFromBirthChart($user, $chartB, $profile);
        $partnershipContext = app(DailyHoroscopeLlmContextBuilder::class)->buildPartnershipContext($attachedA, $attachedB, $locale);
        $periodContext = $this->periodContext->build(
            $bounds['type'],
            $bounds['start'],
            $bounds['end'],
            $locale,
        );

        $scoreContext = $profile
            ? $this->scoring->scorePayloadForProfile($chartPayload, $profile)
            : [];

        $generated = $this->generateWithLlm(
            system: $this->promptBuilder->horoscopePartnershipSystemPromptForPeriod($locale, $bounds['type']),
            userPrompt: $this->promptBuilder->horoscopePartnershipUserPromptForPeriod(
                $locale,
                $bounds['type'],
                $chartPayload,
                $scoreContext,
                $partnershipContext,
                $periodContext,
            ),
            user: $user,
            options: $this->llmOptionsForPeriod($bounds['type']),
        );

        return $this->storeHoroscopeDailyMessage([
            'user_id' => $user->id,
            'forecast_date' => $forecastDate->toDateString(),
            'locale' => $locale,
            'period_type' => $bounds['type'],
            'period_start' => $bounds['start']->toDateString(),
            'period_end' => $bounds['end']->toDateString(),
            'kind' => HoroscopeDailyMessage::KIND_PARTNERSHIP,
            'cache_key' => $cacheKey,
            'birth_chart_id' => null,
            'birth_chart_id_a' => $firstId,
            'birth_chart_id_b' => $secondId,
            'chart_datetime_utc' => $chartDatetimeUtc,
            'chart_payload' => $chartPayload,
            'score_payload' => $scoreContext,
            'context_payload' => [
                'chart_a' => $attachedA,
                'chart_b' => $attachedB,
                'partnership' => $partnershipContext,
            ],
            'period_context' => $periodContext,
            'scoring_profile_name' => (string) ($scoreContext['profile'] ?? ''),
            ...$generated,
            'generated_at' => now(),
        ]);
    }

    private function generateHoroscopeExplanation(HoroscopeDailyMessage $message, User $user): string
    {
        $locale = Str::lower(trim($message->locale));
        $periodType = HoroscopePeriod::normalize($message->period_type);

        if ($message->kind === HoroscopeDailyMessage::KIND_PARTNERSHIP) {
            $system = $this->promptBuilder->horoscopePartnershipExplanationSystemPrompt($locale, $periodType);
            $userPrompt = $this->promptBuilder->horoscopePartnershipExplanationUserPrompt($message, $locale);
        } else {
            $settings = UserDailyHoroscopeSetting::forUser($user);
            $system = $this->promptBuilder->horoscopePersonalExplanationSystemPrompt($settings, $locale, $periodType);
            $userPrompt = $this->promptBuilder->horoscopePersonalExplanationUserPrompt($message, $locale);
        }

        return $this->generateExplanationWithLlm($system, $userPrompt, $user, array_merge(
            ['max_tokens' => 8000],
            $this->llmOptionsForPeriod($periodType),
        ));
    }

    /**
     * @param  array{type: string, start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon, forecast_date: \Illuminate\Support\Carbon}  $bounds
     */
    private function findHoroscopeMessage(int $userId, string $locale, string $cacheKey, array $bounds): ?HoroscopeDailyMessage
    {
        return HoroscopeDailyMessage::query()
            ->where('user_id', $userId)
            ->where('locale', $locale)
            ->where('cache_key', $cacheKey)
            ->where('period_type', $bounds['type'])
            ->whereDate('period_start', $bounds['start'])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function storeHoroscopeDailyMessage(array $attributes): HoroscopeDailyMessage
    {
        try {
            return HoroscopeDailyMessage::create($attributes);
        } catch (QueryException $error) {
            if ($this->isUniqueConstraintViolation($error)) {
                return HoroscopeDailyMessage::query()
                    ->where('user_id', $attributes['user_id'])
                    ->where('locale', $attributes['locale'])
                    ->where('cache_key', $attributes['cache_key'])
                    ->where('period_type', $attributes['period_type'] ?? HoroscopePeriod::DAILY)
                    ->whereDate('period_start', $attributes['period_start'])
                    ->firstOrFail();
            }

            throw $error;
        }
    }

    private function resolveUserBirthChart(User $user, int $birthChartId): BirthChart
    {
        return BirthChart::query()
            ->where('user_id', $user->id)
            ->whereKey($birthChartId)
            ->firstOrFail();
    }

    private function personalCacheKey(int $birthChartId): string
    {
        return 'personal:'.$birthChartId;
    }

    private function partnershipCacheKey(int $firstId, int $secondId): string
    {
        return 'partnership:'.$firstId.':'.$secondId;
    }

    private function personalProfileCacheKey(int $birthChartId): string
    {
        return 'profile:personal:'.$birthChartId;
    }

    private function personalNowProfileCacheKey(string $datetimeUtc, float $lat, float $lon): string
    {
        $minuteUtc = Carbon::parse($datetimeUtc)->utc()->format('Y-m-d\TH:i');
        $latKey = number_format($lat, 4, '.', '');
        $lonKey = number_format($lon, 4, '.', '');

        return 'profile:now:'.substr(md5($minuteUtc.'|'.$latKey.'|'.$lonKey), 0, 24);
    }

    private function partnershipProfileCacheKey(int $firstId, int $secondId): string
    {
        return 'profile:partnership:'.$firstId.':'.$secondId;
    }

    private function findChartExplanation(int $userId, string $locale, string $cacheKey): ?HoroscopeChartExplanation
    {
        return HoroscopeChartExplanation::query()
            ->where('user_id', $userId)
            ->where('locale', $locale)
            ->where('cache_key', $cacheKey)
            ->first();
    }

    private function storeChartExplanation(array $attributes): HoroscopeChartExplanation
    {
        try {
            return HoroscopeChartExplanation::create($attributes);
        } catch (QueryException $error) {
            if ($this->isUniqueConstraintViolation($error)) {
                return HoroscopeChartExplanation::query()
                    ->where('user_id', $attributes['user_id'])
                    ->where('locale', $attributes['locale'])
                    ->where('cache_key', $attributes['cache_key'])
                    ->firstOrFail();
            }

            throw $error;
        }
    }

    private function generatePersonalChartExplanation(
        User $user,
        BirthChart $birthChart,
        string $locale,
        string $cacheKey,
        int $birthChartId,
    ): HoroscopeChartExplanation {
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['scoringProfile']);
        $attachedPayload = $this->buildAttachedPayloadFromBirthChart($user, $birthChart, $settings->resolvedScoringProfile());

        $explanation = $this->generateProfileExplanationWithLlm(
            system: $this->promptBuilder->horoscopePersonalProfileExplanationSystemPrompt($settings, $locale),
            userPrompt: $this->promptBuilder->horoscopePersonalProfileExplanationUserPrompt($attachedPayload, $locale),
            user: $user,
            partnership: false,
        );

        return $this->storeChartExplanation([
            'user_id' => $user->id,
            'locale' => $locale,
            'kind' => HoroscopeChartExplanation::KIND_PERSONAL,
            'cache_key' => $cacheKey,
            'birth_chart_id' => $birthChartId,
            'birth_chart_id_a' => null,
            'birth_chart_id_b' => null,
            'context_payload' => ['attached' => $attachedPayload],
            'explanation' => $explanation,
            'generated_at' => now(),
        ]);
    }

    private function generatePersonalNowChartExplanation(
        User $user,
        string $datetimeUtc,
        float $lat,
        float $lon,
        string $locale,
        string $cacheKey,
    ): HoroscopeChartExplanation {
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['scoringProfile']);
        $attachedPayload = $this->buildAttachedPayloadFromChartEntry(
            $user,
            $datetimeUtc,
            $lat,
            $lon,
            $settings->resolvedScoringProfile(),
            __('horoscope.now'),
        );

        $explanation = $this->generateProfileExplanationWithLlm(
            system: $this->promptBuilder->horoscopePersonalProfileExplanationSystemPrompt($settings, $locale),
            userPrompt: $this->promptBuilder->horoscopePersonalProfileExplanationUserPrompt($attachedPayload, $locale),
            user: $user,
            partnership: false,
        );

        return $this->storeChartExplanation([
            'user_id' => $user->id,
            'locale' => $locale,
            'kind' => HoroscopeChartExplanation::KIND_PERSONAL,
            'cache_key' => $cacheKey,
            'birth_chart_id' => null,
            'birth_chart_id_a' => null,
            'birth_chart_id_b' => null,
            'context_payload' => [
                'attached' => $attachedPayload,
                'datetime_utc' => Carbon::parse($datetimeUtc)->utc()->toIso8601String(),
                'lat' => $lat,
                'lon' => $lon,
            ],
            'explanation' => $explanation,
            'generated_at' => now(),
        ]);
    }

    private function generatePartnershipChartExplanation(
        User $user,
        BirthChart $chartA,
        BirthChart $chartB,
        string $locale,
        string $cacheKey,
        int $firstId,
        int $secondId,
    ): HoroscopeChartExplanation {
        $settings = UserDailyHoroscopeSetting::forUser($user);
        $settings->load(['scoringProfile']);
        $profile = $settings->resolvedScoringProfile();
        $attachedA = $this->buildAttachedPayloadFromBirthChart($user, $chartA, $profile);
        $attachedB = $this->buildAttachedPayloadFromBirthChart($user, $chartB, $profile);
        $partnershipContext = app(DailyHoroscopeLlmContextBuilder::class)->buildPartnershipContext($attachedA, $attachedB, $locale);

        $explanation = $this->generateProfileExplanationWithLlm(
            system: $this->promptBuilder->horoscopePartnershipProfileExplanationSystemPrompt($locale),
            userPrompt: $this->promptBuilder->horoscopePartnershipProfileExplanationUserPrompt(
                $attachedA,
                $attachedB,
                $partnershipContext,
                $locale,
            ),
            user: $user,
            partnership: true,
        );

        return $this->storeChartExplanation([
            'user_id' => $user->id,
            'locale' => $locale,
            'kind' => HoroscopeChartExplanation::KIND_PARTNERSHIP,
            'cache_key' => $cacheKey,
            'birth_chart_id' => null,
            'birth_chart_id_a' => $firstId,
            'birth_chart_id_b' => $secondId,
            'context_payload' => [
                'chart_a' => $attachedA,
                'chart_b' => $attachedB,
                'partnership' => $partnershipContext,
            ],
            'explanation' => $explanation,
            'generated_at' => now(),
        ]);
    }

    private function generateProfileExplanationWithLlm(
        string $system,
        string $userPrompt,
        ?User $user,
        bool $partnership = false,
    ): string {
        @set_time_limit($partnership ? 900 : 600);

        return $this->generateExplanationWithLlm($system, $userPrompt, $user, [
            'max_tokens' => $partnership ? 24000 : 16000,
            'timeout' => $partnership ? 600 : 300,
        ]);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function sortedBirthChartPair(int $birthChartIdA, int $birthChartIdB): array
    {
        return $birthChartIdA < $birthChartIdB
            ? [$birthChartIdA, $birthChartIdB]
            : [$birthChartIdB, $birthChartIdA];
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
     * @return array<string, mixed>
     */
    private function llmOptionsForPeriod(string $periodType): array
    {
        return match (HoroscopePeriod::normalize($periodType)) {
            HoroscopePeriod::WEEKLY, HoroscopePeriod::MONTHLY => [
                'max_tokens' => 12000,
                'timeout' => 180,
            ],
            default => [
                'timeout' => 90,
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{motto: string, summary: string, health: string, money: string, relationships: string, work: string}
     */
    private function generateWithLlm(string $system, string $userPrompt, ?User $user, array $options = []): array
    {
        $apiKey = (string) config('services.openai.api_key');
        if (trim($apiKey) === '') {
            throw new \RuntimeException('Hiányzik az OPENAI_API_KEY (.env).');
        }

        $response = $this->openAi->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $userPrompt],
        ], null, array_merge([
            'response_format' => ['type' => 'json_object'],
        ], $options));

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
     * @param  array<string, mixed>  $options
     */
    private function generateExplanationWithLlm(string $system, string $userPrompt, ?User $user, array $options = []): string
    {
        $apiKey = (string) config('services.openai.api_key');
        if (trim($apiKey) === '') {
            throw new \RuntimeException('Hiányzik az OPENAI_API_KEY (.env).');
        }

        $options = array_merge([
            'response_format' => ['type' => 'json_object'],
            'max_tokens' => 8000,
            'timeout' => 90,
        ], $options);

        $response = $this->openAi->chat([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $userPrompt],
        ], null, $options);

        if ($response->failed()) {
            Log::warning('Horoscope explanation LLM failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('A kifejtés generálása sikertelen.');
        }

        $content = (string) ($response->json('choices.0.message.content') ?? '');
        $parsed = json_decode($content, true);

        if (! is_array($parsed)) {
            throw new \RuntimeException('Az LM kifejtés válasza nem értelmezhető JSON.');
        }

        if ($user) {
            $usage = (array) ($response->json('usage') ?? []);
            $total = (int) ($usage['total_tokens'] ?? 0);
            if ($total > 0 && $user->token_quota_total > 0) {
                $user->increment('token_quota_used', $total);
            }
        }

        $explanation = trim((string) ($parsed['explanation'] ?? ''));
        if ($explanation === '') {
            throw new \RuntimeException('Hiányzó kifejtés az LM válaszában.');
        }

        return $explanation;
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
