<?php

namespace EscapeZoom\Core\Modules\Finance\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class WalletTransaction extends BaseModel
{
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'user_id',
        'amount',
        'balance',
        'type',
        'description',
        'created_at',
        'updated_at',
    ];
}
