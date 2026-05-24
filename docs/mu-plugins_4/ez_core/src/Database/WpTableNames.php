<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Database;

/**
 * WordPress table names with dynamic prefix (gateway pre-WP and normal WP).
 */
final class WpTableNames
{
    public static function prefix(): string
    {
        if (defined('EZ_AJAX_TABLE_PREFIX') && is_string(EZ_AJAX_TABLE_PREFIX) && EZ_AJAX_TABLE_PREFIX !== '') {
            return EZ_AJAX_TABLE_PREFIX;
        }

        global $wpdb;
        if (isset($wpdb) && is_object($wpdb) && property_exists($wpdb, 'prefix') && is_string($wpdb->prefix) && $wpdb->prefix !== '') {
            return $wpdb->prefix;
        }

        return 'wp_';
    }

    public static function terms(): string
    {
        return self::prefix() . 'terms';
    }

    public static function termTaxonomy(): string
    {
        return self::prefix() . 'term_taxonomy';
    }

    public static function termMeta(): string
    {
        return self::prefix() . 'termmeta';
    }

    public static function posts(): string
    {
        return self::prefix() . 'posts';
    }

    public static function postMeta(): string
    {
        return self::prefix() . 'postmeta';
    }

    public static function options(): string
    {
        return self::prefix() . 'options';
    }
}
