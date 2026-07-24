@props(['class' => 'h-5 w-5'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'aria-hidden' => 'true']) }}>
    <circle cx="12" cy="12" r="9.5" stroke="currentColor" stroke-width="1.5" />
    <circle cx="12" cy="12" r="5.5" stroke="currentColor" stroke-width="1" opacity="0.55" />
    <g stroke="currentColor" stroke-width="0.9" opacity="0.75">
        <line x1="12" y1="12" x2="12" y2="2.5" />
        <line x1="12" y1="12" x2="20.2" y2="7" />
        <line x1="12" y1="12" x2="20.2" y2="17" />
        <line x1="12" y1="12" x2="12" y2="21.5" />
        <line x1="12" y1="12" x2="3.8" y2="17" />
        <line x1="12" y1="12" x2="3.8" y2="7" />
    </g>
    <path d="M12 2.5 L13.4 5.2 L12 6.1 L10.6 5.2 Z" fill="currentColor" opacity="0.9" />
    <circle cx="15.8" cy="8.6" r="1.1" fill="currentColor" />
    <circle cx="8.4" cy="14.2" r="0.9" fill="currentColor" opacity="0.85" />
</svg>
