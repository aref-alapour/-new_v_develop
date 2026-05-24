<?php

namespace EscapeZoom\Core\Modules\Comments\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class CommentMeta extends BaseModel
{
    protected $table = 'wp_commentmeta';
    protected $primaryKey = 'meta_id';

    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'meta_key',
        'meta_value',
    ];
}
