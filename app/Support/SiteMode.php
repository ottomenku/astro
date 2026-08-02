<?php

namespace App\Support;

use Illuminate\Http\Request;

class SiteMode
{
    public const EXPERT = 'expert';

    public const PUBLIC = 'public';

    public static function resolve(?Request $request = null): string
    {
        $request ??= request();
        $host = strtolower($request->getHost());

        foreach (config('sites.expert_hosts', []) as $expertHost) {
            if ($host === strtolower($expertHost)) {
                return self::EXPERT;
            }
        }

        return self::PUBLIC;
    }

    public static function isExpert(?Request $request = null): bool
    {
        return self::resolve($request) === self::EXPERT;
    }

    public static function isPublic(?Request $request = null): bool
    {
        return ! self::isExpert($request);
    }

    public static function expertUrl(string $path = '/'): string
    {
        return rtrim((string) config('sites.expert_url'), '/').'/'.ltrim($path, '/');
    }

    public static function publicUrl(string $path = '/'): string
    {
        return rtrim((string) config('sites.public_url'), '/').'/'.ltrim($path, '/');
    }

    public static function canUseAdminUi(?\App\Models\User $user = null): bool
    {
        $user ??= auth()->user();

        return (bool) $user?->is_admin;
    }
}
