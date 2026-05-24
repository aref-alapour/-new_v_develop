<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin;

use EscapeZoom\Core\Modules\Games\Admin\Screens\AreaScreen;
use EscapeZoom\Core\Modules\Games\Admin\Screens\CityScreen;
use EscapeZoom\Core\Modules\Games\Admin\Screens\GameTypeScreen;
use EscapeZoom\Core\Modules\Games\Admin\Screens\GenreScreen;
use EscapeZoom\Core\Modules\Games\Admin\Screens\MoodScreen;
use EscapeZoom\Core\Modules\Games\Admin\Screens\ThemeScreen;
use EscapeZoom\Core\Modules\Games\PostType\EZ_Games_CPT;
use EscapeZoom\Core\Modules\Games\PostType\EZ_Games_Metaboxes;
use EscapeZoom\Core\Modules\Games\PostType\EZ_Games_DB;

// Load PostType class files (EZ_Games_CPT and EZ_Games_DB loaded via Composer PSR-4)
require_once __DIR__ . '/EzAddEntityAjax.php';

/**
 * Games Admin Bootstrap.
 * Uses native WordPress CPT UI instead of custom admin pages.
 * Custom data is stored in wp_ez_products table via EZ_Games_DB.
 */
final class AdminBootstrap
{
    private const CAPABILITY = 'manage_options';

    public static function register(): void
    {
        // Register the CPT
        EZ_Games_CPT::register();

        // Register meta boxes
        EZ_Games_Metaboxes::register();

        // Register save handler (syncs to custom table)
        EZ_Games_DB::register();

        // AJAX: add entity (brand, city, area, game_type, ez_user, genre, mood, theme) from relationships metabox
        EzAddEntityAjax::register();

        // AJAX: quick add from modal dialog (game type, genre, theme, city, area)
        add_action('wp_ajax_ez_gt_ajax_save', [GameTypeScreen::class, 'ajaxSave']);
        add_action('wp_ajax_ez_gt_ajax_update', [GameTypeScreen::class, 'ajaxUpdate']);
        add_action('wp_ajax_ez_gt_refresh_table', [GameTypeScreen::class, 'ajaxRefreshTable']);
        add_action('wp_ajax_ez_gen_ajax_save', [GenreScreen::class, 'ajaxSave']);
        add_action('wp_ajax_ez_gen_ajax_update', [GenreScreen::class, 'ajaxUpdate']);
        add_action('wp_ajax_ez_gen_refresh_table', [GenreScreen::class, 'ajaxRefreshTable']);
        add_action('wp_ajax_ez_mood_ajax_save', [MoodScreen::class, 'ajaxSave']);
        add_action('wp_ajax_ez_mood_ajax_update', [MoodScreen::class, 'ajaxUpdate']);
        add_action('wp_ajax_ez_mood_refresh_table', [MoodScreen::class, 'ajaxRefreshTable']);
        add_action('wp_ajax_ez_theme_ajax_save', [ThemeScreen::class, 'ajaxSave']);
        add_action('wp_ajax_ez_theme_ajax_update', [ThemeScreen::class, 'ajaxUpdate']);
        add_action('wp_ajax_ez_theme_refresh_table', [ThemeScreen::class, 'ajaxRefreshTable']);
        add_action('wp_ajax_ez_city_ajax_save', [CityScreen::class, 'ajaxSave']);
        add_action('wp_ajax_ez_city_ajax_update', [CityScreen::class, 'ajaxUpdate']);
        add_action('wp_ajax_ez_city_refresh_table', [CityScreen::class, 'ajaxRefreshTable']);
        add_action('wp_ajax_ez_area_ajax_save', [AreaScreen::class, 'ajaxSave']);
        add_action('wp_ajax_ez_area_ajax_update', [AreaScreen::class, 'ajaxUpdate']);
        add_action('wp_ajax_ez_area_refresh_table', [AreaScreen::class, 'ajaxRefreshTable']);

        // زیرمنوهای دیکشنری (تایپ‌ها، ژانرها، تم‌ها، شهرها، مناطق) زیر منوی بازی‌ها
        add_action('admin_menu', [self::class, 'registerDictionaryMenus'], 15);

        // Enqueue editor assets on CPT pages
        add_action('admin_enqueue_scripts', [self::class, 'enqueueEditorOnGamePages']);

        // Add migration admin action (optional)
        add_action('admin_post_ez_migrate_games', [self::class, 'handleMigration']);
        add_action('admin_notices', [self::class, 'showMigrationNotice']);
    }

