<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Common\Models\ProductSnapshot;

final class ProductSnapshotController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return ProductSnapshot::class;
    }

    protected static function primaryKey(): string
    {
        return 'product_id';
    }
}
