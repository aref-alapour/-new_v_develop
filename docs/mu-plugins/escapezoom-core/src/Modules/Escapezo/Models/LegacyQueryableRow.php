<?php

namespace EscapeZoom\Core\Modules\Escapezo\Models;

use EscapeZoom\Core\Database\CapsuleBoot;
use Illuminate\Database\Eloquent\Model;

/**
 * Row in the legacy queries DB (e.g. products_data). Set $table if your physical name differs.
 * Requires EZ_ESCAPEZO_DB_* constants; otherwise any query will fail until configured.
 */
class LegacyQueryableRow extends Model
{
    protected $connection = CapsuleBoot::CONNECTION_ESCAPEZO;

    /** @var string Override in subclasses or set dynamically when you introduce dedicated models. */
    protected $table = 'products_data';

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
