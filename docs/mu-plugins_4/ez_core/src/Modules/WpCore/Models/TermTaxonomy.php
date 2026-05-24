<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\WpCore\Models;

use EscapeZoom\Core\Database\WpTableNames;
use EscapeZoom\Core\Modules\Common\Models\BaseModel;

final class TermTaxonomy extends BaseModel
{
    public $timestamps = false;

    protected $primaryKey = 'term_taxonomy_id';

    protected $fillable = [
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
    ];

    protected $casts = [
        'term_id' => 'integer',
        'parent' => 'integer',
        'count' => 'integer',
    ];

    public function getTable(): string
    {
        return WpTableNames::termTaxonomy();
    }
}
