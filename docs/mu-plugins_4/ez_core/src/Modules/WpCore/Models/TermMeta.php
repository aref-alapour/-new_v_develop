<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Database\WpTableNames;
use EscapeZoom\Core\Modules\Common\Models\BaseModel;

final class TermMeta extends BaseModel
{
    public $timestamps = false;

    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'term_id',
        'meta_key',
        'meta_value',
    ];

    protected $casts = [
        'term_id' => 'integer',
    ];

    public function getTable(): string
    {
        return WpTableNames::termMeta();
    }
}
