<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · Felhasználó szerkesztése</h2>
                <div class="text-sm text-gray-500">#{{ $user->id }} · {{ $user->email }}</div>
            </div>

            <a class="underline" href="{{ route('admin.users.index') }}">← vissza</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-50 text-green-800 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="tier" value="Tier" />
                            <select id="tier" name="tier" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @foreach (['base' => 'Base', 'premium' => 'Premium', 'pro' => 'Pro'] as $value => $label)
                                    <option value="{{ $value }}" @selected($user->tier === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tier')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="is_admin" name="is_admin" value="1" type="checkbox" class="rounded border-gray-300" @checked($user->is_admin)>
                            <label for="is_admin" class="text-sm text-gray-700">Admin</label>
                            <x-input-error :messages="$errors->get('is_admin')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="token_quota_total" value="Token quota total" />
                                <x-text-input id="token_quota_total" name="token_quota_total" type="number" class="mt-1 block w-full" :value="old('token_quota_total', $user->token_quota_total)" />
                                <x-input-error :messages="$errors->get('token_quota_total')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="token_quota_used" value="Token quota used" />
                                <x-text-input id="token_quota_used" name="token_quota_used" type="number" class="mt-1 block w-full" :value="old('token_quota_used', $user->token_quota_used)" />
                                <x-input-error :messages="$errors->get('token_quota_used')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button>Mentés</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
