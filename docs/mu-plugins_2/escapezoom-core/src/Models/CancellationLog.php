<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل CancellationLog - جدول wp_cancellation_log
 * 
 * لاگ اقدامات مربوط به لغو
 */
class CancellationLog extends Model
{
    protected $connection = 'default';
    protected $table = 'wp_cancellation_log';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'product_id',
        'user_id',
        'user_role',
        'action',
        'action_time',
    ];

    protected $casts = [
        'request_id' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
        'action_time' => 'integer',
    ];

    // Relations
    
    public function request()
    {
        return $this->belongsTo(CancellationRequest::class, 'request_id', 'ID');
    }

    public function product()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'product_id', 'ID');
    }

    public function user()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'user_id', 'ID');
    }

    // Scopes
    
    public function scopeByRequest($query, int $requestId)
    {
        return $query->where('request_id', $requestId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
