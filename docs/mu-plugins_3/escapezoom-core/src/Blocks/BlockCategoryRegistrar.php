<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Blocks;

/**
 * Registers the "EscapeZoom" block category for Gutenberg.
 */
final class BlockCategoryRegistrar
{
    public const CATEGORY_SLUG  = 'escapezoom';
    public const CATEGORY_TITLE = 'EscapeZoom';
    public const CATEGORY_ICON  = 'admin-customizer';

    public static function register(): void
    {
        add_filter('block_categories_all', [self::class, 'addCategory'], 10, 2);
    }

    /**
     * @param array<int, array<string, mixed>> $categories
     * @param \WP_Block_Editor_Context $context
     * @return array<int, array<string, mixed>>
     */
    public static function addCategory(array $categories, $context): array
    {
        $categories[] = [
            'slug'  => self::CATEGORY_SLUG,
            'title' => self::CATEGORY_TITLE,
            'icon'  => self::CATEGORY_ICON,
        ];
        return $categories;
    }
}
