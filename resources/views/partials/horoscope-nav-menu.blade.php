<div
    x-show="menuOpen"
    x-cloak
    @click.outside="menuOpen = false"
    class="absolute right-0 top-full mt-1 z-50 w-56 rounded-md bg-white border border-gray-200 shadow-lg py-1 text-sm"
>
    <a
        href="{{ route('horoscope.index') }}"
        class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('horoscope.*') ? 'font-semibold text-indigo-600' : '' }}"
    >
        {{ __('app.horoscope') }}
    </a>
    <a
        href="{{ route('chat.index') }}"
        class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('chat.*') ? 'font-semibold text-indigo-600' : '' }}"
    >
        {{ __('app.chat') }}
    </a>
    @if (Auth::user()?->is_admin)
        <a
            href="{{ route('admin.visitors.index') }}"
            class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.*') ? 'font-semibold text-indigo-600' : '' }}"
        >
            {{ __('app.admin') }}
        </a>
    @endif
    <div class="my-1 border-t border-gray-100"></div>
    <div class="px-4 py-2 text-xs text-gray-500">{{ Auth::user()->name }}</div>
    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('profile.edit') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('Profile') }}
    </a>
    <a href="{{ route('profile.horoscope.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('profile.horoscope.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.profile_horoscope') }}
    </a>
    <a href="{{ route('profile.birth-charts.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ request()->routeIs('profile.birth-charts.*') ? 'font-semibold text-indigo-600' : '' }}">
        {{ __('app.profile_birth_charts') }}
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50">
            {{ __('Log Out') }}
        </button>
    </form>
</div>
