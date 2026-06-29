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
                <option value="whole_sign" @selected(old('house_system', $user->house_system ?? 'placidus') === 'whole_sign')">Whole Sign</option>
                <option value="placidus" @selected(old('house_system', $user->house_system ?? 'placidus') === 'placidus')">Placidus</option>
            </select>
            <x-input-error :messages="$errors->get('house_system')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="zodiac_mode" :value="__('app.horoscope_type')" />
            <select id="zodiac_mode" name="zodiac_mode" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="tropical" @selected(old('zodiac_mode', $user->zodiac_mode ?? 'tropical') === 'tropical')>{{ __('horoscope.zodiac_tropical') }}</option>
                <option value="sidereal" @selected(old('zodiac_mode', $user->zodiac_mode ?? 'tropical') === 'sidereal')>{{ __('horoscope.zodiac_sidereal') }}</option>
            </select>
            <x-input-error :messages="$errors->get('zodiac_mode')" class="mt-2" />
        </div>

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">{{ __('app.current_location') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ __('app.current_location_hint') }}</p>

            <div class="mt-4 flex items-center justify-between gap-4">
                <div class="text-xs text-gray-500">{{ __('app.location_tip') }}</div>
                <button type="button" class="text-sm underline text-gray-600" id="profileUseGeolocation">{{ __('app.use_geolocation') }}</button>
            </div>

            <div class="mt-4">
                <x-input-label for="current_place_label" :value="__('app.current_place')" />
                <x-text-input id="current_place_label" name="current_place_label" type="text" class="mt-1 block w-full" :value="old('current_place_label', $user->current_place_label)" placeholder="pl. Budapest" />
                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="profileCurrentResults"></div>
                <x-input-error :messages="$errors->get('current_place_label')" class="mt-2" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="current_lat" :value="__('app.latitude')" />
                    <x-text-input id="current_lat" name="current_lat" type="number" step="0.000001" class="mt-1 block w-full" :value="old('current_lat', $user->current_lat)" />
                    <x-input-error :messages="$errors->get('current_lat')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="current_lon" :value="__('app.longitude')" />
                    <x-text-input id="current_lon" name="current_lon" type="number" step="0.000001" class="mt-1 block w-full" :value="old('current_lon', $user->current_lon)" />
                    <x-input-error :messages="$errors->get('current_lon')" class="mt-2" />
                </div>
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

        const place = document.getElementById('current_place_label');
        const results = document.getElementById('profileCurrentResults');
        const lat = document.getElementById('current_lat');
        const lon = document.getElementById('current_lon');
        attachGeocode(place, results, (item) => {
            lat.value = Number(item.lat).toFixed(6);
            lon.value = Number(item.lon).toFixed(6);
            place.value = item.display_name;
        });

        document.getElementById('profileUseGeolocation')?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('{{ __('app.geolocation_unsupported') }}');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    lat.value = pos.coords.latitude.toFixed(6);
                    lon.value = pos.coords.longitude.toFixed(6);
                },
                () => alert('{{ __('app.geolocation_failed') }}'),
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });
    </script>
</section>
