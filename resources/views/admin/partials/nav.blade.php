<nav class="mb-6 flex flex-wrap gap-4 text-sm">
    <a href="{{ route('admin.visitors.index') }}"
       class="{{ request()->routeIs('admin.visitors.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        Látogatók
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="{{ request()->routeIs('admin.users.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        Felhasználók
    </a>
    <a href="{{ route('admin.conversations.index') }}"
       class="{{ request()->routeIs('admin.conversations.*') ? 'font-semibold text-indigo-600 underline' : 'text-gray-600 hover:text-gray-900' }}">
        Konverzációk
    </a>
</nav>
