<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Blocks;

/**
 * Resolves render_callback for each EZ block.
 * Blocks that use theme PHP (ez_render_*) are rendered via template part; others output the Stencil tag.
 */
final class BlockRenderResolver
{
    /** Blocks that use a theme template part (theme owns markup and may call ez_render_*). */
    private const THEME_TEMPLATE_BLOCKS = [
        'ez-product-card',
        'ez-blog-card',
        'ez-comments',
    ];

    /**
     * Returns the render_callback for the given block slug.
     *
     * @param string $slug Block dir name (e.g. ez-accordion).
     * @return callable|null (string $attributes, string $content, \WP_Block $block) => string
     */
    public static function callbackFor(string $slug): ?callable
    {
        if (in_array($slug, self::THEME_TEMPLATE_BLOCKS, true)) {
            return static function (array $attributes, string $content, \WP_Block $block): string {
                $shortName = str_replace('escapezoom/', '', $block->block_type->name ?? '');
                ob_start();
                set_query_var('ez_block_attributes', $attributes);
                set_query_var('ez_block_content', $content);
                set_query_var('ez_block_instance', $block);
                get_template_part('template-parts/blocks/' . $shortName, null, [
                    'attributes' => $attributes,
                    'content'    => $content,
                    'block'      => $block,
                ]);
                return (string) ob_get_clean();
            };
        }

        return static function (array $attributes, string $content, \WP_Block $block): string {
            return self::renderStencilTag($block->block_type->name, $attributes, $content);
        };
    }

    /**
     * Outputs a single Stencil custom element. Block name is like "escapezoom/ez-button".
     */
    private static function renderStencilTag(string $blockName, array $attributes, string $content): string
    {
        $tag = self::blockNameToTag($blockName);
        if ($tag === '') {
            return $content;
        }
        $attrs = self::attributesToHtml($attributes, $tag);
        return '<' . $tag . $attrs . '>' . $content . '</' . $tag . '>';
    }

    private static function blockNameToTag(string $blockName): string
    {
        $prefix = 'escapezoom/';
        if (strpos($blockName, $prefix) !== 0) {
            return '';
        }
        return substr($blockName, strlen($prefix));
    }

    private static function attributesToHtml(array $attributes, string $tag): string
    {
        $allowed = self::allowedAttributesForTag($tag);
        $out     = [];
        foreach ($attributes as $key => $value) {
            if (!isset($allowed[$key]) || $value === '' || $value === null) {
                continue;
            }
            if (is_bool($value)) {
                if ($value) {
                    $out[] = esc_attr($key);
                }
                continue;
            }
            $out[] = esc_attr($key) . '="' . esc_attr((string) $value) . '"';
        }
        return $out ? ' ' . implode(' ', $out) : '';
    }

    /** Attributes allowed to be output on each tag (whitelist). */
    private static function allowedAttributesForTag(string $tag): array
    {
        $common = ['variant', 'size', 'type', 'href', 'disabled', 'loading', 'items', 'label', 'placeholder', 'name', 'value', 'activeTab', 'apiEndpoint', 'productId', 'status', 'min', 'max', 'title', 'caption'];
        $byTag = [
            'ez-breadcrumb' => ['items' => true],
            'ez-button'     => ['variant' => true, 'size' => true, 'type' => true, 'disabled' => true, 'loading' => true, 'href' => true, 'wFull' => true],
            'ez-badge'      => ['variant' => true, 'sysColor' => true, 'sysBg' => true],
            'ez-text-input' => ['label' => true, 'placeholder' => true, 'name' => true, 'value' => true, 'type' => true],
            'ez-select'     => ['label' => true, 'placeholder' => true, 'options' => true],
            'ez-dropdown'   => ['label' => true, 'placeholder' => true],
            'ez-tabs'       => ['activeTab' => true],
            'ez-pagination'=> ['current' => true, 'total' => true, 'baseUrl' => true],
            'ez-range-datepicker' => ['min' => true, 'max' => true],
            'ez-autocomplete' => ['apiEndpoint' => true, 'placeholder' => true],
            'ez-sans-list'  => ['apiEndpoint' => true],
            'ez-banner-slider' => [],
            'ez-product-card' => ['productId' => true, 'status' => true, 'href' => true, 'isSlide' => true],
            'ez-blog-card'    => ['href' => true],
            'ez-collection-card' => ['href' => true, 'title' => true],
            'ez-brand-card'  => ['href' => true],
        ];
        $allowed = $byTag[$tag] ?? [];
        foreach ($common as $c) {
            $allowed[$c] = true;
        }
        return $allowed;
    }
}
