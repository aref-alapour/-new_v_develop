<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class PostMeta extends BaseModel
{
    protected $table = 'wp_postmeta';
    protected $primaryKey = 'meta_id';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
    ];
}
