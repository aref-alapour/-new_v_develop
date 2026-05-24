<?php

declare(strict_types=1);

arch('repositories do not reference wpdb')
    ->expect('EscapeZoom\Core\Modules\*\Repositories')
    ->not->toUse('wpdb')
    ->group('arch');

arch('actions do not use mysqli')
    ->expect('EscapeZoom\Core\Modules\*\Actions')
    ->not->toUse('mysqli')
    ->group('arch');

arch('ProductRanking services do not use wpdb or mysqli')
    ->expect('EscapeZoom\Core\Modules\ProductRanking\Services')
    ->not->toUse(['wpdb', 'mysqli'])
    ->group('arch');

arch('ProductRanking repositories do not use wpdb')
    ->expect('EscapeZoom\Core\Modules\ProductRanking\Repositories')
    ->not->toUse('wpdb')
    ->group('arch');
