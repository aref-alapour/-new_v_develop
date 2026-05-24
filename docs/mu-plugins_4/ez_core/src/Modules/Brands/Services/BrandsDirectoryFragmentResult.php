<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\Services;

final class BrandsDirectoryFragmentResult
{
    public function __construct(
        public readonly string $html,
        public readonly int $status = 200,
        public readonly ?string $hxPushUrl = null,
        public readonly bool $withVary = false,
    ) {
    }
}
