<?php

namespace App\Http\Middleware;

use App\Models\SiteVisitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedIp
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_admin) {
            return $next($request);
        }

        $ip = $request->ip();

        if ($ip && SiteVisitor::query()->where('ip_address', $ip)->where('is_banned', true)->exists()) {
            abort(403, 'Ez az IP-cím le van tiltva.');
        }

        return $next($request);
    }
}
