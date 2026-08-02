<?php

namespace App\Models;

use App\Support\UiTemplateCatalog;
use Illuminate\Database\Eloquent\Model;

class SiteUiSetting extends Model
{
    protected $fillable = [
        'active_template',
    ];

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting) {
            return $setting;
        }

        return static::query()->create([
            'active_template' => UiTemplateCatalog::CLASSIC,
        ]);
    }

    public static function activeTemplate(): string
    {
        return UiTemplateCatalog::normalize(static::current()->active_template);
    }

    public function activate(string $template): void
    {
        $template = UiTemplateCatalog::normalize($template);

        if (! UiTemplateCatalog::isAvailable($template)) {
            abort(422, 'Template is not available.');
        }

        $this->update([
            'active_template' => $template,
        ]);
    }
}
