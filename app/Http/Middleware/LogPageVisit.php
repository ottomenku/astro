<?php

namespace App\Http\Middleware;

use App\Services\PageVisitRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageVisit
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldLog($request, $response)) {
            return;
        }

        try {
            app(PageVisitRecorder::class)->record($request, $response);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->routeIs('up')) {
            return false;
        }

        if ($response->getStatusCode() >= 500) {
            return false;
        }

        $path = $request->path();

        if (str_starts_with($path, 'build/') || str_starts_with($path, 'storage/')) {
            return false;
        }

        return true;
    }
}
