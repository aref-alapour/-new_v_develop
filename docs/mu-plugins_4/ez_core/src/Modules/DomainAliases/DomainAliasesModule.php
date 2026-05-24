<?php

namespace EscapeZoom\Core\Modules\DomainAliases;

final class DomainAliasesModule
{
    public static function register(): void
    {
        Runtime::register();
        AdminSettings::register();
        LegacyOptionsImporter::register();
    }
}
