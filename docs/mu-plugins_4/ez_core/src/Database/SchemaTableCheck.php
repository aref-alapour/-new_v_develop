<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Reliable table-existence checks (MySQL SHOW TABLES LIKE ? does not accept bound values).
 */
final class SchemaTableCheck
{
    public static function hasTable(
        string $tableName,
        string $connection = CapsuleBoot::CONNECTION_WP,
    ): bool {
        if (! CapsuleBoot::isBooted()) {
            return false;
        }

        return Capsule::connection($connection)->getSchemaBuilder()->hasTable($tableName);
    }
}
