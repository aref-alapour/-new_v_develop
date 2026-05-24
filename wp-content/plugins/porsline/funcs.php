<?php
function Pload($url,$data=null){
    $ch = curl_init();
    $headers = ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36"];
    if(get_option("prsline_token")){
        $headers[] = 'Authorization: API-Key '.get_option("prsline_token");
    }
    if($data != null){
        $headers[] = 'Content-Type:application/json';
    }
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if($data != null){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $curl_scraped_page = curl_exec($ch);
    if(curl_error($ch)){ die(curl_error($ch)); }
    curl_close($ch);
    return $curl_scraped_page;
}
function prs_whoami($token){
    $data = json_decode(Pload(PORSLINE_API_IR."/api/whoami/",json_encode(["authorization"=>"API-Key $token"])),true);
    if(isset($data['authenticated']) && $data['authenticated']){
        update_option("prsline_location","ir");
        return true;
    }else if(isset($data['authenticated']) && $data['authenticated'] == false){
        $data = json_decode(Pload(PORSLINE_API_GB."/api/whoami/",json_encode(["authorization"=>"API-Key $token"])),true);
        if((isset($data['authenticated']) && $data['authenticated'])){
            update_option("prsline_location","gb");
            return true;
        }
    }
    return false;
}
function prs_get_form_data($code){
    $data = get_option("prs_".$code,[]);
    $ret = [];
    $ret['type'] = isset($data['type']) ? $data['type'] : "iframe";
    $ret['brd_size'] = isset($data['brd_size']) ? $data['brd_size'] : "0";
    $ret['brd_clr'] = isset($data['brd_clr']) ? $data['brd_clr'] : "#ffffff";
    $ret['ifm_width'] = isset($data['ifm_width']) ? $data['ifm_width'] : "100%";
    $ret['ifm_height'] = isset($data['ifm_height']) ? $data['ifm_height'] : "480px";
    $ret['btn_text'] = isset($data['btn_text']) ? $data['btn_text'] : __("Click",PORSLINE_DOMAIN);
    $ret['btn_bg'] = isset($data['btn_bg']) ? $data['btn_bg'] : "#333";
    $ret['icon'] = isset($data['icon']) ? $data['icon'] : "";
    $ret['icon_color'] = isset($data['icon_color']) ? $data['icon_color'] : "#000";
    $ret['btn_brd'] = isset($data['btn_brd']) ? $data['btn_brd'] : "0";
    $ret['btn_brd_clr'] = isset($data['btn_brd_clr']) ? $data['btn_brd_clr'] : "#555";
    $ret['btn_brd_stl'] = isset($data['btn_brd_stl']) ? $data['btn_brd_stl'] : "solid";
    $ret['btn_txt_clr'] = isset($data['btn_txt_clr']) ? $data['btn_txt_clr'] : "#fff";
    $ret['btn_align'] = isset($data['btn_align']) ? $data['btn_align'] : "center";
    return $ret;
}
function prs_endpoint(){
    $loc = get_option("prsline_location","ir");
    return ($loc == "ir" ? PORSLINE_API_IR : PORSLINE_API_GB);
}
function prs_geticons(){
    $url = plugin_dir_url(__FILE__);
    $path = plugin_dir_path(__FILE__);
    $icons = [];
    foreach(glob($path."/assets/images/icon/*.svg") as $f){
        $cls = explode(".",basename($f))[0];
        $icons[$cls] = $path.'/assets/images/icon/'.basename($f);
    }
    return $icons;
}
?>