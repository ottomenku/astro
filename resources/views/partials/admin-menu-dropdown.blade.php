<div
    x-show="adminOpen"
    x-cloak
    @click.outside="adminOpen = false"
    class="absolute right-0 top-full mt-1 z-50 w-64 rounded-md bg-white border border-gray-200 shadow-lg py-1 text-sm"
>
    <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
        {{ __('app.admin') }}
    </div>

    <a href="{{ route('admin.daily-horoscope.edit') }}"
       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.daily-horoscope.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.admin_daily_horoscope') }}
    </a>
    <a href="{{ route('admin.page-visits.logs') }}"
       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.page-visits.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.admin_page_visits') }}
    </a>
    <a href="{{ route('admin.page-visits.summary') }}"
       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.page-visits.summary') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.admin_page_visits_summary') }}
    </a>
    <a href="{{ route('admin.visitors.index') }}"
       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.visitors.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.admin_visitors') }}
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.users.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.admin_users') }}
    </a>
    <a href="{{ route('admin.conversations.index') }}"
       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.conversations.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.admin_conversations') }}
    </a>
    <a href="{{ route('admin.astrology-entries.index') }}"
       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.astrology-entries.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.admin_astrology_entries') }}
    </a>
</div>
