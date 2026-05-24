<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\WpCore\Models\Post;

final class PostController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return Post::class;
    }

    protected static function primaryKey(): string
    {
        return 'ID';
    }
}
