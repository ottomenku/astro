<?php

namespace App\Http\Controllers;

use App\Services\HoroscopeTransitService;
use App\Services\TransitSearchService;
use Illuminate\Http\Request;

class HoroscopeToolsController extends Controller
{
    /**
     * Transit "most" – a user current_lat/current_lon alapján.
     */
    public function transitNow(Request $request)
    {
        $validated = $request->validate([
            'planet' => ['required', 'string', 'max:30'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $result = app(HoroscopeTransitService::class)->getTransitNow($user, $validated['planet']);

        return response()->json($result);
    }

    /**
     * Időablakos esemény keresés.
     *
     * Típusok:
     * - enter_house: planet + house
     * - aspect_to_natal: planet + natal_longitude + aspect_angle (+ orb)
     */
    public function findEvent(Request $request)
    {
        $validated = $request->validate([
            'datetime_start_utc' => ['required', 'date'],
            'datetime_end_utc' => ['required', 'date'],
            'event' => ['required', 'array'],
            'event.type' => ['required', 'string', 'in:enter_house,aspect_to_natal'],
            'event.planet' => ['required', 'string', 'max:30'],

            'event.house' => ['required_if:event.type,enter_house', 'integer', 'between:1,12'],

            'event.natal_longitude' => ['required_if:event.type,aspect_to_natal', 'numeric', 'between:0,360'],
            'event.aspect_angle' => ['required_if:event.type,aspect_to_natal', 'numeric', 'in:0,60,90,120,180'],
            'event.orb' => ['nullable', 'numeric', 'between:0,10'],

            'house_system' => ['nullable', 'string', 'in:whole_sign,placidus'],
            'sidereal' => ['nullable', 'boolean'],
            'ayanamsa' => ['nullable', 'string', 'in:lahiri'],
            'step_hours' => ['nullable', 'numeric', 'between:0.25,48'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $result = app(TransitSearchService::class)->findEvent($user, $validated);
        return response()->json($result);
    }
}
