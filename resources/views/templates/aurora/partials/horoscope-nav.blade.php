@php
    $mode = request()->query('mode') === 'dual' ? 'dual' : 'single';
    $view = request()->query('view', 'chart');
    $period = \App\Support\HoroscopePeriod::normalize(request()->query('period'));
    $chartQuery = $mode === 'dual' ? ['mode' => 'dual', 'view' => 'chart'] : ['view' => 'chart'];
    $explanationQuery = $mode === 'dual'
        ? ['mode' => 'dual', 'view' => 'explanation']
        : ['view' => 'explanation'];
    $dualQuery = ['mode' => 'dual', 'view' => 'chart'];
@endphp

<nav class="aurora-horoscope-nav" aria-label="{{ __('horoscope.chart_tab') }}">
    <a href="{{ route('home') }}"
       class="aurora-horoscope-nav-item {{ request()->routeIs('home') ? 'is-active' : '' }}">
        <span class="aurora-horoscope-nav-icon">
            @include('templates.aurora.partials.horoscope-nav-icon', ['icon' => 'home'])
        </span>
        <span class="aurora-horoscope-nav-label">{{ __('public.aurora_nav_home') }}</span>
    </a>
    <a href="{{ route('horoscope.index', $explanationQuery) }}"
       class="aurora-horoscope-nav-item {{ $view === 'explanation' ? 'is-active' : '' }}">
        <span class="aurora-horoscope-nav-icon">
            @include('templates.aurora.partials.horoscope-nav-icon', ['icon' => 'explanation'])
        </span>
        <span class="aurora-horoscope-nav-label">{{ __('public.aurora_nav_explanation') }}</span>
    </a>
    <a href="{{ route('horoscope.index', $chartQuery) }}"
       class="aurora-horoscope-nav-item {{ $view === 'chart' && $mode !== 'dual' ? 'is-active' : '' }}">
        <span class="aurora-horoscope-nav-icon">
            @include('templates.aurora.partials.horoscope-nav-icon', ['icon' => 'chart'])
        </span>
        <span class="aurora-horoscope-nav-label">{{ __('public.aurora_nav_chart') }}</span>
    </a>
    <a href="{{ route('horoscope.index', $dualQuery) }}"
       class="aurora-horoscope-nav-item {{ $mode === 'dual' ? 'is-active' : '' }}">
        <span class="aurora-horoscope-nav-icon">
            @include('templates.aurora.partials.horoscope-nav-icon', ['icon' => 'relationships'])
        </span>
        <span class="aurora-horoscope-nav-label">{{ __('public.aurora_nav_relationships') }}</span>
    </a>
    <button type="button" id="openSimpleHamburgerBtn" class="aurora-horoscope-nav-item aurora-horoscope-nav-button">
        <span class="aurora-horoscope-nav-icon">
            @include('templates.aurora.partials.horoscope-nav-icon', ['icon' => 'menu'])
        </span>
        <span class="aurora-horoscope-nav-label">{{ __('app.menu') }}</span>
    </button>
</nav>
