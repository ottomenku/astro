<?php

namespace App\Http\Controllers;

use App\Models\BirthChart;
use App\Models\ScoringProfile;
use App\Models\UserDailyHoroscopeSetting;
use App\Models\UserHoroscope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileDailyHoroscopeController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $settings = UserDailyHoroscopeSetting::forUser($user);

        return view('profile.daily-horoscope', [
            'user' => $user,
            'settings' => $settings,
            'scoringProfiles' => ScoringProfile::query()->orderBy('name')->get(),
            'birthCharts' => BirthChart::query()->where('user_id', $user->id)->orderBy('name')->get(),
            'savedHoroscopes' => UserHoroscope::query()->where('user_id', $user->id)->orderByDesc('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $settings = UserDailyHoroscopeSetting::forUser($user);

        $request->merge([
            'scoring_profile_id' => $request->input('scoring_profile_id') ?: null,
            'birth_chart_id' => $request->input('birth_chart_id') ?: null,
            'user_horoscope_id' => $request->input('user_horoscope_id') ?: null,
        ]);

        $validated = $request->validate([
            'use_personal_daily' => ['sometimes', 'boolean'],
            'system_prompt' => ['nullable', 'string', 'max:20000'],
            'user_prompt_template' => ['nullable', 'string', 'max:20000'],
            'scoring_profile_id' => ['nullable', 'integer', Rule::exists('scoring_profiles', 'id')],
            'attached_source' => ['nullable', 'string', Rule::in(['none', 'birth_chart', 'user_horoscope'])],
            'birth_chart_id' => [
                'nullable',
                'integer',
                Rule::exists('birth_charts', 'id')->where(fn ($query) => $query->where('user_id', $user->id)),
            ],
            'user_horoscope_id' => [
                'nullable',
                'integer',
                Rule::exists('user_horoscopes', 'id')->where(fn ($query) => $query->where('user_id', $user->id)),
            ],
        ]);

        $attachedSource = $validated['attached_source'] ?? 'none';
        $birthChartId = $attachedSource === 'birth_chart' ? ($validated['birth_chart_id'] ?? null) : null;
        $userHoroscopeId = $attachedSource === 'user_horoscope' ? ($validated['user_horoscope_id'] ?? null) : null;

        $settings->update([
            'use_personal_daily' => $request->boolean('use_personal_daily'),
            'system_prompt' => $this->nullableTrim($validated['system_prompt'] ?? null),
            'user_prompt_template' => $this->nullableTrim($validated['user_prompt_template'] ?? null),
            'scoring_profile_id' => $validated['scoring_profile_id'] ?? null,
            'birth_chart_id' => $birthChartId,
            'user_horoscope_id' => $userHoroscopeId,
        ]);

        return Redirect::route('profile.daily-horoscope.edit')->with('status', 'daily-horoscope-updated');
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
