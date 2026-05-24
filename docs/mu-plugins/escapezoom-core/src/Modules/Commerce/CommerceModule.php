<?php

namespace EscapeZoom\Core\Modules\Commerce;

use EscapeZoom\Core\Modules\Cancellation\API\CancellationRestController;
use EscapeZoom\Core\Modules\Collections\API\CollectionsRestController;
use EscapeZoom\Core\Modules\Comments\API\CommentAuditRestController;
use EscapeZoom\Core\Modules\Commerce\API\CheckoutLedgerRestController;
use EscapeZoom\Core\Modules\Commerce\Cli\MigrateOrdersCommand;
use EscapeZoom\Core\Modules\Commerce\Cli\ValidateParityCommand;
use EscapeZoom\Core\Modules\Finance\API\WalletRestController;
use EscapeZoom\Core\Modules\Marketing\API\MarketingRestController;
use EscapeZoom\Core\Modules\Search\API\ProductSnapshotRestController;
use EscapeZoom\Core\Modules\Search\API\SearchRestController;
use EscapeZoom\Core\Modules\WpCore\API\WpCoreRestController;

class CommerceModule
{
    public static function register(): void
    {
        if (!defined('EZ_LEDGER_WRITE_ENABLED')) {
            define('EZ_LEDGER_WRITE_ENABLED', false);
        }
        if (!defined('EZ_LEDGER_BACKFILL_MODE')) {
            define('EZ_LEDGER_BACKFILL_MODE', false);
        }
        if (!defined('EZ_LEDGER_SHADOW_READ_ENABLED')) {
            define('EZ_LEDGER_SHADOW_READ_ENABLED', false);
        }
        if (!defined('EZ_LEDGER_LEGACY_FALLBACK_ENABLED')) {
            define('EZ_LEDGER_LEGACY_FALLBACK_ENABLED', true);
        }

        add_action('cli_init', static function (): void {
            if (!class_exists('\WP_CLI')) {
                return;
            }
            \WP_CLI::add_command('ez migrate-orders', MigrateOrdersCommand::class);
            \WP_CLI::add_command('ez validate-parity', ValidateParityCommand::class);
        });

        add_action('rest_api_init', static function (): void {
            if (!defined('EZ_LEDGER_READ_ENABLED')) {
                define('EZ_LEDGER_READ_ENABLED', true);
            }

            if (!EZ_LEDGER_READ_ENABLED) {
                return;
            }

            CheckoutLedgerRestController::create()->registerRoutes();
            CancellationRestController::create()->registerRoutes();
            WalletRestController::create()->registerRoutes();
            CollectionsRestController::create()->registerRoutes();
            CommentAuditRestController::create()->registerRoutes();
            MarketingRestController::create()->registerRoutes();
            SearchRestController::create()->registerRoutes();
            ProductSnapshotRestController::create()->registerRoutes();
            WpCoreRestController::create()->registerRoutes();
        });
    }
}
