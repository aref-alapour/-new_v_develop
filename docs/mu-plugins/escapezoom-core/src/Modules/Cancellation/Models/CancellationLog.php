<?php

namespace EscapeZoom\Core\Modules\Cancellation\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class CancellationLog extends BaseModel
{
    protected $table = 'cancellation_log';

    protected $fillable = [
        'request_id',
        'product_id',
        'user_id',
        'user_role',
        'action',
        'action_time',
    ];
}
