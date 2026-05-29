<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\Search\Actions;
use EscapeZoom\Core\Services\ProductDataService;
use Illuminate\Database\Capsule\Manager as Capsule;
final class SearchGamesAction {
    public function execute(string $term): array {
        $db = Capsule::connection('wordpress');
        $results = $db->table('zb_product_list')->where('title','LIKE',"%$term%")->get()->toArray();
        return (new ProductDataService())->standardize($results);
    }
}
