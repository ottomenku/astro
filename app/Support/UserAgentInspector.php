<?php

namespace App\Support;

class UserAgentInspector
{
    /** @var array<int, string> */
    private const BOT_PATTERNS = [
        'googlebot' => 'Googlebot',
        'bingbot' => 'Bingbot',
        'slurp' => 'Yahoo Slurp',
        'duckduckbot' => 'DuckDuckBot',
        'baiduspider' => 'Baidu Spider',
        'yandexbot' => 'YandexBot',
        'facebookexternalhit' => 'Facebook',
        'facebot' => 'Facebook',
        'twitterbot' => 'Twitterbot',
        'linkedinbot' => 'LinkedInBot',
        'whatsapp' => 'WhatsApp',
        'telegrambot' => 'TelegramBot',
        'discordbot' => 'DiscordBot',
        'applebot' => 'Applebot',
        'semrushbot' => 'SemrushBot',
        'ahrefsbot' => 'AhrefsBot',
        'mj12bot' => 'MJ12bot',
        'petalbot' => 'PetalBot',
        'gptbot' => 'GPTBot',
        'claudebot' => 'ClaudeBot',
        'crawler' => 'Generic crawler',
        'spider' => 'Generic spider',
        'bot/' => 'Generic bot',
        'bot ' => 'Generic bot',
        'headless' => 'Headless browser',
        'phantomjs' => 'PhantomJS',
        'selenium' => 'Selenium',
        'lighthouse' => 'Lighthouse',
        'curl/' => 'cURL',
        'wget' => 'Wget',
        'python-requests' => 'Python requests',
        'go-http-client' => 'Go HTTP client',
        'java/' => 'Java HTTP client',
        'libwww-perl' => 'Perl LWP',
    ];

    /**
     * @return array{
     *     is_bot: bool,
     *     bot_name: ?string,
     *     visitor_type: string,
     *     device_type: ?string,
     *     browser: ?string,
     *     browser_version: ?string,
     *     platform: ?string,
     *     platform_version: ?string
     * }
     */
    public function inspect(?string $userAgent): array
    {
        $userAgent = trim((string) $userAgent);

        if ($userAgent === '') {
            return [
                'is_bot' => false,
                'bot_name' => null,
                'visitor_type' => 'unknown',
                'device_type' => null,
                'browser' => null,
                'browser_version' => null,
                'platform' => null,
                'platform_version' => null,
            ];
        }

        $lower = strtolower($userAgent);

        foreach (self::BOT_PATTERNS as $pattern => $name) {
            if (str_contains($lower, $pattern)) {
                return [
                    'is_bot' => true,
                    'bot_name' => $name,
                    'visitor_type' => 'bot',
                    'device_type' => 'bot',
                    'browser' => null,
                    'browser_version' => null,
                    'platform' => null,
                    'platform_version' => null,
                ];
            }
        }

        return [
            'is_bot' => false,
            'bot_name' => null,
            'visitor_type' => 'human',
            'device_type' => $this->detectDeviceType($lower),
            'browser' => $this->detectBrowser($userAgent),
            'browser_version' => $this->detectBrowserVersion($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'platform_version' => $this->detectPlatformVersion($userAgent),
        ];
    }

    private function detectDeviceType(string $lower): string
    {
        if (str_contains($lower, 'ipad') || str_contains($lower, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($lower, 'mobile') || str_contains($lower, 'iphone') || str_contains($lower, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(string $userAgent): ?string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') && ! str_contains($userAgent, 'Edg/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/') => 'Safari',
            default => null,
        };
    }

    private function detectBrowserVersion(string $userAgent): ?string
    {
        $patterns = [
            'Edg/' => 'Edg\/([\d.]+)',
            'OPR/' => 'OPR\/([\d.]+)',
            'Chrome/' => 'Chrome\/([\d.]+)',
            'Firefox/' => 'Firefox\/([\d.]+)',
            'Version/' => 'Version\/([\d.]+)',
        ];

        foreach ($patterns as $needle => $pattern) {
            if (! str_contains($userAgent, $needle)) {
                continue;
            }

            if (preg_match('/'.$pattern.'/', $userAgent, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function detectPlatform(string $userAgent): ?string
    {
        return match (true) {
            str_contains($userAgent, 'Windows NT') => 'Windows',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => null,
        };
    }

    private function detectPlatformVersion(string $userAgent): ?string
    {
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            return $matches[1];
        }

        if (preg_match('/Mac OS X ([0-9_\.]+)/', $userAgent, $matches)) {
            return str_replace('_', '.', $matches[1]);
        }

        if (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            return $matches[1];
        }

        if (preg_match('/OS ([0-9_]+) like Mac OS X/', $userAgent, $matches)) {
            return str_replace('_', '.', $matches[1]);
        }

        return null;
    }
}
