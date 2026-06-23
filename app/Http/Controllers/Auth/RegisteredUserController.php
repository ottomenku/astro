<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HoroscopeService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // születési adatok
            'birth_date' => ['required', 'date'],
            'birth_time' => ['required', 'date_format:H:i'],
            'birth_tz_offset' => ['required', 'numeric', 'between:-14,14'],
            'birth_place_label' => ['nullable', 'string', 'max:255'],
            'birth_lat' => ['required', 'numeric', 'between:-90,90'],
            'birth_lon' => ['required', 'numeric', 'between:-180,180'],

            // jelenlegi hely (profilban később módosítható)
            'current_tz_offset' => ['nullable', 'numeric', 'between:-14,14'],
            'current_place_label' => ['nullable', 'string', 'max:255'],
            'current_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'current_lon' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $birthDate = (string) $request->input('birth_date');
        $birthTime = (string) $request->input('birth_time');
        $birthOffset = (float) $request->input('birth_tz_offset');
        // A bevitt idő lokális; UTC = local - offset
        $birthUtc = Carbon::createFromFormat('Y-m-d H:i', $birthDate.' '.$birthTime, 'UTC')
            ->subMinutes((int) round($birthOffset * 60));

        // current mezők: ha a UI letiltotta (disabled), akkor nem jönnek; ha jönnek de üres string,
        // akkor is a születési adat legyen az alapértelmezett.
        $currentOffset = $request->filled('current_tz_offset') ? (float) $request->input('current_tz_offset') : $birthOffset;
        $currentLabel = $request->filled('current_place_label') ? (string) $request->input('current_place_label') : (string) $request->input('birth_place_label');
        $currentLat = $request->filled('current_lat') ? (float) $request->input('current_lat') : (float) $request->input('birth_lat');
        $currentLon = $request->filled('current_lon') ? (float) $request->input('current_lon') : (float) $request->input('birth_lon');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

            'birth_datetime_utc' => $birthUtc,
            'birth_tz_offset' => $birthOffset,
            'birth_place_label' => $request->input('birth_place_label'),
            'birth_lat' => $request->input('birth_lat'),
            'birth_lon' => $request->input('birth_lon'),

            // ha nincs megadva current, akkor induljon a születési hellyel
            'current_tz_offset' => $currentOffset,
            'current_place_label' => $currentLabel,
            'current_lat' => $currentLat,
            'current_lon' => $currentLon,
        ]);

        // Natál képlet számítása + mentése
        app(HoroscopeService::class)->calculateAndStoreNatal($user, [
            'sidereal' => false,
            'house_system' => 'placidus',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('horoscope.index', absolute: false));
    }
}
