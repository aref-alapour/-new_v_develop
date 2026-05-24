<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Point - جدول points
 * 
 * امتیازات کاربران
 */
class Point extends Model
{
    protected $connection = 'default';
    protected $table = 'points';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'point',
        'created_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'point' => 'integer',
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

    public function scopePositive($query)
    {
        return $query->where('point', '>', 0);
    }

    public function scopeNegative($query)
    {
        return $query->where('point', '<', 0);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods
    
    /**
     * محاسبه مجموع امتیازات یک کاربر
     */
    public static function getTotalByUser(int $userId): int
    {
        return static::where('user_id', $userId)->sum('point');
    }
}
