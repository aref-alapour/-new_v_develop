<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class TermMeta extends BaseModel
{
    protected $table = 'wp_termmeta';
    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'term_id',
        'meta_key',
        'meta_value',
    ];
}
