<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order (جدول wp_ez_orders).
 * پل بین سانس رزرو‌شده و سفارش ووکامرس. wc_order_id = wp_posts.ID (post_type = shop_order).
 * مدل نازک.
 */
class Order extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_orders';

    protected $fillable = [
        'wc_order_id',
        'slot_id',
        'product_id',
        'user_id',
        'payment_status',
        'order_status',
        'total_amount',
        'quantity',
        'is_last_minute',
        'lm_discount_percent',
        'price_before_discount',
        'payment_type',
        'customer_level',
        'topsale_contribution',
        'ticket_issued_at',
        'customer_phone',
        'participants',
        'affiliate_id',
        'created_weekday',
        'created_time',
    ];

    protected $casts = [
        'wc_order_id' => 'integer',
        'slot_id' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
        'total_amount' => 'decimal:0',
        'quantity' => 'integer',
        'is_last_minute' => 'boolean',
        'lm_discount_percent' => 'integer',
        'price_before_discount' => 'integer',
        'customer_level' => 'integer',
        'topsale_contribution' => 'decimal:2',
        'ticket_issued_at' => 'datetime',
        'participants' => 'array',
        'affiliate_id' => 'integer',
    ];

    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    public const ORDER_COMPLETED = 'completed';
    public const ORDER_CANCELLED = 'cancelled';
    public const ORDER_REFUNDED = 'refunded';
    public const ORDER_PENDING = 'pending';

    public const PAYMENT_TYPE_COMPLETE = 'complete';
    public const PAYMENT_TYPE_PARTIAL = 'partial';

    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slot_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(EzUser::class, 'user_id');
    }
}
