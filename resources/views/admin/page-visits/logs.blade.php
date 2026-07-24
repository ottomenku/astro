<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @include('partials.app-icon-toolbar')
                @include('partials.admin-icon-toolbar')

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Oldalmegtekintések</h1>
                        <p class="text-sm text-gray-500">
                            Részletes napló az utolsó {{ $days }} napból. Megőrzés: {{ $retentionDays }} nap.
                        </p>
                    </div>
                    <a href="{{ route('admin.page-visits.summary') }}" class="text-sm text-indigo-700 underline">Összesítő</a>
                </div>

                <form method="GET" class="flex flex-wrap items-end gap-2 mb-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Keresés</label>
                        <x-text-input name="q" :value="$q" placeholder="IP, név, oldal, útvonal" class="w-64" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Időszak</label>
                        <select name="days" class="rounded-md border-gray-300 text-sm">
                            @foreach ([1, 3, 7, 14, 30] as $option)
                                <option value="{{ $option }}" @selected($days === $option)>Utolsó {{ $option }} nap</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Látogató</label>
                        <select name="visitor_type" class="rounded-md border-gray-300 text-sm">
                            <option value="all" @selected($visitorType === 'all')>Mind</option>
                            <option value="human" @selected($visitorType === 'human')>Ember</option>
                            <option value="bot" @selected($visitorType === 'bot')>Robot</option>
                        </select>
                    </div>
                    <x-primary-button>Szűrés</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="text-left border-b text-gray-600">
                                <th class="py-2 pr-3">Időpont</th>
                                <th class="py-2 pr-3">Oldal</th>
                                <th class="py-2 pr-3">IP</th>
                                <th class="py-2 pr-3">Felhasználó</th>
                                <th class="py-2 pr-3">Típus</th>
                                <th class="py-2 pr-3">Eszköz / böngésző</th>
                                <th class="py-2 pr-3">Hely</th>
                                <th class="py-2 pr-3">Nyelv</th>
                                <th class="py-2 pr-3">HTTP</th>
                                <th class="py-2 pr-3">Referer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($visits as $visit)
                                <tr class="border-b align-top {{ $visit->is_bot ? 'bg-amber-50/60' : '' }}">
                                    <td class="py-2 pr-3 whitespace-nowrap">{{ $visit->visited_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-2 pr-3">
                                        <div class="font-medium text-gray-900">{{ $visit->page_label }}</div>
                                        <div class="font-mono text-gray-500">{{ $visit->path }}</div>
                                        @if ($visit->route_name)
                                            <div class="text-gray-400">{{ $visit->route_name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3 font-mono">{{ $visit->ip_address }}</td>
                                    <td class="py-2 pr-3">
                                        @if ($visit->user_name)
                                            <div>{{ $visit->user_name }}</div>
                                            @if ($visit->user_email)
                                                <div class="text-gray-500">{{ $visit->user_email }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">
                                        @if ($visit->is_bot)
                                            <span class="text-amber-700 font-medium">Robot</span>
                                            @if ($visit->bot_name)
                                                <div class="text-gray-500">{{ $visit->bot_name }}</div>
                                            @endif
                                        @else
                                            <span class="text-green-700 font-medium">Ember</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">
                                        <div>{{ $visit->device_type ?? '—' }}</div>
                                        <div>{{ $visit->browser ?? '—' }} {{ $visit->browser_version }}</div>
                                        <div class="text-gray-500">{{ $visit->platform ?? '—' }} {{ $visit->platform_version }}</div>
                                    </td>
                                    <td class="py-2 pr-3">
                                        @if ($visit->country_name || $visit->city || $visit->region)
                                            <div>{{ $visit->country_name ?? $visit->country_code ?? '—' }}</div>
                                            <div class="text-gray-500">
                                                {{ collect([$visit->city, $visit->region])->filter()->implode(', ') }}
                                            </div>
                                            @if ($visit->timezone)
                                                <div class="text-gray-400">{{ $visit->timezone }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3 max-w-[10rem] truncate" title="{{ $visit->accept_language }}">
                                        {{ $visit->accept_language ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-3">{{ $visit->status_code ?? '—' }}</td>
                                    <td class="py-2 pr-3 max-w-[12rem] truncate" title="{{ $visit->referer }}">
                                        {{ $visit->referer ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-gray-500">Nincs naplózott megtekintés a kiválasztott időszakban.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $visits->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
