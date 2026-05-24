<?php


namespace ZhaketUpdater\HelpersClass;


abstract class Modules
{
    protected $settingKey;
    protected $optionKey;
    protected $defaultSettings;

    public function __construct()
    {
        $this->setDefaultSettings(false);
    }

    public function getSettingKey(){
        return $this->settingKey;
    }
    abstract public function setDefaultSettings($initial);

    protected function extractSetting($setting)
    {
        $moduleSetting = ['is_initialized'=>true];
        if (empty($this->defaultSettings) || !array($this->defaultSettings)){
            return $moduleSetting;
        }
        foreach ($this->defaultSettings as $key => $defaultValue) {
            if (isset($setting[$key])) {
                if (in_array($setting[$key],['true','false'])){
                    $moduleSetting[$key] = filter_var($setting[$key], FILTER_VALIDATE_BOOLEAN);
                }else{
                    $moduleSetting[$key] = $setting[$key];
                }

            }else{
                $moduleSetting[$key]=$defaultValue;
            }
        }
        return $moduleSetting;
    }

    public function getSetting()
    {
        $value = get_option($this->optionKey,[]);
        return $this->extractSetting($value);
    }

    public function getSettingValue($key, $defaultValue = null)
    {
        return $this->getSetting()[$key] ?? $defaultValue;
    }

    public function setSetting(array $full_setting,$initialing=false )
    {
        if ($initialing){
            $full_setting[$this->settingKey]=$this->getSetting();
        }
        $moduleSetting = $this->extractSetting($full_setting[$this->settingKey]??[]);
        update_option($this->optionKey, $moduleSetting,false);
    }
}