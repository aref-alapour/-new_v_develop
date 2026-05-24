<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductsSnapshot;

use EscapeZoom\Core\Database\CapsuleBoot;
use Illuminate\Database\Capsule\Manager as Capsule;

final class ProductsSnapshotSearchService
{
    /**
     * @return list<array{product_id: int, product_name: string, product_image_url: string, product_url: string}>
     */
    public static function searchByName(string $term, int $limit = 20): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2 || ! CapsuleBoot::isBooted()) {
            return [];
        }

        $limit = max(1, min(50, $limit));
        $table = ProductsSnapshotTable::name();
        $like = '%' . addcslashes($term, '%_\\') . '%';

        $rows = Capsule::connection(CapsuleBoot::CONNECTION_WP)
            ->table($table)
            ->select(['product_id', 'product_name', 'product_image_url', 'product_url'])
            ->whereIn('product_status', ['active', 'updated'])
            ->where('product_name', 'like', $like)
            ->orderBy('product_name')
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->product_name,
                'product_image_url' => (string) ($row->product_image_url ?? ''),
                'product_url' => (string) ($row->product_url ?? ''),
            ];
        }

        return $items;
    }

    /**
     * @return array{product_id: int, product_name: string, product_image_url: string, product_url: string}|null
     */
    public static function rowForProduct(int $productId): ?array
    {
        if ($productId < 1 || ! CapsuleBoot::isBooted()) {
            return null;
        }

        $row = Capsule::connection(CapsuleBoot::CONNECTION_WP)
            ->table(ProductsSnapshotTable::name())
            ->select(['product_id', 'product_name', 'product_image_url', 'product_url'])
            ->where('product_id', $productId)
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'product_id' => (int) $row->product_id,
            'product_name' => (string) $row->product_name,
            'product_image_url' => (string) ($row->product_image_url ?? ''),
            'product_url' => (string) ($row->product_url ?? ''),
        ];
    }

    /**
     * @return list<int>
     */
    public static function productIdsMatchingName(string $term, int $limit = 200): array
    {
        $items = self::searchByName($term, $limit);

        return array_values(array_map(static fn (array $row): int => (int) $row['product_id'], $items));
    }

    /**
     * @param list<int> $productIds
     * @return array<int, array{product_id: int, product_name: string, product_image_url: string, product_url: string, city_name: string}>
     */
    public static function mapByProductIds(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds), static fn (int $id): bool => $id > 0)));
        if ($productIds === [] || ! CapsuleBoot::isBooted()) {
            return [];
        }

        $table = ProductsSnapshotTable::name();
        $rows = Capsule::connection(CapsuleBoot::CONNECTION_WP)
            ->table($table)
            ->select(['product_id', 'product_name', 'product_image_url', 'product_url', 'product_city'])
            ->whereIn('product_id', $productIds)
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $id = (int) $row->product_id;
            $map[$id] = [
                'product_id' => $id,
                'product_name' => (string) $row->product_name,
                'product_image_url' => (string) ($row->product_image_url ?? ''),
                'product_url' => (string) ($row->product_url ?? ''),
                'city_name' => self::cityNameFromJson($row->product_city ?? null),
            ];
        }

        return $map;
    }

    private static function cityNameFromJson(mixed $json): string
    {
        if ($json === null || $json === '') {
            return '—';
        }

        if (is_string($json)) {
            $decoded = json_decode($json, true);
        } elseif (is_object($json)) {
            $decoded = (array) $json;
        } else {
            $decoded = null;
        }

        if (! is_array($decoded)) {
            return '—';
        }

        $name = $decoded['name'] ?? '';

        return is_string($name) && $name !== '' ? $name : '—';
    }
}
