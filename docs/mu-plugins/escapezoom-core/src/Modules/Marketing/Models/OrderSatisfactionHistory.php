<?php

namespace EscapeZoom\Core\Modules\Marketing\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class OrderSatisfactionHistory extends BaseModel
{
    protected $table = 'wp_orders_satisfaction_history';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'status',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
