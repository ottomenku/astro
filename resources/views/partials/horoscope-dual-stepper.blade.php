@php
    $side = $side ?? 'a';
    $inputPrefix = 'dualShift' . strtoupper($side);
@endphp

<div class="space-y-3">
    <div class="grid grid-cols-2 gap-3">
        <div>
            <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_minutes') }}</div>
            <div class="mt-1 flex items-center gap-2">
                <input
                    class="block w-20 border-gray-300 rounded-md shadow-sm text-sm"
                    type="number"
                    id="{{ $inputPrefix }}Minutes"
                    step="1"
                    min="1"
                    value="1"
                >
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="minutes"
                    data-shift-dir="-1"
                >-</button>
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="minutes"
                    data-shift-dir="1"
                >+</button>
            </div>
        </div>

        <div>
            <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_hours') }}</div>
            <div class="mt-1 flex items-center gap-2">
                <input
                    class="block w-20 border-gray-300 rounded-md shadow-sm text-sm"
                    type="number"
                    id="{{ $inputPrefix }}Hours"
                    step="1"
                    min="1"
                    value="1"
                >
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="hours"
                    data-shift-dir="-1"
                >-</button>
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="hours"
                    data-shift-dir="1"
                >+</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_days') }}</div>
            <div class="mt-1 flex items-center gap-2">
                <input
                    class="block w-20 border-gray-300 rounded-md shadow-sm text-sm"
                    type="number"
                    id="{{ $inputPrefix }}Days"
                    step="1"
                    min="1"
                    value="1"
                >
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="days"
                    data-shift-dir="-1"
                >-</button>
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="days"
                    data-shift-dir="1"
                >+</button>
            </div>
        </div>

        <div>
            <div class="text-sm text-gray-700 font-medium">{{ __('horoscope.step_months') }}</div>
            <div class="mt-1 flex items-center gap-2">
                <input
                    class="block w-20 border-gray-300 rounded-md shadow-sm text-sm"
                    type="number"
                    id="{{ $inputPrefix }}Months"
                    step="1"
                    min="1"
                    value="1"
                >
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="months"
                    data-shift-dir="-1"
                >-</button>
                <button
                    class="px-2 py-1 rounded border border-gray-300 text-sm"
                    type="button"
                    data-dual-shift-side="{{ $side }}"
                    data-shift-unit="months"
                    data-shift-dir="1"
                >+</button>
            </div>
        </div>
    </div>

    <div>
        <button
            class="px-3 py-1.5 rounded border border-gray-300 text-sm"
            type="button"
            data-dual-reset-now="{{ $side }}"
        >{{ __('horoscope.step_reset_now') }}</button>
    </div>

    <div class="text-xs text-gray-500">{{ __('horoscope.step_hint') }}</div>
</div>
