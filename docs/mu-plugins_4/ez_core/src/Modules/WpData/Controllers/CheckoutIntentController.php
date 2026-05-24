<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\WpData\Models\CheckoutIntent;

final class CheckoutIntentController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return CheckoutIntent::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
