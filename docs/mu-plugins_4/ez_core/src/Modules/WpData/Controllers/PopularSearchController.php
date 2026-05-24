<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Search\Models\PopularSearch;

final class PopularSearchController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return PopularSearch::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
