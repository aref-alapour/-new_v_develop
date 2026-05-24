<?php
	global $tab;

	if ( empty( $_REQUEST['plugin'] ) ) {
        return;
    }

	$api = plugins_api(
        'plugin_information',
        array(
            'slug' => wp_unslash( $_REQUEST['plugin'] ),
        )
    );

	if ( is_wp_error( $api ) ) {
        wp_die( $api );
    }

	$plugins_allowedtags = array(
        'a'          => array(
            'href'   => array(),
            'title'  => array(),
            'target' => array(),
        ),
        'abbr'       => array( 'title' => array() ),
        'acronym'    => array( 'title' => array() ),
        'code'       => array(),
        'pre'        => array(),
        'em'         => array(),
        'strong'     => array(),
        'div'        => array( 'class' => array() ),
        'span'       => array( 'class' => array() ),
        'p'          => array(),
        'br'         => array(),
        'ul'         => array(),
        'ol'         => array(),
        'li'         => array(),
        'h1'         => array(),
        'h2'         => array(),
        'h3'         => array(),
        'h4'         => array(),
        'h5'         => array(),
        'h6'         => array(),
        'img'        => array(
            'src'   => array(),
            'class' => array(),
            'alt'   => array(),
        ),
        'blockquote' => array( 'cite' => true ),
    );

	$plugins_section_titles = array(
        'description'  => _x( 'Description', 'Plugin installer section title' ),
        'installation' => _x( 'Installation', 'Plugin installer section title' ),
        'faq'          => _x( 'FAQ', 'Plugin installer section title' ),
        'screenshots'  => _x( 'Screenshots', 'Plugin installer section title' ),
        'changelog'    => _x( 'Changelog', 'Plugin installer section title' ),
        'reviews'      => _x( 'Reviews', 'Plugin installer section title' ),
        'other_notes'  => _x( 'Other Notes', 'Plugin installer section title' ),
        'video'  => _x( 'video','','zhaket-updater' ),
    );

	// Sanitize HTML
	foreach ( (array) $api->sections as $section_name => $content ) {
	    $content=preg_replace('/[\r\n]{2,}/',"<br>",$content);
	    $content=nl2br($content);
        $api->sections[ $section_name ] = wp_kses( $content, $plugins_allowedtags );
    }
	
	if (!empty($api->video)) $api->sections['video']=do_shortcode(sprintf('[video src="%s" ]',$api->video));

	foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug','video','icon','demo_url','cover' ) as $key ) {
        if ( isset( $api->$key ) ) {
            $api->$key = wp_kses( $api->$key, $plugins_allowedtags );
        }
    }
    $api->banners=['low'=>[]];
	if (!empty($api->cover)) {
        $api->sections['screenshots']=sprintf('<img src="%s" alt="%s" class="zhk_updater_plugin_details_description_img" />',$api->cover,$api->name);
        if (isset($api->cover_2 )){
            $api->sections['screenshots'].=sprintf('<img src="%s" alt="%s"  class="zhk_updater_plugin_details_description_img"  />',$api->cover_2,$api->name);
        }
        $api->banners[ 'high' ]= ZHAKET_UPDATER_PLUGIN_URL.'assets/admin/img/zhaket.png';
//        $api->banners[ 'low' ][] = (isset( $api->cover_2 ) && !empty( $api->cover_2 )) ? $api->cover_2 : $api->cover;
    }

	$_tab = esc_attr( $tab );

	$section = isset( $_REQUEST['section'] ) ? wp_unslash( $_REQUEST['section'] ) : 'description'; // Default to the Description tab, Do not translate, API returns English.
	if ( empty( $section ) || ! isset( $api->sections[ $section ] ) ) {
        $section_titles = array_keys( (array) $api->sections );
        $section        = reset( $section_titles );
    }

	iframe_header( __( 'Plugin Installation' ) );

	$_with_banner = '';

	if ( ! empty( $api->banners ) && (! empty( $api->banners['high'] ) ) ) {
        $_with_banner = 'with-banner';
        $low          = empty( $api->banners['low'] ) ? $api->banners['high'] : $api->banners['low'];
        $high         = empty( $api->banners['high'] ) ? $api->banners['low'] : $api->banners['high'];
        ?>
        <style type="text/css">
            #plugin-information-title.with-banner {
                background-image: url( <?php echo esc_url(  $api->icon ); ?> ),url(<?php echo esc_url( $high ); ?> );
                background-color: #4d4d4d;
                background-size: 100px,cover;
                background-repeat: no-repeat;
                background-position: right 10px center,left;

            }
            @media only screen and ( -webkit-min-device-pixel-ratio: 1.5 ) {
                #plugin-information-title.with-banner {
                    background-image: url( <?php echo esc_url( $high ); ?> );
                    background-color: #4d4d4d;
                    background-size: cover;
                    background-repeat: no-repeat;
                    background-position: 200px 0;
                }
            }
            #section-screenshots img,#section-description img,#section-gallery img{
                max-width:100%;
            }
            #section-video .wp-video>div{
                position: absolute;
                top: 20px;
            }
        </style>
        <?php
    }
	echo '<div id="plugin-information-scrollable">';
	echo "<div id='{$_tab}-title' class='{$_with_banner}'><div class='vignette'></div><h2>{$api->name}</h2></div>";
	echo "<div id='{$_tab}-tabs' class='{$_with_banner}'>\n";

