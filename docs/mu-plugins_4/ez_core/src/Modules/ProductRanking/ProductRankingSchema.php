<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\SchemaTableCheck;
use EscapeZoom\Core\Database\WordPressCoreTables;

final class ProductRankingSchema
{
    public static function scoresTable(): string
    {
        return WordPressCoreTables::prefix() . 'product_rank_scores';
    }

    public static function tablesVerified(): bool
    {
        if (! CapsuleBoot::isBooted()) {
            return false;
        }

        return SchemaTableCheck::hasTable(self::scoresTable());
    }
}
