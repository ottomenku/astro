<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('daily.page_title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .home-title {
            font-size: clamp(2.5rem, 8vw, 4.5rem);
            line-height: 1.05;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .home-motto {
            font-size: clamp(1.25rem, 3vw, 1.75rem);
            line-height: 1.4;
        }

        .home-section-title {
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgb(250 204 21);
        }
    </style>
</head>
<body class="min-h-screen bg-black text-white">

<div class="min-h-screen bg-cover bg-center relative"
     style="background-image: url('{{ asset('images/astro-motto-hero.png') }}');">

    <div class="absolute inset-0 bg-black/60"></div>

    <div class="absolute top-4 right-4 z-20 flex items-center gap-3">
        @include('partials.locale-select', [
            'id' => 'homeLocaleSelect',
            'selectClass' => 'rounded-lg bg-black/70 border border-yellow-500/30 text-white text-sm px-2 py-1.5',
        ])
        @auth
            <a href="{{ route('horoscope.index') }}" class="text-sm text-yellow-300 hover:text-yellow-200 underline">
                {{ __('daily.horoscope') }}
            </a>
        @else
            <a href="{{ route('login') }}" class="text-sm text-yellow-300 hover:text-yellow-200 underline">
                {{ __('daily.login') }}
            </a>
        @endauth
    </div>

    <div class="relative z-10 max-w-3xl mx-auto px-5 py-16 sm:py-24">
        <header class="text-center mb-10">
            <h1 class="home-title">Astro MOtto</h1>
            @if ($daily)
                <p class="mt-4 text-sm text-yellow-200/80">
                    {{ __('daily.chart_meta', [
                        'place' => config('daily_horoscope.location.label'),
                        'date' => $daily->forecast_date->format('Y.m.d'),
                    ]) }}
                </p>
            @endif
        </header>

        @if ($error)
            <div class="rounded-2xl border border-red-500/40 bg-red-950/50 p-6 text-red-200 text-center">
                {{ $error }}
            </div>
        @elseif ($daily)
            @auth
                @if ($daily instanceof \App\Models\UserDailyHoroscopeMessage)
                    <p class="text-center text-xs text-yellow-300/70 mb-4">{{ __('daily.personal_badge') }}</p>
                @endif
            @endauth
            <section class="rounded-2xl border border-yellow-500/30 bg-black/70 backdrop-blur-xl p-6 sm:p-8 shadow-2xl space-y-8">
                <div class="text-center">
                    <p class="home-section-title mb-3">{{ __('daily.motto_label') }}</p>
                    <p class="home-motto text-yellow-100 font-medium">„{{ $daily->motto }}”</p>
                </div>

                <div>
                    <h2 class="home-section-title mb-3">{{ __('daily.summary_title') }}</h2>
                    <p class="text-slate-200 leading-relaxed whitespace-pre-line">{{ $daily->summary }}</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <article>
                        <h3 class="home-section-title mb-2">{{ __('daily.section_health') }}</h3>
                        <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ $daily->health }}</p>
                    </article>
                    <article>
                        <h3 class="home-section-title mb-2">{{ __('daily.section_money') }}</h3>
                        <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ $daily->money }}</p>
                    </article>
                    <article>
                        <h3 class="home-section-title mb-2">{{ __('daily.section_relationships') }}</h3>
                        <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ $daily->relationships }}</p>
                    </article>
                    <article>
                        <h3 class="home-section-title mb-2">{{ __('daily.section_work') }}</h3>
                        <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ $daily->work }}</p>
                    </article>
                </div>
            </section>
        @else
            <p class="text-center text-slate-300">{{ __('daily.unpublished') }}</p>
        @endif
    </div>
</div>

</body>
</html>
