<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ez user (profile, level, points). Thin model.
 */
class EzUser extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_users';

    protected $fillable = [
        'wp_user_id',
        'first_name',
        'last_name',
        'phone',
        'display_name',
        'national_id',
        'iban',
        'avatar_id',
        'level',
        'points_total',
        'orders_count',
        'locations_cache',
        'status',
        'internal_role',
        'birth_date',
        'last_order_at',
    ];

    protected $casts = [
        'wp_user_id' => 'integer',
        'level' => 'integer',
        'points_total' => 'integer',
        'orders_count' => 'integer',
        'locations_cache' => 'array',
        'last_order_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function ownedProducts()
    {
        return $this->hasMany(Product::class, 'owner_id', 'id');
    }

    public function managedProducts()
    {
        return $this->hasMany(Product::class, 'manager_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(UserContact::class, 'user_id');
    }
}
