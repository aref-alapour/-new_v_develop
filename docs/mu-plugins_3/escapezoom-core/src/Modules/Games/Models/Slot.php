<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Slot — one product, one date+time. pending|booked|blocked; no row = available. Thin model.
 */
class Slot extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_slots';

    public const STATUS_PENDING = 'pending';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'product_id',
        'slot_start_at',
        'slot_end_at',
        'status',
        'order_id',
        'price_at_booking',
        'pending_expires_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'slot_start_at' => 'datetime',
        'slot_end_at' => 'datetime',
        'order_id' => 'integer',
        'price_at_booking' => 'integer',
        'pending_expires_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
