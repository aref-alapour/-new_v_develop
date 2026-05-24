<?php

namespace EscapeZoom\Core\Modules\Cancellation\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class CancellationRequest extends BaseModel
{
    protected $table = 'cancellation_requests';

    protected $fillable = [
        'order_id',
        'product_id',
        'requester_id',
        'requester_type',
        'reason_id',
        'status',
        'sans_time',
        'created_at',
        'updated_at',
    ];
}
