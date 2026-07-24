<div
    x-show="menuOpen"
    x-cloak
    @click.outside="menuOpen = false"
    class="absolute right-0 top-full mt-1 z-50 w-72 rounded-md bg-white border border-gray-200 shadow-lg py-1 text-sm"
>
    @auth
        <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100">
            {{ Auth::user()->name }}
        </div>

        <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
            {{ __('app.menu_settings') }}
        </div>

        <a href="{{ route('profile.birth-charts.index') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('profile.birth-charts.*') ? 'font-semibold text-indigo-600' : '' }}">
            {{ __('app.profile_birth_charts') }}
        </a>

        <a href="{{ route('profile.horoscope.edit') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('profile.horoscope.*') ? 'font-semibold text-indigo-600' : '' }}">
            {{ __('app.profile_horoscope') }}
        </a>

        <a href="{{ route('profile.daily-horoscope.edit') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('profile.daily-horoscope.*') ? 'font-semibold text-indigo-600' : '' }}">
            {{ __('app.profile_daily_horoscope') }}
        </a>

        <div class="my-1 border-t border-gray-100"></div>

        <div class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
            {{ __('app.menu_account') }}
        </div>

        <a href="{{ route('profile.edit') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('profile.edit') ? 'font-semibold text-indigo-600' : '' }}">
            {{ __('app.profile_account') }}
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50">
                {{ __('Log Out') }}
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
            {{ __('app.login') }}
        </a>
        <a href="{{ route('register') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
            {{ __('app.register') }}
        </a>
    @endauth
</div>
