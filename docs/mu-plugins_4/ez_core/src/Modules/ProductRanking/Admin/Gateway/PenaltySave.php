<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin\Gateway;

use EscapeZoom\Core\Modules\ProductRanking\ProductRankingModule;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

final class PenaltySave
{
    /** @param array<string, mixed> $inputs */
    public static function run(array $inputs, Request $req): Response
    {
        $denied = PenaltyAdminGatewaySupport::authorizeAdmin();
        if ($denied instanceof Response) {
            return $denied;
        }

        $merged = PenaltyAdminGatewaySupport::mergeInput($inputs, $req);

        try {
            $id = isset($merged['penalty_id']) ? absint($merged['penalty_id']) : 0;
            $row = ProductPenaltyRepository::saveFromAdmin([
                'product_id' => absint($merged['product_id'] ?? 0),
                'exclude_popular' => ! empty($merged['exclude_popular']),
                'exclude_hottest' => ! empty($merged['exclude_hottest']),
                'exclude_topsale' => ! empty($merged['exclude_topsale']),
                'is_enabled' => ! empty($merged['is_enabled']),
                'active_from' => isset($merged['active_from']) ? (string) $merged['active_from'] : null,
                'active_until' => isset($merged['active_until']) ? (string) $merged['active_until'] : null,
                'note' => isset($merged['note']) ? (string) $merged['note'] : null,
            ], $id > 0 ? $id : null);

            ProductRankingModule::onActionRecalculate((int) $row->product_id);

            return Response::json(['success' => true])
                ->withHeader('HX-Trigger', '{"penalty-saved":true}');
        } catch (\Throwable $e) {
            return Response::json(
                ['success' => false, 'message' => $e->getMessage()],
                422
            );
        }
    }
}
