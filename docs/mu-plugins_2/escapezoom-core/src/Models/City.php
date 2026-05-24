<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل City - جدول ez_cities (stub)
 *
 * ستون‌های دیگر با migration بعدی اضافه می‌شود
 */
class City extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_cities';

    protected $fillable = [];

    // Relations

    public function products()
    {
        return $this->hasMany(Product::class, 'city_id');
    }
}
