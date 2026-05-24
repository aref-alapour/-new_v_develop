<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductsSnapshot;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\WordPressCoreTables;
use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Carousel reads against products_snapshot — Capsule + bound SQL.
 * Mirrors legacy EZ_Products_Snapshot_Repository (theme delegates here).
 */
final class ProductsSnapshotReadService
{
    public const CACHE_GROUP = 'ez_products_snapshot';

    public static function cacheTtlSeconds(): int
    {
        if (!function_exists('apply_filters')) {
            return 300;
        }
        $ttl = (int) apply_filters('ez_products_snapshot_cache_ttl', 300);

        return $ttl > 0 ? $ttl : 300;
    }

    /**
     * @param array<string, mixed> $args
     * @return array<int, array<string, mixed>>
     */
    public static function query(array $args): array
    {
        $args = self::applySourcePresets($args);

        $joinClauses = [];
        $joinBindings = [];
        $whereClauses = [];
        $whereBindings = [];

        $table = ProductsSnapshotTable::name();
        $trTable = WordPressCoreTables::term_relationships();
        $metaTable = WordPressCoreTables::postmeta();

        $deactivate = ! empty($args['deactivate']);
        $activeSoon = ! empty($args['active_soon']) && ($args['sort_type'] ?? '') === 'recent';
        if ($deactivate) {
            $whereClauses[] = "ps.product_status IN ('active','updated','deactivated','soon','expired','temp')";
        } elseif ($activeSoon) {
            $whereClauses[] = "ps.product_status IN ('active','updated','soon')";
        } else {
            $whereClauses[] = "ps.product_status IN ('active','updated')";
        }

        $p = (array) ($args['params'] ?? []);

        if (isset($p['brand_id']) && $p['brand_id'] !== -1 && $p['brand_id'] !== '-1') {
            $whereClauses[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(ps.product_brand, '$.id')) AS UNSIGNED) = ?";
            $whereBindings[] = (int) $p['brand_id'];
        }

        if (
            isset($p['product_type'])
            && $p['product_type'] !== -1
            && $p['product_type'] !== '-1'
            && $p['product_type'] !== ''
        ) {
            $whereClauses[] = 'ps.product_type = ?';
            $whereBindings[] = (string) $p['product_type'];
        }

