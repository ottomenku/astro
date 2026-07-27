<?php

namespace App\Support;

use App\Models\User;
use App\Models\ChartDisplayDefault;
use Illuminate\Validation\Rule;

class ChartDisplaySettings
{
    /** @var list<string> */
    public const NAMED_COLORS = [
        'black',
        'white',
        'gray',
        'silver',
        'red',
        'maroon',
        'orange',
        'gold',
        'yellow',
        'lime',
        'green',
        'olive',
        'cyan',
        'teal',
        'blue',
        'navy',
        'purple',
        'fuchsia',
        'magenta',
        'pink',
        'brown',
        'coral',
        'crimson',
        'indigo',
        'violet',
        'turquoise',
        'salmon',
        'tan',
        'khaki',
        'lavender',
    ];

    /** @var list<string> */
    public const ASPECT_KEYS = [
        'conjunction',
        'semi_sextile',
        'semi_square',
        'sextile',
        'square',
        'trine',
        'quincunx',
        'opposition',
    ];

    /**
     * @return list<array{name: string, angle: int, mark: string}>
     */
    public static function aspectCatalog(): array
    {
        return [
            ['name' => 'conjunction', 'angle' => 0, 'mark' => '☌'],
            ['name' => 'semi_sextile', 'angle' => 30, 'mark' => '⚺'],
            ['name' => 'semi_square', 'angle' => 45, 'mark' => '∠'],
            ['name' => 'sextile', 'angle' => 60, 'mark' => '△'],
            ['name' => 'square', 'angle' => 90, 'mark' => '□'],
            ['name' => 'trine', 'angle' => 120, 'mark' => '△'],
            ['name' => 'quincunx', 'angle' => 150, 'mark' => '⚻'],
            ['name' => 'opposition', 'angle' => 180, 'mark' => '☍'],
        ];
    }

    /** @var list<string> */
    public const OBJECT_KEYS = [
        'Sun',
        'Moon',
        'Mercury',
        'Venus',
        'Mars',
        'Jupiter',
        'Saturn',
        'Uranus',
        'Neptune',
        'Pluto',
        'True Node',
        'South Node',
        'ASC',
        'MC',
        'IC',
        'DSC',
    ];

    /**
     * @return array{aspects: array<string, array{enabled: bool, color: string}>, objects: array<string, array{enabled: bool, color: string}>}
     */
    public static function defaults(): array
    {
        return [
            'aspects' => [
                'conjunction' => ['enabled' => true, 'color' => 'gray'],
                'semi_sextile' => ['enabled' => false, 'color' => 'lavender'],
                'semi_square' => ['enabled' => false, 'color' => 'brown'],
                'sextile' => ['enabled' => true, 'color' => 'cyan'],
                'square' => ['enabled' => true, 'color' => 'red'],
                'trine' => ['enabled' => true, 'color' => 'green'],
                'quincunx' => ['enabled' => false, 'color' => 'olive'],
                'opposition' => ['enabled' => true, 'color' => 'orange'],
            ],
            'objects' => [
                'Sun' => ['enabled' => true, 'color' => 'gold'],
                'Moon' => ['enabled' => true, 'color' => 'silver'],
                'Mercury' => ['enabled' => true, 'color' => 'green'],
                'Venus' => ['enabled' => true, 'color' => 'blue'],
                'Mars' => ['enabled' => true, 'color' => 'red'],
                'Jupiter' => ['enabled' => true, 'color' => 'maroon'],
                'Saturn' => ['enabled' => true, 'color' => 'purple'],
                'Uranus' => ['enabled' => true, 'color' => 'teal'],
                'Neptune' => ['enabled' => true, 'color' => 'navy'],
                'Pluto' => ['enabled' => true, 'color' => 'indigo'],
                'True Node' => ['enabled' => true, 'color' => 'gray'],
                'South Node' => ['enabled' => true, 'color' => 'gray'],
                'ASC' => ['enabled' => true, 'color' => 'red'],
                'MC' => ['enabled' => true, 'color' => 'blue'],
                'IC' => ['enabled' => true, 'color' => 'green'],
                'DSC' => ['enabled' => true, 'color' => 'orange'],
            ],
        ];
    }

