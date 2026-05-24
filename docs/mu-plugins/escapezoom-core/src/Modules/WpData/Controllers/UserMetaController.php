<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\WpCore\Models\UserMeta;

final class UserMetaController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return UserMeta::class;
    }

    protected static function primaryKey(): string
    {
        return 'umeta_id';
    }
}
