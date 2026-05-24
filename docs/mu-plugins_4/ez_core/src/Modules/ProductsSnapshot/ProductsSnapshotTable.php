<?php

namespace EscapeZoom\Core\Modules\ProductsSnapshot;

use EscapeZoom\Core\Database\WordPressCoreTables;

final class ProductsSnapshotTable
{
    public static function name(): string
    {
        return WordPressCoreTables::prefix() . 'products_snapshot';
    }
}
