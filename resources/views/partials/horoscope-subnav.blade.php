@php
    $mode = request()->query('mode') === 'dual' ? 'dual' : 'single';
    $view = request()->query('view', 'chart');
    $period = \App\Support\HoroscopePeriod::normalize(request()->query('period'));
    $btnOn = 'px-3 py-2 rounded bg-indigo-600 text-white inline-flex items-center justify-center text-sm font-medium';
    $btnOff = 'px-3 py-2 rounded border border-gray-300 inline-flex items-center justify-center text-sm font-medium text-gray-700';
    $promptBtn = 'p-2 rounded border border-gray-300 inline-flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:text-indigo-600';
    $chartQuery = $mode === 'dual' ? ['mode' => 'dual', 'view' => 'chart'] : ['view' => 'chart'];
    $tablesQuery = $mode === 'dual' ? ['mode' => 'dual', 'view' => 'tables'] : ['view' => 'tables'];
    $dailyQuery = $mode === 'dual'
        ? ['mode' => 'dual', 'view' => 'daily', 'period' => $period]
        : ['view' => 'daily', 'period' => $period];
    $explanationQuery = $mode === 'dual'
        ? ['mode' => 'dual', 'view' => 'explanation']
        : ['view' => 'explanation'];
    $messagePromptContext = $mode === 'dual' ? 'partnership_message' : 'personal_message';
    $explanationPromptContext = $mode === 'dual' ? 'partnership_explanation' : 'personal_explanation';
@endphp

<div class="flex flex-wrap gap-2 mt-2">
    <a href="{{ route('horoscope.index', $chartQuery) }}"
       class="{{ $view === 'chart' ? $btnOn : $btnOff }}"
       title="{{ __('horoscope.chart_tab') }}"
       aria-label="{{ __('horoscope.chart_tab') }}">
        @if ($mode === 'dual')
            @include('partials.icons.dual-horoscope-wheels')
        @else
            @include('partials.icons.horoscope-wheel')
        @endif
    </a>
    <a href="{{ route('horoscope.index', $tablesQuery) }}"
       class="{{ $view === 'tables' ? $btnOn : $btnOff }}"
       title="{{ __('horoscope.tables_tab') }}"
       aria-label="{{ __('horoscope.tables_tab') }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <path d="M3 9h18M3 15h18M9 3v18M15 3v18" />
        </svg>
    </a>
    <div id="horoscopeStarsMessageNav" class="flex flex-wrap gap-2 items-center">
        <div class="inline-flex items-center gap-1">
            <a href="{{ route('horoscope.index', $dailyQuery) }}"
               id="horoscopeNavDaily"
               class="{{ $view === 'daily' ? $btnOn : $btnOff }}"
               title="{{ __('horoscope.stars_message_tab') }}"
               aria-label="{{ __('horoscope.stars_message_tab') }}">
                {{ __('horoscope.stars_message_tab') }}
            </a>
            @if (\App\Support\SiteMode::canUseAdminUi())
                <button type="button"
                        class="{{ $promptBtn }} horoscope-prompt-admin-btn"
                        data-prompt-context="{{ $messagePromptContext }}"
                        title="{{ __('horoscope.prompt_settings_title') }}"
                        aria-label="{{ __('horoscope.prompt_settings_title') }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
            @endif
        </div>
        <div class="inline-flex items-center gap-1">
            <a href="{{ route('horoscope.index', $explanationQuery) }}"
               id="horoscopeNavExplanation"
               class="{{ $view === 'explanation' ? $btnOn : $btnOff }}"
               title="{{ __('horoscope.explanation_tab') }}"
               aria-label="{{ __('horoscope.explanation_tab') }}">
                {{ __('horoscope.explanation_tab') }}
            </a>
            @if (\App\Support\SiteMode::canUseAdminUi())
                <button type="button"
                        class="{{ $promptBtn }} horoscope-prompt-admin-btn"
                        id="horoscopePromptAdminExplanationBtn"
                        data-prompt-context="{{ $explanationPromptContext }}"
                        title="{{ __('horoscope.prompt_settings_title') }}"
                        aria-label="{{ __('horoscope.prompt_settings_title') }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
