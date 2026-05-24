<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Marketing\Models\Marketing;

final class MarktingOrderController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return Marketing::class;
    }

    protected static function primaryKey(): string
    {
        return 'order_id';
    }
}
