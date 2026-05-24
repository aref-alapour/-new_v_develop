<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Product (جدول wp_ez_products).
 * product_id خودافزاینده؛ slug یکتا برای URL؛ شهر از city_id؛ مناطق از pivot ez_product_areas.
 */
class Product extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_products';
    protected $primaryKey = 'product_id';
    public $incrementing = true;

    protected $fillable = [
        'slug',
        'brand_id',
        'city_id',
        'game_type_id',
        'owner_id',
        'manager_id',
        'title',
        'brand_title_cache',
        'city_name_cache',
        'areas_cache',
        'hood_name',
        'genres_cache',
        'moods_cache',
        'themes_cache',
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
        'booking_cutoff_min',
        'satisfaction_count',
        'satisfaction_positive_count',
        'hot_rank',
        'topsale_rank',
        'popular_rank',
        'published_at',
        'comments_count',
        'lm_discount_reg',
        'lm_discount_hol',
        'lm_trigger_reg',
        'lm_trigger_hol',
        'lm_trigger_min',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'brand_id' => 'integer',
        'city_id' => 'integer',
        'game_type_id' => 'integer',
        'owner_id' => 'integer',
        'manager_id' => 'integer',
        'min_price' => 'integer',
        'sales_count' => 'integer',
        'capacity_min' => 'integer',
        'capacity_max' => 'integer',
        'age_limit' => 'integer',
        'duration_minutes' => 'integer',
        'booking_cutoff_min' => 'integer',
        'satisfaction_count' => 'integer',
        'satisfaction_positive_count' => 'integer',
        'hot_rank' => 'integer',
        'topsale_rank' => 'integer',
        'popular_rank' => 'integer',
        'comments_count' => 'integer',
        'lm_discount_reg' => 'integer',
        'lm_discount_hol' => 'integer',
        'lm_trigger_reg' => 'integer',
        'lm_trigger_hol' => 'integer',
        'lm_trigger_min' => 'integer',
        'schedule_config' => 'array',
        'published_at' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    public function owner()
    {
        return $this->belongsTo(EzUser::class, 'owner_id');
    }

    public function manager()
    {
        return $this->belongsTo(EzUser::class, 'manager_id');
    }

    /** نام شهر از کش یا ربط city. */
    public function getCityNameAttribute(?string $value): string
    {
        if ($value !== null && $value !== '') {
            return $value;
        }
        if ($this->city_id && $this->relationLoaded('city') && $this->city) {
            return (string) $this->city->name;
        }
        if ($this->city_id) {
            $c = City::query()->find($this->city_id);
            return $c ? (string) $c->name : '';
        }
        return '';
    }

    /** مناطق محصول از جدول wp_ez_product_areas. */
    public function areas()
    {
        return $this->belongsToMany(Area::class, 'ez_product_areas', 'product_id', 'area_id');
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'ez_product_genres', 'product_id', 'genre_id');
    }

    public function moods()
    {
        return $this->belongsToMany(Mood::class, 'ez_product_moods', 'product_id', 'mood_id');
    }

    public function themes()
    {
        return $this->belongsToMany(Theme::class, 'ez_product_themes', 'product_id', 'theme_id');
    }

    public function slots()
    {
        return $this->hasMany(Slot::class, 'product_id', 'product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id', 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'product_id');
    }

    /**
     * آرایهٔ props برای رندر کامپوننت ez-product-card (سرور → HTML).
     * فرمت‌دهی اینجا انجام می‌شود؛ کامپوننت فقط نمایش می‌دهد.
     */
    public function toComponentProps(): array
    {
        return [
            'product-id'  => (string) $this->product_id,
            'title'       => $this->title ?? '',
            'price'       => $this->min_price !== null
                ? number_format((int) $this->min_price) . ' تومان'
                : '',
            'image-url'   => $this->image_url_cache ?? '',
            'href'        => $this->url_path_cache ?? '',
        ];
    }
}
