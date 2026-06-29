<x-app-layout>
    <div class="hidden" id="modeHint" aria-hidden="true"></div>

    <div class="hidden" aria-hidden="true">
        <input type="hidden" id="natalQuery" value="{{ auth()->user()->current_place_label ?? '' }}">
        <input type="hidden" id="natalOffset" value="{{ auth()->user()->current_tz_offset ?? 2 }}">
        <input type="hidden" id="natalLat" value="{{ auth()->user()->current_lat ?? '47.4979' }}">
        <input type="hidden" id="natalLon" value="{{ auth()->user()->current_lon ?? '19.0402' }}">
        <div id="natalResults"></div>
    </div>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="relative flex flex-wrap gap-2 border-b pb-3 items-center" x-data="{ menuOpen: false }">
                        <button type="button" class="px-3 py-2 rounded border border-gray-300" data-tab="chart" id="tabChart">{{ __('horoscope.chart_tab') }}</button>
                        <button type="button" class="px-3 py-2 rounded border border-gray-300" data-tab="tables" id="tabTables">{{ __('horoscope.tables_tab') }}</button>
                        @include('partials.locale-select')
                        <button
                            type="button"
                            class="px-3 py-2 rounded border border-gray-300 inline-flex items-center justify-center"
                            @click="menuOpen = !menuOpen"
                            :aria-expanded="menuOpen"
                            aria-label="{{ __('app.menu') }}"
                        >
                            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <path :class="{'hidden': menuOpen, 'inline-flex': !menuOpen}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !menuOpen, 'inline-flex': menuOpen}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        @include('partials.horoscope-nav-menu')
                    </div>

                    <div class="hidden mt-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 whitespace-pre-wrap" id="errorBox"></div>

                    <div class="mt-4" id="panelChart">
                        <div class="max-w-xl mx-auto flex items-center justify-between gap-3 mb-3">
                            <h3 class="font-semibold">{{ __('horoscope.chart_heading') }}</h3>
                            <button
                                type="button"
                                id="chartSettingsToggle"
                                class="px-2.5 py-2 rounded border border-gray-300 inline-flex items-center justify-center text-gray-600 hover:bg-gray-50"
                                title="{{ __('horoscope.settings') }}"
                                aria-label="{{ __('horoscope.settings') }}"
                                aria-expanded="false"
                                aria-controls="chartSettingsPanel"
                            >
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>

                        <div id="chartSettingsPanel" class="hidden max-w-xl mx-auto mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50/80 space-y-4">
                            <p class="text-xs text-gray-500">{{ __('horoscope.settings_session_hint') }}</p>

                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="houseSystem">{{ __('horoscope.house_system') }}</label>
                                <select id="houseSystem" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="whole_sign" @selected((auth()->user()->house_system ?? 'placidus') === 'whole_sign')">Whole Sign</option>
                                    <option value="placidus" @selected((auth()->user()->house_system ?? 'placidus') === 'placidus')">Placidus</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="zodiacMode">{{ __('app.horoscope_type') }}</label>
                                <select id="zodiacMode" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="tropical" @selected((auth()->user()->zodiac_mode ?? 'tropical') === 'tropical')">{{ __('horoscope.zodiac_tropical') }}</option>
                                    <option value="sidereal" @selected((auth()->user()->zodiac_mode ?? 'tropical') === 'sidereal')">{{ __('horoscope.zodiac_sidereal') }}</option>
                                </select>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <button type="button" id="chartSettingsReset" class="text-sm text-gray-600 underline hover:text-gray-900">
                                    {{ __('horoscope.settings_reset_profile') }}
                                </button>
                                <a href="{{ route('profile.horoscope.edit') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                    {{ __('horoscope.settings_profile_link') }} →
                                </a>
                            </div>
                        </div>

                        <div class="max-w-xl mx-auto" id="chartShell">
                            <svg class="w-full h-auto" viewBox="0 0 400 400" role="img" aria-label="{{ __('horoscope.chart_aria') }}" id="chartSvg"></svg>
                            <div id="selectionBox" class="mt-3 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded px-3 py-2 hidden"></div>
                        </div>

                        <div class="mt-4 max-w-xl mx-auto space-y-2" id="horoscopeChat">
                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    id="horoscopeChatSend"
                                    class="px-4 py-2 rounded-md bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ __('horoscope.send') }}
                                </button>
                            </div>

                            <label class="sr-only" for="horoscopeChatQuestion">{{ __('horoscope.question') }}</label>
                            <textarea
                                id="horoscopeChatQuestion"
                                rows="1"
                                class="horoscope-chat-field block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                placeholder="{{ __('horoscope.question_placeholder') }}"
                                autocomplete="off"
                            ></textarea>

                            <label class="sr-only" for="horoscopeChatAnswer">{{ __('horoscope.answer') }}</label>
                            <textarea
                                id="horoscopeChatAnswer"
                                rows="1"
                                readonly
                                class="horoscope-chat-field horoscope-chat-answer block w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-50"
                                placeholder=""
                            ></textarea>

                            <div class="hidden text-sm text-red-600" id="horoscopeChatError"></div>
                        </div>

                        <!-- A hely automatikusan töltődik a Most / Születési idő gombok alapján -->

                        <!-- Vezérlők: a kért sorrendben -->
                        <div class="mt-6 max-w-xl mx-auto space-y-4">
                            <details class="border border-gray-200 rounded-lg bg-gray-50/50">
                                <summary class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-700 select-none list-none [&::-webkit-details-marker]:hidden">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="text-gray-400 text-xs details-chevron" aria-hidden="true">▼</span>
                                        {{ __('horoscope.date_time_stepping') }}
                                    </span>
                                </summary>

                                <div class="px-4 pb-4 space-y-4 border-t border-gray-200 pt-4">
                                    <div>
                                        <div class="block text-sm font-medium text-gray-700">{{ __('horoscope.date_time') }}</div>
                                        <div class="grid grid-cols-2 gap-2 mt-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500" for="natalDate">{{ __('horoscope.date') }}</label>
                                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="date" id="natalDate">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500" for="natalTime">{{ __('horoscope.time') }}</label>
                                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" type="time" id="natalTime" step="60">
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="block text-sm font-medium text-gray-700">{{ __('horoscope.time_stepping') }}</div>

                                        <div class="mt-2 space-y-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_minutes') }}</div>
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
                                                    <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_hours') }}</div>
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
                                                    <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_days') }}</div>
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
                                                    <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_months') }}</div>
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

                                        <div class="mt-1 text-xs text-gray-500">{{ __('horoscope.step_hint') }}</div>
                                    </div>
                                </div>
                            </details>

                            <div class="flex items-center gap-2 flex-wrap">
                                <button class="px-3 py-1.5 rounded border border-gray-300" type="button" id="setNow">{{ __('horoscope.now') }}</button>
                                <select id="birthChartSelect" class="px-3 py-1.5 rounded border border-gray-300 text-sm max-w-xs min-w-[10rem]" aria-label="{{ __('horoscope.birth_time') }}">
                                    <option value="">{{ __('horoscope.birth_chart_select') }}</option>
                                    @foreach ($birthCharts as $chart)
                                        @php $parts = $chart->localBirthParts(); @endphp
                                        <option value="{{ $chart->id }}" @selected($chart->is_default)>
                                            {{ $chart->name }} — {{ $parts['date'] }} {{ $parts['time'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 hidden" id="panelTables">
                        <h3 class="font-semibold mb-3">{{ __('horoscope.planet_positions') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm text-gray-500 uppercase mb-2">{{ __('horoscope.natal') }}</div>
                                <div id="natalTable"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 uppercase mb-2">{{ __('horoscope.transit') }}</div>
                                <div id="transitTable"></div>
                            </div>
                        </div>

                        <h3 class="font-semibold mb-3 mt-8">{{ __('horoscope.aspects_tab') }}</h3>
                        <div id="aspectsTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .horoscope-chat-field {
            min-height: 2.5rem;
            max-height: 400px;
            overflow-y: auto;
            resize: none;
            field-sizing: content;
        }

        details summary .details-chevron {
            display: inline-block;
            transition: transform 0.15s ease;
        }

        details[open] summary .details-chevron {
            transform: rotate(180deg);
        }
    </style>

    <script>
            const horoscopeI18n = @json(__('horoscope.js'));

            function tr(key, params = {}) {
                let text = horoscopeI18n[key] ?? key;
                if (typeof text !== 'string') {
                    return key;
                }
                for (const [name, value] of Object.entries(params)) {
                    text = text.replace(`:${name}`, value);
                }
                return text;
            }

            function planetLabel(name) {
                return horoscopeI18n.planets?.[name] || name;
            }

            const signNames = horoscopeI18n.signs;

            // Relatív URL-ek: így mindegy, hogy localhost vagy 127.0.0.1 alatt nyitod meg az oldalt,
            // a fetch mindig ugyanarra az originre megy (nem lesz CORS / "Failed to fetch").
            const geocodeUrl = '{{ route('horoscope.geocode', [], false) }}';
            const calcUrl = '{{ route('horoscope.calculate', [], false) }}';
            const horoscopeChatUrl = '{{ route('horoscope.chat', [], false) }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let lastHoroscopeData = null;

            const natalInputs = {
                date: document.getElementById('natalDate'),
                time: document.getElementById('natalTime'),
                query: document.getElementById('natalQuery'),
                results: document.getElementById('natalResults'),
                lat: document.getElementById('natalLat'),
                lon: document.getElementById('natalLon'),
                offset: document.getElementById('natalOffset'),
            };

            const birthChartSelect = document.getElementById('birthChartSelect');
            const setNowBtn = document.getElementById('setNow');

            const presetBtnOn = 'px-3 py-1.5 rounded bg-gray-900 text-white';
            const presetBtnOff = 'px-3 py-1.5 rounded border border-gray-300';
            const birthSelectOn = 'px-3 py-1.5 rounded border-2 border-gray-900 text-sm max-w-xs min-w-[10rem] bg-gray-50';
            const birthSelectOff = 'px-3 py-1.5 rounded border border-gray-300 text-sm max-w-xs min-w-[10rem]';

            function updatePresetButtons(mode) {
                if (setNowBtn) {
                    setNowBtn.className = mode === 'current' ? presetBtnOn : presetBtnOff;
                }
                if (birthChartSelect) {
                    birthChartSelect.className = mode === 'birth' ? birthSelectOn : birthSelectOff;
                }
            }

            const transitInputs = {
                date: document.getElementById('natalDate'),
                time: document.getElementById('natalTime'),
                query: document.getElementById('natalQuery'),
                results: document.getElementById('natalResults'),
                lat: document.getElementById('natalLat'),
                lon: document.getElementById('natalLon'),
                offset: document.getElementById('natalOffset'),
            };

            const errorBox = document.getElementById('errorBox');

            const BIRTH_CHARTS = @json($birthChartsJson);

            const USER_LOC = {
                current: {
                    label: @json(auth()->user()->current_place_label),
                    lat: @json(auth()->user()->current_lat),
                    lon: @json(auth()->user()->current_lon),
                    offset: @json(auth()->user()->current_tz_offset),
                },
            };

            function getBirthChartById(id) {
                if (id === null || id === undefined || id === '') return null;
                return BIRTH_CHARTS.find((chart) => String(chart.id) === String(id)) ?? null;
            }

            function utcMsToLocalInputs(utcMs, offsetHours) {
                const localMs = utcMs + offsetHours * 60 * 60 * 1000;
                const dt = new Date(localMs);

                const pad = (v) => String(v).padStart(2, '0');
                const date = `${dt.getUTCFullYear()}-${pad(dt.getUTCMonth() + 1)}-${pad(dt.getUTCDate())}`;
                const time = `${pad(dt.getUTCHours())}:${pad(dt.getUTCMinutes())}`;
                return { date, time };
            }

            function applyLocation(mode) {
                const src = USER_LOC[mode] || USER_LOC.current;
                if (src.label) natalInputs.query.value = src.label;
                if (src.lat !== null && src.lat !== undefined && src.lat !== '') {
                    const lat = Number(src.lat);
                    if (Number.isFinite(lat)) natalInputs.lat.value = lat.toFixed(4);
                }
                if (src.lon !== null && src.lon !== undefined && src.lon !== '') {
                    const lon = Number(src.lon);
                    if (Number.isFinite(lon)) natalInputs.lon.value = lon.toFixed(4);
                }
                if (src.offset !== null && src.offset !== undefined && src.offset !== '') {
                    natalInputs.offset.value = src.offset;
                }

                transitInputs.query.value = natalInputs.query.value;
                transitInputs.lat.value = natalInputs.lat.value;
                transitInputs.lon.value = natalInputs.lon.value;
                transitInputs.offset.value = natalInputs.offset.value;
            }

            function setNowTime() {
                const offset = Number(natalInputs.offset.value);
                const local = utcMsToLocalInputs(Date.now(), Number.isFinite(offset) ? offset : 0);
                natalInputs.date.value = local.date;
                natalInputs.time.value = local.time;
                transitInputs.date.value = local.date;
                transitInputs.time.value = local.time;
            }

            function applyLocationFromChart(chart) {
                if (chart.label) natalInputs.query.value = chart.label;
                if (chart.lat !== null && chart.lat !== undefined && chart.lat !== '') {
                    const lat = Number(chart.lat);
                    if (Number.isFinite(lat)) natalInputs.lat.value = lat.toFixed(4);
                }
                if (chart.lon !== null && chart.lon !== undefined && chart.lon !== '') {
                    const lon = Number(chart.lon);
                    if (Number.isFinite(lon)) natalInputs.lon.value = lon.toFixed(4);
                }
                if (chart.offset !== null && chart.offset !== undefined && chart.offset !== '') {
                    natalInputs.offset.value = chart.offset;
                }

                transitInputs.query.value = natalInputs.query.value;
                transitInputs.lat.value = natalInputs.lat.value;
                transitInputs.lon.value = natalInputs.lon.value;
                transitInputs.offset.value = natalInputs.offset.value;
            }

            function applyBirthChartData(chart) {
                if (!chart?.datetime_utc) {
                    return false;
                }

                const offset = Number(chart.offset ?? natalInputs.offset.value ?? 0);
                if (!Number.isFinite(offset)) {
                    return false;
                }

                applyLocationFromChart(chart);
                natalInputs.offset.value = String(offset);

                const utcMs = Date.parse(String(chart.datetime_utc));
                if (!Number.isFinite(utcMs)) {
                    return false;
                }

                const local = utcMsToLocalInputs(utcMs, offset);
                natalInputs.date.value = local.date;
                natalInputs.time.value = local.time;
                transitInputs.date.value = local.date;
                transitInputs.time.value = local.time;
                return true;
            }

            function applyPreset(mode, chartId = null) {
                errorBox.classList.add('hidden');

                if (mode === 'birth') {
                    const chart = getBirthChartById(chartId ?? birthChartSelect?.value);
                    if (!applyBirthChartData(chart)) {
                        errorBox.textContent = tr('err_birth_missing');
                        errorBox.classList.remove('hidden');
                        return false;
                    }
                } else {
                    applyLocation('current');
                    setNowTime();
                }

                setDefaultCoords();
                updatePresetButtons(mode);
                return true;
            }

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
            const tabTables = document.getElementById('tabTables');
            const panelChart = document.getElementById('panelChart');
            const panelTables = document.getElementById('panelTables');
            const chartSettingsToggle = document.getElementById('chartSettingsToggle');
            const chartSettingsPanel = document.getElementById('chartSettingsPanel');
            const chartSettingsReset = document.getElementById('chartSettingsReset');
            const modeHint = document.getElementById('modeHint');
            const selectionBox = document.getElementById('selectionBox');

            const PROFILE_CHART_DEFAULTS = {
                house_system: @json(auth()->user()->house_system ?? 'placidus'),
                zodiac_mode: @json(auth()->user()->zodiac_mode ?? 'tropical'),
            };

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
            const signNamesHu = signNames;

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
                        ? `${tr('mode_sidereal')} · ${house} · ${tr('mode_natal')}`
                        : `${tr('mode_tropical')} · ${house} · ${tr('mode_natal')}`;
            }

            function setDefaultTimes() {
                setNowTime();
            }

            let calculateSeq = 0;

            function setActiveTab(name) {
                const btnOn = 'px-3 py-2 rounded bg-indigo-600 text-white';
                const btnOff = 'px-3 py-2 rounded border border-gray-300';

                tabChart.className = name === 'chart' ? btnOn : btnOff;
                tabTables.className = name === 'tables' ? btnOn : btnOff;

                panelChart.classList.toggle('hidden', name !== 'chart');
                panelTables.classList.toggle('hidden', name !== 'tables');
            }

            function resetChartSettingsToProfile() {
                if (!houseSystemSelect || !zodiacModeSelect) return;
                houseSystemSelect.value = PROFILE_CHART_DEFAULTS.house_system;
                zodiacModeSelect.value = PROFILE_CHART_DEFAULTS.zodiac_mode;
                updateModeHint();
                calculate();
            }

            chartSettingsToggle?.addEventListener('click', () => {
                const open = chartSettingsPanel?.classList.toggle('hidden') === false;
                chartSettingsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                chartSettingsToggle.classList.toggle('bg-gray-900', open);
                chartSettingsToggle.classList.toggle('text-white', open);
                chartSettingsToggle.classList.toggle('border-gray-900', open);
            });

            chartSettingsReset?.addEventListener('click', resetChartSettingsToProfile);

            [houseSystemSelect, zodiacModeSelect].forEach((el) => {
                el?.addEventListener('change', () => {
                    updateModeHint();
                    calculate();
                });
            });
            function renderAspectsTable(target, planets) {
                const aspects = calcAspects(planets)
                    .slice()
                    .sort((a, b) => a.def.angle - b.def.angle || a.orb - b.orb);

                if (!aspects.length) {
                    target.innerHTML = `<div class="text-sm text-gray-500">${tr('no_aspects')}</div>`;
                    return;
                }

                const rows = aspects
                    .map(
                        ({ p1, p2, def }) => `<tr>
                            <td class="py-2 pr-4">${planetLabel(p1.name)}</td>
                            <td class="py-2 pr-4 font-semibold" style="color:${def.color}">${def.mark}</td>
                            <td class="py-2">${planetLabel(p2.name)}</td>
                        </tr>`
                    )
                    .join('');

                target.innerHTML = `<div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2 pr-4">${tr('planet')}</th>
                                <th class="py-2 pr-4">${tr('mark')}</th>
                                <th class="py-2">${tr('planet')}</th>
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
                    return tr('err_date_time');
                }
                if (inputs.lat.value === '' || inputs.lon.value === '') {
                    return tr('err_coordinates');
                }
                if (inputs.offset.value === '') {
                    return tr('err_timezone');
                }
                return '';
            }

            function renderTable(target, planets) {
                const rows = planets
                    .slice()
                    .sort((a, b) => planetsOrder.indexOf(a.name) - planetsOrder.indexOf(b.name))
                    .map(
                        (planet) => `<tr>
                            <td>${planetLabel(planet.name)}</td>
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
                                    <th class="py-2 pr-4">${tr('planet')}</th>
                                    <th class="py-2 pr-4">${tr('sign')}</th>
                                    <th class="py-2 pr-4">${tr('house')}</th>
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
                    label.addEventListener('click', () => showSelection(tr('sign_selection', { name: signNames[i] })));
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
                g.addEventListener('click', () => showSelection(tr('planet_selection', { name: planetLabel(name) })));
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
                const seq = ++calculateSeq;
                errorBox.classList.add('hidden');
                const natalError = validateInputs(natalInputs);
                const transitError = validateInputs(transitInputs);
                if (natalError || transitError) {
                    if (seq !== calculateSeq) return;
                    errorBox.textContent = natalError || transitError;
                    errorBox.classList.remove('hidden');
                    return;
                }

                if (calcButton) {
                    calcButton.disabled = true;
                    calcButton.textContent = tr('calculating');
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
                    if (seq !== calculateSeq) return;
                    if (!response.ok) {
                        const err = new Error(data.error || 'Ismeretlen hiba');
                        err.details = data.details || '';
                        throw err;
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

                    lastHoroscopeData = data;
                } catch (error) {
                    if (seq !== calculateSeq) return;
                    console.error('Horoscope calculate failed:', error);
                    const msg = error?.message || 'Ismeretlen hiba';
                    const details = error?.details ? `\n\n${error.details}` : '';
                    const python = error?.python ? `\n\nPython: ${error.python}` : '';
                    errorBox.textContent = `${msg}${python}${details}`;
                    errorBox.classList.remove('hidden');
                } finally {
                    if (seq !== calculateSeq) return;
                    if (calcButton) {
                        calcButton.disabled = false;
                        calcButton.textContent = tr('calculate');
                    }
                }
            }

            // nincs külön transit UI, a tranzit a natalt követi

            // nincs számítás gomb
            updateModeHint();

            (async function bootHoroscope() {
                const defaultChart = BIRTH_CHARTS.find((chart) => chart.is_default) ?? BIRTH_CHARTS[0] ?? null;

                if (defaultChart) {
                    if (birthChartSelect) {
                        birthChartSelect.value = String(defaultChart.id);
                    }
                    if (applyPreset('birth', defaultChart.id)) {
                        await calculate();
                    }
                    return;
                }

                if (applyPreset('current')) {
                    await calculate();
                }
            })();

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

            setNowBtn?.addEventListener('click', () => {
                if (birthChartSelect) {
                    birthChartSelect.value = '';
                }
                if (applyPreset('current')) {
                    calculate();
                }
            });

            birthChartSelect?.addEventListener('change', () => {
                if (!birthChartSelect.value) {
                    return;
                }
                if (applyPreset('birth', birthChartSelect.value)) {
                    calculate();
                }
            });

            // Tab kezelés
            tabChart.addEventListener('click', () => setActiveTab('chart'));
            tabTables.addEventListener('click', () => setActiveTab('tables'));
            setActiveTab('chart');

            // Egyszerűsített chat az ábra alatt
            const horoscopeChatQuestion = document.getElementById('horoscopeChatQuestion');
            const horoscopeChatAnswer = document.getElementById('horoscopeChatAnswer');
            const horoscopeChatSend = document.getElementById('horoscopeChatSend');
            const horoscopeChatError = document.getElementById('horoscopeChatError');
            let horoscopeChatBusy = false;

            function adjustHoroscopeChatFieldHeight(field) {
                if (!field) return;
                field.style.height = 'auto';
                const nextHeight = Math.min(field.scrollHeight, 400);
                field.style.height = `${Math.max(nextHeight, 40)}px`;
            }

            function adjustHoroscopeChatAnswerHeight() {
                adjustHoroscopeChatFieldHeight(horoscopeChatAnswer);
            }

            function adjustHoroscopeChatQuestionHeight() {
                adjustHoroscopeChatFieldHeight(horoscopeChatQuestion);
            }

            function setHoroscopeChatError(message) {
                if (!horoscopeChatError) return;
                if (!message) {
                    horoscopeChatError.textContent = '';
                    horoscopeChatError.classList.add('hidden');
                    return;
                }
                horoscopeChatError.textContent = message;
                horoscopeChatError.classList.remove('hidden');
            }

            async function sendHoroscopeChatQuestion() {
                if (!horoscopeChatQuestion || !horoscopeChatAnswer || horoscopeChatBusy) return;

                const prompt = horoscopeChatQuestion.value.trim();
                if (!prompt) return;

                horoscopeChatBusy = true;
                horoscopeChatQuestion.disabled = true;
                if (horoscopeChatSend) horoscopeChatSend.disabled = true;
                setHoroscopeChatError('');
                horoscopeChatAnswer.value = tr('chat_answer_pending');
                adjustHoroscopeChatAnswerHeight();

                try {
                    const payload = {
                        prompt,
                        chart: lastHoroscopeData,
                    };

                    const response = await fetch(horoscopeChatUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const contentType = (response.headers.get('content-type') || '').toLowerCase();
                    let data = {};
                    if (contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        throw new Error(`Nem JSON válasz (${response.status}). Kezdet: ${text.slice(0, 160)}`);
                    }

                    if (!response.ok) {
                        throw new Error(readHoroscopeChatError(data, tr('chat_failed')));
                    }

                    horoscopeChatAnswer.value = data.response || '';
                } catch (error) {
                    horoscopeChatAnswer.value = '';
                    setHoroscopeChatError(error?.message || tr('chat_unknown_error'));
                } finally {
                    horoscopeChatBusy = false;
                    horoscopeChatQuestion.disabled = false;
                    if (horoscopeChatSend) horoscopeChatSend.disabled = false;
                    adjustHoroscopeChatAnswerHeight();
                    adjustHoroscopeChatQuestionHeight();
                    horoscopeChatQuestion.focus();
                }
            }

            function readHoroscopeChatError(data, fallback) {
                if (data?.error) return data.error;
                if (data?.message) return data.message;
                if (data?.errors) {
                    const first = Object.values(data.errors).flat()[0];
                    if (first) return first;
                }
                return fallback;
            }

            horoscopeChatSend?.addEventListener('click', sendHoroscopeChatQuestion);

            horoscopeChatQuestion?.addEventListener('input', adjustHoroscopeChatQuestionHeight);

            horoscopeChatQuestion?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendHoroscopeChatQuestion();
                }
            });

            adjustHoroscopeChatQuestionHeight();
            adjustHoroscopeChatAnswerHeight();
    </script>
</x-app-layout>