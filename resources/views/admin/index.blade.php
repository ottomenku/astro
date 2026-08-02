<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @include('admin.partials.header')

                <h1 class="text-xl font-semibold text-gray-900 mb-6">{{ __('app.admin') }}</h1>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('admin.daily-horoscope.edit') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_daily_horoscope') }}</span>
                    </a>
                    <a href="{{ route('admin.chart-display.edit') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_chart_display') }}</span>
                    </a>
                    <a href="{{ route('admin.templates.index') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_templates') }}</span>
                    </a>
                    <a href="{{ route('admin.page-visits.logs') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_page_visits') }}</span>
                    </a>
                    <a href="{{ route('admin.page-visits.summary') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_page_visits_summary') }}</span>
                    </a>
                    <a href="{{ route('admin.visitors.index') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_visitors') }}</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_users') }}</span>
                    </a>
                    <a href="{{ route('admin.conversations.index') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_conversations') }}</span>
                    </a>
                    <a href="{{ route('admin.astrology-entries.index') }}" class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="block font-medium text-gray-900">{{ __('app.admin_astrology_entries') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
