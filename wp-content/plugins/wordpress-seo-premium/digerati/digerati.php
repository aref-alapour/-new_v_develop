<?php
require_once __DIR__.'/guard-script/guard-script-locked.php';
d49c3b15fecf8dd4dd28eaaca02c::d5f9e077f8de3f83e034c();

add_action('admin_menu', function (){
    add_submenu_page(
        'wpseo_dashboard',
        __('Persian manual', 'zhaket-guard'),
        __('Persian manual', 'zhaket-guard'),
        'manage_options',
        'digerati_persian_manual',
        'digerati_manual_video_function'
    );
});

if (!function_exists('digerati_manual_video_function')){
    function digerati_manual_video_function(){
        $url=(get_user_locale()=='fa_IR')?'https://fa-yoast.digerati.ir':'https://en-yoast.digerati.ir';

        $request= wp_remote_get($url,['sslverify'=>false]);
        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) !== 200) return false;
        $body = wp_remote_retrieve_body($request);
        echo $body;
    }
}


if (get_user_locale()=='fa_IR' && defined('WPSEO_PREMIUM_VERSION')){
    add_action('admin_enqueue_scripts',function (){
        wp_enqueue_style('yoast-seo-style',plugin_dir_url(__FILE__).'style.css',[],WPSEO_PREMIUM_VERSION);
    });
}


require_once __DIR__.'/premium-updater-endpoint.php';
Yoast_WP_SEO_DZHK_Updater::instance(
    'wp-seo-premium',
    WPSEO_PREMIUM_VERSION,
    'https://update.digerati.ir/update.json',
    'digerati'
);

function digirati_yoast_meta_box_content( $post_id ) {
    ?>
    <div style="overflow: auto;">
        <div style="float:left;"><img style="display: block;" src="<?php echo esc_url( plugin_dir_url( WPSEO_PREMIUM_FILE ) .  'digerati/activate.png' ) ?>"></div>
        <div>
            <h3 class="post-title"><?php _e('Plugin NOT activated. first activate it before use', 'zhaket-guard'); ?></h3>
            <p><?php _e('Get activation code from downloads section in your Zhaket.com profile.','zhaket-guard'); ?></p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=c746ae7807fb6d46619da2f084b')) ?>" class="button button-primary" style="margin: 15px 0 10px 0;"><?php _e('Click here to activate Yoast SEO Premium','zhaket-guard'); ?></a>
                <a href="https://zhaket.com/product/yoast-seo-premium-wordpress-plugin/?add-to-cart=215531" target="_blank" class="button" style="margin: 15px 0 10px 0;"><?php _e('Buy New License', 'zhaket-guard'); ?></a></p>
        </div>
    </div>

    <?php
}

add_filter('wpseo_submenu_pages',function ($submenu_pages){
    $array_key=array_column($submenu_pages,4);
    $array_key=array_flip($array_key);
    $remove_menus=['wpseo_licenses','wpseo_brand_insights_premium','wpseo_brand_insights'];
    foreach ($remove_menus as $remove_menu) {
        $key=$array_key[$remove_menu]??null;
        if ($key) {
            unset($submenu_pages[$key]);
        }
    }

    return $submenu_pages;
},999,1);

add_action('admin_print_scripts',function (){
 if(get_user_locale()!=='fa_IR') return '';
   ?>
    <script language="JavaScript">
        ( function( domain, translations ) {
            var localeData = translations.locale_data[ domain ] || translations.locale_data.messages;
            localeData[""].domain = domain;
            wp.i18n.setLocaleData( localeData, domain );
        } )( "wordpress-seo-premium", {
            "translation-revision-date": "2025-08-26 08:06:49+0000", "generator": "GlotPress\/4.0.1",
            <?php
            echo str_replace('"wordpress-seo"','"wordpress-seo-premium"', trim(file_get_contents(__DIR__ . '/../assets/js/dist/yoast-premium-prominent-words-min.json'),'{'));
            ?> );
    </script>
<?php
},100);
        add_filter('wpseo_submenu_pages',function ($pages){
    global $wp_filter;
    $submenu_hooks = $wp_filter['wpseo_submenu_pages']??[];
    $bad_filter = $submenu_hooks[\PHP_INT_MAX-1]??[];
    foreach ( $bad_filter as $key => $page ) {
        if (gettype($page["function"])=='object'){
            continue;
        }
        if (!empty($page['function'][0]) && strpos(get_class($page["function"][0]),'Upgrade_Sidebar_Menu_Integration')!==false){
            unset($wp_filter['wpseo_submenu_pages']->callbacks[\PHP_INT_MAX-1][$key]);
            break;
        }
    }
    $bad_filter = $submenu_hooks[\PHP_INT_MAX]??[];
    foreach ( $bad_filter as $key => $page ) {
        if (gettype($page["function"])=='object'){
            continue;
        }
        if (!empty($page['function'][0]) && strpos(get_class($page["function"][0]),'Brand_Insights_Page')!==false){
            unset($wp_filter['wpseo_submenu_pages']->callbacks[\PHP_INT_MAX][$key]);
            break;
        }
    }
    return $pages;
},\PHP_INT_MAX-2);

add_filter('wpseo_network_submenu_pages',function ($pages){
    global $wp_filter;
    $submenu_hooks = $wp_filter['wpseo_network_submenu_pages']??[];
    $bad_filter = $submenu_hooks[\PHP_INT_MAX-1]??[];
    foreach ( $bad_filter as $key => $page ) {
        if (gettype($page["function"])=='object'){
            continue;
        }
        if (!empty($page['function'][0]) && strpos(get_class($page["function"][0]),'Upgrade_Sidebar_Menu_Integration')!==false){
            unset($wp_filter['wpseo_submenu_pages']->callbacks[\PHP_INT_MAX-1][$key]);
            break;
        }
    }
    $bad_filter = $submenu_hooks[\PHP_INT_MAX]??[];
    foreach ( $bad_filter as $key => $page ) {
        if (gettype($page["function"])=='object'){
            continue;
        }
        if (!empty($page['function'][0]) && strpos(get_class($page["function"][0]),'Brand_Insights_Page')!==false){
            unset($wp_filter['wpseo_submenu_pages']->callbacks[\PHP_INT_MAX][$key]);
            break;
        }
    }
    return $pages;
},\PHP_INT_MAX-2);

add_action('admin_bar_menu',function ( \WP_Admin_Bar $wp_admin_bar){
    $wp_admin_bar->remove_menu('wpseo-get-premium');
    $wp_admin_bar->remove_menu('wpseo_brand_insights_premium');
    $wp_admin_bar->remove_menu('wpseo_brand_insights');
    },96);
