<?php

namespace EscapeZoom\Core\Modules\Search\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class ProductSearch extends BaseModel
{
    protected $table = 'wp_products_search';

    protected $fillable = [
        'product_id',
        'title',
        'city_name',
        'hood',
        'product_type',
        'image',
        'updated_at',
        'created_at',
    ];
}
