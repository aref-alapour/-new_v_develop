<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Product - جدول ez_products
 *
 * product_id = wp_posts.ID (رابطه ۱:۱ با محصول ووکامرس)
 */
class Product extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_products';
    protected $primaryKey = 'product_id';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'brand_id',
        'city_id',
        'area_id',
        'owner_id',
        'manager_id',
        'title',
        'brand_title_cache',
        'city_name_cache',
        'area_name_cache',
        'hood_name',
        'game_type',
        'genres_cache',
        'url_path_cache',
        'image_url_cache',
        'min_price',
        'difficulty_level',
        'schedule_config',
        'status',
        'sale_status',
        'sales_count',
        'capacity_min',
        'capacity_max',
        'age_limit',
        'duration_minutes',
        'satisfaction_count',
        'satisfaction_positive_count',
        'hot_score',
        'topsale_score',
        'published_at',
        'post_modified_at',
        'comments_count',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'brand_id' => 'integer',
        'city_id' => 'integer',
        'area_id' => 'integer',
        'owner_id' => 'integer',
        'manager_id' => 'integer',
        'min_price' => 'integer',
        'difficulty_level' => 'integer',
        'sales_count' => 'integer',
        'capacity_min' => 'integer',
        'capacity_max' => 'integer',
        'age_limit' => 'integer',
        'duration_minutes' => 'integer',
        'satisfaction_count' => 'integer',
        'satisfaction_positive_count' => 'integer',
        'hot_score' => 'decimal:4',
        'topsale_score' => 'decimal:2',
        'comments_count' => 'integer',
        'published_at' => 'datetime',
        'post_modified_at' => 'datetime',
        'genres_cache' => 'array',
        'schedule_config' => 'array',
    ];

    // Relations

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function owner()
    {
        return $this->belongsTo(EzUser::class, 'owner_id');
    }

    public function manager()
    {
        return $this->belongsTo(EzUser::class, 'manager_id');
    }

    public function wpPost()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'product_id', 'ID');
    }

    public function slots()
    {
        return $this->hasMany(Slot::class, 'product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id');
    }
}
