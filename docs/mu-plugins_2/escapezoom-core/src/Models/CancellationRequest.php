<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل CancellationRequest - جدول wp_cancellation_requests
 * 
 * درخواست‌های لغو سفارش
 */
class CancellationRequest extends Model
{
    protected $connection = 'default';
    protected $table = 'wp_cancellation_requests';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'requester_id',
        'requester_type',
        'reason_id',
        'status',
        'sans_time',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'requester_id' => 'integer',
        'reason_id' => 'integer',
        'sans_time' => 'integer',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];

    // Relations (استفاده از مدل‌های وردپرس هسته برای دسترسی به relations یکسان)
    
    public function order()
    {
        return $this->belongsTo(\EscapeZoom\Core\Models\WordPress\Post::class, 'order_id', 'ID');
    }

    public function product()
    {
        return $this->belongsTo(\EscapeZoom\Core\Models\WordPress\Post::class, 'product_id', 'ID');
    }

    public function requester()
    {
        return $this->belongsTo(\EscapeZoom\Core\Models\WordPress\User::class, 'requester_id', 'ID');
    }

    public function logs()
    {
        return $this->hasMany(CancellationLog::class, 'request_id', 'ID');
    }

    // Scopes
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByRequester($query, int $requesterId)
    {
        return $query->where('requester_id', $requesterId);
    }
}
