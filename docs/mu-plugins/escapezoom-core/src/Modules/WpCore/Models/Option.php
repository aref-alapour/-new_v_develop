<?php

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class Option extends BaseModel
{
    protected $table = 'wp_options';
    protected $primaryKey = 'option_id';

    public $timestamps = false;

    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];
}
