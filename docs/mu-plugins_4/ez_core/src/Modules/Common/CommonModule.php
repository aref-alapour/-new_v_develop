<?php

namespace EscapeZoom\Core\Modules\Common;

final class CommonModule
{
    public static function register(): void
    {
        ProductSnapshotSync::register();
    }
}
