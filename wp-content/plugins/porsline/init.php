<?php
/*
Plugin Name:Porsline
Plugin URI: https://porsline.ir/
Description: افزونه اختصاصی پرس لاین برای وردپرس
Version: 2.9
Author: Porsline
Author URI: https://porsline.ir/
Text Domain: porsline
Domain Path: /langs
*/
define('PORSLINE_DOMAIN', 'porsline');
define("PORSLINE_API_IR", "https://survey.porsline.ir");
define("PORSLINE_API_GB", "https://survey.porsline.com");
require_once("funcs.php");
require_once("admin_menu.php");
require_once("ajax.php");
require_once("shortcode.php");
add_action('plugins_loaded', function () {
    load_plugin_textdomain('porsline', false, basename(dirname(__FILE__)) . '/langs');
});
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('prsl_admin_css', plugin_dir_url(__FILE__) . '/assets/admin_style.css', false, '1.2.0');
    wp_enqueue_script('prsl_admin_Sc', plugins_url('/assets/admin_script.js', __FILE__), array('wp-color-picker'), false, true);
});
add_action("admin_notices", function () {
    global $pagenow, $typenow;
    if (!get_option("prsline_token", false) && $pagenow != "admin.php") {
        echo '<div class="notice notice-error"><p>' . __('Plugin Activated , For Enter Your Token Please Click Here', PORSLINE_DOMAIN) . ' - <a href="' . admin_url("admin.php?page=porsline") . '">' . __('Click To Start', PORSLINE_DOMAIN) . '</a></p></div>';
    }
});
function add_nonce_to_admin_head()
{
?>
    <script type="text/javascript">
        const prs_adm_nonce = '<?= wp_create_nonce('prs_load_nonce'); ?>';
    </script>
<?php
}
add_action('admin_head', 'add_nonce_to_admin_head');
?>