        if (isset($p['city_id'])) {
            $cid = $p['city_id'];
            if (is_array($cid) && [] !== $cid) {
                $ids = array_values(array_filter(array_map('intval', $cid)));
                if ([] !== $ids) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $whereClauses[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(ps.product_city, '$.id')) AS UNSIGNED) IN ({$placeholders})";
                    foreach ($ids as $cv) {
                        $whereBindings[] = $cv;
                    }
                }
            } elseif (! is_array($cid) && -1 !== (int) $cid && 0 !== (int) $cid) {
                $whereClauses[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(ps.product_city, '$.id')) AS UNSIGNED) = ?";
                $whereBindings[] = (int) $cid;
            } elseif (0 === $cid || '0' === $cid) {
                $whereClauses[] = "CAST(JSON_UNQUOTE(JSON_EXTRACT(ps.product_city, '$.id')) AS UNSIGNED) NOT IN (15,162,122)";
            }
        }

        if (isset($p['tag']) && $p['tag'] !== -1 && $p['tag'] !== '-1') {
            $tag = $p['tag'];
            $positiveTags = [];
            $negativeTags = [];
            if (is_array($tag)) {
                foreach ($tag as $t) {
                    $t = (int) $t;
                    if ($t > 0) {
                        $positiveTags[] = $t;
                    } elseif ($t < 0) {
                        $negativeTags[] = abs($t);
                    }
                }
            } elseif ((int) $tag < 0) {
                $negativeTags[] = abs((int) $tag);
            } elseif ((int) $tag > 0) {
                $positiveTags[] = (int) $tag;
            }

            if ([] !== $positiveTags) {
                $ph = implode(',', array_fill(0, count($positiveTags), '?'));
                $joinClauses[] = "INNER JOIN `{$trTable}` tr_pos ON tr_pos.object_id = ps.product_id AND tr_pos.term_taxonomy_id IN ({$ph})";
                foreach ($positiveTags as $pt) {
                    $joinBindings[] = $pt;
                }
            }

            if ([] !== $negativeTags) {
                $ph = implode(',', array_fill(0, count($negativeTags), '?'));
                $whereClauses[] = "ps.product_id NOT IN (\n\t\t\t\t\tSELECT object_id FROM `{$trTable}`\n\t\t\t\t\tWHERE term_taxonomy_id IN ({$ph})\n\t\t\t\t)";
                foreach ($negativeTags as $nt) {
                    $whereBindings[] = $nt;
                }
            }
        }

        if (isset($p['exclude_products']) && is_array($p['exclude_products']) && [] !== $p['exclude_products']) {
            $ex = array_values(array_filter(array_map('intval', $p['exclude_products'])));
            if ([] !== $ex) {
                $ph = implode(',', array_fill(0, count($ex), '?'));
                $whereClauses[] = "ps.product_id NOT IN ({$ph})";
                foreach ($ex as $ev) {
                    $whereBindings[] = $ev;
                }
            }
        }

        if (isset($p['price']) && is_array($p['price']) && 2 === count($p['price'])) {
            $pmin = (int) $p['price'][0];
            $pmax = (int) $p['price'][1];
            if ($pmin >= 0 && $pmax > 0 && $pmax >= $pmin) {
                $whereClauses[] = 'ps.min_price BETWEEN ? AND ?';
                $whereBindings[] = $pmin;
                $whereBindings[] = $pmax;
            }
        }

        if (! empty($args['only_events']) && ($args['event_type'] ?? '') === 'discount') {
            $whereClauses[] = "ps.product_id IN (\n\t\t\t\tSELECT post_id FROM `{$metaTable}`\n\t\t\t\tWHERE meta_key = 'special_discount_date'\n\t\t\t\tAND meta_value <> ''\n\t\t\t\tAND CAST(meta_value AS UNSIGNED) > UNIX_TIMESTAMP()\n\t\t\t)";
        }

        $sortType = (string) ($p['sort_type'] ?? $args['sort_type'] ?? 'popular');
        $orderBy = self::orderByClause($sortType);

        $limit = max(0, (int) ($args['limit'] ?? 0));
        $page = max(1, (int) ($args['page'] ?? 1));
        $random = ! empty($args['random']);
        $fetchLimit = $limit > 0 ? max($limit * 4, 80) : 200;

        [$rankJoin, $rankSelect] = self::rankScoresJoinFragments();
        $joinSql = trim(implode(' ', array_filter([$rankJoin, [] !== $joinClauses ? implode(' ', $joinClauses) : ''])));
        $whereSql = [] !== $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        $sql = trim("SELECT ps.*{$rankSelect} FROM `{$table}` ps {$joinSql} {$whereSql} {$orderBy} LIMIT ?");
        $bindings = array_merge($joinBindings, $whereBindings, [$fetchLimit]);

        $payload = $sql."\0".json_encode($bindings, JSON_UNESCAPED_SLASHES);
        $cacheKey = 'rows:'.md5($payload);

        $rows = function_exists('wp_cache_get')
            ? wp_cache_get($cacheKey, self::CACHE_GROUP)
            : false;

        if (! is_array($rows)) {
            $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
            $result = $conn->select($sql, $bindings);
            $rows = [];
            foreach ($result as $row) {
                $rows[] = (array) $row;
            }

            if (function_exists('wp_cache_set')) {
                wp_cache_set($cacheKey, $rows, self::CACHE_GROUP, self::cacheTtlSeconds());
            }
        }

        foreach ($rows as &$row) {
            self::decodeJsonColumns($row);
        }
        unset($row);

        if (
            isset($p['schedule'])
            && is_array($p['schedule'])
            && 2 === count($p['schedule'])
        ) {
            $start = (int) $p['schedule'][0];
            $end = (int) $p['schedule'][1];
            if ($start > 0 && $end > $start) {
                $rows = self::filterBySchedule($rows, $start, $end);
            }
        }

        if (! empty($args['only_ads'])) {
            $rows = array_values(array_filter($rows, static fn(array $r) => (int) ($r['rank_popular'] ?? 0) > 0));
        } elseif (! empty($args['exclude_ads'])) {
            $rows = array_values(array_filter($rows, static fn(array $r) => (int) ($r['rank_popular'] ?? 0) <= 0));
        } elseif ('hottest' === $sortType && empty($args['unpin_ads'])) {
            usort(
                $rows,
                static fn(array $a, array $b) => ((int) ($b['rank_popular'] ?? 0)
                    <=> (int) ($a['rank_popular'] ?? 0))
            );
        }

        if (! empty($args['only_events']) && ! empty($args['most_discount'])) {
            $rows = self::sortByDiscountPercentage($rows);
        }

        if ($random) {
            $memory = [];
            if (! empty($args['random_memory'])) {
                $memory = array_filter(
                    array_map('intval', explode(',', (string) $args['random_memory']))
                );
            }
            if ([] !== $memory) {
                $rows = array_values(
                    array_filter(
                        $rows,
                        static fn(array $r) => ! in_array((int) $r['product_id'], $memory, true)
                    )
                );
            }
            shuffle($rows);
        }

        $maxPages = $limit > 0 ? (int) ceil(count($rows) / $limit) : 1;
        if ($limit > 0) {
            $rows = $random
                ? array_slice($rows, 0, $limit)
                : array_slice($rows, ($page - 1) * $limit, $limit);
        }

        $GLOBALS['ez_products_snapshot_last_max_pages'] = $maxPages;

        return $rows;
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, array<string, mixed>>
     */
    public static function productsByIds(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ([] === $ids) {
            return [];
        }

        $table = ProductsSnapshotTable::name();
        [$rankJoin, $rankSelect] = self::rankScoresJoinFragments();
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge($ids, $ids);
        $sql = "SELECT ps.*{$rankSelect} FROM `{$table}` ps {$rankJoin} WHERE ps.product_id IN ({$ph}) ORDER BY FIELD(ps.product_id, {$ph})";

        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $result = $conn->select($sql, $params);
        $rows = [];
        foreach ($result as $row) {
            $rows[] = (array) $row;
        }

        foreach ($rows as &$row) {
            self::decodeJsonColumns($row);
        }
        unset($row);

        return $rows;
    }

    private static function orderByClause(string $sortType): string
    {
        $rankOrder = self::rankScoresOrderExpressions();

        switch ($sortType) {
            case 'hottest':
            case 'trend':
                return "ORDER BY {$rankOrder['hottest']} DESC, ps.rate DESC, ps.comments_count DESC, ps.product_id DESC";
            case 'topsale':
                return "ORDER BY {$rankOrder['topsale']} DESC, ps.comments_count DESC, ps.product_id DESC";
            case 'recent':
                return 'ORDER BY ps.product_id DESC';
            case 'popular':
            default:
                return "ORDER BY {$rankOrder['popular']} DESC, ps.comments_count DESC, ps.rate DESC, ps.product_id DESC";
        }
    }

    /**
     * @return array{0: string, 1: string} join SQL, extra SELECT columns
     */
    private static function rankScoresJoinFragments(): array
    {
        if (! ProductRankingSchema::tablesVerified()) {
            return ['', ''];
        }

        $prs = ProductRankingSchema::scoresTable();

        return [
            "LEFT JOIN `{$prs}` prs ON prs.product_id = ps.product_id",
            ', COALESCE(prs.score_popular, 0) AS rank_popular, COALESCE(prs.score_hottest, 0) AS rank_hottest, COALESCE(prs.score_topsale, 0) AS rank_topsale',
        ];
    }

    /**
     * @return array{popular: string, hottest: string, topsale: string}
     */
    private static function rankScoresOrderExpressions(): array
    {
        if (ProductRankingSchema::tablesVerified()) {
            return [
                'popular' => 'prs.score_popular',
                'hottest' => 'prs.score_hottest',
                'topsale' => 'prs.score_topsale',
            ];
        }

        return [
            'popular' => 'ps.rank_popular',
            'hottest' => 'ps.rank_hottest',
            'topsale' => 'ps.rank_topsale',
        ];
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private static function applySourcePresets(array $args): array
    {
        $source = (string) ($args['source'] ?? '');
        $paramsRef = &$args['params'];

        if (! isset($paramsRef) || ! is_array($paramsRef)) {
            $args['params'] = [];
            $paramsRef = &$args['params'];
        }

        if ('' === $source) {
            return $args;
        }

        $p = &$paramsRef;

        switch (true) {
            case 'home_trends' === $source:
                $args['sort_type'] = 'trend';
                $args['random'] = true;
                $args['limit'] = $args['limit'] ?? 40;
                break;

            case 'home_cities_escaperoom' === $source:
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $p['product_type'] = 'اتاق فرار';
                $args['unpin_ads'] = false;
                break;

            case 'home_cities_cinema' === $source:
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $p['product_type'] = 'سینما ترس';
                break;

            case 'home_cities_lasertag' === $source:
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $p['product_type'] = 'لیزرتگ';
                break;

            case 'home_discounts_event' === $source:
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = $p['sort_type'] ?? 'recent';
                $args['only_events'] = true;
                $args['event_type'] = 'discount';
                $args['random'] = empty($args['most_discount']) && ! isset($p['sort_type']);
                break;

            case 'cat_sansyab' === $source:
                $args['sort_type'] = $p['sort_type'] ?? 'recent';
                $args['random'] = isset($p['sort_type']) && -1 === (int) $p['sort_type'];
                $args['limit'] = $args['limit'] ?? 200;
                $args['page'] = (int) ($p['page'] ?? $args['page'] ?? 1);
                break;

            case str_contains($source, 'city_page_product_'):
                $cid = (int) explode('city_page_product_', $source)[1];
                $p['city_id'] = [$cid];
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = 'hottest';
                $args['random'] = true;
                break;

            case str_contains($source, 'city_page_discounts_event_'):
                $rest = explode('city_page_discounts_event_', $source)[1];
                $p['city_id'] = array_map('intval', explode(',', $rest));
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = 'recent';
                $args['only_events'] = true;
                $args['event_type'] = 'discount';
                $args['random'] = true;
                break;

            case str_contains($source, 'type_page_cat_'):
                $rest = explode('type_page_cat_', $source)[1];
                $pieces = explode('_', $rest);
                $typeSlug = $pieces[0] ?? '';
                $cityId = isset($pieces[1]) ? (int) $pieces[1] : -1;
                $p['city_id'] = (-1 === $cityId) ? -1 : [$cityId];
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $args['random'] = ! isset($p['sort_type']);
                if (function_exists('get_product_type_equivalent')) {
                    $p['product_type'] = get_product_type_equivalent($typeSlug);
                }
                break;

            case str_contains($source, 'type_page_discounts_event_'):
                $typeSlug = explode('type_page_discounts_event_', $source)[1];
                if (function_exists('get_product_type_equivalent')) {
                    $p['product_type'] = get_product_type_equivalent($typeSlug);
                }
                $args['limit'] = $args['limit'] ?? 40;
                $args['sort_type'] = 'recent';
                $args['only_events'] = true;
                $args['event_type'] = 'discount';
                $args['random'] = true;
                break;

            case str_contains($source, 'type_page_escaperoom_genre_'):
                $genre = explode('type_page_escaperoom_genre_', $source)[1];
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $args['limit'] = $args['limit'] ?? 40;
                $p['tag'] = 'horror' === $genre ? [124] : -124;
                $args['unpin_ads'] = true;
                $args['badge_ads'] = false;
                $p['city_id'] = -1;
                $args['random'] = true;
                $p['product_type'] = 'اتاق فرار';
                break;

            case 'hood_page' === $source:
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $args['limit'] = $args['limit'] ?? 40;
                $args['unpin_ads'] = false;
                $args['badge_ads'] = false;
                $p['city_id'] = -1;
                $args['random'] = false;
                break;

            case 'genre_page' === $source:
                $args['sort_type'] = $p['sort_type'] ?? 'recent';
                $args['limit'] = $args['limit'] ?? 40;
                $args['unpin_ads'] = false;
                $args['badge_ads'] = false;
                $args['random'] = false;
                break;

            case 'typecity_page_ads' === $source:
                $args['sort_type'] = $p['sort_type'] ?? 'recent';
                $args['limit'] = $args['limit'] ?? 40;
                $args['unpin_ads'] = true;
                $args['badge_ads'] = true;
                $args['random'] = true;
                $args['only_ads'] = true;
                break;

            case 'typecity_page_monopoly' === $source:
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $args['limit'] = $args['limit'] ?? 40;
                $args['unpin_ads'] = true;
                $args['badge_ads'] = false;
                $args['random'] = true;
                break;

            case str_contains($source, 'typecity_page_genre_'):
                $genre = explode('typecity_page_genre_', $source)[1];
                if ('horror' === $genre) {
                    $p['tag'] = [124];
                } elseif ('exciting' === $genre) {
                    $p['tag'] = [178];
                } elseif ('family' === $genre) {
                    $p['tag'] = [-124, -178];
                } elseif ('nonhorror' === $genre) {
                    $p['tag'] = -124;
                }
                $args['sort_type'] = $p['sort_type'] ?? 'hottest';
                $args['unpin_ads'] = true;
                $args['limit'] = $args['limit'] ?? 40;
                $args['badge_ads'] = false;
                $args['random'] = false;
                break;
            default:
                break;
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function decodeJsonColumns(array &$row): void
    {
        foreach (['product_brand', 'product_city', 'product_area', 'product_tags', 'schedule'] as $col) {
            if (isset($row[$col]) && is_string($row[$col]) && '' !== $row[$col]) {
                $decoded = json_decode((string) $row[$col], true);
                $row[$col] = null === $decoded ? null : $decoded;
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private static function filterBySchedule(array $rows, int $start, int $end): array
    {
        if ($end <= $start) {
            return $rows;
        }
        $out = [];
        foreach ($rows as $row) {
            $schedule = $row['schedule'] ?? null;
            if (! is_array($schedule) || [] === $schedule) {
                $out[] = $row;
                continue;
            }
            $bucket = $schedule['normal'] ?? $schedule['weekend'] ?? null;
            if (! is_array($bucket)) {
                $keys = array_keys($schedule);
                $bucket = $keys ? (is_array($schedule[$keys[0]]) ? $schedule[$keys[0]] : null) : null;
            }
            if (! is_array($bucket)) {
                continue;
            }

            foreach ($bucket as $sans) {
                if (! is_array($sans) || empty($sans['time'])) {
                    continue;
                }
                $ts = strtotime(gmdate('Y-m-d', $start).' '.(string) $sans['time'].' Asia/Tehran');
                if ($ts >= $start && $ts <= $end) {
                    $out[] = $row;
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private static function sortByDiscountPercentage(array $rows): array
    {
        if (! function_exists('get_post_meta')) {
            return $rows;
        }

        $pcts = [];
        foreach ($rows as $row) {
            $pid = (int) ($row['product_id'] ?? 0);
            $pcts[$pid] = (int) get_post_meta($pid, 'special_discount_percentage', true);
        }
        usort(
            $rows,
            static fn(array $a, array $b) => ($pcts[(int) $b['product_id']] ?? 0)
                <=> ($pcts[(int) $a['product_id']] ?? 0)
        );

        return $rows;
    }
}