    /**
     * Add submenu pages for taxonomy/location dictionaries under Games (ez_game) menu.
     */
    public static function registerDictionaryMenus(): void
    {
        $parent = 'edit.php?post_type=' . EZ_Games_CPT::POST_TYPE;
        $cap = self::CAPABILITY;

        add_submenu_page($parent, __('تایپ‌ها', 'escapezoom-core'), __('تایپ‌ها', 'escapezoom-core'), $cap, 'escapezoom-game-types', [GameTypeScreen::class, 'render']);
        add_submenu_page($parent, __('ژانرها', 'escapezoom-core'), __('ژانرها', 'escapezoom-core'), $cap, 'escapezoom-genres', [GenreScreen::class, 'render']);
        add_submenu_page($parent, __('مودها', 'escapezoom-core'), __('مودها', 'escapezoom-core'), $cap, 'escapezoom-moods', [MoodScreen::class, 'render']);
        add_submenu_page($parent, __('تم‌ها', 'escapezoom-core'), __('تم‌ها', 'escapezoom-core'), $cap, 'escapezoom-themes', [ThemeScreen::class, 'render']);
        add_submenu_page($parent, __('شهرها', 'escapezoom-core'), __('شهرها', 'escapezoom-core'), $cap, 'escapezoom-cities', [CityScreen::class, 'render']);
        add_submenu_page($parent, __('مناطق', 'escapezoom-core'), __('مناطق', 'escapezoom-core'), $cap, 'escapezoom-areas', [AreaScreen::class, 'render']);
    }

    /**
     * Enqueue editor and media scripts on ez_game edit pages and on any EscapeZoom admin page
     * (page=escapezoom* or hook contains 'escapezoom') so wp_editor works on brands, game types, etc.
     */
    public static function enqueueEditorOnGamePages(string $hookSuffix): void
    {
        global $post_type;

        $isEscapeZoomPage = (strpos($hookSuffix, 'escapezoom') !== false);
        $isGameCpt = in_array($hookSuffix, ['post.php', 'post-new.php'], true)
            && isset($post_type) && $post_type === EZ_Games_CPT::POST_TYPE;

        if (!$isEscapeZoomPage && !$isGameCpt) {
            return;
        }

        wp_enqueue_editor();
        wp_enqueue_media();
        wp_enqueue_script('editor');
        wp_enqueue_style('editor-buttons');

        if ($isEscapeZoomPage) {
            $plugin_root = dirname(__DIR__, 4);
            $plugin_file = $plugin_root . '/escapezoom-core.php';
            wp_enqueue_style(
                'ez-games-admin-editor',
                plugins_url('src/Modules/Games/assets/admin-editor.css', $plugin_file),
                [],
                '1.0.0'
            );
        }
    }

    /**
     * Handle migration of existing products to CPT posts.
     */
    public static function handleMigration(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }
        
        check_admin_referer('ez_migrate_games');
        
        $result = EZ_Games_DB::migrate_products_to_posts();
        
        $redirect_url = add_query_arg([
            'post_type'      => EZ_Games_CPT::POST_TYPE,
            'ez_migrated'    => $result['migrated'],
            'ez_failed'      => $result['failed'],
        ], admin_url('edit.php'));
        
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Show migration notice if there are unmigrated products.
     */
    public static function showMigrationNotice(): void
    {
        global $post_type;
        $screen = get_current_screen();
        
        if (!$screen || $screen->post_type !== EZ_Games_CPT::POST_TYPE) {
            return;
        }
        
        // Show success message after migration
        if (isset($_GET['ez_migrated'])) {
            $migrated = (int) $_GET['ez_migrated'];
            $failed = (int) ($_GET['ez_failed'] ?? 0);
            
            if ($migrated > 0 || $failed > 0) {
                $class = $failed > 0 ? 'notice-warning' : 'notice-success';
                echo '<div class="notice ' . $class . ' is-dismissible"><p>';
                printf(
                    __('%d بازی با موفقیت منتقل شد. %d خطا.', 'escapezoom-core'),
                    $migrated,
                    $failed
                );
                echo '</p></div>';
            }
            return;
        }
        
        // Check for products not yet linked to a post (via _ez_product_id meta)
        global $wpdb;
        $table = $wpdb->prefix . 'ez_products';
        $meta = $wpdb->postmeta;
        $count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table} p
             WHERE NOT EXISTS (
                 SELECT 1 FROM {$meta} pm
                 WHERE pm.meta_key = '" . EZ_Games_DB::PRODUCT_ID_META_KEY . "'
                 AND pm.meta_value = CAST(p.product_id AS CHAR)
             )"
        );
        
        if ($count > 0) {
            $migrate_url = wp_nonce_url(
                admin_url('admin-post.php?action=ez_migrate_games'),
                'ez_migrate_games'
            );
            
            echo '<div class="notice notice-info"><p>';
            printf(
                __('%d بازی قدیمی هنوز به CPT منتقل نشده‌اند. ', 'escapezoom-core'),
                $count
            );
            echo '<a href="' . esc_url($migrate_url) . '" class="button button-primary">';
            echo esc_html__('انتقال همه', 'escapezoom-core');
            echo '</a></p></div>';
        }
    }
}

