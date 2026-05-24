<?php

namespace EscapeZoom\Core\Modules\Common\Models;

use EscapeZoom\Core\Database\CapsuleBoot;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    protected $connection = CapsuleBoot::CONNECTION_WP;

    protected $guarded = [];
}
