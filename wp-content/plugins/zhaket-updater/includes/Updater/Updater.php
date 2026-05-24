<?php
namespace ZhaketUpdater\Updater;


use ZhaketUpdater\AdminMenu\AdminMenu;
use ZhaketUpdater\DependencyChecker;
use ZhaketUpdater\HelpersClass\SingleInstance;
use ZhaketUpdater\Settings\Setting;
use ZhUpClientUpdater\Zhup_Admin_Notice;
use ZhUpClientUpdater\Zhup_backup;
use ZhUpClientUpdater\ZhUp_installer;
use ZhUpClientUpdater\Zhup_plugin_row;
use ZhUpClientUpdater\Zhaket_options;
use ZhUpClientUpdater\ZhUpClient;

final class Updater
{
    use SingleInstance;

    public function __construct()
    {
        $this->hook();
        if (is_admin() || (defined('DOING_CRON') && DOING_CRON)){
            //fixme this must test for updater reaction in any need location
            //todo detect needed ajax for prevent load on any ajax
            if (DependencyChecker::getInstance()->canRunApplication() && class_exists('ZhUpClientUpdater\ZhUpClient')){
                ZhUpClient::instance();
                Zhup_backup::get_instance();
                Zhup_plugin_row::getInstance();
                ZhUp_installer::getInstance();
                Zhup_Admin_Notice::getInstance();
            }
        }
    }

    public function hook()
    {
        add_action('activated_plugin', [$this, 'plugin_activated']);
        add_filter( 'cron_schedules',array($this,'everySixCronSchedule' ));
    }



    public function everySixCronSchedule( $schedules ) {
        $schedules['every_six_hours'] = array(
            'interval' => 21600, // Every 6 hours
            'display'  => esc_html__('Every 6 hours','zhaket-updater'),
        );
        return $schedules;
    }

    public function plugin_activated($plugin)
    {
        if ($plugin === \ZhaketUpdater::PLUGIN_SLUG) {
            exit(wp_redirect(AdminMenu::dashboardTabUrl('manual')));
        }
    }

    public static function activationHook()
    {
        update_option('zhupclient_ver', 1);
        Setting::getInstance()->initialStartupOption();
        self::install_db();
        self::add_ai_db();

        delete_site_transient('update_plugins');
        delete_site_transient('update_themes');
        delete_site_transient('zhaket_send_data');


        if (!wp_next_scheduled('ZhUpClient_update_checker')) {
            wp_schedule_event(time()+5, 'twicedaily', 'ZhUpClient_update_checker');
        }
        if (!wp_next_scheduled('ZhUpClient_update_agent')) {
            wp_schedule_event(time()+21600, 'every_six_hours', 'ZhUpClient_update_agent');
        }
    }

    public static function deactivateHook(){
        wp_clear_scheduled_hook('ZhUpClient_update_checker');
        wp_clear_scheduled_hook('ZhUpClient_update_agent');
        wp_clear_scheduled_hook('ZhUpClient_send_site_data');

        delete_site_transient('update_plugins');
        delete_site_transient('update_themes');
    }

    public static function checkDbVersion(){
        $db_ver=get_option('zhaket_updater_db_version',null);
        if ((int)$db_ver < 1){
            self::add_ai_db();
            update_option('zhaket_updater_db_version',1,true);
        }
    }

    public static function install_db()
    {
        global $wpdb;
        $zhk_updater_logs = $wpdb->prefix . 'zhk_updater_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
                CREATE TABLE IF NOT EXISTS $zhk_updater_logs (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				type varchar(100),
				item varchar(300),
				ver varchar(50),
				value MEDIUMTEXT,
				parent bigint(20) UNSIGNED,
				level TINYINT UNSIGNED DEFAULT 0,
				date TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
				status boolean NOT NULL DEFAULT false,
				PRIMARY KEY (id)
			) $charset_collate;
	";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    public static function add_ai_db()
    {
        global $wpdb;
        $zhk_ai = $wpdb->prefix . 'zhk_ai';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
                CREATE TABLE IF NOT EXISTS $zhk_ai (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				type varchar(100),
				body MEDIUMTEXT,
				date TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id)
			) $charset_collate;
	";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}