@php
    use App\Support\ChartDisplaySettings;
@endphp

<div class="border-t pt-6">
    @include('partials.chart-display-settings-fields', [
        'chartDisplay' => ChartDisplaySettings::resolve($user),
        'hint' => __('horoscope.chart_display_user_hint'),
    ])
</div>
