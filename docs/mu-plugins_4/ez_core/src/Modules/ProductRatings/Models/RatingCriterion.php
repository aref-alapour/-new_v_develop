<?php

namespace EscapeZoom\Core\Modules\ProductRatings\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema;

class RatingCriterion extends BaseModel
{
    public $timestamps = false;

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getTable(): string
    {
        return ProductRatingsSchema::criteriaTable();
    }
}
