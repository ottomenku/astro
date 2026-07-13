<?php

namespace App\Services;

use App\Models\AstrologyEntry;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AstrologyKnowledgeService
{
    public function __construct(private readonly ChatService $chat) {}

    /**
     * @return array{title: string, answer: string, cached: bool}
     */
    public function resolve(User $user, string $type, string $key, string $locale, string $title): array
    {
        $type = Str::lower(trim($type));
        $key = trim($key);
        $locale = Str::lower(trim($locale));
        $title = trim($title) !== '' ? trim($title) : $key;

        $this->assertAllowed($type, $key);

        $entry = AstrologyEntry::query()
            ->where('type', $type)
            ->where('key', $key)
            ->where('locale', $locale)
            ->first();

        if ($entry) {
            return [
                'title' => $entry->title,
                'answer' => $entry->answer,
                'cached' => true,
            ];
        }

        $question = $this->buildQuestion($type, $title, $locale);
        $system = $this->buildSystemPrompt($locale);

        $result = $this->chat->sendWithSystem($user, $question, $system);
        $answer = trim((string) ($result['answer'] ?? ''));

        if ($answer === '') {
            throw new \RuntimeException('Az LM nem adott értelmezhető választ.');
        }

        AstrologyEntry::create([
            'type' => $type,
            'key' => $key,
            'locale' => $locale,
            'title' => $title,
            'question' => $question,
            'answer' => $answer,
            'created_by_user_id' => $user->id,
        ]);

        return [
            'title' => $title,
            'answer' => $answer,
            'cached' => false,
        ];
    }

    private function assertAllowed(string $type, string $key): void
    {
        $configKey = match ($type) {
            'sign' => 'signs',
            'planet' => 'planets',
            'fixed_star' => 'fixed_stars',
            default => null,
        };

        $allowed = $configKey !== null ? config("astrology.{$configKey}") : null;

        if (! is_array($allowed) || ! in_array($key, $allowed, true)) {
            throw new InvalidArgumentException('Ismeretlen asztrológiai elem.');
        }
    }

    private function buildQuestion(string $type, string $title, string $locale): string
    {
        if ($locale === 'en') {
            $subject = match ($type) {
                'sign' => "the zodiac sign {$title}",
                'planet' => "the planet {$title}",
                'fixed_star' => "the fixed star {$title}",
                default => $title,
            };

            return "What does {$subject} mean in astrology? Give a short summary (3–5 sentences): core qualities and what it symbolizes in a horoscope chart.";
        }

        $subject = match ($type) {
            'sign' => "a {$title} jegy",
            'planet' => "a {$title} bolygó",
            'fixed_star' => "a {$title} fix csillag",
            default => $title,
        };

        return "Mit jelent az asztrológiában {$subject}? Adj rövid összefoglalót (3–5 mondat): alap tulajdonságok és mit jelképez a horoszkópban.";
    }

    private function buildSystemPrompt(string $locale): string
    {
        if ($locale === 'en') {
            return implode("\n", [
                'You are an astrology encyclopedia assistant.',
                'Answer in English with a concise explanation in 3–5 sentences.',
                'First sentence: main qualities (element, modality, polarity where relevant).',
                'Following sentences: what it symbolizes in a horoscope.',
                'Do not greet the user or ask follow-up questions.',
            ]);
        }

        return implode("\n", [
            'Te egy asztrológiai enciklopédia asszisztens vagy.',
            'Válaszolj magyarul, röviden, 3–5 mondatban.',
            'Az első mondatban említsd a fő tulajdonságokat (elem, minőség, polaritás, ahol releváns).',
            'A további mondatokban írd le, mit jelképez a horoszkópban.',
            'Ne köszönj és ne kérdezz vissza.',
        ]);
    }
}
