<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Concerns\AdvancedCustomFields;
use Corcel\Concerns\MetaFields;
use Corcel\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * مدل ترم وردپرس (wp_terms).
 */
class Term extends Model
{
    use MetaFields;
    use AdvancedCustomFields;

    protected $connection = 'wordpress';
    protected $table = 'terms';
    protected $primaryKey = 'term_id';
    public $timestamps = false;

    /** تاکسونومی مرتبط (هر ترم یک ردیف در term_taxonomy دارد برای هر taxonomy) */
    public function taxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'term_id');
    }
}
