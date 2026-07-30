<?php

namespace App\Http\Middleware;

use App\Support\SiteMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectExpertRoot
{
    public function handle(Request $request, Closure $next): Response
    {
        if (SiteMode::isExpert($request) && $request->is('/') && ! $request->user()?->is_admin) {
            return redirect(SiteMode::publicUrl('/'));
        }

        return $next($request);
    }
}
