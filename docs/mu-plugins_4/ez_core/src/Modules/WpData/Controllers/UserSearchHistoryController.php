<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Search\Models\UserSearchHistory;

final class UserSearchHistoryController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return UserSearchHistory::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
