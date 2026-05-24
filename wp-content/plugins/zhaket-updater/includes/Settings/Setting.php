<?php


namespace ZhaketUpdater\Settings;


use ZhaketUpdater\AdminMenu\AdminMenu;
use ZhaketUpdater\Ai\Ai;
use ZhaketUpdater\Checkup\Checkup;
use ZhaketUpdater\CustomizeAdmin\CustomizeAdmin;
use ZhaketUpdater\CustomLogin\CustomLogin;
use ZhaketUpdater\DependencyChecker;
use ZhaketUpdater\HelpersClass\SingleInstance;
use ZhaketUpdater\Hub\Hub;
use ZhaketUpdater\PersianDate\PersianDate;
use ZhaketUpdater\Recaptcha\Recaptcha;
use ZhaketUpdater\SendData\SendData;
use ZhaketUpdater\UserPrevent\UserPrevent;
use ZhUpClientUpdater\Zhup_backup;
use ZhUpClientUpdater\ZhUp_installer;
use ZhUpClientUpdater\ZhUpClient;
use ZhUpClientUpdater\zhUpClient_check;

final class Setting
{
    use SingleInstance;
    protected $moduleWithSettings;

    public function __construct()
    {
        $this->moduleWithSetting();
        $this->define_hooks();
    }

    protected function moduleWithSetting(){
        if (!DependencyChecker::getInstance()->canRunApplication()){
            $this->moduleWithSettings=[];
            return;
        }
        $this->moduleWithSettings=[
            CustomizeAdmin::getInstance(),
            Hub::getInstance(),
            PersianDate::getInstance(),
            Checkup::getInstance(),
            CustomLogin::getInstance(),
            recaptcha::getInstance(),
            UserPrevent::getInstance(),
            Ai::getInstance()
        ];
    }

    public function define_hooks()
    {
        add_action('wp_ajax_zhaket_updater_get_settings',  [$this,'getOption']);
        add_action('wp_ajax_zhaket_updater_save_settings',  [$this,'saveOption']);
        add_action('wp_ajax_zhaket_show_buyed_product',  [$this,'load_zhaket_plugins_list']);
        add_action('wp_ajax_zhaket_updater_product_details',  [$this,'load_zhaket_plugins_details']);
        add_action('wp_ajax_zhaket-updater-downlaod-product',  [$this,'generate_product_link']);

        if (AdminMenu::isSettingPage()) {
            add_action( 'admin_init', [ $this, 'generateReport' ], 1 );
            add_action( 'admin_init', [ $this, 'deleteBackup' ], 1 );
        }
    }

