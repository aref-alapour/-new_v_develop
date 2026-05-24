<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Marketing\Models\OrderSatisfactionHistory;

final class OrderSatisfactionHistoryController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return OrderSatisfactionHistory::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
