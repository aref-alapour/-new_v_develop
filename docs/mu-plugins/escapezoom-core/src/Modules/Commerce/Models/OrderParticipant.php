<?php

namespace EscapeZoom\Core\Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderParticipant extends Model
{
    protected $table = 'wp_ez_order_participants';

    protected $fillable = [
        'order_id',
        'phone_number',
        'is_main_customer',
    ];

    protected $casts = [
        'is_main_customer' => 'boolean',
    ];
}
