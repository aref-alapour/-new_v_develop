<?php

namespace EscapeZoom\Core\Modules\Finance\Services;

use EscapeZoom\Core\Modules\Finance\Models\WalletTransaction;

class WalletLedgerService
{
    public function listUserTransactions(int $userId, int $limit = 50)
    {
        return WalletTransaction::query()
            ->where('user_id', $userId)
            ->orderByDesc('ID')
            ->limit($limit)
            ->get();
    }
}
