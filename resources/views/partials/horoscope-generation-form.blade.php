@php
    $focusLabel = ($type ?? 'message') === 'explanation'
        ? __('horoscope.generation_focus_label_explanation')
        : __('horoscope.generation_focus_label_message');
    $submitLabel = ($type ?? 'message') === 'explanation'
        ? __('horoscope.generation_submit_explanation')
        : __('horoscope.generation_submit_message');
@endphp

<div class="rounded-lg border border-amber-200 bg-white/80 p-4 space-y-4 relative z-10" id="{{ $formId ?? 'horoscopeGenerationForm' }}">
    <div class="flex justify-center">
        <button type="button"
                id="{{ $submitBtnId ?? 'horoscopeGenerateBtn' }}"
                class="horoscope-generate-btn inline-flex items-center justify-center min-w-[12rem] px-6 py-3 rounded-md bg-indigo-600 text-white text-sm font-semibold shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            {{ $submitLabel }}
        </button>
    </div>

    <p class="text-sm text-amber-900/80">{{ __('horoscope.generation_form_intro') }}</p>

    <div>
        <label for="{{ $focusInputId ?? 'horoscopeUserFocus' }}" class="block text-sm font-medium text-amber-900 mb-1">
            {{ $focusLabel }}
        </label>
        <textarea id="{{ $focusInputId ?? 'horoscopeUserFocus' }}"
                  rows="4"
                  class="block w-full rounded-md border-amber-200 text-sm shadow-sm focus:border-amber-400 focus:ring-amber-400"
                  placeholder="{{ __('horoscope.generation_focus_placeholder') }}"></textarea>
    </div>

    <div>
        <span class="block text-sm font-medium text-amber-900 mb-2">{{ __('horoscope.generation_topics_label') }}</span>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach (\App\Support\HoroscopeTopicCatalog::ALL as $topic)
                <label class="inline-flex items-center gap-2 text-sm text-amber-950">
                    <input type="checkbox"
                           class="horoscope-topic-checkbox rounded border-amber-300 text-amber-600 focus:ring-amber-500"
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
        <label for="{{ $detailSelectId ?? 'horoscopeDetailLevel' }}" class="block text-sm font-medium text-amber-900 mb-1">
            {{ __('horoscope.generation_detail_label') }}
        </label>
        <select id="{{ $detailSelectId ?? 'horoscopeDetailLevel' }}"
                class="block w-full rounded-md border-amber-200 text-sm shadow-sm focus:border-amber-400 focus:ring-amber-400">
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
