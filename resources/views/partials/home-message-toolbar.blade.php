<div class="home-message-toolbar">
    <div class="home-period-tabs">
        @foreach ([\App\Support\HoroscopePeriod::DAILY, \App\Support\HoroscopePeriod::WEEKLY, \App\Support\HoroscopePeriod::MONTHLY] as $periodOption)
            <a href="{{ route('home', ['period' => $periodOption]) }}#daily-horoscope"
               class="home-period-btn {{ $period === $periodOption ? 'home-period-btn-active' : '' }}">
                {{ __('daily.period_'.$periodOption) }}
            </a>
        @endforeach
    </div>

    @auth
        <a href="{{ route('horoscope.index') }}"
           class="home-horoscope-btn"
           title="{{ __('app.horoscope') }}"
           aria-label="{{ __('app.horoscope') }}">
            @include('partials.icons.horoscope-wheel', ['class' => 'h-5 w-5'])
        </a>
    @else
        <a href="{{ route('login') }}" class="home-login-btn">{{ __('daily.login') }}</a>
    @endauth
</div>
