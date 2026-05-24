<?php

namespace EscapeZoom\Core\Modules\Common\Services;

use EscapeZoom\Core\Modules\Common\Models\ProductSnapshot;
use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotTable;

/**
 * Builds rows for wp_products_snapshot from WooCommerce product posts.
 */
class ProductSnapshotService
{
    /** @var string[] */
    private array $gameTypeCityPrefixes = [
        'اتاق فرار',
        'سینما ترس',
        'لیزرتگ',
        'اتاق خشم',
        'کافه بازی',
        'پینت بال',
        'فوتبال حبابی',
    ];

    public function buildSnapshotRow(int $productId): array|false
    {
        if ($productId <= 0 || get_post_type($productId) !== 'product') {
            return false;
        }

        if (get_post_status($productId) !== 'publish') {
            return false;
        }

        $saleStatus = (string) $this->getPostMetaDirect($productId, 'product_state', '');
        if ($saleStatus === '') {
            $saleStatus = 'active';
        }

        $productType = null;
        $cityData = null;
        $identityTerms = get_the_terms($productId, 'ez_game_identity');
        if ($identityTerms && !is_wp_error($identityTerms) && $identityTerms !== []) {
            $idTerm = $identityTerms[0];
            if ($idTerm) {
                $productType = $idTerm->name;
            }
        }
        $terms = get_the_terms($productId, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if ((int) $term->parent !== 0) {
                    $parentTerm = get_term($term->parent);
                    if ($parentTerm && !is_wp_error($parentTerm) && $productType === null) {
                        $productType = $parentTerm->name;
                    }
                    $cityData = [
                        'id' => (int) $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                    ];
                } elseif ($productType === null) {
                    $productType = $term->name;
                }
            }
        }

        if (!$productType) {
            $productType = 'نامشخص';
        }

        $cityData = $this->normalizeCityData($cityData, $productType);
        $productHood = (string) $this->getPostMetaDirect($productId, 'room_loc', '');

        $areaData = null;
        $tagsData= [];
        $seenTagKeys = [];

        $appendTagRow = static function (string $title, string $url) use (&$tagsData, &$seenTagKeys): void {
            $k = $title . "\0" . $url;
            if (isset($seenTagKeys[$k])) {
                return;
            }
            $seenTagKeys[$k] = true;
            $tagsData[] = ['title' => $title, 'url' => $url];
        };

        $genreTerms = get_the_terms($productId, 'product_genre');
        if ($genreTerms && !is_wp_error($genreTerms)) {
            foreach ($genreTerms as $tag) {
                $termLink = get_term_link($tag->term_id);
                $termUrl = is_wp_error($termLink) ? '' : str_replace(home_url(), '', (string) $termLink);
                $appendTagRow($tag->name, $termUrl);
            }
        }

        $tagTerms = get_the_terms($productId, 'product_tag');
        if ($tagTerms && !is_wp_error($tagTerms)) {
            foreach ($tagTerms as $tag) {
                $termLink = get_term_link($tag->term_id);
                $termUrl = is_wp_error($termLink) ? '' : str_replace(home_url(), '', (string) $termLink);

                if (strpos($tag->name, '|||||') === 0) {
                    $appendTagRow(str_replace('|||||', '', $tag->name), $termUrl);
                    continue;
                }

                if ($areaData === null) {
                    $areaData = ['title' => $tag->name, 'url' => $termUrl];
                }

                $appendTagRow($tag->name, $termUrl);
            }
        }

        $brandData = null;
        $brandTerms = taxonomy_exists('product_brand') ? get_the_terms($productId, 'product_brand') : false;
        if ($brandTerms && !is_wp_error($brandTerms) && $brandTerms !== []) {
            $brand = $brandTerms[0];
            $brandThumbnailId = get_term_meta($brand->term_id, 'thumbnail_id', true);
            $brandImage = $brandThumbnailId ? wp_get_attachment_url((int) $brandThumbnailId) : '';
            $brandData = [
                'id' => (int) $brand->term_id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'image' => $brandImage ?: '',
            ];
        }

        $productName = (string) get_the_title($productId);
        $fullUrl = get_permalink($productId);
        $relativeUrl = $fullUrl ? str_replace(home_url(), '', $fullUrl) : '';
        $imageUrl = wp_get_attachment_url(get_post_thumbnail_id($productId));

        $schedule = [];
        if (function_exists('get_sanses')) {
            $schedule = get_sanses($productId);
        } else {
            $metaSchedule = $this->getPostMetaDirect($productId, 'sanses', []);
            if (is_array($metaSchedule)) {
                $schedule = $metaSchedule;
            }
        }

        $minPrice = $this->extractMinPrice($schedule, 0);
        $managerId = (int) $this->getPostMetaDirect($productId, 'sans_manager', 0);
        if ($managerId <= 0) {
            $managerId = (int) $this->getPostMetaDirect($productId, 'manager_id', 0);
        }