    /**
     * @return array{aspects: array<string, array{enabled: bool, color: string}>, objects: array<string, array{enabled: bool, color: string}>}
     */
    public static function adminDefaults(): array
    {
        try {
            $stored = ChartDisplayDefault::current()->settings;

            if (is_array($stored)) {
                return self::merge(self::defaults(), $stored);
            }
        } catch (\Throwable) {
            // Tábla még nem létezik (migráció előtt).
        }

        return self::defaults();
    }

    /**
     * @return array{aspects: array<string, array{enabled: bool, color: string}>, objects: array<string, array{enabled: bool, color: string}>}
     */
    public static function resolve(?User $user): array
    {
        $base = self::adminDefaults();
        $stored = $user?->chart_display_settings;

        if (! is_array($stored)) {
            return $base;
        }

        return self::merge($base, $stored);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{aspects: array<string, array{enabled: bool, color: string}>, objects: array<string, array{enabled: bool, color: string}>}
     */
    public static function persistAdminDefaults(array $input): array
    {
        $settings = self::fromRequest($input);
        ChartDisplayDefault::current()->update(['settings' => $settings]);

        return $settings;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{aspects: array<string, array{enabled: bool, color: string}>, objects: array<string, array{enabled: bool, color: string}>}
     */
    public static function merge(array $defaults, array $overrides): array
    {
        $merged = $defaults;

        foreach (['aspects', 'objects'] as $group) {
            if (! isset($overrides[$group]) || ! is_array($overrides[$group])) {
                continue;
            }

            foreach ($overrides[$group] as $key => $item) {
                if (! is_array($item) || ! isset($merged[$group][$key])) {
                    continue;
                }

                if (array_key_exists('enabled', $item)) {
                    $merged[$group][$key]['enabled'] = filter_var($item['enabled'], FILTER_VALIDATE_BOOLEAN);
                }

                if (isset($item['color']) && self::isNamedColor((string) $item['color'])) {
                    $merged[$group][$key]['color'] = strtolower((string) $item['color']);
                }
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{aspects: array<string, array{enabled: bool, color: string}>, objects: array<string, array{enabled: bool, color: string}>}
     */
    public static function fromRequest(array $input): array
    {
        $payload = ['aspects' => [], 'objects' => []];

        foreach (self::ASPECT_KEYS as $key) {
            if (! array_key_exists($key, $input['aspects'] ?? [])) {
                continue;
            }

            $item = $input['aspects'][$key];
            $payload['aspects'][$key] = [
                'enabled' => filter_var($item['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'color' => $item['color'] ?? self::defaults()['aspects'][$key]['color'],
            ];
        }

        foreach (self::OBJECT_KEYS as $key) {
            if (! array_key_exists($key, $input['objects'] ?? [])) {
                continue;
            }

            $item = $input['objects'][$key];
            $payload['objects'][$key] = [
                'enabled' => filter_var($item['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'color' => $item['color'] ?? self::defaults()['objects'][$key]['color'],
            ];
        }

        return self::merge(self::defaults(), $payload);
    }

    public static function isNamedColor(string $color): bool
    {
        return in_array(strtolower($color), self::NAMED_COLORS, true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        $rules = [];

        foreach (self::ASPECT_KEYS as $key) {
            $rules["chart_display.aspects.{$key}.enabled"] = ['sometimes', 'boolean'];
            $rules["chart_display.aspects.{$key}.color"] = ['required', 'string', Rule::in(self::NAMED_COLORS)];
        }

        foreach (self::OBJECT_KEYS as $key) {
            $rules["chart_display.objects.{$key}.enabled"] = ['sometimes', 'boolean'];
            $rules["chart_display.objects.{$key}.color"] = ['required', 'string', Rule::in(self::NAMED_COLORS)];
        }

        return $rules;
    }
}
