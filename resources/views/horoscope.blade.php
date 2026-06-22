<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Horoszkóp</h2>
            <div class="text-sm text-gray-500" id="modeHint">Trópusi · Whole Sign · Natal</div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap gap-2 border-b pb-3">
                        <button type="button" class="px-3 py-2 rounded border border-gray-300" data-tab="chart" id="tabChart">Ábra</button>
                        <button type="button" class="px-3 py-2 rounded border border-gray-300" data-tab="table" id="tabTable">Táblázat</button>
                        <button type="button" class="px-3 py-2 rounded border border-gray-300" data-tab="aspects" id="tabAspects">Fényszögek</button>
                    </div>

                    <div class="mt-4" id="panelChart">
                        <h3 class="font-semibold mb-3">Horoszkóp ábra</h3>
                        <div class="max-w-xl mx-auto" id="chartShell">
                            <svg class="w-full h-auto" viewBox="0 0 400 400" role="img" aria-label="Horoszkóp kerék" id="chartSvg"></svg>
                            <div id="selectionBox" class="mt-3 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded px-3 py-2 hidden"></div>
                        </div>

                        <!-- A hely automatikusan töltődik a Most / Születési idő gombok alapján -->

                        <!-- Vezérlők: a kért sorrendben -->
                        <div class="mt-6 max-w-xl mx-auto space-y-4">
                            <!-- 1) Dátum + idő: egy sorban, közvetlenül az ábra alatt -->
                            <div>
                                <div class="block text-sm font-medium text-gray-700">Dátum / idő</div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500" for="natalDate">Dátum</label>
                                        <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="date" id="natalDate">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500" for="natalTime">Idő</label>
                                        <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="time" id="natalTime" step="60">
                                    </div>
                                </div>
                            </div>

                            <!-- 2) Idő léptetése: percek / órák / napok -->
                            <div>
                                <div class="block text-sm font-medium text-gray-700">Idő léptetése</div>

                                <div class="mt-2 space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-sm text-gray-700 font-medium">Percek léptetése:</div>
                                            <div class="mt-1 flex items-center gap-2">
                                                <input
                                                    class="block w-24 border-gray-300 rounded-md shadow-sm"
                                                    type="number"
                                                    id="shiftMinutes"
                                                    step="1"
                                                    min="1"
                                                    value="1"
                                                >
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="minutes"
                                                    data-shift-dir="-1"
                                                >-</button>
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="minutes"
                                                    data-shift-dir="1"
                                                >+</button>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-sm text-gray-700 font-medium">Órák léptetése:</div>
                                            <div class="mt-1 flex items-center gap-2">
                                                <input
                                                    class="block w-24 border-gray-300 rounded-md shadow-sm"
                                                    type="number"
                                                    id="shiftHours"
                                                    step="1"
                                                    min="1"
                                                    value="1"
                                                >
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="hours"
                                                    data-shift-dir="-1"
                                                >-</button>
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="hours"
                                                    data-shift-dir="1"
                                                >+</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-sm text-gray-700 font-medium">Napok léptetése:</div>
                                            <div class="mt-1 flex items-center gap-2">
                                                <input
                                                    class="block w-24 border-gray-300 rounded-md shadow-sm"
                                                    type="number"
                                                    id="shiftDays"
                                                    step="1"
                                                    min="1"
                                                    value="1"
                                                >
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="days"
                                                    data-shift-dir="-1"
                                                >-</button>
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="days"
                                                    data-shift-dir="1"
                                                >+</button>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-sm text-gray-700 font-medium">Hónapok léptetése:</div>
                                            <div class="mt-1 flex items-center gap-2">
                                                <input
                                                    class="block w-24 border-gray-300 rounded-md shadow-sm"
                                                    type="number"
                                                    id="shiftMonths"
                                                    step="1"
                                                    min="1"
                                                    value="1"
                                                >
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="months"
                                                    data-shift-dir="-1"
                                                >-</button>
                                                <button
                                                    class="px-3 py-1.5 rounded border border-gray-300"
                                                    type="button"
                                                    data-shift-unit="months"
                                                    data-shift-dir="1"
                                                >+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-1 text-xs text-gray-500">Perc / óra / nap léptetés, azonnali újraszámolással.</div>

                                <div class="mt-2 flex items-center gap-2">
                                    <button class="px-3 py-1.5 rounded bg-gray-900 text-white" type="button" id="setNow">Most</button>
                                    <button class="px-3 py-1.5 rounded border border-gray-300" type="button" id="setBirth">Születési idő</button>
                                </div>
                            </div>

                            <!-- 3) Időzóna -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="natalOffset">Időzóna offset (óra, pl. +2)</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="number" step="0.25" id="natalOffset" value="2">
                            </div>

                            <!-- 4) Hely -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="natalQuery">Hely (település / cím)</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="text" id="natalQuery" placeholder="pl. Budapest">
                                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="natalResults"></div>
                            </div>

                            <!-- 5) Szélesség / hosszúság -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="natalLat">Szélesség (lat)</label>
                                    <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="number" step="0.0001" id="natalLat" value="47.4979">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="natalLon">Hosszúság (lon)</label>
                                    <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="number" step="0.0001" id="natalLon" value="19.0402">
                                </div>
                            </div>

                            <!-- 6) Házrendszer, majd 7) Zodiákus -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="houseSystem">Házrendszer</label>
                                <select class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" id="houseSystem">
                                    <option value="whole_sign">Whole Sign</option>
                                    <option value="placidus" selected>Placidus</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="zodiacMode">Zodiákus</label>
                                <select class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" id="zodiacMode">
                                    <option value="tropical" selected>Trópusi (0° Kos = tavaszpont)</option>
                                    <option value="sidereal">Sziderikus (Lahiri)</option>
                                </select>
                            </div>

                            <!-- Számítás gomb nem kell: automatikus számítás (Most/Születési idő/léptetés/módosítás) -->

                            <div class="hidden mt-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 whitespace-pre-wrap" id="errorBox"></div>
                        </div>
                    </div>

                    <div class="mt-4 hidden" id="panelTable">
                        <h3 class="font-semibold mb-3">Bolygó pozíciók</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm text-gray-500 uppercase mb-2">Natal</div>
                                <div id="natalTable"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 uppercase mb-2">Tranzit</div>
                                <div id="transitTable"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 hidden" id="panelAspects">
                        <h3 class="font-semibold mb-3">Fényszögek</h3>
                        <div id="aspectsTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
            // Relatív URL-ek: így mindegy, hogy localhost vagy 127.0.0.1 alatt nyitod meg az oldalt,
            // a fetch mindig ugyanarra az originre megy (nem lesz CORS / "Failed to fetch").
            const geocodeUrl = '{{ route('horoscope.geocode', [], false) }}';
            const calcUrl = '{{ route('horoscope.calculate', [], false) }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const natalInputs = {
                date: document.getElementById('natalDate'),
                time: document.getElementById('natalTime'),
                query: document.getElementById('natalQuery'),
                results: document.getElementById('natalResults'),
                lat: document.getElementById('natalLat'),
                lon: document.getElementById('natalLon'),
                offset: document.getElementById('natalOffset'),
            };

            const setBirthBtn = document.getElementById('setBirth');

            // User születési/jelenlegi hely adatok (Laravel Auth user-ből)
            const USER_LOC = {
                birth: {
                    label: @json(auth()->user()->birth_place_label),
                    lat: @json(auth()->user()->birth_lat),
                    lon: @json(auth()->user()->birth_lon),
                    offset: @json(auth()->user()->birth_tz_offset),
                },
                current: {
                    label: @json(auth()->user()->current_place_label),
                    lat: @json(auth()->user()->current_lat),
                    lon: @json(auth()->user()->current_lon),
                    offset: @json(auth()->user()->current_tz_offset),
                },
            };

            // Születési dátum/idő (UTC + offset) -> lokális input mezők
            const USER_BIRTH = {
                datetime_utc: @json(optional(auth()->user()->birth_datetime_utc)->toISOString()),
                offset: @json(auth()->user()->birth_tz_offset),
            };

            function applyLocation(mode) {
                const src = USER_LOC[mode] || USER_LOC.current;
                if (src.label) natalInputs.query.value = src.label;
                if (src.lat !== null && src.lat !== undefined) natalInputs.lat.value = Number(src.lat).toFixed(4);
                if (src.lon !== null && src.lon !== undefined) natalInputs.lon.value = Number(src.lon).toFixed(4);
                if (src.offset !== null && src.offset !== undefined && src.offset !== '') natalInputs.offset.value = src.offset;

                // tranzit defaultban kövesse a natalt
                transitInputs.query.value = natalInputs.query.value;
                transitInputs.lat.value = natalInputs.lat.value;
                transitInputs.lon.value = natalInputs.lon.value;
                transitInputs.offset.value = natalInputs.offset.value;
            }

            function setBirthTimeFromUser() {
                if (!USER_BIRTH.datetime_utc) return;
                const offset = Number(USER_BIRTH.offset ?? natalInputs.offset.value ?? 0);
                natalInputs.offset.value = String(offset);

                const utcMs = Date.parse(USER_BIRTH.datetime_utc);
                const local = utcMsToLocalInputs(utcMs, offset);
                natalInputs.date.value = local.date;
                natalInputs.time.value = local.time;

                // tranzit defaultban kövesse a natalt
                transitInputs.date.value = natalInputs.date.value;
                transitInputs.time.value = natalInputs.time.value;
            }

            // Transit UI (később): jelenleg a tranzit automatikusan a natal adatokkal azonos.
            const transitInputs = {
                date: natalInputs.date,
                time: natalInputs.time,
                query: natalInputs.query,
                results: natalInputs.results,
                lat: natalInputs.lat,
                lon: natalInputs.lon,
                offset: natalInputs.offset,
            };

            const errorBox = document.getElementById('errorBox');
            // nincs számítás gomb, marad a kód a "busy" jelzéshez (null-ellenőrzéssel)
            const calcButton = document.getElementById('calcButton');
            // régi "Natal → tranzit" gomb már nincs a tabos UI-ban
            const copyButton = null;
            const chartSvg = document.getElementById('chartSvg');
            const natalTable = document.getElementById('natalTable');
            const transitTable = document.getElementById('transitTable');
            const zodiacModeSelect = document.getElementById('zodiacMode');
            const houseSystemSelect = document.getElementById('houseSystem');
            // jelenleg csak natal réteg van a keréken
            const showNatalCheckbox = { checked: true, addEventListener: () => {} };
            const showTransitCheckbox = { checked: false, addEventListener: () => {} };
            const aspectsTable = document.getElementById('aspectsTable');
            const tabChart = document.getElementById('tabChart');
            const tabTable = document.getElementById('tabTable');
            const tabAspects = document.getElementById('tabAspects');
            const panelChart = document.getElementById('panelChart');
            const panelTable = document.getElementById('panelTable');
            const panelAspects = document.getElementById('panelAspects');
            const modeHint = document.getElementById('modeHint');
            const selectionBox = document.getElementById('selectionBox');

            function showSelection(text) {
                if (!selectionBox) return;
                selectionBox.textContent = text;
                selectionBox.classList.remove('hidden');
            }

            const planetsOrder = [
                'Sun',
                'Moon',
                'Mercury',
                'Venus',
                'Mars',
                'Jupiter',
                'Saturn',
                'Uranus',
                'Neptune',
                'Pluto',
                'True Node',
            ];

            const signSymbols = ['♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓'];
            const signNamesHu = ['Kos', 'Bika', 'Ikrek', 'Rák', 'Oroszlán', 'Szűz', 'Mérleg', 'Skorpió', 'Nyilas', 'Bak', 'Vízöntő', 'Halak'];

            // Jegy meta (a megadott táblázat alapján)
            const signMeta = [
                { name: 'Aries', element: 'fire', quality: 'cardinal', polarity: 'positive' },
                { name: 'Taurus', element: 'earth', quality: 'fixed', polarity: 'negative' },
                { name: 'Gemini', element: 'air', quality: 'mutable', polarity: 'positive' },
                { name: 'Cancer', element: 'water', quality: 'cardinal', polarity: 'negative' },
                { name: 'Leo', element: 'fire', quality: 'fixed', polarity: 'positive' },
                { name: 'Virgo', element: 'earth', quality: 'mutable', polarity: 'negative' },
                { name: 'Libra', element: 'air', quality: 'cardinal', polarity: 'positive' },
                { name: 'Scorpio', element: 'water', quality: 'fixed', polarity: 'negative' },
                { name: 'Sagittarius', element: 'fire', quality: 'mutable', polarity: 'positive' },
                { name: 'Capricorn', element: 'earth', quality: 'cardinal', polarity: 'negative' },
                { name: 'Aquarius', element: 'air', quality: 'fixed', polarity: 'positive' },
                { name: 'Pisces', element: 'water', quality: 'mutable', polarity: 'negative' },
            ];

            const aspectDefs = [
                { name: 'conjunction', angle: 0, color: '#6c757d', mark: '☌' },
                // kérés szerint 60°: háromszög
                { name: 'sextile', angle: 60, color: '#0dcaf0', mark: '△' },
                // kérés szerint 90°: négyzet
                { name: 'square', angle: 90, color: '#dc3545', mark: '□' },
                { name: 'trine', angle: 120, color: '#198754', mark: '△' },
                { name: 'opposition', angle: 180, color: '#fd7e14', mark: '☍' },
            ];

            function updateModeHint() {
                const mode = zodiacModeSelect.value;
                const house = houseSystemSelect.value === 'placidus' ? 'Placidus' : 'Whole Sign';
                modeHint.textContent =
                    mode === 'sidereal'
                        ? `Sziderikus (Lahiri) · ${house} · Natal`
                        : `Trópusi · ${house} · Natal`;
            }

            function setDefaultTimes() {
                const now = new Date();
                const pad = (v) => String(v).padStart(2, '0');
                natalInputs.date.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
                natalInputs.time.value = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
                transitInputs.date.value = natalInputs.date.value;
                transitInputs.time.value = natalInputs.time.value;
            }

            function setActiveTab(name) {
                const btnOn = 'px-3 py-2 rounded bg-indigo-600 text-white';
                const btnOff = 'px-3 py-2 rounded border border-gray-300';

                tabChart.className = name === 'chart' ? btnOn : btnOff;
                tabTable.className = name === 'table' ? btnOn : btnOff;
                tabAspects.className = name === 'aspects' ? btnOn : btnOff;

                panelChart.classList.toggle('hidden', name !== 'chart');
                panelTable.classList.toggle('hidden', name !== 'table');
                panelAspects.classList.toggle('hidden', name !== 'aspects');
            }
            function renderAspectsTable(target, planets) {
                const aspects = calcAspects(planets)
                    .slice()
                    .sort((a, b) => a.def.angle - b.def.angle || a.orb - b.orb);

                if (!aspects.length) {
                    target.innerHTML = '<div class="text-sm text-gray-500">Nincs találat a jelenlegi orbis beállítással.</div>';
                    return;
                }

                const rows = aspects
                    .map(
                        ({ p1, p2, def }) => `<tr>
                            <td class="py-2 pr-4">${p1.name}</td>
                            <td class="py-2 pr-4 font-semibold" style="color:${def.color}">${def.mark}</td>
                            <td class="py-2">${p2.name}</td>
                        </tr>`
                    )
                    .join('');

                target.innerHTML = `<div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2 pr-4">Bolygó</th>
                                <th class="py-2 pr-4">Jel</th>
                                <th class="py-2">Bolygó</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">${rows}</tbody>
                    </table>
                </div>`;
            }

            function localToUtcMs(dateStr, timeStr, offsetHours) {
                const [year, month, day] = dateStr.split('-').map(Number);
                const [hour, minute] = timeStr.split(':').map(Number);

                const localMs = Date.UTC(year, month - 1, day, hour, minute);
                return localMs - offsetHours * 60 * 60 * 1000;
            }

            function utcMsToLocalInputs(utcMs, offsetHours) {
                const localMs = utcMs + offsetHours * 60 * 60 * 1000;
                const dt = new Date(localMs);

                const pad = (v) => String(v).padStart(2, '0');
                const date = `${dt.getUTCFullYear()}-${pad(dt.getUTCMonth() + 1)}-${pad(dt.getUTCDate())}`;
                const time = `${pad(dt.getUTCHours())}:${pad(dt.getUTCMinutes())}`;
                return { date, time };
            }

            function shiftNatalTimeBySeconds(deltaSeconds) {
                const err = validateInputs(natalInputs);
                if (err) {
                    errorBox.textContent = err;
                    errorBox.classList.remove('hidden');
                    return;
                }

                const offset = Number(natalInputs.offset.value);
                const utcMs = localToUtcMs(natalInputs.date.value, natalInputs.time.value, offset);
                const nextUtcMs = utcMs + deltaSeconds * 1000;
                const nextLocal = utcMsToLocalInputs(nextUtcMs, offset);

                natalInputs.date.value = nextLocal.date;
                natalInputs.time.value = nextLocal.time;
                // tranzit defaultban kövesse a natalt
                transitInputs.date.value = nextLocal.date;
                transitInputs.time.value = nextLocal.time;

                calculate();
            }

            function shiftNatalTimeByMonths(deltaMonths) {
                const err = validateInputs(natalInputs);
                if (err) {
                    errorBox.textContent = err;
                    errorBox.classList.remove('hidden');
                    return;
                }

                const offset = Number(natalInputs.offset.value);
                const utcMs = localToUtcMs(natalInputs.date.value, natalInputs.time.value, offset);

                // A localMs-t UTC-ként kezeljük, így a dt.getUTC* visszaadja a lokális értékeket.
                const localMs = utcMs + offset * 60 * 60 * 1000;
                const dt = new Date(localMs);
                dt.setUTCMonth(dt.getUTCMonth() + deltaMonths);

                const pad = (v) => String(v).padStart(2, '0');
                const date = `${dt.getUTCFullYear()}-${pad(dt.getUTCMonth() + 1)}-${pad(dt.getUTCDate())}`;
                const time = `${pad(dt.getUTCHours())}:${pad(dt.getUTCMinutes())}`;

                natalInputs.date.value = date;
                natalInputs.time.value = time;
                transitInputs.date.value = date;
                transitInputs.time.value = time;

                calculate();
            }

            function setDefaultCoords() {
                // Budapest alapértékek (csak ha üresek a mezők)
                if (natalInputs.lat.value === '') natalInputs.lat.value = '47.4979';
                if (natalInputs.lon.value === '') natalInputs.lon.value = '19.0402';
                if (transitInputs.lat.value === '') transitInputs.lat.value = '47.4979';
                if (transitInputs.lon.value === '') transitInputs.lon.value = '19.0402';
            }

            function toUtcIso(dateStr, timeStr, offsetHours) {
                const [year, month, day] = dateStr.split('-').map(Number);
                const [hour, minute] = timeStr.split(':').map(Number);
                const localMs = Date.UTC(year, month - 1, day, hour, minute);
                const utcMs = localMs - offsetHours * 60 * 60 * 1000;
                return new Date(utcMs).toISOString();
            }

            async function geocode(query) {
                const response = await fetch(`${geocodeUrl}?q=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    return [];
                }
                const data = await response.json();
                return data.results || [];
            }

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

            function attachGeocode(inputs) {
                let timeout;
                inputs.query.addEventListener('input', () => {
                    clearTimeout(timeout);
                    const query = inputs.query.value.trim();
                    if (query.length < 3) {
                        renderResults(inputs.results, [], () => {});
                        return;
                    }
                    timeout = setTimeout(async () => {
                        const results = await geocode(query);
                        renderResults(inputs.results, results, (item) => {
                            inputs.lat.value = Number(item.lat).toFixed(4);
                            inputs.lon.value = Number(item.lon).toFixed(4);
                            inputs.query.value = item.display_name;
                            renderResults(inputs.results, [], () => {});
                        });
                    }, 400);
                });
            }

            function validateInputs(inputs) {
                if (!inputs.date.value || !inputs.time.value) {
                    return 'Adj meg dátumot és időt.';
                }
                if (inputs.lat.value === '' || inputs.lon.value === '') {
                    return 'Add meg a lat/lon koordinátákat.';
                }
                if (inputs.offset.value === '') {
                    return 'Add meg az időzóna offsetet.';
                }
                return '';
            }

            function renderTable(target, planets) {
                const rows = planets
                    .slice()
                    .sort((a, b) => planetsOrder.indexOf(a.name) - planetsOrder.indexOf(b.name))
                    .map(
                        (planet) => `<tr>
                            <td>${planet.name}</td>
                            <td>${planet.sign} ${planet.sign_degree.toFixed(2)}°</td>
                            <td>${planet.house}</td>
                        </tr>`
                    )
                    .join('');

                target.innerHTML = `
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">Bolygó</th>
                                    <th class="py-2 pr-4">Jegy</th>
                                    <th class="py-2 pr-4">Ház</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">${rows}</tbody>
                        </table>
                    </div>`;
            }

            const CHART = {
                cx: 200,
                cy: 200,

                // Zodiákus gyűrű
                rZodiacOuter: 168,
                rZodiacInner: 140,

                // Ház gyűrű
                rHouseOuter: 140,
                // +20% vastagság (eredetileg 35px volt: 140-105). 35 * 1.2 = 42.
                rHouseInner: 98,

                // Belső kör és aspektusok
                // az aspektus zóna 20%-kal keskenyebb: (rHouseInner - rInner) 15px -> 12px
                rAspect: 92,
                rInner: 86,

                // Bolygók
                rPlanetBase: 123,
                rPlanetStep: 12,
            };

            function normalizeAngle(deg) {
                let v = deg % 360;
                if (v < 0) v += 360;
                return v;
            }

            function polarToCartesian(angleDeg, radius) {
                const angleRad = (angleDeg - 90) * (Math.PI / 180);
                return {
                    x: CHART.cx + radius * Math.cos(angleRad),
                    y: CHART.cy + radius * Math.sin(angleRad),
                };
            }

            function svgEl(tag) {
                return document.createElementNS('http://www.w3.org/2000/svg', tag);
            }

            function annularSectorPath(startAngleDeg, endAngleDeg, rOuter, rInner) {
                const a0 = polarToCartesian(startAngleDeg, rOuter);
                const a1 = polarToCartesian(endAngleDeg, rOuter);
                const b1 = polarToCartesian(endAngleDeg, rInner);
                const b0 = polarToCartesian(startAngleDeg, rInner);
                const large = endAngleDeg - startAngleDeg > 180 ? 1 : 0;
                return [
                    `M ${a0.x} ${a0.y}`,
                    `A ${rOuter} ${rOuter} 0 ${large} 1 ${a1.x} ${a1.y}`,
                    `L ${b1.x} ${b1.y}`,
                    `A ${rInner} ${rInner} 0 ${large} 0 ${b0.x} ${b0.y}`,
                    'Z',
                ].join(' ');
            }

            function clearChart() {
                chartSvg.innerHTML = '';

                // alap gyűrűk
                const zodiacOuter = svgEl('circle');
                zodiacOuter.setAttribute('cx', CHART.cx);
                zodiacOuter.setAttribute('cy', CHART.cy);
                zodiacOuter.setAttribute('r', CHART.rZodiacOuter);
                zodiacOuter.setAttribute('fill', 'none');
                // a zodiákus külső köre a legkülső
                zodiacOuter.setAttribute('stroke', '#212529');
                zodiacOuter.setAttribute('stroke-width', '2');
                chartSvg.appendChild(zodiacOuter);

                const zodiacInner = svgEl('circle');
                zodiacInner.setAttribute('cx', CHART.cx);
                zodiacInner.setAttribute('cy', CHART.cy);
                zodiacInner.setAttribute('r', CHART.rZodiacInner);
                zodiacInner.setAttribute('fill', 'none');
                zodiacInner.setAttribute('stroke', '#343a40');
                zodiacInner.setAttribute('stroke-width', '1');
                chartSvg.appendChild(zodiacInner);

                const houseInner = svgEl('circle');
                houseInner.setAttribute('cx', CHART.cx);
                houseInner.setAttribute('cy', CHART.cy);
                houseInner.setAttribute('r', CHART.rHouseInner);
                houseInner.setAttribute('fill', 'none');
                houseInner.setAttribute('stroke', '#6c757d');
                houseInner.setAttribute('stroke-width', '1');
                chartSvg.appendChild(houseInner);

                const inner = svgEl('circle');
                inner.setAttribute('cx', CHART.cx);
                inner.setAttribute('cy', CHART.cy);
                inner.setAttribute('r', CHART.rInner);
                inner.setAttribute('fill', '#fff');
                inner.setAttribute('stroke', '#6c757d');
                inner.setAttribute('stroke-width', '1');
                chartSvg.appendChild(inner);

                // rétegek sorrendben
                const ticks = svgEl('g');
                ticks.setAttribute('data-layer', 'ticks');
                chartSvg.appendChild(ticks);

                const zodiac = svgEl('g');
                zodiac.setAttribute('data-layer', 'zodiac');
                chartSvg.appendChild(zodiac);

                const houses = svgEl('g');
                houses.setAttribute('data-layer', 'houses');
                chartSvg.appendChild(houses);

                const aspects = svgEl('g');
                aspects.setAttribute('data-layer', 'aspects');
                chartSvg.appendChild(aspects);

                const planets = svgEl('g');
                planets.setAttribute('data-layer', 'planets');
                chartSvg.appendChild(planets);

                // címkék (mindig legfelül): házszámok, fényszög jelölések
                const labels = svgEl('g');
                labels.setAttribute('data-layer', 'labels');
                labels.setAttribute('style', 'pointer-events: none;');
                chartSvg.appendChild(labels);
            }

            function getLayer(name) {
                return chartSvg.querySelector(`g[data-layer="${name}"]`);
            }

            function decanColor(meta, decanIndex) {
                // decanIndex: 1..3
                if (decanIndex === 1) {
                    return meta.polarity === 'negative' ? '#000000' : '#ffffff';
                }
                if (decanIndex === 2) {
                    if (meta.quality === 'fixed') return '#a855f7'; // lila
                    if (meta.quality === 'cardinal') return '#facc15'; // sárga
                    return '#7dd3fc'; // világoskék (változó)
                }
                // 3. dekád: elem
                if (meta.element === 'water') return '#2563eb';
                if (meta.element === 'fire') return '#dc2626';
                if (meta.element === 'earth') return '#92400e';
                return '#16a34a'; // air
            }

            function drawZodiacTicks(rotationDeg) {
                const layer = getLayer('ticks');
                if (!layer) return;
                for (let deg = 0; deg < 360; deg++) {
                    const angle = deg + rotationDeg;
                    const isMajor = deg % 10 === 0;
                    const isMid = !isMajor && deg % 5 === 0;

                    const len = isMajor ? 10 : isMid ? 7 : 4;
                    const r1 = CHART.rZodiacOuter;
                    const r2 = CHART.rZodiacOuter - len;

                    const a = polarToCartesian(angle, r1);
                    const b = polarToCartesian(angle, r2);
                    const line = svgEl('line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    line.setAttribute('stroke', isMajor ? '#111827' : '#9ca3af');
                    line.setAttribute('stroke-width', isMajor ? '1.4' : isMid ? '1.1' : '0.8');
                    layer.appendChild(line);
                }
            }

            function hexToRgb(hex) {
                const h = String(hex).replace('#', '').trim();
                const v = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
                const n = parseInt(v, 16);
                return {
                    r: (n >> 16) & 255,
                    g: (n >> 8) & 255,
                    b: n & 255,
                };
            }

            function rgbToHex({ r, g, b }) {
                const to2 = (x) => String(Math.max(0, Math.min(255, Math.round(x))).toString(16)).padStart(2, '0');
                return `#${to2(r)}${to2(g)}${to2(b)}`;
            }

            function mixHex(a, b, t) {
                const c1 = hexToRgb(a);
                const c2 = hexToRgb(b);
                return rgbToHex({
                    r: c1.r + (c2.r - c1.r) * t,
                    g: c1.g + (c2.g - c1.g) * t,
                    b: c1.b + (c2.b - c1.b) * t,
                });
            }

            function drawDecans(rotationDeg) {
                const layer = getLayer('zodiac');
                if (!layer) return;

                // Kérés: a dekád színek legyenek halványabbak és átmenetesek (ne éles vágás)
                // Viszont a jegyhatár (30°) maradjon éles (azt külön vonallal húzzuk).
                const baseOpacity = 0.55;
                const transitionWidthDeg = 4; // az átmenet szélessége a dekád-határ körül
                const slices = 10; // minél több, annál simább az átmenet

                for (let sign = 0; sign < 12; sign++) {
                    const meta = signMeta[sign];
                    const c1 = decanColor(meta, 1);
                    const c2 = decanColor(meta, 2);
                    const c3 = decanColor(meta, 3);

                    // Alapszegmensek (a dekád-határok körül meghagyunk helyet az átmenetnek)
                    const halfT = transitionWidthDeg / 2;
                    const segs = [
                        { start: 0, end: 10 - halfT, color: c1 },
                        { start: 10 + halfT, end: 20 - halfT, color: c2 },
                        { start: 20 + halfT, end: 30, color: c3 },
                    ];

                    segs.forEach((s) => {
                        if (s.end <= s.start) return;
                        const path = svgEl('path');
                        path.setAttribute(
                            'd',
                            annularSectorPath(
                                sign * 30 + s.start + rotationDeg,
                                sign * 30 + s.end + rotationDeg,
                                CHART.rZodiacOuter,
                                CHART.rZodiacInner
                            )
                        );
                        path.setAttribute('fill', s.color);
                        path.setAttribute('opacity', String(baseOpacity));
                        // NINCS stroke: a dekád-határ ne legyen éles
                        layer.appendChild(path);
                    });

                    // Átmenet a 10° és 20° határokon belül (jegyhatáron nincs átmenet)
                    const transitions = [
                        { boundary: 10, from: c1, to: c2 },
                        { boundary: 20, from: c2, to: c3 },
                    ];

                    transitions.forEach(({ boundary, from, to }) => {
                        const tStart = boundary - halfT;
                        const step = transitionWidthDeg / slices;
                        for (let i = 0; i < slices; i++) {
                            const a0 = tStart + i * step;
                            const a1 = a0 + step;
                            const t = (i + 0.5) / slices;
                            const col = mixHex(from, to, t);
                            const path = svgEl('path');
                            path.setAttribute(
                                'd',
                                annularSectorPath(
                                    sign * 30 + a0 + rotationDeg,
                                    sign * 30 + a1 + rotationDeg,
                                    CHART.rZodiacOuter,
                                    CHART.rZodiacInner
                                )
                            );
                            path.setAttribute('fill', col);
                            path.setAttribute('opacity', String(baseOpacity));
                            layer.appendChild(path);
                        }
                    });
                }
            }

            function drawZodiacRing(rotationDeg) {
                const layer = getLayer('zodiac');
                if (!layer) return;

                // dekád háttérszínezés a zodiákus gyűrűben
                drawDecans(rotationDeg);

                // fokbeosztás a zodiákus külső peremén (mint a régi legkülső kör)
                drawZodiacTicks(rotationDeg);

                // 30° határok (jegyhatár) – vékony sárga „árnyék” kiemeléssel
                for (let i = 0; i < 12; i++) {
                    const angle = normalizeAngle(i * 30 + rotationDeg);
                    const a = polarToCartesian(angle, CHART.rZodiacInner);
                    const b = polarToCartesian(angle, CHART.rZodiacOuter);

                    // sárga aláhúzás / árnyék (kicsit vastagabb)
                    const glow = svgEl('line');
                    glow.setAttribute('x1', a.x);
                    glow.setAttribute('y1', a.y);
                    glow.setAttribute('x2', b.x);
                    glow.setAttribute('y2', b.y);
                    glow.setAttribute('stroke', '#facc15');
                    // erősebb sárga árnyék
                    glow.setAttribute('stroke-width', '2.6');
                    glow.setAttribute('opacity', '0.85');
                    layer.appendChild(glow);

                    const line = svgEl('line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    // jegyelválasztó vonal: vastagabb + fekete
                    line.setAttribute('stroke', '#111827');
                    line.setAttribute('stroke-width', '2');
                    layer.appendChild(line);

                    // jegy jel a szektor közepén
                    const mid = normalizeAngle(i * 30 + 15 + rotationDeg);
                    const p = polarToCartesian(mid, (CHART.rZodiacInner + CHART.rZodiacOuter) / 2);
                    const label = svgEl('text');
                    label.setAttribute('x', p.x);
                    label.setAttribute('y', p.y);
                    label.setAttribute('text-anchor', 'middle');
                    label.setAttribute('dominant-baseline', 'middle');
                    label.setAttribute('font-size', '14');
                    label.setAttribute('fill', '#111827');
                    label.textContent = signSymbols[i];
                    label.style.cursor = 'pointer';
                    label.addEventListener('click', () => showSelection(`Jegy: ${signNamesHu[i]}`));
                    layer.appendChild(label);
                }
            }

            function drawInnerRingTicks(rotationDeg) {
                // Legbelső gyűrű: rInner..rAspect zóna
                const layer = getLayer('ticks');
                if (!layer) return;

                for (let deg = 0; deg < 360; deg++) {
                    const angle = deg + rotationDeg;
                    const isDecan = deg % 10 === 0;
                    const isMid = !isDecan && deg % 5 === 0;

                    // belső jegyelválasztók (30°) – kék árnyék
                    const isSignBoundary = deg % 30 === 0;

                    const len = isDecan ? 10 : isMid ? 7 : 4;
                    const r1 = CHART.rAspect;
                    const r2 = CHART.rAspect - len;

                    // A 30° jegyelválasztóknál nem rajzolunk külön „rövid tick”-et,
                    // ott a teljes hosszú (inner->houseInner) vastag fekete vonal jelöl.
                    if (!isSignBoundary) {
                        const a = polarToCartesian(angle, r1);
                        const b = polarToCartesian(angle, r2);
                        const line = svgEl('line');
                        line.setAttribute('x1', a.x);
                        line.setAttribute('y1', a.y);
                        line.setAttribute('x2', b.x);
                        line.setAttribute('y2', b.y);
                        line.setAttribute('stroke', isDecan ? '#111827' : '#9ca3af');
                        // belső dekádok legyenek vékonyabbak + halványabbak
                        line.setAttribute('stroke-width', isDecan ? '0.75' : isMid ? '0.65' : '0.45');
                        line.setAttribute('opacity', '0.65');
                        layer.appendChild(line);
                    }

                    if (isSignBoundary) {
                        // kék glow a 30° határnak (a normál vonal alatt)
                        // a belső jegyelválasztó most érjen a következő körig (houseInner)
                        const ga = polarToCartesian(angle, CHART.rHouseInner);
                        // belülről induljon a 2. körig (inner -> houseInner)
                        const gb = polarToCartesian(angle, CHART.rInner);
                        const glow = svgEl('line');
                        glow.setAttribute('x1', gb.x);
                        glow.setAttribute('y1', gb.y);
                        glow.setAttribute('x2', ga.x);
                        glow.setAttribute('y2', ga.y);
                        glow.setAttribute('stroke', '#60a5fa');
                        glow.setAttribute('stroke-width', '2.4');
                        glow.setAttribute('opacity', '0.7');
                        // a kék árnyék legyen a normál vonal alatt: ezért utólag beszúrjuk úgy,
                        // hogy a layer elejére tesszük.
                        layer.insertBefore(glow, layer.firstChild);

                        // a belső jegyelválasztó vonal legyen vastagabb + fekete
                        const sa = polarToCartesian(angle, CHART.rHouseInner);
                        const sb = polarToCartesian(angle, CHART.rInner);
                        const sline = svgEl('line');
                        sline.setAttribute('x1', sb.x);
                        sline.setAttribute('y1', sb.y);
                        sline.setAttribute('x2', sa.x);
                        sline.setAttribute('y2', sa.y);
                        sline.setAttribute('stroke', '#111827');
                        sline.setAttribute('stroke-width', '2');
                        sline.setAttribute('opacity', '0.95');
                        layer.appendChild(sline);

                        // vékony szürke összekötő vonal a külső és belső jegyelválasztók között
                        // (zodiákus inner -> houseInner)
                        const ca = polarToCartesian(angle, CHART.rZodiacInner);
                        const cb = polarToCartesian(angle, CHART.rHouseInner);
                        const connector = svgEl('line');
                        connector.setAttribute('x1', cb.x);
                        connector.setAttribute('y1', cb.y);
                        connector.setAttribute('x2', ca.x);
                        connector.setAttribute('y2', ca.y);
                        connector.setAttribute('stroke', '#9ca3af');
                        connector.setAttribute('stroke-width', '0.7');
                        connector.setAttribute('opacity', '0.85');
                        // a szürke csatlakozó menjen a vastag vonalak alá
                        layer.insertBefore(connector, layer.firstChild);
                    }
                }
            }

            function drawInnerPlanetMarkers(planets, rotationDeg) {
                // Bolygó-helyzet jelölő vonalak a legbelső gyűrűben
                const layer = getLayer('ticks');
                if (!layer) return;

                planets.forEach((p) => {
                    const style = getPlanetStyle(p.name);
                    const angle = p.longitude + rotationDeg;
                    const a = polarToCartesian(angle, CHART.rInner + 1);
                    const b = polarToCartesian(angle, CHART.rAspect - 1);
                    const line = svgEl('line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    line.setAttribute('stroke', style.fg);
                    line.setAttribute('stroke-width', '1.4');
                    line.setAttribute('opacity', '0.95');
                    layer.appendChild(line);
                });
            }

            function drawPlanetMarkers(planets, rotationDeg) {
                const layer = getLayer('ticks');
                if (!layer) return;
                planets.forEach((p) => {
                    const style = getPlanetStyle(p.name);
                    const angle = p.longitude + rotationDeg;
                    const a = polarToCartesian(angle, CHART.rZodiacInner + 1);
                    const b = polarToCartesian(angle, CHART.rZodiacOuter - 1);
                    const line = svgEl('line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    line.setAttribute('stroke', style.fg);
                    line.setAttribute('stroke-width', '1.1');
                    line.setAttribute('opacity', '0.9');
                    layer.appendChild(line);

                    // plusz jelölés a zodiákus külső fokbeosztásán (kis tick)
                    const t1 = polarToCartesian(angle, CHART.rZodiacOuter);
                    const t2 = polarToCartesian(angle, CHART.rZodiacOuter - 12);
                    const tick = svgEl('line');
                    tick.setAttribute('x1', t1.x);
                    tick.setAttribute('y1', t1.y);
                    tick.setAttribute('x2', t2.x);
                    tick.setAttribute('y2', t2.y);
                    tick.setAttribute('stroke', style.fg);
                    tick.setAttribute('stroke-width', '1.4');
                    tick.setAttribute('opacity', '0.95');
                    layer.appendChild(tick);
                });
            }

            function drawHousesFromCusps(cusps, rotationDeg) {
                const layer = getLayer('houses');
                if (!layer) return;

                // 1/4/7/10 tengelyek (ASC/IC/DSC/MC)
                const axisIndices = new Set([0, 3, 6, 9]);

                cusps.forEach((rawAngle, idx) => {
                    const angle = normalizeAngle(rawAngle + rotationDeg);
                    const outer = polarToCartesian(angle, CHART.rHouseOuter);
                    const inner = polarToCartesian(angle, CHART.rHouseInner);

                    const line = svgEl('line');
                    line.setAttribute('x1', inner.x);
                    line.setAttribute('y1', inner.y);
                    line.setAttribute('x2', outer.x);
                    line.setAttribute('y2', outer.y);
                    line.setAttribute('stroke', '#dc3545');
                    line.setAttribute('opacity', axisIndices.has(idx) ? '0.95' : '0.55');
                    line.setAttribute('stroke-width', axisIndices.has(idx) ? '2.4' : '1.4');
                    layer.appendChild(line);
                });
            }

            function angleMid(a, b) {
                // midpoint a->b on circle
                let diff = (b - a + 360) % 360;
                return (a + diff / 2) % 360;
            }

            function drawHouseNumbersFromCusps(cusps, rotationDeg) {
                const layer = getLayer('labels');
                if (!layer) return;
                for (let i = 0; i < 12; i++) {
                    const mid = angleMid(cusps[i], cusps[(i + 1) % 12]);
                    // a ház-gyűrűben legyen (ne a zodiákus gyűrűben)
                    const r = (CHART.rHouseOuter + CHART.rHouseInner) / 2;
                    const point = polarToCartesian(normalizeAngle(mid + rotationDeg), r);

                    const label = svgEl('text');
                    label.setAttribute('x', point.x);
                    label.setAttribute('y', point.y);
                    label.setAttribute('text-anchor', 'middle');
                    label.setAttribute('dominant-baseline', 'middle');
                    // 50%-kal kisebb
                    label.setAttribute('font-size', '6');
                    label.setAttribute('fill', '#dc3545');
                    label.setAttribute('font-weight', '600');
                    label.textContent = String(i + 1);
                    layer.appendChild(label);
                }
            }

            function drawAspectMark(def, a, b) {
                const layer = getLayer('labels');
                if (!layer) return;
                const mx = (a.x + b.x) / 2;
                const my = (a.y + b.y) / 2;

                const text = svgEl('text');
                text.setAttribute('x', mx);
                text.setAttribute('y', my);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('dominant-baseline', 'middle');
                text.setAttribute('font-size', '10');
                text.setAttribute('fill', def.color);
                text.setAttribute('font-weight', '700');
                // csak jel (nincs fokszám)
                text.textContent = `${def.mark}`;
                layer.appendChild(text);
            }

            function smallestAngleDiff(a, b) {
                let d = Math.abs(a - b) % 360;
                return d > 180 ? 360 - d : d;
            }

            function aspectOrbis(p1, p2) {
                const isLuminary = (p) => p.name === 'Sun' || p.name === 'Moon';
                return isLuminary(p1) || isLuminary(p2) ? 3 : 2;
            }

            function calcAspects(planets) {
                const aspects = [];
                for (let i = 0; i < planets.length; i++) {
                    for (let j = i + 1; j < planets.length; j++) {
                        const p1 = planets[i];
                        const p2 = planets[j];
                        const diff = smallestAngleDiff(p1.longitude, p2.longitude);
                        const orbis = aspectOrbis(p1, p2);
                        for (const def of aspectDefs) {
                            const delta = Math.abs(diff - def.angle);
                            if (delta <= orbis) {
                                aspects.push({ p1, p2, def, orb: delta });
                                break;
                            }
                        }
                    }
                }
                return aspects;
            }

            function drawAspects(planets, radius, strokeOpacity, rotationDeg) {
                const layer = getLayer('aspects');
                if (!layer) return;
                const aspects = calcAspects(planets);
                aspects.forEach(({ p1, p2, def }) => {
                    const a = polarToCartesian(normalizeAngle(p1.longitude + rotationDeg), radius);
                    const b = polarToCartesian(normalizeAngle(p2.longitude + rotationDeg), radius);
                    const line = svgEl('line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    line.setAttribute('stroke', def.color);
                    line.setAttribute('stroke-width', '2.2');
                    line.setAttribute('opacity', String(strokeOpacity));
                    layer.appendChild(line);

                    drawAspectMark(def, a, b);
                });
            }

            // Standard bolygó jelek (unicode). A kérésed szerint ezeket használjuk.
            // Font: Windows alatt a Segoe UI Symbol általában tartalmazza ezeket.
            const planetSymbols = {
                Sun: '☉',
                Moon: '☾',
                Mercury: '☿',
                Venus: '♀',
                Mars: '♂',
                Jupiter: '♃',
                Saturn: '♄',
                Uranus: '♅',
                Neptune: '♆',
                Pluto: '♇',
                'True Node': '☊',
            };

            function getPlanetStyle(name) {
                const base = {
                    symbol: planetSymbols[name] ?? '?',
                    fg: '#111',
                    bg: '#fff',
                    r: 14,
                    fontSize: 18,
                    ringStroke: '#111',
                    ringStrokeWidth: 1,
                };

                switch (name) {
                    case 'Sun':
                        // Nap: sárga alapon fehér
                        return { ...base, fg: '#ffffff', bg: '#facc15', r: 18, fontSize: 28, ringStroke: '#111827' };
                    case 'Moon':
                        return { ...base, fg: '#111827', bg: '#ffffff', r: 16, fontSize: 22 };
                    case 'Mars':
                        return { ...base, fg: '#dc2626' };
                    case 'Venus':
                        return { ...base, fg: '#2563eb' };
                    case 'Jupiter':
                        return { ...base, fg: '#7f1d1d' }; // bordó
                    case 'Saturn':
                        return { ...base, fg: '#7c3aed' }; // lila
                    case 'Mercury':
                        return { ...base, fg: '#16a34a' };
                    default:
                        return { ...base, fg: '#111827' };
                }
            }

            function drawPlanetGlyph(name, x, y, style) {
                const layer = getLayer('planets');
                if (!layer) return;

                const g = svgEl('g');
                g.setAttribute('transform', `translate(${x} ${y})`);

                // kör alakú ikon (vékony fekete kör)
                const ring = svgEl('circle');
                ring.setAttribute('cx', '0');
                ring.setAttribute('cy', '0');
                ring.setAttribute('r', String(style.r));
                ring.setAttribute('fill', style.bg);
                ring.setAttribute('stroke', style.ringStroke);
                ring.setAttribute('stroke-width', String(style.ringStrokeWidth));
                g.appendChild(ring);

                const t = svgEl('text');
                t.setAttribute('x', '0');
                t.setAttribute('y', '0');
                t.setAttribute('text-anchor', 'middle');
                t.setAttribute('dominant-baseline', 'middle');
                t.setAttribute('font-size', String(style.fontSize));
                t.setAttribute('font-family', '"Segoe UI Symbol", "Noto Sans Symbols2", "DejaVu Sans", sans-serif');
                t.setAttribute('fill', style.fg);
                t.textContent = style.symbol;
                g.appendChild(t);

                layer.appendChild(g);

                // kattintás: bolygó neve
                g.style.cursor = 'pointer';
                g.addEventListener('click', () => showSelection(`Bolygó: ${name}`));
            }

            function drawPlanets(planets, rotationDeg) {
                const layer = getLayer('planets');
                if (!layer) return;

                // kis ütközéskezelés: ha nagyon közel vannak egymáshoz, lépcsőzzük a sugarat
                const sorted = planets
                    .slice()
                    .sort((a, b) => normalizeAngle(a.longitude + rotationDeg) - normalizeAngle(b.longitude + rotationDeg));

                let lastAngle = null;
                let level = 0;
                sorted.forEach((planet) => {
                    const style = getPlanetStyle(planet.name);
                    const angle = normalizeAngle(planet.longitude + rotationDeg);
                    if (lastAngle !== null && smallestAngleDiff(angle, lastAngle) < 8) {
                        level = (level + 1) % 3;
                    } else {
                        level = 0;
                    }
                    lastAngle = angle;

                    const radius = CHART.rPlanetBase + level * CHART.rPlanetStep;
                    const point = polarToCartesian(angle, radius);

                    // kis jelölő pötty
                    const dot = svgEl('circle');
                    dot.setAttribute('cx', point.x);
                    dot.setAttribute('cy', point.y);
                    dot.setAttribute('r', '2.5');
                    dot.setAttribute('fill', style.fg);
                    layer.appendChild(dot);

                    drawPlanetGlyph(planet.name, point.x, point.y, style);
                });
            }

            async function calculate() {
                errorBox.classList.add('hidden');
                const natalError = validateInputs(natalInputs);
                const transitError = validateInputs(transitInputs);
                if (natalError || transitError) {
                    errorBox.textContent = natalError || transitError;
                    errorBox.classList.remove('hidden');
                    return;
                }

                if (calcButton) {
                    calcButton.disabled = true;
                    calcButton.textContent = 'Számítás...';
                }

                try {
                    const payload = {
                        natal: {
                            datetime_utc: toUtcIso(
                                natalInputs.date.value,
                                natalInputs.time.value,
                                Number(natalInputs.offset.value)
                            ),
                            lat: Number(natalInputs.lat.value),
                            lon: Number(natalInputs.lon.value),
                        },
                        transit: {
                            datetime_utc: toUtcIso(
                                transitInputs.date.value,
                                transitInputs.time.value,
                                Number(transitInputs.offset.value)
                            ),
                            lat: Number(transitInputs.lat.value),
                            lon: Number(transitInputs.lon.value),
                        },
                        sidereal: zodiacModeSelect.value === 'sidereal',
                        ayanamsa: 'lahiri',
                        house_system: houseSystemSelect.value,
                    };

                    const response = await fetch(calcUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.error || 'Ismeretlen hiba');
                    }

                    if (showNatalCheckbox.checked) {
                        renderTable(natalTable, data.natal.planets);
                        renderAspectsTable(aspectsTable, data.natal.planets);
                    } else {
                        natalTable.innerHTML = '';
                        aspectsTable.innerHTML = '';
                    }
                    if (showTransitCheckbox.checked) {
                        renderTable(transitTable, data.transit.planets);
                    } else {
                        transitTable.innerHTML = '';
                    }

                    // Klasszikus kerék forgatás: ASC balra (9 óránál)
                    const rotationDeg = normalizeAngle(270 - data.natal.asc);

                    clearChart();
                    drawZodiacRing(rotationDeg);
                    // bolygó jelölő vonalak a zodiákus gyűrűben
                    drawPlanetMarkers(data.natal.planets, rotationDeg);

                    // Legbelső gyűrű: fok+dekád beosztás + bolygó jelölő vonalak
                    drawInnerRingTicks(rotationDeg);
                    drawInnerPlanetMarkers(data.natal.planets, rotationDeg);

                    // Házak: a natal cuspok adják az alapot
                    drawHousesFromCusps(data.natal.houses, rotationDeg);
                    drawHouseNumbersFromCusps(data.natal.houses, rotationDeg);

                    // Aspektusok + bolygók: egyelőre csak natal a keréken
                    if (showNatalCheckbox.checked) {
                        drawAspects(data.natal.planets, CHART.rAspect, 0.55, rotationDeg);
                        drawPlanets(data.natal.planets, rotationDeg);
                    }
                } catch (error) {
                    console.error('Horoscope calculate failed:', error);
                    const msg = error?.message || 'Ismeretlen hiba';
                    const details = error?.details ? `\n\n${error.details}` : '';
                    const python = error?.python ? `\n\nPython: ${error.python}` : '';
                    errorBox.textContent = `${msg}${python}${details}`;
                    errorBox.classList.remove('hidden');
                } finally {
                    if (calcButton) {
                        calcButton.disabled = false;
                        calcButton.textContent = 'Számítás';
                    }
                }
            }

            // nincs külön transit UI, a tranzit a natalt követi

            // nincs számítás gomb
            attachGeocode(natalInputs);
            // nincs külön transit UI
            setDefaultTimes();
            setDefaultCoords();
            updateModeHint();

            // Betöltéskor: alapból Mostani hely + Most idő
            applyLocation('current');
            calculate();

            // Kezdő állapot: klasszikus kerék alap (ASC-rotáció nélkül), hogy ne legyen üres az ábra.
            // A "Számítás" után a teljes kerék ASC szerint elforgatva jelenik meg.
            clearChart();
            drawZodiacRing(0);

            zodiacModeSelect.addEventListener('change', () => {
                updateModeHint();
                // ha már vannak eredmények, újraszámoltatjuk
                calculate();
            });

            houseSystemSelect.addEventListener('change', () => {
                updateModeHint();
                calculate();
            });
            // jelenleg nincs show/hide kapcsoló

            // kézi idő módosítás esetén is számoljunk újra
            [
                natalInputs.date,
                natalInputs.time,
                natalInputs.offset,
                transitInputs.date,
                transitInputs.time,
                transitInputs.offset,
            ].forEach((el) => {
                el.addEventListener('change', calculate);
            });

            // léptető gombok
            const shiftValueInputs = {
                minutes: document.getElementById('shiftMinutes'),
                hours: document.getElementById('shiftHours'),
                days: document.getElementById('shiftDays'),
                months: document.getElementById('shiftMonths'),
            };

            function readPositiveInt(inputEl, fallback = 1) {
                if (!inputEl) return fallback;
                const v = parseInt(String(inputEl.value), 10);
                return Number.isFinite(v) && v > 0 ? v : fallback;
            }

            const unitSeconds = {
                minutes: 60,
                hours: 3600,
                days: 86400,
            };

            document.querySelectorAll('[data-shift-unit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const unit = btn.getAttribute('data-shift-unit');
                    const dir = Number(btn.getAttribute('data-shift-dir'));
                    if (!unit || !Number.isFinite(dir) || (dir !== 1 && dir !== -1)) return;
                    const amount = readPositiveInt(shiftValueInputs[unit], 1);

                    if (unit === 'months') {
                        shiftNatalTimeByMonths(dir * amount);
                        return;
                    }
                    if (!(unit in unitSeconds)) return;
                    shiftNatalTimeBySeconds(dir * amount * unitSeconds[unit]);
                });
            });

            document.getElementById('setNow')?.addEventListener('click', () => {
                setDefaultTimes();
                applyLocation('current');
                calculate();
            });

            setBirthBtn?.addEventListener('click', () => {
                setBirthTimeFromUser();
                applyLocation('birth');
                calculate();
            });

            // Tab kezelés
            tabChart.addEventListener('click', () => setActiveTab('chart'));
            tabTable.addEventListener('click', () => setActiveTab('table'));
            tabAspects.addEventListener('click', () => setActiveTab('aspects'));
            setActiveTab('chart');
    </script>
</x-app-layout>