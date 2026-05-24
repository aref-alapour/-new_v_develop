<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define the necessary constants for later use.
define( 'NAGATHEME_UREP_FILE', __FILE__ );

define( 'NAGATHEME_UREP_PATH', plugin_dir_path( __FILE__ ) );

define( 'NAGATHEME_UREP_URL', plugin_dir_url( __FILE__ ) );

require_once NAGATHEME_UREP_PATH . 'includes/requirements.php';

require_once NAGATHEME_UREP_PATH . 'vendor/autoload.php';

use NagaTheme\UserRoleEditorPro\Includes\Requirements;

if ( Requirements::run_compatibility_check() ) {
	// Load NagaTheme Files.
	require_once( NAGATHEME_UREP_PATH . 'naga-urep-ic.php' );
} else {
	// Requirements are not met.
	// NagaTheme functionalities are required in case for User Role Editor Pro to work properly.
	add_action(
		'admin_menu',
		function () {
			remove_submenu_page( 'users.php', 'users-user-role-editor-pro.php' );
			remove_submenu_page( 'options-general.php', 'settings-user-role-editor-pro.php' );
		},
		999999
	);
}
