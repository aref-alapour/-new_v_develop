<?php

namespace EscapeZoom\Core\Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckoutOrder extends Model
{
    protected $table = 'wp_ez_orders';
    protected $primaryKey = 'order_id';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'slot_start_at',
        'slot_end_at',
        'quantity',
        'stage_status',
        'expires_at',
        'order_status',
    ];

    protected $casts = [
        'slot_start_at' => 'datetime',
        'slot_end_at' => 'datetime',
        'expires_at' => 'datetime',
        'quantity' => 'integer',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id', 'order_id');
    }
}
