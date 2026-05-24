<?php
/**
 * elite_rooms_of_tehran_func3
 *
 * توابع: elite_rooms_of_tehran_func3
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 2173-3368)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function elite_rooms_of_tehran_func3($state) {

    $current_archive_obj = get_queried_object();
    $term_id    = $current_archive_obj->term_id;
    $term_type  = $current_archive_obj->taxonomy;

    $posts_per_page = 45;
    $type           = 'popular';

    if ( $term_type == 'product_tag' ) {
        $params = [
            'tag' => [$term_id],
        ];

    } else {

        if ( $current_archive_obj->parent == 0 ) {
            $params = [
                'product_type' => $current_archive_obj->name,
            ];

        } else {
            $params = [
                'city_id' => [$term_id],
            ];
        }
    }

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => true,
        "format"        => 'html_cat',
        'is_mobile'     => wp_is_mobile(),
        'sort_type'     => 'popular',
        'unpin_ads'     => $state == 'complete' ? false : true,
        'badge_ads'     => $state == 'complete' ? true : false,
        'random'        => $state == 'complete' ? false : true,
        'random_memory' => '',
        'show_more'     => 0,
    ];

    $data = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) );

    $products = $data->products; ?>

    <script>document.body.classList.add("woocommerce");</script>
    <style>
        .load-more {
            display: none;
            margin-top: 300px;
        }
        .img-arc-mobile {
            height: auto;
            width: 100%;
            max-width: 80px!important;
        }
        .arc-box-mobile {
            border: 1px solid #eee;
            margin: 10px 0 10px;
            width: 100%;
            border-radius: 3px position: relative;
        }
        .arc-mobile-btn {
            font-size: 10px;
            height: 25px;
            width: calc(100% - 29px);
            margin-left: 30px!important;
        }
        .arc-mobile-footer {
            margin-top: -13px;
        }
        .arc-mobile-loc,.arc-mobile-price,.arc-mobile-nafar,.arc-mobile-time {
            font-size: 10px;
            font-weight: 400;
            text-align: center;
        }
        .arc-mobile-title {
            font-size: 1em !important;
            margin-right: -7px;
            font-weight: 800;
        }
        h5 {
            font-size: 14px;
            padding: 10px;
        }
        .swiper-wrapper {
            height: auto!important;
        }
        #non_slider_wrapperx li {
            padding: 10px;
            font-size: 13px;
            background: #e9e9e9;
            /*width: 100%;*/
            text-align: center;
            display: inline-block;
        }
        a.btn.btn-light.m-2 {
            width: 59px;
        }
        @media only screen and (min-width: 0px) and (max-width: 594px) {
            .media-body .arc-mobile-btn {
                width: 100% !important;
                white-space: nowrap !important;
                margin-left: 0 !important;
            }
            #non_slider_wrapperx li {
                width: 100%;
            }
        }
        @media only screen and (max-width: 600px) {
            span.special-escapezooms {
                margin-top: 85px;
                right: 63px;
            }
            span.shakes {
                right: 63px;
                margin-top: 86px;
            }
        }
        .zardkooh-shop-sort-by {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-pack: justify;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .shop-page-header-sort {
            max-width: 414px;
            float: left;
            opacity: 1 !important;
            z-index: 99;
            display: flex;
            justify-content: flex-end;
            list-style: none;
            background-color: #fff;
            box-shadow: none;
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
            align-items: center;
            height: 30px;
            margin-top: 5px;
            border: 1px solid #d5d5d5;
        }
        .shop-page-header-sort li a.is-active {
            background-color: #ee5a24;
            color: #fff;
        }
        .shop-page-header-sort li a:last-child {
            border-left: none;
        }
        .shop-page-header-sort li a {
            color: #606060;
            padding: 10px;
            border: 1px solid #d5d5d5;
        }
        .shop-page-header-sort {
            /*left: 5px;*/
            /*top: 10px;*/
        }
        .woocommerce ul.products {
            margin: 45px 0 1em;
        }
        .is-active {
            display: block!important;
        }
        #ez_cat_filter_wrapper {
            background: #fff;
            padding: 0;
            box-shadow: 0 2px 1px rgb(82 99 116 / 13%);
            border: 1px solid #eee;
            border-radius: 8px;
        }
        button#ez_cat_filter_submit {
            display: block;
            margin: 30px auto 10px;
            background: #ee5a24;
            color: #fff;
            width: 80%;
        }
        .ez_cat_filter_item_city_list, .ez_cat_filter_item_tag_list {
            appearance: auto;
        }
        .ez_cat_filter_item {
            position: relative;
            margin: 5px;
            border-radius: 8px;
        }
        .ez_cat_filter_item_slider_min {
            position: absolute;
            top: 32px;
            right: 5px;
            font-size: 13px;
        }
        .ez_cat_filter_item_slider_max {
            position: absolute;
            top: 32px;
            left: 5px;
            font-size: 13px;
        }
        .noUi-pips.noUi-pips-horizontal {
            display: none;
        }
        .ez_cat_filter_item_schedule_days_item {
            background: #fff;
            color: #323232;
            border-radius: 8px;
            width: 90px;
            display: flex;
            height: 40px;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: 1px solid #80ffbe;
            transition: 0.5s;
        }
        .ez_cat_filter_item_schedule_days_item:hover {
            background: #90ffc6;
            transition: 0.5s;
        }
        #ez_cat_filter_item_schedule_days {
            display: flex;
            justify-content: space-between;
            margin: 20px 5px;
        }
        .ez_cat_filter_item_schedule_slider_wrapper {
            position: relative;
            display: none;
            justify-content: space-between;
            margin: 20px 5px 20px 0px;
        }
        .ez_cat_filter_item_schedule_days_item.is-active {
            background: #18ab60;
            display: flex !important;
            color: #fff;
        }
        .noUi-target {
            margin: 20px 10px 20px 10px;
        }
        .ez_cat_filter_item_title {
            display: block;
            font-size: 15px;
            color: #343434;
        }
        div#product_filter_item_accordion_wrapper .accordion-body {
            background: #ffffff;
        }
        .ez_cat_filter_item_body {
            color: #000;
        }
        #ez_cat_filter_item_schedule_days_wrapper .ez_cat_filter_item_slider_min, #ez_cat_filter_item_schedule_days_wrapper .ez_cat_filter_item_slider_max {
            top: 30px;
        }
        #ez_cat_filter_item_count {
            width: 90px;
        }
        #ez_cat_filter_item_level {
            width: 100%;
        }
        .ez_cat_filter_item_city_list_item *, .ez_cat_filter_item_tag_list_item * {
            cursor: pointer;
        }
        #ez_cat_filter_title {
            padding: 10px;
            display: block;
            color: #fff;
            font-size: 21px;
            background: #ee5a24;
            text-align: center;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
        }
        #ez_cat_filter_item_price_max_title {
            position: absolute;
            top: -30px;
            left: 5px;
            font-size: 12px;
        }
        #ez_cat_filter_item_price_min_title {
            position: absolute;
            top: -30px;
            right: 5px;
            font-size: 12px;
        }
        #ez_cat_filter_item_duration {
            width: 190px;
        }
        #ez_cat_filter_item_age {
            width: 190px;
        }
        .ez_cat_filter_item_schedule_slider_input {
            font-size: 18px !important;
            border: 1px solid #cacaca !important;
            width: 80px;
            height: 35px;
            margin: 0 4px;
            display: block;
            text-align: left;
            padding: 5px 0px 0px 29px!important;
            border-radius: 6px !important;
        }
        .ez_cat_filter_item_schedule_slider_input_wrapper {
            display: flex;
            position: relative;
        }
        .ez_cat_filter_item_schedule_slider_input_up_arrow {
            position: absolute;
            left: 4px;
            top: 0;
            cursor: pointer;
            font-size: 25px;
            padding: 1px 4px 0px 7px;
            height: 34px;
        }
        .remodal .ez_cat_filter_item_schedule_slider_input_up_arrow {
            top: -2px;
        }
        .ez_cat_filter_item_schedule_slider_input:disabled {
            color: #363636 !important;
        }
        .ez_cat_filter_item_schedule_slider_input_down_arrow {
            position: absolute;
            right: 58px;
            font-size: 25px;
            top: 2px;
            cursor: pointer;
            padding: 0px 7px 0px 9px;
            height: 34px;
        }
        .remodal .ez_cat_filter_item_schedule_slider_input_down_arrow {
            top: 16px;
        }
        .ez_cat_filter_item_schedule_slider_input_title {
            display: flex;
            align-items: center;
        }
        .ez_cat_filter_item_schedule_slider_input_arrow_line {
            position: absolute;
            right: 65px;
            border-top: 1px solid #cacaca;
            width: 34px;
            top: 17px;
            rotate: 90deg;
        }
        .ez_cat_filter_item_schedule_slider_input_arrow_border {
            position: absolute;
            left: 10px;
            border-top: 1px solid #cacaca;
            width: 34px;
            top: 17px;
            rotate: 90deg;
        }
        .ez_2clmns {
            display: grid;
            grid-auto-flow: dense;
            grid-template-columns: 33% 33% 33%;
        }
        #ez_cat_filter_wrapper select {
            border: 1px solid #cacaca;
            border-radius: 6px;
            height: 30px;
        }
        .ez_cat_filter_item_city_list_item, .ez_cat_filter_item_tag_list_item {
            text-align: right;
            padding: 2px;
        }
        #ez_cat_filter_item_count_wrapper .ez_cat_filter_item_schedule_slider_input_wrapper {
            /*width: 58px;*/
        }
        #ez_cat_filter_item_count_wrapper .ez_cat_filter_item_schedule_slider_input_up_arrow {
            left: 23px;
        }
        #ez_cat_filter_item_count_wrapper .ez_cat_filter_item_schedule_slider_input_down_arrow {
            right: 5px;
        }
        .woocommerce ul.products.columns-3 li.product, .woocommerce-page ul.products.columns-3 li.product {
            width: 15.5%;
        }
        #ez_cat_filter_item_schedule_today_slider_wrapper, #ez_cat_filter_item_schedule_count_slider_wrapper {
            display: flex;
        }
        .noUi-connect {
            background: #f96f0c;
        }
        #zardkooh-woo-ajax-navigation-sort-by {
            position: relative;
        }
        .swiper-pagination.nav1.swiper-pagination-clickable.swiper-pagination-bullets.swiper-pagination-horizontal {
            display: none;
        }
        .swiper-initialized{
            height: 282px !important;
        }
        .swiper-slide {
            margin-left: 10px;
        }
        @media only screen and (max-width: 600px) {
            .swiper-slide {
                margin-left: 10px;
            }
            .slider-single-content__title h2 img {
                width: 25px !important;
            }
        }
        @media only screen and (max-width: 600px){
            span.special-escapezooms {
                right: 120px !important;
                bottom: 0 !important;
            }
        }
        .filteresnewm.fade .modal-dialog {
            transform: translate3d(0, 40vh, 0) !important;
        }
        #product_filter_item_accordion_wrapper {
            margin: 5px;
        }
        #product_filter_item_accordion_wrapper .accordion-button:after {
            margin: 0 auto 0 0;
        }
        #product_filter_item_accordion_wrapper .accordion-body {
            padding: 1px 0;
        }
        #ez_cat_filter_item_schedule_count_slider_wrapper .ez_cat_filter_item_schedule_slider_input_arrow_line {
            right: 11px;
        }
        #ez_cat_filter_item_schedule_count_slider_wrapper .ez_cat_filter_item_schedule_slider_input_arrow_border {
            left: 29px;
        }
        #ez_cat_filter_item_price_wrapper .ez_cat_filter_item_body {
            margin: 40px 10px;
        }
        .accordion-item:first-of-type .accordion-button {
            background: #f3f3f3;
            border: 1px solid #e3e3e3;
            padding: 10px;
        }
        .slider-single-content__title h2 a {
            color: #242424;
            font-weight: bold;
        }
        .slider-single-content__title h2 img {
            width: 25px;
            margin: 0px 0 0 10px;
        }
        .slider-single-content__title {
            margin-bottom: 20px;
        }

        #nuwruz_wrapper {
            background: #60bb00;
            padding: 20px 10px 0px 10px;
            border: 1px solid #e3e2e2;
            border-radius: 8px;
            margin: 20px 0;
        }
        .shop-page-header-sort li:first-child a {
            border-right: none;
        }
        #single_product_sansyab_top_desc {
            text-align: justify;
            font-size: 14px;
            background: #f3f3f3;
            padding: 20px;
            border: 1px solid #e3e2e2;
            border-radius: 8px;
            margin: 20px 0;
        }
        #ez_cat_filter_item_schedule_days_wrapper span.ez_cat_filter_item_title {
            background: #f3f3f3;
            border: 2px solid #e3e3e3;
            padding: 10px;
            border-radius: 10px;
        }
        .ez-tag-title, .shakes-title {
            font-size: 17px!important;
            color: #242424;
            font-weight: bold;
        }
        .shop-page-header {
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
        }
        #product_list_resultof_sansyab{
            padding: 0px 30px 0 0;
        }
        #non_slider_wrapperx.shop-page-header {
            position: relative;
        }
        #non_slider_wrapperx .shop-page-header-sort {
            height: 30px;
        }
        .filternav {
            background: #f3f3f3;
        }
        @media only screen and (min-width: 0px) and (max-width:594px) {
            .shop-page-header-sort {
                margin-top:50px !important;
            }
            .elite_rooms_wrapper .slider-single-content {
                margin-bottom:50px;
            }
            .filternav ,.filternav a{
                font-size:15px;
            }
            #product_list_resultof_sansyab{
                padding: 0px;
            }
            #non_slider_wrapperx .shop-page-header-sort {
                height: auto;
            }
            .rb-left{
                line-height:8px;
            }
            .box-rooms img{
                height:100% !important;
            }
        }
    </style>

    <?php
    if ( $state == 'complete' ) : ?>

        <style>
            #non_slider_wrapperx .shop-page-header-sort {
                height: 30px;
            }
            .shop-page-header-sort {
                margin-top: 0 !important;
            }
            #non_slider_wrapperx li {
                width: auto;
            }
        </style>

        <div class="shop-page-header" id="non_slider_wrapperx">
            <section id="zardkooh-woo-ajax-navigation-sort-by" class="zardkooh-shop-sort-by yith-wcan-sort-by">
                <div class="sort-by-title"><?php //echo _e( 'Sort By', 'zardkooh' ); ?></div>

                <ul class="shop-page-header-sort" style="max-width: 414px; float: left; opacity: 1 !important;">
                    <li><a id="product_popular_sort_btn" data-id="popular" class="orderby-item is-active" href="javascript:" data-id="default" >محبوب ترین</a></li>
                    <li><a id="product_topsale_sort_btn" data-id="topsale" class="orderby-item" href="javascript:" >پرفروش ترین</a></li>
                    <li><a id="product_recent_sort_btn" data-id="recent" class="orderby-item" href="javascript:" data-id="date" d>جدیدترین</a></li>
                </ul>

            </section>
        </div>

    <?php
    else :
        if (wp_is_mobile()) { ?>

            <div class="filternav" style="">
                <div class="row">
                    <div class="col-sm-6 col-6">
                        <a class="btn" data-bs-toggle="modal" data-bs-target="#advanced-filter-mobile" style="font-size: 13px;">فیلتر</a>
                        <a class="btn" data-bs-toggle="modal" data-bs-target="#sortfilteres" style="margin: 0 0 0 0;font-size: 13px;padding: 6px 10px;">مرتب سازی</a>
                    </div>
                    <div class="col-sm-6 col-6">
                        <div class="form-check form-switch">
                            <!-- SansDar Haye Emrooz -->
                            <input class="form-check-input" type="checkbox" role="switch" id="ez_cat_filter_item_schedule_today_only_toggle" style="margin-top: 0;width: 3em;height: 1.5em;">
                            <label class="form-check-label" for="flexSwitchCheckDefault" style="font-size: 11px;margin-left: 5px;">سانس دارهای امروز</label>
                        </div>

                    </div>
                </div>
            </div>

            <div class="sansyabnewm modal fade" id="filteresnew" data-bs-backdrop="filter" data-bs-keyboard="false" tabindex="-1" aria-labelledby="filteresnewlabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <!-- sansyab -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sort Options -->
            <div class="filteresnewm modal fade" id="sortfilteres" data-bs-backdrop="sort" data-bs-keyboard="false" tabindex="-1" aria-labelledby="sortfiltereslabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="shop-page-header" id="non_slider_wrapperx">
                                <ul class="form-select shop-page-header-sort" size="3" aria-label="size 3 select example" style="height: auto;display: flex;justify-content: center;flex-direction: column;">
                                    <li id="product_popular_sort_btn" data-id="popular" class="orderby-item">محبوب ترین</li>
                                    <li id="product_topsale_sort_btn" data-id="topsale" class="orderby-item">پرفروش ترین</li>
                                    <li id="product_recent_sort_btn" data-id="recent" class="orderby-item">جدیدترین</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        } else { ?>

            <div class="shop-page-header" id="non_slider_wrapperx">
                <section id="zardkooh-woo-ajax-navigation-sort-by" class="zardkooh-shop-sort-by yith-wcan-sort-by">
                    <div class="sort-by-title"><?php //echo _e( 'Sort By', 'zardkooh' ); ?></div>

                    <ul class="shop-page-header-sort" style="max-width: 414px; float: left; opacity: 1 !important;">
                        <li><a id="product_popular_sort_btn" data-id="popular" class="orderby-item" href="javascript:" data-id="default" >محبوب ترین</a></li>
                        <li><a id="product_topsale_sort_btn" data-id="topsale" class="orderby-item" href="javascript:" >پرفروش ترین</a></li>
                        <li><a id="product_recent_sort_btn" data-id="recent" class="orderby-item" href="javascript:" data-id="date" d>جدیدترین</a></li>
                    </ul>

                </section>
            </div>

            <?php
        }
    endif;

    if (wp_is_mobile()) { ?>
        <div class="shop-filters">

            <?php
            if ( $state == 'complete' ) : ?>
                <button type="button" class="btn btn-primary filtermob" data-bs-toggle="modal" data-bs-target="#advanced-filter-mobile" style="background: #ee5a24;color: #fff;font-size: 20px;padding: 5px;">سانس یاب</button>
            <?php
            endif; ?>

            <!-- Modal -->
            <div class="modal fade" id="advanced-filter-mobile" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                        </div>
                        <div class="modal-body">
                            <?php
                            get_template_part('inc/template/categories_filters', ''); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } ?>

    <ul class="products shop-page-items columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>" >
        <?php echo $products; ?>
    </ul>

    <input type="hidden" id="product_list_data_sort_type" value="<?php echo $type ?>">
    <input type="hidden" id="product_list_data_query_type" value="<?php echo $term_type == 'product_tag' ? 'tag' : 'cat' ?>">
    <input type="hidden" id="product_list_data_query_id" value="<?php echo $term_id ?>">
    <input type="hidden" id="product_list_cur_page_num" value="2">
    <input type="hidden" id="product_list_max_num_pages" value="<?php echo $data->max_num_pages; ?>">
    <input type="hidden" id="product_list_is_mobile" value="<?php echo wp_is_mobile() ? 1 : 0; ?>">
    <input type="hidden" id="posts_per_page" value="<?php echo $posts_per_page; ?>">

    <input type="hidden" id="product_list_filter_product_type" value="<?php if ( $term_type == 'product_cat' ) { echo $current_archive_obj->parent == 0 ? $current_archive_obj->name : -1; } else {echo -1;} ?>">
    <input type="hidden" id="product_list_filter_city_list" value="<?php echo $term_type == 'product_tag' ? -1 : $term_id ?>">
    <input type="hidden" id="product_list_filter_tag_list" value="<?php echo $term_type == 'product_tag' ? $term_id : -1 ?>">
    <input type="hidden" id="product_list_filter_count" value="-1">
    <input type="hidden" id="product_list_filter_level" value="-1">
    <input type="hidden" id="product_list_filter_age" value="-1">
    <input type="hidden" id="product_list_filter_duration" value="-1">
    <input type="hidden" id="product_list_filter_price_min" value="-1">
    <input type="hidden" id="product_list_filter_price_max" value="-1">
    <input type="hidden" id="product_list_filter_schedule_min" value="-1">
    <input type="hidden" id="product_list_filter_schedule_max" value="-1">

    <div class="loader load-more"></div>
    <span id="scroll_end"></span>

    <input type="hidden" id="product_list_memory_for_random_purposes" value="<?php echo $state == 'complete' ? -1 : implode(',', (array)$data->products_id);  ?>">
    <input type="hidden" id="product_list_type_of_request" value="<?php echo $state; ?>">

    <script>
        jQuery(document).ready(function($) {

            !function(a){"function"==typeof define&&define.amd?define([],a):"object"==typeof exports?module.exports=a():window.noUiSlider=a()}(function(){"use strict";function a(a,b){var c=document.createElement("div");return j(c,b),a.appendChild(c),c}function b(a){return a.filter(function(a){return!this[a]&&(this[a]=!0)},{})}function c(a,b){return Math.round(a/b)*b}function d(a,b){var c=a.getBoundingClientRect(),d=a.ownerDocument,e=d.documentElement,f=m();return/webkit.*Chrome.*Mobile/i.test(navigator.userAgent)&&(f.x=0),b?c.top+f.y-e.clientTop:c.left+f.x-e.clientLeft}function e(a){return"number"==typeof a&&!isNaN(a)&&isFinite(a)}function f(a,b,c){c>0&&(j(a,b),setTimeout(function(){k(a,b)},c))}function g(a){return Math.max(Math.min(a,100),0)}function h(a){return Array.isArray(a)?a:[a]}function i(a){a=String(a);var b=a.split(".");return b.length>1?b[1].length:0}function j(a,b){a.classList?a.classList.add(b):a.className+=" "+b}function k(a,b){a.classList?a.classList.remove(b):a.className=a.className.replace(new RegExp("(^|\\b)"+b.split(" ").join("|")+"(\\b|$)","gi")," ")}function l(a,b){return a.classList?a.classList.contains(b):new RegExp("\\b"+b+"\\b").test(a.className)}function m(){var a=void 0!==window.pageXOffset,b="CSS1Compat"===(document.compatMode||""),c=a?window.pageXOffset:b?document.documentElement.scrollLeft:document.body.scrollLeft,d=a?window.pageYOffset:b?document.documentElement.scrollTop:document.body.scrollTop;return{x:c,y:d}}function n(){return window.navigator.pointerEnabled?{start:"pointerdown",move:"pointermove",end:"pointerup"}:window.navigator.msPointerEnabled?{start:"MSPointerDown",move:"MSPointerMove",end:"MSPointerUp"}:{start:"mousedown touchstart",move:"mousemove touchmove",end:"mouseup touchend"}}function o(a,b){return 100/(b-a)}function p(a,b){return 100*b/(a[1]-a[0])}function q(a,b){return p(a,a[0]<0?b+Math.abs(a[0]):b-a[0])}function r(a,b){return b*(a[1]-a[0])/100+a[0]}function s(a,b){for(var c=1;a>=b[c];)c+=1;return c}function t(a,b,c){if(c>=a.slice(-1)[0])return 100;var d,e,f,g,h=s(c,a);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],f+q([d,e],c)/o(f,g)}function u(a,b,c){if(c>=100)return a.slice(-1)[0];var d,e,f,g,h=s(c,b);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],r([d,e],(c-f)*o(f,g))}function v(a,b,d,e){if(100===e)return e;var f,g,h=s(e,a);return d?(f=a[h-1],g=a[h],e-f>(g-f)/2?g:f):b[h-1]?a[h-1]+c(e-a[h-1],b[h-1]):e}function w(a,b,c){var d;if("number"==typeof b&&(b=[b]),"[object Array]"!==Object.prototype.toString.call(b))throw new Error("noUiSlider: 'range' contains invalid value.");if(d="min"===a?0:"max"===a?100:parseFloat(a),!e(d)||!e(b[0]))throw new Error("noUiSlider: 'range' value isn't numeric.");c.xPct.push(d),c.xVal.push(b[0]),d?c.xSteps.push(!isNaN(b[1])&&b[1]):isNaN(b[1])||(c.xSteps[0]=b[1]),c.xHighestCompleteStep.push(0)}function x(a,b,c){if(!b)return!0;c.xSteps[a]=p([c.xVal[a],c.xVal[a+1]],b)/o(c.xPct[a],c.xPct[a+1]);var d=(c.xVal[a+1]-c.xVal[a])/c.xNumSteps[a],e=Math.ceil(Number(d.toFixed(3))-1),f=c.xVal[a]+c.xNumSteps[a]*e;c.xHighestCompleteStep[a]=f}function y(a,b,c,d){this.xPct=[],this.xVal=[],this.xSteps=[d||!1],this.xNumSteps=[!1],this.xHighestCompleteStep=[],this.snap=b,this.direction=c;var e,f=[];for(e in a)a.hasOwnProperty(e)&&f.push([a[e],e]);for(f.length&&"object"==typeof f[0][0]?f.sort(function(a,b){return a[0][0]-b[0][0]}):f.sort(function(a,b){return a[0]-b[0]}),e=0;e<f.length;e++)w(f[e][1],f[e][0],this);for(this.xNumSteps=this.xSteps.slice(0),e=0;e<this.xNumSteps.length;e++)x(e,this.xNumSteps[e],this)}function z(a,b){if(!e(b))throw new Error("noUiSlider: 'step' is not numeric.");a.singleStep=b}function A(a,b){if("object"!=typeof b||Array.isArray(b))throw new Error("noUiSlider: 'range' is not an object.");if(void 0===b.min||void 0===b.max)throw new Error("noUiSlider: Missing 'min' or 'max' in 'range'.");if(b.min===b.max)throw new Error("noUiSlider: 'range' 'min' and 'max' cannot be equal.");a.spectrum=new y(b,a.snap,a.dir,a.singleStep)}function B(a,b){if(b=h(b),!Array.isArray(b)||!b.length)throw new Error("noUiSlider: 'start' option is incorrect.");a.handles=b.length,a.start=b}function C(a,b){if(a.snap=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'snap' option must be a boolean.")}function D(a,b){if(a.animate=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'animate' option must be a boolean.")}function E(a,b){if(a.animationDuration=b,"number"!=typeof b)throw new Error("noUiSlider: 'animationDuration' option must be a number.")}function F(a,b){var c,d=[!1];if(b===!0||b===!1){for(c=1;c<a.handles;c++)d.push(b);d.push(!1)}else{if(!Array.isArray(b)||!b.length||b.length!==a.handles+1)throw new Error("noUiSlider: 'connect' option doesn't match handle count.");d=b}a.connect=d}function G(a,b){switch(b){case"horizontal":a.ort=0;break;case"vertical":a.ort=1;break;default:throw new Error("noUiSlider: 'orientation' option is invalid.")}}function H(a,b){if(!e(b))throw new Error("noUiSlider: 'margin' option must be numeric.");if(0!==b&&(a.margin=a.spectrum.getMargin(b),!a.margin))throw new Error("noUiSlider: 'margin' option is only ticketinged on linear sliders.")}function I(a,b){if(!e(b))throw new Error("noUiSlider: 'limit' option must be numeric.");if(a.limit=a.spectrum.getMargin(b),!a.limit||a.handles<2)throw new Error("noUiSlider: 'limit' option is only supported on linear sliders with 2 or more handles.")}function J(a,b){switch(b){case"ltr":a.dir=0;break;case"rtl":a.dir=1;break;default:throw new Error("noUiSlider: 'direction' option was not recognized.")}}function K(a,b){if("string"!=typeof b)throw new Error("noUiSlider: 'behaviour' must be a string containing options.");var c=b.indexOf("tap")>=0,d=b.indexOf("drag")>=0,e=b.indexOf("fixed")>=0,f=b.indexOf("snap")>=0,g=b.indexOf("hover")>=0;if(e){if(2!==a.handles)throw new Error("noUiSlider: 'fixed' behaviour must be used with 2 handles");H(a,a.start[1]-a.start[0])}a.events={tap:c||f,drag:d,fixed:e,snap:f,hover:g}}function L(a,b){if(b!==!1)if(b===!0){a.tooltips=[];for(var c=0;c<a.handles;c++)a.tooltips.push(!0)}else{if(a.tooltips=h(b),a.tooltips.length!==a.handles)throw new Error("noUiSlider: must pass a formatter for all handles.");a.tooltips.forEach(function(a){if("boolean"!=typeof a&&("object"!=typeof a||"function"!=typeof a.to))throw new Error("noUiSlider: 'tooltips' must be passed a formatter or 'false'.")})}}function M(a,b){if(a.format=b,"function"==typeof b.to&&"function"==typeof b.from)return!0;throw new Error("noUiSlider: 'format' requires 'to' and 'from' methods.")}function N(a,b){if(void 0!==b&&"string"!=typeof b&&b!==!1)throw new Error("noUiSlider: 'cssPrefix' must be a string or `false`.");a.cssPrefix=b}function O(a,b){if(void 0!==b&&"object"!=typeof b)throw new Error("noUiSlider: 'cssClasses' must be an object.");if("string"==typeof a.cssPrefix){a.cssClasses={};for(var c in b)b.hasOwnProperty(c)&&(a.cssClasses[c]=a.cssPrefix+b[c])}else a.cssClasses=b}function P(a,b){if(b!==!0&&b!==!1)throw new Error("noUiSlider: 'useRequestAnimationFrame' option should be true (default) or false.");a.useRequestAnimationFrame=b}function Q(a){var b,c={margin:0,limit:0,animate:!0,animationDuration:300,format:T};b={step:{r:!1,t:z},start:{r:!0,t:B},connect:{r:!0,t:F},direction:{r:!0,t:J},snap:{r:!1,t:C},animate:{r:!1,t:D},animationDuration:{r:!1,t:E},range:{r:!0,t:A},orientation:{r:!1,t:G},margin:{r:!1,t:H},limit:{r:!1,t:I},behaviour:{r:!0,t:K},format:{r:!1,t:M},tooltips:{r:!1,t:L},cssPrefix:{r:!1,t:N},cssClasses:{r:!1,t:O},useRequestAnimationFrame:{r:!1,t:P}};var d={connect:!1,direction:"ltr",behaviour:"tap",orientation:"horizontal",cssPrefix:"noUi-",cssClasses:{target:"target",base:"base",origin:"origin",handle:"handle",horizontal:"horizontal",vertical:"vertical",background:"background",connect:"connect",ltr:"ltr",rtl:"rtl",draggable:"draggable",drag:"state-drag",tap:"state-tap",active:"active",tooltip:"tooltip",pips:"pips",pipsHorizontal:"pips-horizontal",pipsVertical:"pips-vertical",marker:"marker",markerHorizontal:"marker-horizontal",markerVertical:"marker-vertical",markerNormal:"marker-normal",markerLarge:"marker-large",markerSub:"marker-sub",value:"value",valueHorizontal:"value-horizontal",valueVertical:"value-vertical",valueNormal:"value-normal",valueLarge:"value-large",valueSub:"value-sub"},useRequestAnimationFrame:!0};Object.keys(b).forEach(function(e){if(void 0===a[e]&&void 0===d[e]){if(b[e].r)throw new Error("noUiSlider: '"+e+"' is required.");return!0}b[e].t(c,void 0===a[e]?d[e]:a[e])}),c.pips=a.pips;var e=[["left","top"],["right","bottom"]];return c.style=e[c.dir][c.ort],c.styleOposite=e[c.dir?0:1][c.ort],c}function R(c,e,i){function o(b,c){var d=a(b,e.cssClasses.origin),f=a(d,e.cssClasses.handle);return f.setAttribute("data-handle",c),d}function p(b,c){return!!c&&a(b,e.cssClasses.connect)}function q(a,b){ba=[],ca=[],ca.push(p(b,a[0]));for(var c=0;c<e.handles;c++)ba.push(o(b,c)),ha[c]=c,ca.push(p(b,a[c+1]))}function r(b){j(b,e.cssClasses.target),0===e.dir?j(b,e.cssClasses.ltr):j(b,e.cssClasses.rtl),0===e.ort?j(b,e.cssClasses.horizontal):j(b,e.cssClasses.vertical),aa=a(b,e.cssClasses.base)}function s(b,c){return!!e.tooltips[c]&&a(b.firstChild,e.cssClasses.tooltip)}function t(){var a=ba.map(s);Z("update",function(b,c,d){if(a[c]){var f=b[c];e.tooltips[c]!==!0&&(f=e.tooltips[c].to(d[c])),a[c].innerHTML=f}})}function u(a,b,c){if("range"===a||"steps"===a)return ia.xVal;if("count"===a){var d,e=100/(b-1),f=0;for(b=[];(d=f++*e)<=100;)b.push(d);a="positions"}return"positions"===a?b.map(function(a){return ia.fromStepping(c?ia.getStep(a):a)}):"values"===a?c?b.map(function(a){return ia.fromStepping(ia.getStep(ia.toStepping(a)))}):b:void 0}function v(a,c,d){function e(a,b){return(a+b).toFixed(7)/1}var f={},g=ia.xVal[0],h=ia.xVal[ia.xVal.length-1],i=!1,j=!1,k=0;return d=b(d.slice().sort(function(a,b){return a-b})),d[0]!==g&&(d.unshift(g),i=!0),d[d.length-1]!==h&&(d.push(h),j=!0),d.forEach(function(b,g){var h,l,m,n,o,p,q,r,s,t,u=b,v=d[g+1];if("steps"===c&&(h=ia.xNumSteps[g]),h||(h=v-u),u!==!1&&void 0!==v)for(h=Math.max(h,1e-7),l=u;l<=v;l=e(l,h)){for(n=ia.toStepping(l),o=n-k,r=o/a,s=Math.round(r),t=o/s,m=1;m<=s;m+=1)p=k+m*t,f[p.toFixed(5)]=["x",0];q=d.indexOf(l)>-1?1:"steps"===c?2:0,!g&&i&&(q=0),l===v&&j||(f[n.toFixed(5)]=[l,q]),k=n}}),f}function w(a,b,c){function d(a,b){var c=b===e.cssClasses.value,d=c?m:n,f=c?k:l;return b+" "+d[e.ort]+" "+f[a]}function f(a,b,c){return'class="'+d(c[1],b)+'" style="'+e.style+": "+a+'%"'}function g(a,d){d[1]=d[1]&&b?b(d[0],d[1]):d[1],i+="<div "+f(a,e.cssClasses.marker,d)+"></div>",d[1]&&(i+="<div "+f(a,e.cssClasses.value,d)+">"+c.to(d[0])+"</div>")}var h=document.createElement("div"),i="",k=[e.cssClasses.valueNormal,e.cssClasses.valueLarge,e.cssClasses.valueSub],l=[e.cssClasses.markerNormal,e.cssClasses.markerLarge,e.cssClasses.markerSub],m=[e.cssClasses.valueHorizontal,e.cssClasses.valueVertical],n=[e.cssClasses.markerHorizontal,e.cssClasses.markerVertical];return j(h,e.cssClasses.pips),j(h,0===e.ort?e.cssClasses.pipsHorizontal:e.cssClasses.pipsVertical),Object.keys(a).forEach(function(b){g(b,a[b])}),h.innerHTML=i,h}function x(a){var b=a.mode,c=a.density||1,d=a.filter||!1,e=a.values||!1,f=a.stepped||!1,g=u(b,e,f),h=v(c,b,g),i=a.format||{to:Math.round};return fa.appendChild(w(h,d,i))}function y(){var a=aa.getBoundingClientRect(),b="offset"+["Width","Height"][e.ort];return 0===e.ort?a.width||aa[b]:a.height||aa[b]}function z(a,b,c,d){var f=function(b){return!fa.hasAttribute("disabled")&&(!l(fa,e.cssClasses.tap)&&(b=A(b,d.pageOffset),!(a===ea.start&&void 0!==b.buttons&&b.buttons>1)&&((!d.hover||!b.buttons)&&(b.calcPoint=b.points[e.ort],void c(b,d)))))},g=[];return a.split(" ").forEach(function(a){b.addEventListener(a,f,!1),g.push([a,f])}),g}function A(a,b){a.preventDefault();var c,d,e=0===a.type.indexOf("touch"),f=0===a.type.indexOf("mouse"),g=0===a.type.indexOf("pointer"),h=a;if(0===a.type.indexOf("MSPointer")&&(g=!0),e){if(h.touches.length>1)return!1;c=a.changedTouches[0].pageX,d=a.changedTouches[0].pageY}return b=b||m(),(f||g)&&(c=a.clientX+b.x,d=a.clientY+b.y),h.pageOffset=b,h.points=[c,d],h.cursor=f||g,h}function B(a){var b=a-d(aa,e.ort),c=100*b/y();return e.dir?100-c:c}function C(a){var b=100,c=!1;return ba.forEach(function(d,e){if(!d.hasAttribute("disabled")){var f=Math.abs(ga[e]-a);f<b&&(c=e,b=f)}}),c}function D(a,b,c,d){var e=c.slice(),f=[!a,a],g=[a,!a];d=d.slice(),a&&d.reverse(),d.length>1?d.forEach(function(a,c){var d=M(e,a,e[a]+b,f[c],g[c]);d===!1?b=0:(b=d-e[a],e[a]=d)}):f=g=[!0];var h=!1;d.forEach(function(a,d){h=R(a,c[a]+b,f[d],g[d])||h}),h&&d.forEach(function(a){E("update",a),E("slide",a)})}function E(a,b,c){Object.keys(ka).forEach(function(d){var f=d.split(".")[0];a===f&&ka[d].forEach(function(a){a.call(da,ja.map(e.format.to),b,ja.slice(),c||!1,ga.slice())})})}function F(a,b){"mouseout"===a.type&&"HTML"===a.target.nodeName&&null===a.relatedTarget&&H(a,b)}function G(a,b){if(navigator.appVersion.indexOf("MSIE 9")===-1&&0===a.buttons&&0!==b.buttonsProperty)return H(a,b);var c=(e.dir?-1:1)*(a.calcPoint-b.startCalcPoint),d=100*c/b.baseSize;D(c>0,d,b.locations,b.handleNumbers)}function H(a,b){var c=aa.querySelector("."+e.cssClasses.active);null!==c&&k(c,e.cssClasses.active),a.cursor&&(document.body.style.cursor="",document.body.removeEventListener("selectstart",document.body.noUiListener)),document.documentElement.noUiListeners.forEach(function(a){document.documentElement.removeEventListener(a[0],a[1])}),k(fa,e.cssClasses.drag),P(),b.handleNumbers.forEach(function(a){E("set",a),E("change",a),E("end",a)})}function I(a,b){if(1===b.handleNumbers.length){var c=ba[b.handleNumbers[0]];if(c.hasAttribute("disabled"))return!1;j(c.children[0],e.cssClasses.active)}a.preventDefault(),a.stopPropagation();var d=z(ea.move,document.documentElement,G,{startCalcPoint:a.calcPoint,baseSize:y(),pageOffset:a.pageOffset,handleNumbers:b.handleNumbers,buttonsProperty:a.buttons,locations:ga.slice()}),f=z(ea.end,document.documentElement,H,{handleNumbers:b.handleNumbers}),g=z("mouseout",document.documentElement,F,{handleNumbers:b.handleNumbers});if(document.documentElement.noUiListeners=d.concat(f,g),a.cursor){document.body.style.cursor=getComputedStyle(a.target).cursor,ba.length>1&&j(fa,e.cssClasses.drag);var h=function(){return!1};document.body.noUiListener=h,document.body.addEventListener("selectstart",h,!1)}b.handleNumbers.forEach(function(a){E("start",a)})}function J(a){a.stopPropagation();var b=B(a.calcPoint),c=C(b);return c!==!1&&(e.events.snap||f(fa,e.cssClasses.tap,e.animationDuration),R(c,b,!0,!0),P(),E("slide",c,!0),E("set",c,!0),E("change",c,!0),E("update",c,!0),void(e.events.snap&&I(a,{handleNumbers:[c]})))}function K(a){var b=B(a.calcPoint),c=ia.getStep(b),d=ia.fromStepping(c);Object.keys(ka).forEach(function(a){"hover"===a.split(".")[0]&&ka[a].forEach(function(a){a.call(da,d)})})}function L(a){a.fixed||ba.forEach(function(a,b){z(ea.start,a.children[0],I,{handleNumbers:[b]})}),a.tap&&z(ea.start,aa,J,{}),a.hover&&z(ea.move,aa,K,{hover:!0}),a.drag&&ca.forEach(function(b,c){if(b!==!1&&0!==c&&c!==ca.length-1){var d=ba[c-1],f=ba[c],g=[b];j(b,e.cssClasses.draggable),a.fixed&&(g.push(d.children[0]),g.push(f.children[0])),g.forEach(function(a){z(ea.start,a,I,{handles:[d,f],handleNumbers:[c-1,c]})})}})}function M(a,b,c,d,f){return ba.length>1&&(d&&b>0&&(c=Math.max(c,a[b-1]+e.margin)),f&&b<ba.length-1&&(c=Math.min(c,a[b+1]-e.margin))),ba.length>1&&e.limit&&(d&&b>0&&(c=Math.min(c,a[b-1]+e.limit)),f&&b<ba.length-1&&(c=Math.max(c,a[b+1]-e.limit))),c=ia.getStep(c),c=g(c),c!==a[b]&&c}function N(a){return a+"%"}function O(a,b){ga[a]=b,ja[a]=ia.fromStepping(b);var c=function(){ba[a].style[e.style]=N(b),S(a),S(a+1)};window.requestAnimationFrame&&e.useRequestAnimationFrame?window.requestAnimationFrame(c):c()}function P(){ha.forEach(function(a){var b=ga[a]>50?-1:1,c=3+(ba.length+b*a);ba[a].childNodes[0].style.zIndex=c})}function R(a,b,c,d){return b=M(ga,a,b,c,d),b!==!1&&(O(a,b),!0)}function S(a){if(ca[a]){var b=0,c=100;0!==a&&(b=ga[a-1]),a!==ca.length-1&&(c=ga[a]),ca[a].style[e.style]=N(b),ca[a].style[e.styleOposite]=N(100-c)}}function T(a,b){null!==a&&a!==!1&&("number"==typeof a&&(a=String(a)),a=e.format.from(a),a===!1||isNaN(a)||R(b,ia.toStepping(a),!1,!1))}function U(a,b){var c=h(a),d=void 0===ga[0];b=void 0===b||!!b,c.forEach(T),e.animate&&!d&&f(fa,e.cssClasses.tap,e.animationDuration),ha.forEach(function(a){R(a,ga[a],!0,!1)}),P(),ha.forEach(function(a){E("update",a),null!==c[a]&&b&&E("set",a)})}function V(a){U(e.start,a)}function W(){var a=ja.map(e.format.to);return 1===a.length?a[0]:a}function X(){for(var a in e.cssClasses)e.cssClasses.hasOwnProperty(a)&&k(fa,e.cssClasses[a]);for(;fa.firstChild;)fa.removeChild(fa.firstChild);delete fa.noUiSlider}function Y(){return ga.map(function(a,b){var c=ia.getNearbySteps(a),d=ja[b],e=c.thisStep.step,f=null;e!==!1&&d+e>c.stepAfter.startValue&&(e=c.stepAfter.startValue-d),f=d>c.thisStep.startValue?c.thisStep.step:c.stepBefore.step!==!1&&d-c.stepBefore.highestStep,100===a?e=null:0===a&&(f=null);var g=ia.countStepDecimals();return null!==e&&e!==!1&&(e=Number(e.toFixed(g))),null!==f&&f!==!1&&(f=Number(f.toFixed(g))),[f,e]})}function Z(a,b){ka[a]=ka[a]||[],ka[a].push(b),"update"===a.split(".")[0]&&ba.forEach(function(a,b){E("update",b)})}function $(a){var b=a&&a.split(".")[0],c=b&&a.substring(b.length);Object.keys(ka).forEach(function(a){var d=a.split(".")[0],e=a.substring(d.length);b&&b!==d||c&&c!==e||delete ka[a]})}function _(a,b){var c=W(),d=["margin","limit","range","animate","snap","step","format"];d.forEach(function(b){void 0!==a[b]&&(i[b]=a[b])});var f=Q(i);d.forEach(function(b){void 0!==a[b]&&(e[b]=f[b])}),f.spectrum.direction=ia.direction,ia=f.spectrum,e.margin=f.margin,e.limit=f.limit,ga=[],U(a.start||c,b)}var aa,ba,ca,da,ea=n(),fa=c,ga=[],ha=[],ia=e.spectrum,ja=[],ka={};if(fa.noUiSlider)throw new Error("Slider was already initialized.");return r(fa),q(e.connect,aa),da={destroy:X,steps:Y,on:Z,off:$,get:W,set:U,reset:V,__moveHandles:function(a,b,c){D(a,b,ga,c)},options:i,updateOptions:_,target:fa,pips:x},L(e.events),U(e.start),e.pips&&x(e.pips),e.tooltips&&t(),da}function S(a,b){if(!a.nodeName)throw new Error("noUiSlider.create requires a single element.");var c=Q(b,a),d=R(a,c,b);return a.noUiSlider=d,d}y.prototype.getMargin=function(a){var b=this.xNumSteps[0];if(b&&a%b)throw new Error("noUiSlider: 'limit' and 'margin' must be divisible by step.");return 2===this.xPct.length&&p(this.xVal,a)},y.prototype.toStepping=function(a){return a=t(this.xVal,this.xPct,a)},y.prototype.fromStepping=function(a){return u(this.xVal,this.xPct,a)},y.prototype.getStep=function(a){return a=v(this.xPct,this.xSteps,this.snap,a)},y.prototype.getNearbySteps=function(a){var b=s(a,this.xPct);return{stepBefore:{startValue:this.xVal[b-2],step:this.xNumSteps[b-2],highestStep:this.xHighestCompleteStep[b-2]},thisStep:{startValue:this.xVal[b-1],step:this.xNumSteps[b-1],highestStep:this.xHighestCompleteStep[b-1]},stepAfter:{startValue:this.xVal[b-0],step:this.xNumSteps[b-0],highestStep:this.xHighestCompleteStep[b-0]}}},y.prototype.countStepDecimals=function(){var a=this.xNumSteps.map(i);return Math.max.apply(null,a)},y.prototype.convert=function(a){return this.getStep(this.toStepping(a))};var T={to:function(a){return void 0!==a&&a.toFixed(2)},from:Number};return{create:S}});
            !function(e){"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?module.exports=e():window.wNumb=e()}(function(){"use strict";var o=["decimals","thousand","mark","prefix","suffix","encoder","decoder","negativeBefore","negative","edit","undo"];function w(e){return e.split("").reverse().join("")}function h(e,t){return e.substring(0,t.length)===t}function f(e,t,n){if((e[t]||e[n])&&e[t]===e[n])throw new Error(t)}function x(e){return"number"==typeof e&&isFinite(e)}function n(e,t,n,r,i,o,f,u,s,c,a,p){var d,l,h,g=p,v="",m="";return o&&(p=o(p)),!!x(p)&&(!1!==e&&0===parseFloat(p.toFixed(e))&&(p=0),p<0&&(d=!0,p=Math.abs(p)),!1!==e&&(p=function(e,t){return e=e.toString().split("e"),(+((e=(e=Math.round(+(e[0]+"e"+(e[1]?+e[1]+t:t)))).toString().split("e"))[0]+"e"+(e[1]?e[1]-t:-t))).toFixed(t)}(p,e)),-1!==(p=p.toString()).indexOf(".")?(h=(l=p.split("."))[0],n&&(v=n+l[1])):h=p,t&&(h=w((h=w(h).match(/.{1,3}/g)).join(w(t)))),d&&u&&(m+=u),r&&(m+=r),d&&s&&(m+=s),m+=h,m+=v,i&&(m+=i),c&&(m=c(m,g)),m)}function r(e,t,n,r,i,o,f,u,s,c,a,p){var d,l="";return a&&(p=a(p)),!(!p||"string"!=typeof p)&&(u&&h(p,u)&&(p=p.replace(u,""),d=!0),r&&h(p,r)&&(p=p.replace(r,"")),s&&h(p,s)&&(p=p.replace(s,""),d=!0),i&&function(e,t){return e.slice(-1*t.length)===t}(p,i)&&(p=p.slice(0,-1*i.length)),t&&(p=p.split(t).join("")),n&&(p=p.replace(n,".")),d&&(l+="-"),""!==(l=(l+=p).replace(/[^0-9\.\-.]/g,""))&&(l=Number(l),f&&(l=f(l)),!!x(l)&&l))}function i(e,t,n){var r,i=[];for(r=0;r<o.length;r+=1)i.push(e[o[r]]);return i.push(n),t.apply("",i)}return function e(t){if(!(this instanceof e))return new e(t);"object"==typeof t&&(t=function(e){var t,n,r,i={};for(void 0===e.suffix&&(e.suffix=e.postfix),t=0;t<o.length;t+=1)if(void 0===(r=e[n=o[t]]))"negative"!==n||i.negativeBefore?"mark"===n&&"."!==i.thousand?i[n]=".":i[n]=!1:i[n]="-";else if("decimals"===n){if(!(0<=r&&r<8))throw new Error(n);i[n]=r}else if("encoder"===n||"decoder"===n||"edit"===n||"undo"===n){if("function"!=typeof r)throw new Error(n);i[n]=r}else{if("string"!=typeof r)throw new Error(n);i[n]=r}return f(i,"mark","thousand"),f(i,"prefix","negative"),f(i,"prefix","negativeBefore"),i}(t),this.to=function(e){return i(t,n,e)},this.from=function(e){return i(t,r,e)})}});
            /**********************************************************/

            if ( location.hostname == 'escapezoom.co' ) {
                $res = $('.shop-page-items').html().replace(/escapezoom.ir/g, "escapezoom.co");
                $('.shop-page-items').html($res);
            }

            if ( $('#ez_cat_filter_item_schedule_today_min').length )
                $('#ez_cat_filter_item_schedule_today_min').val((new Date()).getHours());

            var max_num_pages   = parseInt( $('#product_list_max_num_pages').val() );
            var is_mobile       = $('#product_list_is_mobile').val();
            var posts_per_page  = <?php echo $posts_per_page ?>;
            var list_type       = $('#product_list_type_of_request').val();

            var ajax_processing = false;
            $(window).on('scroll', function() {

                if ( !$('#scroll_end').length ) return;

                var current_page_num    = parseInt( $('#product_list_cur_page_num').val() );
                var sort_type           = $('#product_list_data_sort_type').val();

                if ( current_page_num > max_num_pages ) return false;

                if ( isScrolledIntoView( $('#scroll_end') ) && !ajax_processing ) {

                    ajax_processing = true;

                    var product_type    = $('#product_list_filter_product_type').val();
                    var city            = $('#product_list_filter_city_list').val() == -1 ? -1 : $('#product_list_filter_city_list').val().split(',');
                    var tag             = $('#product_list_filter_tag_list').val()  == -1 ? -1 : $('#product_list_filter_tag_list').val().split(',');
                    var count           = $('#product_list_filter_count').val();
                    var level           = $('#product_list_filter_level').val();
                    var age             = $('#product_list_filter_age').val();
                    var duration        = $('#product_list_filter_duration').val();
                    var price_min       = $('#product_list_filter_price_min').val();
                    var price_max       = $('#product_list_filter_price_max').val();
                    var schedule_min    = $('#product_list_filter_schedule_min').val();
                    var schedule_max    = $('#product_list_filter_schedule_max').val();

                    var params = {
                        'product_type'  : product_type,
                        'city_id'       : product_type == -1 ? city : -1,
                        'tag'           : tag,
                        'count'         : count,
                        'level'         : level,
                        'age'           : age,
                        'duration'      : duration,
                        'price'         : price_min     == -1 ? -1  : [price_min, price_max],
                        'schedule'      : schedule_min  == -1 ? -1 : [schedule_min, schedule_max],
                    };

                    var unpin_ads       = 0;
                    var badge_ads       = 1;
                    var random          = 0;
                    var random_memory   = 0;

                    if ( list_type != 'complete' ) {

                        unpin_ads   = 1;
                        badge_ads   = 0;

                        var memory_list = $('#product_list_memory_for_random_purposes').val(); // if this is empty means random mode is not enable anymore

                        if ( memory_list != -1 ) {
                            random          = 1;
                            random_memory   = memory_list;

                        } else {
                            random          = 0;
                            random_memory   = '';
                        }
                    }

                    $.ajax({
                        type: 'POST',
                        url: (location.hostname === 'wo.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                        data: {
                            "type": "sort_products_get",
                            "data": {
                                "params"        : params,
                                'image_type'    : 'url',
                                "limit"         : posts_per_page,
                                "page"          : current_page_num,
                                "format"        : 'html_cat',
                                "is_mobile"     : is_mobile,
                                'sort_type'     : sort_type,
                                'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                                'unpin_ads'     : unpin_ads,
                                'badge_ads'     : badge_ads,
                                'random'        : random,
                                'random_memory' : random_memory,
                                'show_more'     : 0,
                            }
                        },
                        dataType: "json",
                        success: function(data) {
                            $('#product_list_cur_page_num').val(++current_page_num);

                            $(data.products).appendTo('ul.shop-page-items');

                            var memory_list = $('#product_list_memory_for_random_purposes').val();
                            if ( memory_list != -1 )
                                $('#product_list_memory_for_random_purposes').val(data.products_id.join(",") + ',' + memory_list);

                            ajax_processing = false;
                        },
                    });
                }
            });
            /**********************************************************/
            $('body').on('click', '#non_slider_wrapperx .orderby-item', function (e, triggered){

                var $this       = $(this);
                var sort_type   = 'popular';

                $('#product_list_memory_for_random_purposes').val('');

                // if user clicked on one of the sort buttons the button will be active, so we won't show random display anymore.
                // this element get trigger only by 2 buttons: "only today's sanses" toggle & "result display" button of sansyab
                if ( !triggered || $($this).closest('.shop-page-header-sort').find('.orderby-item.is-active').length !== 0  ) {
                    sort_type = $this.data('id');

                    $('#product_list_memory_for_random_purposes').val(-1); //destruction of random mode
                    $('.shop-page-header-sort .orderby-item').removeClass('is-active');
                    $('#product_list_data_sort_type').val(sort_type);
                    $this.addClass('is-active');
                }

                $('#product_list_cur_page_num').val(2);

                $('.load-more').show();

                var product_type    = $('#product_list_filter_product_type').val();
                var city            = $('#product_list_filter_city_list').val() == -1 ? -1 : $('#product_list_filter_city_list').val().split(',');
                var tag             = $('#product_list_filter_tag_list').val()  == -1 ? -1 : $('#product_list_filter_tag_list').val().split(',');
                var count           = $('#product_list_filter_count').val();
                var level           = $('#product_list_filter_level').val();
                var age             = $('#product_list_filter_age').val();
                var duration        = $('#product_list_filter_duration').val();
                var price_min       = $('#product_list_filter_price_min').val();
                var price_max       = $('#product_list_filter_price_max').val();
                var schedule_min    = $('#product_list_filter_schedule_min').val();
                var schedule_max    = $('#product_list_filter_schedule_max').val();

                var params = {
                    'product_type'  : product_type,
                    'city_id'       : product_type == -1 ? city : -1,
                    'tag'           : tag,
                    'count'         : count,
                    'level'         : level,
                    'age'           : age,
                    'duration'      : duration,
                    'price'         : price_min     == -1 ? -1 : [price_min, price_max],
                    'schedule'      : schedule_min  == -1 ? -1 : [schedule_min, schedule_max],
                };

                var unpin_ads   = 0;
                var badge_ads   = 1;
                var random      = 0;

                if ( list_type != 'complete' ) {

                    unpin_ads = 1;
                    badge_ads = 0;

                    var memory_list = $('#product_list_memory_for_random_purposes').val(); // if this is empty means random mode is disable.
                    if ( memory_list != -1 )
                        random = 1;
                    else
                        random = 0;
                }

                $.ajax({
                    type: 'POST',
                    url: (location.hostname === 'wo.escapezoom.local' ? 'http://' : 'https://') + location.hostname + '/web-service/web-service.php',
                    data: {
                        "type": "sort_products_get",
                        "data": {
                            "params"        : params,
                            'image_type'    : 'url',
                            "limit"         : posts_per_page,
                            "page"          : 1,
                            "format"        : 'html_cat',
                            "is_mobile"     : is_mobile,
                            'sort_type'     : sort_type,
                            'url'           : '<?php echo $_SERVER['HTTP_HOST'] ?>',
                            'unpin_ads'     : unpin_ads,
                            'badge_ads'     : badge_ads,
                            'random'        : random,
                            'random_memory' : '',
                            'show_more'     : 0,
                        }
                    },
                    dataType: "json",
                    success: function(data) {
                        $('.load-more').hide();
                        $('ul.shop-page-items').empty();

                        if ( random && !(typeof data.products_id === "undefined") )
                            $('#product_list_memory_for_random_purposes').val(data.products_id.join(","));

                        $(data.products).appendTo('ul.shop-page-items');
                    },
                });
            });
            /**********************************************************/
            $('body').on('click', '#ez_cat_filter_submit', function () {

                if ( $('#non_slider_wrapperx .shop-page-header-sort').find('.orderby-item.is-active').length )
                    $('#non_slider_wrapperx .shop-page-header-sort').find('.orderby-item.is-active').trigger('click', true);
                else
                    $('#non_slider_wrapperx .shop-page-header-sort').find('.orderby-item[data-id=popular]').trigger('click', true);

                if ($( "#advanced-filter-mobile" ).hasClass("show"))
                    $('#ez_cat_filter_item_schedule_today_only_toggle').closest('.form-switch').remove();
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_count', function () {

                if ( $(this).val() == 'همه' )
                    $('#product_list_filter_count').val( -1 );

                else
                    $('#product_list_filter_count').val( $(this).val() );

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_level', function () {
                $('#product_list_filter_level').val( $(this).val() );

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_duration', function () {
                $('#product_list_filter_duration').val( $(this).val() );

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_schedule_today_min', function () {

                var d       = new Date();
                var month   = d.getMonth() + 1;
                var day     = d.getDate();
                var output  = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $(this).val() + ':00';

                $('#product_list_filter_schedule_min').val(Math.floor(new Date(output).getTime() / 1000));
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_schedule_today_max', function () {

                var d       = new Date();
                var month   = d.getMonth() + 1;
                var day     = d.getDate();
                var output  = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $(this).val() + ':00';

                $('#product_list_filter_schedule_max').val(Math.floor(new Date(output).getTime() / 1000));
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_schedule_tomorrow_min', function () {

                var d       = new Date();
                d.setDate(d.getDate() + 1);
                var month   = d.getMonth() + 1;
                var day     = d.getDate();
                var output  = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $(this).val() + ':00';

                $('#product_list_filter_schedule_min').val(Math.floor(new Date(output).getTime() / 1000));
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_schedule_tomorrow_max', function () {

                var d       = new Date();
                d.setDate(d.getDate() + 1);
                var month   = d.getMonth() + 1;
                var day     = d.getDate();
                var output  = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $(this).val() + ':00';

                $('#product_list_filter_schedule_max').val(Math.floor(new Date(output).getTime() / 1000));
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_schedule_overmorrow_min', function () {

                var d       = new Date();
                d.setDate(d.getDate() + 2);
                var month   = d.getMonth() + 1;
                var day     = d.getDate();
                var output  = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $(this).val() + ':00';

                $('#product_list_filter_schedule_min').val(Math.floor(new Date(output).getTime() / 1000));
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_schedule_overmorrow_max', function () {

                var d       = new Date();
                d.setDate(d.getDate() + 2);
                var month   = d.getMonth() + 1;
                var day     = d.getDate();
                var output  = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $(this).val() + ':00';

                $('#product_list_filter_schedule_max').val(Math.floor(new Date(output).getTime() / 1000));
            });
            /**********************************************************/
            $('body').on('click', '#ez_cat_filter_item_schedule_days_wrapper .ez_cat_filter_item_schedule_slider_input_up_arrow', function () {

                var $this   = $(this);
                var cur_val = parseInt($this.siblings('.ez_cat_filter_item_schedule_slider_input').val());

                if ( $('#ez_cat_filter_item_schedule_days').find('.ez_cat_filter_item_schedule_days_item.is-active').length === 0  ) {
                    $('#ez_cat_filter_item_schedule_days_today').click();
                }

                if ( cur_val < 24 ) {
                    $this.siblings('.ez_cat_filter_item_schedule_slider_input').val( cur_val + 1 ).trigger('change');
                }

                $('#ez_cat_filter_submit').click();

            });
            /**********************************************************/
            $('body').on('click', '#ez_cat_filter_item_schedule_days_wrapper .ez_cat_filter_item_schedule_slider_input_down_arrow', function () {

                var $this   = $(this);
                var cur_val = parseInt($this.siblings('.ez_cat_filter_item_schedule_slider_input').val());

                if ( cur_val > (new Date()).getHours() )
                    $this.siblings('.ez_cat_filter_item_schedule_slider_input').val( cur_val - 1 ).trigger('change');

                $('#ez_cat_filter_submit').click();

            });
            /**********************************************************/
            $('body').on('click', '#ez_cat_filter_item_count_wrapper .ez_cat_filter_item_schedule_slider_input_up_arrow', function () {

                var $this   = $(this);
                var cur_val = $this.siblings('.ez_cat_filter_item_schedule_slider_input').val();

                if ( cur_val == 'همه' ) {
                    $this.siblings('.ez_cat_filter_item_schedule_slider_input').val( 1 ).trigger('change');

                } else {

                    cur_val = parseInt(cur_val);

                    if ( cur_val < 16 ) {
                        $this.siblings('.ez_cat_filter_item_schedule_slider_input').val( cur_val + 1 ).trigger('change');
                    }
                }
            });
            /**********************************************************/
            $('body').on('click', '#ez_cat_filter_item_count_wrapper .ez_cat_filter_item_schedule_slider_input_down_arrow', function () {

                var $this   = $(this);
                var cur_val = parseInt($this.siblings('.ez_cat_filter_item_schedule_slider_input').val());

                if ( cur_val > 1 ) {
                    $this.siblings('.ez_cat_filter_item_schedule_slider_input').val( cur_val - 1 ).trigger('change');
                } else {
                    $this.siblings('.ez_cat_filter_item_schedule_slider_input').val( 'همه' ).trigger('change');
                }
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_age', function () {
                $('#product_list_filter_age').val( $(this).val() );

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            cities = [];
            $('body').on('change', '.ez_cat_filter_item_city_list', function () {

                if(this.checked) {
                    cities.push($(this).val());
                } else {
                    var index = cities.indexOf($(this).val());
                    if (index !== -1)
                        cities.splice(index, 1);
                }

                $('#product_list_filter_city_list').val( cities.join(",") == '' ? -1 : cities.join(",") );

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            tags = [];
            $('body').on('change', '.ez_cat_filter_item_tag_list', function () {

                if(this.checked) {
                    tags.push($(this).val());
                } else {
                    var index = tags.indexOf($(this).val());
                    if (index !== -1)
                        tags.splice(index, 1);
                }

                $('#product_list_filter_tag_list').val( tags.join(",") == '' ? -1 : tags.join(",") );

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            function ez_cat_filter_item_price () {
                var $slider = $('#ez_cat_filter_item_price_slider').get(0);
                var gap     = 10000;
                var minVal  = 50000;
                var maxVal  = 350000;

                if ($slider ) {
                    noUiSlider.create($slider, {
                        start: [minVal, maxVal],
                        connect: true,
                        step: gap,
                        direction: 'rtl',
                        range: {
                            'min': minVal,
                            'max': maxVal
                        },
                        pips: {
                            mode: 'range',
                            density: gap
                        },
                        format: wNumb({
                            decimals: 0,
                            thousand: '،'
                        }),
                        ariaFormat: wNumb({
                            decimals: 0,
                            thousand: '،'
                        }),
                    });
                    $slider.noUiSlider.on('update', function (values, handle) {
                        var value = values[handle];

                        if (handle){
                            $('#ez_cat_filter_item_price_max').text(value);
                            $('#product_list_filter_price_max').val(value.split('،').join(''));
                        }
                        else {
                            $('#ez_cat_filter_item_price_min').text(value);
                            $('#product_list_filter_price_min').val(value.split('،').join(''));
                        }

                        $('.noUi-value-large').text(minVal);
                        $('.noUi-value-large:last-child').text(maxVal);

                        $('#ez_cat_filter_submit').click();
                    });
                }
            } ez_cat_filter_item_price();
            /**********************************************************/
            $('body').on('click', '.ez_cat_filter_item_schedule_days_item', function () {

                var $day = $(this).attr('id').split('ez_cat_filter_item_schedule_days_')[1];

                $('.ez_cat_filter_item_schedule_slider_wrapper').css('display', 'none');
                $('#ez_cat_filter_item_schedule_count_slider_wrapper').css('display', 'flex');
                $('.ez_cat_filter_item_schedule_days_item').removeClass('is-active');

                $('#ez_cat_filter_item_schedule_' + $day + '_slider_wrapper').css('display', 'flex');
                $(this).addClass('is-active');

                var $plus = 0;
                if ( $day == 'today' ) $plus = 0;
                if ( $day == 'tomorrow' ) $plus = 1;
                if ( $day == 'overmorrow' ) $plus = 2;

                var d = new Date();
                d.setDate(d.getDate() + $plus);
                var month       = d.getMonth() + 1;
                var day         = d.getDate();
                var date_min    = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $('#ez_cat_filter_item_schedule_' + $day + '_min').val() + ':00';
                var date_max    = d.getFullYear() + '/' + (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + ' ' + $('#ez_cat_filter_item_schedule_' + $day + '_max').val() + ':00';

                $('#product_list_filter_schedule_min').val(Math.floor(new Date(date_min).getTime() / 1000));
                $('#product_list_filter_schedule_max').val(Math.floor(new Date(date_max).getTime() / 1000));

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            $('body').on('change', '#ez_cat_filter_item_schedule_today_only_toggle', function () {

                if($("#ez_cat_filter_item_schedule_today_only_toggle").prop('checked') == true) {
                    $('#ez_cat_filter_item_schedule_days_today').click();

                } else {
                    $('#product_list_filter_schedule_min').val(-1);
                    $('#product_list_filter_schedule_max').val(-1);
                }

                $('#ez_cat_filter_submit').click();
            });
            /**********************************************************/
            $('body').on('click', '#non_slider_wrapperx .orderby-item', function () {
                $('#sortfilteres').modal('hide');
            });
            /**********************************************************/
            $('body').on('click', '#product_list_mobile_get_res_btn', function () {
                $('#ez_cat_filter_wrapper .btn-close').click();
            });
        });
        /**********************************************************/
        function isScrolledIntoView(elem) {
            var docViewTop = $(window).scrollTop();
            var docViewBottom = docViewTop + $(window).height();
            var elemTop = $(elem).offset().top - 100;
            var elemBottom = elemTop + $(elem).height();

            return ( elemBottom <= docViewBottom );
        }
    </script>

    <?php
}
