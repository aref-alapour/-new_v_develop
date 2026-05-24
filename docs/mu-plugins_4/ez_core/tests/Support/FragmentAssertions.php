<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Tests\Support;

/**
 * Structural parity checks for brands.fragment HTML.
 */
final class FragmentAssertions
{
    /**
     * @return array{swap_root:bool, relative:bool, grid:bool, pagination:bool, persian_nav:bool}
     */
    public static function inspect(string $html): array
    {
        $hasPagination = (str_contains($html, '/ajax?action=brands.fragment&page=')
                || str_contains($html, '/ajax?action=brands.fragment&amp;page='))
            && str_contains($html, 'hx-indicator="#ez-brands-htmx-skeleton"');

        return [
            'swap_root' => str_contains($html, 'id="brands-directory-swap"'),
            'relative' => str_contains($html, 'class="relative"') || str_contains($html, "class='relative'"),
            'grid' => str_contains($html, 'grid grid-cols-2') && str_contains($html, '2xl:grid-cols-8'),
            'pagination' => $hasPagination,
            'persian_nav' => str_contains($html, 'aria-label="صفحه‌بندی برندها"'),
        ];
    }
}
