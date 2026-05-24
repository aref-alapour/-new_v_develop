<?php

namespace EscapeZoom\Core\Modules\Collections\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class Collection extends BaseModel
{
    protected $table = 'collections';

    protected $fillable = [
        'user_id',
        'title',
        'users',
        'likes_count',
        'active',
        'created_at',
        'updated_at',
    ];
}
