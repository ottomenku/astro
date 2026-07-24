<div class="rounded-xl border border-amber-200 bg-amber-50/40 p-5 sm:p-6 space-y-6 relative pt-16" id="horoscopeDailyPanel">
    <div class="absolute top-4 right-4 left-4 sm:left-auto sm:pl-0 flex flex-wrap items-center justify-end gap-2 max-w-full">
        <div class="flex flex-wrap justify-end gap-1.5" id="horoscopePeriodTabs">
            @foreach ([\App\Support\HoroscopePeriod::DAILY, \App\Support\HoroscopePeriod::WEEKLY, \App\Support\HoroscopePeriod::MONTHLY] as $periodOption)
                <button type="button"
                        data-period="{{ $periodOption }}"
                        class="horoscope-period-btn {{ $periodOption === \App\Support\HoroscopePeriod::DAILY ? 'horoscope-period-btn-active' : '' }}">
                    {{ __('daily.period_'.$periodOption) }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="text-center space-y-2">
        <p class="hidden text-xs font-semibold uppercase tracking-wide text-amber-700" id="horoscopeDailyBadge"></p>
        <p class="text-xs text-amber-800/80" id="horoscopeDailyMeta"></p>
    </div>

    <div class="text-center text-sm text-gray-600" id="horoscopeDailyLoading">{{ __('horoscope.daily_loading') }}</div>
    <div class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" id="horoscopeDailyError"></div>

    <div class="hidden space-y-6" id="horoscopeDailyContent">
        <div class="text-center">
            <p class="text-lg sm:text-xl text-amber-950 font-medium leading-relaxed" id="horoscopeDailyMotto"></p>
        </div>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-2" id="horoscopeDailySummaryTitle">{{ __('daily.summary_title_daily') }}</h2>
            <p class="text-gray-800 leading-relaxed whitespace-pre-line text-sm" id="horoscopeDailySummary"></p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <article>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-2">{{ __('daily.section_health') }}</h3>
                <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line" id="horoscopeDailyHealth"></p>
            </article>
            <article>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-2">{{ __('daily.section_money') }}</h3>
                <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line" id="horoscopeDailyMoney"></p>
            </article>
            <article>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-2">{{ __('daily.section_relationships') }}</h3>
                <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line" id="horoscopeDailyRelationships"></p>
            </article>
            <article>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-2">{{ __('daily.section_work') }}</h3>
                <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line" id="horoscopeDailyWork"></p>
            </article>
        </div>
    </div>

    <style>
        .horoscope-period-btn {
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid rgba(217, 119, 6, 0.35);
            color: rgb(146 64 14);
            font-size: 0.75rem;
            line-height: 1.25rem;
            background: rgba(255, 255, 255, 0.65);
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .horoscope-period-btn:hover {
            background: rgba(251, 191, 36, 0.18);
        }

        .horoscope-period-btn-active {
            background: rgb(245 158 11);
            color: rgb(255 251 235);
            border-color: rgb(245 158 11);
            font-weight: 600;
        }
    </style>
</div>