$api->sections['faq']='<div class="zhk_updater_plugin_details_rtl_correction"><h3 class="zhk_updater_plugin_details_faq_question">پیش از به روز رسانی چه کارهایی باید انجام بدهم؟</h3>
<ul>
    <li>با مراجعه به پنل مدیریت فایل ها در هاست خود یا استفاده از ابزارهای مدیریت اف تی پی، از فایل فعلی محصول در پوشه wp-content/plugins یک نسخه پشتیبان تهیه کنید.</li>
    <li>از سازگار بودن نسخه جدید محصول با نسخه وردپرس و سایر محصولات اعم از افزونه ها و قالب سایت خود اطمینان کسب کنید.</li>
    <li>جزییات تغییرات محصول را در صفحه محصول روی سایت ژاکت یا در سایت اصلی سازنده ان مطالعه کرده سپس به روزرسانی به نسخه جدید را اجرا کنید.</li>
</ul>

<h3 class="zhk_updater_plugin_details_faq_question">آیا به محض مشاهده پیام به روز رسانی لازم است تا به روز رسانی را انجام دهم؟</h3>
<ul>
    <li>یکی از مهم ترین مولفه ها برای پایدار بودن سرعت و امنیت سایت های وردپرسی، به روز بودن قالب ها و افزونه هایی است که روی آن نصب شده اند. اما پیش از به روز رسانی محصولات باید توجه کنید این به روز رسانی موجب تداخل با سایر محصولات شما نشود. لذا پیشنهاد می شود در صورتی که در تغییرات محصول، موارد و مشکلات امنیتی رفع شده است نسبت به به روزرسانی سریع اقدام نمایید و چنانچه به روز رسانی صرفا شامل اضافه شدن امکانات جدید است پس از بررسی عمیق تر تغییرات، به روز رسانی را اعمال کنید.</li>
</ul>

<h3 class="zhk_updater_plugin_details_faq_question">چنانچه پس از به روز رسانی به نسخه جدید با مشکل دسترسی به پیشخوان مدیریتی وردپرس مواجه شدم چه کار باید انجام دهم؟</h3>
<ul>
    <li>به پنل مدیریت فایل ها در هاست خود مراجعه کرده و با تغییر نام پوشه مربوط به پلاگین یا قالب، آن را از کار بیاندازید تا بتوانید مجددا به پیشخوان مدیریتی وردپرس دسترسی یافته و نسبت به حذف و نصب مجدد نسخه قبلی از روی نسخه پشتیبان اقدام کنید. </li>
</ul>

<h3 class="zhk_updater_plugin_details_faq_question">چنانچه پس از به روز رسانی نیازمند راهنمایی باشم چه کار باید انجام دهم؟</h3>
<ul>
    <li>در صورتی که مدت پشتیبانی تضمین شده شما در ژاکت به پایان نرسیده باشد می توانید با مراجعه به حساب کاربری خود در سایت ژاکِت و ارسال تیکت پشتیبانی به فروشنده، نسبت به شرح مشکل و اخذ راهنمایی اقدام نمایید. </li>
</ul>

