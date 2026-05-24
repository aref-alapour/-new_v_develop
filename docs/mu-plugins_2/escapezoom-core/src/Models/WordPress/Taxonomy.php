<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Model;
use Corcel\Model\Builder\TaxonomyBuilder;
use Corcel\Model\Meta\TermMeta;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * مدل تاکسونومی وردپرس (wp_term_taxonomy) – دسته، تگ، product_cat و غیره.
 */
class Taxonomy extends Model
{
    protected $connection = 'wordpress';
    protected $table = 'term_taxonomy';
    protected $primaryKey = 'term_taxonomy_id';
    protected $with = ['term'];
    public $timestamps = false;

    /** ترم مرتبط */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    /** تاکسونومی والد (برای زیردسته‌ها) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'parent');
    }

    /** متاهای ترم */
    public function meta(): HasMany
    {
        return $this->hasMany(TermMeta::class, 'term_id');
    }

    /** پست‌های دارای این تاکسونومی */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id'
        );
    }

    public function newEloquentBuilder($query)
    {
        return new TaxonomyBuilder($query);
    }

    public function newQuery()
    {
        return isset($this->taxonomy) && $this->taxonomy
            ? parent::newQuery()->where('taxonomy', $this->taxonomy)
            : parent::newQuery();
    }

    public function __get($key)
    {
        if (!isset($this->$key) && isset($this->term->$key)) {
            return $this->term->$key;
        }
        return parent::__get($key);
    }
}
