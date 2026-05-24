<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Comments\Models\CommentMeta;

final class CommentMetaController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return CommentMeta::class;
    }

    protected static function primaryKey(): string
    {
        return 'meta_id';
    }
}
