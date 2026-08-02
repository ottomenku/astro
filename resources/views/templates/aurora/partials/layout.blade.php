<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#061426">
    <title>{{ $pageTitle ?? __('daily.page_title') }} — AstroMotto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/aurora-public.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="starfield" aria-hidden="true"></div>
<main class="app-shell">
    @include('templates.aurora.partials.site-header', [
        'mode' => $mode ?? 'home',
    ])

    {{ $slot }}

</main>

@include('templates.aurora.partials.modals', [
    'hasBirthChart' => $hasBirthChart ?? false,
    'birthCharts' => $birthCharts ?? collect(),
    'defaultBirthChartId' => $defaultBirthChartId ?? null,
])
@include('partials.auth-modal', ['open' => request('auth')])
@include('partials.birth-chart-required-modal')

@if (($mode ?? 'home') === 'personal')
    @include('partials.simple-hamburger-menu')
@endif

@include('templates.aurora.partials.scripts', [
    'mode' => $mode ?? 'home',
    'hasBirthChart' => $hasBirthChart ?? false,
    'period' => $period ?? \App\Support\HoroscopePeriod::DAILY,
])

</body>
</html>
