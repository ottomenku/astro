<nav class="mb-6 flex flex-wrap gap-x-4 gap-y-2 text-sm border-b border-gray-200 pb-3">
    <a href="{{ route('admin.index') }}"
       class="{{ request()->routeIs('admin.index') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin') }}
    </a>
    <a href="{{ route('admin.daily-horoscope.edit') }}"
       class="{{ request()->routeIs('admin.daily-horoscope.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_daily_horoscope') }}
    </a>
    <a href="{{ route('admin.chart-display.edit') }}"
       class="{{ request()->routeIs('admin.chart-display.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_chart_display') }}
    </a>
    <a href="{{ route('admin.templates.index') }}"
       class="{{ request()->routeIs('admin.templates.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_templates') }}
    </a>
    <a href="{{ route('admin.page-visits.logs') }}"
       class="{{ request()->routeIs('admin.page-visits.logs') || request()->routeIs('admin.page-visits.settings.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_page_visits') }}
    </a>
    <a href="{{ route('admin.page-visits.summary') }}"
       class="{{ request()->routeIs('admin.page-visits.summary') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_page_visits_summary') }}
    </a>
    <a href="{{ route('admin.visitors.index') }}"
       class="{{ request()->routeIs('admin.visitors.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_visitors') }}
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="{{ request()->routeIs('admin.users.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_users') }}
    </a>
    <a href="{{ route('admin.conversations.index') }}"
       class="{{ request()->routeIs('admin.conversations.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_conversations') }}
    </a>
    <a href="{{ route('admin.astrology-entries.index') }}"
       class="{{ request()->routeIs('admin.astrology-entries.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        {{ __('app.admin_astrology_entries') }}
    </a>
</nav>
