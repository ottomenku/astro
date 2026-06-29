<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.nav')

                    <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">{{ __('app.birth_chart_add') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ __('app.profile_birth_charts_hint') }}</p>
                    </header>

                    @include('profile.partials.birth-chart-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
