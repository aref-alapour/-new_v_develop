<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Model\Meta\Meta as CorcelMeta;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * مدل پست‌متا وردپرس (wp_postmeta).
 */
class PostMeta extends CorcelMeta
{
    protected $connection = 'wordpress';
    protected $table = 'postmeta';
    protected $fillable = ['meta_key', 'meta_value', 'post_id'];

    /** پست مربوطه */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
