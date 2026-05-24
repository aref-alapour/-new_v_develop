<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

/**
 * WordPress-prefixed table names for EZ AJAX gateway store tables.
 */
final class AjaxGatewaySchema
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

    public static function noncesTable(): string
    {
        return self::prefix() . 'ez_ajax_nonces';
    }

    public static function rateTable(): string
    {
        return self::prefix() . 'ez_ajax_rate';
    }
}
