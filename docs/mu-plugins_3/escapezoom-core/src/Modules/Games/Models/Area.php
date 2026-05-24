<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Area (جدول wp_ez_areas؛ وابسته به شهر).
 */
class Area extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_areas';

    protected $fillable = ['city_id', 'name', 'slug', 'is_active'];

    protected $casts = [
        'id' => 'integer',
        'city_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public static function getActiveByCity(int $cityId): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()->where('city_id', $cityId)->where('is_active', 1)->orderBy('name')->get();
    }
}
