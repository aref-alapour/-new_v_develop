<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\SchemaTableCheck;
use EscapeZoom\Core\Database\WordPressCoreTables;

final class ProductPenaltySchema
{
    public static function table(): string
    {
        return WordPressCoreTables::prefix() . 'ez_product_penalties';
    }

    public static function tablesVerified(): bool
    {
        if (! CapsuleBoot::isBooted()) {
            return false;
        }

        return SchemaTableCheck::hasTable(self::table());
    }
}
