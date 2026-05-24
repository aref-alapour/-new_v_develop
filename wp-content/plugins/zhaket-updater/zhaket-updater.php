<?php
/**
 * Zhaket Smart Updater
 *
 * Plugin Name:  Zhaket Smart Updater
 * Description:  Zhaket Smart Updater, an unique tool for instant update product purchases from zhaket.com. Zhaket is difference...
 * Version:      4.2.1
 * Plugin URI:   https://www.zhaket.com/web/zhaket-smart-updater
 * Author:       zhaket
 * Author URI:   https://zhaket.com
 * Requires at least: 6.0.0
 * Tested up to: 6.7.1
 * Text Domain:  zhaket-updater
 * Domain Path:  /languages/
 * Requires PHP: 7.4
 */


use ZhaketUpdater\DependencyChecker;
use ZhaketUpdater\MainApp;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class ZhaketUpdater {

    /**
     * Plugin version.
     *
     * @var string
     */
    const VERSION = '4.2.1';

    /**
     * zhaket link.
     *
     * @var string
     */
    const LINK = '<a href="https://zhaket.com">zhaket.com</a>';

    /**
     * plugin slug.
     *
     * @var string
     */
    const PLUGIN_SLUG = 'zhaket-updater/zhaket-updater.php';

    /**
     * Plugin instance.
     *
     * @var ZhaketUpdater
     */
    protected static $instance = null;


    /**
     * Get the singleton instance.
     *
     * @return ZhaketUpdater
     */
    public static function getInstance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->defineConstants();
        $this->loadTextdomain();
        $this->autoloadClasses();
        MainApp::getInstance();
    }

    /**
     * Define plugin constants.
     */
    private function defineConstants() {
        define('ZHAKET_UPDATER_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('ZHAKET_UPDATER_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('ZHAKET_UPDATER_PLUGIN_ASSET_DIR', ZHAKET_UPDATER_PLUGIN_DIR.'assets');
        define('ZHAKET_UPDATER_PLUGIN_ASSET_URL', ZHAKET_UPDATER_PLUGIN_URL.'assets');
    }

    /**
     * Load plugin textdomain for translations.
     */
    public function loadTextdomain() {
        add_action( 'after_setup_theme', function (){
            load_plugin_textdomain( 'zhaket-updater', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        } );
    }

    /**
     * Autoload classes.
     */
    private function autoloadClasses() {
        require_once ZHAKET_UPDATER_PLUGIN_DIR . 'vendor/autoload.php';
    }


    public static function registerActivation()
    {
        MainApp::getInstance()->registerActivation();
    }

    public static function registerDeactivate()
    {
        MainApp::getInstance()->registerDeactivate();
    }

    public static function checkDbVersion()
    {
        MainApp::getInstance()->checkDbVersion();
    }

}

ZhaketUpdater::getInstance();

register_activation_hook( __FILE__, array( 'ZhaketUpdater', 'registerActivation' ) );
register_deactivation_hook( __FILE__, array( 'ZhaketUpdater', 'registerDeactivate' ) );
add_action('plugins_loaded', array( 'ZhaketUpdater', 'checkDbVersion' ) );

