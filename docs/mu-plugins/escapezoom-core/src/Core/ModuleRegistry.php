<?php

namespace EscapeZoom\Core\Core;

final class ModuleRegistry
{
    /**
     * @return array<int, class-string>
     */
    private static function modules(): array
    {
        return [
            \EscapeZoom\Core\Modules\Core\CoreModule::class,
            \EscapeZoom\Core\Modules\WpData\WpDataModule::class,
            \EscapeZoom\Core\Modules\Commerce\CommerceModule::class,
        ];
    }

    public static function registerAll(): void
    {
        foreach (self::modules() as $moduleClass) {
            if (!class_exists($moduleClass)) {
                Logger::warning('Module class missing: ' . $moduleClass);
                continue;
            }

            if (!method_exists($moduleClass, 'register')) {
                Logger::warning('Module has no register() method: ' . $moduleClass);
                continue;
            }

            try {
                $moduleClass::register();
            } catch (\Throwable $e) {
                Logger::error('Module registration failed for ' . $moduleClass . ': ' . $e->getMessage());
            }
        }
    }
}
