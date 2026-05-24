<?php

namespace ZhaketUpdater;

use ZhaketUpdater\AdminMenu\AdminMenu;
use ZhaketUpdater\Ai\Ai;
use ZhaketUpdater\Offer\Offer;
use ZhaketUpdater\Pro\Pro;
use ZhaketUpdater\Widget\Widget;
use ZhaketUpdater\Checkup\Checkup;
use ZhaketUpdater\CustomizeAdmin\CustomizeAdmin;
use ZhaketUpdater\CustomLogin\CustomLogin;
use ZhaketUpdater\Debug\Debug;
use ZhaketUpdater\HelpersClass\PreventCron;
use ZhaketUpdater\HelpersClass\SingleInstance;
use ZhaketUpdater\Hub\Hub;
use ZhaketUpdater\MenuAlert\MenuAlert;
use ZhaketUpdater\PersianDate\PersianDate;
use ZhaketUpdater\Recaptcha\Recaptcha;
use ZhaketUpdater\SendData\SendData;
use ZhaketUpdater\Settings\Setting;
use ZhaketUpdater\Updater\Updater;
use ZhaketUpdater\UserPrevent\UserPrevent;

defined( 'ABSPATH' ) || exit;

/**
 * Main Class
 *
 * This class handles the core functionality of the plugin.
 */
class MainApp
{
    use SingleInstance;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->enqueueStyle();
        $this->init();
        $this->includes();
        $this->ioncubeIncludes();

    }

    protected function enqueueStyle(){
        add_action( 'admin_enqueue_scripts', array($this, 'enqueueGlobalAdminAssets') );
    }

    /**
     * Enqueue Admin CSS and JS.
     */
    public function enqueueGlobalAdminAssets()
    {
         // Enqueue Admin JS
         wp_enqueue_script(
             'zhaket-updater-admin',
             ZHAKET_UPDATER_PLUGIN_ASSET_URL.'/admin/script.min.js',
             array('jquery'),
             \ZhaketUpdater::VERSION,
             true
         );
         wp_localize_script(
             'zhaket-updater-admin',
             'zhaketUpdater',
             apply_filters( 'zhaket-updater-admin-localize', array(
                 'ajax_url' => admin_url( 'admin-ajax.php' ),
                 'nonce' => wp_create_nonce( 'zhaket-updater-admin-nonce' ),
                 'site_url' => get_site_url(),
                 'assets_uri' => ZHAKET_UPDATER_PLUGIN_ASSET_URL,
                 'ai_tab' => self_admin_url( 'admin.php?page=zhaket-updater&tab=ai-tools' ),
                 'translate'=>[
                     'makeContentWithAi'=>esc_html__( 'Make content with AI', 'zhaket-updater' ),
                     'cardTitle'=>esc_html__( 'Zhaket, the most reputable marketplace specializing in WordPress themes and plugins.', 'zhaket-updater' ),
                     'cartTitleSearch'=>esc_html__( 'Search "%s" in ', 'zhaket-updater' ),
                     'cartDescription'=>esc_html__('Purchase premium and professional WordPress plugins with dedicated support tailored to your needs from Zhaket.','zhaket-updater'),
                     'by'=>esc_html__( 'by', 'zhaket-updater' ),
                     'zhaket'=>esc_html__( 'Zhaket', 'zhaket-updater' ),
                     'viewInZhaket'=>esc_html__( 'View in Zhaket', 'zhaket-updater' ),
                     'PremiumPlugins'=>esc_html__( 'Premium WordPress Plugins', 'zhaket-updater' ),
                     'SupportUpdates'=>esc_html__( 'Continuous Support and Updates', 'zhaket-updater' ),
                 ]
             ) )
         );
    }

    private function includes()
    {
        AdminMenu::getInstance();
        Setting::getInstance();
    }

    private function ioncubeIncludes()
    {
        if (!DependencyChecker::getInstance()->canRunApplication()) return;

        MenuAlert::getInstance();
        Offer::getInstance();
        Pro::getInstance();
        CustomizeAdmin::getInstance();
        Hub::getInstance();
        PersianDate::getInstance();
        Checkup::getInstance();
        Ai::getInstance();
        CustomLogin::getInstance();
        Recaptcha::getInstance();
        UserPrevent::getInstance();
        Debug::getInstance();
        Widget::getInstance();
        Updater::getInstance();//this must be latest item in this list
    }

    public function init()
    {
        add_action('init',[$this,'checkUpdateVersion']);
    }

    public function checkUpdateVersion()
    {
        $current_version=get_option('zhaket_updater_version',null);
        if (empty($current_version) || version_compare($current_version, \ZhaketUpdater::VERSION, '<')) {
            $this->registerActivation();
        }
    }

    public function registerActivation()
    {
        update_option('zhaket_updater_version',\ZhaketUpdater::VERSION);
        Hub::getInstance()->activationHook();
        Updater::activationHook();
    }

    public function registerDeactivate()
    {
        Updater::deactivateHook();
    }

    public function checkDbVersion()
    {
        Updater::checkDbVersion();
    }

    /**
     * Prevent cloning of the instance.
     */
    private function __clone(){}

    /**
     * Prevent unserializing of the instance.
     */
    public function __wakeup(){}
}

