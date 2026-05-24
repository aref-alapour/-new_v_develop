<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Theme (جدول wp_ez_themes). دیکشنری تم برای فیلتر و آرشیو.
 */
class Theme extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_themes';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
