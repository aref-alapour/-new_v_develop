<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated جدول wp_ez_locations با wp_ez_cities و wp_ez_areas جایگزین شده است.
 * در کد جدید از City و Area استفاده کنید.
 */
class Location extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_locations';
}
