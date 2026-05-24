<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\ProductRanking\ProductPenaltySchema;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;

test('penalty repository legacy fallback respects facet', function () {
    if (! extension_loaded('pdo_mysql')) {
        test()->markTestSkipped('pdo_mysql not loaded — run tests inside Docker PHP');
    }

    if (ProductPenaltySchema::tablesVerified()) {
        test()->markTestSkipped('DB penalties table present — legacy fallback not exercised');
    }

    $ids = RankingConfig::penaltyProductIds();
    if ($ids === []) {
        test()->markTestSkipped('no legacy penalty ids');
    }

    $id = (int) $ids[0];
    if (! RankingConfig::isPenaltyActive()) {
        expect(ProductPenaltyRepository::isPenalized($id, 'hottest'))->toBeFalse();
    } else {
        expect(ProductPenaltyRepository::isPenalized($id, 'hottest'))->toBeTrue();
    }
});

test('penalty active_from window gates isPenalized', function () {
    if (! extension_loaded('pdo_mysql')) {
        test()->markTestSkipped('pdo_mysql not loaded — run tests inside Docker PHP');
    }

    if (! ProductPenaltySchema::tablesVerified()) {
        test()->markTestSkipped('penalties table not applied');
    }

    $productId = 999_999_991;
    ProductPenaltyRepository::deleteById(
        (int) (\EscapeZoom\Core\Modules\ProductRanking\Models\ProductPenalty::query()
            ->where('product_id', $productId)
            ->value('id') ?? 0)
    );

    $future = date('Y-m-d H:i:s', time() + 86400);
    $past = date('Y-m-d H:i:s', time() - 86400);

    $row = ProductPenaltyRepository::saveFromAdmin([
        'product_id' => $productId,
        'exclude_hottest' => true,
        'exclude_popular' => false,
        'exclude_topsale' => false,
        'is_enabled' => true,
        'active_from' => $future,
        'active_until' => null,
    ]);

    expect(ProductPenaltyRepository::isPenalized($productId, 'hottest'))->toBeFalse();

    ProductPenaltyRepository::saveFromAdmin([
        'product_id' => $productId,
        'exclude_hottest' => true,
        'active_from' => $past,
        'active_until' => null,
    ], (int) $row->id);

    expect(ProductPenaltyRepository::isPenalized($productId, 'hottest'))->toBeTrue();

    ProductPenaltyRepository::deleteById((int) $row->id);
});
