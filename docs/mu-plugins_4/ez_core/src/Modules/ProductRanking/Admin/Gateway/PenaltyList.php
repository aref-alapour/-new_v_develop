<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin\Gateway;

use EscapeZoom\Core\Core\Bootstrap;
use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminFilters;
use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminPresenter;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

final class PenaltyList
{
    /** @param array<string, mixed> $inputs */
    public static function run(array $inputs, Request $req): Response
    {
        PenaltyGatewayWpShims::ensure();
        Bootstrap::bootDataLayerOnly();

        $merged = PenaltyAdminGatewaySupport::mergeInput($inputs, $req);
        $filters = ProductPenaltyAdminFilters::fromRequest($merged);
        $html = ProductPenaltyAdminPresenter::captureTableHtml($filters);

        return Response::html($html)
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
