<?php

namespace App\Services;

use App\Models\PageVisit;
use App\Support\GeoHeaderParser;
use App\Support\UserAgentInspector;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PageVisitRecorder
{
    public function __construct(
        private readonly UserAgentInspector $userAgentInspector,
        private readonly GeoHeaderParser $geoHeaderParser,
    ) {}

    public function record(Request $request, Response $response): void
    {
        $ip = $request->ip();
        if (! $ip) {
            return;
        }

        $user = $request->user();
        $route = $request->route();
        $routeName = $route?->getName();
        $userAgent = (string) $request->userAgent();
        $agent = $this->userAgentInspector->inspect($userAgent);
        $geo = $this->geoHeaderParser->fromRequestHeaders($request->headers->all());

        PageVisit::query()->create([
            'visited_at' => now(),
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => $ip,
            'route_name' => $routeName,
            'path' => '/'.ltrim($request->path(), '/'),
            'page_label' => $this->pageLabel($routeName, $request->path()),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'is_bot' => $agent['is_bot'],
            'bot_name' => $agent['bot_name'],
            'visitor_type' => $agent['visitor_type'],
            'user_agent' => $userAgent !== '' ? $userAgent : null,
            'referer' => $this->normalizeReferer($request->headers->get('referer')),
            'accept_language' => $this->normalizeHeader($request->headers->get('accept-language')),
            'device_type' => $agent['device_type'],
            'browser' => $agent['browser'],
            'browser_version' => $agent['browser_version'],
            'platform' => $agent['platform'],
            'platform_version' => $agent['platform_version'],
            'country_code' => $geo['country_code'],
            'country_name' => $geo['country_name'],
            'region' => $geo['region'],
            'city' => $geo['city'],
            'timezone' => $geo['timezone'],
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
        ]);
    }

    private function pageLabel(?string $routeName, string $path): string
    {
        $labels = [
            'home' => 'Nyitólap',
            'login' => 'Belépés',
            'register' => 'Regisztráció',
            'dashboard' => 'Dashboard',
            'horoscope.index' => 'Horoszkóp',
            'chat.index' => 'Chat',
            'profile.edit' => 'Profil',
            'profile.horoscope.edit' => 'Profil – horoszkóp beállítások',
            'profile.daily-horoscope.edit' => 'Profil – napi horoszkóp',
            'profile.birth-charts.index' => 'Profil – születési képletek',
            'profile.birth-charts.create' => 'Profil – új születési képlet',
            'profile.birth-charts.edit' => 'Profil – születési képlet szerkesztés',
            'admin.visitors.index' => 'Admin – látogatók (IP)',
            'admin.page-visits.logs' => 'Admin – oldalmegtekintések',
            'admin.page-visits.summary' => 'Admin – látogatás összesítő',
            'admin.users.index' => 'Admin – felhasználók',
            'admin.users.edit' => 'Admin – felhasználó szerkesztés',
            'admin.conversations.index' => 'Admin – konverzációk',
            'admin.conversations.show' => 'Admin – konverzáció részletek',
            'admin.daily-horoscope.edit' => 'Admin – napi horoszkóp',
        ];

        if ($routeName && isset($labels[$routeName])) {
            return $labels[$routeName];
        }

        if ($routeName) {
            return $routeName;
        }

        return '/'.ltrim($path, '/');
    }

    private function normalizeReferer(?string $referer): ?string
    {
        $referer = trim((string) $referer);

        return $referer === '' ? null : mb_substr($referer, 0, 2048);
    }

    private function normalizeHeader(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, 255);
    }
}
