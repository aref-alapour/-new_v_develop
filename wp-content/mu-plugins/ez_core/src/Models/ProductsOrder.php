<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Models;
use Illuminate\Database\Eloquent\Model;
final class ProductsOrder extends Model {
    protected $table = 'zb_products_order';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['product_id', 'hottest', 'popular', 'top_sale', 'type'];
}
