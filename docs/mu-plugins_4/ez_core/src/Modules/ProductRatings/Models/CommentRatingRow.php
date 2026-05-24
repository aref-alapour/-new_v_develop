<?php

namespace EscapeZoom\Core\Modules\ProductRatings\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema;

/**
 * Composite PK (comment_id, criterion_id). Use explicit where clauses; avoid save().
 */
class CommentRatingRow extends BaseModel
{
    protected $primaryKey = 'comment_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'comment_id' => 'integer',
        'criterion_id' => 'integer',
        'score_raw' => 'integer',
    ];

    public function getTable(): string
    {
        return ProductRatingsSchema::rowsTable();
    }
}
