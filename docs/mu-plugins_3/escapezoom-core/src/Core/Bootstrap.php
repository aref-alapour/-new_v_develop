<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Core;

use EscapeZoom\Core\Database\CapsuleBoot;

/**
 * Core bootstrap: autoload and Database (Eloquent).
 * No theme dependency; logic engine only.
 */
final class Bootstrap
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (!self::loadAutoloader()) {
            return;
        }
        CapsuleBoot::boot();
        if (!defined('EZ_CORE_BOOTED')) {
            define('EZ_CORE_BOOTED', true);
        }
        self::$booted = true;
    }

    private static function loadAutoloader(): bool
    {
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_file($autoload)) {
            return false;
        }
        require_once $autoload;
        return true;
    }
}
