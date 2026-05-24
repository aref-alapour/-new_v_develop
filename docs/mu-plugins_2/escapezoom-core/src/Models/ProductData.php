<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل ProductData - جدول products_data در دیتابیس external
 * 
 * این جدول اطلاعات محصولات و بازی‌ها را نگهداری می‌کند
 */
class ProductData extends Model
{
    /**
     * نام اتصال دیتابیس
     *
     * @var string
     */
    protected $connection = 'external';

    /**
     * نام جدول
     *
     * @var string
     */
    protected $table = 'products_data';

    /**
     * کلید اصلی جدول
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * آیا timestamps فعال است؟
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * فیلدهای قابل fill
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'product_type',
        'title',
        'price',
        'notable',
        'special',
        'active',
        'monopoly',
        'brand_id',
        'discount_data',
        'instant_off',
        'geo',
        'image',
        'age_limit',
        'level',
        'schedule',
        'duration',
        'url',
        'hood',
        'city_id',
        'city_name',
        'tags_id',
        'tags_title',
        'count_min',
        'count_max',
        'pish_person',
        'auto_disable',
        'contact_info',
        'owner_id',
        'manager_id',
        'comments_count',
        'rate',
    ];

    /**
     * فیلدهای که باید cast شوند
     *
     * @var array
     */
    protected $casts = [
        'product_id' => 'integer',
        'price' => 'float',
        'notable' => 'integer',
        'special' => 'integer',
        'active' => 'integer',
        'monopoly' => 'integer',
        'brand_id' => 'integer',
        'discount_data' => 'array',
        'instant_off' => 'array',
        'age_limit' => 'integer',
        'level' => 'integer',
        'schedule' => 'array',
        'duration' => 'integer',
        'city_id' => 'integer',
        'tags_id' => 'array',
        'tags_title' => 'array',
        'count_min' => 'integer',
        'count_max' => 'integer',
        'pish_person' => 'float',
        'auto_disable' => 'integer',
        'contact_info' => 'array',
        'owner_id' => 'integer',
        'manager_id' => 'integer',
        'comments_count' => 'integer',
        'rate' => 'float',
    ];

    /**
     * Scope برای جستجوی عنوان
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $title
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByTitle($query, string $title)
    {
        return $query->where('title', 'like', "%{$title}%");
    }

    /**
     * Scope برای فیلتر محصولات فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Scope برای فیلتر بر اساس شهر
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $cityId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Scope برای محصولات ویژه
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpecial($query)
    {
        return $query->where('special', 1);
    }

    /**
     * Scope برای محصولات قابل توجه
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotable($query)
    {
        return $query->where('notable', 1);
    }

    /**
     * Accessor برای دریافت URL کامل تصویر
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? home_url($this->image) : null;
    }

    // Relations
    
    /**
     * رابطه با محصول WordPress
     */
    public function wpProduct()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'product_id', 'ID');
    }

    /**
     * رابطه با مالک
     */
    public function owner()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'owner_id', 'ID');
    }

    /**
     * رابطه با مدیر
     */
    public function manager()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'manager_id', 'ID');
    }

    /**
     * رابطه با رزروها
     */
    public function bookings()
    {
        return $this->hasMany(BookingHistory::class, 'room_id', 'product_id');
    }
}
