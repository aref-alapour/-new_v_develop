<?php

namespace EscapeZoom\Core\Models\WordPress;

use Corcel\Concerns\AdvancedCustomFields;
use Corcel\Concerns\Aliases;
use Corcel\Concerns\CustomTimestamps;
use Corcel\Concerns\MetaFields;
use Corcel\Concerns\OrderScopes;
use Corcel\Concerns\Shortcodes;
use Corcel\Corcel;
use Corcel\Model;
use Corcel\Model\Builder\PostBuilder;
use Corcel\Model\Meta\ThumbnailMeta;
use EscapeZoom\Core\Models\WordPress\PostMeta;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * مدل پست وردپرس (جداول wp_posts) با روابط کامل.
 * از Corcel گسترش یافته؛ همه‌ی relations به مدل‌های همین پلاگین اشاره می‌کنند.
 */
class Post extends Model
{
    use Aliases;
    use AdvancedCustomFields;
    use MetaFields;
    use Shortcodes;
    use OrderScopes;
    use CustomTimestamps;

    const CREATED_AT = 'post_date';
    const UPDATED_AT = 'post_modified';

    protected $connection = 'wordpress';
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    protected $dates = ['post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt'];
    protected $with = ['meta'];

    protected static $postTypes = [];

    protected $fillable = [
        'post_content',
        'post_title',
        'post_excerpt',
        'post_type',
        'to_ping',
        'pinged',
        'post_content_filtered',
    ];

    protected $appends = [
        'title',
        'slug',
        'content',
        'type',
        'mime_type',
        'url',
        'author_id',
        'parent_id',
        'created_at',
        'updated_at',
        'excerpt',
        'status',
        'image',
        'terms',
        'main_category',
        'keywords',
        'keywords_str',
    ];

    protected static $aliases = [
        'title' => 'post_title',
        'content' => 'post_content',
        'excerpt' => 'post_excerpt',
        'slug' => 'post_name',
        'type' => 'post_type',
        'mime_type' => 'post_mime_type',
        'url' => 'guid',
        'author_id' => 'post_author',
        'parent_id' => 'post_parent',
        'created_at' => 'post_date',
        'updated_at' => 'post_modified',
        'status' => 'post_status',
    ];

    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->getPostInstance((array) $attributes);
        $model->exists = true;
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->getConnectionName());
        $model->fireModelEvent('retrieved', false);
        return $model;
    }

    protected function getPostInstance(array $attributes)
    {
        $class = static::class;
        if (isset($attributes['post_type']) && $attributes['post_type']) {
            if (isset(static::$postTypes[$attributes['post_type']])) {
                $class = static::$postTypes[$attributes['post_type']];
            } elseif (Corcel::isLaravel()) {
                $postTypes = config('corcel.post_types');
                if (is_array($postTypes) && isset($postTypes[$attributes['post_type']])) {
                    $class = $postTypes[$attributes['post_type']];
                }
            }
        }
        return new $class();
    }

    public function newEloquentBuilder($query)
    {
        return new PostBuilder($query);
    }

    public function newQuery()
    {
        return $this->postType
            ? parent::newQuery()->type($this->postType)
            : parent::newQuery();
    }

    /** نویسنده پست */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    /** پست والد (مثلاً برای صفحات) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_parent');
    }

    /** پست‌های فرزند */
    public function children(): HasMany
    {
        return $this->hasMany(Post::class, 'post_parent');
    }

    /** تاکسونومی‌ها (دسته‌ها، تگ‌ها و غیره) */
    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(
            Taxonomy::class,
            'term_relationships',
            'object_id',
            'term_taxonomy_id'
        );
    }

    /** کامنت‌ها */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_post_ID');
    }

    /** متاهای پست (با اتصال wordpress) */
    public function meta(): HasMany
    {
        return $this->hasMany(PostMeta::class, 'post_id');
    }

    /** مالک محصول (از متا user_ebtal → wp_users) برای محصولات */
    public function owner(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            PostMeta::class,
            'post_id',   // FK on postmeta → posts
            'ID',        // FK on users (meta_value = user ID)
            'ID',        // local key on posts
            'meta_value' // FK on postmeta → users.ID
        )->where('meta_key', 'user_ebtal');
    }

    /** تصویر شاخص */
    public function thumbnail()
    {
        return $this->hasOne(ThumbnailMeta::class, 'post_id')
            ->where('meta_key', '_thumbnail_id');
    }

    /** پیوست‌ها */
    public function attachment(): HasMany
    {
        return $this->hasMany(Post::class, 'post_parent')
            ->where('post_type', 'attachment');
    }

    /** ریویژن‌ها */
    public function revision(): HasMany
    {
        return $this->hasMany(Post::class, 'post_parent')
            ->where('post_type', 'revision');
    }

    public function hasTerm(string $taxonomy, string $term): bool
    {
        return isset($this->terms[$taxonomy]) && isset($this->terms[$taxonomy][$term]);
    }

    public function getPostType()
    {
        return $this->postType;
    }

    public function getContentAttribute()
    {
        return $this->stripShortcodes($this->post_content);
    }

    public function getExcerptAttribute()
    {
        return $this->stripShortcodes($this->post_excerpt);
    }

    public function getImageAttribute()
    {
        if ($this->thumbnail && $this->thumbnail->attachment) {
            return $this->thumbnail->attachment->guid;
        }
        return null;
    }

    public function getTermsAttribute()
    {
        return $this->taxonomies->groupBy(function ($taxonomy) {
            return $taxonomy->taxonomy === 'post_tag' ? 'tag' : $taxonomy->taxonomy;
        })->map(function ($group) {
            return $group->mapWithKeys(function ($item) {
                return [$item->term->slug => $item->term->name];
            });
        })->toArray();
    }

    public function getMainCategoryAttribute()
    {
        $terms = $this->terms;
        return $terms['category'][0] ?? ($terms['product_cat'][0] ?? null);
    }

    public function getKeywordsAttribute()
    {
        $terms = $this->terms;
        return $terms['tag'] ?? [];
    }

    public function getKeywordsStrAttribute()
    {
        return is_array($this->keywords) ? implode(', ', $this->keywords) : '';
    }
}
