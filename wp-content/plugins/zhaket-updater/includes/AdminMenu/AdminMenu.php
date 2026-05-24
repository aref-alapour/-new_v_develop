<?php
namespace ZhaketUpdater\AdminMenu;

use ZhaketUpdater\DependencyChecker;
use ZhaketUpdater\HelpersClass\PreventCron;
use ZhaketUpdater\HelpersClass\SingleInstance;
use ZhaketUpdater\Hub\Hub;
use ZhaketUpdater\Settings\Setting;

final class AdminMenu
{
    use SingleInstance,PreventCron;

    protected static $_settings_cache = null;


    /**
     * plugin slug.
     *
     * @var string
     */
    const ADMIN_MENU_SLUG = 'zhaket-updater';


    
    public function __construct()
    {
        $this->defineHooks();
        $this->removeAllNotices();
    }

    public function defineHooks()
    {
        add_action('admin_menu', [$this,'adminMenuItem'], 10);
        add_action( 'admin_enqueue_scripts', array($this, 'enqueueSettingAdminAssets') );
    }

    public static function isSettingPage(){
       return !empty($_GET['page']) && $_GET['page'] === self::ADMIN_MENU_SLUG;
    }

    protected function removeAllNotices()
    {
        if (!self::isSettingPage()) {
            return;
        }

        add_action('admin_notices',function (){
            remove_all_actions('admin_notices');
        },-PHP_INT_MAX);

        add_action('all_admin_notices',function (){
            remove_all_actions('all_admin_notices');
        },-PHP_INT_MAX);

        add_action('user_admin_notices',function (){
            remove_all_actions('user_admin_notices');
        },-PHP_INT_MAX);
    }

    /**
     * Enqueue Admin CSS and JS.
     */
    public function enqueueSettingAdminAssets()
    {
        global $pagenow;

        if ( in_array( $pagenow, ['plugins.php','plugin-install.php'] )) {
            wp_enqueue_style('zhaket-updater',ZHAKET_UPDATER_PLUGIN_ASSET_URL.'/admin/admin.css',[],\ZhaketUpdater::VERSION);
        }

        wp_enqueue_style(self::ADMIN_MENU_SLUG.'-setting', ZHAKET_UPDATER_PLUGIN_ASSET_URL . '/admin/app/app.css',[],\ZhaketUpdater::VERSION);

        if (!self::isSettingPage()) {
            return;
        }
        
        wp_enqueue_media();

        wp_localize_script(
            'wp-backbone',
            'zhaket_updater',
            [
                'version' => \ZhaketUpdater::VERSION,
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'email' => get_bloginfo( 'admin_email' ),
                'lang' => get_user_locale() == 'fa_IR' ? 'fa' : 'en',
                'giftLink' => self_admin_url( 'admin.php?page=zhaket-updater&tab=offer' ),
                'restAddress' => get_rest_url( null, '/zhk/v1/updater/' ),
                'createReportUrl' => wp_nonce_url(
                    self_admin_url( 'admin.php?page=zhaket-updater&tab=errors&generate_report=true' ),
                    Setting::getNonceKey(),'nonce'
                ),
                'nonce' => wp_create_nonce( Setting::getNonceKey() ),
                'development'=>false,
                'hub_agreement'=>Hub::getInstance()->getOption('hub_agree'),
            ]
        );
    }

    public function adminMenuItem()
    {
        $this->redirectToNewMenu();
        $this->mainAdminMenu();
        $this->ioncubeManualMenu();
        $this->giftSubMenu();
        $this->buySubmenu();
    }

    public function dashboardIndex()
    {
        $this->activeSendData();
        require_once ZHAKET_UPDATER_PLUGIN_DIR . 'template/admin/dashboard.php';
    }

    public function activeSendData()
    {
        if (!empty($_GET['data_sender'])){
            $old_option = get_option('ZhUpClient_options',[]);
            $data_option['send-data'] = '1';
            update_option('ZhUpClient_options',$data_option);
            if (!wp_next_scheduled('ZhUpClient_send_site_data')) {
                wp_schedule_event(time(), 'daily', 'ZhUpClient_send_site_data');
            }
        }
    }

    private function mainAdminMenu()
    {
        add_menu_page(
            __( 'Zhaket smart updater', 'zhaket-updater' ),
            __( 'Zhaket updater', 'zhaket-updater' ),
            'manage_options',
            self::ADMIN_MENU_SLUG,
            [$this, 'dashboardIndex'],
            ZHAKET_UPDATER_PLUGIN_URL.'assets/admin/img/icon.png',
            81
        );
    }

    function redirectToNewMenu() {
        global $pagenow;

        if (
            $pagenow == 'admin.php' &&
            isset($_GET['page']) &&
            in_array($_GET['page'] ,['ZhUpClient_options','ZhUpClient_options_gifts','ZhUpClient_options_buy'])) {
            wp_redirect(admin_url('admin.php?page='.self::ADMIN_MENU_SLUG));
            exit;
        }
    }

    private function giftSubMenu()
    {
        add_submenu_page(self::ADMIN_MENU_SLUG,__( 'Gifts and discounts', 'zhaket-updater' ), __( 'Gifts and discounts', 'zhaket-updater' ),'manage_options',self::ADMIN_MENU_SLUG.'&tab=offer' , "__return_empty_string");

    }

    private function ioncubeManualMenu()
    {
        if (!DependencyChecker::getInstance()->ioncubeHasError())
            return;

        add_submenu_page( self::ADMIN_MENU_SLUG, esc_html__( 'ionCube required', 'zhaket-updater' ), esc_html__( 'ionCube required', 'zhaket-updater' ), 'manage_options', self::ADMIN_MENU_SLUG.'-ioncube','__return_null');
    }

    private function buySubmenu()
    {
        add_submenu_page(self::ADMIN_MENU_SLUG,esc_html__('zhaket buy','zhaket-updater'),esc_html__('zhaket buy','zhaket-updater') , 'manage_options',self::ADMIN_MENU_SLUG.'&tab=buyedProducts','__return_null');
    }

    public function ioncubeManual()
    {

    }


    public static function dashboardTabUrl($tab=null)
    {
        $dashboardUrl= sprintf("admin.php?page=%s",self::ADMIN_MENU_SLUG);
        if (!empty($tab)){
            $dashboardUrl=add_query_arg(['tab'=>$tab],$dashboardUrl);
        }
        return self_admin_url($dashboardUrl);
    }
}