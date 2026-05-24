<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Repositories;

use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminFilters;
use EscapeZoom\Core\Modules\ProductRanking\Models\ProductPenalty;
use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotSearchService;
use EscapeZoom\Core\Modules\ProductRanking\ProductPenaltySchema;
use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use Illuminate\Support\Carbon;

final class ProductPenaltyRepository
{
    /** @var array<int, ProductPenalty|null> */
    private static array $cache = [];

    public static function countRows(): int
    {
        if (! ProductPenaltySchema::tablesVerified()) {
            return 0;
        }

        return (int) ProductPenalty::query()->count();
    }

    /**
     * @return array{rows: list<ProductPenalty>, total: int, page: int, per_page: int}
     */
    public static function paginateForAdmin(ProductPenaltyAdminFilters $filters): array
    {
        if (! ProductPenaltySchema::tablesVerified()) {
            return ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => $filters->perPage];
        }

        $query = ProductPenalty::query();

        if ($filters->productId > 0) {
            $query->where('product_id', $filters->productId);
        } elseif ($filters->search !== '') {
            $productIds = ProductsSnapshotSearchService::productIdsMatchingName($filters->search, 200);
            if ($productIds === []) {
                return ['rows' => [], 'total' => 0, 'page' => $filters->page, 'per_page' => $filters->perPage];
            }
            $query->whereIn('product_id', $productIds);
        }

        if ($filters->facets !== []) {
            $query->where(static function ($q) use ($filters): void {
                foreach ($filters->facets as $facet) {
                    match ($facet) {
                        'hottest' => $q->orWhere('exclude_hottest', 1),
                        'popular' => $q->orWhere('exclude_popular', 1),
                        'topsale' => $q->orWhere('exclude_topsale', 1),
                        default => null,
                    };
                }
            });
        }

        if ($filters->penaltyFrom !== null) {
            $query->where(static function ($q) use ($filters): void {
                $q->whereNull('active_until')
                    ->orWhere('active_until', '>=', $filters->penaltyFrom . ' 00:00:00');
            });
        }

        if ($filters->penaltyUntil !== null) {
            $query->where(static function ($q) use ($filters): void {
                $q->whereNull('active_from')
                    ->orWhere('active_from', '<=', $filters->penaltyUntil . ' 23:59:59');
            });
        }

        if ($filters->createdFrom !== null) {
            $query->where('created_at', '>=', $filters->createdFrom . ' 00:00:00');
        }

        if ($filters->createdUntil !== null) {
            $query->where('created_at', '<=', $filters->createdUntil . ' 23:59:59');
        }

        if ($filters->updatedFrom !== null) {
            $query->where('updated_at', '>=', $filters->updatedFrom . ' 00:00:00');
        }

