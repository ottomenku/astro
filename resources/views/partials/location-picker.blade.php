@php
    use App\Support\CountryList;

    $pickerId = $pickerId ?? 'location';
    $placeInputName = $placeInputName ?? 'place_label';
    $latInputName = $latInputName ?? 'lat';
    $lonInputName = $lonInputName ?? 'lon';
    $placeValue = $placeValue ?? '';
    $latValue = $latValue ?? '';
    $lonValue = $lonValue ?? '';
    $defaultCountry = $defaultCountry ?? 'hu';
    $showGeolocation = $showGeolocation ?? false;
    $cityValue = $cityValue ?? (str_contains((string) $placeValue, ',') ? trim(explode(',', (string) $placeValue, 2)[0]) : (string) $placeValue);
    $countryOptions = CountryList::options();
@endphp

<div class="location-picker space-y-4" data-location-picker="{{ $pickerId }}">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700" for="{{ $pickerId }}_country">{{ __('app.country') }}</label>
            <select
                id="{{ $pickerId }}_country"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                data-location-country
            >
                @foreach ($countryOptions as $code => $label)
                    <option value="{{ $code }}" @selected(old($pickerId.'_country', $defaultCountry) === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="{{ $pickerId }}_city">{{ __('app.city') }}</label>
            <input
                type="text"
                id="{{ $pickerId }}_city"
                value="{{ old($pickerId.'_city', $cityValue) }}"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                placeholder="{{ __('app.city_placeholder') }}"
                autocomplete="off"
                data-location-city
            >
            <div class="mt-2 hidden border border-gray-200 rounded-md divide-y bg-white shadow-sm max-h-48 overflow-y-auto" id="{{ $pickerId }}_results" data-location-results></div>
            <p class="mt-1 text-xs text-gray-500">{{ __('app.location_pick_from_list') }}</p>
        </div>
    </div>

    <input type="hidden" name="{{ $placeInputName }}" id="{{ $pickerId }}_place" value="{{ old($placeInputName, $placeValue) }}" data-location-place>
    <input type="hidden" name="{{ $latInputName }}" id="{{ $pickerId }}_lat" value="{{ old($latInputName, $latValue) }}" data-location-lat>
    <input type="hidden" name="{{ $lonInputName }}" id="{{ $pickerId }}_lon" value="{{ old($lonInputName, $lonValue) }}" data-location-lon>

    <p class="text-xs text-gray-500 {{ ($latValue !== '' && $latValue !== null) ? '' : 'hidden' }}" id="{{ $pickerId }}_coords_hint" data-location-hint>
        {{ __('app.location_coords_set', [
            'lat' => is_numeric($latValue) ? number_format((float) $latValue, 4, '.', '') : '—',
            'lon' => is_numeric($lonValue) ? number_format((float) $lonValue, 4, '.', '') : '—',
        ]) }}
    </p>

    @if ($showGeolocation)
        <div class="flex justify-end">
            <button type="button" class="text-sm underline text-gray-600" data-location-geolocate>{{ __('app.use_geolocation') }}</button>
        </div>
    @endif
</div>

@once
    <script>
        window.initLocationPickers = window.initLocationPickers || function initLocationPickers() {
            const geocodeUrl = @json(route('horoscope.geocode', [], false));
            const i18n = {
                geolocationUnsupported: @json(__('app.geolocation_unsupported')),
                geolocationFailed: @json(__('app.geolocation_failed')),
                locationCoordsSet: @json(__('app.location_coords_set', ['lat' => ':lat', 'lon' => ':lon'])),
            };

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
                    btn.className = 'w-full text-left px-3 py-2 hover:bg-gray-50 text-sm';
                    btn.textContent = item.label || item.display_name;
                    btn.addEventListener('click', () => onPick(item));
                    resultsEl.appendChild(btn);
                });
            }

            async function geocode(query, country) {
                const params = new URLSearchParams({ q: query });
                if (country) {
                    params.set('country', country);
                }
                const response = await fetch(`${geocodeUrl}?${params.toString()}`);
                if (!response.ok) {
                    return [];
                }
                const data = await response.json();
                return data.results || [];
            }

            document.querySelectorAll('[data-location-picker]').forEach((root) => {
                if (root.dataset.locationInitialized === '1') {
                    return;
                }
                root.dataset.locationInitialized = '1';

                const countryEl = root.querySelector('[data-location-country]');
                const cityEl = root.querySelector('[data-location-city]');
                const resultsEl = root.querySelector('[data-location-results]');
                const placeEl = root.querySelector('[data-location-place]');
                const latEl = root.querySelector('[data-location-lat]');
                const lonEl = root.querySelector('[data-location-lon]');
                const hintEl = root.querySelector('[data-location-hint]');
                const geolocateBtn = root.querySelector('[data-location-geolocate]');

                function updateHint() {
                    if (!hintEl || !latEl?.value || !lonEl?.value) {
                        hintEl?.classList.add('hidden');
                        return;
                    }
                    hintEl.textContent = i18n.locationCoordsSet
                        .replace(':lat', Number(latEl.value).toFixed(4))
                        .replace(':lon', Number(lonEl.value).toFixed(4));
                    hintEl.classList.remove('hidden');
                }

                function applySelection(item) {
                    const city = item.city || cityEl.value.trim();
                    if (cityEl) {
                        cityEl.value = city;
                    }
                    if (countryEl && item.country_code) {
                        countryEl.value = item.country_code.toLowerCase();
                    }
                    if (placeEl) {
                        placeEl.value = item.label || item.display_name || city;
                    }
                    if (latEl) {
                        latEl.value = Number(item.lat).toFixed(6);
                    }
                    if (lonEl) {
                        lonEl.value = Number(item.lon).toFixed(6);
                    }
                    updateHint();
                }

                function clearCoordinates() {
                    if (placeEl) placeEl.value = '';
                    if (latEl) latEl.value = '';
                    if (lonEl) lonEl.value = '';
                    updateHint();
                }

                let timeout;
                cityEl?.addEventListener('input', () => {
                    clearTimeout(timeout);
                    clearCoordinates();
                    const query = cityEl.value.trim();
                    if (query.length < 2) {
                        renderResults(resultsEl, [], () => {});
                        return;
                    }
                    timeout = setTimeout(async () => {
                        const results = await geocode(query, countryEl?.value || '');
                        renderResults(resultsEl, results, (item) => {
                            applySelection(item);
                            renderResults(resultsEl, [], () => {});
                        });
                    }, 350);
                });

                countryEl?.addEventListener('change', () => {
                    const query = cityEl?.value.trim() || '';
                    if (query.length < 2) {
                        return;
                    }
                    clearTimeout(timeout);
                    timeout = setTimeout(async () => {
                        const results = await geocode(query, countryEl.value || '');
                        renderResults(resultsEl, results, (item) => {
                            applySelection(item);
                            renderResults(resultsEl, [], () => {});
                        });
                    }, 200);
                });

                geolocateBtn?.addEventListener('click', () => {
                    if (!navigator.geolocation) {
                        alert(i18n.geolocationUnsupported);
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            if (latEl) latEl.value = pos.coords.latitude.toFixed(6);
                            if (lonEl) lonEl.value = pos.coords.longitude.toFixed(6);
                            if (placeEl && !placeEl.value) {
                                placeEl.value = @json(__('app.use_geolocation'));
                            }
                            updateHint();
                        },
                        () => alert(i18n.geolocationFailed),
                        { enableHighAccuracy: true, timeout: 8000 }
                    );
                });

                updateHint();
            });
        };
    </script>
@endonce
<script>window.initLocationPickers?.();</script>
