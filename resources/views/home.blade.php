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

        .home-message-box {
            position: relative;
            padding-top: 3.75rem;
        }

        .home-message-toolbar {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            max-width: calc(100% - 2rem);
        }

        .home-period-tabs {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.375rem;
        }

        .home-period-btn {
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid rgba(250, 204, 21, 0.35);
            color: rgb(254 240 138);
            font-size: 0.75rem;
            line-height: 1.25rem;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .home-period-btn:hover {
            background: rgba(250, 204, 21, 0.12);
        }

        .home-period-btn-active {
            background: rgb(250 204 21);
            color: rgb(17 24 39);
            border-color: rgb(250 204 21);
            font-weight: 600;
        }

        .home-login-btn {
            padding: 0.35rem 0.875rem;
            border-radius: 9999px;
            border: 1px solid rgba(250, 204, 21, 0.45);
            color: rgb(254 240 138);
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.25rem;
            white-space: nowrap;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .home-login-btn:hover {
            background: rgba(250, 204, 21, 0.15);
            border-color: rgb(250 204 21);
            color: rgb(254 249 195);
        }

        .home-horoscope-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem;
            border-radius: 9999px;
            border: 1px solid rgba(250, 204, 21, 0.45);
            color: rgb(254 240 138);
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .home-horoscope-btn:hover {
            background: rgba(250, 204, 21, 0.15);
            border-color: rgb(250 204 21);
            color: rgb(254 249 195);
        }
    </style>
</head>
<body class="min-h-screen bg-black text-white">

<div class="min-h-screen bg-cover bg-center relative"
     style="background-image: url('{{ asset('images/astro-motto-hero.png') }}');">

    <div class="absolute inset-0 bg-black/60"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-5 py-16 sm:py-24">
        <header class="text-center mb-10">
            <h1 class="home-title">Astro MOtto</h1>

            @if ($daily)
                <p class="mt-4 text-sm text-yellow-200/80">
                    @if ($period === \App\Support\HoroscopePeriod::DAILY)
                        {{ __('daily.chart_meta', [
                            'place' => config('daily_horoscope.location.label'),
                            'date' => $daily->period_start?->format('Y.m.d') ?? $daily->forecast_date->format('Y.m.d'),
                        ]) }}
                    @else
                        {{ __('daily.period_meta', [
                            'place' => config('daily_horoscope.location.label'),
                            'start' => $daily->period_start?->format('Y.m.d') ?? $daily->forecast_date->format('Y.m.d'),
                            'end' => $daily->period_end?->format('Y.m.d') ?? $daily->forecast_date->format('Y.m.d'),
                        ]) }}
                    @endif
                </p>
            @endif
        </header>

        @if ($error)
            <div id="daily-horoscope" class="home-message-box rounded-2xl border border-red-500/40 bg-red-950/50 p-6 sm:p-8 text-red-200 text-center">
                @include('partials.home-message-toolbar')
                {{ $error }}
            </div>
        @elseif ($daily)
            @auth
                @if ($daily instanceof \App\Models\UserDailyHoroscopeMessage)
                    <p class="text-center text-xs text-yellow-300/70 mb-4">
                        {{ $period === \App\Support\HoroscopePeriod::DAILY ? __('daily.personal_badge') : __('daily.personal_period_badge', ['period' => __('daily.period_'.$period)]) }}
                    </p>
                @endif
            @endauth
            <section id="daily-horoscope" class="home-message-box rounded-2xl border border-yellow-500/30 bg-black/70 backdrop-blur-xl p-6 sm:p-8 shadow-2xl space-y-8">
                @include('partials.home-message-toolbar')

                <div class="text-center">
                    <p class="home-motto text-yellow-100 font-medium">„{{ $daily->motto }}”</p>
                </div>

                <div>
                    <h2 class="home-section-title mb-3">{{ __('daily.summary_title_'.$period) }}</h2>
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
            <div id="daily-horoscope" class="home-message-box rounded-2xl border border-yellow-500/30 bg-black/70 backdrop-blur-xl p-6 sm:p-8 shadow-2xl text-center">
                @include('partials.home-message-toolbar')
                <p class="text-slate-300">{{ __('daily.unpublished') }}</p>
            </div>
        @endif
    </div>
</div>

</body>
</html>
