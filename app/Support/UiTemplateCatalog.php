<?php

namespace App\Support;

class UiTemplateCatalog
{
    public const CLASSIC = 'classic';

    public const AURORA = 'aurora';

    /** @return array<string, array{name: string, description: string, available: bool, coming_soon?: bool, preview_from?: string, preview_to?: string}> */
    public static function all(): array
    {
        return [
            self::CLASSIC => [
                'name' => __('app.template_classic_name'),
                'description' => __('app.template_classic_description'),
                'available' => true,
                'preview_from' => '#000000',
                'preview_to' => '#ca8a04',
            ],
            self::AURORA => [
                'name' => __('app.template_aurora_name'),
                'description' => __('app.template_aurora_description'),
                'available' => true,
                'preview_from' => '#04101f',
                'preview_to' => '#f1ba3f',
            ],
        ];
    }

    public static function normalize(?string $template): string
    {
        $template = strtolower(trim((string) $template));

        return array_key_exists($template, self::all())
            ? $template
            : self::CLASSIC;
    }

    public static function isAvailable(string $template): bool
    {
        $template = self::normalize($template);

        return (bool) (self::all()[$template]['available'] ?? false);
    }

    /** @return array<string, array{name: string, description: string, available: bool, coming_soon?: bool, preview_from?: string, preview_to?: string}> */
    public static function optionsForAdmin(): array
    {
        return self::all();
    }
}
