<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\Booking\Actions;
use EscapeZoom\Core\Services\ProductDataService;
use EscapeZoom\Core\Support\ProductSupport;
use Illuminate\Database\Capsule\Manager as Capsule;

final class SortProductsGetAction {
    public function execute(array $params): array {
        $db = Capsule::connection('wordpress');
        $q = $db->table('zb_product_list as p')
            ->leftJoin('zb_products_order as o', 'p.product_id', '=', 'o.product_id')
            ->select('p.*');

        if (!empty($params['type'])) {
            $type = ProductSupport::getProductTypeEquivalent($params['type']);
            $q->where('p.product_type', $type);
        }
        if (!empty($params['city_id'])) $q->where('p.city_id', $params['city_id']);
        if (!empty($params['genre'])) $q->where('p.genres', 'LIKE', '%' . $params['genre'] . '%');
        if (!empty($params['hood'])) $q->where('p.hood', 'LIKE', '%' . $params['hood'] . '%');

        $sortBy = $params['sort_by'] ?? 'hottest';
        if (in_array($sortBy, ['hottest', 'popular', 'top_sale'])) {
            $q->orderByDesc("o.$sortBy");
        } else {
            $q->orderByDesc('p.rate');
        }

        $results = $q->get()->toArray();
        if (!empty($params['bounds'])) {
            $bounds = json_decode($params['bounds']);
            if ($bounds) {
                $results = array_filter($results, fn($r) => ProductSupport::isPointWithinBounds((string)$r->geo, $bounds));
            }
        }

        $pds = new ProductDataService();
        return $pds->standardize($results, !empty($params['free_sans']));
    }
}
