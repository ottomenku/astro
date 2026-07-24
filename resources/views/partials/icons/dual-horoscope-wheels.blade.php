@props(['class' => 'h-5 w-5'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 '.$class]) }} aria-hidden="true">
    @include('partials.icons.horoscope-wheel', ['class' => 'h-4 w-4'])
    @include('partials.icons.horoscope-wheel', ['class' => 'h-4 w-4'])
</span>
