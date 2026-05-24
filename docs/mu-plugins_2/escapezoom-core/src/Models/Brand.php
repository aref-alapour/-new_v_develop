<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Brand - جدول ez_brands
 *
 * برندها (مثل مجموعه‌های اتاق فرار، سینما ترس و غیره)
 */
class Brand extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_brands';

    protected $fillable = [
        'title',
        'slug',
        'logo',
        'description',
        'address',
        'score',
        'reputation',
        'game_types',
        'teams',
    ];

    protected $casts = [
        'score' => 'decimal:1',
        'reputation' => 'integer',
        'game_types' => 'array',
        'teams' => 'array',
    ];

    // Relations

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
