<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\WpData\Controllers;

use EscapeZoom\Core\Modules\ProductRanking\Models\ProductPenalty;

final class ProductPenaltyController extends AbstractWpTableCrudController
{
    protected static function modelClass(): string
    {
        return ProductPenalty::class;
    }

    protected static function primaryKey(): string
    {
        return 'id';
    }
}
