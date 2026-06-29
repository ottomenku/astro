<nav class="flex flex-wrap gap-4 border-b border-gray-200 pb-3 mb-6 text-sm">
    <a
        href="{{ route('profile.edit') }}"
        @class([
            'font-semibold text-indigo-600 border-b-2 border-indigo-600 pb-1' => request()->routeIs('profile.edit'),
            'text-gray-600 hover:text-gray-900 pb-1' => ! request()->routeIs('profile.edit'),
        ])
    >
        {{ __('app.profile_account') }}
    </a>
    <a
        href="{{ route('profile.horoscope.edit') }}"
        @class([
            'font-semibold text-indigo-600 border-b-2 border-indigo-600 pb-1' => request()->routeIs('profile.horoscope.*'),
            'text-gray-600 hover:text-gray-900 pb-1' => ! request()->routeIs('profile.horoscope.*'),
        ])
    >
        {{ __('app.profile_horoscope') }}
    </a>
    <a
        href="{{ route('profile.birth-charts.index') }}"
        @class([
            'font-semibold text-indigo-600 border-b-2 border-indigo-600 pb-1' => request()->routeIs('profile.birth-charts.*'),
            'text-gray-600 hover:text-gray-900 pb-1' => ! request()->routeIs('profile.birth-charts.*'),
        ])
    >
        {{ __('app.profile_birth_charts') }}
    </a>
</nav>
