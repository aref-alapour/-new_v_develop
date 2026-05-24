<?php

namespace EscapeZoom\Core\Modules\Search\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class PopularSearch extends BaseModel
{
    protected $table = 'wp_popular_searches';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'query',
        'count',
        'last_seen_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