<h3 class="zhk_updater_plugin_details_faq_question">چنانچه مدت پشتیبانی تضمین شده در ژاکت به پایان رسیده باشد چه کار باید انجام دهم؟</h3>
<ul>
    <li>با مراجعه به بخش دانلودها در حساب کاربری خود در سایت ژاکت می توانید نسبت به تمدید پشتیبانی اقدام نمایید و پس از آن با ارسال تیکت پشتیبانی به فروشنده، نسبت به شرح مشکل و اخذ راهنمایی اقدام نمایید.</li>
</ul>

<h3 class="zhk_updater_plugin_details_faq_question">چطور به آموزش کامل نحوه استفاده از به روز رسان هوشمند ژاکت دسترسی داشته باشم؟</h3>
<ul>
    <li>کافیست از منوی به روز رسان هوشمند ژاکت در پیشخوان مدیریتی سایت وردپرسی خود روی گزینه آموزش استفاده کلیک کرده و در ادامه بخش آموزش استفاده را کلیک کنید.</li>
</ul></div> 
';

	foreach ( (array) $api->sections as $section_name => $content ) {
        if ( 'reviews' === $section_name && ( empty( $api->ratings ) || 0 === array_sum( (array) $api->ratings ) ) ) {
            continue;
        }

        $title = $plugins_section_titles[$section_name] ?? ucwords(str_replace('_', ' ', $section_name));

        $class       = ( $section_name === $section ) ? ' class="current"' : '';
        $href        = add_query_arg(
            array(
                'tab'     => $tab,
                'section' => $section_name,
            )
        );
        $href        = esc_url( $href );
        $san_section = esc_attr( $section_name );
        echo "\t<a name='$san_section' href='$href' $class>$title</a>\n";
    }

	echo "</div>\n";

	?>
