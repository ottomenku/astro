<?php

namespace App\Http\Middleware;

use App\Support\SiteMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExpertSiteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! SiteMode::isExpert($request)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user?->is_admin) {
            return $next($request);
        }

        if ($user) {
            return redirect(SiteMode::publicUrl('/uzenet'));
        }

        return redirect(SiteMode::publicUrl('/'));
    }
}
