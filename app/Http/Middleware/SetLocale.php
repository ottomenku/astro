<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'hu'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);

        if (! $request->session()->has('locale')) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $sessionLocale = $request->session()->get('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, self::SUPPORTED, true)) {
            return $sessionLocale;
        }

        $cookieLocale = $request->cookie('locale');
        if (is_string($cookieLocale) && in_array($cookieLocale, self::SUPPORTED, true)) {
            return $cookieLocale;
        }

        return $request->getPreferredLanguage(self::SUPPORTED) ?? 'en';
    }
}
