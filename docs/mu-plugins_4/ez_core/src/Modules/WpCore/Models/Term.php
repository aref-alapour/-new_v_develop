<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Database\WpTableNames;
use EscapeZoom\Core\Modules\Common\Models\BaseModel;

final class Term extends BaseModel
{
    public $timestamps = false;

    protected $primaryKey = 'term_id';

    protected $fillable = [
        'name',
        'slug',
        'term_group',
    ];

    public function getTable(): string
    {
        return WpTableNames::terms();
    }
}
