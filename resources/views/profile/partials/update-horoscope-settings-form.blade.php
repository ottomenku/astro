<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('app.profile_horoscope') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('app.profile_horoscope_hint') }}</p>
    </header>

    <form method="post" action="{{ route('profile.horoscope.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="house_system" :value="__('horoscope.house_system')" />
            <select id="house_system" name="house_system" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="placidus" @selected(old('house_system', $user->house_system ?? 'placidus') === 'placidus')">Placidus</option>
                <option value="whole_sign" @selected(old('house_system', $user->house_system ?? 'placidus') === 'whole_sign')">Whole Sign</option>
            </select>
            <x-input-error :messages="$errors->get('house_system')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="scoring_profile_id" :value="__('app.scoring_profile')" />
            <select id="scoring_profile_id" name="scoring_profile_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach ($scoringProfiles as $profile)
                    <option value="{{ $profile->id }}" @selected((int) old('scoring_profile_id', $user->scoring_profile_id ?? $scoringProfiles->firstWhere('is_default', true)?->id) === $profile->id)>
                        {{ $profile->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">{{ __('app.scoring_profile_hint') }}</p>
            <x-input-error :messages="$errors->get('scoring_profile_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="zodiac_mode" :value="__('app.horoscope_type')" />
            <select id="zodiac_mode" name="zodiac_mode" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="tropical" @selected(old('zodiac_mode', $user->zodiac_mode ?? 'tropical') === 'tropical')>{{ __('horoscope.zodiac_tropical') }}</option>
                <option value="sidereal" @selected(old('zodiac_mode', $user->zodiac_mode ?? 'tropical') === 'sidereal')>{{ __('horoscope.zodiac_sidereal') }}</option>
            </select>
            <x-input-error :messages="$errors->get('zodiac_mode')" class="mt-2" />
        </div>

        @include('profile.partials.chart-display-settings')

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">{{ __('app.current_location') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ __('app.current_location_hint') }}</p>

            <div class="mt-4">
                @include('partials.location-picker', [
                    'pickerId' => 'current',
                    'placeInputName' => 'current_place_label',
                    'latInputName' => 'current_lat',
                    'lonInputName' => 'current_lon',
                    'placeValue' => old('current_place_label', $user->current_place_label),
                    'latValue' => old('current_lat', $user->current_lat),
                    'lonValue' => old('current_lon', $user->current_lon),
                    'defaultCountry' => 'hu',
                    'showGeolocation' => true,
                ])
                <x-input-error :messages="$errors->get('current_place_label')" class="mt-2" />
                <x-input-error :messages="$errors->get('current_lat')" class="mt-2" />
                <x-input-error :messages="$errors->get('current_lon')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="current_tz_offset" :value="__('app.current_tz_offset')" />
                <x-text-input id="current_tz_offset" name="current_tz_offset" type="number" step="0.25" class="mt-1 block w-full" :value="old('current_tz_offset', $user->current_tz_offset ?? '2')" />
                <x-input-error :messages="$errors->get('current_tz_offset')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'horoscope-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
