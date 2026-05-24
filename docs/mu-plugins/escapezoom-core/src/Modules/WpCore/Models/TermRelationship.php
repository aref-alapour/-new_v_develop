<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class TermRelationship extends BaseModel
{
    protected $table = 'wp_term_relationships';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'object_id',
        'term_taxonomy_id',
        'term_order',
    ];
}
