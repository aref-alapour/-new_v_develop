<?php


namespace ZhaketUpdater\Settings;


final class Helper
{

    public static function convert_old_option_to_new($old)
    {
        $new=[];
        $new['backup']=[];
        $new['notifications']=[];
        $new['settings']=[];
        $new['checkUpdate']=[];

        $new['checkUpdate']['allPlugins']= ($old['all-plugins']??'1') == '1';
        $new['checkUpdate']['allThemes']= ($old['all-themes']??'1') == '1';
        $new['backup']['backup']= ($old['remove-old-backup']??'1') == '1';
        $new['backup']['backupCount']=(int)($old['backup-keep-count']??'2');
        $new['notifications']['all_notice']= ($old['send-notifications']??'1') == '1';
        $new['notifications']['email_for_notice']=$old['email-address'] ?? get_bloginfo('admin_email');
        $new['notifications']['email_interval']=(int)($old['send-email-delay']??'2');
        $new['notifications']['email_notice']= ($old['send-admin-notifications']??'1') == '1';
        $new['notifications']['wordpress_notice']= ($old['show-admin-notifications']??'1') == '1';
        $new['notifications']['wordpress_offer']= ($old['show-admin-offer']??'1') == '1';
        $new['notifications']['send_data']= ($old['send-data']??'0') == '1';

        $new['settings']['alternative_domain']= ($old['use-second-way-domain-test']??'1') == '1';
        $new['settings']['alternative_update']= ($old['use-second-way-download']??'1') == '1';
        $new['settings']['alternative_server']= ($old['use-second-download-server']??'1') == '1';

        foreach ((array)($old['check-spacial-plugins']??[]) as $key => $value){
            $new['checkUpdate']['spacialPlugins'][]=['slug'=>$key,'value'=>$value=='1'];
        }
        foreach ((array)($old['check-spacial-themes']??[]) as $key => $value){
            $new['checkUpdate']['spacialThemes'][]=['slug'=>$key,'value'=>($value=='1')];
        }

        return $new;
    }

    public static function convert_bool_to_int($value){
        if ($value==false)
            return '0';
        return '1';
    }

    public static function convertArrayKeysAndValues($array) {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::convertArrayKeysAndValues($value);
            } else {
                // Convert "true" and "false" strings to boolean
                if ($value === "true") {
                    $result[$key] = true;
                } elseif ($value === "false") {
                    $result[$key] = false;
                } else {
                    // Leave other values unchanged
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }


}