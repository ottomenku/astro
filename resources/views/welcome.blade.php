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
            padding-top: 2rem;
            padding-bottom: 2rem;
            padding-left: 0;
            padding-right: 0;
        }

        .login-form-inner-x {
            padding-left: 10px;
            padding-right: 10px;
        }

        .login-form-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding-left: 10px;
            padding-right: 10px;
        }

        .login-form-btn {
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            transition: background-color 0.15s, color 0.15s, border-color 0.15s;
        }

        .login-form-btn-primary {
            background-color: #eab308;
            color: #000;
        }

        .login-form-btn-primary:hover {
            background-color: #facc15;
        }

        .login-form-btn-secondary {
            border: 1px solid rgba(234, 179, 8, 0.5);
            color: #facc15;
        }

        .login-form-btn-secondary:hover {
            border-color: #facc15;
            color: #fde047;
        }

        .login-title {
            font-size: 5.85rem;
            line-height: 1;
            font-weight: 700;
            margin-bottom: 2rem;
            letter-spacing: 0.025em;
            text-align: center;
        }

        @media (min-width: 768px) {
            .login-title {
                font-size: 7.8rem;
            }

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
        <h1 class="login-title">
            Astro MOtto
        </h1>

        <div class="login-form-card bg-black/70 backdrop-blur-xl border border-yellow-500/30 rounded-2xl shadow-2xl">
            @if (session('status'))
                <p class="login-form-inner-x mb-4 text-sm text-green-400 text-left">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <div class="login-form-inner-x mb-4 text-sm text-red-400 text-left">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div class="login-form-inner-x text-left">
                    <label class="block text-sm mb-1 text-slate-300">Email</label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div class="login-form-inner-x text-left">
                    <label class="block text-sm mb-1 text-slate-300">Jelszó</label>
                    <input type="password"
                           name="password"
                           required
                           class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div class="login-form-actions">
                    <button type="submit" class="login-form-btn login-form-btn-primary">
                        Belépés
                    </button>
                    <a href="{{ route('register') }}" class="login-form-btn login-form-btn-secondary">
                        Regisztráció
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
