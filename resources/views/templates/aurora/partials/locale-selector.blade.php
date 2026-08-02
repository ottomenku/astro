@props(['id' => 'auroraHeaderLocale'])

<form method="POST" action="{{ route('locale.update') }}" class="aurora-locale-pill-form">
    @csrf
    <fieldset class="period-selector aurora-header-locale" id="{{ $id }}">
        <legend class="sr-only">{{ __('app.language') }}</legend>
        @foreach (['hu' => 'HU', 'en' => 'EN'] as $code => $label)
            <label>
                <input type="radio"
                       name="locale"
                       value="{{ $code }}"
                       @checked(app()->getLocale() === $code)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </fieldset>
</form>
