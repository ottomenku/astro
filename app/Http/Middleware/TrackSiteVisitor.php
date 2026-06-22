<?php

namespace App\Http\Middleware;

use App\Models\SiteVisitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisitor
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        if ($request->routeIs('up')) {
            return $next($request);
        }

        $ip = $request->ip();

        if (! $ip) {
            return $next($request);
        }

        $now = now();
        $user = $request->user();

        $visitor = SiteVisitor::query()->firstOrCreate(
            ['ip_address' => $ip],
            [
                'visit_count' => 0,
                'horoscope_views' => 0,
                'chat_views' => 0,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
            ]
        );

        $updates = [
            'visit_count' => $visitor->visit_count + 1,
            'last_seen_at' => $now,
        ];

        if ($user) {
            $updates['user_id'] = $user->id;
            $updates['user_name'] = $user->name;
        }

        if ($request->routeIs('horoscope.index')) {
            $updates['horoscope_views'] = $visitor->horoscope_views + 1;
        }

        if ($request->routeIs('chat.index')) {
            $updates['chat_views'] = $visitor->chat_views + 1;
        }

        if ($visitor->first_seen_at === null) {
            $updates['first_seen_at'] = $now;
        }

        $visitor->update($updates);

        return $next($request);
    }
}
