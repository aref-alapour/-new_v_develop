<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\Search\Actions;
use Illuminate\Database\Capsule\Manager as Capsule;

final class GetSuggestedGamesAction {
    public function execute(int $tagId, string $citySlug): array {
        $key = "suggested_products_" . $citySlug;
        $data = get_option($key, []);
        $pids = $data['products'] ?? [];
        if (empty($pids)) return ['product_ids' => []];

        $db = Capsule::connection('wordpress');
        $tag = get_term($tagId);
        if (!$tag) return ['product_ids' => []];

        $results = $db->table('zb_product_list')
            ->whereIn('product_id', $pids)
            ->where('tags', 'LIKE', '%' . $tag->slug . '%')
            ->pluck('product_id')
            ->toArray();

        return ['product_ids' => $results];
    }
}
