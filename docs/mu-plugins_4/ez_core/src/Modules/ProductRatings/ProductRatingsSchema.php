<?php

namespace EscapeZoom\Core\Modules\ProductRatings;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\SchemaTableCheck;
use EscapeZoom\Core\Database\WordPressCoreTables;

/**
 * WordPress-prefixed table names for product rating relational tables.
 */
final class ProductRatingsSchema
{
    public static function prefix(): string
    {
        return WordPressCoreTables::prefix();
    }

    public static function criteriaTable(): string
    {
        return self::prefix() . 'ez_rating_criteria';
    }

    public static function rowsTable(): string
    {
        return self::prefix() . 'ez_comment_rating_rows';
    }

    public static function weightsTable(): string
    {
        return self::prefix() . 'ez_comment_rating_weights';
    }

    public static function rollupsTable(): string
    {
        return self::prefix() . 'ez_product_rating_rollups';
    }

    /**
     * True when all four rating tables exist (DDL applied via ez_bootstrap_custom_tables.sql).
     */
    public static function tablesVerified(): bool
    {
        if (! CapsuleBoot::isBooted()) {
            return false;
        }

        foreach (['ez_rating_criteria', 'ez_comment_rating_rows', 'ez_comment_rating_weights', 'ez_product_rating_rollups'] as $suffix) {
            if (! SchemaTableCheck::hasTable(WordPressCoreTables::prefix() . $suffix)) {
                return false;
            }
        }

        return true;
    }
}
