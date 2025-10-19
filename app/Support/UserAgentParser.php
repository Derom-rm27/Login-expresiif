<?php

declare(strict_types=1);

namespace App\Support;

final class UserAgentParser
{
    public static function parse(string $userAgent): array
    {
        return [
            'os' => self::parseOperatingSystem($userAgent),
            'browser' => self::parseBrowser($userAgent)
        ];
    }

    private static function parseOperatingSystem(string $userAgent): string
    {
        if (stripos($userAgent, 'Windows') !== false) return 'Windows';
        if (stripos($userAgent, 'Mac') !== false) return 'macOS';
        if (stripos($userAgent, 'Linux') !== false) return 'Linux';
        if (stripos($userAgent, 'Android') !== false) return 'Android';
        if (stripos($userAgent, 'iOS') !== false) return 'iOS';
        return 'Unknown OS';
    }

    private static function parseBrowser(string $userAgent): string
    {
        if (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edg') === false) return 'Chrome';
        if (stripos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) return 'Safari';
        if (stripos($userAgent, 'Edg') !== false) return 'Edge';
        if (stripos($userAgent, 'Opera') !== false) return 'Opera';
        if (stripos($userAgent, 'Brave') !== false) return 'Brave';
        return 'Unknown Browser';
    }
}