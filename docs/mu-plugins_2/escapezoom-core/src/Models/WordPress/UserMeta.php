<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Model\Meta\Meta as CorcelMeta;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * مدل یوزرمتای وردپرس (wp_usermeta).
 */
class UserMeta extends CorcelMeta
{
    protected $connection = 'wordpress';
    protected $table = 'usermeta';
    protected $primaryKey = 'umeta_id';
    protected $fillable = ['meta_key', 'meta_value', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
