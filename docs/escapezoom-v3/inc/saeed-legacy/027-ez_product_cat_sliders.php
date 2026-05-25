<?php
/**
 * ez_product_cat_sliders
 *
 * توابع: ez_product_cat_sliders
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1245-2172)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_product_cat_sliders () {

    $current_archive_obj = get_queried_object();
    $term_id = $current_archive_obj->term_id;

    $city_name = get_term( $current_archive_obj )->name;

    $is_father_page = false;
    if ( $current_archive_obj->parent == 0 ) {
        $product_type   = $city_name;
        $is_father_page    = true;

    } else
        $product_type = get_term( $current_archive_obj->parent )->name;

    /*===============================================================*/
    // تبلیغات

    $posts_per_page = 100;
    $params = [
        'city_id' => [$term_id],
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => true,
        "format"        => 'html_slider',
        'is_mobile'     => wp_is_mobile(),
        'sort_type'     => 'popular',
        'only_ads'      => true,
        'show_more'     => 0,
    ];
    $products = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) )->products; ?>

    <script>
        jQuery(document).ready(function ($) {

            if ( location.hostname == 'escapezoom.co' ) {
                $('.topescaperoom').each(function(i, obj) {
                    $res = $(obj).html().replace(/escapezoom.ir/g, "escapezoom.co");
                    $(obj).html($res);
                });
            }

            var swiper = new Swiper("#swiper_adsx", {
                slidesPerView: "auto",
                spaceBetween: 10,
                loop: false,
                paginationClickable: true,
                freeMode: true,
                pagination: {
                    el: "#adsx_wrapper .nav1",
                    clickable: true,
                }
            });

            var posts_per_page  = <?php echo $posts_per_page ?>;
            var sort_type   = 'popular';

            var city        = $('#product_list_filter_city_list').val() == -1 ? -1 : $('#product_list_filter_city_list').val().split(',');
            var is_mobile   = $('#product_list_is_mobile').val();

            var params = {
                'city_id' : city,
            };

            $.ajax({
                type: 'POST',
                url: (location.hostname === 'dev.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                data: {
                    "async": false,
                    "type": "sort_products_get",
                    "data": {
                        "params"        : params,
                        'image_type'    : 'url',
                        "limit"         : posts_per_page,
                        "page"          : 1,
                        "format"        : 'html_slider',
                        "is_mobile"     : is_mobile,
                        'sort_type'     : sort_type,
                        'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                        'only_ads'      : true,
                        'show_more'     : 0,
                    }
                },
                dataType: "json",
                success: function(data) {
                    setTimeout(function() {
                        $('#adsx_wrapper .swiper-wrapper').empty();
                        $(data.products).appendTo('#adsx_wrapper .swiper-wrapper');
                        $('#adsx_wrapper .swiper-wrapper').css('display', 'flex');
                    }, 1);
                },
            });
        });
    </script>

    <?php
    if ( !empty( $products ) ) : ?>

        <div id="adsx_wrapper" class="elite_rooms_wrapper" style="position: relative;z-index: 2; background: #f3f3f3; padding: 10px; border: 1px solid #e3e2e2; border-radius: 8px; margin: 20px 0;">
            <section class="slider-single-content" style="margin-bottom: 10px!important;">
                <div class="slider-single-content__title slidertitleh2">
                    <div class=" swiper-pagination nav1"></div>
                    <?php if (wp_is_mobile()) : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های ویژه <?php echo $city_name ?></a>
                        </h2>
                    <?php else : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های ویژه <?php echo $city_name ?></a>
                        </h2>
                    <?php endif; ?>
                </div>
            </section>

            <div dir="rtl" id="swiper_adsx" data-id="1" class="swiper topescaperoom" style="align-items: stretch;display: flex;">
                <div class="swiper-wrapper" style="padding: 5px 0 !important;">
                    <?php echo $products; ?>
                </div>
            </div>
        </div>

    <?php
    endif;

    /*===============================================================*/
    // تخفیف ویژه

    if ( get_current_user_id() == 3325 || 1 ) :

        $posts_per_page = 100;

        if ( $is_father_page )
            $params = [
                'product_type' => $product_type,
            ];
        else
            $params = [
                'city_id' => [$term_id],
            ];

        $args = [
            'params'        => $params,
            'image_type'    => 'url',
            'limit'         => $posts_per_page,
            'page'          => 1,
            'max_num_pages' => true,
            "format"        => 'html_slider',
            'is_mobile'     => wp_is_mobile(),
            'sort_type'     => 'popular',
            'show_more'     => 0,
            'only_events'   => true,
            'event_type'    => "discount",
            'random'        => true,
            'badge_ads'     => false,
        ];
        $products = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) )->products;

        if ( !empty( $products ) ) :  ?>
            <div id="special_discount_wrapper" class="elite_rooms_wrapper" style="position: relative;z-index: 2; background: #f3f3f3; padding: 10px; border: 1px solid #e3e2e2; border-radius: 8px; margin: 20px 0; min-height: 400px;">
                <div style="margin-bottom: 30px;display: flex;align-items: center;justify-content: space-between;" class="event-products-header">
                    <h2 style="font-size: 22px;display: flex;align-items: center; gap:4px;">
                        <span style="filter: drop-shadow(0px 1px 2px rgba(0, 0, 0, 0.25));transform: rotate(180deg); position: relative;width: 41px;height: 41px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 32 32" fill="none" style="position: absolute;left: 0;top: 0;transform:translate(6px,8px);z-index: 1;">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4221 10.7605C26.6894 10.5553 26.863 10.2506 26.9047 9.91351C26.9464 9.57639 26.8527 9.23442 26.6443 8.96281L24.4212 6.06587C24.2105 5.80088 23.9059 5.628 23.5729 5.58446C23.2399 5.54092 22.9051 5.63021 22.6407 5.83309C22.3764 6.03598 22.2035 6.33622 22.1594 6.66917C22.1153 7.00211 22.2035 7.34111 22.405 7.61316L23.8422 9.48602L22.2937 10.6744L20.8565 8.80152C20.6458 8.53653 20.3412 8.36365 20.0081 8.32011C19.6751 8.27657 19.3404 8.36586 19.076 8.56874C18.8116 8.77162 18.6388 9.07187 18.5947 9.40482C18.5506 9.73776 18.6388 10.0768 18.8402 10.3488L20.2775 12.2217L12.4009 18.2647C11.4146 17.4875 10.1852 17.0939 8.94331 17.1577C7.70138 17.2215 6.53213 17.7383 5.65473 18.6112C4.77733 19.4842 4.25203 20.6533 4.17729 21.8995C4.10255 23.1457 4.48351 24.3834 5.24874 25.3806C6.01398 26.3777 7.11097 27.0659 8.33407 27.3161C9.55717 27.5663 10.8224 27.3614 11.8926 26.7397C12.9628 26.118 13.7645 25.1223 14.1475 23.9391C14.5304 22.756 14.4682 21.4666 13.9727 20.3128L26.4221 10.7605ZM8.96694 24.8287C9.63992 24.9226 10.3184 24.7448 10.8533 24.3344C11.3881 23.9239 11.7354 23.3145 11.8187 22.6401C11.9021 21.9658 11.7147 21.2817 11.2978 20.7384C10.8809 20.1951 10.2686 19.8371 9.59559 19.7432C8.92261 19.6493 8.24408 19.8271 7.70927 20.2375C7.17445 20.6479 6.82716 21.2573 6.7438 21.9317C6.66044 22.6061 6.84783 23.2902 7.26475 23.8334C7.68167 24.3767 8.29396 24.7347 8.96694 24.8287Z" fill="#ffffff"/>
                              <circle cx="9.8806" cy="6.0847" r="3.08665" fill="#ffffff"/>
                              <circle cx="26.9128" cy="24.9719" r="3.08665" fill="#ffffff"/>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="41" height="41" viewBox="0 0 41 41" fill="none" style="position: absolute;left: 0;right: 0;bottom: 0;top: 0;">
                              <path d="M39.9221 24.4033C38.5688 22.0621 38.5698 19.1755 39.9241 16.8349C40.1873 16.3796 40.2589 15.8384 40.1231 15.3304C39.9874 14.8223 39.6554 14.389 39.2002 14.1256C36.8624 12.7744 35.412 10.2729 35.412 7.57269C35.412 7.04668 35.2031 6.54221 34.8311 6.17026C34.4592 5.79831 33.9547 5.58935 33.4287 5.58935C30.7297 5.58935 28.2299 4.14009 26.8797 1.80317C26.6168 1.34766 26.1837 1.01521 25.6758 0.878932C25.4241 0.811502 25.1617 0.794312 24.9034 0.828342C24.6451 0.862373 24.396 0.946958 24.1704 1.07727C21.8268 2.43191 18.9372 2.43084 16.594 1.07528C16.1385 0.812288 15.5972 0.741017 15.0891 0.877149C14.581 1.01328 14.1478 1.34567 13.8848 1.80118C12.5341 4.13795 10.0328 5.58935 7.33381 5.58935C6.80779 5.58935 6.30332 5.79831 5.93137 6.17026C5.55942 6.54221 5.35046 7.04668 5.35046 7.57269C5.35046 10.2717 3.89903 12.773 1.56227 14.1236C1.33663 14.254 1.13889 14.4276 0.980369 14.6345C0.821848 14.8414 0.705651 15.0774 0.63842 15.3292C0.571188 15.581 0.554242 15.8436 0.588547 16.1019C0.622853 16.3603 0.70774 16.6093 0.838354 16.8349C2.19289 19.1758 2.19289 22.0623 0.838354 24.4033C0.57653 24.859 0.505493 25.3997 0.64073 25.9076C0.775967 26.4155 1.10651 26.8493 1.56029 27.1145C3.89705 28.4652 5.34848 30.9664 5.34848 33.6654C5.34848 34.1915 5.55744 34.6959 5.92939 35.0679C6.30134 35.4398 6.80581 35.6488 7.33183 35.6488C10.0321 35.6488 12.5335 37.0991 13.8848 39.437C14.0604 39.7371 14.3111 39.9864 14.6122 40.1603C14.9134 40.3341 15.2546 40.4266 15.6024 40.4286C15.9475 40.4286 16.2906 40.3374 16.596 40.1609C18.9372 38.8062 21.8267 38.8073 24.1684 40.1609C24.6237 40.424 25.1649 40.4956 25.6729 40.3598C26.181 40.2241 26.6143 39.8921 26.8777 39.437C28.2274 37.0998 30.7278 35.6488 33.4267 35.6488C33.9527 35.6488 34.4572 35.4398 34.8291 35.0679C35.2011 34.6959 35.41 34.1915 35.41 33.6654C35.41 30.9664 36.8615 28.4652 39.1982 27.1145C39.4239 26.9841 39.6216 26.8105 39.7801 26.6036C39.9386 26.3968 40.0548 26.1607 40.1221 25.9089C40.1893 25.6571 40.2063 25.3945 40.172 25.1362C40.1376 24.8778 40.0528 24.6288 39.9221 24.4033Z" fill="url(#paint0_linear_279_28)"/>
                              <defs>
                                <linearGradient id="paint0_linear_279_28" x1="30" y1="22" x2="46.5" y2="67.5" gradientUnits="userSpaceOnUse">
                                  <stop stop-color="#F21543"/>
                                  <stop offset="1" stop-color="#8C0C27"/>
                                </linearGradient>
                              </defs>
                            </svg>
                        </span>
                        <span style="color: #242424;font-weight: bold;font-size: 17px;">پیشنهادهای داغ هفته <?php echo $city_name ?></span>
                    </h2>
                    <div style="display: flex; align-items: center;justify-content: space-between; gap: 4px; direction: ltr; margin-left: 10px" class="timestamp-box">
                        <div class="timer-box-container days-expire-count">
                            <div class="box-time-character">0</div>
                            <?php
                            $today = intval(date('w'));
                            function convertNum($num){
                                if($num >=1 && $num <= 7){
                                    return 7 - $num;
                                }
                                return null;
                            }
                            ?>
                            <div class="box-time-character"><?= convertNum($today); ?></div>
                        </div>
                        <span>:</span>
                        <div class="timer-box-container hours-expire-count">
                            <div class="box-time-character">0</div>
                            <div class="box-time-character">0</div>
                        </div>
                        <span>:</span>
                        <div class="timer-box-container minutes-expire-count">
                            <div class="box-time-character">0</div>
                            <div class="box-time-character">0</div>
                        </div>
                        <span>:</span>
                        <div class="timer-box-container seconds-expire-count">
                            <div class="box-time-character">0</div>
                            <div class="box-time-character">0</div>
                        </div>
                    </div>
                </div>

                <div dir="rtl" id="swiper_special_discount" data-id="1" class="swiper topescaperoom" style="align-items: stretch;display: flex;">
                    <div class="swiper-wrapper" style="padding: 5px 0 !important;">
                        <?php echo $products; ?>
                    </div>
                </div>
            </div>
        <?php
        endif; ?>

        <style>
            .timer-box-container {
                display: flex;
                gap: 2px;
            }
            .box-time-character {
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 20px;
                font-weight: 900;
                border-radius: 6px;
                text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
                width: 30px;
                height: 30px;
                background: linear-gradient(161deg, #F21543 65.74%, #8C0C27 167.69%);
                box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
            }
            @media screen and (max-width: 768px) {
                .event-products-header {
                    flex-direction: column;
                    gap: 10px;
                    align-items: stretch;
                }
            }
        </style>

        <script>
            jQuery(document).ready(function ($) {

                if ( location.hostname == 'escapezoom.co' ) {
                    $('.topescaperoom').each(function(i, obj) {
                        $res = $(obj).html().replace(/escapezoom.ir/g, "escapezoom.co");
                        $(obj).html($res);
                    });
                }

                new Swiper("#swiper_special_discount", {
                    slidesPerView: "auto",
                    spaceBetween: 10,
                    loop: false,
                    paginationClickable: true,
                    freeMode: true,
                    pagination: {
                        el: "#special_discount_wrapper .nav1",
                        clickable: true,
                    }
                });

                var city        = $('#product_list_filter_city_list').val() == -1 ? -1 : $('#product_list_filter_city_list').val().split(',');
                var is_mobile   = $('#product_list_is_mobile').val();

                var params = {
                    'city_id' : city,
                };

                $.ajax({
                    type: 'POST',
                    url: (location.hostname === 'dev.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                    data: {
                        "async": false,
                        "type": "sort_products_get",
                        "data": {
                            "params"        : params,
                            'image_type'    : 'url',
                            "limit"         : 100,
                            "page"          : 1,
                            "format"        : 'html_slider',
                            "is_mobile"     : is_mobile,
                            'sort_type'     : 'popular',
                            'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                            'only_events'   : 1,
                            'event_type'    : "discount",
                            'random'        : 1,
                            'badge_ads'     : 0,
                        }
                    },
                    dataType: "json",
                    success: function(data) {
                        setTimeout(function() {
                            $('#special_discount_wrapper .swiper-wrapper').empty();
                            $(data.products).appendTo('#special_discount_wrapper .swiper-wrapper');
                            $('#special_discount_wrapper .swiper-wrapper').css('display', 'flex');
                        }, 1);
                    },
                });
            });

            let todayNum = `<?php echo date('w'); ?>`
            function convertNumber(num) {
                if (num >= 1 && num <= 7) {
                    return 7 - num;
                }
                return null;
            }

            function getValue(d, timePart) {
                var val = 0
                switch (timePart) {
                    case "hours":
                        val = 23 - parseInt(d.getHours());
                        break;
                    case "minutes":
                        val = 59 - parseInt(d.getMinutes())
                        break;
                    case "seconds":
                        val = 59 - parseInt(d.getSeconds())
                        break;
                    case "milliseconds":
                        val = 999 - parseInt(d.getMilliseconds())
                        break;
                    default:
                        break;
                }
                return val.toString().padStart(2, '0');
            }
            function init() {
                setInterval(function () {
                    let d = new Date();
                    let h = getValue(d, "hours");
                    let m = getValue(d, "minutes");
                    let s = getValue(d, "seconds");
                    let ms = getValue(d, "milliseconds");
                    $('.timestamp-box').each(function (){
                        let hourString = String(h).padStart(2, '0');
                        let minuteString = String(m).padStart(2, '0');
                        let secondString = String(s).padStart(2, '0');
                        let milliSecondString = String(ms).padStart(2, '0');
                        $(this).find('.hours-expire-count .box-time-character').eq(0).text(hourString.charAt(0)); // دهگان ساعت
                        $(this).find('.hours-expire-count .box-time-character').eq(1).text(hourString.charAt(1)); // یکان ساعت
                        $(this).find('.minutes-expire-count .box-time-character').eq(0).text(minuteString.charAt(0)); // دهگان دقیقه
                        $(this).find('.minutes-expire-count .box-time-character').eq(1).text(minuteString.charAt(1)); // یکان دقیقه
                        $(this).find('.seconds-expire-count .box-time-character').eq(0).text(secondString.charAt(0)); // دهگان روز
                        $(this).find('.seconds-expire-count .box-time-character').eq(1).text(secondString.charAt(1)); // یکان روز
                        $(this).find('.milliseconds-expire-count .box-time-character').eq(0).text(milliSecondString.charAt(0)); // هزارگان روز
                        $(this).find('.milliseconds-expire-count .box-time-character').eq(1).text(milliSecondString.charAt(1)); // دهگان روز
                        $(this).find('.milliseconds-expire-count .box-time-character').eq(2).text(milliSecondString.charAt(2)); // یکان روز
                    })
                }, 1);
            }
            init()
        </script>

    <?php
    endif;

    /*===============================================================*/
    // زوم کلاب

    $params = [
        'city_id'   => [$term_id],
        'monopoly'  => 1,
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => true,
        "format"        => 'html_slider',
        'is_mobile'     => wp_is_mobile(),
        'sort_type'     => 'popular',
        'exclude_ads'   => false,
        'unpin_ads'     => true,
        'badge_ads'     => false,
        'show_more'     => 0,
        'random'        => true
    ];
    $products = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) )->products; ?>

    <script>
        jQuery(document).ready(function ($) {

            if ( location.hostname == 'escapezoom.co' ) {
                $('.topescaperoom').each(function(i, obj) {
                    $res = $(obj).html().replace(/escapezoom.ir/g, "escapezoom.co");
                    $(obj).html($res);
                });
            }

            var swiper = new Swiper("#swiper_monopoly", {
                slidesPerView: "auto",
                spaceBetween: 10,
                loop: false,
                paginationClickable: true,
                freeMode: true,
                pagination: {
                    el: "#monopoly_wrapper .nav1",
                    clickable: true,
                }
            });

            var posts_per_page = <?php echo $posts_per_page ?>;

            var is_mobile   = $('#product_list_is_mobile').val();

            var params = {
                'city_id' : city,
                'monopoly': 1
            };

            $.ajax({
                type: 'POST',
                url: (location.hostname === 'dev.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "params"        : params,
                        'image_type'    : 'url',
                        "limit"         : posts_per_page,
                        "page"          : 1,
                        "format"        : 'html_slider',
                        "is_mobile"     : is_mobile,
                        'sort_type'     : 'popular',
                        'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                        'exclude_ads'   : 0,
                        'unpin_ads'     : 1,
                        'badge_ads'     : 0,
                        'show_more'     : 0,
                        'random'        : 1,
                    }
                },
                dataType: "json",
                success: function(data) {

                    setTimeout(function() {
                        $('#monopoly_wrapper .swiper-wrapper').empty();
                        $(data.products).appendTo('#monopoly_wrapper .swiper-wrapper');
                        $('#monopoly_wrapper .swiper-wrapper').css('display', 'flex');
                    }, 1);

                },
            });
        });
    </script>

    <?php
    if ( !empty( $products ) ) : ?>

        <div id="monopoly_wrapper" class="elite_rooms_wrapper" style="position: relative;z-index: 2;">
            <section class="slider-single-content">
                <div class="slider-single-content__title slidertitleh2">
                    <div class=" swiper-pagination nav1"></div>
                    <?php if (wp_is_mobile()) : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"> زوم کلاب <?php echo $city_name ?></a>
                        </h2>
                    <?php else : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"> زوم کلاب <?php echo $city_name ?></a>
                        </h2>
                    <?php endif; ?>
                </div>
            </section>
            <div dir="rtl" id="swiper_monopoly" data-id="1" class="swiper topescaperoom" style="align-items: stretch;display: flex;">
                <div class="swiper-wrapper">
                    <?php echo $products; ?>
                </div>
            </div>
        </div>

    <?php
    endif;

    /*===============================================================*/
    // ترسناک

    if ( $product_type != 'اتاق فرار' )
        return;

    $params = [
        'city_id'   => [$term_id],
        'tag'       => [124],
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => true,
        "format"        => 'html_slider',
        'is_mobile'     => wp_is_mobile(),
        'sort_type'     => 'popular',
        'exclude_ads'   => false,
        'unpin_ads'     => true,
        'badge_ads'     => false,
        'show_more'     => 0,
    ];
    $products = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) )->products; ?>

    <script>
        jQuery(document).ready(function ($) {

            if ( location.hostname == 'escapezoom.co' ) {
                $('.topescaperoom').each(function(i, obj) {
                    $res = $(obj).html().replace(/escapezoom.ir/g, "escapezoom.co");
                    $(obj).html($res);
                });
            }

            setTimeout(function() {
                $('#swiper_horror .swiper-wrapper').attr( "style", "transform: translate3d(0px, 0px, 0px); transition-duration: 0ms;" );
                $('#swiper_horror .swiper-wrapper .swiper-slide').removeClass("swiper-slide-active");
            }, 50);

            var swiper = new Swiper("#swiper_horror", {
                slidesPerView: "auto",
                spaceBetween: 10,
                loop: false,
                paginationClickable: true,
                freeMode: true,
                pagination: {
                    el: "#horror_wrapper .nav1",
                    clickable: true,
                }
            });

            var posts_per_page = <?php echo $posts_per_page ?>;

            setTimeout(function() {
                $('#horror_wrapper #product_popular_sort_btn').trigger('click');
            }, 1);

            $('body').on('click', '#horror_wrapper .shop-page-header-sort .orderby-item', function () {

                var $this       = $(this);
                var sort_type   = $this.data('id');

                $('#horror_wrapper .shop-page-header-sort .orderby-item').removeClass('is-active');
                $('#product_list_cur_page_num').val(2);
                $('#product_list_data_sort_type').val(sort_type);
                $this.addClass('is-active');

                $('#horror_wrapper .swiper-wrapper').empty();

                var city        = $('#product_list_filter_city_list').val() == -1 ? -1 : $('#product_list_filter_city_list').val().split(',');
                var is_mobile   = $('#product_list_is_mobile').val();

                var params = {
                    'city_id' : city,
                    'tag'     : '124'.split(',')
                };

                $.ajax({
                    type: 'POST',
                    url: (location.hostname === 'dev.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                    data: {
                        "type": "sort_products_get",
                        "data": {
                            "params"        : params,
                            'image_type'    : 'url',
                            "limit"         : posts_per_page,
                            "page"          : 1,
                            "format"        : 'html_slider',
                            "is_mobile"     : is_mobile,
                            'sort_type'     : sort_type,
                            'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                            'exclude_ads'   : 0,
                            'unpin_ads'     : 1,
                            'badge_ads'     : 0,
                            'show_more'     : 0,
                        }
                    },
                    dataType: "json",
                    success: function(data) {
                        $('#horror_wrapper .swiper-wrapper').css('display', 'none');

                        $(data.products).appendTo('#horror_wrapper .swiper-wrapper');

                        setTimeout(function() {
                            $('#horror_wrapper .swiper-wrapper').css('display', 'flex');
                        }, 25);

                    },
                });
            });
        });
    </script>

    <?php
    if ( !empty( $products ) ) : ?>

        <div id="horror_wrapper" class="elite_rooms_wrapper" style="position: relative;z-index: 2;">

            <section class="slider-single-content">
                <div class="slider-single-content__title slidertitleh2">
                    <div class=" swiper-pagination nav1"></div>
                    <?php if (wp_is_mobile()) : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های ترسناک <?php echo $city_name ?></a>
                        </h2>
                    <?php else : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های ترسناک <?php echo $city_name ?></a>
                        </h2>
                    <?php endif; ?>
                </div>
            </section>

            <div class="shop-page-header">
                <section id="zardkooh-woo-ajax-navigation-sort-by" class="zardkooh-shop-sort-by yith-wcan-sort-by">

                    <ul class="shop-page-header-sort" style="max-width: 414px; float: left; opacity: 1 !important;">
                        <li><a id="product_popular_sort_btn" data-id="popular" class="orderby-item is-active" href="javascript:" data-id="default" >محبوب ترین</a></li>
                        <li><a id="product_topsale_sort_btn" data-id="topsale" class="orderby-item" href="javascript:" >پرفروش ترین</a></li>
                        <li><a id="product_recent_sort_btn" data-id="recent" class="orderby-item" href="javascript:" data-id="date" d>جدیدترین</a></li>
                    </ul>

                </section>
            </div>

            <div dir="rtl" id="swiper_horror" data-id="1" class="swiper topescaperoom" style="align-items: stretch;display: flex;">
                <div class="swiper-wrapper">
                    <?php echo $products; ?>
                </div>
            </div>
        </div>

    <?php
    endif;

    /*===============================================================*/
    // غیر ترسناک

    $params = [
        'city_id'   => [$term_id],
        'tag'       => -124,
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => true,
        "format"        => 'html_slider',
        'is_mobile'     => wp_is_mobile(),
        'sort_type'     => 'popular',
        'exclude_ads'   => false,
        'unpin_ads'     => true,
        'badge_ads'     => false,
        'show_more'     => 0,
    ];
    $products = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) )->products; ?>

    <script>
        jQuery(document).ready(function ($) {

            if ( location.hostname == 'escapezoom.co' ) {
                $('.topescaperoom').each(function(i, obj) {
                    $res = $(obj).html().replace(/escapezoom.ir/g, "escapezoom.co");
                    $(obj).html($res);
                });
            }

            var swiper = new Swiper("#swiper_non_horror", {
                slidesPerView: "auto",
                spaceBetween: 10,
                loop: false,
                paginationClickable: true,
                freeMode: true,
                pagination: {
                    el: "#non_horror_wrapper .nav1",
                    clickable: true,
                }
            });

            var posts_per_page  = <?php echo $posts_per_page ?>;

            setTimeout(function() {
                $('#non_horror_wrapper #product_popular_sort_btn').trigger('click');
            }, 1);

            $('body').on('click', '#non_horror_wrapper .shop-page-header-sort .orderby-item', function () {

                var $this       = $(this);
                var sort_type   = $this.data('id');

                $('#non_horror_wrapper .shop-page-header-sort .orderby-item').removeClass('is-active');
                $('#product_list_cur_page_num').val(2);
                $('#product_list_data_sort_type').val(sort_type);
                $this.addClass('is-active');

                $('#non_horror_wrapper .swiper-wrapper').empty();

                var city        = $('#product_list_filter_city_list').val() == -1 ? -1 : $('#product_list_filter_city_list').val().split(',');
                var is_mobile   = $('#product_list_is_mobile').val();

                var params = {
                    'city_id' : city,
                    'tag'     : '-124'
                };

                $.ajax({
                    type: 'POST',
                    url: (location.hostname === 'dev.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                    data: {
                        "type": "sort_products_get",
                        "data": {
                            "params"        : params,
                            'image_type'    : 'url',
                            "limit"         : posts_per_page,
                            "page"          : 1,
                            "format"        : 'html_slider',
                            "is_mobile"     : is_mobile,
                            'sort_type'     : sort_type,
                            'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                            'exclude_ads'   : 0,
                            'unpin_ads'     : 1,
                            'badge_ads'     : 0,
                            'show_more'     : 0,
                        }
                    },
                    dataType: "json",
                    success: function(data) {
                        $('#non_horror_wrapper .swiper-wrapper').css('display', 'none');

                        $(data.products).appendTo('#non_horror_wrapper .swiper-wrapper');

                        setTimeout(function() {
                            $('#non_horror_wrapper .swiper-wrapper').css('display', 'flex');
                        }, 25);

                    },
                });
            });
        });
    </script>

    <?php
    if ( !empty( $products ) ) : ?>

        <div id="non_horror_wrapper" class="elite_rooms_wrapper" style="position: relative;z-index: 2;">

            <div class="shop-page-header">
                <section id="zardkooh-woo-ajax-navigation-sort-by" class="zardkooh-shop-sort-by yith-wcan-sort-by">

                    <ul class="shop-page-header-sort" style="max-width: 414px; float: left; opacity: 1 !important;">
                        <li><a id="product_popular_sort_btn" data-id="popular" class="orderby-item is-active" href="javascript:" data-id="default" >محبوب ترین</a></li>
                        <li><a id="product_topsale_sort_btn" data-id="topsale" class="orderby-item" href="javascript:" >پرفروش ترین</a></li>
                        <li><a id="product_recent_sort_btn" data-id="recent" class="orderby-item" href="javascript:" data-id="date" d>جدیدترین</a></li>
                    </ul>

                </section>
            </div>

            <section class="slider-single-content">
                <div class="slider-single-content__title slidertitleh2">
                    <div class=" swiper-pagination nav1"></div>
                    <?php if (wp_is_mobile()) : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های غیرترسناک و هیجانی <?php echo $city_name ?></a>
                        </h2>
                    <?php else : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های غیرترسناک و هیجانی <?php echo $city_name ?></a>
                        </h2>                <?php endif; ?>
                </div>
            </section>

            <div dir="rtl" id="swiper_non_horror" data-id="1" class="swiper topescaperoom" style="align-items: stretch;display: flex;">
                <div class="swiper-wrapper">
                    <?php echo $products; ?>
                </div>
            </div>
        </div>

    <?php
    endif;

    /*===============================================================*/
    // ترند

    $posts_per_page = 100;
    $params = [
        'city_id' => [$term_id],
    ];

    $args = [
        'params'        => $params,
        'image_type'        => 'url',
        'limit'             => $posts_per_page,
        'page'              => 1,
        'max_num_pages'     => true,
        "format"            => 'html_slider',
        'is_mobile'         => wp_is_mobile(),
        'sort_type'         => 'trend',
        'only_ads'          => false,
        'show_more'         => 0,
        'badge_ads'         => false,
        'random'            => true
    ];
    $products = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) )->products; ?>

    <script>
        jQuery(document).ready(function ($) {

            if ( location.hostname == 'escapezoom.co' ) {
                $('.topescaperoom').each(function(i, obj) {
                    $res = $(obj).html().replace(/escapezoom.ir/g, "escapezoom.co");
                    $(obj).html($res);
                });
            }

            var swiper = new Swiper("#swiper_adsx", {
                slidesPerView: "auto",
                spaceBetween: 10,
                loop: false,
                paginationClickable: true,
                freeMode: true,
                pagination: {
                    el: "#trends_wrapper .nav1",
                    clickable: true,
                }
            });

            var posts_per_page  = <?php echo $posts_per_page ?>;

            var city        = $('#product_list_filter_city_list').val() == -1 ? -1 : $('#product_list_filter_city_list').val().split(',');
            var is_mobile   = $('#product_list_is_mobile').val();

            var params = {
                'city_id' : city,
            };

            $.ajax({
                type: 'POST',
                url: (location.hostname === 'dev.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                data: {
                    "async": false,
                    "type": "sort_products_get",
                    "data": {
                        "params"        : params,
                        'image_type'    : 'url',
                        "limit"         : posts_per_page,
                        "page"          : 1,
                        "format"        : 'html_slider',
                        "is_mobile"     : is_mobile,
                        'max_num_pages' : true,
                        'sort_type'     : 'trend',
                        'only_ads'      : 0,
                        'show_more'     : 0,
                        'badge_ads'     : 0,
                        'random'        : 1,
                        'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                    }
                },
                dataType: "json",
                success: function(data) {
                    setTimeout(function() {
                        $('#trends_wrapper .swiper-wrapper').empty();
                        $(data.products).appendTo('#trends_wrapper .swiper-wrapper');
                        $('#trends_wrapper .swiper-wrapper').css('display', 'flex');
                    }, 1);
                },
            });
        });
    </script>

    <?php
    if ( !empty( $products ) ) : ?>

        <div id="trends_wrapper" class="elite_rooms_wrapper" style="position: relative;z-index: 2;">

            <section class="slider-single-content">
                <div class="slider-single-content__title slidertitleh2">
                    <div class=" swiper-pagination nav1"></div>
                    <?php if (wp_is_mobile()) : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های ترند <?php echo $city_name ?></a>
                        </h2>
                    <?php else : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:"><?php echo $product_type ?>های ترند <?php echo $city_name ?></a>
                        </h2>
                    <?php endif; ?>
                </div>
            </section>

            <div dir="rtl" id="swiper_adsx" data-id="1" class="swiper topescaperoom" style="align-items: stretch;display: flex;">
                <div class="swiper-wrapper">
                    <?php echo $products; ?>
                </div>
            </div>
        </div>

    <?php
    endif;

}
