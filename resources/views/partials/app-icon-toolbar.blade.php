@php
    $iconBtn = 'px-3 py-2 rounded border border-gray-300 inline-flex items-center justify-center text-gray-700 hover:bg-gray-50';
    $iconBtnActive = 'px-3 py-2 rounded border border-indigo-400 bg-indigo-50 inline-flex items-center justify-center text-indigo-700';
    $horoscopeMode = request()->routeIs('horoscope.index')
        ? (request()->query('mode') === 'dual' ? 'dual' : 'single')
        : null;
@endphp

<div class="relative flex flex-wrap gap-2 pb-2 items-center" x-data="{ menuOpen: false, adminOpen: false }">
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
       class="{{ $horoscopeMode === 'single' ? $iconBtnActive : $iconBtn }}"
       title="{{ __('app.horoscope') }}"
       aria-label="{{ __('app.horoscope') }}">
        @include('partials.icons.horoscope-wheel')
    </a>

    <a href="{{ route('horoscope.index', ['mode' => 'dual']) }}"
       class="{{ $horoscopeMode === 'dual' ? $iconBtnActive : $iconBtn }}"
       title="{{ __('app.dual_horoscope') }}"
       aria-label="{{ __('app.dual_horoscope') }}">
        @include('partials.icons.dual-horoscope-wheels')
    </a>

    <a href="{{ auth()->check() ? route('chat.index') : route('login') }}"
       class="{{ request()->routeIs('chat.*') ? $iconBtnActive : $iconBtn }}"
       title="{{ __('app.chat') }}"
       aria-label="{{ __('app.chat') }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
        </svg>
    </a>

    <div class="ml-auto flex flex-wrap gap-2 items-center">
        @include('partials.locale-select')

        @if (Auth::user()?->is_admin)
            <div class="relative">
                <button type="button"
                        class="{{ request()->routeIs('admin.*') ? $iconBtnActive : $iconBtn }} min-w-[2.5rem] font-bold text-lg leading-none"
                        @click="adminOpen = !adminOpen; menuOpen = false"
                        :aria-expanded="adminOpen"
                        title="{{ __('app.admin') }}"
                        aria-label="{{ __('app.admin') }}">
                    A
                </button>
                @include('partials.admin-menu-dropdown')
            </div>
        @endif

        <button type="button"
                class="{{ $iconBtn }}"
                @click="menuOpen = !menuOpen; adminOpen = false"
                :aria-expanded="menuOpen"
                aria-label="{{ __('app.menu') }}">
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <path :class="{'hidden': menuOpen, 'inline-flex': !menuOpen}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !menuOpen, 'inline-flex': menuOpen}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        @include('partials.app-user-menu')
    </div>
</div>
