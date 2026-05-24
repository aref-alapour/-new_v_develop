<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\Comments\Models\Comment;

final class CommentController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return Comment::class;
    }

    protected static function primaryKey(): string
    {
        return 'comment_ID';
    }
}
