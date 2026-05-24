<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل EzUser - جدول ez_users (stub)
 *
 * ستون‌های دیگر با migration بعدی اضافه می‌شود
 */
class EzUser extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_users';

    protected $fillable = [];

    // Relations

    public function ownedProducts()
    {
        return $this->hasMany(Product::class, 'owner_id');
    }

    public function managedProducts()
    {
        return $this->hasMany(Product::class, 'manager_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}