        return [
            'product_id' => $productId,
            'product_name' => $productName,
            'product_type' => $productType,
            'product_status' => $saleStatus,
            'product_url' => $relativeUrl,
            'product_image_url' => $imageUrl ?: '',
            'min_price' => max(0, $minPrice),
            'min_prepayment_person_count' => max(1, (int) $this->getPostMetaDirect($productId, 'pish_pardakht_per_person', 1)),
            'discount_data' => null,
            'product_hood' => $productHood,
            'product_brand' => $brandData,
            'product_city' => $cityData,
            'product_area' => $areaData,
            'product_tags' => $tagsData !== [] ? $tagsData : null,
            'comments_count' => max(0, (int) $this->getPostMetaDirect($productId, 'comments_count_new', 0)),
            'rate' => max(0, min(5, (float) $this->getPostMetaDirect($productId, 'ez_weighted_rating_overall', 0))),
            'schedule' => $schedule !== [] ? $schedule : null,
            'owner_id' => (int) get_post_field('post_author', $productId),
            'manager_id' => $managerId,
            'rank_popular' => 0,
            'rank_hottest' => 0,
            'rank_topsale' => 0,
        ];
    }

    public function upsertSnapshot(int $productId): bool
    {
        $row = $this->buildSnapshotRow($productId);
        if ($row === false) {
            return false;
        }

        $this->persistRow($productId, $row);

        return true;
    }

    /**
     * Keep snapshot aligned with the product row: upsert when publish+valid, otherwise remove.
     */
    public function syncProduct(int $productId): void
    {
        $productId = max(0, $productId);
        if ($productId <= 0 || get_post_type($productId) !== 'product') {
            return;
        }

        $row = $this->buildSnapshotRow($productId);
        if ($row === false) {
            $this->deleteSnapshot($productId);

            return;
        }

        $this->persistRow($productId, $row);
    }

    public function deleteSnapshot(int $productId): void
    {
        $productId = max(0, $productId);
        if ($productId <= 0) {
            return;
        }

        try {
            ProductSnapshot::query()->where('product_id', $productId)->delete();
        } catch (\Throwable $e) {
            global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->delete(ProductsSnapshotTable::name(), ['product_id' => $productId], ['%d']);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function persistRow(int $productId, array $row): void
    {
        try {
            ProductSnapshot::query()->updateOrCreate(['product_id' => $productId], $row);
        } catch (\Throwable $e) {
            global $wpdb;
            $flat = $this->flattenRowForWpdb($row);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->replace(ProductsSnapshotTable::name(), $flat);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function flattenRowForWpdb(array $row): array
    {
        foreach (['product_brand', 'product_city', 'product_area', 'product_tags', 'schedule'] as $key) {
            if (array_key_exists($key, $row) && ($row[$key] !== null && !is_string($row[$key]))) {
                $row[$key] = wp_json_encode($row[$key], JSON_UNESCAPED_UNICODE);
            }
        }

        return $row;
    }

    /**
     * @return int[] product IDs
     */
    public function getNextProductIdBatch(int $afterId, int $limit = 100): array
    {
        global $wpdb;

        $limit = max(1, min(200, $limit));
        $afterId = max(0, $afterId);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND ID > %d ORDER BY ID ASC LIMIT %d",
                'product',
                'publish',
                $afterId,
                $limit
            )
        );

        return array_map('intval', is_array($ids) ? $ids : []);
    }

    private function extractMinPrice(array $schedule, int $fallback): int
    {
        $fallback = max(0, $fallback);
        if ($schedule === []) {
            return $fallback;
        }

        $candidates = [];
        foreach ($schedule as $daySanses) {
            if (!is_array($daySanses)) {
                continue;
            }
            foreach ($daySanses as $sans) {
                if (!is_array($sans)) {
                    continue;
                }
                $offPrice = isset($sans['off_price']) ? (int) $sans['off_price'] : 0;
                $price = isset($sans['price']) ? (int) $sans['price'] : 0;
                $value = $offPrice > 0 ? $offPrice : $price;
                if ($value > 0) {
                    $candidates[] = $value;
                }
            }
        }

        return $candidates === [] ? $fallback : min($candidates);
    }

    private function getPostMetaDirect(int $postId, string $metaKey, mixed $default = null): mixed
    {
        global $wpdb;

        $value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC LIMIT 1",
                $postId,
                $metaKey
            )
        );

        if ($value === null) {
            return $default;
        }

        return maybe_unserialize($value);
    }

    private function normalizeCityData(?array $cityData, string $productType): ?array
    {
        if (!$cityData || empty($cityData['name'])) {
            return $cityData;
        }

        $cityName = trim((string) $cityData['name']);
        $prefixes = array_unique(array_filter(array_merge([$productType], $this->gameTypeCityPrefixes)));

        foreach ($prefixes as $prefix) {
            $prefix = trim((string) $prefix);
            if ($prefix === '') {
                continue;
            }

            $prefixWithSpace = $prefix . ' ';
            if (mb_strpos($cityName, $prefixWithSpace) === 0) {
                $cityName = trim(mb_substr($cityName, mb_strlen($prefixWithSpace)));
                break;
            }

            if (mb_strpos($cityName, $prefix) === 0) {
                $cityName = trim(mb_substr($cityName, mb_strlen($prefix)));
                break;
            }
        }

        $cityData['name'] = $cityName;

        return $cityData;
    }
}
