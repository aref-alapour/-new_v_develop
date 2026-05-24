<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin\Gateway;

use EscapeZoom\Core\Modules\ProductRanking\ProductRankingModule;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

final class PenaltyDelete
{
    /** @param array<string, mixed> $inputs */
    public static function run(array $inputs, Request $req): Response
    {
        $denied = PenaltyAdminGatewaySupport::authorizeAdmin();
        if ($denied instanceof Response) {
            return $denied;
        }

        $merged = PenaltyAdminGatewaySupport::mergeInput($inputs, $req);
        $id = absint($merged['id'] ?? 0);
        $row = $id > 0 ? ProductPenaltyRepository::findById($id) : null;

        if ($row !== null && ProductPenaltyRepository::deleteById($id)) {
            ProductRankingModule::onActionRecalculate((int) $row->product_id);
        }

        return Response::json(['success' => true])
            ->withHeader('HX-Trigger', '{"penalty-deleted":true}');
    }
}
