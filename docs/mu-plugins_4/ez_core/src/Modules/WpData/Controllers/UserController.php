<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\WpCore\Models\User;

final class UserController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return User::class;
    }

    protected static function primaryKey(): string
    {
        return 'ID';
    }
}
