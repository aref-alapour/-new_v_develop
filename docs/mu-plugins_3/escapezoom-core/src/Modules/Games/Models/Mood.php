<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mood (جدول wp_ez_moods). دیکشنری مود برای فیلتر و آرشیو.
 */
class Mood extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_moods';

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
        return $this->belongsToMany(Product::class, 'ez_product_moods', 'mood_id', 'product_id');
    }
}

