<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Invitation - جدول invitations
 * 
 * دعوت‌نامه‌های کاربران
 */
class Invitation extends Model
{
    protected $connection = 'default';
    protected $table = 'invitations';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'inviter_id',
        'invited_id',
        'product_id',
        'created_at',
    ];

    protected $casts = [
        'inviter_id' => 'integer',
        'invited_id' => 'integer',
        'product_id' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relations
    
    public function inviter()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'inviter_id', 'ID');
    }

    public function invited()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'invited_id', 'ID');
    }

    public function product()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'product_id', 'ID');
    }

    // Scopes
    
    public function scopeByInviter($query, int $inviterId)
    {
        return $query->where('inviter_id', $inviterId);
    }

    public function scopeByInvited($query, int $invitedId)
    {
        return $query->where('invited_id', $invitedId);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
