<?php

namespace EscapeZoom\Core\Modules\Marketing\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class OrderStatusLog extends BaseModel
{
    protected $table = 'wp_order_status_log';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
