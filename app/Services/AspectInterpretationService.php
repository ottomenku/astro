<?php

namespace App\Services;

use App\Models\AstrologyEntry;
use App\Models\User;
use Illuminate\Support\Str;

class AspectInterpretationService
{
    public function __construct(
        private readonly ChatService $chat,
        private readonly AstrologyEntryClickService $clicks,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array{title: string, answer: string, cached: bool}
     */
    public function resolve(User $user, array $context, string $locale): array
    {
        $locale = Str::lower(trim($locale));
        $normalized = $this->normalizeContext($context);
        $cacheKey = $this->buildCacheKey($normalized);
        $title = $this->buildTitle($normalized, $locale);

        $entry = AstrologyEntry::query()
            ->where('type', 'aspect')
            ->where('key', $cacheKey)
            ->where('locale', $locale)
            ->first();

        if ($entry) {
            $this->clicks->recordClick($entry, $user);

            return [
                'title' => $entry->title,
                'answer' => $entry->answer,
                'cached' => true,
            ];
        }

        $question = $this->buildQuestion($normalized, $locale);
        $system = $this->buildSystemPrompt($normalized, $locale);

        $result = $this->chat->sendWithSystem($user, $question, $system);
        $answer = trim((string) ($result['answer'] ?? ''));

        if ($answer === '') {
            throw new \RuntimeException('Az LM nem adott értelmezhető választ.');
        }

        AstrologyEntry::create([
            'type' => 'aspect',
            'key' => $cacheKey,
            'locale' => $locale,
            'title' => $title,
            'question' => $question,
            'answer' => $answer,
            'created_by_user_id' => $user->id,
            ...$this->clicks->initialClickAttributes($user),
        ]);

        return [
            'title' => $title,
            'answer' => $answer,
            'cached' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function normalizeContext(array $context): array
    {
        $mode = (string) ($context['mode'] ?? 'natal');
        if (! in_array($mode, ['natal', 'transit', 'synastry'], true)) {
            throw new \InvalidArgumentException('Érvénytelen fényszög mód.');
        }

        $aspect = Str::lower(trim((string) ($context['aspect'] ?? '')));
        if ($aspect === '') {
            throw new \InvalidArgumentException('Hiányzó fényszög típus.');
        }

        return [
            'mode' => $mode,
            'aspect' => $aspect,
            'body1' => $this->normalizeBody((array) ($context['body1'] ?? [])),
            'body2' => $this->normalizeBody((array) ($context['body2'] ?? [])),
            'meta' => [
                'chart_a_id' => isset($context['meta']['chart_a_id']) ? (int) $context['meta']['chart_a_id'] : null,
                'chart_b_id' => isset($context['meta']['chart_b_id']) ? (int) $context['meta']['chart_b_id'] : null,
                'side_a_is_now' => (bool) ($context['meta']['side_a_is_now'] ?? false),
                'side_b_is_now' => (bool) ($context['meta']['side_b_is_now'] ?? false),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function normalizeBody(array $body): array
    {
        $name = trim((string) ($body['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Hiányzó égitest.');
        }

        $gender = $body['gender'] ?? null;
        if ($gender !== null && ! in_array($gender, ['male', 'female'], true)) {
            $gender = null;
        }

        return [
            'name' => $name,
            'sign' => trim((string) ($body['sign'] ?? '')),
            'house' => isset($body['house']) ? (int) $body['house'] : null,
            'sign_degree' => isset($body['sign_degree']) ? round((float) $body['sign_degree'], 2) : null,
            'owner' => trim((string) ($body['owner'] ?? '')),
            'gender' => $gender,
            'retrograde' => (bool) ($body['retrograde'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildCacheKey(array $context): string
    {
        $parts = [
            $context['mode'],
            $context['body1']['name'],
            $context['body2']['name'],
            $context['aspect'],
        ];

        if ($context['mode'] === 'transit') {
            $parts[] = 'a'.($context['meta']['chart_a_id'] ?? '0');
            $parts[] = 'b'.($context['meta']['chart_b_id'] ?? '0');
            $parts[] = ($context['meta']['side_a_is_now'] ? '1' : '0').($context['meta']['side_b_is_now'] ? '1' : '0');
        }

        if ($context['mode'] === 'synastry') {
            $parts[] = (string) ($context['meta']['chart_a_id'] ?? '0');
            $parts[] = (string) ($context['meta']['chart_b_id'] ?? '0');
        }

        return Str::limit(implode('|', $parts), 64, '');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildTitle(array $context, string $locale): string
    {
        $left = $this->bodyLabel($context['body1'], $locale);
        $right = $this->bodyLabel($context['body2'], $locale);
        $aspect = $this->aspectLabel($context['aspect'], $locale);

        return "{$left} {$aspect} {$right}";
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function bodyLabel(array $body, string $locale): string
    {
        $name = $this->translatePlanet($body['name'], $locale);
        $sign = $this->translateSign($body['sign'], $locale);
        $house = $body['house'] ? (string) $body['house'] : '?';
        $retrograde = ! empty($body['retrograde'])
            ? ($locale === 'hu' ? ', R' : ', R')
            : '';

        return "{$name} ({$sign}, {$house}. ház{$retrograde})";
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildQuestion(array $context, string $locale): string
    {
        $payload = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ($locale === 'en') {
            return match ($context['mode']) {
                'natal' => "Interpret this natal aspect for personality and inner dynamics. Use the JSON data (sign, house, gender, retrograde if present). Aspect data:\n{$payload}",
                'transit' => "One chart is the birth/personality chart and the other is the current moment (Now). Explain how this aspect affects the person now: supportive vs challenging themes, and what the period is good or not good for. Use retrograde flags when present. Data:\n{$payload}",
                'synastry' => "Both charts are birth charts of two people. Explain how this synastry aspect shapes the relationship: harmonious themes, friction, and likely situations. Use retrograde flags when present. Data:\n{$payload}",
            };
        }

        return match ($context['mode']) {
            'natal' => "Értelmezd ezt a natál fényszöget a személyiség és belső dinamika szempontjából. Használd a JSON adatokat (jegy, ház, nem, retrograde ha megvan). Fényszög adat:\n{$payload}",
            'transit' => "Az egyik képlet születési/személyiség horoszkóp, a másik a jelen pillanat (Now). Magyarázd el, hogyan hat ez a fényszög most az illetőre: mire jó vagy nem jó az adott időszakban, milyen témák erősödnek. Használd a retrograde mezőt, ha szerepel. Adat:\n{$payload}",
            'synastry' => "Mindkét képlet születési horoszkóp két embernek. Magyarázd el, hogyan hat ez a szinasztria fényszög a kapcsolatra: harmonikus és konfliktusos témák, várható helyzetek. Használd a retrograde mezőt, ha szerepel. Adat:\n{$payload}",
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildSystemPrompt(array $context, string $locale): string
    {
        if ($locale === 'en') {
            return implode("\n", [
                'You are an astrology interpretation assistant.',
                'Answer in 4–7 concise sentences, no greeting.',
                'Always reference the aspect type and the signs/houses provided.',
                'If retrograde is true for a body, mention its introspective, delayed, or inward-turned expression.',
                'If gender is provided for a chart owner, use it naturally (he/she) when describing that person.',
                'For natal aspects: focus on personality patterns.',
                'For transit/Now aspects: focus on current influence on the birth chart person.',
                'For synastry: focus on relationship dynamics between two people.',
            ]);
        }

        return implode("\n", [
            'Te asztrológiai fényszög értelmező asszisztens vagy.',
            'Válaszolj 4–7 tömör mondatban, köszönés nélkül.',
            'Mindig hivatkozz a fényszög típusára és a megadott jegyekre/házakra.',
            'Ha egy égitestnél retrograde igaz, említsd a visszaforduló, belső forduló vagy késleltetett kifejeződését.',
            'Ha a JSON-ban szerepel nem (male/female), természetesen használd (ő/férfi/nő) az illető leírásánál.',
            'Natál fényszögnél a személyiség mintázataira fókuszálj.',
            'Tranzit/Now fényszögnél a születési képletre gyakorolt aktuális hatást írd le.',
            'Szinasztriánál a két ember kapcsolati dinamikáját értelmezd.',
        ]);
    }

    private function aspectLabel(string $aspect, string $locale): string
    {
        $labels = [
            'hu' => [
                'conjunction' => 'együttállásban áll',
                'sextile' => 'szextilben áll',
                'square' => 'kvadrátban áll',
                'trine' => 'trigonban áll',
                'opposition' => 'szemben áll',
            ],
            'en' => [
                'conjunction' => 'conjunct',
                'sextile' => 'sextile',
                'square' => 'square',
                'trine' => 'trine',
                'opposition' => 'opposite',
            ],
        ];

        return $labels[$locale][$aspect] ?? $labels['en'][$aspect] ?? $aspect;
    }

    private function translatePlanet(string $name, string $locale): string
    {
        if ($locale !== 'hu') {
            return $name;
        }

        return (string) (__('horoscope.js.planets.'.$name, [], $locale) ?: $name);
    }

    private function translateSign(string $sign, string $locale): string
    {
        if ($sign === '' || $locale !== 'hu') {
            return $sign;
        }

        $signs = (array) __('horoscope.js.signs', [], $locale);
        $english = [
            'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
            'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces',
        ];
        $index = array_search($sign, $english, true);

        return $index === false ? $sign : (string) ($signs[$index] ?? $sign);
    }
}
