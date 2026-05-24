<?php

namespace EscapeZoom\Core\Modules\ProductRatings;

use EscapeZoom\Core\Modules\ProductRatings\Models\RatingCriterion;
use EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup;

final class RatingCriteriaSeeder
{
    /**
     * Insert default criteria rows when missing (idempotent).
     */
    public static function ensure(): void
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return;
        }

        $rows = [
            ['atmosphere', 'فضاسازی', 1],
            ['puzzle', 'کیفیت معما', 2],
            ['creativity', 'تازگی و خلاقیت', 3],
            ['acting', 'بازیگردانی و اکت', 4],
            ['staff', 'برخورد پرسنل', 5],
        ];

        foreach ($rows as $r) {
            RatingCriterion::query()->firstOrCreate(
                ['slug' => $r[0]],
                ['label' => $r[1], 'sort_order' => $r[2], 'active' => true]
            );
        }

        RatingCriterionLookup::reset();
    }
}
