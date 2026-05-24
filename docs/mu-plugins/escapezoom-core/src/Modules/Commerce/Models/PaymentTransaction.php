<?php

namespace EscapeZoom\Core\Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $table = 'wp_ez_payment_transactions';

    protected $fillable = [
        'order_id',
        'user_id',
        'gateway',
        'channel',
        'event_type',
        'direction',
        'status',
        'amount',
        'currency',
        'idempotency_key',
        'gateway_transaction_id',
        'gateway_reference_id',
        'gateway_payload',
        'error_code',
        'error_message',
        'occurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'gateway_payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function checkoutOrder(): BelongsTo
    {
        return $this->belongsTo(CheckoutOrder::class, 'order_id', 'order_id');
    }
}
