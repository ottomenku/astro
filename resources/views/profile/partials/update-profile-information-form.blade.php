<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-700">Jelenlegi hely (tranzitokhoz)</h3>
            <p class="text-xs text-gray-500 mt-1">Ezt használjuk a „most” és a jövőbeli tranzit kérdésekhez.</p>

            <div class="mt-4 flex items-center justify-between gap-4">
                <div class="text-xs text-gray-500">Tipp: használhatod a hely keresőt, vagy a böngésző geolokációját.</div>
                <button type="button" class="text-sm underline text-gray-600" id="profileUseGeolocation">Helyem meghatározása</button>
            </div>

            <div class="mt-4">
                <x-input-label for="current_place_label" value="Hely (település / cím)" />
                <x-text-input id="current_place_label" name="current_place_label" type="text" class="mt-1 block w-full" :value="old('current_place_label', $user->current_place_label)" placeholder="pl. Budapest" />
                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="profileCurrentResults"></div>
                <x-input-error :messages="$errors->get('current_place_label')" class="mt-2" />
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="current_lat" value="Szélesség (lat)" />
                    <x-text-input id="current_lat" name="current_lat" type="number" step="0.000001" class="mt-1 block w-full" :value="old('current_lat', $user->current_lat)" />
                    <x-input-error :messages="$errors->get('current_lat')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="current_lon" value="Hosszúság (lon)" />
                    <x-text-input id="current_lon" name="current_lon" type="number" step="0.000001" class="mt-1 block w-full" :value="old('current_lon', $user->current_lon)" />
                    <x-input-error :messages="$errors->get('current_lon')" class="mt-2" />
                </div>
            </div>

            <div class="mt-4">
                <x-input-label for="current_tz_offset" value="Időzóna offset (óra, pl. +2)" />
                <x-text-input id="current_tz_offset" name="current_tz_offset" type="number" step="0.25" class="mt-1 block w-full" :value="old('current_tz_offset', $user->current_tz_offset)" />
                <x-input-error :messages="$errors->get('current_tz_offset')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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

        const geoBtn = document.getElementById('profileUseGeolocation');
        geoBtn?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('A böngésző nem támogatja a geolokációt.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    lat.value = pos.coords.latitude.toFixed(6);
                    lon.value = pos.coords.longitude.toFixed(6);
                },
                (err) => {
                    console.error(err);
                    alert('Nem sikerült meghatározni a helyet (engedély / hiba).');
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });
    </script>
</section>
