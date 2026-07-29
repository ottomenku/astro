<?php

namespace App\Support;

class HoroscopeTopicCatalog
{
    public const HEALTH = 'health';

    public const MONEY = 'money';

    public const RELATIONSHIPS = 'relationships';

    public const WORK = 'work';

    public const ALL = [
        self::HEALTH,
        self::MONEY,
        self::RELATIONSHIPS,
        self::WORK,
    ];

    /** @var array<string, list<string>> */
    private const PLANETS = [
        self::HEALTH => ['Sun', 'Moon', 'Mars', 'Neptune', 'Saturn'],
        self::MONEY => ['Venus', 'Jupiter', 'Saturn', 'Pluto', 'Mercury'],
        self::RELATIONSHIPS => ['Venus', 'Mars', 'Moon', 'Sun', 'Neptune'],
        self::WORK => ['Sun', 'Mars', 'Saturn', 'Mercury', 'Jupiter'],
    ];

    /**
     * @param  list<string>|null  $topics
     * @return list<string>
     */
    public static function normalizeTopics(?array $topics): array
    {
        if ($topics === null || $topics === []) {
            return self::ALL;
        }

        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $topic): ?string => is_string($topic) && in_array($topic, self::ALL, true) ? $topic : null,
            $topics,
        ))));

        return $normalized !== [] ? $normalized : self::ALL;
    }

    /**
     * @param  list<string>  $topics
     * @return list<string>
     */
    public static function planetsForTopics(array $topics): array
    {
        $planets = [];

        foreach ($topics as $topic) {
            foreach (self::PLANETS[$topic] ?? [] as $planet) {
                $planets[$planet] = true;
            }
        }

        return array_keys($planets);
    }

    /**
     * @param  list<string>  $topics
     */
    public static function bodyMatchesTopics(string $bodyId, array $topics): bool
    {
        $name = self::bodyIdToPlanetName($bodyId);

        return in_array($name, self::planetsForTopics($topics), true);
    }

    public static function bodyIdToPlanetName(string $bodyId): string
    {
        return match ($bodyId) {
            'true_node' => 'True Node',
            'south_node' => 'South Node',
            default => ucwords(str_replace('_', ' ', $bodyId)),
        };
    }

    public static function label(string $topic, string $locale): string
    {
        return match ($topic) {
            self::HEALTH => __('daily.section_health', [], $locale),
            self::MONEY => __('daily.section_money', [], $locale),
            self::RELATIONSHIPS => __('daily.section_relationships', [], $locale),
            self::WORK => __('daily.section_work', [], $locale),
            default => $topic,
        };
    }
}
