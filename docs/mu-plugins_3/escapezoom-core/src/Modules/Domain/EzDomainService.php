<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Domain;

/**
 * Lightweight domain awareness for EscapeZoom.
 *
 * - Knows about main and alias domains.
 * - Applies robots noindex/nofollow policy for alias (.co) domains.
 *
 * Canonical logic is expected to be handled by the active SEO configuration (e.g. SEO plugins or theme-level settings).
 */
final class EzDomainService
{
    public const MAIN_DOMAIN    = 'escapezoom.ir';
    public const ALIAS_DOMAINS  = [
        'escapezoom.co',
        'www.escapezoom.co',
    ];

    public static function register(): void
    {
        if (!defined('ABSPATH')) {
            return;
        }

        add_filter('wp_robots', [self::class, 'filterRobotsForAliases'], 5);
    }

    /**
     * If we are on an alias (.co) domain, force noindex,nofollow.
     *
     * @param array<string,bool> $robots
     * @return array<string,bool>
     */
    public static function filterRobotsForAliases(array $robots): array
    {
        $host = self::getCurrentHost();
        if ($host === '') {
            return $robots;
        }

        if (self::isAliasDomain($host)) {
            return [
                'noindex'  => true,
                'nofollow' => true,
            ];
        }

        return $robots;
    }

    public static function getCurrentHost(): string
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return (string) $_SERVER['HTTP_HOST'];
        }
        if (!empty($_SERVER['SERVER_NAME'])) {
            return (string) $_SERVER['SERVER_NAME'];
        }
        return '';
    }

    public static function isAliasDomain(string $host): bool
    {
        $host = strtolower($host);
        foreach (self::ALIAS_DOMAINS as $alias) {
            if ($host === strtolower($alias)) {
                return true;
            }
        }

        // Fallback: treat any host containing escapezoom.co as alias
        if (str_contains($host, 'escapezoom.co')) {
            return true;
        }

        return false;
    }
}

