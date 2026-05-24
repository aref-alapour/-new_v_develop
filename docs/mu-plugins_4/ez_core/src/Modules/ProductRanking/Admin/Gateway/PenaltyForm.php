<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin\Gateway;

use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminPresenter;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

final class PenaltyForm
{
    /** @param array<string, mixed> $inputs */
    public static function run(array $inputs, Request $req): Response
    {
        $denied = PenaltyAdminGatewaySupport::authorizeAdmin();
        if ($denied instanceof Response) {
            return $denied;
        }

        $id = isset($inputs['id']) ? max(0, (int) $inputs['id']) : 0;
        $row = $id > 0 ? ProductPenaltyRepository::findById($id) : null;
        $html = ProductPenaltyAdminPresenter::captureFormInnerHtml($row);

        return Response::html($html)
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
