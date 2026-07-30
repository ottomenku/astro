<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('public.page_title') }} — Astro MOtto</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .pm-shell {
            min-height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
        }

        .pm-chip-btn {
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            line-height: 1.25rem;
            white-space: nowrap;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .pm-top-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.75rem;
        }

        .pm-setup-hint {
            flex: 1;
            min-width: 0;
            font-size: 0.75rem;
            line-height: 1.45;
            color: rgb(255 255 255 / 0.85);
        }

        .pm-more-options-btn {
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: rgb(226 232 240);
            background: rgba(255, 255, 255, 0.08);
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .pm-more-options-btn:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.14);
        }

        .pm-more-options-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .pm-action-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.375rem;
        }

        .pm-period-btn {
            border: 1px solid rgba(129, 140, 248, 0.45);
            color: rgb(199 210 254);
            background: rgba(255, 255, 255, 0.06);
        }

        .pm-period-btn:hover {
            background: rgba(99, 102, 241, 0.2);
        }

        .pm-period-btn-active {
            background: rgb(99 102 241);
            color: #fff;
            border-color: rgb(99 102 241);
            font-weight: 600;
        }

        .pm-generate-btn {
            border: 1px solid rgb(99 102 241);
            color: #fff;
            background: rgb(79 70 229);
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .pm-generate-btn:hover {
            background: rgb(67 56 202);
        }

        .pm-text-field,
        .pm-select-field {
            background-color: rgba(255, 255, 255, 0.06);
            border-color: rgba(129, 140, 248, 0.45);
        }

        .pm-text-field {
            color: #fff;
        }

        .pm-select-field {
            color: #fff;
        }

        .pm-select-field option {
            background-color: rgba(255, 255, 255, 0.14);
            color: #374151;
        }

        #generationPanel label,
        #generationPanel span.block {
            color: #fff;
        }

        #generationPanel,
        #personalMessageGenerationForm {
            position: relative;
            z-index: 0;
        }
    </style>
</head>
<body class="text-white pm-shell">

