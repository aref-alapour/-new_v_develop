<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Models;

use EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema;
use EscapeZoom\Core\Modules\Common\Models\BaseModel;

final class EzAjaxRateBucket extends BaseModel
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'bucket';

    protected $keyType = 'string';

    protected $fillable = [
        'bucket',
        'tokens',
        'refill_at',
    ];

    protected $casts = [
        'tokens' => 'integer',
        'refill_at' => 'integer',
    ];

    public function getTable(): string
    {
        return AjaxGatewaySchema::rateTable();
    }
}
