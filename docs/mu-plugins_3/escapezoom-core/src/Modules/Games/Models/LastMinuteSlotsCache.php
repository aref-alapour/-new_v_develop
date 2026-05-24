<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * LastMinuteSlotsCache (جدول wp_ez_last_minute_slots_cache). کش سانس‌های لحظه‌آخری (فقط کش متنی + product_id برای فیلتر).
 */
class LastMinuteSlotsCache extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'default';
    protected $table = 'ez_last_minute_slots_cache';

    protected $fillable = [
        'product_id',
        'slot_start_at',
        'city_id',
        'game_type_id',
        'title',
        'city_name_cache',
        'area_names_cache',
        'game_type_title_cache',
        'genre_names_cache',
        'moods_cache',
        'image_url_cache',
        'url_path_cache',
        'capacity_min',
        'capacity_max',
        'age_limit',
        'difficulty_level',
        'lm_discount',
        'price_before',
        'price_after',
        'computed_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'slot_start_at' => 'datetime',
        'city_id' => 'integer',
        'game_type_id' => 'integer',
        'capacity_min' => 'integer',
        'capacity_max' => 'integer',
        'age_limit' => 'integer',
        'difficulty_level' => 'integer',
        'lm_discount' => 'integer',
        'price_before' => 'integer',
        'price_after' => 'integer',
        'computed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }
}
