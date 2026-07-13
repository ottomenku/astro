<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 text-gray-900">
                @include('partials.app-icon-toolbar')
                <p class="mt-4">{{ __("You're logged in!") }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
