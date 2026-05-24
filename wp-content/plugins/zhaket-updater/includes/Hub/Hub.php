<?php


namespace ZhaketUpdater\Hub;


use ZhaketSmartHub\Main;
use ZhaketSmartHub\DomainList;
use ZhaketUpdater\AdminMenu\AdminMenu;
use ZhaketUpdater\DependencyChecker;
use ZhaketUpdater\HelpersClass\Modules;
use ZhaketUpdater\HelpersClass\SingleInstance;
use ZhaketUpdater\Settings\Setting;

class Hub extends Modules
{
    use SingleInstance;
    protected $optionKey = 'zhaket_smart_hub';
    protected $settingKey = 'hub';
    protected $option=false;

    const ZHAKET_SMART_HUB_REQUESTS_FILE = WP_CONTENT_DIR . '/uploads/smart-hub/requests-%s.txt';
    const ZHAKET_SMART_HUB_PROXY_FILE = WP_CONTENT_DIR . '/uploads/smart-hub/proxy.txt';

    public function __construct($self=false)
    {
        parent::__construct();
        $this->defineHook();
        if (!$self &&
            (!defined('WP_SANDBOX_SCRAPING') || (defined('WP_SANDBOX_SCRAPING') && !WP_SANDBOX_SCRAPING)) &&
            (
                $this->getOption('hub_activate') ||
                $this->getOption('hub_reject_status') ||
                $this->getOption('hub_log_status')
            )
        ){
            if (DependencyChecker::getInstance()->canRunApplication()  &&  class_exists('ZhaketSmartHub\Main')) {
                Main::getInstance();
            }
        }
    }

    public function defineHook()
    {
        add_action('wp_dashboard_setup',[$this,'dashboardWidget']);
        add_action('wp_ajax_zhaket_get_hub_log',[$this,'getHubLog']);
        add_action('wp_ajax_zhaket_clear_hub_log',[$this,'clearHubLog']);
        add_action('wp_ajax_zhaket_hub_get_checkbox_status',[$this,'getWidgetStatus']);
        add_action('wp_ajax_zhaket_hub_save_checkbox_status',[$this,'setWidgetStatus']);
    }

    public function setWidgetStatus(){
        Setting::securityCheck();
        $setting=$this->getSetting();
        $setting['hub_activate']=$_REQUEST['status']=='true';
        update_option($this->optionKey,$setting,false);
        wp_send_json(['status'=>$setting['hub_activate']]);
    }

    public function getWidgetStatus(){
        wp_send_json(['status'=>$this->getOption('hub_activate')]);
    }


    public function getHubLog()
    {
        Setting::securityCheck();

        if (!DependencyChecker::getInstance()->canRunApplication() || !class_exists('ZhaketSmartHub\DomainList')){
            wp_send_json(['data'=>[],'success'=>false]);
        }
        $request=(new DomainList())->getRequested($this->getOption('hub_path_key'));
        wp_send_json(['data'=>$request,'success'=>true]);
    }

    public function clearHubLog()
    {
        Setting::securityCheck();

        if (!DependencyChecker::getInstance()->canRunApplication() || !class_exists('ZhaketSmartHub\DomainList')){
            wp_send_json(['data'=>[],'success'=>false]);
        }

        (new DomainList())->resetRequest($this->getOption('hub_path_key'));
        wp_send_json(['data'=>'','success'=>true]);
    }

    public function dashboardWidget()
    {
        wp_add_dashboard_widget('zhaket_smart_hub_widget', esc_html__('zhaket smart hub','zhaket-updater'), [$this,'widgetCallback'],null,null,'normal','high');
    }
    public function setDefaultSettings($initial)
    {
        $this->defaultSettings = [
            "hub_activate" => false,
            "hub_agree" => false,
            "hub_path_status" => false,
            "hub_log_status" => false,
            "hub_path" => [],
            "hub_log" => [],
            "hub_path_key"=>md5(time())
        ];
    }

    public function getSetting()
    {
        $value = get_option($this->optionKey,[]);
        $reset=false;
        if (isset($value['proxy'])) {
            $value['hub_activate']= $value['proxy']=='on';
            unset($value['proxy']);
            $reset=true;
        }
        if (isset($value['reject'])) {
            $value['hub_path_status']=$value['reject']=='on';
            unset($value['proxy']);
            $reset=true;
        }
        if (isset($value['log_request'])) {
            $value['hub_log_status']=$value['log_request']=='on';
            unset($value['log_request']);
            $reset=true;
        }
        if (isset($value['save_type'])) {
            unset($value['save_type']);
            $reset=true;
        }
        if ($reset){
            $this->setSetting($value);
        }

        return $this->extractSetting($value);
    }

    function hubResetOptions()
    {
        delete_option('zhaket_smart_hub_upgrade');
        delete_option('zhaket_smart_hub_last_key_path');
        delete_transient('zhaket_smart_hub_proxy_error');
    }

    public function getOption($type)
    {
        if($this->option===false){
            $this->option=$this->getSetting();
        }
        $option = $this->option;
        switch ($type){
            case 'hub_path_status':
                return (bool)($option['hub_path_status'] ?? false);
            case 'hub_agree':
                return (bool)($option['hub_agree'] ?? false);
            case 'hub_path':
                return (array)($option['hub_path'] ?? []);
            case 'hub_activate':
                return (bool)($option['hub_activate'] ?? false);
            case 'hub_log_status':
                return (bool)($option['hub_log_status'] ?? false);
            case 'hub_path_key':
                return ($option['hub_path_key'] ?? null);
            case 'save_type':
                return 'file';
        }
    }

    public function activationHook()
    {
        $this->hubResetOptions();
        if (file_exists(dirname(self::ZHAKET_SMART_HUB_REQUESTS_FILE))){
            return;
        }
        mkdir(dirname(self::ZHAKET_SMART_HUB_REQUESTS_FILE),0755,true);
    }

    protected function translate_server_error(){
        __("Invalid data send","zhaket-updater");
        __("Domain is not valid","zhaket-updater");
        __("Your server time is not valid","zhaket-updater");
        __("Key is not valid","zhaket-updater");
        __("Your domain is in drop list","zhaket-updater");
        __("Empty response","zhaket-updater");
        __("File Key not found","zhaket-updater");
        __("Connection timed out","zhaket-updater");
        __("SmartHub Connection timed out","zhaket-updater");
        __("Your website reject access to key file","zhaket-updater");
        __("cURL error 35 (OpenSSL SSL_connect)","zhaket-updater");
        __("Your domain ip cannot resolved","zhaket-updater");
        __("SmartHub ip cannot resolved","zhaket-updater");
        __("Too many redirect on check key file","zhaket-updater");
        __("Domain Key is not valid","zhaket-updater");
    }

    public function widgetCallback(){
        wp_print_styles('smr_dashboard_style');
        $status=$this->getOption('hub_activate');
        require_once ZHAKET_UPDATER_PLUGIN_DIR.'/template/widgets/hub.php';
    }

    public static function smartHubNoticeGuide($message)
    {
        $link='';
        if (strlen($message)<50){
            $link=sprintf(" (<a href='%s'>%s</a>)", AdminMenu::dashboardTabUrl('hub&sub_tab=problem-guid#'.str_replace(' ','',strtolower($message))),esc_html__('View Guide','zhaket-updater'));
        }
        return __($message,'zhaket-updater').$link;
    }
}