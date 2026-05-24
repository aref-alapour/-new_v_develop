<?php

namespace EscapeZoom\Core\Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderGameSnapshot extends Model
{
    protected $table = 'wp_ez_order_game_snapshot';
    protected $primaryKey = 'order_id';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'game_id',
        'game_name',
        'game_city',
        'game_area',
        'game_duration',
        'game_brand',
        'manager_id',
        'price_at_order',
        'snapshot_json',
    ];

    protected $casts = [
        'price_at_order' => 'decimal:0',
        'snapshot_json' => 'array',
    ];
}
