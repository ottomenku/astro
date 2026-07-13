@php
    $iconBtn = 'px-3 py-2 rounded border border-gray-300 inline-flex items-center justify-center text-gray-700 hover:bg-gray-50';
    $iconBtnActive = 'px-3 py-2 rounded border border-indigo-400 bg-indigo-50 inline-flex items-center justify-center text-indigo-700';
@endphp

<div class="relative flex flex-wrap gap-2 pb-2 items-center" x-data="{ menuOpen: false }">
    <a href="{{ route('home') }}"
       class="{{ request()->routeIs('home') ? $iconBtnActive : $iconBtn }}"
       title="{{ __('app.homepage') }}"
       aria-label="{{ __('app.homepage') }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 10.5 12 3l9 7.5" />
            <path d="M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
        </svg>
    </a>

    <a href="{{ route('horoscope.index') }}"
       class="{{ request()->routeIs('horoscope.*') ? $iconBtnActive : $iconBtn }}"
       title="{{ __('app.horoscope') }}"
       aria-label="{{ __('app.horoscope') }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <circle cx="12" cy="12" r="3" />
            <path d="M12 3v18M3 12h18" />
        </svg>
    </a>

    <a href="{{ route('chat.index') }}"
       class="{{ request()->routeIs('chat.*') ? $iconBtnActive : $iconBtn }}"
       title="{{ __('app.chat') }}"
       aria-label="{{ __('app.chat') }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
        </svg>
    </a>

    @if (Auth::user()?->is_admin)
        <a href="{{ route('admin.visitors.index') }}"
           class="{{ request()->routeIs('admin.*') ? $iconBtnActive : $iconBtn }}"
           title="{{ __('app.admin') }}"
           aria-label="{{ __('app.admin') }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 15v2" />
                <path d="M12 3v2" />
                <path d="M4.93 4.93l1.41 1.41" />
                <path d="M17.66 17.66l1.41 1.41" />
                <path d="M3 12h2" />
                <path d="M19 12h2" />
                <path d="M4.93 19.07l1.41-1.41" />
                <path d="M17.66 6.34l1.41-1.41" />
                <circle cx="12" cy="12" r="4" />
            </svg>
        </a>
    @endif

    <div class="ml-auto flex flex-wrap gap-2 items-center">
        @include('partials.locale-select')
        <button type="button"
                class="{{ $iconBtn }}"
                @click="menuOpen = !menuOpen"
                :aria-expanded="menuOpen"
                aria-label="{{ __('app.menu') }}">
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <path :class="{'hidden': menuOpen, 'inline-flex': !menuOpen}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !menuOpen, 'inline-flex': menuOpen}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        @include('partials.horoscope-nav-menu')
    </div>
</div>
