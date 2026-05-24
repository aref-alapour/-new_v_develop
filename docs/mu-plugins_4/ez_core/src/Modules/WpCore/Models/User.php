<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class User extends BaseModel
{
    protected $table = 'wp_users';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'user_login',
        'user_pass',
        'user_nicename',
        'user_email',
        'user_status',
        'display_name',
        'user_registered',
    ];
}
