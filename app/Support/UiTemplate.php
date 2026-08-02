<?php

namespace App\Support;

use App\Models\SiteUiSetting;
use Illuminate\View\View;

class UiTemplate
{
    public static function active(): string
    {
        return SiteUiSetting::activeTemplate();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function render(string $page, array $data = [], ?string $legacyView = null): View
    {
        $template = self::active();
        $legacyView ??= self::legacyViewFor($page);
        $templateView = "templates.{$template}.{$page}";

        if (view()->exists($templateView)) {
            return view($templateView, $data);
        }

        return view($legacyView, $data);
    }

    public static function legacyViewFor(string $page): string
    {
        return match ($page) {
            'home' => 'home',
            'personal-message' => 'personal-message.index',
            'horoscope' => 'horoscope',
            default => $page,
        };
    }
}
