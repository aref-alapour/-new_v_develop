<?php

namespace EscapeZoom\Core\Modules\WpData\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class CheckoutIntent extends BaseModel
{
    protected $table = 'wp_checkout_intent';

    protected $fillable = [
        'uuid',
        'user_id',
        'cart_key',
        'status',
        'payload',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