<div id="<?php echo $_tab; ?>-content" class='<?php echo $_with_banner; ?>'>
    <div class="fyi">
        <ul>
            <?php if ( ! empty( $api->version ) ) { ?>
                <li><strong><?php _e( 'Version:' ); ?></strong> <?php echo $api->version; ?></li>
            <?php } if ( ! empty( $api->author ) ) { ?>
                <li><strong><?php _e( 'Author:' ); ?></strong> <?php echo links_add_target( $api->author, '_blank' ); ?></li>
            <?php } if ( ! empty( $api->last_updated ) ) { ?>
                <li><strong><?php _e( 'Last Updated:' ); ?></strong>
                    <?php
                    /* translators: %s: Human-readable time difference. */
                    printf( __( '%s ago' ), human_time_diff( strtotime( $api->last_updated ) ) );
                    ?>
                </li>
            <?php } if ( ! empty( $api->total_sales ) ) { ?>
                <li><strong><?php _e( 'total sales:','zhaket-updater' ); ?></strong> <?php echo $api->total_sales; ?></li>
            <?php } if ( ! empty( $api->requires ) ) { ?>
                <li>
                    <strong><?php _e( 'Requires WordPress Version:' ); ?></strong>
                    <?php
                    /* translators: %s: Version number. */
                    printf( __( '%s or higher' ), $api->requires );
                    ?>
                </li>
            <?php } if ( ! empty( $api->tested ) ) { ?>
                <li><strong><?php _e( 'Compatible up to:' ); ?></strong> <?php echo $api->tested; ?></li>
            <?php } if ( ! empty( $api->requires_php ) ) { ?>
                <li>
                    <strong><?php _e( 'Requires PHP Version:' ); ?></strong>
                    <?php
                    /* translators: %s: Version number. */
                    printf( __( '%s or higher' ), $api->requires_php );
                    ?>
                </li>
            <?php } if ( isset( $api->active_installs ) ) { ?>
                <li><strong><?php _e( 'Active Installations:' ); ?></strong>
                    <?php
                    if ( $api->active_installs >= 1000000 ) {
                        $active_installs_millions = floor( $api->active_installs / 1000000 );
                        printf(
                        /* translators: %s: Number of millions. */
                            _nx( '%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations' ),
                            number_format_i18n( $active_installs_millions )
                        );
                    } elseif ( 0 == $api->active_installs ) {
                        _ex( 'Less Than 10', 'Active plugin installations' );
                    } else {
                        echo number_format_i18n( $api->active_installs ) . '+';
                    }
                    ?>
                </li>
            <?php } if ( ! empty( $api->pluginuri ) ) { ?>
                <li><a target="_blank" href="<?php echo esc_url( $api->pluginuri ); ?>"><?php _e( 'Plugin Homepage &#187;' ); ?></a></li>
            <?php }  if ( ! empty( $api->demo_url ) && empty($api->is_child) ) { ?>
                <li>
                    <div class="zhk_updater_plugin_details_button">
                        <a target="_blank" href="<?php echo esc_url( $api->demo_url ); ?>"><?php _e( 'view demo','zhaket-updater' ); ?></a>
                    </div>
                </li>
            <?php } if ( ! empty( $api->donate_link ) && empty( $api->contributors ) ) { ?>
                <li><a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;' ); ?></a></li>
            <?php } ?>
        </ul>
        <?php if ( ! empty( $api->rating ) ) { ?>
            <h3><?php _e( 'Average Rating in Zhaket','zhaket-updater' ); ?></h3>
            <?php
            wp_star_rating(
                array(
                    'rating' => $api->rating,
                    'type'   => 'percent',
                    'number' => $api->num_ratings,
                )
            );
            ?>
            <p aria-hidden="true" class="fyi-description">
                <?php
                printf(
                /* translators: %s: Number of ratings. */
                    _n( '(based on %s rating)', '(based on %s ratings)', $api->num_ratings ),
                    number_format_i18n( $api->num_ratings )
                );
                ?>
            </p>
            <?php
        }

        if ( ! empty( $api->ratings ) && array_sum( (array) $api->ratings ) > 0 ) {
            ?>
            <h3><?php _e( 'Reviews' ); ?></h3>
            <p class="fyi-description"><?php _e( 'Read all reviews on WordPress.org or write your own!' ); ?></p>
            <?php
            foreach ( $api->ratings as $key => $ratecount ) {
                // Avoid div-by-zero.
                $_rating    = $api->num_ratings ? ( $ratecount / $api->num_ratings ) : 0;
                $aria_label = esc_attr(
                    sprintf(
                    /* translators: 1: Number of stars (used to determine singular/plural), 2: Number of reviews. */
                        _n(
                            'Reviews with %1$d star: %2$s. Opens in a new tab.',
                            'Reviews with %1$d stars: %2$s. Opens in a new tab.',
                            $key
                        ),
                        $key,
                        number_format_i18n( $ratecount )
                    )
                );
                ?>
                <div class="counter-container">
						<span class="counter-label">
							<?php
                            printf(
                                '<a href="%s" target="_blank" aria-label="%s">%s</a>',
                                "https://wordpress.org/support/plugin/{$api->slug}/reviews/?filter={$key}",
                                $aria_label,
                                /* translators: %s: Number of stars. */
                                sprintf( _n( '%d star', '%d stars', $key ), $key )
                            );
                            ?>
						</span>
                    <span class="counter-back">
							<span class="counter-bar" style="width: <?php echo 92 * $_rating; ?>px;"></span>
						</span>
                    <span class="counter-count" aria-hidden="true"><?php echo number_format_i18n( $ratecount ); ?></span>
                </div>
                <?php
            }
        }
        if ( ! empty( $api->contributors ) ) {
            ?>
            <h3><?php _e( 'Contributors' ); ?></h3>
            <ul class="contributors">
                <?php
                foreach ( (array) $api->contributors as $contrib_username => $contrib_details ) {
                    $contrib_name = $contrib_details['display_name'];
                    if ( ! $contrib_name ) {
                        $contrib_name = $contrib_username;
                    }
                    $contrib_name = esc_html( $contrib_name );

                    $contrib_profile = esc_url( $contrib_details['profile'] );
                    $contrib_avatar  = esc_url( add_query_arg( 's', '36', $contrib_details['avatar'] ) );

                    echo "<li><a href='{$contrib_profile}' target='_blank'><img src='{$contrib_avatar}' width='18' height='18' alt='' />{$contrib_name}</a></li>";
                }
                ?>
            </ul>
            <?php if ( ! empty( $api->donate_link ) ) { ?>
                <a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;' ); ?></a>
            <?php } ?>
        <?php } ?>
    </div>
    <div id="section-holder">
