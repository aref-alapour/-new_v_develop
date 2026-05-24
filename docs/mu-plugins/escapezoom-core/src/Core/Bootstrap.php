<?php

namespace EscapeZoom\Core\Core;

use EscapeZoom\Core\Database\CapsuleBoot;

final class Bootstrap
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (!defined('EZ_CORE_BOOTED')) {
            define('EZ_CORE_BOOTED', false);
        }

        if (version_compare(PHP_VERSION, '8.2', '<')) {
            Logger::warning('PHP 8.2+ is required. Current: ' . PHP_VERSION);
            return;
        }

        try {
            CapsuleBoot::boot();
            ModuleRegistry::registerAll();
            self::$booted = true;
            if (!defined('EZ_CORE_RUNTIME_BOOTED')) {
                define('EZ_CORE_RUNTIME_BOOTED', true);
            }
            Logger::info('Boot completed.');
        } catch (\Throwable $e) {
            Logger::error('Boot exception: ' . $e->getMessage());
        }
    }
}
