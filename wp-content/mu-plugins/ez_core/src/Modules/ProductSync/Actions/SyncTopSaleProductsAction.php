<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\ProductSync\Actions;
use Illuminate\Database\Capsule\Manager as Capsule;
final class SyncTopSaleProductsAction {
    public function execute(): bool {
        $db = Capsule::connection('wordpress');
        $counts = $db->table('zb_booking_history')->selectRaw('room_id as product_id, count(*) as c')->groupBy('room_id')->get();
        foreach ($counts as $row) {
            $db->table('zb_products_order')->updateOrInsert(['product_id' => $row->product_id], ['top_sale' => $row->c]);
        }
        return true;
    }
}
