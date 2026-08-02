@php
    $isHome = ($mode ?? 'home') === 'home';
    $isPersonal = ($mode ?? 'home') === 'personal';
    $isHoroscope = ($mode ?? 'home') === 'horoscope';
@endphp

<header class="site-header{{ $isHome ? ' site-header--home' : '' }}">
    <a class="brand" href="{{ route('home') }}" aria-label="{{ __('app.homepage') }}">
        @if ($isHoroscope)
            <span class="aurora-horoscope-brand-title">{{ __('public.aurora_nav_chart') }}</span>
        @else
            <img class="brand-lockup" src="{{ asset('assets/aurora/astromotto-logo.png') }}" alt="AstroMOtto" width="359" height="112">
        @endif
    </a>

    @if (! $isHoroscope)
        @include('templates.aurora.partials.locale-selector')
    @endif

    @if ($isPersonal)
        <button type="button" id="openSimpleHamburgerBtn" class="more-button">
            <span aria-hidden="true">•••</span> {{ __('public.aurora_more_options') }}
        </button>
    @elseif ($isHoroscope)
        <div class="aurora-user-chip">{{ auth()->user()->name }} ›</div>
    @else
        @auth
            <button type="button" id="openSimpleHamburgerBtn" class="more-button">
                <span aria-hidden="true">•••</span> {{ __('public.aurora_more_options') }}
            </button>
        @else
            <button type="button" class="more-button aurora-open-auth" data-auth-tab="login">
                <span aria-hidden="true">•••</span> {{ __('public.aurora_more_options') }}
            </button>
        @endauth
    @endif
</header>

@auth
    @if (! $isPersonal && ! $isHoroscope)
        @include('partials.simple-hamburger-menu')
    @endif
@endauth
