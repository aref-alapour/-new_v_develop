<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\Analytics\Actions;
use EscapeZoom\Core\Models\ProductView;
final class RecordProductViewAction {
    public function execute(int $pid, string $ip, string $agent, string $referer): bool {
        return (bool)ProductView::create(['product_id'=>$pid, 'ip'=>$ip, 'agent'=>$agent, 'referer'=>$referer, 'timestamp'=>time()]);
    }
}
