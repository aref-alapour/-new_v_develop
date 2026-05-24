<?php

namespace EscapeZoom\Core\Modules\ProductRatings;

/**
 * Legacy comment_rating meta keys → criterion slug (aligned with Ez_Product_Ratings_Schema).
 *
 * @return array<int,string>
 */
final class ProductRatingsLegacyMap
{
    /**
     * @return array<int,string>
     */
    public static function termKeyToSlugMap(): array
    {
        return [
            1094 => 'atmosphere',
            1095 => 'puzzle',
            1098 => 'creativity',
            1096 => 'acting',
            1097 => 'staff',
        ];
    }
}
