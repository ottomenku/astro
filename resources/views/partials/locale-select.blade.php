@props([
    'id' => 'localeSelect',
    'selectClass' => 'px-2 py-2 rounded border border-gray-300 text-sm bg-white',
])

<form method="POST" action="{{ route('locale.update') }}" {{ $attributes->merge(['class' => 'inline-flex']) }}>
    @csrf
    <label for="{{ $id }}" class="sr-only">{{ __('app.language') }}</label>
    <select
        name="locale"
        id="{{ $id }}"
        class="locale-select {{ $selectClass }}"
        onchange="this.form.submit()"
    >
        <option value="hu" @selected(app()->getLocale() === 'hu')>HU</option>
        <option value="en" @selected(app()->getLocale() === 'en')>EN</option>
    </select>
</form>

@once
    <style>
        select.locale-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none !important;
            cursor: pointer;
            text-align: center;
            text-align-last: center;
        }

        select.locale-select::-ms-expand {
            display: none;
        }
    </style>
@endonce
