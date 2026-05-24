<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Repositories;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema;
use EscapeZoom\Core\Modules\AjaxGateway\Models\EzAjaxNonce;
use Illuminate\Database\Capsule\Manager as Capsule;

final class EzAjaxNonceRepository
{
    /**
     * Atomically claim a nonce (INSERT IGNORE semantics).
     *
     * @throws \RuntimeException On storage failure.
     */
    public function useOnce(string $nonce, int $ttlSeconds): bool
    {
        $this->ensureCapsule();

        $digest = hash('sha256', $nonce, true);
        $expiresAt = time() + max(1, $ttlSeconds);

        $inserted = (int) Capsule::connection(CapsuleBoot::CONNECTION_WP)
            ->table(AjaxGatewaySchema::noncesTable())
            ->insertOrIgnore([
                'nonce' => $digest,
                'expires_at' => $expiresAt,
            ]);

        if (mt_rand(0, 99) === 0) {
            $this->gc();
        }

        return $inserted === 1;
    }

    public function gc(): void
    {
        $this->ensureCapsule();

        EzAjaxNonce::query()
            ->where('expires_at', '<', time())
            ->limit(500)
            ->delete();
    }

    private function ensureCapsule(): void
    {
        if (! CapsuleBoot::isBooted()) {
            throw new \RuntimeException('Eloquent data layer is not booted for EZ AJAX nonce store.');
        }
    }
}
