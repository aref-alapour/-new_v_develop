<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class Term extends BaseModel
{
    protected $table = 'wp_terms';
    protected $primaryKey = 'term_id';

    protected $fillable = [
        'name',
        'slug',
        'term_group',
    ];
}
