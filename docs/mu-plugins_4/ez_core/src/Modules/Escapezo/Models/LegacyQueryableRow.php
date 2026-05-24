<?php

namespace EscapeZoom\Core\Modules\Escapezo\Models;

use EscapeZoom\Core\Database\CapsuleBoot;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy queries DB row (default: products_data). Subclass or override $table when schema is known.
 */
class LegacyQueryableRow extends Model
{
    protected $connection = CapsuleBoot::CONNECTION_ESCAPEZO;

    protected $table = 'products_data';

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
