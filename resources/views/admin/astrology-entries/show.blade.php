<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 text-gray-900">
                @include('admin.partials.header')

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h1 class="text-lg font-semibold">{{ $entry->title }}</h1>
                        <p class="text-sm text-gray-500">
                            {{ $entry->typeLabel() }} · {{ $entry->key }} · {{ strtoupper($entry->locale) }}
                        </p>
                    </div>
                    <a href="{{ route('admin.astrology-entries.index') }}" class="text-sm text-indigo-700 underline">Vissza a listához</a>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6 text-sm">
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Azonosító (key)</dt>
                        <dd class="font-mono text-xs mt-1 break-all">{{ $entry->key }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Kattintások száma</dt>
                        <dd class="font-semibold text-lg mt-1">{{ number_format($entry->click_count, 0, ',', ' ') }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Első kattintó</dt>
                        <dd class="font-medium mt-1">
                            @if ($entry->firstClickedBy)
                                {{ $entry->firstClickedBy->name }}<br>
                                <span class="text-gray-600">{{ $entry->firstClickedBy->email }}</span><br>
                                <span class="text-gray-500 text-xs">{{ $entry->first_clicked_at?->format('Y.m.d H:i:s') }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Utolsó kattintás</dt>
                        <dd class="font-medium mt-1">{{ $entry->last_clicked_at?->format('Y.m.d H:i:s') ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">LM generálta</dt>
                        <dd class="font-medium mt-1">
                            @if ($entry->createdBy)
                                {{ $entry->createdBy->name }}<br>
                                <span class="text-gray-600">{{ $entry->createdBy->email }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Létrehozva</dt>
                        <dd class="font-medium mt-1">{{ $entry->created_at?->format('Y.m.d H:i:s') }}</dd>
                    </div>
                </dl>

                <div class="space-y-6">
                    <section>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">Prompt</h2>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $entry->question }}</div>
                    </section>

                    <section>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">Válasz</h2>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $entry->answer }}</div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
