<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;
use EscapeZoom\Core\Modules\ProductRanking\ProductPenaltySchema;

class ProductPenalty extends BaseModel
{
    public $timestamps = false;

    protected $casts = [
        'product_id' => 'integer',
        'exclude_popular' => 'boolean',
        'exclude_hottest' => 'boolean',
        'exclude_topsale' => 'boolean',
        'is_enabled' => 'boolean',
        'popular_comment_divisor' => 'float',
        'topsale_quantity_divisor' => 'float',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
    ];

    public function getTable(): string
    {
        return ProductPenaltySchema::table();
    }
}
