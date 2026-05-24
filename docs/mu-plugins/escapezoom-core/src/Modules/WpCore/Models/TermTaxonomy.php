<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class TermTaxonomy extends BaseModel
{
    protected $table = 'wp_term_taxonomy';
    protected $primaryKey = 'term_taxonomy_id';

    protected $fillable = [
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
    ];
}