<?php
	$requires_php = $api->requires_php ?? null;
	$requires_wp  = $api->requires ?? null;

	$compatible_php = is_php_version_compatible( $requires_php );
	$compatible_wp  = is_wp_version_compatible( $requires_wp );
	$tested_wp      = ( empty( $api->tested ) || version_compare( get_bloginfo( 'version' ), $api->tested, '<=' ) );

	if ( ! $compatible_php ) {
        echo '<div class="notice notice-error notice-alt"><p>';
        _e( '<strong>Error:</strong> This plugin <strong>requires a newer version of PHP</strong>.' );
        if ( current_user_can( 'update_php' ) ) {
            printf(
            /* translators: %s: URL to Update PHP page. */
                ' ' . __( '<a href="%s" target="_blank">Click here to learn more about updating PHP</a>.' ),
                esc_url( wp_get_update_php_url() )
            );
            wp_update_php_annotation( '</p><p><em>', '</em>' );
        } else {
            echo '</p>';
        }
        echo '</div>';
    }

	if ( ! $tested_wp ) {
        echo '<div class="notice notice-warning notice-alt"><p>';
        _e( '<strong>Warning:</strong> This plugin <strong>has not been tested</strong> with your current version of WordPress.' );
        echo '</p></div>';
    } elseif ( ! $compatible_wp ) {
        echo '<div class="notice notice-error notice-alt"><p>';
        _e( '<strong>Error:</strong> This plugin <strong>requires a newer version of WordPress</strong>.' );
        if ( current_user_can( 'update_core' ) ) {
            printf(
            /* translators: %s: URL to WordPress Updates screen. */
                ' ' . __( '<a href="%s" target="_parent">Click here to update WordPress</a>.' ),
                self_admin_url( 'update-core.php' )
            );
        }
        echo '</p></div>';
    }

	foreach ( (array) $api->sections as $section_name => $content ) {
        $content = links_add_base_url( $content, 'https://wordpress.org/plugins/' . $api->slug . '/' );
        $content = links_add_target( $content, '_blank' );

        $san_section = esc_attr( $section_name );

        $display = ( $section_name === $section ) ? 'block' : 'none';

        echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
        echo $content;
        if ($section_name=='description' ){
            echo '<br><br>';
            echo '<div class="zhk_updater_plugin_details_rtl_correction">';
            if (! empty( $api->is_child )) {
                esc_html_e(
                    'This product is child of other product. To view main product , click on below link',
                    'zhaket-updater'
                );
            }
        echo '<div class="zhk_updater_plugin_details_button"> <a target="_blank" href="'.esc_url( $api->homepage ).'">'.__( 'View in zhaket','zhaket-updater'  ).'</a> </div></div>';
        }
        echo "\t</div>\n";
    }
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n"; // #plugin-information-scrollable
	echo "<div id='$tab-footer'>\n";
	if ( ! empty( $api->download_link ) && ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
        $status = install_plugin_install_status( $api );
        switch ( $status['status'] ) {
            case 'install':
                if ( $status['url'] ) {
                    if ( $compatible_php && $compatible_wp ) {
                        echo '<a data-slug="' . esc_attr( $api->slug ) . '" id="plugin_install_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Now' ) . '</a>';
                    } else {
                        printf(
                            '<button type="button" class="button button-primary button-disabled right" disabled="disabled">%s</button>',
                            _x( 'Cannot Install', 'plugin' )
                        );
                    }
                }
                break;
            case 'update_available':
                if ( $status['url'] ) {
                    if ( $compatible_php ) {
                        echo '<a data-slug="' . esc_attr( $api->slug ) . '" data-plugin="' . esc_attr( $status['file'] ) . '" id="plugin_update_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Update Now' ) . '</a>';
                    } else {
                        printf(
                            '<button type="button" class="button button-primary button-disabled right" disabled="disabled">%s</button>',
                            _x( 'Cannot Update', 'plugin' )
                        );
                    }
                }
                break;
            case 'newer_installed':
                /* translators: %s: Plugin version. */
                echo '<a class="button button-primary right disabled">' . sprintf( __( 'Newer Version (%s) Installed' ), $status['version'] ) . '</a>';
                break;
            case 'latest_installed':
                echo '<a class="button button-primary right disabled">' . __( 'Latest Version Installed' ) . '</a>';
                break;
        }
    }
	echo "</div>\n";

	iframe_footer();
	exit;
