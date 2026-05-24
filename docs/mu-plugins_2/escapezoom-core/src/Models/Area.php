<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Area - جدول ez_areas (stub)
 *
 * ستون‌های دیگر با migration بعدی اضافه می‌شود
 */
class Area extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_areas';

    protected $fillable = [];

    // Relations

    public function products()
    {
        return $this->hasMany(Product::class, 'area_id');
    }
}
