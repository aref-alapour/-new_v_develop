<?php

namespace EscapeZoom\Core\Modules\Common\Models;

use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotTable;

class ProductSnapshot extends BaseModel
{
    public function getTable(): string
    {
        return ProductsSnapshotTable::name();
    }

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'product_brand' => 'array',
        'product_city' => 'array',
        'product_area' => 'array',
        'product_tags' => 'array',
        'schedule' => 'array',
    ];
}