        if ($filters->updatedUntil !== null) {
            $query->where('updated_at', '<=', $filters->updatedUntil . ' 23:59:59');
        }

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('id')
            ->forPage($filters->page, $filters->perPage)
            ->get()
            ->all();

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $filters->page,
            'per_page' => $filters->perPage,
        ];
    }

    public static function findById(int $id): ?ProductPenalty
    {
        if ($id < 1 || ! ProductPenaltySchema::tablesVerified()) {
            return null;
        }

        return ProductPenalty::query()->where('id', $id)->first();
    }

    public static function findByProductId(int $productId): ?ProductPenalty
    {
        if ($productId < 1 || ! ProductPenaltySchema::tablesVerified()) {
            return null;
        }

        return ProductPenalty::query()->where('product_id', $productId)->first();
    }

    /**
     * @param array{
     *   product_id: int,
     *   exclude_popular?: bool|int,
     *   exclude_hottest?: bool|int,
     *   exclude_topsale?: bool|int,
     *   is_enabled?: bool|int,
     *   active_from?: string|null,
     *   active_until?: string|null,
     *   note?: string|null
     * } $data
     */
    public static function saveFromAdmin(array $data, ?int $id = null): ProductPenalty
    {
        if (! ProductPenaltySchema::tablesVerified()) {
            throw new \RuntimeException('Penalties table is not available.');
        }

        $productId = (int) ($data['product_id'] ?? 0);
        if ($productId < 1) {
            throw new \InvalidArgumentException('product_id is required.');
        }

        $payload = [
            'product_id' => $productId,
            'exclude_popular' => ! empty($data['exclude_popular']) ? 1 : 0,
            'exclude_hottest' => ! empty($data['exclude_hottest']) ? 1 : 0,
            'exclude_topsale' => ! empty($data['exclude_topsale']) ? 1 : 0,
            'is_enabled' => ! isset($data['is_enabled']) || ! empty($data['is_enabled']) ? 1 : 0,
            'active_from' => self::normalizeDateTime($data['active_from'] ?? null),
            'active_until' => self::normalizeDateTime($data['active_until'] ?? null),
            'note' => self::normalizeNote($data['note'] ?? null),
        ];

        self::assertDateWindow($payload['active_from'], $payload['active_until']);

        if ($id !== null && $id > 0) {
            $row = self::findById($id);
            if ($row === null) {
                throw new \InvalidArgumentException('Penalty row not found.');
            }
            ProductPenalty::query()->where('id', $id)->update($payload);
            $saved = self::findById($id);
        } else {
            $existing = self::findByProductId($productId);
            if ($existing !== null) {
                ProductPenalty::query()->where('id', (int) $existing->id)->update($payload);
                $saved = self::findById((int) $existing->id);
            } else {
                $saved = ProductPenalty::query()->create($payload);
            }
        }

        if ($saved === null) {
            throw new \RuntimeException('Failed to save penalty row.');
        }

        self::clearCacheForProduct($productId);

        return $saved;
    }

    public static function deleteById(int $id): bool
    {
        if ($id < 1 || ! ProductPenaltySchema::tablesVerified()) {
            return false;
        }

        $row = self::findById($id);
        if ($row === null) {
            return false;
        }

        $productId = (int) $row->product_id;
        $deleted = ProductPenalty::query()->where('id', $id)->delete() > 0;
        if ($deleted) {
            self::clearCacheForProduct($productId);
        }

        return $deleted;
    }

    public static function clearCacheForProduct(int $productId): void
    {
        unset(self::$cache[$productId]);
    }

    public static function isPenalized(int $productId, string $facet): bool
    {
        if ($productId < 1) {
            return false;
        }

        $row = self::activeRow($productId);
        if ($row !== null) {
            return match ($facet) {
                'popular' => (bool) $row->exclude_popular,
                'hottest' => (bool) $row->exclude_hottest,
                'topsale' => (bool) $row->exclude_topsale,
                default => false,
            };
        }

        return self::legacyIsPenalized($productId, $facet);
    }

    public static function popularCommentDivisor(int $productId): ?float
    {
        if ($productId < 1) {
            return null;
        }

        $row = self::activeRow($productId);
        if ($row !== null && $row->popular_comment_divisor !== null) {
            $div = (float) $row->popular_comment_divisor;

            return $div > 0 ? $div : null;
        }

        if (in_array($productId, RankingConfig::popularCommentPenaltyProductIds(), true)) {
            return 2.5;
        }

        return null;
    }

    public static function topsaleQuantityDivisor(int $productId): ?float
    {
        if ($productId < 1) {
            return null;
        }

        $row = self::activeRow($productId);
        if ($row !== null && $row->topsale_quantity_divisor !== null) {
            $div = (float) $row->topsale_quantity_divisor;

            return $div > 0 ? $div : null;
        }

        if (in_array($productId, RankingConfig::topsaleHeldPenaltyProductIds(), true)) {
            return 1.5;
        }

        return null;
    }

    /**
     * @return array{inserted: int, updated: int}
     */
    public static function seedFromLegacyDefaults(): array
    {
        if (! ProductPenaltySchema::tablesVerified()) {
            return ['inserted' => 0, 'updated' => 0];
        }

        $until = RankingConfig::isPenaltyActive()
            ? Carbon::createFromTimestamp(RankingConfig::PENALTY_UNTIL_TIMESTAMP)->format('Y-m-d H:i:s')
            : null;

        $excludeIds = array_unique(array_merge(
            RankingConfig::penaltyProductIds(),
            RankingConfig::popularCommentPenaltyProductIds(),
            RankingConfig::topsaleHeldPenaltyProductIds()
        ));
        $popularDivisorIds = RankingConfig::popularCommentPenaltyProductIds();
        $topsaleDivisorIds = RankingConfig::topsaleHeldPenaltyProductIds();

        $inserted = 0;
        $updated = 0;

        foreach ($excludeIds as $productId) {
            $productId = (int) $productId;
            if ($productId < 1) {
                continue;
            }

            $payload = [
                'product_id' => $productId,
                'exclude_popular' => in_array($productId, RankingConfig::penaltyProductIds(), true) ? 1 : 0,
                'exclude_hottest' => in_array($productId, RankingConfig::penaltyProductIds(), true) ? 1 : 0,
                'exclude_topsale' => in_array($productId, RankingConfig::penaltyProductIds(), true) ? 1 : 0,
                'is_enabled' => 1,
                'popular_comment_divisor' => in_array($productId, $popularDivisorIds, true) ? 2.5 : null,
                'topsale_quantity_divisor' => in_array($productId, $topsaleDivisorIds, true) ? 1.5 : null,
                'active_from' => null,
                'active_until' => $until,
                'note' => 'seeded from RankingConfig legacy lists',
            ];

            $existing = ProductPenalty::query()->where('product_id', $productId)->first();
            if ($existing === null) {
                ProductPenalty::query()->create($payload);
                ++$inserted;
            } else {
                ProductPenalty::query()->where('product_id', $productId)->update($payload);
                ++$updated;
            }
            unset(self::$cache[$productId]);
        }

        return ['inserted' => $inserted, 'updated' => $updated];
    }

    public static function isEffectivelyActive(ProductPenalty $row): bool
    {
        if (array_key_exists('is_enabled', $row->getAttributes()) && ! (bool) $row->is_enabled) {
            return false;
        }

        $now = self::now();

        if ($row->active_from !== null && Carbon::parse((string) $row->active_from)->gt($now)) {
            return false;
        }

        if ($row->active_until !== null && Carbon::parse((string) $row->active_until)->lte($now)) {
            return false;
        }

        return true;
    }

    /**
     * @return array{key: string, label: string, badge: string}
     */
    public static function statusMeta(ProductPenalty $row): array
    {
        if (array_key_exists('is_enabled', $row->getAttributes()) && ! (bool) $row->is_enabled) {
            return ['key' => 'disabled', 'label' => 'غیرفعال', 'badge' => 'badge-ghost'];
        }

        $now = self::now();

        if ($row->active_from !== null && Carbon::parse((string) $row->active_from)->gt($now)) {
            return ['key' => 'scheduled', 'label' => 'آینده', 'badge' => 'badge-warning'];
        }

        if ($row->active_until !== null && Carbon::parse((string) $row->active_until)->lte($now)) {
            return ['key' => 'expired', 'label' => 'منقضی', 'badge' => 'badge-ghost'];
        }

        return ['key' => 'active', 'label' => 'فعال', 'badge' => 'badge-success'];
    }

    public static function cityNameForProduct(int $productId): string
    {
        if ($productId < 1 || ! function_exists('ez_get_product_meta')) {
            return '—';
        }

        $meta = ez_get_product_meta($productId);

        return isset($meta->city_name) && $meta->city_name !== ''
            ? (string) $meta->city_name
            : '—';
    }

    private static function activeRow(int $productId): ?ProductPenalty
    {
        if (! ProductPenaltySchema::tablesVerified()) {
            return null;
        }

        if (array_key_exists($productId, self::$cache)) {
            return self::$cache[$productId];
        }

        $row = ProductPenalty::query()->where('product_id', $productId)->first();
        if ($row !== null && self::isEffectivelyActive($row)) {
            self::$cache[$productId] = $row;

            return $row;
        }

        self::$cache[$productId] = null;

        return null;
    }

    private static function legacyIsPenalized(int $productId, string $facet): bool
    {
        if (! RankingConfig::isPenaltyActive()) {
            return false;
        }

        return match ($facet) {
            'popular', 'hottest', 'topsale' => in_array($productId, RankingConfig::penaltyProductIds(), true),
            default => false,
        };
    }

    private static function now(): Carbon
    {
        if (function_exists('wp_timezone')) {
            return Carbon::now(wp_timezone());
        }

        return Carbon::now();
    }

    private static function normalizeDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $string = is_string($value) ? trim($value) : (string) $value;
        if ($string === '') {
            return null;
        }

        $string = str_replace('T', ' ', $string);
        $parsed = Carbon::parse($string, function_exists('wp_timezone') ? wp_timezone() : null);

        return $parsed->format('Y-m-d H:i:s');
    }

    private static function normalizeNote(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $note = trim((string) $value);

        return $note === '' ? null : mb_substr($note, 0, 255);
    }

    private static function assertDateWindow(?string $from, ?string $until): void
    {
        if ($from === null || $until === null) {
            return;
        }

        if (strtotime($from) > strtotime($until)) {
            throw new \InvalidArgumentException('active_from must be before active_until.');
        }
    }
}
