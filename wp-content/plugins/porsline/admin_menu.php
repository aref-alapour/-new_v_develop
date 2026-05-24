<?php
add_action("admin_menu",function(){
    add_menu_page(__( 'Pors Line', PORSLINE_DOMAIN ),__( 'Pors Line', PORSLINE_DOMAIN ),'manage_options','porsline','porsline_dashboard',plugin_dir_url(__FILE__).'assets/icon.png' ,'');

	//add_submenu_page(PLUGIN_SLUG,'Your Plugin','Dashboard',PLUGIN_ROLE,PLUGIN_SLUG,'your_plugin_dashboard_callback',);
});
function porsline_form_token(){
   
    if(isset($_POST['token']) && !empty($_POST['token'])){
        $token = wp_strip_all_tags($_POST['token']);
        if(prs_whoami($token)){
            update_option("prsline_token",$token);
            echo '<script> window.location.href = window.location.href </script>';
        } else {
            echo '<br><br><br><br><div class="notice notice-error"><p>'.__('Token You Entered Is Wrong',PORSLINE_DOMAIN).'</p></div>';
        }
    }
?>
    <div class="form_token">
		<div class="title-wrapper">
        	<h3><?=__("Welcome To Pors Line",PORSLINE_DOMAIN); ?></h3>
			<img src="<?=plugin_dir_url(__FILE__)?>assets/images/porsline-logo.png" alt="Porsline" draggable="false" />
		</div>
        <ul class="help_prsline">
            <li><?=__('Log in to your <a href="https://survey.porsline.com/" target="_blank">Porsline</a> account',PORSLINE_DOMAIN); ?></li>
            <li><?=__('From your profile menu, go to the API Key section.',PORSLINE_DOMAIN); ?></li>
            <li><?=__('Click on Generate API Key.',PORSLINE_DOMAIN); ?></li>
            <li><?=__('Copy the API key and paste it here.',PORSLINE_DOMAIN); ?></li>
        </ul>
        <form method="post" action="<?=admin_url("admin.php?page=porsline"); ?>">
			<div class="form-wrapper">
				<div class="form-label">
					<span>کلید API</span>
				</div>
				<div class="form-input">
					<input type="text" name="token" id="token" placeholder="<?=str_repeat("x",41); ?>">
				</div>
			</div>
			<div class="button-wrapper">
            	<button class="button button-primary" type="submit"><?=__("Submit",PORSLINE_DOMAIN); ?></button>
			</div>
        </form>
    </div>
<?php
}
function porsline_list_all(){
    $all = json_decode(Pload(prs_endpoint()."/api/folders/"),true);
    $path = plugin_dir_path(__FILE__);
    $url = plugin_dir_url(__FILE__);
    $folder = "/assets/images/icon/";
    echo '<script> const icons_url = "'.$url.$folder.'"; </script>';
?>
<div class="prs_head">
<h3>
	<?=__("Forms",PORSLINE_DOMAIN); ?>
</h3>
<a href="<?=admin_url("admin.php?page=porsline&logout=1"); ?>" class="button button-primary"><?=__("LogOut",PORSLINE_DOMAIN); ?></a>
</div>
<div class="porsline">
    <div class="tabs">
		<h4 class="folders">
			<?=__("Folders",PORSLINE_DOMAIN); ?>
		</h4>
<?php
$show = false;
foreach($all as $a){
    if(empty($a['surveys'])){ continue; }
    echo '<div class="tab'.($show ? '' : ' active').'" data-tab="tbl'.$a['id'].'"><div class="icon-title"><span class="dashicons dashicons-open-folder"></span><span>'.$a['name'].'</span></div><span>'.count($a['surveys']).'</span></div>';
    $show = true;
}
?>
    </div>
    <div class="contents">
<?php
$show = false;
foreach($all as $i=>$a){
	usort($all[$i]['surveys'], function ($a, $b) {
		return strtotime($b['created_date']) <=> strtotime($a['created_date']);
	});
}
foreach($all as $a){
    if(empty($a['surveys'])){ continue; }
    echo '<div class="content" id="tbl'.$a['id'].'"'.($show ? ' style="display:none;"' : '').'>';
foreach($a['surveys'] as $i){
    echo '<div class="item">
        <div class="infos">
            <h4>'.$i['name'].'</h4>
        </div>
        <div class="options">
			<div class="form-information">
				<div class="form-information__view">
					<label>'.__("Views",PORSLINE_DOMAIN).'</label>
					<div>'.$i['views'].'</div>
				</div>
				<div class="form-information__answer">
					<label>'.__("Answers",PORSLINE_DOMAIN).'</label>
					<div>'.$i['submitted_responses'].'</div>
				</div>
				<div class="form-information__status">
					<div'.($i['active'] == 1 ? '' : ' class="not-active"').'>'.($i['active'] == 1 ? __("Enable",PORSLINE_DOMAIN) : __("Disable",PORSLINE_DOMAIN)).'</div>
				</div>
			</div>
            <button class="settings" data-code="'.$i['preview_code'].'"><span class="dashicons dashicons-ellipsis"></span></button>
        </div>
    </div>';
}
echo '</div>';
?>

<?php $show = true; } ?>
</div>
    </div>
</div>
<div class="lighter">
<div class="dialog settings">
    <div class="porsline-shortcode-container">
        <p class="porsline-shortcode-label"><?=__("Copy the code below and place it in the desired section.",PORSLINE_DOMAIN); ?></p>
        <div class="porsline-shortcode-input-container">
            <input type="text" class="porsline-shortcode-input" readonly value="[porsline ]">
            <span class="preview_code" style="display:none;"></span>
            <button type="button" class="porsline-copy-btn"><?=__("Copy",PORSLINE_DOMAIN); ?></button>
        </div>
    </div>
    
    <label class="title section-title"><?=__("Choose Type Of Showing",PORSLINE_DOMAIN); ?></label>
    
    <div class="display-mode-cards">
        <div class="display-card" data-value="iframe">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/standard-iframe.svg" alt="استاندارد">
            </div>
            <div class="card-title"><?=__("Iframe",PORSLINE_DOMAIN); ?></div>
        </div>
        
        <div class="display-card" data-value="popup">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/popup.svg" alt="پاپ آپ">
            </div>
            <div class="card-title"><?=__("PopUp",PORSLINE_DOMAIN); ?></div>
        </div>
        
        <div class="display-card" data-value="newtab">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/fullscreen.svg" alt="تب جدید">
            </div>
            <div class="card-title"><?=__("New Tab",PORSLINE_DOMAIN); ?></div>
        </div>
        
        <div class="display-card" data-value="full">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/fullscreen.svg" alt="تمام صفحه">
            </div>
            <div class="card-title"><?=__("Full Screen",PORSLINE_DOMAIN); ?></div>
        </div>
        
        <div class="display-card" data-value="slidein">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/coursel-slider.svg" alt="اسلایدر">
            </div>
            <div class="card-title"><?=__("Slider",PORSLINE_DOMAIN); ?></div>
        </div>
        
        <div class="display-card" data-value="sidetab">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/side-tab.svg" alt="تب کناری">
            </div>
            <div class="card-title"><?=__("SideTab Screen",PORSLINE_DOMAIN); ?></div>
        </div>
        
        <div class="display-card" data-value="widget">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/popover-widget.svg" alt="ویجت">
            </div>
            <div class="card-title"><?=__("Widget",PORSLINE_DOMAIN); ?></div>
        </div>
        
        <div class="display-card" data-value="newwindow">
            <div class="card-image">
                <img src="<?=plugin_dir_url(__FILE__)?>assets/images/fullscreen.svg" alt="پنجره جدید">
            </div>
            <div class="card-title"><?=__("New Window",PORSLINE_DOMAIN); ?></div>
        </div>
    </div>
    
    <input type="hidden" name="type" id="type">
    
    <div class="iframe_opts">
        <div class="input-group">
            <label class="title" for="brd_size"><?=__("Border Size",PORSLINE_DOMAIN); ?></label>
            <input type="number" value="0" name="brd_size" id="brd_size" min="0">
            <span class="unit-label">px</span>
        </div>
        
        <div class="colorpicker-group">
            <label class="title" for="brd_clr"><?=__("Border Color",PORSLINE_DOMAIN); ?></label>
            <input type="text" class="colorpicker" name="brd_clr" id="brd_clr">
        </div>
        
        <div class="input-group not-full-width">
            <label class="title" for="ifm_width">عرض</label>
            <input type="text" value="100" name="ifm_width_value" id="ifm_width_value" class="dimension-value">
            <select name="ifm_width_unit" id="ifm_width_unit" class="dimension-unit">
                <option value="%">%</option>
                <option value="px">px</option>
                <option value="auto">auto</option>
            </select>
            <input type="hidden" name="ifm_width" id="ifm_width" value="100%">
        </div>
        
        <div class="input-group not-full-width">
            <label class="title" for="ifm_height">طول</label>
            <input type="text" value="auto" name="ifm_height_value" id="ifm_height_value" class="dimension-value">
            <select name="ifm_height_unit" id="ifm_height_unit" class="dimension-unit">
                <option value="%">%</option>
                <option value="px">px</option>
                <option value="auto" selected>auto</option>
            </select>
            <input type="hidden" name="ifm_height" id="ifm_height" value="auto">
        </div>
    </div>
    
    <div class="btn_opts">
        <div class="input-group">
            <label class="title" for="btn_text"><?=__("Button Text",PORSLINE_DOMAIN); ?></label>
            <input type="text" name="btn_text" id="btn_text">
        </div>
        <div class="input-group iconkeeper">
            <label class="title" for="btn_brd"><?=__("Icon",PORSLINE_DOMAIN); ?></label>
            <div class="icon">
                <span class="holder"></span>
                <div class="dropdown">
                    <?php  foreach(glob($path."$folder/*.svg") as $i){ echo '<img data-key="'.(explode(".",basename($i))[0]).'" src="'.$url.$folder.basename($i).'">'; } ?>
                </div>
            </div>
            <span class="removeicon">حذف آیکن</span>
        </div>
        <input type="hidden" name="icon" id="icon">

        <div class="colorpicker-group">
            <label class="title" for="icon_color"><?=__("Icon Color",PORSLINE_DOMAIN); ?></label>
            <input type="text" class="colorpicker" name="icon_color" id="icon_color">
        </div>

        <div class="colorpicker-group">
            <label class="title" for="btn_bg"><?=__("Button Background Color",PORSLINE_DOMAIN); ?></label>
            <input type="text" class="colorpicker" name="btn_bg" id="btn_bg">
        </div>
        
        <div class="colorpicker-group">
            <label class="title" for="btn_txt_clr"><?=__("Button Text Color",PORSLINE_DOMAIN); ?></label>
            <input type="text" class="colorpicker" name="btn_txt_clr" id="btn_txt_clr">
        </div>
        
        <div class="input-group">
            <label class="title" for="btn_brd"><?=__("Button Border Size",PORSLINE_DOMAIN); ?></label>
            <input type="number" name="btn_brd" id="btn_brd" value="0" min="0">
            <span>px</span>
        </div>
        
        <div class="colorpicker-group">
            <label class="title" for="btn_brd_clr"><?=__("Button Border Color",PORSLINE_DOMAIN); ?></label>
            <input type="text" class="colorpicker" name="btn_brd_clr" id="btn_brd_clr">
        </div>
        
        <div class="input-group input-group-for-select">
            <label class="title" for="btn_brd_stl"><?=__("Button Border Style",PORSLINE_DOMAIN); ?></label>
			<div class="select-parent">
            <select name="btn_brd_stl" id="btn_brd_stl">
                <option value="solid">Solid</option>
                <option value="dashed">Dashed</option>
                <option value="dotted">Dotted</option>
                <option value="double">Double</option>
                <option value="groove">Groove</option>
                <option value="ridge">Ridge</option>
                <option value="outset">Outset</option>
            </select>
			</div>
        </div>
        
        <div class="input-group input-group-for-select">
            <label class="title" for="btn_align"><?=__("Button Align",PORSLINE_DOMAIN); ?></label>
			<div class="select-parent">
            <select name="btn_align" id="btn_align">
                <option value="right"><?=__("Right",PORSLINE_DOMAIN); ?></option>
                <option value="center"><?=__("Center",PORSLINE_DOMAIN); ?></option>
                <option value="left"><?=__("Left",PORSLINE_DOMAIN); ?></option>
            </select>
			</div>
        </div>
    </div>
    
    <button class="button button-primary save"><?=__("Save",PORSLINE_DOMAIN); ?></button>
</div>
</div>
<?php
}
function porsline_dashboard(){
    if(isset($_GET['logout'])){
        delete_option("prsline_token");
    }
    if(!get_option("prsline_token",false)){
        porsline_form_token();
    } else {
        porsline_list_all();
    }
}
?>
