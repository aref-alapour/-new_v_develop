<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\WpCore\Models\Option;

final class OptionController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return Option::class;
    }

    protected static function primaryKey(): string
    {
        return 'option_id';
    }
}
