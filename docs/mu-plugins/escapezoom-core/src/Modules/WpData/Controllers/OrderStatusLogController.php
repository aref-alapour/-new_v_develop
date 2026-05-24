<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Marketing\Models\OrderStatusLog;

final class OrderStatusLogController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return OrderStatusLog::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
