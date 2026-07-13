<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('partials.app-icon-toolbar')

                <div class="max-w-xl mt-6">
                    @include('profile.partials.update-horoscope-settings-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
