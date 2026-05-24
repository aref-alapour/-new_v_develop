<?php

namespace EscapeZoom\Core\Core;

use EscapeZoom\Core\Database\CapsuleBoot;

final class Bootstrap
{
    private static bool $booted = false;

    private static bool $dataBooted = false;

    /**
     * Boot ONLY the Eloquent data layer (Capsule).
     *
     * Safe to call from the AJAX gateway dispatcher BEFORE WordPress is loaded:
     * the only requirement is that DB_* constants are defined (handled by
     * ez-ajax-gateway/secrets-bootstrap.php).
     */
    public static function bootDataLayerOnly(): void
    {
        if (self::$dataBooted) {
            return;
        }

        if (version_compare(PHP_VERSION, '8.2', '<')) {
            // Capsule 12 requires PHP 8.2+; bail silently — caller decides whether Eloquent is required.
            return;
        }

        try {
            CapsuleBoot::boot();
            self::$dataBooted = true;
        } catch (\Throwable $e) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            if (function_exists('error_log')) {
                error_log('EZ Core: data-only boot failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Full boot — data layer + WP-hook-driven module registration.
     *
     * Called from ez_core/bootstrap.php under the normal mu-plugin path,
     * AFTER WordPress core is fully loaded (add_action() available).
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::bootDataLayerOnly();

        if (!self::$dataBooted) {
            return;
        }

        if (!function_exists('add_action')) {
            // No WP context — modules register WP hooks, so skip them. Data layer is already booted.
            return;
        }

        try {
            ModuleRegistry::registerAll();
            self::$booted = true;
            Logger::info('EZ Core boot completed.');
        } catch (\Throwable $e) {
            Logger::error('Module registration failed: ' . $e->getMessage());
        }
    }
}
