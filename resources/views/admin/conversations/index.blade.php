<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 text-gray-900">
                @include('partials.app-icon-toolbar')
                @include('partials.admin-icon-toolbar')

                <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">ID</th>
                                    <th class="py-2 pr-4">User</th>
                                    <th class="py-2 pr-4">Model</th>
                                    <th class="py-2 pr-4">Prompt</th>
                                    <th class="py-2 pr-4">Válasz</th>
                                    <th class="py-2 pr-4">Idő</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($conversations as $c)
                                    <tr class="border-b align-top">
                                        <td class="py-2 pr-4">{{ $c->id }}</td>
                                        <td class="py-2 pr-4">
                                            @if ($c->user)
                                                {{ $c->user->email }}
                                            @else
                                                <span class="text-gray-400">(nincs)</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ $c->model }}</td>
                                        <td class="py-2 pr-4 max-w-xs">
                                            <div class="whitespace-pre-wrap break-words">{{ \Illuminate\Support\Str::limit($c->prompt, 300) }}</div>
                                        </td>
                                        <td class="py-2 pr-4 max-w-xs">
                                            <div class="whitespace-pre-wrap break-words">{{ \Illuminate\Support\Str::limit($c->response, 300) }}</div>
                                        </td>
                                        <td class="py-2 pr-4">{{ $c->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $conversations->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
