<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ProductLookup (جدول wp_ez_product_lookup). Lookup برای فیلتر سریع محصولات (شهر، نوع، منطقه، ژانر، مود، از پایوت‌ها پر می‌شود).
 */
class ProductLookup extends Model
{
    public $timestamps = false;

    protected $connection = 'default';
    protected $table = 'ez_product_lookup';
    protected $primaryKey = 'product_id';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'city_id',
        'type_id',
        'area_ids',
        'genre_ids',
        'mood_ids',
        'min_price',
        'rating',
        'status',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'city_id' => 'integer',
        'type_id' => 'integer',
        'area_ids' => 'array',
        'genre_ids' => 'array',
        'mood_ids' => 'array',
        'min_price' => 'integer',
        'rating' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
