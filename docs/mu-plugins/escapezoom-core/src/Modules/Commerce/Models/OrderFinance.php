<?php

namespace EscapeZoom\Core\Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFinance extends Model
{
    protected $table = 'wp_ez_order_finance';
    protected $primaryKey = 'order_id';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'currency',
        'payment_type',
        'price_unit',
        'gross_amount',
        'coupon_discount_amount',
        'level_discount_amount',
        'payable_amount',
        'wallet_amount',
        'online_amount',
        'installment_amount',
        'paid_amount',
        'remaining_amount',
        'coupon_code',
        'pricing_snapshot_json',
    ];

    protected $casts = [
        'price_unit' => 'decimal:0',
        'gross_amount' => 'decimal:0',
        'coupon_discount_amount' => 'decimal:0',
        'level_discount_amount' => 'decimal:0',
        'payable_amount' => 'decimal:0',
        'wallet_amount' => 'decimal:0',
        'online_amount' => 'decimal:0',
        'installment_amount' => 'decimal:0',
        'paid_amount' => 'decimal:0',
        'remaining_amount' => 'decimal:0',
        'pricing_snapshot_json' => 'array',
    ];
}
