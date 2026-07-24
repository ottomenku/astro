<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 text-gray-900">
                @include('partials.app-icon-toolbar')
                @include('partials.admin-icon-toolbar')

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h1 class="text-lg font-semibold">{{ __('app.admin_astrology_entries') }}</h1>
                        <p class="text-sm text-gray-500">Elem- és fényszög-kattintások, promptok és válaszok</p>
                    </div>
                </div>

                <form method="get" class="flex flex-wrap gap-3 mb-4 text-sm">
                    <input type="search"
                           name="q"
                           value="{{ $q }}"
                           placeholder="Keresés cím, azonosító, prompt, válasz…"
                           class="rounded border-gray-300 w-full sm:w-72">
                    <select name="type" class="rounded border-gray-300">
                        <option value="all" @selected($type === 'all')>Minden típus</option>
                        <option value="sign" @selected($type === 'sign')>Jegy</option>
                        <option value="planet" @selected($type === 'planet')>Bolygó</option>
                        <option value="fixed_star" @selected($type === 'fixed_star')>Fix csillag</option>
                        <option value="aspect" @selected($type === 'aspect')>Fényszög</option>
                    </select>
                    <select name="locale" class="rounded border-gray-300">
                        <option value="all" @selected($locale === 'all')>Minden nyelv</option>
                        <option value="hu" @selected($locale === 'hu')>Magyar</option>
                        <option value="en" @selected($locale === 'en')>Angol</option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Szűrés</button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2 pr-4">ID</th>
                                <th class="py-2 pr-4">Típus</th>
                                <th class="py-2 pr-4">Azonosító</th>
                                <th class="py-2 pr-4">Cím</th>
                                <th class="py-2 pr-4">Nyelv</th>
                                <th class="py-2 pr-4">Kattintások</th>
                                <th class="py-2 pr-4">Első kattintó</th>
                                <th class="py-2 pr-4">Utolsó kattintás</th>
                                <th class="py-2 pr-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                <tr class="border-b align-top">
                                    <td class="py-2 pr-4">{{ $entry->id }}</td>
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $entry->typeLabel() }}</td>
                                    <td class="py-2 pr-4 font-mono text-xs break-all max-w-[12rem]">{{ $entry->key }}</td>
                                    <td class="py-2 pr-4">{{ $entry->title }}</td>
                                    <td class="py-2 pr-4">{{ strtoupper($entry->locale) }}</td>
                                    <td class="py-2 pr-4 font-semibold">{{ number_format($entry->click_count, 0, ',', ' ') }}</td>
                                    <td class="py-2 pr-4">
                                        @if ($entry->firstClickedBy)
                                            {{ $entry->firstClickedBy->name }}<br>
                                            <span class="text-gray-600">{{ $entry->firstClickedBy->email }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4 whitespace-nowrap">
                                        {{ $entry->last_clicked_at?->format('Y.m.d H:i') ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        <a href="{{ route('admin.astrology-entries.show', $entry) }}" class="text-indigo-700 underline">Részletek</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-6 text-center text-gray-500">Még nincs rögzített kattintás.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $entries->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
