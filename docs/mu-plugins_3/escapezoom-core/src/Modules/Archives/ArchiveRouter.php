<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Archives;

/**
 * روتینگ آرشیو: query_vars، rewrite rules، template_redirect و لود قالب از جدول ez_archives_map.
 */
final class ArchiveRouter
{
    public const QUERY_VAR_TYPE = 'ez_path_type';
    public const QUERY_VAR_SLUG = 'ez_slug';
    public const QUERY_VAR_GAME_TYPE = 'ez_game_type_slug';
    public const CONTEXT_GLOBAL = 'ez_archive_context';

    public static function register(): void
    {
        add_filter('query_vars', [self::class, 'addQueryVars']);
        add_action('init', [self::class, 'addRewriteRules'], 12);
        add_action('template_redirect', [self::class, 'maybeLoadArchive'], 5);
    }

    /** @param array<string> $vars */
    public static function addQueryVars(array $vars): array
    {
        $vars[] = self::QUERY_VAR_TYPE;
        $vars[] = self::QUERY_VAR_SLUG;
        $vars[] = self::QUERY_VAR_GAME_TYPE;
        return $vars;
    }

    public static function addRewriteRules(): void
    {
        $top = [
            'city/([^/]+)/?$' => 'index.php?' . self::QUERY_VAR_TYPE . '=city&' . self::QUERY_VAR_SLUG . '=$matches[1]',
            'type/([^/]+)/?$' => 'index.php?' . self::QUERY_VAR_TYPE . '=type&' . self::QUERY_VAR_SLUG . '=$matches[1]',
            // Taxonomy: /game-type-slug/genre|theme|mode/term-slug — segment 1 = game type, 2 = path_type, 3 = slug
            '([^/]+)/(genre|theme|mode)/([^/]+)/?$' => 'index.php?' . self::QUERY_VAR_GAME_TYPE . '=$matches[1]&' . self::QUERY_VAR_TYPE . '=$matches[2]&' . self::QUERY_VAR_SLUG . '=$matches[3]',
        ];
        foreach ($top as $regex => $redirect) {
            add_rewrite_rule($regex, $redirect, 'top');
        }

        if (get_option('ez_archives_rewrite_flushed', 0) < 2) {
            flush_rewrite_rules(true);
            update_option('ez_archives_rewrite_flushed', 2);
        }
    }

    private static function serve404(): void
    {
        global $wp_query;
        status_header(404);
        nocache_headers();
        $wp_query->set_404();
        if (function_exists('get_404_template')) {
            /** @var string $template */
            $template = get_404_template();
            if ($template !== '') {
                include $template;
                exit;
            }
        }
        exit;
    }

    public static function maybeLoadArchive(): void
    {
        $path_type = (string) get_query_var(self::QUERY_VAR_TYPE, '');
        $slug = (string) get_query_var(self::QUERY_VAR_SLUG, '');
        if ($path_type === '' || $slug === '') {
            return;
        }
        // URL segment "mode" maps to DB path_type "mood"
        if ($path_type === 'mode') {
            $path_type = 'mood';
        }

        global $wpdb;
        $mapTable = $wpdb->prefix . 'ez_archives_map';
        $filtersTable = $wpdb->prefix . 'ez_archive_filters';
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$mapTable} WHERE path_type = %s AND slug = %s AND is_active = 1 LIMIT 1",
                $path_type,
                $slug
            )
        );

        if (!$row || !isset($row->post_id)) {
            self::serve404();
            return;
        }

        $filters = $wpdb->get_results($wpdb->prepare(
            "SELECT filter_type, filter_value FROM {$filtersTable} WHERE archive_map_id = %d",
            (int) $row->id
        ));
        $city_id = null;
        $area_id = null;
        $type_id = null;
        $genre_id = null;
        $mood_id = null;
        $theme_id = null;
        foreach ($filters ?: [] as $f) {
            $val = (int) $f->filter_value;
            if ($f->filter_type === 'city_id') {
                $city_id = $val;
            } elseif ($f->filter_type === 'area_id') {
                $area_id = $val;
            } elseif ($f->filter_type === 'type_id') {
                $type_id = $val;
            } elseif ($f->filter_type === 'genre_id') {
                $genre_id = $val;
            } elseif ($f->filter_type === 'mood_id') {
                $mood_id = $val;
            } elseif ($f->filter_type === 'theme_id') {
                $theme_id = $val;
            }
        }

        if (in_array($path_type, ['genre', 'theme', 'mood'], true)) {
            $game_type_slug = (string) get_query_var(self::QUERY_VAR_GAME_TYPE, '');
            if ($game_type_slug === '') {
                self::serve404();
                return;
            }
            $type_row = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ez_game_types WHERE slug = %s AND is_active = 1 LIMIT 1",
                $game_type_slug
            ));
            $expected_type_id = $type_row ? (int) $type_row->id : 0;
            if ($expected_type_id === 0 || $type_id !== $expected_type_id) {
                self::serve404();
                return;
            }
        }

        $post_id = (int) $row->post_id;
        $post = get_post($post_id);
        if (!$post || $post->post_type !== \EscapeZoom\Core\Modules\Archives\PostType\EZ_Archive_CPT::POST_TYPE || $post->post_status !== 'publish') {
            self::serve404();
            return;
        }

        $GLOBALS[self::CONTEXT_GLOBAL] = (object) [
            'path_type' => $row->path_type,
            'slug'     => $row->slug,
            'city_id'  => $city_id,
            'area_id'  => $area_id,
            'type_id'  => $type_id,
            'genre_id' => $genre_id,
            'mood_id'  => $mood_id,
            'theme_id' => $theme_id,
            'post_id'  => $post_id,
            'row'      => $row,
        ];

        $template = dirname(__DIR__, 3) . '/templates/single-ez_archive.php';
        if (is_file($template)) {
            include $template;
            exit;
        }
    }
}
