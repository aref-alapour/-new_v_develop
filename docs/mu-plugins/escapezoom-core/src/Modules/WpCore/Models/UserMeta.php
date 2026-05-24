<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class UserMeta extends BaseModel
{
    protected $table = 'wp_usermeta';
    protected $primaryKey = 'umeta_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'meta_key',
        'meta_value',
    ];
}
