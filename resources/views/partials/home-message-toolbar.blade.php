<div class="home-message-toolbar">
    <div class="home-period-tabs">
        @foreach ([\App\Support\HoroscopePeriod::DAILY, \App\Support\HoroscopePeriod::WEEKLY, \App\Support\HoroscopePeriod::MONTHLY] as $periodOption)
            <a href="{{ route('home', ['period' => $periodOption]) }}#daily-horoscope"
               class="home-period-btn {{ $period === $periodOption ? 'home-period-btn-active' : '' }}">
                {{ __('daily.period_'.$periodOption) }}
            </a>
        @endforeach
    </div>

    @include('partials.locale-select', [
        'id' => 'homeLocaleSelect',
        'selectClass' => 'home-locale-select',
    ])

    @auth
        <a href="{{ route('message.index') }}" class="home-login-btn">{{ __('public.personal_message_btn') }}</a>
    @else
        <button type="button" class="home-login-btn open-personal-message-btn">{{ __('public.personal_message_btn') }}</button>
    @endauth
</div>
