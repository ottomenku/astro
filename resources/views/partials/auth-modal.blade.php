<div id="authModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 py-8" role="dialog" aria-modal="true" aria-labelledby="authModalTitle">
    <div class="fixed inset-0 bg-black/70" data-auth-modal-close></div>
    <div class="relative w-full max-w-md bg-black/90 border border-yellow-500/30 rounded-2xl shadow-2xl p-6 text-white">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-white text-2xl leading-none" data-auth-modal-close aria-label="{{ __('public.close') }}">&times;</button>

        <h2 id="authModalTitle" class="text-lg font-semibold text-center mb-4 text-yellow-100">{{ __('public.auth_modal_title') }}</h2>

        <div class="flex gap-2 mb-5">
            <button type="button" id="authTabLogin" class="flex-1 py-2 rounded-lg text-sm font-semibold bg-yellow-500 text-black">{{ __('app.login') }}</button>
            <button type="button" id="authTabRegister" class="flex-1 py-2 rounded-lg text-sm font-semibold border border-yellow-500/40 text-yellow-100">{{ __('app.register') }}</button>
        </div>

        @if ($errors->any() && old('_form') === 'login')
            <div class="mb-4 text-sm text-red-400">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($errors->any() && old('_form') === 'register')
            <div class="mb-4 text-sm text-red-400">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form id="authLoginForm" method="POST" action="{{ route('login') }}" class="space-y-4 {{ ($open ?? '') === 'register' ? 'hidden' : '' }}">
            @csrf
            <input type="hidden" name="_form" value="login">
            <div>
                <label class="block text-sm mb-1 text-slate-300">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>
            <div>
                <label class="block text-sm mb-1 text-slate-300">{{ __('app.password') }}</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-lg bg-yellow-500 text-black font-semibold hover:bg-yellow-400">{{ __('app.login') }}</button>
        </form>

        <form id="authRegisterForm" method="POST" action="{{ route('register') }}" class="space-y-4 {{ ($open ?? '') === 'register' ? '' : 'hidden' }}">
            @csrf
            <input type="hidden" name="_form" value="register">
            <div>
                <label class="block text-sm mb-1 text-slate-300">{{ __('app.name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>
            <div>
                <label class="block text-sm mb-1 text-slate-300">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>
            <div>
                <label class="block text-sm mb-1 text-slate-300">{{ __('app.password') }}</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>
            <div>
                <label class="block text-sm mb-1 text-slate-300">{{ __('app.password_confirmation') }}</label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-lg bg-yellow-500 text-black font-semibold hover:bg-yellow-400">{{ __('app.register') }}</button>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('authModal');
        const loginForm = document.getElementById('authLoginForm');
        const registerForm = document.getElementById('authRegisterForm');
        const tabLogin = document.getElementById('authTabLogin');
        const tabRegister = document.getElementById('authTabRegister');

        function showTab(tab) {
            const isLogin = tab === 'login';
            loginForm?.classList.toggle('hidden', !isLogin);
            registerForm?.classList.toggle('hidden', isLogin);
            tabLogin?.classList.toggle('bg-yellow-500', isLogin);
            tabLogin?.classList.toggle('text-black', isLogin);
            tabLogin?.classList.toggle('border', !isLogin);
            tabLogin?.classList.toggle('border-yellow-500/40', !isLogin);
            tabLogin?.classList.toggle('text-yellow-100', !isLogin);
            tabRegister?.classList.toggle('bg-yellow-500', !isLogin);
            tabRegister?.classList.toggle('text-black', !isLogin);
            tabRegister?.classList.toggle('border', isLogin);
            tabRegister?.classList.toggle('border-yellow-500/40', isLogin);
            tabRegister?.classList.toggle('text-yellow-100', isLogin);
        }

        function openModal(tab = 'login') {
            modal?.classList.remove('hidden');
            showTab(tab);
        }

        function closeModal() {
            modal?.classList.add('hidden');
        }

        tabLogin?.addEventListener('click', () => showTab('login'));
        tabRegister?.addEventListener('click', () => showTab('register'));
        modal?.querySelectorAll('[data-auth-modal-close]').forEach((el) => {
            el.addEventListener('click', closeModal);
        });
        window.addEventListener('open-auth-modal', (event) => {
            openModal(event.detail?.tab || 'login');
        });

        @if (($open ?? '') || ($errors->any() && old('_form')))
            openModal(@json(old('_form', $open ?? 'login')));
        @endif
    })();
</script>
