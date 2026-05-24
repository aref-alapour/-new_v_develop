<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Marketing\Models\OrderLog;

final class OrderLogController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return OrderLog::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
