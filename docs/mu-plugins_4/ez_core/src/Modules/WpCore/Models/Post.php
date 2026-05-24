<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class Post extends BaseModel
{
    protected $table = 'wp_posts';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'post_author',
        'post_date',
        'post_content',
        'post_title',
        'post_status',
        'comment_status',
        'ping_status',
        'post_name',
        'post_type',
    ];
}
