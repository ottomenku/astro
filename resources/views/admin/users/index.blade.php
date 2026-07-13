<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @include('partials.app-icon-toolbar')
                @include('partials.admin-icon-toolbar')

                <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
                    <x-text-input name="q" :value="$q" placeholder="Keresés (név/email)" class="w-64" />
                    <x-primary-button>Szűrés</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2 pr-4">ID</th>
                                    <th class="py-2 pr-4">Név</th>
                                    <th class="py-2 pr-4">Email</th>
                                    <th class="py-2 pr-4">Tier</th>
                                    <th class="py-2 pr-4">Admin</th>
                                    <th class="py-2 pr-4">Token (used/total)</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr class="border-b">
                                        <td class="py-2 pr-4">{{ $user->id }}</td>
                                        <td class="py-2 pr-4">{{ $user->name }}</td>
                                        <td class="py-2 pr-4">{{ $user->email }}</td>
                                        <td class="py-2 pr-4">{{ $user->tier }}</td>
                                        <td class="py-2 pr-4">{{ $user->is_admin ? 'igen' : 'nem' }}</td>
                                        <td class="py-2 pr-4">{{ $user->token_quota_used }} / {{ $user->token_quota_total }}</td>
                                        <td class="py-2 pr-4 text-right">
                                            <a class="underline" href="{{ route('admin.users.edit', $user) }}">Szerkesztés</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
