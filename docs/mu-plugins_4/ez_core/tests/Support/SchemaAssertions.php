<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Tests\Support;

use EscapeZoom\Core\Database\SchemaTableCheck;
use EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema;

final class SchemaAssertions
{
    public static function ajaxGatewayTablesExist(): bool
    {
        return self::hasTable(AjaxGatewaySchema::noncesTable())
            && self::hasTable(AjaxGatewaySchema::rateTable());
    }

    public static function hasTable(string $tableName): bool
    {
        return SchemaTableCheck::hasTable($tableName);
    }
}
