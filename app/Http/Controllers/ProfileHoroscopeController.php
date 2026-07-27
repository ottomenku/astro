<?php

namespace App\Http\Controllers;

use App\Models\ScoringProfile;
use App\Http\Requests\ProfileHoroscopeUpdateRequest;
use App\Support\ChartDisplaySettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileHoroscopeController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.horoscope', [
            'user' => $request->user(),
            'scoringProfiles' => ScoringProfile::query()->orderBy('name')->get(),
        ]);
    }

    public function update(ProfileHoroscopeUpdateRequest $request): RedirectResponse
    {
        $data = collect($request->safe()->only([
            'house_system',
            'zodiac_mode',
            'scoring_profile_id',
            'current_tz_offset',
            'current_place_label',
            'current_lat',
            'current_lon',
        ]))->all();

        if ($request->has('chart_display')) {
            $data['chart_display_settings'] = ChartDisplaySettings::fromRequest($request->input('chart_display', []));
        }

        $request->user()->update($data);

        return Redirect::route('profile.horoscope.edit')->with('status', 'horoscope-updated');
    }
}
