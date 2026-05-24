<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Concerns\CustomTimestamps;
use Corcel\Concerns\MetaFields;
use Corcel\Model;
use Corcel\Model\Builder\CommentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * مدل کامنت وردپرس (wp_comments).
 */
class Comment extends Model
{
    use MetaFields;
    use CustomTimestamps;

    const CREATED_AT = 'comment_date';
    const UPDATED_AT = null;

    protected $connection = 'wordpress';
    protected $table = 'comments';
    protected $primaryKey = 'comment_ID';
    protected $dates = ['comment_date'];

    /** پست مربوطه */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'comment_post_ID');
    }

    /** کاربر نویسنده (اگر لاگین بوده) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** کامنت والد */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_parent');
    }

    /** همان parent با نام دیگر */
    public function original(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_parent');
    }

    /** پاسخ‌ها */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_parent');
    }

    public static function findByPostId(int $postId)
    {
        return (new static())->where('comment_post_ID', $postId)->get();
    }

    public function newEloquentBuilder($query)
    {
        return new CommentBuilder($query);
    }
}
