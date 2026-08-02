            const auroraPositionsTable = document.getElementById('auroraPositionsTable');
            const auroraAspectsTable = document.getElementById('auroraAspectsTable');
            let auroraHoroscopeData = null;
            let auroraAspectStore = { entries: [], isCross: false };
            let auroraActivePositionTab = 'natal';
            let auroraActiveAspectFilter = 'all';

            const AURORA_PLANET_BG = {
                Sun: 'rgb(254, 173, 39)',
                Moon: 'rgb(2, 25, 71)',
                Mercury: 'rgb(23, 53, 38)',
                Venus: 'rgb(34, 26, 80)',
                Mars: 'rgb(86, 25, 25)',
                Jupiter: 'rgb(53, 22, 23)',
                Saturn: 'rgb(38, 16, 62)',
                Uranus: 'rgb(12, 13, 48)',
                Neptune: 'rgb(12, 13, 48)',
                Pluto: 'rgb(12, 13, 48)',
                'True Node': 'rgb(8, 24, 42)',
                'South Node': 'rgb(8, 24, 42)',
                ASC: 'rgb(8, 24, 42)',
                MC: 'rgb(8, 24, 42)',
                IC: 'rgb(8, 24, 42)',
                DSC: 'rgb(8, 24, 42)',
            };

            function auroraGlyphBadge(glyph, background) {
                return `<span class="aurora-glyph-badge" style="--badge-bg:${background}">${glyph}</span>`;
            }

            function auroraAspectBadge(def) {
                return `<span class="aurora-aspect-mark" style="color:${def.color}">${def.mark}</span>`;
            }

            const AURORA_HARMONIOUS_ASPECTS = new Set(['sextile', 'trine']);
            const AURORA_TENSE_ASPECTS = new Set(['square', 'opposition', 'semi_square', 'quincunx', 'semi_sextile']);

            function signSymbolForName(signName) {
                const idx = signMeta.findIndex((sign) => sign.name === signName);
                const symbol = idx >= 0 ? signSymbols[idx] : '';
                return symbol ? auroraSignText(symbol) : '';
            }

            function auroraHouseBadge(house) {
                return `<span class="aurora-house-badge">${house}</span>`;
            }

            function auroraPlanetCell(body) {
                const glyph = planetSymbols[body.name] || '•';
                const bg = AURORA_PLANET_BG[body.name] || 'rgb(8, 24, 42)';
                return `<span class="aurora-planet-cell">${auroraGlyphBadge(glyph, bg)}<span>${planetDisplayName(body)}</span></span>`;
            }

            function auroraSignCell(body) {
                return `<span class="aurora-sign-cell"><span class="aurora-sign-glyph">${signSymbolForName(body.sign)}</span><span>${body.sign_degree.toFixed(2)}°</span></span>`;
            }

            function auroraAspectPlanetIcon(body) {
                const glyph = planetSymbols[body.name] || '•';
                const bg = AURORA_PLANET_BG[body.name] || 'rgb(8, 24, 42)';
                return auroraGlyphBadge(glyph, bg);
            }

            function auroraAspectSignIcon(body) {
                return `<span class="aurora-sign-glyph">${signSymbolForName(body.sign)}</span>`;
            }

            function auroraAspectSideParts(body) {
                return `${auroraAspectPlanetIcon(body)}${auroraAspectSignIcon(body)}${auroraHouseBadge(body.house)}`;
            }

            function auroraAspectSideLeft(body) {
                return `<div class="aurora-aspect-side aurora-aspect-side-left">${auroraAspectSideParts(body)}</div>`;
            }

            function auroraAspectSideRight(body) {
                return `<div class="aurora-aspect-side aurora-aspect-side-right">${auroraAspectSideParts(body)}</div>`;
            }

            function auroraAspectSide(body) {
                return `<div class="aurora-aspect-side">${auroraAspectSideParts(body)}</div>`;
            }

            function renderAuroraPositionTable(chart) {
                if (!auroraPositionsTable || !chart) {
                    if (auroraPositionsTable) {
                        auroraPositionsTable.innerHTML = `<div class="aurora-table-empty">${tr('no_aspects')}</div>`;
                    }
                    return;
                }

                const bodies = enrichChartBodies(chart)
                    .slice()
                    .sort((a, b) => {
                        const order = [...planetsOrder, 'South Node', 'ASC', 'MC', 'IC', 'DSC'];
                        return order.indexOf(a.name) - order.indexOf(b.name);
                    });

                const rows = bodies.map((body, index) => `<tr class="aurora-data-row" data-body-row data-body-index="${index}">
                        <td>${auroraPlanetCell(body)}</td>
                        <td>${auroraSignCell(body)}</td>
                        <td class="aurora-house-col">${auroraHouseBadge(body.house)}</td>
                    </tr>`).join('');

                auroraPositionsTable.innerHTML = `<div class="aurora-table-wrap">
                    <table class="aurora-data-table">
                        <thead>
                            <tr>
                                <th>${tr('object')}</th>
                                <th>${tr('sign')}</th>
                                <th>${tr('house')}</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;

                auroraPositionsTable.__bodyRows = bodies;
                auroraPositionsTable.querySelectorAll('[data-body-row]').forEach((row) => {
                    row.addEventListener('click', () => {
                        const body = auroraPositionsTable.__bodyRows[Number(row.dataset.bodyIndex)];
                        if (body) {
                            openChartBodyInfo(body);
                        }
                    });
                });
            }

            function aspectMatchesAuroraFilter(entry, filter) {
                if (filter === 'all') {
                    return true;
                }
                if (entry.type !== 'aspect') {
                    return filter === 'all';
                }
                const name = entry.aspect.def.name;
                if (filter === 'harmonious') {
                    return AURORA_HARMONIOUS_ASPECTS.has(name);
                }
                if (filter === 'tense') {
                    return AURORA_TENSE_ASPECTS.has(name);
                }
                return true;
            }

            function paintAuroraAspectTable() {
                if (!auroraAspectsTable) {
                    return;
                }

                const entries = auroraAspectStore.entries.filter((entry) => aspectMatchesAuroraFilter(entry, auroraActiveAspectFilter));
                if (!entries.length) {
                    auroraAspectsTable.innerHTML = `<div class="aurora-table-empty">${tr('no_aspects')}</div>`;
                    auroraAspectsTable.__complexAspectStore = null;
                    return;
                }

                let htmlRows = '';
                entries.forEach((entry, index) => {
                    if (entry.type === 'aspect') {
                        const { p1, p2, def } = entry.aspect;
                        htmlRows += `<button type="button" class="aurora-aspect-row" data-aspect-table-row data-row-index="${index}">
                            <div class="aurora-aspect-row-inner">
                                ${auroraAspectSideLeft(p1)}
                                <div class="aurora-aspect-center">${auroraAspectBadge(def)}</div>
                                ${auroraAspectSideRight(p2)}
                            </div>
                            <span class="aurora-aspect-chevron" aria-hidden="true">›</span>
                        </button>`;
                        return;
                    }

                    const { star, body, orb } = entry;
                    htmlRows += `<button type="button" class="aurora-aspect-row" data-aspect-table-row data-row-index="${index}">
                        <div class="aurora-aspect-row-inner">
                            <div class="aurora-aspect-side aurora-aspect-side-left">${auroraGlyphBadge('★', 'rgb(8, 24, 42)')}</div>
                            <div class="aurora-aspect-center">${auroraAspectBadge({ color: 'rgb(254, 173, 39)', mark: '☌' })}</div>
                            <div class="aurora-aspect-side aurora-aspect-side-right">${auroraAspectSideParts(body)}</div>
                        </div>
                        <span class="aurora-aspect-chevron" aria-hidden="true">›</span>
                    </button>`;
                });

                auroraAspectsTable.innerHTML = `<div class="aurora-aspect-list">${htmlRows}</div>`;
                auroraAspectsTable.__complexAspectStore = {
                    entries,
                    isCross: auroraAspectStore.isCross,
                };
                bindComplexAspectTableClicks(auroraAspectsTable);
            }

            function renderAuroraAspectStore(chartA, chartB = null) {
                const isCross = chartB !== null;
                const bodiesA = enrichChartBodies(chartA);
                const bodiesB = isCross ? enrichChartBodies(chartB) : null;
                const aspects = (isCross ? calcCrossAspects(bodiesA, bodiesB) : calcAspects(bodiesA))
                    .slice()
                    .sort((a, b) => a.def.angle - b.def.angle || a.orb - b.orb);
                const starRows = isCross ? [] : calcFixedStarConjunctionRows(chartA);
                const entries = [];

                aspects.forEach(({ p1, p2, def, orb }) => {
                    entries.push({ type: 'aspect', aspect: { p1, p2, def, orb } });
                });

                starRows.forEach(({ star, body, orb }) => {
                    entries.push({ type: 'star', star, body, orb });
                });

                auroraAspectStore = { entries, isCross };
                paintAuroraAspectTable();
            }

            function refreshAuroraTablesFromCache() {
                if (!auroraHoroscopeData) {
                    return;
                }

                const chart = auroraActivePositionTab === 'transit'
                    ? auroraHoroscopeData.transit
                    : auroraHoroscopeData.natal;

                renderAuroraPositionTable(chart);

                if (auroraHoroscopeData.natal && auroraHoroscopeData.transit && HOROSCOPE_MODE === 'dual') {
                    renderAuroraAspectStore(auroraHoroscopeData.natal, auroraHoroscopeData.transit);
                    return;
                }

                renderAuroraAspectStore(auroraHoroscopeData.natal);
            }

            function syncAuroraHoroscopePanels() {
                const onChart = activeViewName === 'chart';
                document.getElementById('auroraPositionsSection')?.classList.toggle('hidden', !onChart);
                document.getElementById('auroraAspectsSection')?.classList.toggle('hidden', !onChart);
            }

            document.querySelectorAll('[data-pos-tab]').forEach((button) => {
                button.addEventListener('click', () => {
                    auroraActivePositionTab = button.dataset.posTab || 'natal';
                    document.querySelectorAll('[data-pos-tab]').forEach((el) => {
                        el.classList.toggle('is-active', el === button);
                    });
                    refreshAuroraTablesFromCache();
                });
            });

            document.querySelectorAll('[data-aspect-filter]').forEach((button) => {
                button.addEventListener('click', () => {
                    auroraActiveAspectFilter = button.dataset.aspectFilter || 'all';
                    document.querySelectorAll('[data-aspect-filter]').forEach((el) => {
                        el.classList.toggle('is-active', el === button);
                    });
                    paintAuroraAspectTable();
                });
            });

            const originalRenderTable = renderTable;
            renderTable = function (target, chart) {
                originalRenderTable(target, chart);
                if (AURORA_LAYOUT) {
                    refreshAuroraTablesFromCache();
                }
            };

            const originalRenderComplexAspectTable = renderComplexAspectTable;
            renderComplexAspectTable = function (target, chartA, chartB = null) {
                originalRenderComplexAspectTable(target, chartA, chartB);
                if (AURORA_LAYOUT) {
                    renderAuroraAspectStore(chartA, chartB);
                }
            };

            const originalSyncHoroscopeViewPanels = syncHoroscopeViewPanels;
            syncHoroscopeViewPanels = function () {
                originalSyncHoroscopeViewPanels();
                if (AURORA_LAYOUT) {
                    syncAuroraHoroscopePanels();
                }
            };
