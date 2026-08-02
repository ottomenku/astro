@component('templates.aurora.partials.layout', [
    'mode' => 'personal',
    'pageTitle' => __('public.page_title'),
    'hasBirthChart' => $hasBirthChart,
    'birthCharts' => $birthCharts,
    'defaultBirthChartId' => $defaultBirthChartId,
    'period' => $period,
])
    @include('templates.aurora.partials.daily-experience', [
        'mode' => 'personal',
        'period' => $period,
        'daily' => null,
        'error' => null,
        'hasBirthChart' => $hasBirthChart,
        'birthCharts' => $birthCharts,
        'defaultBirthChartId' => $defaultBirthChartId,
    ])
@endcomponent
