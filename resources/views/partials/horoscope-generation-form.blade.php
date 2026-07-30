@php
    $focusLabel = ($type ?? 'message') === 'explanation'
        ? __('horoscope.generation_focus_label_explanation')
        : __('horoscope.generation_focus_label_message');
    $submitLabel = ($type ?? 'message') === 'explanation'
        ? __('horoscope.generation_submit_explanation')
        : __('horoscope.generation_submit_message');
    $isPersonal = ($variant ?? '') === 'personal';
    $formShellClass = $isPersonal
        ? 'rounded-lg border border-indigo-400/30 bg-white/5 p-4 space-y-4'
        : 'rounded-lg border border-amber-200 bg-white/80 p-4 space-y-4 relative z-10';
    $introClass = $isPersonal ? 'text-sm text-white/80' : 'text-sm text-amber-900/80';
    $labelClass = $isPersonal ? 'block text-sm font-medium text-white mb-1' : 'block text-sm font-medium text-amber-900 mb-1';
    $topicsLabelClass = $isPersonal ? 'block text-sm font-medium text-white mb-2' : 'block text-sm font-medium text-amber-900 mb-2';
    $topicItemClass = $isPersonal ? 'inline-flex items-center gap-2 text-sm text-white' : 'inline-flex items-center gap-2 text-sm text-amber-950';
    $fieldClass = $isPersonal
        ? 'pm-text-field block w-full rounded-md border border-indigo-400/45 bg-white/[0.06] text-white text-sm px-3 py-2 shadow-sm focus:border-indigo-300 focus:ring-indigo-300 placeholder:text-gray-500'
        : 'block w-full rounded-md border-amber-200 text-sm shadow-sm focus:border-amber-400 focus:ring-amber-400';
    $selectClass = $isPersonal
        ? 'pm-select-field block w-full rounded-md border border-indigo-400/45 bg-white/[0.06] text-sm px-3 py-2 shadow-sm focus:border-indigo-300 focus:ring-indigo-300'
        : 'block w-full rounded-md border-amber-200 text-sm shadow-sm focus:border-amber-400 focus:ring-amber-400';
    $focusPlaceholder = $isPersonal
        ? __('public.focus_placeholder')
        : __('horoscope.generation_focus_placeholder');
    $checkboxClass = $isPersonal
        ? 'horoscope-topic-checkbox rounded border-indigo-300/50 bg-white/[0.06] text-indigo-400 focus:ring-indigo-400'
        : 'horoscope-topic-checkbox rounded border-amber-300 text-amber-600 focus:ring-amber-500';
@endphp

<div class="{{ $formShellClass }}" id="{{ $formId ?? 'horoscopeGenerationForm' }}">
    @if (empty($hideSubmit))
        <div class="flex justify-center">
            <button type="button"
                    id="{{ $submitBtnId ?? 'horoscopeGenerateBtn' }}"
                    class="horoscope-generate-btn inline-flex items-center justify-center min-w-[12rem] px-6 py-3 rounded-md bg-indigo-600 text-white text-sm font-semibold shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ $submitLabel }}
            </button>
        </div>
    @endif

    <p class="{{ $introClass }}">{{ __('horoscope.generation_form_intro') }}</p>

    <div>
        <label for="{{ $focusInputId ?? 'horoscopeUserFocus' }}" class="{{ $labelClass }}">
            {{ $focusLabel }}
        </label>
        <textarea id="{{ $focusInputId ?? 'horoscopeUserFocus' }}"
                  rows="4"
                  class="{{ $fieldClass }}"
                  placeholder="{{ $focusPlaceholder }}"></textarea>
    </div>

    <div>
        <span class="{{ $topicsLabelClass }}">{{ __('horoscope.generation_topics_label') }}</span>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach (\App\Support\HoroscopeTopicCatalog::ALL as $topic)
                <label class="{{ $topicItemClass }}">
                    <input type="checkbox"
                           class="{{ $checkboxClass }}"
                           data-topic="{{ $topic }}"
                           data-topic-group="{{ $topicsGroupId ?? 'horoscopeTopics' }}"
                           value="{{ $topic }}"
                           checked>
                    {{ \App\Support\HoroscopeTopicCatalog::label($topic, app()->getLocale()) }}
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label for="{{ $detailSelectId ?? 'horoscopeDetailLevel' }}" class="{{ $labelClass }}">
            {{ __('horoscope.generation_detail_label') }}
        </label>
        <select id="{{ $detailSelectId ?? 'horoscopeDetailLevel' }}"
                class="{{ $selectClass }}">
            <option value="short">{{ __('horoscope.generation_detail_short') }}</option>
            <option value="normal" selected>{{ __('horoscope.generation_detail_normal') }}</option>
            <option value="detailed">{{ __('horoscope.generation_detail_detailed') }}</option>
        </select>
    </div>

    <style>
        .horoscope-generate-btn {
            background-color: rgb(79 70 229) !important;
            color: rgb(255 255 255) !important;
            border: 1px solid rgb(67 56 202);
        }

        .horoscope-generate-btn:hover {
            background-color: rgb(67 56 202) !important;
        }
    </style>
</div>
