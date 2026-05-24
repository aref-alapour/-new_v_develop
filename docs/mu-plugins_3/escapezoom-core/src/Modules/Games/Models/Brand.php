<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Brand — one brand has many products (games). Thin model.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $logo
 * @property string|null $description
 * @property int|null $thumbnail_id
 * @property string|null $address
 * @property string|null $phone Brand phone number
 * @property string|null $instagram Instagram URL
 * @property string|null $website Website URL
 * @property int|null $established_year Year brand was established
 * @property float $score
 * @property int $reputation
 * @property array|null $game_types
 * @property array|null $teams
 */
class Brand extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_brands';

    protected $fillable = [
        'title',
        'slug',
        'logo',
        'description',
        'thumbnail_id',
        'address',
        'phone',
        'instagram',
        'website',
        'established_year',
        'score',
        'reputation',
        'game_types',
        'teams',
    ];

    protected $casts = [
        'thumbnail_id' => 'integer',
        'established_year' => 'integer',
        'score' => 'float',
        'reputation' => 'integer',
        'game_types' => 'array',
        'teams' => 'array',
    ];

    /**
     * Get all products (games) belonging to this brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id', 'id');
    }

    /**
     * Get only active/published games for this brand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class, 'brand_id', 'id')
            ->where('status', 'publish');
    }

    /**
     * Get games count for display.
     */
    public function getGamesCountAttribute(): int
    {
        return $this->activeProducts()->count();
    }

    /**
     * Get thumbnail URL from attachment ID.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_id) {
            return $this->logo;
        }
        return wp_get_attachment_image_url($this->thumbnail_id, 'medium');
    }

    /**
     * Get full URL for the brand single page.
     */
    public function getUrlAttribute(): string
    {
        return home_url('/brand/' . $this->slug);
    }
}
