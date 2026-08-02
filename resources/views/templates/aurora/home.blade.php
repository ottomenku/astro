@component('templates.aurora.partials.layout', [
    'mode' => 'home',
    'pageTitle' => __('daily.page_title'),
    'period' => $period,
])
    @include('templates.aurora.partials.daily-experience', [
        'mode' => 'home',
        'period' => $period,
        'daily' => $daily,
        'error' => $error ?? null,
    ])
@endcomponent
