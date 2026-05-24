<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin;

final class ProductPenaltyAdminFilters
{
    /** @param array<string, mixed> $input */
    public static function fromRequest(array $input): self
    {
        $facets = [];
        $facetInput = $input['facet'] ?? $input['facet[]'] ?? null;
        if (is_array($facetInput)) {
            foreach ($facetInput as $facet) {
                $facet = self::sanitizeKey((string) $facet);
                if (in_array($facet, ['hottest', 'popular', 'topsale'], true)) {
                    $facets[] = $facet;
                }
            }
        }

        $productId = max(0, (int) ($input['product_id'] ?? 0));
        $search = isset($input['s']) ? self::sanitizeString((string) $input['s']) : '';
        if ($productId > 0) {
            $search = '';
        }

        return new self(
            page: max(1, (int) ($input['paged'] ?? 1)),
            perPage: min(50, max(10, (int) ($input['per_page'] ?? 20))),
            search: $search,
            productId: $productId,
            facets: $facets,
            penaltyFrom: self::date($input['penalty_from'] ?? null),
            penaltyUntil: self::date($input['penalty_until'] ?? null),
            createdFrom: self::date($input['created_from'] ?? null),
            createdUntil: self::date($input['created_until'] ?? null),
            updatedFrom: self::date($input['updated_from'] ?? null),
            updatedUntil: self::date($input['updated_until'] ?? null),
        );
    }

    /**
     * @param list<string> $facets
     */
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 20,
        public readonly string $search = '',
        public readonly int $productId = 0,
        public readonly array $facets = [],
        public readonly ?string $penaltyFrom = null,
        public readonly ?string $penaltyUntil = null,
        public readonly ?string $createdFrom = null,
        public readonly ?string $createdUntil = null,
        public readonly ?string $updatedFrom = null,
        public readonly ?string $updatedUntil = null,
    ) {
    }

    private static function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $string = self::sanitizeString((string) $value);

        return $string !== '' ? $string : null;
    }

    private static function sanitizeString(string $value): string
    {
        $value = trim($value);
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($value);
        }

        return strip_tags($value);
    }

    private static function sanitizeKey(string $value): string
    {
        if (function_exists('sanitize_key')) {
            return sanitize_key($value);
        }

        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($value)) ?? '';
    }
}
