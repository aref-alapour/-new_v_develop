<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Collection - جدول collections
 * 
 * مجموعه‌های محصولات کاربران
 */
class Collection extends Model
{
    protected $connection = 'default';
    protected $table = 'collections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'active',
        'items',
        'users',
        'likes_count',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'active' => 'integer',
        'items' => 'array',
        'users' => 'array',
        'likes_count' => 'integer',
    ];

    // Relations
    
    public function owner()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'user_id', 'ID');
    }

    /**
     * دریافت محصولات این مجموعه
     * items یک آرایه از product_id ها است
     */
    public function products()
    {
        if (empty($this->items)) {
            return collect([]);
        }
        
        return \Corcel\Model\Post::whereIn('ID', $this->items)
            ->where('post_type', 'product')
            ->get();
    }

    /**
     * دریافت کاربران همکار این مجموعه
     * users یک آرایه از user_id ها است
     */
    public function collaborators()
    {
        if (empty($this->users)) {
            return collect([]);
        }
        
        return \Corcel\Model\User::whereIn('ID', $this->users)->get();
    }

    // Scopes
    
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePopular($query, int $minLikes = 10)
    {
        return $query->where('likes_count', '>=', $minLikes);
    }

    // Helper Methods
    
    public function addItem(int $productId): void
    {
        $items = $this->items ?? [];
        if (!in_array($productId, $items)) {
            $items[] = $productId;
            $this->items = $items;
            $this->save();
        }
    }

    public function removeItem(int $productId): void
    {
        $items = $this->items ?? [];
        $items = array_diff($items, [$productId]);
        $this->items = array_values($items);
        $this->save();
    }

    public function addCollaborator(int $userId): void
    {
        $users = $this->users ?? [];
        if (!in_array($userId, $users)) {
            $users[] = $userId;
            $this->users = $users;
            $this->save();
        }
    }

    public function removeCollaborator(int $userId): void
    {
        $users = $this->users ?? [];
        $users = array_diff($users, [$userId]);
        $this->users = array_values($users);
        $this->save();
    }

    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }
}
