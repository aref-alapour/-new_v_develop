<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Blocks;

/**
 * Bootstraps Gutenberg blocks: category, editor assets, and block registration.
 * All EZ Stencil components must have a corresponding block registered here.
 *
 * Editor scripts and styles are loaded from the core plugin dist (index.js + index.cjs.js + front-bundle.css).
 * Only when the active theme is escapezoom-v3. Run "npm run build" in escapezoom-core/assets to build.
 */
final class BlocksBootstrap
{
    private const THEME_SLUG = 'escapezoom-v3';
    private const BLOCKS_DIR = __DIR__ . '/block-definitions';

    private static function pluginRootFile(): string
    {
        return dirname(__DIR__, 2) . '/escapezoom-core.php';
    }

    public static function boot(): void
    {
        BlockCategoryRegistrar::register();
        add_action('init', [self::class, 'registerBlocks'], 20);
        add_action('enqueue_block_editor_assets', [self::class, 'enqueueEditorAssets'], 5);
    }

    public static function registerBlocks(): void
    {
        $blocks = self::getBlockSlugs();
        $base   = self::BLOCKS_DIR;

        foreach ($blocks as $slug) {
            $dir = $base . '/' . $slug;
            if (!is_dir($dir)) {
                continue;
            }
            $args = [
                'render_callback' => BlockRenderResolver::callbackFor($slug),
            ];
            \register_block_type_from_metadata($dir, $args);
        }
    }

    public static function enqueueEditorAssets(): void
    {
        if (get_template() !== self::THEME_SLUG) {
            return;
        }

        $plugin_root = self::pluginRootFile();
        $plugin_dir  = dirname($plugin_root);
        $ver         = (defined('WP_DEBUG') && WP_DEBUG) ? (string) time() : '1.0.0';

        $esm_path = $plugin_dir . '/dist/js/index.js';
        if (is_file($esm_path)) {
            wp_enqueue_script(
                'ez-components-esm',
                plugins_url('dist/js/index.js', $plugin_root),
                [],
                (string) filemtime($esm_path),
                true
            );
        }

        $nomodule_path = $plugin_dir . '/dist/js/index.cjs.js';
        if (is_file($nomodule_path)) {
            wp_enqueue_script(
                'ez-components-nomodule',
                plugins_url('dist/js/index.cjs.js', $plugin_root),
                [],
                (string) filemtime($nomodule_path),
                true
            );
        }

        add_filter('script_loader_tag', [self::class, 'filterEzComponentsScriptTag'], 10, 3);

        $css_path = $plugin_dir . '/dist/css/front-bundle.css';
        if (is_file($css_path)) {
            wp_enqueue_style(
                'escapezoom-core-editor',
                plugins_url('dist/css/front-bundle.css', $plugin_root),
                [],
                (string) filemtime($css_path)
            );
        }
    }

    /**
     * Ensures ESM script has type="module" and legacy script has nomodule in the block editor.
     * Only modifies tags for handles ez-components-esm and ez-components-nomodule; all other handles unchanged.
     * To verify load in editor console: customElements.get('ez-button') should return a function/class, not undefined.
     *
     * @param string $tag    The script tag.
     * @param string $handle The script handle.
     * @param string $src    The script src.
     * @return string
     */
    public static function filterEzComponentsScriptTag(string $tag, string $handle, string $src): string
    {
        if ($handle !== 'ez-components-esm' && $handle !== 'ez-components-nomodule') {
            return $tag;
        }
        // Match opening <script (with or without space after) so WP output always gets the attribute.
        if ($handle === 'ez-components-esm') {
            return preg_replace('#<script\s?#', '<script type="module" ', $tag, 1);
        }
        if ($handle === 'ez-components-nomodule') {
            return preg_replace('#<script\s?#', '<script nomodule ', $tag, 1);
        }
        return $tag;
    }

    /**
     * @return list<string> Block directory names (e.g. ez-accordion).
     */
    private static function getBlockSlugs(): array
    {
        $dir = self::BLOCKS_DIR;
        if (!is_dir($dir)) {
            return [];
        }
        $slugs = [];
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . '/' . $entry;
            if (is_dir($path) && is_file($path . '/block.json')) {
                $slugs[] = $entry;
            }
        }
        sort($slugs);
        return $slugs;
    }
}
