<?php

namespace EscapeZoom\Core\Modules\ProductRatings\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema;

class CommentRatingWeight extends BaseModel
{
    protected $primaryKey = 'comment_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $casts = [
        'comment_id' => 'integer',
        'weight' => 'integer',
        'stored_level' => 'integer',
    ];

    public function getTable(): string
    {
        return ProductRatingsSchema::weightsTable();
    }
}
