<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Astro MOtto</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-form-card {
            width: calc(100% - 40px);
            margin-left: 20px;
            margin-right: 20px;
        }

        @media (min-width: 768px) {
            .login-form-card {
                width: 100%;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-black text-white">

<div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
     style="background-image: url('{{ asset('images/astro-motto-hero.png') }}');">

    <div class="relative z-10 w-full text-center">
        <h1 class="text-6xl md:text-7xl font-bold mb-2 tracking-wide">
            Astro MOtto
        </h1>

        <p class="text-slate-300 mb-8">
            Bejelentkezés
        </p>

        <div class="login-form-card bg-black/70 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-8 shadow-2xl">
            @if (session('status'))
                <p class="mb-4 text-sm text-green-400 text-left">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-400 text-left">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div class="text-left">
                    <label class="block text-sm mb-1 text-slate-300">Email</label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div class="text-left">
                    <label class="block text-sm mb-1 text-slate-300">Jelszó</label>
                    <input type="password"
                           name="password"
                           required
                           class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-yellow-500 hover:bg-yellow-400 text-black font-semibold py-3 transition">
                    Belépés
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
