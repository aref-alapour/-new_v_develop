<?php

namespace EscapeZoom\Core\Modules\Search\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class UserSearchHistory extends BaseModel
{
    protected $table = 'wp_user_search_history';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'searches',
        'updated_at',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
