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

        <div>
            <x-input-label for="name" :value="__('app.birth_chart_name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $birthChart->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="gender" :value="__('app.gender')" />
            <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="male" @selected(old('gender', $birthChart->gender) === 'male')>{{ __('app.gender_male') }}</option>
                <option value="female" @selected(old('gender', $birthChart->gender) === 'female')>{{ __('app.gender_female') }}</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        @php
            $birthParts = $birthChart->exists
                ? $birthChart->localBirthParts()
                : ['date' => old('birth_date', ''), 'time' => old('birth_time', '')];
            $correctedParts = $birthChart->exists
                ? ($birthChart->localCorrectedParts() ?? ['date' => '', 'time' => ''])
                : ['date' => old('corrected_date', ''), 'time' => old('corrected_time', '')];
        @endphp

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

            <div class="mt-4">
                <x-input-label for="birth_tz_offset" :value="__('app.birth_tz_offset')" />
                <x-text-input id="birth_tz_offset" name="birth_tz_offset" type="number" step="0.25" class="mt-1 block w-full" :value="old('birth_tz_offset', $birthChart->birth_tz_offset ?? 2)" required />
                <x-input-error :messages="$errors->get('birth_tz_offset')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="time_accuracy" :value="__('app.time_accuracy')" />
                <select id="time_accuracy" name="time_accuracy" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((int) old('time_accuracy', $birthChart->time_accuracy ?? 3) === $i)>
                            {{ $i }} — {{ __("app.time_accuracy_{$i}") }}
                        </option>
                    @endfor
                </select>
                <x-input-error :messages="$errors->get('time_accuracy')" class="mt-2" />
            </div>
        </div>

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">{{ __('app.corrected_datetime') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ __('app.corrected_datetime_hint') }}</p>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="corrected_date" :value="__('app.birth_date')" />
                    <x-text-input id="corrected_date" name="corrected_date" type="date" class="mt-1 block w-full" :value="old('corrected_date', $correctedParts['date'])" />
                    <x-input-error :messages="$errors->get('corrected_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="corrected_time" :value="__('app.birth_time')" />
                    <x-text-input id="corrected_time" name="corrected_time" type="time" class="mt-1 block w-full" :value="old('corrected_time', $correctedParts['time'])" />
                    <x-input-error :messages="$errors->get('corrected_time')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">{{ __('app.birth_place') }}</h3>

            <div class="mt-4">
                <x-input-label for="birth_place_label" :value="__('app.birth_place')" />
                <x-text-input id="birth_place_label" name="birth_place_label" type="text" class="mt-1 block w-full" :value="old('birth_place_label', $birthChart->birth_place_label)" placeholder="pl. Budapest" />
                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="birthChartPlaceResults"></div>
                <x-input-error :messages="$errors->get('birth_place_label')" class="mt-2" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="birth_lat" :value="__('app.latitude')" />
                    <x-text-input id="birth_lat" name="birth_lat" type="number" step="0.000001" class="mt-1 block w-full" :value="old('birth_lat', $birthChart->birth_lat)" />
                    <x-input-error :messages="$errors->get('birth_lat')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="birth_lon" :value="__('app.longitude')" />
                    <x-text-input id="birth_lon" name="birth_lon" type="number" step="0.000001" class="mt-1 block w-full" :value="old('birth_lon', $birthChart->birth_lon)" />
                    <x-input-error :messages="$errors->get('birth_lon')" class="mt-2" />
                </div>
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

    <script>
        const geocodeUrl = '{{ route('horoscope.geocode', [], false) }}';

        function renderResults(resultsEl, results, onPick) {
            resultsEl.innerHTML = '';
            if (!results.length) {
                resultsEl.classList.add('hidden');
                return;
            }
            resultsEl.classList.remove('hidden');
            results.forEach((item) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-3 py-2 hover:bg-gray-50';
                btn.textContent = item.display_name;
                btn.addEventListener('click', () => onPick(item));
                resultsEl.appendChild(btn);
            });
        }

        async function geocode(query) {
            const response = await fetch(`${geocodeUrl}?q=${encodeURIComponent(query)}`);
            if (!response.ok) return [];
            const data = await response.json();
            return data.results || [];
        }

        function attachGeocode(inputEl, resultsEl, onPick) {
            let timeout;
            inputEl.addEventListener('input', () => {
                clearTimeout(timeout);
                const query = inputEl.value.trim();
                if (query.length < 3) {
                    renderResults(resultsEl, [], () => {});
                    return;
                }
                timeout = setTimeout(async () => {
                    const results = await geocode(query);
                    renderResults(resultsEl, results, (item) => {
                        onPick(item);
                        renderResults(resultsEl, [], () => {});
                    });
                }, 350);
            });
        }

        const place = document.getElementById('birth_place_label');
        const results = document.getElementById('birthChartPlaceResults');
        const lat = document.getElementById('birth_lat');
        const lon = document.getElementById('birth_lon');

        if (place && results && lat && lon) {
            attachGeocode(place, results, (item) => {
                lat.value = Number(item.lat).toFixed(6);
                lon.value = Number(item.lon).toFixed(6);
                place.value = item.display_name;
            });
        }
    </script>
</section>
