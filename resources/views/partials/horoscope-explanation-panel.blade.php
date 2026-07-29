<div class="rounded-xl border border-amber-200 bg-amber-50/40 p-5 sm:p-6 space-y-6" id="horoscopeExplanationPanel">
    @include('partials.horoscope-generation-form', [
        'type' => 'explanation',
        'formId' => 'horoscopeExplanationGenerationForm',
        'focusInputId' => 'horoscopeExplanationUserFocus',
        'detailSelectId' => 'horoscopeExplanationDetailLevel',
        'submitBtnId' => 'horoscopeExplanationGenerateBtn',
        'topicsGroupId' => 'horoscopeExplanationTopics',
    ])

    <p class="hidden text-xs text-center text-amber-800/70" id="horoscopeExplanationTokens"></p>
    <p class="hidden text-xs text-center text-amber-900/80" id="horoscopeExplanationDurationHint">{{ __('horoscope.explanation_duration_hint') }}</p>

    <div class="text-center space-y-2">
        <p class="hidden text-xs font-semibold uppercase tracking-wide text-amber-700" id="horoscopeExplanationBadge"></p>
        <p class="hidden text-xs text-amber-800/80" id="horoscopeExplanationMeta"></p>
    </div>

    <div class="hidden text-center text-sm text-gray-600" id="horoscopeExplanationLoading">{{ __('horoscope.explanation_loading') }}</div>
    <div class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" id="horoscopeExplanationError"></div>

    <div class="hidden rounded-lg border border-amber-200 bg-white/70 p-4" id="horoscopeExplanationContent">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-3" id="horoscopeExplanationTitle">{{ __('horoscope.explanation_title') }}</h2>
        <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line" id="horoscopeExplanationText"></p>
    </div>
</div>
