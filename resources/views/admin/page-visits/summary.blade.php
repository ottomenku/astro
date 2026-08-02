@php
    $weekRows = $report['week']['rows']->keyBy(fn ($row) => ($row->route_name ?? 'unknown').'|'.($row->page_label ?? ''));
    $monthRows = $report['month']['rows']->keyBy(fn ($row) => ($row->route_name ?? 'unknown').'|'.($row->page_label ?? ''));
    $keys = $weekRows->keys()->merge($monthRows->keys())->unique();
@endphp

<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @include('admin.partials.header')

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Látogatás összesítő</h1>
                        <p class="text-sm text-gray-500">Oldalankénti heti és havi statisztika.</p>
                    </div>
                    <a href="{{ route('admin.page-visits.logs') }}" class="text-sm text-indigo-700 underline">Részletes napló</a>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.page-visits.settings.update') }}" class="mb-6 p-4 rounded-lg border border-gray-200 bg-gray-50">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Napló megőrzése (nap)</label>
                            <x-text-input name="retention_days" type="number" min="1" max="365"
                                          :value="old('retention_days', $retentionDays)" class="w-28" />
                            <p class="text-xs text-gray-500 mt-1">A régebbi bejegyzések automatikusan törlődnek (naponta 03:15-kor).</p>
                        </div>
                        <x-primary-button>Mentés és tisztítás</x-primary-button>
                    </div>
                </form>

                <div class="grid gap-4 sm:grid-cols-2 mb-6">
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <h2 class="text-sm font-semibold text-indigo-900 mb-2">Aktuális hét ({{ $report['week']['label'] }})</h2>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-gray-600">Összes megtekintés</dt><dd class="font-semibold">{{ $report['week']['overview']['total'] }}</dd>
                            <dt class="text-gray-600">Egyedi IP</dt><dd class="font-semibold">{{ $report['week']['overview']['unique_ips'] }}</dd>
                            <dt class="text-gray-600">Ember</dt><dd>{{ $report['week']['overview']['humans'] }}</dd>
                            <dt class="text-gray-600">Robot</dt><dd>{{ $report['week']['overview']['bots'] }}</dd>
                        </dl>
                    </div>
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                        <h2 class="text-sm font-semibold text-emerald-900 mb-2">Aktuális hónap ({{ $report['month']['label'] }})</h2>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-gray-600">Összes megtekintés</dt><dd class="font-semibold">{{ $report['month']['overview']['total'] }}</dd>
                            <dt class="text-gray-600">Egyedi IP</dt><dd class="font-semibold">{{ $report['month']['overview']['unique_ips'] }}</dd>
                            <dt class="text-gray-600">Ember</dt><dd>{{ $report['month']['overview']['humans'] }}</dd>
                            <dt class="text-gray-600">Robot</dt><dd>{{ $report['month']['overview']['bots'] }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b text-gray-600">
                                <th class="py-2 pr-4" rowspan="2">Oldal</th>
                                <th class="py-2 px-2 text-center border-l" colspan="4">Heti</th>
                                <th class="py-2 px-2 text-center border-l" colspan="4">Havi</th>
                            </tr>
                            <tr class="text-left border-b text-xs text-gray-500">
                                <th class="py-2 px-2 text-right border-l">Találat</th>
                                <th class="py-2 px-2 text-right">IP</th>
                                <th class="py-2 px-2 text-right">Ember</th>
                                <th class="py-2 px-2 text-right">Robot</th>
                                <th class="py-2 px-2 text-right border-l">Találat</th>
                                <th class="py-2 px-2 text-right">IP</th>
                                <th class="py-2 px-2 text-right">Ember</th>
                                <th class="py-2 px-2 text-right">Robot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($keys as $key)
                                @php
                                    $week = $weekRows->get($key);
                                    $month = $monthRows->get($key);
                                    $label = $week->page_label ?? $month->page_label ?? 'Ismeretlen';
                                    $route = $week->route_name ?? $month->route_name;
                                @endphp
                                <tr class="border-b">
                                    <td class="py-2 pr-4">
                                        <div class="font-medium">{{ $label }}</div>
                                        @if ($route)
                                            <div class="text-xs text-gray-400">{{ $route }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2 px-2 text-right border-l">{{ $week->hits ?? 0 }}</td>
                                    <td class="py-2 px-2 text-right">{{ $week->unique_ips ?? 0 }}</td>
                                    <td class="py-2 px-2 text-right">{{ $week->human_hits ?? 0 }}</td>
                                    <td class="py-2 px-2 text-right">{{ $week->bot_hits ?? 0 }}</td>
                                    <td class="py-2 px-2 text-right border-l">{{ $month->hits ?? 0 }}</td>
                                    <td class="py-2 px-2 text-right">{{ $month->unique_ips ?? 0 }}</td>
                                    <td class="py-2 px-2 text-right">{{ $month->human_hits ?? 0 }}</td>
                                    <td class="py-2 px-2 text-right">{{ $month->bot_hits ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-gray-500">Még nincs összesíthető adat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
