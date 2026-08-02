@include('templates.aurora.partials.horoscope-shell-open')
@include('partials.horoscope-app', array_merge(get_defined_vars(), ['auroraLayout' => true]))
@include('templates.aurora.partials.horoscope-shell-close')
