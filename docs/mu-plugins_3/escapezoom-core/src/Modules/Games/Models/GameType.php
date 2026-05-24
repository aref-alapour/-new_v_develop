<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Game type lookup. Thin model.
 */
class GameType extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_game_types';

    protected $fillable = ['title', 'slug', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'game_type_id', 'id');
    }
}
