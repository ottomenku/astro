<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyHoroscopeSetting;
use App\Models\ScoringProfile;
use App\Services\DailyHoroscopePromptBuilder;
use App\Services\DailyHoroscopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminDailyHoroscopeController extends Controller
{
    public function edit(Request $request, DailyHoroscopeService $dailyHoroscope, DailyHoroscopePromptBuilder $promptBuilder)
    {
        $locale = Str::lower(trim((string) $request->query('locale', 'hu')));
        if (! in_array($locale, ['hu', 'en'], true)) {
            $locale = 'hu';
        }

        $setting = DailyHoroscopeSetting::forLocale($locale);
        $setting->load('scoringProfile');

        $profiles = ScoringProfile::query()->orderBy('id')->get();
        $preview = $dailyHoroscope->previewPayload($locale);
        $selectedScore = $this->selectedScore($setting, $preview['scores']);
        $draft = $dailyHoroscope->draftForToday($locale);

        $systemOutputFormat = $promptBuilder->globalSystemOutputFormat($locale);
        $assembledSystemPrompt = $promptBuilder->globalSystemPrompt($locale);
        $assembledUserPrompt = $selectedScore !== []
            ? $promptBuilder->globalUserPrompt($locale, $preview['chart_payload'], $selectedScore)
            : '';

        $activeTab = $this->resolveTab((string) $request->query('tab', 'generation'), $profiles);

        return view('admin.daily-horoscope.edit', [
            'locale' => $locale,
            'activeTab' => $activeTab,
            'setting' => $setting,
            'profiles' => $profiles,
            'preview' => $preview,
            'scores' => $preview['scores'],
            'selectedScore' => $selectedScore,
            'systemOutputFormat' => $systemOutputFormat,
            'assembledSystemPrompt' => $assembledSystemPrompt,
            'assembledUserPrompt' => $assembledUserPrompt,
            'draft' => $draft,
        ]);
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['hu', 'en'])],
            'system_prompt' => ['nullable', 'string', 'max:20000'],
        ]);

        $locale = Str::lower($validated['locale']);
        DailyHoroscopeSetting::forLocale($locale)->update([
            'system_prompt' => $this->nullableTrim($validated['system_prompt'] ?? null),
        ]);

        return $this->redirectToTab($locale, 'system', 'System prompt mentve.');
    }

    public function updateGeneration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['hu', 'en'])],
            'user_prompt_append' => ['nullable', 'string', 'max:20000'],
            'scoring_profile_id' => ['required', 'integer', Rule::exists('scoring_profiles', 'id')],
            'auto_publish' => ['sometimes', 'boolean'],
        ]);

        $locale = Str::lower($validated['locale']);
        DailyHoroscopeSetting::forLocale($locale)->update([
            'user_prompt_append' => $this->nullableTrim($validated['user_prompt_append'] ?? null),
            'scoring_profile_id' => (int) $validated['scoring_profile_id'],
            'auto_publish' => $request->boolean('auto_publish'),
        ]);

        return $this->redirectToTab($locale, 'prompt', 'Beállítások mentve – a kimenő prompt frissítve.');
    }

    public function regenerate(Request $request, DailyHoroscopeService $dailyHoroscope): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['hu', 'en'])],
        ]);

        $locale = Str::lower($validated['locale']);

        try {
            $message = $dailyHoroscope->regenerateGlobal($locale, $request->user());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['regenerate' => 'Az újragenerálás sikertelen: '.$exception->getMessage()]);
        }

        $status = $message->isPublished()
            ? 'Újragenerálva és automatikusan publikálva.'
            : 'Újragenerálva – ellenőrizd a Válasz fülön, majd publikáld.';

        return $this->redirectToTab($locale, 'response', $status);
    }

    public function updateMessage(Request $request, DailyHoroscopeService $dailyHoroscope): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['hu', 'en'])],
            'motto' => ['required', 'string', 'max:500'],
            'summary' => ['required', 'string', 'max:10000'],
            'health' => ['required', 'string', 'max:10000'],
            'money' => ['required', 'string', 'max:10000'],
            'relationships' => ['required', 'string', 'max:10000'],
            'work' => ['required', 'string', 'max:10000'],
        ]);

        $locale = Str::lower($validated['locale']);

        try {
            $dailyHoroscope->updateDraft($locale, $validated);
        } catch (\Throwable $exception) {
            return back()->withErrors(['message' => $exception->getMessage()]);
        }

        return $this->redirectToTab($locale, 'response', 'A mai szöveg mentve.');
    }

    public function publish(Request $request, DailyHoroscopeService $dailyHoroscope): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['hu', 'en'])],
        ]);

        $locale = Str::lower($validated['locale']);

        try {
            $dailyHoroscope->publishDraft($locale, $request->user());
        } catch (\Throwable $exception) {
            return back()->withErrors(['publish' => $exception->getMessage()]);
        }

        return $this->redirectToTab($locale, 'response', 'Publikálva – a nyitólap mostantól ezt mutatja.');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, ScoringProfile>  $profiles
     */
    private function resolveTab(string $tab, $profiles): string
    {
        $tab = Str::lower(trim($tab));
        $allowed = ['generation', 'response', 'prompt', 'system'];

        foreach ($profiles as $profile) {
            $allowed[] = 'score-'.$profile->id;
        }

        return in_array($tab, $allowed, true) ? $tab : 'generation';
    }

    /**
     * @param  array<int, array<string, mixed>>  $scores
     * @return array<string, mixed>
     */
    private function selectedScore(DailyHoroscopeSetting $setting, array $scores): array
    {
        $profileId = $setting->scoring_profile_id;

        if ($profileId && isset($scores[$profileId])) {
            return $scores[$profileId];
        }

        $profile = $setting->scoringProfile ?? ScoringProfile::defaultProfile();
        if ($profile && isset($scores[$profile->id])) {
            return $scores[$profile->id];
        }

        return reset($scores) ?: [];
    }

    private function redirectToTab(string $locale, string $tab, string $status): RedirectResponse
    {
        return redirect()
            ->route('admin.daily-horoscope.edit', ['locale' => $locale, 'tab' => $tab])
            ->with('status', $status);
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
