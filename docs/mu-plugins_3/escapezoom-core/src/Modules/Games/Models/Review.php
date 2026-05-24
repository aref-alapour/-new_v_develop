<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Review (جدول wp_ez_reviews). نظرات محصولات؛ جایگزین wp_comments برای نوع review.
 * product_id = ez_products.product_id.
 * مدل نازک.
 */
class Review extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_reviews';

    protected $fillable = [
        'product_id',
        'user_id',
        'content',
        'status',
        'score_decor',
        'score_puzzle',
        'score_scare',
        'score_behavior',
        'score_creative',
        'avg_rating',
        'weight',
        'reply_content',
        'reply_updated_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'user_id' => 'integer',
        'score_decor' => 'integer',
        'score_puzzle' => 'integer',
        'score_scare' => 'integer',
        'score_behavior' => 'integer',
        'score_creative' => 'integer',
        'avg_rating' => 'float',
        'weight' => 'integer',
        'reply_updated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
