<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Horoszkóp</h2>
            <div class="text-sm text-gray-500" id="modeHint">Trópusi · Whole Sign · Natal</div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-5">
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="p-6 space-y-4">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700" for="zodiacMode">Zodiákus</label>
                                <select class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" id="zodiacMode">
                                    <option value="tropical" selected>Trópusi (0° Kos = tavaszpont)</option>
                                    <option value="sidereal">Sziderikus (Lahiri)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="houseSystem">Házrendszer</label>
                                    <select class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" id="houseSystem">
                                        <option value="whole_sign" selected>Whole Sign</option>
                                        <option value="placidus">Placidus</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="block text-sm font-medium text-gray-700">Megjelenítés</div>
                                    <label class="mt-2 flex items-center gap-2 text-sm">
                                        <input class="rounded border-gray-300" type="checkbox" id="showNatal" checked>
                                        <span>Natal</span>
                                    </label>
                                    <label class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                        <input class="rounded border-gray-300" type="checkbox" id="showTransit" disabled>
                                        <span>Tranzit (később)</span>
                                    </label>
                                </div>
                            </div>
                            <h2 class="text-xs font-semibold uppercase text-gray-500">Natal adatok</h2>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700" for="natalDate">Dátum</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="date" id="natalDate">
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700" for="natalTime">Idő</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="time" id="natalTime" step="60">
                            </div>

                            <div class="mb-3">
                                <div class="block text-sm font-medium text-gray-700">Idő léptetése</div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button class="px-3 py-1.5 rounded border border-gray-300" type="button" data-step="-60">-1 perc</button>
                                    <button class="px-3 py-1.5 rounded border border-gray-300" type="button" data-step="60">+1 perc</button>
                                    <button class="px-3 py-1.5 rounded border border-gray-300" type="button" data-step="-3600">-1 óra</button>
                                    <button class="px-3 py-1.5 rounded border border-gray-300" type="button" data-step="3600">+1 óra</button>
                                    <button class="px-3 py-1.5 rounded border border-gray-300" type="button" data-step="-86400">-1 nap</button>
                                    <button class="px-3 py-1.5 rounded border border-gray-300" type="button" data-step="86400">+1 nap</button>
                                    <button class="px-3 py-1.5 rounded bg-gray-900 text-white" type="button" id="setNow">Most</button>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">A léptetés a Natal dátum/időt módosítja (offset figyelembevételével) és azonnal újraszámol.</div>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700" for="natalQuery">Hely (település / cím)</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="text" id="natalQuery" placeholder="pl. Budapest">
                                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="natalResults"></div>
                            </div>
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
                            <div class="mt-2 mb-4">
                                <label class="block text-sm font-medium text-gray-700" for="natalOffset">Időzóna offset (óra, pl. +2)</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="number" step="0.25" id="natalOffset" value="2">
                            </div>

                            <h2 class="text-xs font-semibold uppercase text-gray-500">Tranzit adatok</h2>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700" for="transitDate">Dátum</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="date" id="transitDate">
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700" for="transitTime">Idő</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="time" id="transitTime" step="60">
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700" for="transitQuery">Hely (település / cím)</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="text" id="transitQuery" placeholder="pl. Budapest">
                                <div class="mt-2 hidden border border-gray-200 rounded-md divide-y" id="transitResults"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="transitLat">Szélesség (lat)</label>
                                    <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="number" step="0.0001" id="transitLat" value="47.4979">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" for="transitLon">Hosszúság (lon)</label>
                                    <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="number" step="0.0001" id="transitLon" value="19.0402">
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="block text-sm font-medium text-gray-700" for="transitOffset">Időzóna offset (óra, pl. +2)</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="number" step="0.25" id="transitOffset" value="2">
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 mt-4">
                                <button class="px-3 py-2 rounded border border-gray-300" type="button" id="copyNatal">Natal → tranzit</button>
                                <button class="px-3 py-2 rounded bg-indigo-600 text-white" type="button" id="calcButton">Számítás</button>
                            </div>
                            <div class="hidden mt-3 p-3 rounded border border-red-200 bg-red-50 text-red-800" id="errorBox"></div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold mb-3">Horoszkóp kerék</h3>
                            <div class="max-w-xl mx-auto" id="chartShell">
                                <svg class="w-full h-auto" viewBox="0 0 400 400" role="img" aria-label="Horoszkóp kerék" id="chartSvg"></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="p-6">
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

            const transitInputs = {
                date: document.getElementById('transitDate'),
                time: document.getElementById('transitTime'),
                query: document.getElementById('transitQuery'),
                results: document.getElementById('transitResults'),
                lat: document.getElementById('transitLat'),
                lon: document.getElementById('transitLon'),
                offset: document.getElementById('transitOffset'),
            };

            const errorBox = document.getElementById('errorBox');
            const calcButton = document.getElementById('calcButton');
            const copyButton = document.getElementById('copyNatal');
            const chartSvg = document.getElementById('chartSvg');
            const natalTable = document.getElementById('natalTable');
            const transitTable = document.getElementById('transitTable');
            const zodiacModeSelect = document.getElementById('zodiacMode');
            const houseSystemSelect = document.getElementById('houseSystem');
            const showNatalCheckbox = document.getElementById('showNatal');
            const showTransitCheckbox = document.getElementById('showTransit');
            const modeHint = document.getElementById('modeHint');

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

            const aspectDefs = [
                { name: 'conjunction', angle: 0, color: '#6c757d' },
                { name: 'sextile', angle: 60, color: '#0dcaf0' },
                { name: 'square', angle: 90, color: '#dc3545' },
                { name: 'trine', angle: 120, color: '#198754' },
                { name: 'opposition', angle: 180, color: '#fd7e14' },
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

                // Külső kerék / fokbeosztás
                rOuter: 180,
                rTicksOuter: 180,
                rTicksMinor: 175,
                rTicksMid: 172,
                rTicksMajor: 168,

                // Zodiákus gyűrű
                rZodiacOuter: 168,
                rZodiacInner: 140,

                // Ház gyűrű
                rHouseOuter: 140,
                rHouseInner: 105,

                // Belső kör és aspektusok
                rAspect: 95,
                rInner: 90,

                // Bolygók
                rPlanetBase: 125,
                rPlanetStep: 10,
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

            function clearChart() {
                chartSvg.innerHTML = '';

                // alap gyűrűk
                const outer = svgEl('circle');
                outer.setAttribute('cx', CHART.cx);
                outer.setAttribute('cy', CHART.cy);
                outer.setAttribute('r', CHART.rOuter);
                outer.setAttribute('fill', '#fff');
                outer.setAttribute('stroke', '#212529');
                outer.setAttribute('stroke-width', '2');
                chartSvg.appendChild(outer);

                const zodiacOuter = svgEl('circle');
                zodiacOuter.setAttribute('cx', CHART.cx);
                zodiacOuter.setAttribute('cy', CHART.cy);
                zodiacOuter.setAttribute('r', CHART.rZodiacOuter);
                zodiacOuter.setAttribute('fill', 'none');
                zodiacOuter.setAttribute('stroke', '#343a40');
                zodiacOuter.setAttribute('stroke-width', '1');
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
            }

            function getLayer(name) {
                return chartSvg.querySelector(`g[data-layer="${name}"]`);
            }

            function drawDegreeTicks(rotationDeg) {
                const layer = getLayer('ticks');
                if (!layer) return;
                for (let deg = 0; deg < 360; deg++) {
                    const angle = normalizeAngle(deg + rotationDeg);
                    const isMajor = deg % 10 === 0;
                    const isMid = !isMajor && deg % 5 === 0;
                    const r1 = CHART.rTicksOuter;
                    const r2 = isMajor ? CHART.rTicksMajor : isMid ? CHART.rTicksMid : CHART.rTicksMinor;
                    const a = polarToCartesian(angle, r1);
                    const b = polarToCartesian(angle, r2);
                    const line = svgEl('line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    line.setAttribute('stroke', isMajor ? '#212529' : '#adb5bd');
                    line.setAttribute('stroke-width', isMajor ? '1.6' : isMid ? '1.2' : '0.9');
                    layer.appendChild(line);
                }
            }

            function drawZodiacRing(rotationDeg) {
                const layer = getLayer('zodiac');
                if (!layer) return;

                // 30° határok
                for (let i = 0; i < 12; i++) {
                    const angle = normalizeAngle(i * 30 + rotationDeg);
                    const a = polarToCartesian(angle, CHART.rZodiacInner);
                    const b = polarToCartesian(angle, CHART.rZodiacOuter);
                    const line = svgEl('line');
                    line.setAttribute('x1', a.x);
                    line.setAttribute('y1', a.y);
                    line.setAttribute('x2', b.x);
                    line.setAttribute('y2', b.y);
                    line.setAttribute('stroke', '#495057');
                    line.setAttribute('stroke-width', '1');
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
                    label.setAttribute('fill', '#0d6efd');
                    label.textContent = signSymbols[i];
                    layer.appendChild(label);
                }
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
                const layer = getLayer('houses');
                if (!layer) return;
                for (let i = 0; i < 12; i++) {
                    const mid = angleMid(cusps[i], cusps[(i + 1) % 12]);
                    const point = polarToCartesian(normalizeAngle(mid + rotationDeg), 154);
                    const label = svgEl('text');
                    label.setAttribute('x', point.x);
                    label.setAttribute('y', point.y);
                    label.setAttribute('text-anchor', 'middle');
                    label.setAttribute('dominant-baseline', 'middle');
                    label.setAttribute('font-size', '12');
                    label.setAttribute('fill', '#dc3545');
                    label.textContent = String(i + 1);
                    layer.appendChild(label);
                }
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
                    line.setAttribute('stroke-width', '1');
                    line.setAttribute('opacity', String(strokeOpacity));
                    layer.appendChild(line);
                });
            }

            // Egyszerű, vonalas planet-glyph-ek (SVG path). Nem tipográfiai jel, hanem rajzolt forma.
            // Lokális koordináta: kb. -12..+12, ezt skálázzuk.
            const planetGlyphPaths = {
                Sun: [
                    'M 0 -7 A 7 7 0 1 0 0 7 A 7 7 0 1 0 0 -7',
                    'M 0 -2 A 2 2 0 1 0 0 2 A 2 2 0 1 0 0 -2',
                ],
                Moon: ['M 4 -8 A 8 8 0 1 0 4 8 A 6 6 0 1 1 4 -8'],
                Mercury: [
                    'M 0 -11 C -4 -15 4 -15 0 -11',
                    'M 0 -5 A 5 5 0 1 0 0 5 A 5 5 0 1 0 0 -5',
                    'M 0 5 L 0 12',
                    'M -4 9 L 4 9',
                ],
                Venus: [
                    'M 0 -6 A 6 6 0 1 0 0 6 A 6 6 0 1 0 0 -6',
                    'M 0 6 L 0 13',
                    'M -4 10 L 4 10',
                ],
                Mars: [
                    'M 0 -6 A 6 6 0 1 0 0 6 A 6 6 0 1 0 0 -6',
                    'M 3 -3 L 12 -12',
                    'M 12 -12 L 12 -5',
                    'M 12 -12 L 5 -12',
                ],
                Jupiter: [
                    'M -2 -12 L -2 12',
                    'M -2 -2 C -2 -9 8 -9 8 -2 C 8 5 -2 5 -2 12',
                    'M -8 2 L 6 2',
                ],
                Saturn: [
                    'M -5 -12 L -5 12',
                    'M -5 -4 C -5 -9 6 -9 6 -4 C 6 1 -5 1 -5 6',
                    'M -10 4 L 10 4',
                ],
                Uranus: [
                    'M -8 -12 L -8 4',
                    'M 8 -12 L 8 4',
                    'M -8 -2 L 8 -2',
                    'M 0 4 A 3 3 0 1 0 0 10 A 3 3 0 1 0 0 4',
                    'M 0 10 L 0 14',
                    'M -4 14 L 4 14',
                ],
                Neptune: [
                    'M 0 -13 L 0 13',
                    'M 0 -13 C -10 -5 -10 3 0 3',
                    'M 0 -13 C 10 -5 10 3 0 3',
                    'M -8 13 L 8 13',
                ],
                Pluto: [
                    'M 0 -12 A 4 4 0 1 0 0 -4 A 4 4 0 1 0 0 -12',
                    'M 0 -8 L 0 14',
                    'M -6 6 L 6 6',
                    'M -8 -2 C -8 -10 8 -10 8 -2',
                ],
                'True Node': [
                    'M -8 -4 A 8 8 0 0 1 8 -4',
                    'M -8 4 A 8 8 0 0 0 8 4',
                    'M 0 -4 L 0 4',
                ],
            };

            function drawPlanetGlyph(name, x, y, size, stroke) {
                const layer = getLayer('planets');
                if (!layer) return;
                const paths = planetGlyphPaths[name];
                if (!paths) return;

                const g = svgEl('g');
                const scale = size / 28;
                g.setAttribute('transform', `translate(${x} ${y}) scale(${scale})`);

                // háttér “halo”, hogy olvasható legyen a vonal a keréken
                const halo = svgEl('circle');
                halo.setAttribute('cx', '0');
                halo.setAttribute('cy', '0');
                halo.setAttribute('r', '12');
                halo.setAttribute('fill', '#fff');
                halo.setAttribute('opacity', '0.9');
                g.appendChild(halo);

                paths.forEach((d) => {
                    const p = svgEl('path');
                    p.setAttribute('d', d);
                    p.setAttribute('fill', 'none');
                    p.setAttribute('stroke', stroke);
                    p.setAttribute('stroke-width', '2');
                    p.setAttribute('stroke-linecap', 'round');
                    p.setAttribute('stroke-linejoin', 'round');
                    g.appendChild(p);
                });

                layer.appendChild(g);
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
                    dot.setAttribute('fill', '#111');
                    layer.appendChild(dot);

                    drawPlanetGlyph(planet.name, point.x, point.y, 14, '#111');
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

                calcButton.disabled = true;
                calcButton.textContent = 'Számítás...';

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
                    } else {
                        natalTable.innerHTML = '';
                    }
                    if (showTransitCheckbox.checked) {
                        renderTable(transitTable, data.transit.planets);
                    } else {
                        transitTable.innerHTML = '';
                    }

                    // Klasszikus kerék forgatás: ASC balra (9 óránál)
                    const rotationDeg = normalizeAngle(270 - data.natal.asc);

                    clearChart();
                    drawDegreeTicks(rotationDeg);
                    drawZodiacRing(rotationDeg);

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
                    errorBox.textContent = error.message;
                    errorBox.classList.remove('hidden');
                } finally {
                    calcButton.disabled = false;
                    calcButton.textContent = 'Számítás';
                }
            }

            copyButton.addEventListener('click', () => {
                transitInputs.date.value = natalInputs.date.value;
                transitInputs.time.value = natalInputs.time.value;
                transitInputs.lat.value = natalInputs.lat.value;
                transitInputs.lon.value = natalInputs.lon.value;
                transitInputs.offset.value = natalInputs.offset.value;
                transitInputs.query.value = natalInputs.query.value;
            });

            calcButton.addEventListener('click', calculate);
            attachGeocode(natalInputs);
            attachGeocode(transitInputs);
            setDefaultTimes();
            setDefaultCoords();
            updateModeHint();

            // Betöltéskor azonnali számítás az aktuális időpontra
            calculate();

            // Kezdő állapot: klasszikus kerék alap (ASC-rotáció nélkül), hogy ne legyen üres az ábra.
            // A "Számítás" után a teljes kerék ASC szerint elforgatva jelenik meg.
            clearChart();
            drawDegreeTicks(0);
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
            showNatalCheckbox.addEventListener('change', calculate);
            showTransitCheckbox.addEventListener('change', calculate);

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
            document.querySelectorAll('[data-step]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const delta = Number(btn.getAttribute('data-step'));
                    if (!Number.isFinite(delta)) return;
                    shiftNatalTimeBySeconds(delta);
                });
            });

            document.getElementById('setNow').addEventListener('click', () => {
                setDefaultTimes();
                calculate();
            });
    </script>
</x-app-layout>