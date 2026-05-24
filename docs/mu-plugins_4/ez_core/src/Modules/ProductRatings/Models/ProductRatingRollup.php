<?php

namespace EscapeZoom\Core\Modules\ProductRatings\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema;

/**
 * Composite PK (product_id, criterion_id). Use explicit where clauses; avoid save().
 */
class ProductRatingRollup extends BaseModel
{
    protected $primaryKey = 'product_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'product_id' => 'integer',
        'criterion_id' => 'integer',
        'sum_weighted_score' => 'integer',
        'sum_weight' => 'integer',
    ];

    public function getTable(): string
    {
        return ProductRatingsSchema::rollupsTable();
    }
}
