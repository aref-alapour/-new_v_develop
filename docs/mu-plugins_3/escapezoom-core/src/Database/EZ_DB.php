<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Database;

/**
 * Single entry point for wp_ez_* tables per database/schema.sql.
 *
 * No dependency on database/migrations or on post_id for products.
 * Table prefix from WordPress (e.g. wp_); EZ tables are prefix + 'ez_' + name.
 */
final class EZ_DB
{
    private const EZ_PREFIX = 'ez_';

    /**
     * Get full table name for an EZ table (e.g. 'products' → 'wp_ez_products').
     */
    public static function getTable(string $name): string
    {
        $prefix = isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : 'wp_';
        return $prefix . self::EZ_PREFIX . $name;
    }

    /**
     * Get WordPress table prefix (e.g. wp_).
     */
    public static function getPrefix(): string
    {
        return isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : 'wp_';
    }

    /**
     * Product URL from slug only (games are independent of wp_posts).
     * Path base: /room/{slug}/ (or as configured).
     */
    public static function productUrlFromSlug(string $slug, string $pathBase = '/room/'): string
    {
        return home_url(rtrim($pathBase, '/') . '/' . trim($slug, '/') . '/');
    }

    /**
     * Archive/category URL from landing_page_id (Gutenberg page).
     * Returns permalink of the page or empty string if invalid.
     */
    public static function archiveUrlFromLandingPageId(?int $landing_page_id): string
    {
        if ($landing_page_id === null || $landing_page_id <= 0) {
            return '';
        }
        $url = get_permalink($landing_page_id);
        return is_string($url) ? $url : '';
    }
}
