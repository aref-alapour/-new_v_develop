<?php

namespace EscapeZoom\Core\Modules\Common\Models;

class ProductSnapshot extends BaseModel
{
    protected $table = 'wp_products_snapshot';
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
