<?php

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\ProductRatings\Models\RatingCriterion;

final class RatingCriterionController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return RatingCriterion::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
