@php
    use App\Support\HoroscopePeriod;

    $isPersonal = ($mode ?? 'home') === 'personal';
    $periods = [HoroscopePeriod::DAILY, HoroscopePeriod::WEEKLY, HoroscopePeriod::MONTHLY];
    $topicMap = [
        'relationships' => [
            'class' => 'relationship',
            'icon' => 'icon-relationship.svg',
            'label' => __('daily.section_relationships'),
        ],
        'work' => [
            'class' => 'career',
            'icon' => 'icon-career.svg',
            'label' => __('public.aurora_section_work'),
        ],
        'money' => [
            'class' => 'money',
            'icon' => 'icon-money.svg',
            'label' => __('daily.section_money'),
        ],
        'health' => [
            'class' => 'health',
            'icon' => 'icon-health.svg',
            'label' => __('daily.section_health'),
        ],
    ];

    $mottoHtml = $daily?->motto
        ? '„'.e($daily->motto).'”'
        : e(__('public.aurora_default_motto_line1')).'<br>'.e(__('public.aurora_default_motto_line2'));

    $readMoreText = $daily?->explanation;
    if (! $readMoreText && $daily) {
        $readMoreParts = array_filter([
            $daily->summary,
            $daily->health ? __('daily.section_health')."\n".$daily->health : null,
            $daily->money ? __('daily.section_money')."\n".$daily->money : null,
            $daily->relationships ? __('daily.section_relationships')."\n".$daily->relationships : null,
            $daily->work ? __('public.aurora_section_work')."\n".$daily->work : null,
        ]);
        $readMoreText = implode("\n\n", $readMoreParts);
    }
@endphp

<section class="daily-reading" aria-labelledby="auroraMotto">
    <blockquote class="motto" id="auroraMotto">{!! $mottoHtml !!}</blockquote>

    <fieldset class="period-selector" id="auroraPeriodSelector">
        <legend class="sr-only">{{ __('daily.period_daily') }}</legend>
        @foreach ($periods as $periodOption)
            @if ($isPersonal)
                <label>
                    <input type="radio"
                           name="period"
                           value="{{ $periodOption }}"
                           @checked($period === $periodOption)>
                    <span>{{ __('daily.period_'.$periodOption) }}</span>
                </label>
            @else
                <a href="{{ route('home', ['period' => $periodOption]) }}#daily-horoscope"
                   class="aurora-period-option{{ $period === $periodOption ? ' is-active' : '' }}">
                    <span>{{ __('daily.period_'.$periodOption) }}</span>
                </a>
            @endif
        @endforeach
    </fieldset>

    <article class="message-card" id="daily-horoscope" tabindex="0" aria-label="{{ __('public.aurora_heading') }}">
        <div class="message-scroll" id="auroraMessageScroll">
            @if ($error ?? null)
                <p class="aurora-message-error">{{ $error }}</p>
            @elseif ($isPersonal)
                <p class="aurora-message-loading" id="auroraMessageLoading">{{ __('public.loading') }}</p>
                <p class="aurora-message-empty aurora-hidden-panel" id="auroraMessageEmpty">{{ __('daily.unpublished') }}</p>
                <p class="aurora-message-error aurora-hidden-panel" id="auroraMessageError"></p>
                <div class="aurora-hidden-panel" id="auroraMessageBody"></div>
            @elseif ($daily)
                <p>{{ $daily->summary }}</p>
            @else
                <p class="aurora-message-empty">{{ __('daily.unpublished') }}</p>
            @endif
        </div>

        <div class="message-actions">
            <button class="more-reading aurora-read-more-btn"
                    type="button"
                    aria-haspopup="dialog"
                    @disabled(! ($daily || $isPersonal))>
                {{ __('public.aurora_read_more') }}
            </button>

            @if ($isPersonal)
                <button type="button" class="inline-personal" id="auroraOwnQuestionBtn">
                    {{ __('public.aurora_own_question_btn') }}
                </button>
            @elseif (auth()->check())
                <a class="inline-personal" href="{{ route('message.index', ['period' => $period]) }}">
                    {{ __('public.aurora_own_question_btn') }}
                </a>
            @else
                <button type="button" class="inline-personal aurora-open-auth" data-auth-tab="login">
                    {{ __('public.aurora_own_question_btn') }}
                </button>
            @endif
        </div>
    </article>
</section>

<section class="topics" aria-labelledby="topics-title">
    <h2 id="topics-title">{{ __('public.aurora_topics_heading') }}</h2>
    <div class="topic-grid">
        @foreach (['relationships', 'work', 'money', 'health'] as $topicKey)
            @php $topic = $topicMap[$topicKey]; @endphp
            <button class="topic-button {{ $topic['class'] }} aurora-topic-btn"
                    type="button"
                    data-topic="{{ $topicKey }}"
                    data-topic-label="{{ $topic['label'] }}"
                    @disabled(! $isPersonal && ! $daily)>
                <img src="{{ asset('assets/aurora/'.$topic['icon']) }}" alt="" width="56" height="56">
                <span>{{ $topic['label'] }}</span>
            </button>
        @endforeach

        @if (auth()->check())
            <a class="personal-chart" href="{{ route('horoscope.index') }}">
                <img src="{{ asset('assets/aurora/horoscope-wheel.svg') }}" alt="">
                <span>{!! nl2br(e(__('public.aurora_personal_chart'))) !!}</span>
            </a>
        @else
            <button type="button" class="personal-chart aurora-open-auth" data-auth-tab="login" data-auth-redirect="{{ route('horoscope.index') }}">
                <img src="{{ asset('assets/aurora/horoscope-wheel.svg') }}" alt="">
                <span>{!! nl2br(e(__('public.aurora_personal_chart'))) !!}</span>
            </button>
        @endif
    </div>
</section>

@if ($isPersonal)
    <div class="aurora-hidden-panel" id="auroraTopicStore"></div>

    <div class="aurora-hidden-panel" id="auroraGenerationDefaults">
        @include('partials.horoscope-generation-form', [
            'type' => 'message',
            'variant' => 'personal',
            'formId' => 'personalMessageGenerationForm',
            'focusInputId' => 'personalMessageUserFocus',
            'detailSelectId' => 'personalMessageDetailLevel',
            'topicsGroupId' => 'personalMessageTopics',
            'hideSubmit' => true,
        ])
    </div>
@else
    <div class="aurora-hidden-panel"
         id="auroraTopicStore"
         data-read-more="{{ e($readMoreText ?? '') }}"
         data-topic-health="{{ e($daily?->health ?? '') }}"
         data-topic-money="{{ e($daily?->money ?? '') }}"
         data-topic-relationships="{{ e($daily?->relationships ?? '') }}"
         data-topic-work="{{ e($daily?->work ?? '') }}"></div>
@endif
