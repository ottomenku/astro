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
                        <h2 class="text-lg font-medium text-gray-900">{{ __('app.profile_birth_charts') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ __('app.profile_birth_charts_hint') }}</p>
                    </header>

                    @if (session('status') === 'birth-chart-created')
                        <p class="mb-4 text-sm text-green-600">{{ __('app.birth_chart_created') }}</p>
                    @elseif (session('status') === 'birth-chart-updated')
                        <p class="mb-4 text-sm text-green-600">{{ __('Saved.') }}</p>
                    @elseif (session('status') === 'birth-chart-deleted')
                        <p class="mb-4 text-sm text-green-600">{{ __('app.birth_chart_deleted') }}</p>
                    @endif

                    <div class="mb-6">
                        <a href="{{ route('profile.birth-charts.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            {{ __('app.birth_chart_add') }}
                        </a>
                    </div>

                    @if ($birthCharts->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('app.birth_charts_empty') }}</p>
                    @else
                        <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                            @foreach ($birthCharts as $chart)
                                <li class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $chart->name }}
                                            @if ($chart->is_default)
                                                <span class="ml-2 text-xs font-normal text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ __('app.birth_chart_default_badge') }}</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            {{ $chart->localBirthParts()['date'] }} {{ $chart->localBirthParts()['time'] }}
                                            · {{ $chart->gender === 'female' ? __('app.gender_female') : __('app.gender_male') }}
                                            @if ($chart->birth_place_label)
                                                · {{ $chart->birth_place_label }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm shrink-0">
                                        <a href="{{ route('profile.birth-charts.edit', $chart) }}" class="text-indigo-600 hover:underline">{{ __('app.edit') }}</a>
                                        <form method="post" action="{{ route('profile.birth-charts.destroy', $chart) }}" onsubmit="return confirm(@json(__('app.birth_chart_delete_confirm')))">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-red-600 hover:underline">{{ __('app.delete') }}</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
