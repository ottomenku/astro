<div id="simpleHamburgerPanel" class="hidden fixed inset-0 z-50 isolate" role="dialog" aria-modal="true" aria-label="{{ __('public.more_options_btn') }}">
    <div class="fixed inset-0 z-0 bg-black/70" id="simpleHamburgerBackdrop"></div>
    <nav class="fixed top-0 right-0 z-10 h-full w-72 max-w-[85vw] bg-white shadow-xl p-6 flex flex-col gap-1 text-gray-800">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-semibold text-gray-900">{{ __('public.more_options_btn') }}</span>
            <button type="button" id="simpleHamburgerClose" class="text-gray-500 hover:text-gray-800 text-2xl leading-none" aria-label="{{ __('public.close') }}">&times;</button>
        </div>

        <a href="{{ route('home') }}" class="block py-3 px-2 rounded hover:bg-gray-100 text-sm font-medium">{{ __('public.back_home') }}</a>
        <a href="{{ route('horoscope.index') }}" class="block py-3 px-2 rounded hover:bg-gray-100 text-sm font-medium">{{ __('public.menu_horoscope') }}</a>
        <a href="{{ route('horoscope.index', ['view' => 'tables']) }}" class="block py-3 px-2 rounded hover:bg-gray-100 text-sm font-medium">{{ __('public.menu_tables') }}</a>
        <a href="{{ route('horoscope.index', ['mode' => 'dual', 'view' => 'chart']) }}" class="block py-3 px-2 rounded hover:bg-gray-100 text-sm font-medium">{{ __('public.menu_dual') }}</a>
        <a href="{{ route('profile.horoscope.edit') }}" class="block py-3 px-2 rounded hover:bg-gray-100 text-sm font-medium">{{ __('public.menu_settings') }}</a>
        <a href="{{ route('profile.birth-charts.index') }}" class="block py-3 px-2 rounded hover:bg-gray-100 text-sm font-medium">{{ __('public.menu_birth_charts') }}</a>

        <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-4 border-t border-gray-200">
            @csrf
            <button type="submit" class="w-full text-left py-3 px-2 rounded hover:bg-gray-100 text-sm font-medium text-red-700">{{ __('public.menu_logout') }}</button>
        </form>
    </nav>
</div>

<script>
    (function () {
        const panel = document.getElementById('simpleHamburgerPanel');
        const openBtn = document.getElementById('openSimpleHamburgerBtn');
        const closeBtn = document.getElementById('simpleHamburgerClose');
        const backdrop = document.getElementById('simpleHamburgerBackdrop');

        function openPanel() {
            panel?.classList.remove('hidden');
        }

        function closePanel() {
            panel?.classList.add('hidden');
        }

        openBtn?.addEventListener('click', openPanel);
        closeBtn?.addEventListener('click', closePanel);
        backdrop?.addEventListener('click', closePanel);
    })();
</script>
