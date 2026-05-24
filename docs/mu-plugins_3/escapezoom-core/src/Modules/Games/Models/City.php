<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * City (جدول wp_ez_cities).
 */
class City extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_cities';

    protected $fillable = ['name', 'slug', 'is_active'];

    protected $casts = [
        'id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'city_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'city_id');
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()->where('is_active', 1)->orderBy('name')->get();
    }
}
