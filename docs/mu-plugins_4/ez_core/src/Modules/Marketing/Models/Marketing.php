<?php

namespace EscapeZoom\Core\Modules\Marketing\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class Marketing extends BaseModel
{
    protected $table = 'wp_markting';

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'customer_id',
        'customer_phone',
        'game_id',
        'game_name',
        'order_status',
        'order_tickets_quantity',
        'order_coupon_used',
        'order_finall_price',
        'order_level_discount',
        'order_method',
        'order_coupon',
        'order_paid',
        'order_created_at',
    ];

    protected $casts = [
        'order_created_at' => 'datetime',
    ];
}
