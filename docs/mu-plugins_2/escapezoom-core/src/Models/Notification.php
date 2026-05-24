<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Notification - جدول notifications
 * 
 * اعلان‌های سیستم
 */
class Notification extends Model
{
    protected $connection = 'default';
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'content',
        'type',
        'users',
        'read',
        'created_at',
    ];

    protected $casts = [
        'users' => 'array',
        'read' => 'array',
        'created_at' => 'datetime',
    ];

    // Helper Methods
    
    /**
     * دریافت کاربران دریافت‌کننده
     */
    public function recipients()
    {
        if (empty($this->users)) {
            return collect([]);
        }
        
        return \Corcel\Model\User::whereIn('ID', $this->users)->get();
    }

    /**
     * چک کردن خوانده شدن برای یک کاربر
     */
    public function isReadBy(int $userId): bool
    {
        return in_array($userId, $this->read ?? []);
    }

    /**
     * علامت زدن به عنوان خوانده شده
     */
    public function markAsReadBy(int $userId): void
    {
        $read = $this->read ?? [];
        if (!in_array($userId, $read)) {
            $read[] = $userId;
            $this->read = $read;
            $this->save();
        }
    }

    /**
     * علامت زدن به عنوان خوانده نشده
     */
    public function markAsUnreadBy(int $userId): void
    {
        $read = $this->read ?? [];
        $read = array_diff($read, [$userId]);
        $this->read = array_values($read);
        $this->save();
    }

    // Scopes
    
    public function scopeForUser($query, int $userId)
    {
        return $query->whereJsonContains('users', $userId);
    }

    public function scopeUnreadBy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('read')
              ->orWhereJsonDoesntContain('read', $userId);
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
