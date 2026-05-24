<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\WpCore\Models\PostMeta;

final class PostMetaController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return PostMeta::class;
    }

    protected static function primaryKey(): string
    {
        return 'meta_id';
    }
}
