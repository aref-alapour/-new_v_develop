<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Genre (جدول wp_ez_genres). الگوی یکسان با area: entity + pivot؛ lookup از پایوت پر می‌شود.
 */
class Genre extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_genres';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'ez_product_genres', 'genre_id', 'product_id');
    }
}
