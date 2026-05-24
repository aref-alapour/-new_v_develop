<?php
add_action("wp_ajax_prs_load",function(){
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'prs_load_nonce' ) ) {
        die(json_encode(["result" => "0", "message" => "Invalid nonce."]));
    }
    
    if(isset($_POST['code']) && !empty($_POST['code']) && current_user_can("manage_options")){
        die(json_encode(prs_get_form_data($_POST['code'])));
    }
    die(json_encode(["result"=>"0"]));
});
add_action("wp_ajax_prs_save",function(){
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'prs_load_nonce' ) ) {
        die(json_encode(["result" => "0", "message" => "Invalid nonce."]));
    }
    if(isset($_POST['code']) && !empty($_POST['code']) && current_user_can("manage_options")){
        $save = [];
        $save['type'] = isset($_POST['type']) ? $_POST['type'] : "iframe";
        $save['brd_size'] = isset($_POST['brd_size']) ? $_POST['brd_size'] : "0";
        $save['brd_clr'] = isset($_POST['brd_clr']) ? $_POST['brd_clr'] : "#ffffff";
        $save['ifm_width'] = isset($_POST['ifm_width']) ? $_POST['ifm_width'] : "100%";
        $save['icon'] = isset($_POST['icon']) ? $_POST['icon'] : "";
        $save['icon_color'] = isset($_POST['icon_color']) ? $_POST['icon_color'] : "#000";
        $save['ifm_height'] = isset($_POST['ifm_height']) ? $_POST['ifm_height'] : "auto";
        $save['btn_text'] = isset($_POST['btn_text']) ? $_POST['btn_text'] : __("Click",PORSLINE_DOMAIN);
        $save['btn_bg'] = isset($_POST['btn_bg']) ? $_POST['btn_bg'] : "#333";
        $save['btn_brd'] = isset($_POST['btn_brd']) ? $_POST['btn_brd'] : "0";
        $save['btn_brd_clr'] = isset($_POST['btn_brd_clr']) ? $_POST['btn_brd_clr'] : "#555";
        $save['btn_brd_stl'] = isset($_POST['btn_brd_stl']) ? $_POST['btn_brd_stl'] : "solid";
        $save['btn_txt_clr'] = isset($_POST['btn_txt_clr']) ? $_POST['btn_txt_clr'] : "#fff";
        $save['btn_align'] = isset($_POST['btn_align']) ? $_POST['btn_align'] : "center";
        update_option("prs_".$_POST['code'],$save);
        die(json_encode(["result"=>"1"]));
    }
    die(json_encode(["result"=>"0"]));
})
?>