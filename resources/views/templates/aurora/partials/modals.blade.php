<div id="auroraTopicModal" class="aurora-modal" role="dialog" aria-modal="true" aria-labelledby="auroraTopicModalTitle">
    <div class="aurora-modal-backdrop" data-aurora-modal-close></div>
    <div class="aurora-modal-panel">
        <button type="button" class="aurora-modal-close" data-aurora-modal-close aria-label="{{ __('public.close') }}">&times;</button>
        <h2 id="auroraTopicModalTitle" class="aurora-modal-title"></h2>
        <div id="auroraTopicModalBody" class="aurora-modal-body"></div>
    </div>
</div>

<div id="auroraReadMoreModal" class="aurora-modal" role="dialog" aria-modal="true" aria-labelledby="auroraReadMoreModalTitle">
    <div class="aurora-modal-backdrop" data-aurora-modal-close></div>
    <div class="aurora-modal-panel">
        <button type="button" class="aurora-modal-close" data-aurora-modal-close aria-label="{{ __('public.close') }}">&times;</button>
        <h2 id="auroraReadMoreModalTitle" class="aurora-modal-title">{{ __('public.aurora_read_more_title') }}</h2>
        <div id="auroraReadMoreModalBody" class="aurora-modal-body"></div>
    </div>
</div>

<div id="auroraOwnQuestionModal" class="aurora-modal" role="dialog" aria-modal="true" aria-labelledby="auroraOwnQuestionModalTitle">
    <div class="aurora-modal-backdrop" data-aurora-modal-close></div>
    <div class="aurora-modal-panel aurora-own-question-panel">
        <button type="button" class="aurora-modal-close" data-aurora-modal-close aria-label="{{ __('public.close') }}">&times;</button>
        <h2 id="auroraOwnQuestionModalTitle" class="aurora-modal-title">{{ __('public.aurora_own_question_title') }}</h2>
        <p class="aurora-modal-body" style="margin-bottom: 14px;">{{ __('public.aurora_own_question_intro') }}</p>

        @isset($hasBirthChart)
            @if ($hasBirthChart)
                <label for="messageBirthChartSelect">{{ __('public.select_birth_chart') }}</label>
                <select id="messageBirthChartSelect">
                    @foreach ($birthCharts as $chart)
                        <option value="{{ $chart->id }}" @selected($defaultBirthChartId === $chart->id)>{{ $chart->name }}</option>
                    @endforeach
                </select>
            @endif
        @endisset

        <label for="auroraOwnQuestionFocus">{{ __('horoscope.generation_focus_label_message') }}</label>
        <textarea id="auroraOwnQuestionFocus" placeholder="{{ __('public.focus_placeholder') }}"></textarea>

        <button type="button" class="aurora-generate-btn" id="auroraGenerateFromModalBtn">
            {{ __('public.generate_btn') }}
        </button>
    </div>
</div>
