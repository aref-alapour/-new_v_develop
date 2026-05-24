<?php

namespace EscapeZoom\Core\Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderUserSnapshot extends Model
{
    protected $table = 'wp_ez_order_user_snapshot';
    protected $primaryKey = 'order_id';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'first_name',
        'last_name',
        'display_name',
        'phone_number',
        'email',
        'user_level',
        'registered_at_snapshot',
        'snapshot_json',
    ];

    protected $casts = [
        'registered_at_snapshot' => 'datetime',
        'snapshot_json' => 'array',
    ];
}
