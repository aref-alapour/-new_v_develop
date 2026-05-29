<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Models;
use Illuminate\Database\Eloquent\Model;
final class ProductView extends Model {
    protected $table = 'zb_product_view';
    protected $primaryKey = 'view_id';
    public $timestamps = false;
    protected $fillable = ['product_id','ip','agent','referer','timestamp'];
}
