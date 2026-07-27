<x-app-layout>
    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 sm:p-8">
                @include('partials.app-icon-toolbar')
                @include('partials.admin-icon-toolbar')
                @include('admin.partials.nav')

                <header class="mb-6">
                    <h1 class="text-lg font-semibold text-gray-900">{{ __('horoscope.chart_display_admin_title') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('horoscope.chart_display_admin_intro') }}</p>
                </header>

                @if (session('status'))
                    <div class="mb-6 p-4 rounded bg-green-50 text-green-800 border border-green-200 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="post" action="{{ route('admin.chart-display.update') }}" class="space-y-6">
                    @csrf
                    @method('put')

                    @include('partials.chart-display-settings-fields', [
                        'chartDisplay' => $chartDisplay,
                        'hint' => __('horoscope.chart_display_admin_hint'),
                    ])

                    <div class="flex items-center gap-4 border-t pt-6">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
