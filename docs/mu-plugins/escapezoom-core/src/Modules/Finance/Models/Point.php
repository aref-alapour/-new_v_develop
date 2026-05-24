<?php

namespace EscapeZoom\Core\Modules\Finance\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class Point extends BaseModel
{
    protected $table = 'points';

    protected $fillable = [
        'user_id',
        'point',
        'action',
        'description',
        'created_at',
    ];
}
