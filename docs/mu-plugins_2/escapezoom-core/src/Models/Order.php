<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Order - جدول ez_orders
 *
 * پل بین سانس رزرو‌شده و سفارش ووکامرس.
 * wc_order_id = wp_posts.ID (post_type = shop_order).
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
        'ticket_issued_at',
    ];

    protected $casts = [
        'wc_order_id' => 'integer',
        'slot_id' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
        'total_amount' => 'decimal:0',
        'quantity' => 'integer',
        'ticket_issued_at' => 'datetime',
    ];

    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    public const ORDER_COMPLETED = 'completed';
    public const ORDER_CANCELLED = 'cancelled';
    public const ORDER_REFUNDED = 'refunded';
    public const ORDER_PENDING = 'pending';

    // Relations

    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slot_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(EzUser::class, 'user_id');
    }

    /**
     * سفارش ووکامرس (wp_posts — اتصال wordpress/external بسته به تنظیمات)
     */
    public function wcOrder()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'wc_order_id', 'ID');
    }
}
