<?php
namespace ZhUpClientUpdater;
use ZhaketUpdater\Settings\Setting;

defined( 'ABSPATH') || exit ("no access");

if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'],Setting::getNonceKey())) wp_die('no access');
global $wpdb;

ob_start();
phpinfo();
$php_info=ob_get_clean();
if (strlen($php_info)<10){
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ob_start();
    phpinfo();
    echo '<br>php version:';
    echo phpversion();
    $php_info=ob_get_clean();
}

ob_start();
print_r($wpdb->dbh);
$database=ob_get_clean();

ob_start();

$settings=[
    'wordpress'=> get_bloginfo('version'),
    'database'=>$database,
    'zhaket_updater_version'=>get_plugin_data(WP_PLUGIN_DIR . '/zhaket-updater/zhaket-updater.php')['Version']??'',
    'all_plugins'=>get_plugins(),
    'all_themes'=>wp_get_themes(),
    'zhupclient_ver'=>get_option('zhupclient_ver'),
    'zhupclient_plugins'=>get_option('zhupclient_plugins'),
    'zhupclient_themes'=>get_option('zhupclient_themes'),
    'zhupclient_validate_domain'=>get_option('zhupclient_validate_domain'),
    'zhupclient_validate_result'=>get_option('zhupclient_validate_result'),
    'ZhUpClient_options'=>get_option('ZhUpClient_options'),
];

$table=$wpdb->prefix.'zhk_updater_logs';
$query="select * from {$table} ORDER BY id DESC LIMIT 200";
$result=$wpdb->get_results($query,ARRAY_A);
if ($wpdb->last_error)
    $result=$wpdb->last_error;
$ext=md5(microtime());
if (!file_exists(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report')) {
    mkdir( ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report',755,true );
}
file_put_contents(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report/php_info_'.$ext,$php_info);
file_put_contents(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report/all_settings_'.$ext,json_encode($settings));
file_put_contents(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report/plugin_logs_'.$ext,json_encode($result));
ob_get_clean();
$zip_result=Zhup_backup::caj_create_backup(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report',1,'generate-report','report');
unlink(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report/php_info_'.$ext);
unlink(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report/all_settings_'.$ext);
unlink(ZHAKET_UPDATER_PLUGIN_DIR.'updater/_report/plugin_logs_'.$ext);

header('Location: '.$zip_result);
exit;