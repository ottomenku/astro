<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('partials.app-icon-toolbar')

                <div class="max-w-xl mt-6">
                    <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">{{ __('app.birth_chart_edit') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $birthChart->name }}</p>
                    </header>

                    @include('profile.partials.birth-chart-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
