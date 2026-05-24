<?php

namespace ZhaketUpdater;

use ZhaketUpdater\AdminNotice\AdminNotice;
use ZhaketUpdater\AdminNotice\GenerateNotice;
use ZhaketUpdater\AdminNotice\Message;
use ZhaketUpdater\AdminNotice\NoticeLocation;
use ZhaketUpdater\AdminNotice\NotificationType;
use ZhaketUpdater\HelpersClass\SingleInstance;

final class DependencyChecker
{
    use SingleInstance;
    /**
     * @var true
     */
    private $phpHasError=true;
    /**
     * @var true
     */
    private $ioncubeHasError=true;


    public function __construct(){
        $this->checkDependency();
    }

    /**
     * @return true
     */
    public function ioncubeHasError()
    {
        return $this->ioncubeHasError;
    }

    /**
     * @return void
     */
    private function checkDependency(){
        $this->checkPhpVersion();
        $this->checkIonCubeStatus();
        $this->showNoticeHook();
    }

    /**
     * @return bool
     */
    public function canRunApplication()
    {
        return !$this->phpHasError && !$this->ioncubeHasError;
    }


    /**
     * @return void
     */
    private function showNoticeHook()
    {
        if ($this->ioncubeHasError){
            add_action('admin_notices',function (){
                echo (new GenerateNotice())->make($this->ionCubeErrorMessage());
            });

        }

        if ($this->phpHasError){
            add_action('admin_notices',function (){
                echo (new GenerateNotice())->make($this->phpErrorMessage());
            });
        }
    }

    /**
     * @return void
     */
    protected function checkPhpVersion(){
        $phpVersion=phpversion();
        if(version_compare( $phpVersion, '7.4', '>=') && version_compare( $phpVersion, '8.4', '<')) {
            $this->phpHasError = false;
        }
    }

    /**
     * @return void
     */
    protected function checkIonCubeStatus()
    {
        if (!extension_loaded('ionCube Loader')) return;

        $ioncubeLoaderVersion=ioncube_loader_version();

        if(function_exists('ioncube_loader_version') && version_compare($ioncubeLoaderVersion,'13.0','>='))
            $this->ioncubeHasError=false;
    }


    /**
     * @return array
     */
    public function ionCubeErrorMessage() {
        $content= esc_html__('we detect you do not have ionCube loader or it is too old , please call to your host service to update ionCube loader version to upper than 13.0','zhaket-updater');
        return [
            'title'=>esc_html__('Error in zhaket smart updater','zhaket-updater'),
            'title_color'=>'white',
            'content_color'=>'white',
            'bg_color'=>'#FF6437',
            'content'=>$content,
        ];
    }

    /**
     * @return array
     */
    public function phpErrorMessage(){
        $content = esc_html__('Zhaket updater plugin need php version 7.4 to 8.3.  please call to your host service to update php','zhaket-updater');
        return [
            'title'=>esc_html__('Error in zhaket smart updater','zhaket-updater'),
            'title_color'=>'white',
            'content_color'=>'white',
            'bg_color'=>'#FF6437',
            'content'=>$content,
        ];
    }
}