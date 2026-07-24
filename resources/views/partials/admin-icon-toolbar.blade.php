@php
    $iconBtn = 'px-3 py-2 rounded border border-gray-300 inline-flex items-center justify-center text-gray-700 hover:bg-gray-50';
    $iconBtnActive = 'px-3 py-2 rounded border border-indigo-400 bg-indigo-50 inline-flex items-center justify-center text-indigo-700';
@endphp

<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.page-visits.logs') }}"
       class="{{ request()->routeIs('admin.page-visits.*') ? $iconBtnActive : $iconBtn }}"
       title="Oldalmegtekintések"
       aria-label="Oldalmegtekintések">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 3v18h18" />
            <path d="M7 14l4-4 3 3 5-7" />
        </svg>
    </a>
    <a href="{{ route('admin.visitors.index') }}"
       class="{{ request()->routeIs('admin.visitors.*') ? $iconBtnActive : $iconBtn }}"
       title="Látogatók"
       aria-label="Látogatók">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="{{ request()->routeIs('admin.users.*') ? $iconBtnActive : $iconBtn }}"
       title="Felhasználók"
       aria-label="Felhasználók">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 00-3-3.87" />
            <path d="M16 3.13a4 4 0 010 7.75" />
        </svg>
    </a>
    <a href="{{ route('admin.conversations.index') }}"
       class="{{ request()->routeIs('admin.conversations.*') ? $iconBtnActive : $iconBtn }}"
       title="Konverzációk"
       aria-label="Konverzációk">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
        </svg>
    </a>
    <a href="{{ route('admin.astrology-entries.index') }}"
       class="{{ request()->routeIs('admin.astrology-entries.*') ? $iconBtnActive : $iconBtn }}"
       title="{{ __('app.admin_astrology_entries') }}"
       aria-label="{{ __('app.admin_astrology_entries') }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 16v-4" />
            <path d="M12 8h.01" />
        </svg>
    </a>
    <a href="{{ route('admin.daily-horoscope.edit') }}"
       class="{{ request()->routeIs('admin.daily-horoscope.*') ? $iconBtnActive : $iconBtn }}"
       title="Napi horoszkóp"
       aria-label="Napi horoszkóp">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
        </svg>
    </a>
</div>
