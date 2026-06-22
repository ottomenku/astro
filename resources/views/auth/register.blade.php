<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">Születési adatok (natál képlethez)</h3>
            <p class="text-xs text-gray-500 mt-1">A rendszer a megadott adatokból kiszámolja a natál horoszkópot és elmenti.</p>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="birth_date" value="Születési dátum" />
                    <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date')" required />
                    <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="birth_time" value="Születési idő" />
                    <x-text-input id="birth_time" name="birth_time" type="time" step="60" class="mt-1 block w-full" :value="old('birth_time')" required />
                    <x-input-error :messages="$errors->get('birth_time')" class="mt-2" />
                </div>
            </div>

            <div class="mt-4">
                <x-input-label for="birth_tz_offset" value="Időzóna offset (óra, pl. +2)" />
                <x-text-input id="birth_tz_offset" name="birth_tz_offset" type="number" step="0.25" class="mt-1 block w-full" :value="old('birth_tz_offset', '2')" required />
                <x-input-error :messages="$errors->get('birth_tz_offset')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="birth_place_label" value="Születési hely (település / cím)" />
                <x-text-input id="birth_place_label" name="birth_place_label" type="text" class="mt-1 block w-full" :value="old('birth_place_label')" placeholder="pl. Budapest" />
                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="birthResults"></div>
                <x-input-error :messages="$errors->get('birth_place_label')" class="mt-2" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="birth_lat" value="Szélesség (lat)" />
                    <x-text-input id="birth_lat" name="birth_lat" type="number" step="0.000001" class="mt-1 block w-full" :value="old('birth_lat', '47.4979')" required />
                    <x-input-error :messages="$errors->get('birth_lat')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="birth_lon" value="Hosszúság (lon)" />
                    <x-text-input id="birth_lon" name="birth_lon" type="number" step="0.000001" class="mt-1 block w-full" :value="old('birth_lon', '19.0402')" required />
                    <x-input-error :messages="$errors->get('birth_lon')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="mt-6 border-t pt-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">Jelenlegi hely (tranzitokhoz)</h3>
                    <p class="text-xs text-gray-500 mt-1">Később a Profil oldalon módosítható. Ha üresen hagyod, a születési hellyel indul.</p>
                </div>
                <button type="button" class="text-sm underline text-gray-600" id="useGeolocation">Helyem meghatározása</button>
            </div>

            <div class="mt-4">
                <x-input-label for="current_place_label" value="Jelenlegi hely (település / cím)" />
                <x-text-input id="current_place_label" name="current_place_label" type="text" class="mt-1 block w-full" :value="old('current_place_label')" placeholder="pl. Budapest" />
                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="currentResults"></div>
                <x-input-error :messages="$errors->get('current_place_label')" class="mt-2" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="current_lat" value="Szélesség (lat)" />
                    <x-text-input id="current_lat" name="current_lat" type="number" step="0.000001" class="mt-1 block w-full" :value="old('current_lat')" />
                    <x-input-error :messages="$errors->get('current_lat')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="current_lon" value="Hosszúság (lon)" />
                    <x-text-input id="current_lon" name="current_lon" type="number" step="0.000001" class="mt-1 block w-full" :value="old('current_lon')" />
                    <x-input-error :messages="$errors->get('current_lon')" class="mt-2" />
                </div>
            </div>

            <div class="mt-4">
                <x-input-label for="current_tz_offset" value="Jelenlegi időzóna offset (óra, pl. +2)" />
                <x-text-input id="current_tz_offset" name="current_tz_offset" type="number" step="0.25" class="mt-1 block w-full" :value="old('current_tz_offset')" />
                <x-input-error :messages="$errors->get('current_tz_offset')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
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

        const birthPlace = document.getElementById('birth_place_label');
        const birthResults = document.getElementById('birthResults');
        const birthLat = document.getElementById('birth_lat');
        const birthLon = document.getElementById('birth_lon');

        attachGeocode(birthPlace, birthResults, (item) => {
            birthLat.value = Number(item.lat).toFixed(6);
            birthLon.value = Number(item.lon).toFixed(6);
            birthPlace.value = item.display_name;
        });

        const currentPlace = document.getElementById('current_place_label');
        const currentResults = document.getElementById('currentResults');
        const currentLat = document.getElementById('current_lat');
        const currentLon = document.getElementById('current_lon');

        attachGeocode(currentPlace, currentResults, (item) => {
            currentLat.value = Number(item.lat).toFixed(6);
            currentLon.value = Number(item.lon).toFixed(6);
            currentPlace.value = item.display_name;
        });

        const geoBtn = document.getElementById('useGeolocation');
        geoBtn?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('A böngésző nem támogatja a geolokációt.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    currentLat.value = pos.coords.latitude.toFixed(6);
                    currentLon.value = pos.coords.longitude.toFixed(6);
                },
                (err) => {
                    console.error(err);
                    alert('Nem sikerült meghatározni a helyet (engedély / hiba).');
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });

        // ha a user üresen hagyta a current mezőket, akkor a backend fallback-ol,
        // de UX-ben is oké, ha startból a születési értékek látszanak:
        // (csak akkor másolunk, ha current tényleg üres)
        function copyBirthToCurrentIfEmpty() {
            if (!currentPlace.value.trim() && birthPlace.value.trim()) currentPlace.value = birthPlace.value;
            if (!currentLat.value && birthLat.value) currentLat.value = birthLat.value;
            if (!currentLon.value && birthLon.value) currentLon.value = birthLon.value;
            const currentOffset = document.getElementById('current_tz_offset');
            const birthOffset = document.getElementById('birth_tz_offset');
            if (currentOffset && birthOffset && !currentOffset.value && birthOffset.value) currentOffset.value = birthOffset.value;
        }
        birthLat.addEventListener('change', copyBirthToCurrentIfEmpty);
        birthLon.addEventListener('change', copyBirthToCurrentIfEmpty);
        birthPlace.addEventListener('change', copyBirthToCurrentIfEmpty);
    </script>
</x-guest-layout>
