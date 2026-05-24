<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once 'includes/ez-wallet-dependency.php';
if ( (new EZ_Wallet_Dependency)->check() ) {
    return;
} // The plugin won't work if the Woocommerce is not installed or enabled

define('PLUGIN_NAME', 'ez-wallet');
define('EZ_TRANSACTION_TABLE', 'wallet_transactions');

function ez_wallet_plugin_run() {
    require_once 'includes/ez-wallet-main.php';
    return new EZ_Wallet(__FILE__);
}

// Get Running.
ez_wallet_plugin_run();

// Define global variable for crud database entries
$wldb = new EZ_Transaction_CRUD();