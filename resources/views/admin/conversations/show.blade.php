<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 text-gray-900">
                @include('admin.partials.header')

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h1 class="text-lg font-semibold">Konverzáció #{{ $conversation->id }}</h1>
                        <p class="text-sm text-gray-500">{{ $conversation->created_at?->format('Y.m.d H:i:s') }}</p>
                    </div>
                    <a href="{{ route('admin.conversations.index') }}" class="text-sm text-indigo-700 underline">Vissza a listához</a>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6 text-sm">
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Felhasználó</dt>
                        <dd class="font-medium mt-1">
                            @if ($conversation->user)
                                {{ $conversation->user->name }}<br>
                                <span class="text-gray-600">{{ $conversation->user->email }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Modell</dt>
                        <dd class="font-medium mt-1">{{ $conversation->model }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Thread ID</dt>
                        <dd class="font-medium mt-1">{{ data_get($conversation->meta, 'thread_id') ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <dt class="text-gray-500">Token összesen</dt>
                        <dd class="font-medium mt-1 text-lg">
                            @if ($usage['total_tokens'] !== null)
                                {{ number_format($usage['total_tokens'], 0, ',', ' ') }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($usage['total_tokens'] !== null)
                    <div class="grid gap-3 sm:grid-cols-3 mb-6">
                        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-indigo-700">Prompt token</div>
                            <div class="text-2xl font-semibold text-indigo-900 mt-1">
                                {{ number_format((int) $usage['prompt_tokens'], 0, ',', ' ') }}
                            </div>
                        </div>
                        <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-emerald-700">Válasz token</div>
                            <div class="text-2xl font-semibold text-emerald-900 mt-1">
                                {{ number_format((int) $usage['completion_tokens'], 0, ',', ' ') }}
                            </div>
                        </div>
                        <div class="rounded-lg border border-amber-100 bg-amber-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-amber-700">Összesen</div>
                            <div class="text-2xl font-semibold text-amber-900 mt-1">
                                {{ number_format((int) $usage['total_tokens'], 0, ',', ' ') }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-6">
                    <section>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">Prompt</h2>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $conversation->prompt }}</div>
                    </section>

                    <section>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">Teljes válasz</h2>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $conversation->response }}</div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
