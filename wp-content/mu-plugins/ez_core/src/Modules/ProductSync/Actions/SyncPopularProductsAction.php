<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\ProductSync\Actions;
use Illuminate\Database\Capsule\Manager as Capsule;
final class SyncPopularProductsAction {
    public function execute(): bool {
        $db = Capsule::connection('wordpress');
        $counts = $db->table('zb_product_view')->selectRaw('product_id, count(*) as c')->groupBy('product_id')->get();
        foreach ($counts as $row) {
            $db->table('zb_products_order')->updateOrInsert(['product_id' => $row->product_id], ['popular' => $row->c]);
        }
        return true;
    }
}
