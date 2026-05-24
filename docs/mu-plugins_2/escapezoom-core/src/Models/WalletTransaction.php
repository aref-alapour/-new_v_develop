<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل WalletTransaction - جدول wallet_transactions
 * 
 * تراکنش‌های کیف پول سفارشی
 */
class WalletTransaction extends Model
{
    protected $connection = 'default';
    protected $table = 'wallet_transactions';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'amount',
        'balance',
        'type',
        'status',
        'description',
        'created_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Relations
    
    public function user()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'user_id', 'ID');
    }

    // Scopes
    
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'در حال پردازش');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'انجام شد');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'رد شده');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDeposit($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeWithdrawal($query)
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
