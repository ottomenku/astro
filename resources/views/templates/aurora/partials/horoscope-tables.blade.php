<section class="aurora-horoscope-section" id="auroraPositionsSection">
    <h2 class="aurora-horoscope-section-title">{{ __('public.aurora_planet_positions') }}</h2>
    <div class="aurora-table-tabs" role="tablist" aria-label="{{ __('horoscope.planet_positions') }}">
        <button type="button" class="aurora-table-tab is-active" data-pos-tab="natal">{{ __('horoscope.natal') }}</button>
        <button type="button" class="aurora-table-tab" data-pos-tab="transit">{{ __('horoscope.transit') }}</button>
    </div>
    <div id="auroraPositionsTable" class="aurora-table-host"></div>
</section>

<section class="aurora-horoscope-section" id="auroraAspectsSection">
    <h2 class="aurora-horoscope-section-title">{{ __('horoscope.aspects_tab') }}</h2>
    <p class="aurora-horoscope-section-hint">{{ __('horoscope.aspects_table_hint') }}</p>
    <div class="aurora-table-tabs" role="tablist" aria-label="{{ __('horoscope.aspects_tab') }}">
        <button type="button" class="aurora-table-tab is-active" data-aspect-filter="all">{{ __('public.aurora_aspect_filter_all') }}</button>
        <button type="button" class="aurora-table-tab" data-aspect-filter="harmonious">{{ __('public.aurora_aspect_filter_harmonious') }}</button>
        <button type="button" class="aurora-table-tab" data-aspect-filter="tense">{{ __('public.aurora_aspect_filter_tense') }}</button>
    </div>
    <div id="auroraAspectsTable" class="aurora-table-host"></div>
</section>
