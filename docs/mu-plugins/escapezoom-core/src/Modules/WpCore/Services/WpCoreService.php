<?php

namespace EscapeZoom\Core\Modules\WpCore\Services;

use EscapeZoom\Core\Modules\WpCore\Models\Option;
use EscapeZoom\Core\Modules\WpCore\Models\Post;
use EscapeZoom\Core\Modules\WpCore\Models\PostMeta;
use EscapeZoom\Core\Modules\WpCore\Models\Term;
use EscapeZoom\Core\Modules\WpCore\Models\TermTaxonomy;
use EscapeZoom\Core\Modules\WpCore\Models\User;

class WpCoreService
{
    public function getUserById(int $userId): ?User
    {
        return User::query()->where('ID', $userId)->first();
    }

    public function getPostById(int $postId): ?Post
    {
        return Post::query()->where('ID', $postId)->first();
    }

    public function getPostMeta(int $postId, ?string $metaKey = null)
    {
        $query = PostMeta::query()->where('post_id', $postId);
        if ($metaKey !== null && $metaKey !== '') {
            $query->where('meta_key', $metaKey);
        }
        return $query->get();
    }

    public function getTermsByTaxonomy(string $taxonomy, int $limit = 100)
    {
        return Term::query()
            ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
            ->where('wp_term_taxonomy.taxonomy', $taxonomy)
            ->limit($limit)
            ->get(['wp_terms.*', 'wp_term_taxonomy.term_taxonomy_id', 'wp_term_taxonomy.taxonomy', 'wp_term_taxonomy.parent']);
    }

    public function getOptionByName(string $optionName): ?Option
    {
        return Option::query()->where('option_name', $optionName)->first();
    }
}
