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

// Define global variable for crud database entries.
global $wldb;
$wldb = new EZ_Transaction_CRUD();

if ( ! function_exists( 'ez_get_wallet_balance' ) ) {
	function ez_get_wallet_balance( $user_id = 0 ) {
		global $wldb;

		if ( ! is_object( $wldb ) || ! method_exists( $wldb, 'get_balance' ) ) {
			return 0.0;
		}

		$user_id = $user_id ? (int) $user_id : (int) get_current_user_id();

		return (float) $wldb->get_balance( $user_id );
	}
}