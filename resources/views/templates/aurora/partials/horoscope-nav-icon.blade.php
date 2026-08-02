@switch($icon)
    @case('home')
        <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
            <circle cx="14" cy="14" r="4.25" stroke="currentColor" stroke-width="1.45"/>
            <path d="M14 4.5v3.2M14 20.3v3.2M4.5 14h3.2M20.3 14h3.2M7.4 7.4l2.3 2.3M18.3 18.3l2.3 2.3M20.6 7.4l-2.3 2.3M9.7 18.3l-2.3 2.3" stroke="currentColor" stroke-width="1.45" stroke-linecap="round"/>
        </svg>
        @break
    @case('explanation')
        <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
            <rect x="5" y="7" width="18" height="16" rx="3.2" stroke="currentColor" stroke-width="1.45"/>
            <path d="M5 12.2h18" stroke="currentColor" stroke-width="1.45" stroke-linecap="round"/>
            <circle cx="10" cy="16.3" r="1" fill="currentColor"/>
            <circle cx="14" cy="16.3" r="1" fill="currentColor"/>
            <circle cx="18" cy="16.3" r="1" fill="currentColor"/>
        </svg>
        @break
    @case('chart')
        <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
            <circle cx="14" cy="14" r="9.5" stroke="currentColor" stroke-width="1.45"/>
            <path d="M14 8.4 15.15 12.95 19.85 12.95 15.95 15.8 17.1 20.35 14 17.55 10.9 20.35 12.05 15.8 8.15 12.95 12.85 12.95Z" fill="currentColor" stroke="none"/>
        </svg>
        @break
    @case('relationships')
        <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
            <circle cx="10.5" cy="14" r="6.8" stroke="currentColor" stroke-width="1.45"/>
            <circle cx="17.5" cy="14" r="6.8" stroke="currentColor" stroke-width="1.45"/>
            <path d="M10.5 11.7 11.15 13.45 10.5 15.2 9.85 13.45ZM8.65 13.45 10.5 12.55 12.35 13.45 10.5 14.35Z" fill="currentColor"/>
            <path d="M17.5 11.7 18.15 13.45 17.5 15.2 16.85 13.45ZM15.65 13.45 17.5 12.55 19.35 13.45 17.5 14.35Z" fill="currentColor"/>
        </svg>
        @break
    @case('menu')
        <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
            <path d="M6.5 10h15M6.5 14h15M6.5 18h15" stroke="currentColor" stroke-width="1.55" stroke-linecap="round"/>
        </svg>
        @break
@endswitch