<div class="max-w-2xl mx-auto px-5 py-6 sm:py-8 min-h-screen flex flex-col">
    <div class="pm-top-row">
        <p class="pm-setup-hint">{{ __('public.setup_hint') }}</p>
        <button type="button" id="openSimpleHamburgerBtn" class="pm-chip-btn pm-more-options-btn" disabled aria-disabled="true">
            {{ __('public.more_options_btn') }}
        </button>
    </div>

    <div class="flex flex-col gap-4">
        <div class="pm-action-row" id="personalMessagePeriodTabs">
            @foreach ([\App\Support\HoroscopePeriod::DAILY, \App\Support\HoroscopePeriod::WEEKLY, \App\Support\HoroscopePeriod::MONTHLY] as $periodOption)
                <button type="button"
                        data-period="{{ $periodOption }}"
                        class="pm-chip-btn pm-period-btn {{ $period === $periodOption ? 'pm-period-btn-active' : '' }}">
                    {{ __('daily.period_'.$periodOption) }}
                </button>
            @endforeach
            <button type="button" id="generatePersonalMessageBtn" class="pm-chip-btn pm-generate-btn">
                {{ __('public.generate_btn') }}
            </button>
        </div>

        <div id="personalMessageLoading" class="hidden text-sm text-white/80">{{ __('public.loading') }}</div>
        <div id="personalMessageError" class="hidden w-full rounded-lg border border-red-400/40 bg-red-950/40 p-4 text-sm text-red-200"></div>

        <div id="personalMessageContent" class="hidden w-full rounded-xl border border-indigo-400/30 bg-white/5 backdrop-blur p-5 sm:p-6 space-y-5 text-indigo-50">
            <p class="text-center text-lg font-medium text-white" id="personalMessageMotto"></p>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-white/80 mb-2" id="personalMessageSummaryTitle"></h2>
                <p class="text-sm leading-relaxed whitespace-pre-line text-white/90" id="personalMessageSummary"></p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 text-sm">
                <article><h3 class="text-xs uppercase text-white/80 mb-1">{{ __('daily.section_health') }}</h3><p id="personalMessageHealth" class="whitespace-pre-line text-white/90"></p></article>
                <article><h3 class="text-xs uppercase text-white/80 mb-1">{{ __('daily.section_money') }}</h3><p id="personalMessageMoney" class="whitespace-pre-line text-white/90"></p></article>
                <article><h3 class="text-xs uppercase text-white/80 mb-1">{{ __('daily.section_relationships') }}</h3><p id="personalMessageRelationships" class="whitespace-pre-line text-white/90"></p></article>
                <article><h3 class="text-xs uppercase text-white/80 mb-1">{{ __('daily.section_work') }}</h3><p id="personalMessageWork" class="whitespace-pre-line text-white/90"></p></article>
            </div>
            <p class="hidden text-xs text-center text-white/60" id="personalMessageTokens"></p>
        </div>

        <div id="generationPanel" class="w-full">
            @if ($hasBirthChart)
                <div class="mb-4">
                    <label for="messageBirthChartSelect" class="block text-sm font-medium text-white mb-1">{{ __('public.select_birth_chart') }}</label>
                    <select id="messageBirthChartSelect" class="pm-select-field w-full rounded-md text-sm px-3 py-2">
                        @foreach ($birthCharts as $chart)
                            <option value="{{ $chart->id }}" @selected($defaultBirthChartId === $chart->id)>{{ $chart->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @include('partials.horoscope-generation-form', [
                'type' => 'message',
                'variant' => 'personal',
                'formId' => 'personalMessageGenerationForm',
                'focusInputId' => 'personalMessageUserFocus',
                'detailSelectId' => 'personalMessageDetailLevel',
                'topicsGroupId' => 'personalMessageTopics',
                'hideSubmit' => true,
            ])
        </div>
    </div>
</div>

@include('partials.simple-hamburger-menu')
@include('partials.birth-chart-required-modal')

<script>
    (function () {
        const HAS_BIRTH_CHART = @json($hasBirthChart);
        let selectedPeriod = @json($period);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const dailyUrl = @json(route('horoscope.daily-message', [], false));
        const i18n = @json(__('horoscope.js'));

        const generateBtn = document.getElementById('generatePersonalMessageBtn');
        const birthChartSelect = document.getElementById('messageBirthChartSelect');
        const loadingEl = document.getElementById('personalMessageLoading');
        const errorEl = document.getElementById('personalMessageError');
        const contentEl = document.getElementById('personalMessageContent');
        const periodButtons = document.querySelectorAll('#personalMessagePeriodTabs .pm-period-btn');

        periodButtons.forEach((button) => {
            button.addEventListener('click', () => {
                selectedPeriod = button.dataset.period || 'daily';
                periodButtons.forEach((btn) => {
                    btn.classList.toggle('pm-period-btn-active', btn === button);
                });
            });
        });

        function tr(key, params = {}) {
            let text = i18n[key] ?? key;
            for (const [name, value] of Object.entries(params)) {
                text = text.replace(`:${name}`, value);
            }
            return text;
        }

        function collectTopics() {
            return Array.from(document.querySelectorAll('.horoscope-topic-checkbox[data-topic-group="personalMessageTopics"]:checked')).map((el) => el.value);
        }

        generateBtn?.addEventListener('click', async () => {
            if (!HAS_BIRTH_CHART) {
                window.showBirthChartRequiredModal?.();
                return;
            }

            const chartId = Number(birthChartSelect?.value);
            if (!chartId) {
                window.showBirthChartRequiredModal?.();
                return;
            }

            loadingEl?.classList.remove('hidden');
            errorEl?.classList.add('hidden');
            contentEl?.classList.add('hidden');

            try {
                const response = await fetch(dailyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        mode: 'single',
                        birth_chart_id: chartId,
                        period: selectedPeriod,
                        user_focus: document.getElementById('personalMessageUserFocus')?.value?.trim() || '',
                        detail_level: document.getElementById('personalMessageDetailLevel')?.value || 'normal',
                        topics: collectTopics(),
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.error || @json(__('public.error')));
                }

                document.getElementById('personalMessageMotto').textContent = data.motto ? `„${data.motto}”` : '';
                document.getElementById('personalMessageSummaryTitle').textContent = data.summary_title || tr('summary_title_daily');
                document.getElementById('personalMessageSummary').textContent = data.summary || '';
                document.getElementById('personalMessageHealth').textContent = data.health || '';
                document.getElementById('personalMessageMoney').textContent = data.money || '';
                document.getElementById('personalMessageRelationships').textContent = data.relationships || '';
                document.getElementById('personalMessageWork').textContent = data.work || '';

                const tokensEl = document.getElementById('personalMessageTokens');
                if (tokensEl && data.tokens_used) {
                    tokensEl.textContent = tr('generation_tokens_used', { count: String(data.tokens_used) });
                    tokensEl.classList.remove('hidden');
                }

                contentEl?.classList.remove('hidden');
                contentEl?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } catch (error) {
                if (errorEl) {
                    errorEl.textContent = error?.message || @json(__('public.error'));
                    errorEl.classList.remove('hidden');
                }
            } finally {
                loadingEl?.classList.add('hidden');
            }
        });
    })();
</script>

</body>
</html>
