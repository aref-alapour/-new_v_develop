<?php

namespace EscapeZoom\Core\Modules\ProductRatings\Services;

use EscapeZoom\Core\Modules\ProductRatings\Models\RatingCriterion;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema;

final class RatingCriterionLookup
{
    /** @var array<string,int>|null */
    private static ?array $slugToId = null;

    /** @var array<int,string>|null */
    private static ?array $idToSlug = null;

    public static function reset(): void
    {
        self::$slugToId = null;
        self::$idToSlug = null;
    }

    /**
     * @return array<string,int>
     */
    public static function slugToIdMap(): array
    {
        self::ensureLoaded();

        return self::$slugToId ?? [];
    }

    public static function idForSlug(string $slug): int
    {
        return (int) (self::slugToIdMap()[ $slug ] ?? 0);
    }

    public static function slugForId(int $id): string
    {
        self::ensureLoaded();

        return (string) (self::$idToSlug[ $id ] ?? '');
    }

    private static function ensureLoaded(): void
    {
        if (self::$slugToId !== null) {
            return;
        }

        self::$slugToId = [];
        self::$idToSlug = [];

        if (! ProductRatingsSchema::tablesVerified()) {
            return;
        }

        $rows = RatingCriterion::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get([ 'id', 'slug' ]);

        foreach ($rows as $row) {
            $slug = (string) $row->slug;
            $id = (int) $row->id;
            if ($slug !== '' && $id > 0) {
                self::$slugToId[ $slug ] = $id;
                self::$idToSlug[ $id ] = $slug;
            }
        }
    }
}
