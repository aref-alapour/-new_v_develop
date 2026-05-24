<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Archives;

use EscapeZoom\Core\Modules\Archives\API\ArchivesRestController;
use EscapeZoom\Core\Modules\Archives\PostType\EZ_Archive_CPT;

/**
 * ماژول آرشیوساز: CPT، روتینگ، ادمین، REST API.
 */
final class ArchiveBootstrap
{
    public static function register(): void
    {
        EZ_Archive_CPT::register();
        ArchiveRouter::register();
        add_action('rest_api_init', [self::class, 'registerRestRoutes']);
        if (is_admin()) {
            ArchiveMapAdmin::register();
        }
    }

    public static function registerRestRoutes(): void
    {
        ArchivesRestController::create()->registerRoutes();
    }
}
