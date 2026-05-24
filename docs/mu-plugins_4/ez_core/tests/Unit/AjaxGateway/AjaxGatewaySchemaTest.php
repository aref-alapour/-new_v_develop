<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema;
use EscapeZoom\Core\Tests\Support\SchemaAssertions;

test('ajax gateway ddl tables exist', function () {
    $this->skipUnlessDb();

    expect(SchemaAssertions::hasTable(AjaxGatewaySchema::noncesTable()))->toBeTrue();
    expect(SchemaAssertions::hasTable(AjaxGatewaySchema::rateTable()))->toBeTrue();
    expect(SchemaAssertions::ajaxGatewayTablesExist())->toBeTrue();
})->group('gateway');
