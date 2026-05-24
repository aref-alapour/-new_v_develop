<?php

namespace EscapeZoom\Core\Modules\Comments\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class Comment extends BaseModel
{
    protected $table = 'wp_comments';

    protected $primaryKey = 'comment_ID';

    public $timestamps = false;

    protected $fillable = [
        'comment_post_ID',
        'comment_author',
        'comment_author_email',
        'comment_content',
        'comment_type',
        'comment_parent',
        'user_id',
        'comment_approved',
        'comment_date',
        'comment_date_gmt',
    ];
}
