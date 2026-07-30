<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Astro MOtto</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .landing-title {
            font-size: clamp(3rem, 12vw, 7.8rem);
            line-height: 1;
            font-weight: 700;
            letter-spacing: 0.025em;
            text-align: center;
        }

        .landing-card {
            width: calc(100% - 40px);
            margin: 0 20px;
            max-width: 500px;
        }

        @media (min-width: 768px) {
            .landing-card {
                margin-left: auto;
                margin-right: auto;
            }
        }

        .landing-primary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: 0.5rem;
            background-color: #eab308;
            color: #000;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.15s;
        }

        .landing-primary-btn:hover {
            background-color: #facc15;
        }
    </style>
</head>
<body class="min-h-screen bg-black text-white">

<div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
     style="background-image: url('{{ asset('images/astro-motto-hero.png') }}');">

    <div class="absolute top-4 right-4 z-20">
        @include('partials.locale-select', [
            'id' => 'landingLocaleSelect',
            'selectClass' => 'rounded-lg bg-black/70 border border-yellow-500/30 text-white text-sm px-2 py-1.5',
        ])
    </div>

    <div class="relative z-10 w-full text-center px-4">
        <h1 class="landing-title mb-10">Astro MOtto</h1>

        <div class="landing-card mx-auto bg-black/70 backdrop-blur-xl border border-yellow-500/30 rounded-2xl shadow-2xl p-8">
            <button type="button" id="openPersonalMessageBtn" class="landing-primary-btn">
                {{ __('public.personal_message_btn') }}
            </button>
        </div>
    </div>
</div>

@include('partials.auth-modal', ['open' => request('auth')])

<script>
    document.getElementById('openPersonalMessageBtn')?.addEventListener('click', () => {
        @auth
            window.location.href = @json(route('message.index'));
        @else
            window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }));
        @endauth
    });
</script>

</body>
</html>
