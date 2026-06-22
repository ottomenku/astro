<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · Látogatók</h2>

            <form method="GET" class="flex items-center gap-2">
                <x-text-input name="q" :value="$q" placeholder="Keresés (IP / név)" class="w-64" />
                <x-primary-button>Szűrés</x-primary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.partials.nav')

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">IP-cím</th>
                                    <th class="py-2 pr-4">Felhasználó</th>
                                    <th class="py-2 pr-4">Látogatások</th>
                                    <th class="py-2 pr-4">Horoszkóp</th>
                                    <th class="py-2 pr-4">Chat</th>
                                    <th class="py-2 pr-4">Első látogatás</th>
                                    <th class="py-2 pr-4">Utolsó látogatás</th>
                                    <th class="py-2 pr-4">Státusz</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($visitors as $visitor)
                                    <tr class="border-b {{ $visitor->is_banned ? 'bg-red-50' : '' }}">
                                        <td class="py-2 pr-4 font-mono">{{ $visitor->ip_address }}</td>
                                        <td class="py-2 pr-4">
                                            @if ($visitor->user_name)
                                                {{ $visitor->user_name }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ $visitor->visit_count }}</td>
                                        <td class="py-2 pr-4">{{ $visitor->horoscope_views }}</td>
                                        <td class="py-2 pr-4">{{ $visitor->chat_views }}</td>
                                        <td class="py-2 pr-4 whitespace-nowrap">{{ $visitor->first_seen_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td class="py-2 pr-4 whitespace-nowrap">{{ $visitor->last_seen_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td class="py-2 pr-4">
                                            @if ($visitor->is_banned)
                                                <span class="text-red-600 font-medium">Tiltva</span>
                                            @else
                                                <span class="text-green-600">Aktív</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4 text-right whitespace-nowrap">
                                            @if ($visitor->is_banned)
                                                <form method="POST" action="{{ route('admin.visitors.unban', $visitor) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="underline text-green-700">Feloldás</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.visitors.ban', $visitor) }}" class="inline"
                                                      onsubmit="return confirm('Biztosan tiltod ezt az IP-címet?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="underline text-red-600">Tiltás</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-6 text-center text-gray-500">Még nincs látogatói adat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $visitors->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
