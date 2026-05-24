<?php

namespace EscapeZoom\Core\Modules\Marketing\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class OrderLog extends BaseModel
{
    protected $table = 'wp_orders_log';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'order_log_status',
        'order_log_view',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
