<?php
/**
 * get_deactivated_rooms
 *
 * توابع: get_deactivated_rooms
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3369-3508)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_deactivated_rooms() {

    $current_archive_obj = get_queried_object();
    $term_id = $current_archive_obj->term_id;

    if ( $current_archive_obj->parent == 0 )
        $product_type = get_term( $current_archive_obj )->name;
    else
        $product_type = get_term( $current_archive_obj->parent )->name;

    $posts_per_page = 100;
    $params = [
        'city_id' => [$term_id],
    ];

    $args = [
        'params'            => $params,
        'image_type'        => 'url',
        'limit'             => $posts_per_page,
        'page'              => 1,
        'max_num_pages'     => true,
        "format"            => 'html_slider',
        'is_mobile'         => wp_is_mobile(),
        'sort_type'         => 'recent',
        'deactivate'        => true,
        'only_ads'          => false,
        'show_more'         => 0,
        'badge_ads'         => false,
        'random'            => true
    ];
    $data = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) );
    $products = $data->products; ?>

    <script>
        jQuery(document).ready(function ($) {

            if ( location.hostname == 'escapezoom.co' ) {
                $('.topescaperoom').each(function(i, obj) {
                    $res = $(obj).html().replace(/escapezoom.ir/g, "escapezoom.co");
                    $(obj).html($res);
                });
            }

            var swiper = new Swiper("#swiper_adsx", {

                <?php if ( wp_is_mobile() ) : ?>
                slidesPerView: 2.5,

                <?php else : ?>
                slidesPerView: 7.5,

                <?php endif; ?>

                spaceBetween: 10,
                loop: false,
                paginationClickable: true,
                freeMode: true,
                pagination: {
                    el: "#deactivated_wrapper .nav1",
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
                        'max_num_pages' : true,
                        'sort_type'     : 'recent',
                        'deactivate'    : true,
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
                        $('#deactivated_wrapper .swiper-wrapper').empty();
                        $(data.products).appendTo('#deactivated_wrapper .swiper-wrapper');
                        $('#deactivated_wrapper .swiper-wrapper').css('display', 'flex');
                    }, 1);
                },
            });
        });
    </script>

    <?php
    if ( !empty( $products ) ) : ?>

        <div id="deactivated_wrapper" class="elite_rooms_wrapper" style="position: relative;z-index: 2;">

            <section class="slider-single-content">
                <div class="slider-single-content__title slidertitleh2">
                    <div class=" swiper-pagination nav1"></div>
                    <?php if (wp_is_mobile()) : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:;">رزرو غیرفعال</a>
                        </h2>
                    <?php else : ?>
                        <h2>
                            <img style="width: 25px!important;" src="http://escapezoom.ir/wp-content/uploads/2023/08/escapezoom_tiny_logo.png">
                            <a href="javascript:;">رزرو غیرفعال</a>
                        </h2>
                    <?php endif; ?>
                </div>
            </section>

            <div dir="rtl" id="swiper_adsx" data-id="1" class="swiper topescaperoom">
                <div class="swiper-wrapper">
                    <?php echo $products; ?>
                </div>
            </div>
        </div>

    <?php
    endif;
}
