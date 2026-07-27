@php
    use App\Support\BirthPlaceDefaults;
    use App\Support\BirthTimezoneOptions;

    $birthParts = $birthChart->exists
        ? $birthChart->localBirthParts()
        : ['date' => old('birth_date', ''), 'time' => old('birth_time', '')];
    $useDefaultTimezone = ! $birthChart->exists && old('birth_tz_offset') === null;
    $selectedTzOffset = old('birth_tz_offset', $birthChart->birth_tz_offset ?? BirthTimezoneOptions::defaultOffset());
    $locationDefaults = BirthPlaceDefaults::class;
    $defaultPlaceLabel = $locationDefaults::placeLabel();
    $defaultCity = $locationDefaults::city();
    $placeValue = old('birth_place_label', $birthChart->birth_place_label ?? ($birthChart->exists ? '' : $defaultPlaceLabel));
    $latValue = old('birth_lat', $birthChart->birth_lat ?? ($birthChart->exists ? '' : $locationDefaults::lat()));
    $lonValue = old('birth_lon', $birthChart->birth_lon ?? ($birthChart->exists ? '' : $locationDefaults::lon()));
    $cityValue = old('birth_city', $birthChart->exists
        ? (str_contains((string) $placeValue, ',') ? trim(explode(',', (string) $placeValue, 2)[0]) : (string) $placeValue)
        : $defaultCity);
@endphp

<section>
    <form
        method="post"
        action="{{ $birthChart->exists ? route('profile.birth-charts.update', $birthChart) : route('profile.birth-charts.store') }}"
        class="space-y-6"
    >
        @csrf
        @if ($birthChart->exists)
            @method('patch')
        @endif

        <div class="form-row-2-1">
            <div class="form-col-2">
                <x-input-label for="name" :value="__('app.birth_chart_name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $birthChart->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div class="form-col-1">
                <x-input-label for="gender" :value="__('app.gender')" />
                <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="male" @selected(old('gender', $birthChart->gender) === 'male')>{{ __('app.gender_male') }}</option>
                    <option value="female" @selected(old('gender', $birthChart->gender) === 'female')>{{ __('app.gender_female') }}</option>
                </select>
                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
            </div>
        </div>

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">{{ __('app.birth_datetime') }}</h3>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="birth_date" :value="__('app.birth_date')" />
                    <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date', $birthParts['date'])" required />
                    <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="birth_time" :value="__('app.birth_time')" />
                    <x-text-input id="birth_time" name="birth_time" type="time" class="mt-1 block w-full" :value="old('birth_time', $birthParts['time'])" required />
                    <x-input-error :messages="$errors->get('birth_time')" class="mt-2" />
                </div>
            </div>

            <div class="mt-4 form-row-2-1">
                <div class="form-col-2">
                    <x-input-label for="birth_tz_offset" :value="__('app.birth_timezone')" />
                    <select id="birth_tz_offset" name="birth_tz_offset" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                        @foreach (BirthTimezoneOptions::all() as $tzOption)
                            <option value="{{ BirthTimezoneOptions::optionValue($tzOption) }}" @selected(BirthTimezoneOptions::matchesSelected($tzOption, (float) $selectedTzOffset, $useDefaultTimezone))>
                                {{ BirthTimezoneOptions::label($tzOption) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('birth_tz_offset')" class="mt-2" />
                </div>
                <div class="form-col-1">
                    <x-input-label for="time_accuracy" :value="__('app.time_accuracy_short')" />
                    <select id="time_accuracy" name="time_accuracy" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" @selected((int) old('time_accuracy', $birthChart->time_accuracy ?? 3) === $i)>
                                {{ $i }} — {{ __("app.time_accuracy_{$i}") }}
                            </option>
                        @endfor
                    </select>
                    <x-input-error :messages="$errors->get('time_accuracy')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">{{ __('app.birth_place') }}</h3>

            <div class="mt-4">
                @include('partials.location-picker', [
                    'pickerId' => 'birth',
                    'placeInputName' => 'birth_place_label',
                    'latInputName' => 'birth_lat',
                    'lonInputName' => 'birth_lon',
                    'placeValue' => $placeValue,
                    'latValue' => $latValue,
                    'lonValue' => $lonValue,
                    'cityValue' => $cityValue,
                    'defaultCountry' => BirthPlaceDefaults::countryCode(),
                ])
                <x-input-error :messages="$errors->get('birth_place_label')" class="mt-2" />
                <x-input-error :messages="$errors->get('birth_lat')" class="mt-2" />
                <x-input-error :messages="$errors->get('birth_lon')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_default"
                name="is_default"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('is_default', $birthChart->is_default))
            >
            <x-input-label for="is_default" :value="__('app.birth_chart_default')" class="!mt-0" />
        </div>
        <x-input-error :messages="$errors->get('is_default')" class="mt-2" />

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <a href="{{ route('profile.birth-charts.index') }}" class="text-sm text-gray-600 underline">{{ __('app.cancel') }}</a>
        </div>
    </form>
</section>