    public function deleteBackup()
    {
        if (!DependencyChecker::getInstance()->canRunApplication() || !class_exists('ZhUpClientUpdater\Zhup_backup')){
            return;
        }
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'backup-delete' && isset( $_GET['id'] ) ) {
            self::securityCheck();
            Zhup_backup::delete_backup( (int) $_GET['id'] );
        }
    }

    public function generateReport() {
        if(!isset( $_GET['generate_report'] ) ) {
            return;
        }
        self::securityCheck();
        if (!DependencyChecker::getInstance()->canRunApplication()){
            return;
        }
        require_once ZHAKET_UPDATER_PLUGIN_DIR.'/updater/generate_report.php';
    }

    public function load_zhaket_plugins_details()
    {
        self::securityCheck();
        if (!DependencyChecker::getInstance()->canRunApplication() || !class_exists('ZhUpClientUpdater\zhUpClient_check')){
            wp_send_json_error(esc_html__('dependency check failed','zhaket-updater'));
        }

        $url=str_replace('##',(int)$_REQUEST['product_id'],zhUpClient_check::define('ZhUpClient_product_info'));
        $api_response = wp_remote_get($url , array('timeout' => 20,'sslverify'=>false));
        if (!is_wp_error($api_response) && wp_remote_retrieve_response_code($api_response) === 200) {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response));
            $api_response_body = $api_response_body ?? 0;
            $response_obj = new \stdClass();
            if (isset($api_response_body->web_link)) foreach ((array)$api_response_body as $key => $item) {
                if ($item instanceof \stdClass)
                    $response_obj->$key = (array)$item;
                else
                    $response_obj->$key = $item;
            }
            if (empty($response_obj->icon)){
                $response_obj->icon = ZHAKET_UPDATER_PLUGIN_ASSET_URL.'/admin/img/zhaket-logo.png';
            }
            $response_obj->last_updated=human_time_diff(($response_obj->last_updated ?? 0),time());
            wp_send_json_success($response_obj);
        }
    }

    public function load_zhaket_plugins_list()
    {
         if (!DependencyChecker::getInstance()->canRunApplication() || !class_exists('ZhUpClientUpdater\ZhUp_installer')){
             wp_send_json_error(esc_html__('dependency check failed','zhaket-updater'));
         }
        self::securityCheck();
        $installer=ZhUp_installer::getInstance();
        $data=$installer->load_zhaket_plugins_list();
        wp_send_json($data);
    }

    public static function getNonceKey()
    {
        return 'zhaket-updater-settings-'.get_current_user_id();
    }

    public static function securityCheck()
    {
        if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], self::getNonceKey()) && current_user_can('manage_options')) {
            return;
        }
        wp_send_json_error(esc_html__('Nonce verification failed. or no permission','zhaket-updater'), 403);
    }

    public function getOption($direct_access=false) {
        if (!$direct_access){
            self::securityCheck();
        }

        $old_option = get_option('ZhUpClient_options',[]);
        $updater_data=Helper::convert_old_option_to_new($old_option);

        $plugins = [];
        $old_setting_plugin_data = array_column(($updater_data['checkUpdate']['spacialPlugins']??[]),'value','slug');

        $parameters = $this->getInstalledItemList();
        if (is_array($parameters['plugins'][0]))
            foreach ($parameters['plugins'][0] as $slug => $plugin) {
                $value=!isset($old_setting_plugin_data[$slug])? true : $old_setting_plugin_data[$slug];
                $plugins[]=[
                    'slug'=>$slug,
                    'name'=>$plugin,
                    'value'=>$value
                ];
            }

        $themes = [];
        $old_setting_theme_data = array_column(( $updater_data['checkUpdate']['spacialThemes']??[]),'value','slug');
        if (is_array($parameters['themes'][0]))
            foreach ($parameters['themes'][0] as $slug => $theme) {
                $value=!isset($old_setting_theme_data[$slug])? true : $old_setting_theme_data[$slug];
                $themes[]=[
                    'slug'=>$slug,
                    'name'=>$theme,
                    'value'=>$value
                ];
            }
        $updater_data['checkUpdate']['spacialPlugins']=$plugins;
        $updater_data['checkUpdate']['spacialThemes']=$themes;
        if (empty($updater_data['backup'])){
            $updater_data['backup']=[];
        }

        if (!$updater_data) {
            return new \WP_Error('no_option', 'تنظیماتی پیدا نشد', array('status' => 404));
        }

        if (DependencyChecker::getInstance()->ioncubeHasError() || !class_exists('ZhUpClientUpdater\Zhup_backup')) {
            $old_backup=['plugins'=>[],'themes'=>[]];
        }else{
            $old_backup = Zhup_backup::new_option_backup_data($parameters);
        }
        $updater_data['backup']['plugins']=$old_backup['plugins']??[];
        $updater_data['backup']['themes']=$old_backup['themes']??[];

        foreach ($this->moduleWithSettings as $module){
            if ($module->getSettingKey()!=='settings'){
                $updater_data[$module->getSettingKey()]=$module->getSetting();
            }else{
                $updater_data['settings']=array_merge($updater_data['settings'], $module->getSetting());
            }
        }
        $updater_data['is_initialized']=true;
        if ($direct_access){
            return $updater_data;
        }
        return wp_send_json($updater_data);
    }

    public function initialStartupOption()
    {
        $old_option = get_option('ZhUpClient_options',[]);
        $settings=[];
        foreach ($this->moduleWithSettings as $module){
            $module->setSetting($settings,true);
        }

        if (empty($old_option)){
            $converted_data=$this->convert_new_option_to_old($settings);
            update_option('ZhUpClient_options', $converted_data['old']);
        }
    }

    public function saveOption() {
        self::securityCheck();
        $settings = $_REQUEST;

        foreach ($this->moduleWithSettings as $module){
            $module->setSetting($settings);
        }

        $converted_data=$this->convert_new_option_to_old($settings);
        update_option('ZhUpClient_options', $converted_data['old']);

        delete_site_transient( 'update_themes' );
        delete_site_transient( 'update_plugins' );

        if (!empty($converted_data['old']['send-data']) && $converted_data['old']['send-data']=='1'){
            if (!wp_next_scheduled('ZhUpClient_send_site_data')) {
                wp_schedule_event(time(), 'daily', 'ZhUpClient_send_site_data');
            }
        }else{
            wp_clear_scheduled_hook('ZhUpClient_send_site_data');
        }

        return wp_send_json(array('success' => true, 'message' => esc_html__('Setting Saved !','zhaket-updater'),'setting'=>$converted_data['new']));
    }


    private function getInstalledItemList() {
        $all_plugins    = get_plugins();
        $plugins_list   = [];
        $plugin_default = [];
        foreach ( (array)$all_plugins as $plugin_key => $plugin_data ) {
            $text_domain                   =  !empty($plugin_data['TextDomain']) ? $plugin_data['TextDomain'] : 'default';
            $plugins_list[ $plugin_key ]   = translate( $plugin_data['Name'], $text_domain );
            $plugin_default[ $plugin_key ] = 1;
        }


        $all_themes     = wp_get_themes();
        $themes_list    = [];
        $themes_default = [];
        foreach ( (array)$all_themes as $theme_key => $theme_data ) {
            $text_domain                  = !empty( $theme_data->textdomain_loaded ) ? $theme_data->textdomain_loaded : 'default';
            $themes_list[ $theme_key ]    = translate( $theme_data->get_stylesheet(), $text_domain );
            $themes_default[ $theme_key ] = 1;
        }

        return [
            'plugins' => [ $plugins_list, $plugin_default ],
            'themes'  => [ $themes_list, $themes_default ],
        ];

    }


    public function convert_new_option_to_old($new)
    {
        $new=Helper::convertArrayKeysAndValues($new);
        $old=[];
        $old['check-spacial-plugins']=[];
        $old['check-spacial-themes']=[];
        $old['ai']=[];
        $old['all-plugins']= Helper::convert_bool_to_int($new['checkUpdate']['allPlugins']??true);
        $old['all-themes']= Helper::convert_bool_to_int($new['checkUpdate']['allThemes'] ?? true);
        $old['remove-old-backup']=Helper::convert_bool_to_int($new['backup']['backup']??true);
        $old['backup-keep-count']=(string)($new['backup']['backupCount'] ?? 2);
        $old['send-notifications']= Helper::convert_bool_to_int($new['notifications']['all_notice']??true);
        $old['email-address']=$new['notifications']['email_for_notice']?? get_bloginfo('admin_email');
        $old['send-email-delay']=(string)($new['notifications']['email_interval'] ?? 2);
        $old['send-admin-notifications']= Helper::convert_bool_to_int($new['notifications']['email_notice']?? true);
        $old['show-admin-notifications']= Helper::convert_bool_to_int($new['notifications']['wordpress_notice'] ?? true);
        $old['send-data']= Helper::convert_bool_to_int($new['notifications']['send_data'] ?? false);
        $old['show-admin-offer']= Helper::convert_bool_to_int($new['notifications']['wordpress_offer']??true);
        $old['use-second-way-domain-test']=Helper::convert_bool_to_int($new['settings']['alternative_domain']?? false);
        $old['use-second-way-download']=Helper::convert_bool_to_int($new['settings']['alternative_update']?? false);
        $old['use-second-download-server']=Helper::convert_bool_to_int($new['settings']['alternative_server']?? false);

        $plugins = [];
        $old_setting_plugin_data = array_column(($new['checkUpdate']['spacialPlugins']??[]),'value','slug');
        $parameters = $this->getInstalledItemList();
        if (is_array($parameters['plugins'][0]))
            foreach ($parameters['plugins'][0] as $slug => $plugin) {
                $value=!isset($old_setting_plugin_data[$slug])? true : $old_setting_plugin_data[$slug];
                $plugins[]=[
                    'slug'=>$slug,
                    'name'=>$plugin,
                    'value'=>$value
                ];
            }
        $new['checkUpdate']['spacialPlugins']=$plugins;

        $themes = [];
        $old_setting_theme_data = array_column(( $new['checkUpdate']['spacialThemes']??[]),'value','slug');
        if (is_array($parameters['themes'][0]))
            foreach ($parameters['themes'][0] as $slug => $theme) {
                $value=!isset($old_setting_theme_data[$slug])? true : $old_setting_theme_data[$slug];
                $themes[]=[
                    'slug'=>$slug,
                    'name'=>$theme,
                    'value'=>$value
                ];
            }
        $new['checkUpdate']['spacialThemes']=$themes;

        foreach ((array)($new['checkUpdate']['spacialPlugins']??[]) as $value){
            $old['check-spacial-plugins'][$value['slug']]=!empty($value['value'])?'1':'0';
        }

        foreach ((array)($new['checkUpdate']['spacialThemes']??[]) as $value){
            $old['check-spacial-themes'][$value['slug']]=!empty($value['value'])?'1':'0';
        }

        return ['old'=>$old,'new'=>$new];
    }

    public function generate_product_link()
    {
        self::securityCheck();
        if (!DependencyChecker::getInstance()->canRunApplication() || !class_exists('ZhUpClientUpdater\zhUpClient_check')){
            wp_send_json_error(esc_html__('dependency check failed','zhaket-updater'));
        }

        zhUpClient_check::generate_downlaod_address($_REQUEST['product_id']);
    }
}