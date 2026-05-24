<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin\Gateway;

use EscapeZoom\Core\Core\Bootstrap;
use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotSearchService;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

final class PenaltyProductSearch
{
    /** @param array<string, mixed> $inputs */
    public static function run(array $inputs, Request $req): Response
    {
        Bootstrap::bootDataLayerOnly();

        $term = isset($inputs['q']) ? trim((string) $inputs['q']) : '';
        if (mb_strlen($term) < 2) {
            return Response::json(['items' => []]);
        }

        $rows = ProductsSnapshotSearchService::searchByName($term, 20);
        $items = array_map(static function (array $row): array {
            return [
                'id' => (int) $row['product_id'],
                'title' => (string) $row['product_name'],
                'image_url' => (string) $row['product_image_url'],
                'url' => (string) $row['product_url'],
            ];
        }, $rows);

        return Response::json(['items' => $items])
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
