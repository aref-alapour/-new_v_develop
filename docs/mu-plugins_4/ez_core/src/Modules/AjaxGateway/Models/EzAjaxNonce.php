<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Models;

use EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema;
use EscapeZoom\Core\Modules\Common\Models\BaseModel;

final class EzAjaxNonce extends BaseModel
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'nonce';

    protected $keyType = 'string';

    protected $fillable = [
        'nonce',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'integer',
    ];

    public function getTable(): string
    {
        return AjaxGatewaySchema::noncesTable();
    }
}
