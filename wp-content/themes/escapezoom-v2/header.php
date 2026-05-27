<?php
global $categories_menu;
$categories = $categories_menu;
$cities_data = get_cities_with_city_id();
$cities_menu = [];
foreach ($cities_data as $city) {
    $city_id = (int) $city['city_id'];
    $final_url = '';
    if ( get_post_status( $city_id ) === 'publish' && get_post_type( $city_id ) === 'page' ) {
        $final_url = get_the_permalink( $city_id );
    } else {
        $term_link = get_term_link( $city_id, 'product_cat' );
        if ( ! is_wp_error( $term_link ) ) {
            $final_url = $term_link;
        } else {
            $final_url = '#';
        }
        
    }
    $cities_menu[] = [
        'name'        => $city['name'],
        'eng_name'    => $city['slug'],
        'is_featured' => $city['is_featured'],
        'slug'        => esc_url( $final_url ),
        'id'          => $city_id
    ];
}
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['ez_pending_cookie'])) {
    $cookie_data = $_SESSION['ez_pending_cookie'];
    setcookie('ez_user_searches', $cookie_data, time() + (30 * 24 * 60 * 60), '/', '', false, false);
    unset($_SESSION['ez_pending_cookie']);
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth" dir="rtl" style="margin: 0 !important;">

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="theme-color" content="#fd7013">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="اسکیپ زوم">
    <meta name="application-name" content="اسکیپ زوم">
    <meta name="msapplication-TileColor" content="#fd7013">
    <meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/assets/images/pwa/icon-144x144.png">
    <meta name="msapplication-config" content="<?php echo get_template_directory_uri(); ?>/browserconfig.xml">

    <!-- PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/images/pwa/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/assets/images/pwa/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/images/pwa/apple-touch-icon.png">
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/pwa/favicon-32x32.png">

    <!-- iOS Splash Screens - Optional but recommended -->
    <link rel="apple-touch-startup-image" href="<?php echo get_template_directory_uri(); ?>/assets/images/pwa/icon-512x512.png">

    <link rel="manifest" href="<?php echo esc_url( home_url( '/manifest.json' ) ); ?>">

    <?php
    if (
        function_exists( 'ez_ajax_boot_print_inline' )
        && function_exists( 'ez_booking_gateway_enabled' )
        && function_exists( 'ez_ajax_should_boot' )
        && ez_booking_gateway_enabled()
        && ez_ajax_should_boot()
    ) {
        ez_ajax_boot_print_inline();
    }
    ?>

    <?php if ($_SERVER['SERVER_NAME'] == 'escapezoom.co' || $_SERVER['SERVER_NAME'] == 'www.escapezoom.co') { ?>
        <!-- <script>
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-MK2B3ZFF');
        </script> -->
    <?php } else { ?>
        <!-- <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-WBCCS78');
        </script> -->
    <?php } ?>

    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php if ($_SERVER['SERVER_NAME'] == 'escapezoom.co' || $_SERVER['SERVER_NAME'] == 'www.escapezoom.co') {
        echo '<meta name="googlebot" content="noindex">';
        add_filter('wp_robots', function ($robots) {
            $robots['noindex'] = true;
            $robots['nofollow'] = true;
            return $robots;
        });
        /*echo '<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/> <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script> <script> const swiperDefaultSlider = new Swiper(".topescaperoom", { slidesPerView: "auto", spaceBetween: 10, freeMode: true, }); </script>';*/
    } else {
        add_filter('wp_robots', function ($robots) {
            $robots['index'] = true;
            $robots['follow'] = true;
            return $robots;
        });
    }

    if (is_page('408') && is_paged()) {
        echo '
            <link rel="canonical" href="https://escapezoom.ir/" />
            <script type="application/ld+json">
                {
                "@context": "http://schema.org/",
                "@type": "WebSite",
                "url": "https://escapezoom.ir/",
                "potentialAction": {
                "@type": "SearchAction",
                "target": "https://escapezoom.ir/shop/?s={search_term_string}",
                "query-input": "required name=search_term_string"
                }
                }
            </script>
            ';
    }

    if ($_SERVER['SERVER_NAME'] == 'escapezoom.co' || $_SERVER['SERVER_NAME'] == 'www.escapezoom.co') { ?>
        <!--<script>-->
        <!--    !function (t, e, n) {-->
        <!--        t.yektanetAnalyticsObject = n, t[n] = t[n] || function () {-->
        <!--            t[n].q.push(arguments)-->
        <!--        }, t[n].q = t[n].q || [];-->
        <!--        var a = new Date,-->
        <!--            r = a.getFullYear().toString() + "0" + a.getMonth() + "0" + a.getDate() + "0" + a.getHours(),-->
        <!--            c = e.getElementsByTagName("script")[0], s = e.createElement("script");-->
        <!--        s.id = "ua-script-kziWIhHH";-->
        <!--        s.dataset.analyticsobject = n;-->
        <!--        s.async = 1;-->
        <!--        s.type = "text/javascript";-->
        <!--        s.src = "https://cdn.yektanet.com/rg_woebegone/scripts_v3/kziWIhHH/rg.complete.js?v=" + r, c.parentNode.insertBefore(s, c)-->
        <!--    }(window, document, "yektanet");-->
        <!--</script>-->
    <?php } ?>

    <?php wp_head(); ?>

    <!-- Theme JavaScript Variables -->
    <script>
        var theme_url = '<?php echo get_template_directory_uri(); ?>';
        var ezSearchAjaxUrl = '<?php echo home_url('/wp-content/themes/escapezoom-v2/template/func/main-search-ajax.php'); ?>';

        // Cities data for modal
        var citiesData = <?php echo json_encode($cities_menu); ?>;
    </script>
    <script type="text/javascript" src="https://s1.mediaad.org/serve/115280/retargeting.js"async></script>
</head>

<body <?php body_class('overflow-x-hidden'); ?>>

    <?php if ($_SERVER['SERVER_NAME'] == 'escapezoom.co' || $_SERVER['SERVER_NAME'] == 'www.escapezoom.co') { ?>
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MK2B3ZFF"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    <?php } else { ?>
        <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WBCCS78"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <?php } ?>
    <?php if (! is_wc_login_page()) { ?>

        <header class="container mx-auto max-lg:min-h-[66px] lg:min-h-[75px] relative">
            <div class="px-4 sm:px-6 md:divide-y md:divide-slate-60 md:px-8">
                <nav class="hidden flex-wrap items-center justify-between pt-7 lg:flex">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 xl:gap-8">
                        <!-- Logo -->
                        <a class="flex items-center gap-4.5" href="<?php bloginfo('url'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="113" fill="none" viewBox="0 0 113 29" class="w-40 2xl:w-44">
                                <path class="fill-primary-500" fill-rule="evenodd" d="M110.388 23.144c-.991 0-1.771-.832-1.771-1.81V7.091c0-.98.781-1.811 1.771-1.811.99 0 1.77.832 1.77 1.811v14.243c0 .979-.78 1.81-1.77 1.81Zm-5.235 0H90.997c-.035 0-.069 0-.097-.002h-.005a6.754 6.754 0 0 1-4.632-2.029c-3.211 2.644-9.819 3.029-12.737-.207a6.753 6.753 0 0 1-5.029 2.238h-.034a6.753 6.753 0 0 1-5.029-2.238 6.757 6.757 0 0 1-5.03 2.238H46.643c-.962 0-1.77-.771-1.77-1.741 0-.97.808-1.742 1.77-1.742h11.761a3.289 3.289 0 0 0 3.288-3.288V13.94l.001-.049v-.019l.001-.014.002-.035.001-.014.002-.03.002-.011.001-.012a.29.29 0 0 1 .004-.031l.002-.015.004-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.026-.002.027-.001.037-.002h.006c.014-.001.028-.002.037-.001l.02-.001h.058l.042.001.032.001.008.001a.434.434 0 0 1 .054.003l.013.001.022.002.011.001a.236.236 0 0 0 .021.002l.014.002a1.72 1.72 0 0 1 1.468 1.397l.007.041.006.042.004.028.001.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016.001.01v.009l.001.026v2.472a3.289 3.289 0 0 0 3.288 3.288h.034a3.289 3.289 0 0 0 3.288-3.288v-2.491l.001-.01.001-.014.002-.035v-.014l.003-.03.001-.011.002-.012.003-.031.002-.015.005-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.025-.002.028-.001.036-.002h.007a.233.233 0 0 1 .036-.001l.02-.001h.059l.042.001.032.001.007.001a.459.459 0 0 1 .054.003l.014.001.022.002.011.001a.206.206 0 0 0 .021.002 1.72 1.72 0 0 1 1.482 1.399l.007.041.006.042.003.028.002.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016v.01l.001.009v.026l.001.014v2.458c0 4.429 6.896 3.85 9.115 1.631a5.362 5.362 0 0 0-3.774-9.155h-.021l-.023.001a5.342 5.342 0 0 0-3.767 1.57.563.563 0 0 1-.146.106 1.764 1.764 0 0 1-2.483-2.483.581.581 0 0 1 .105-.147L81.668.503a1.788 1.788 0 0 1 2.524 0 1.79 1.79 0 0 1 0 2.525l-1.471 1.471-.007.006-.863.864a8.938 8.938 0 0 1 6.643 13.01 3.276 3.276 0 0 0 2.177 1.255 1.79 1.79 0 0 1 .326-.031h14.156c.974 0 1.77.798 1.77 1.771 0 .974-.796 1.77-1.77 1.77Zm-63.937-13.13a1.952 1.952 0 1 1 0-3.904 1.952 1.952 0 0 1 0 3.904Zm-15.893 4.912-1.054-.188.094-.527a4.242 4.242 0 0 1 4.175-3.491h.535l-3.656 3.679-.094.527Zm7.077 3.871.006-.005a5.444 5.444 0 0 0-3.868-9.275 5.444 5.444 0 1 0 1.47 10.688l.078-.022 2.095 3.628h-3.643c-4.888 0-8.852-3.963-8.852-8.85a8.852 8.852 0 0 1 17.702 0v12.79l-5.103-8.839.115-.115ZM9.162 23.811a8.821 8.821 0 0 1-5.443-1.871v5c0 .938-.766 1.704-1.704 1.704A1.707 1.707 0 0 1 .312 26.94V14.961a8.852 8.852 0 0 1 17.702 0c0 4.887-3.965 8.85-8.852 8.85Zm0-14.294a5.444 5.444 0 0 0-3.868 9.275l.03.03.002.002a5.444 5.444 0 1 0 3.836-9.307Zm32.054 1.348c.938 0 1.703.766 1.703 1.703v14.371c0 .937-.765 1.703-1.703 1.703a1.706 1.706 0 0 1-1.703-1.703V12.568c0-.938.765-1.703 1.703-1.703Z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M47.352 24.8c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm10.511 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.921-2.052 2.052-2.052Zm4.203 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Z" clip-rule="evenodd" class="fill-slate-800"></path>
                            </svg>
                        </a>
                        <!-- Logo -->

                        <!-- Nav Menu - Mega Menu System -->
                        <?php
                        // استفاده از سیستم مگامنو برای منوی هدر
                        if (function_exists('get_option') && !empty(get_option('ez_mega_menu_header'))) {
                            get_template_part('template/layout/navbar-megamenu');
                        } else {
                            // fallback به منوی قدیمی
                            get_template_part('template/layout/navbar', null, ['categories' => $categories]);
                        }
                        ?>
                        <!-- Nav Menu -->

                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-5">

                        <!-- City Selector - Desktop -->
                        <div class="max-lg:hidden city-selection-wrapper border-l pl-4 py-2.5 ml-2 relative group">
                            <div class="flex items-center cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path
                                        d="M9.99967 1.66669C6.33301 1.66669 3.33301 4.66669 3.33301 8.33335C3.33301 12.8334 9.16634 17.9167 9.41634 18.1667C9.58301 18.25 9.83301 18.3334 9.99967 18.3334C10.1663 18.3334 10.4163 18.25 10.583 18.1667C10.833 17.9167 16.6663 12.8334 16.6663 8.33335C16.6663 4.66669 13.6663 1.66669 9.99967 1.66669ZM9.99967 16.4167C8.24967 14.75 4.99967 11.1667 4.99967 8.33335C4.99967 5.58335 7.24967 3.33335 9.99967 3.33335C12.7497 3.33335 14.9997 5.58335 14.9997 8.33335C14.9997 11.0834 11.7497 14.75 9.99967 16.4167ZM9.99967 5.00002C8.16634 5.00002 6.66634 6.50002 6.66634 8.33335C6.66634 10.1667 8.16634 11.6667 9.99967 11.6667C11.833 11.6667 13.333 10.1667 13.333 8.33335C13.333 6.50002 11.833 5.00002 9.99967 5.00002ZM9.99967 10C9.08301 10 8.33301 9.25002 8.33301 8.33335C8.33301 7.41669 9.08301 6.66669 9.99967 6.66669C10.9163 6.66669 11.6663 7.41669 11.6663 8.33335C11.6663 9.25002 10.9163 10 9.99967 10Z"
                                        fill="#90A1B9" />
                                </svg>
                                <p class="ms-2 me-5 text-2xs text-gray-600">انتخاب شهر</p>
                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="6" viewBox="0 0 8 6" fill="none">
                                    <path d="M1 1.5L3.29289 3.79289C3.68342 4.18342 4.31658 4.18342 4.70711 3.79289L7 1.5"
                                        stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>

                            <!-- City Selection Modal -->
                            <div class="hidden group-hover:block absolute top-full left-0 z-50">
                                <div class="border w-[262px] bg-white rounded-br-lg rounded-bl-lg shadow-lg" style="max-height: 450px;">
                                    <div class="py-5 px-3">
                                        <!-- Search Box -->
                                        <div class="relative w-full">
                                            <input id="city-search-input-lg" type="text"
                                                class="w-full h-[38px] border border-gray-200 px-1.5 rounded-lg py-4 ps-4 text-sm focus:outline-none focus:border-primary-500"
                                                placeholder="جستجوی شهر...">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-2.5 text-gray-400"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                        </div>
                                        
                                        <div class="w-full h-[1px] bg-[#E4EBF0] my-4"></div>

                                        <!-- حالت کروسل (نمایش پیش‌فرض) -->
                                        <div id="city-carousel-mode-lg">
                                            <div class="flex items-center justify-between mb-3 px-1">
                                                <span class="text-xs text-gray-500 font-semibold">انتخاب شهر</span>
                                                <div class="flex gap-1">
                                                    <button id="city-prev-btn-lg" class="p-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                                                    </button>
                                                    <button id="city-next-btn-lg" class="p-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="overflow-hidden relative w-full">
                                                <div id="city-carousel-track-lg" class="flex transition-transform duration-300 ease-in-out" style="transform: translateX(0%);">
                                                    <?php 
                                                    // گروه بندی شهرها به دسته های 8 تایی
                                                    // $cities_menu آرایه کل شهرهاست که در بالای header.php تعریف شده
                                                    $city_chunks = array_chunk($cities_menu, 8);
                                                    
                                                    foreach ($city_chunks as $chunk): ?>
                                                        <div class="w-full flex-shrink-0 grid grid-cols-2 gap-2 px-1 items-start">
                                                            <?php foreach ($chunk as $city): ?>
                                                                <a href="<?= esc_url( $city['slug'] ) ?>" 
                                                                class="flex items-center justify-center text-xs text-slate-700 bg-gray-50 border border-gray-100 hover:bg-EzOrange hover:text-primary-500 hover:border-EzOrange transition-colors rounded-lg px-2 py-2 text-center truncate">
                                                                    <?php echo esc_html($city['name']); ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- اندیکاتور صفحات (اختیاری) -->
                                            <div class="flex justify-center gap-1 mt-3" id="city-carousel-dots-lg">
                                                <?php foreach ($city_chunks as $index => $chunk): ?>
                                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-200 <?php echo $index === 0 ? '!bg-EzOrange' : ''; ?>"></div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <!-- حالت نتایج جستجو (پیش‌فرض مخفی) -->
                                        <div id="city-search-mode-lg" class="hidden">
                                            <div class="mb-2 text-xs text-gray-500 px-1">نتایج جستجو</div>
                                            <div id="city-search-results-lg" class="max-h-[220px] overflow-y-auto flex flex-col gap-1 pr-1 custom-scrollbar">
                                                <!-- نتایج با جاوااسکریپت اینجا تزریق میشن -->
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- City Selector - Desktop -->

                        <!-- Search Button Container - Desktop -->
                        <div class="max-lg:hidden relative" id="search-container-desktop">
                            <button type="button" id="search-btn-desktop"
                                class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-white text-gray-900 border border-gray-100 hover:bg-button-gradient focus-visible:bg-button-gradient p-2 shadow-wrapper h-11.5 min-w-12 rounded-lg hover:shadow-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                    fill="none" class="icon-default-desktop block">
                                    <path
                                        d="M8.53418 0.751953C10.5859 0.712459 12.5733 1.46913 14.0791 2.86328C15.5847 4.25732 16.4918 6.18007 16.6104 8.22852C16.7243 10.2001 16.0966 12.1387 14.8594 13.6709L17.0107 15.8213L17.0137 15.8242L17.0684 15.8857C17.1873 16.0358 17.2517 16.223 17.25 16.416C17.2481 16.6366 17.1599 16.8479 17.0039 17.0039C16.8479 17.1599 16.6366 17.2481 16.416 17.25C16.1954 17.2519 15.9829 17.1669 15.8242 17.0137L13.6709 14.8604C12.1387 16.0974 10.1999 16.7243 8.22852 16.6104C6.18007 16.4918 4.25732 15.5847 2.86328 14.0791C1.46913 12.5733 0.712459 10.5859 0.751953 8.53418C0.791453 6.48244 1.62315 4.52528 3.07422 3.07422C4.52528 1.62315 6.48244 0.791453 8.53418 0.751953ZM8.6875 2.43262C7.02875 2.43262 5.43756 3.09173 4.26465 4.26465C3.09173 5.43756 2.43262 7.02875 2.43262 8.6875C2.43274 10.3461 3.09186 11.9366 4.26465 13.1094C5.43756 14.2823 7.02875 14.9414 8.6875 14.9414C10.3461 14.9413 11.9366 14.2822 13.1094 13.1094C14.2822 11.9366 14.9413 10.3461 14.9414 8.6875C14.9414 7.02875 14.2823 5.43756 13.1094 4.26465C11.9366 3.09186 10.3461 2.43274 8.6875 2.43262Z"
                                        fill="#0F172B" stroke="#0F172B" stroke-width="0.5" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                    fill="none" class="icon-active-desktop hidden">
                                    <path
                                        d="M8.53418 0.751953C10.5859 0.712459 12.5733 1.46913 14.0791 2.86328C15.5847 4.25732 16.4918 6.18007 16.6104 8.22852C16.7243 10.2001 16.0966 12.1387 14.8594 13.6709L17.0107 15.8213L17.0137 15.8242L17.0684 15.8857C17.1873 16.0358 17.2517 16.223 17.25 16.416C17.2481 16.6366 17.1599 16.8479 17.0039 17.0039C16.8479 17.1599 16.6366 17.2481 16.416 17.25C16.1954 17.2519 15.9829 17.1669 15.8242 17.0137L13.6709 14.8604C12.1387 16.0974 10.1999 16.7243 8.22852 16.6104C6.18007 16.4918 4.25732 15.5847 2.86328 14.0791C1.46913 12.5733 0.712459 10.5859 0.751953 8.53418C0.791453 6.48244 1.62315 4.52528 3.07422 3.07422C4.52528 1.62315 6.48244 0.791453 8.53418 0.751953ZM8.6875 2.43262C7.02875 2.43262 5.43756 3.09173 4.26465 4.26465C3.09173 5.43756 2.43262 7.02875 2.43262 8.6875C2.43274 10.3461 3.09186 11.9366 4.26465 13.1094C5.43756 14.2823 7.02875 14.9414 8.6875 14.9414C10.3461 14.9413 11.9366 14.2822 13.1094 13.1094C14.2822 11.9366 14.9413 10.3461 14.9414 8.6875C14.9414 7.02875 14.2823 5.43756 13.1094 4.26465C11.9366 3.09186 10.3461 2.43274 8.6875 2.43262Z"
                                        fill="#FF6900" stroke="#FF6900" stroke-width="0.5" />
                                </svg>
                            </button>
                        </div>
                        <!-- Search Button Container - Desktop -->
                        <a class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-white text-gray-900 border border-gray-100 hover:bg-button-gradient focus-visible:bg-button-gradient p-2 shadow-wrapper h-11.5 min-w-12 rounded-lg hover:shadow-none"
                            href="<?= home_url('/panel/'); ?>">
                            <span class="truncate">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24">
                                    <circle cx="11.579" cy="7.278" r="4.778" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round"></circle>
                                    <path clip-rule="evenodd"
                                        d="M4 18.701a2.215 2.215 0 01.22-.97c.457-.915 1.748-1.4 2.819-1.62a16.778 16.778 0 012.343-.33 25.04 25.04 0 014.385 0c.787.056 1.57.166 2.343.33 1.07.22 2.361.659 2.82 1.62a2.27 2.27 0 010 1.95c-.459.96-1.75 1.4-2.82 1.61-.772.172-1.555.286-2.343.34-1.188.1-2.38.118-3.57.054-.275 0-.54 0-.815-.055a15.417 15.417 0 01-2.334-.338c-1.08-.21-2.361-.65-2.828-1.611A2.28 2.28 0 014 18.7z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                    </path>
                                </svg>
                            </span>
                        </a>

                    </div>

                </nav>
                <!-- mobile header start -->
                <nav class="bg-white fixed z-100 mobile-navbar">
                    <div class="fixed bg-white top-0 w-full right-0 z-50">
                        <div
                            class="lg:hidden px-5 py-3 flex items-center justify-between text-slate-800 transition-all z-90 shadow-[0_2px_4px_0_rgba(9,25,45,0.13)]">
                            <div class="flex items-center">
                                <div class="cursor-pointer max-md:ml-4" id="open-mobile-nav2">
                                    <svg class="burger-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M3.05556 18C2.75648 18 2.50597 17.896 2.304 17.688C2.10204 17.48 2.00071 17.2229 2 16.9167C1.9993 16.6104 2.10063 16.3533 2.304 16.1453C2.50737 15.9373 2.75789 15.8333 3.05556 15.8333H19.9444C20.2435 15.8333 20.4944 15.9373 20.6971 16.1453C20.8997 16.3533 21.0007 16.6104 21 16.9167C20.9993 17.2229 20.898 17.4803 20.696 17.6891C20.494 17.8978 20.2435 18.0014 19.9444 18H3.05556ZM3.05556 12.5833C2.75648 12.5833 2.50597 12.4793 2.304 12.2713C2.10204 12.0633 2.00071 11.8062 2 11.5C1.9993 11.1938 2.10063 10.9367 2.304 10.7287C2.50737 10.5207 2.75789 10.4167 3.05556 10.4167H19.9444C20.2435 10.4167 20.4944 10.5207 20.6971 10.7287C20.8997 10.9367 21.0007 11.1938 21 11.5C20.9993 11.8062 20.898 12.0637 20.696 12.2724C20.494 12.4811 20.2435 12.5848 19.9444 12.5833H3.05556ZM3.05556 7.16666C2.75648 7.16666 2.50597 7.06266 2.304 6.85466C2.10204 6.64666 2.00071 6.38955 2 6.08333C1.9993 5.77711 2.10063 5.52 2.304 5.312C2.50737 5.104 2.75789 5 3.05556 5H19.9444C20.2435 5 20.4944 5.104 20.6971 5.312C20.8997 5.52 21.0007 5.77711 21 6.08333C20.9993 6.38955 20.898 6.64703 20.696 6.85575C20.494 7.06447 20.2435 7.16811 19.9444 7.16666H3.05556Z"
                                            fill="#0F172B" />
                                    </svg>
                                    <svg class="close-icon hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6L18 18" stroke="#0F172B" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <a href="<?= home_url(); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="126" height="42" viewBox="0 0 126 42"
                                        fill="none">
                                        <g clip-path="url(#clip0_46903_16482)">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M29.2933 19.5413C29.672 17.445 31.4962 15.9195 33.6298 15.9195H34.1846V17.029H33.6298C32.035 17.029 30.6695 18.1704 30.3868 19.7386L30.2907 20.2827L29.1973 20.0907L29.2933 19.5413Z"
                                                fill="#FD7013" />
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M117.471 27.6223C117.471 22.699 117.471 17.7704 117.471 12.8418C117.471 11.8283 118.282 10.9642 119.306 10.9642C120.336 10.9642 121.146 11.8283 121.146 12.8418C121.146 17.7704 121.146 22.699 121.146 27.6223C121.146 28.6411 120.336 29.5052 119.306 29.5052C118.277 29.5052 117.471 28.6411 117.471 27.6223ZM67.177 19.8293C67.177 19.824 67.177 19.8187 67.177 19.8187C67.177 19.808 67.177 19.7973 67.177 19.7867C67.177 19.7813 67.177 19.776 67.1823 19.7707V19.76C67.1823 19.7493 67.1823 19.7386 67.1823 19.728L67.1876 19.712C67.1876 19.7013 67.1876 19.6906 67.193 19.68C67.193 19.6746 67.193 19.6693 67.193 19.6693C67.193 19.6586 67.1983 19.648 67.1983 19.6373C67.337 18.8799 67.9557 18.2878 68.7238 18.1865C68.7398 18.1865 68.7558 18.1865 68.7718 18.1811C68.7825 18.1811 68.7932 18.1811 68.7878 18.1811C68.7985 18.1811 68.8092 18.1811 68.8145 18.1811C68.8305 18.1758 68.8465 18.1758 68.8465 18.1758C68.8679 18.1758 68.8892 18.1758 68.8839 18.1758C68.8892 18.1758 68.8945 18.1758 68.8892 18.1758C68.9052 18.1758 68.9212 18.1758 68.9265 18.1758C68.9319 18.1758 68.9425 18.1758 68.9479 18.1758C68.9692 18.1758 68.9905 18.1758 69.0119 18.1758C69.0225 18.1758 69.0385 18.1758 69.0545 18.1758C69.0705 18.1758 69.0865 18.1758 69.0865 18.1758C69.0919 18.1758 69.0972 18.1758 69.0919 18.1758C69.1079 18.1758 69.1185 18.1758 69.1292 18.1811C69.1399 18.1811 69.1452 18.1811 69.1506 18.1811H69.1612C69.1719 18.1811 69.1772 18.1811 69.1879 18.1811L69.1986 18.1865C69.2039 18.1865 69.2146 18.1865 69.2199 18.1865C69.2252 18.1865 69.2306 18.1865 69.2359 18.1865C69.9987 18.2878 70.6227 18.8799 70.7561 19.6373C70.7614 19.6533 70.7614 19.6693 70.7668 19.68C70.7668 19.696 70.7668 19.712 70.7721 19.7226C70.7721 19.7333 70.7721 19.744 70.7721 19.7547C70.7774 19.76 70.7774 19.76 70.7774 19.7653C70.7774 19.7813 70.7774 19.7973 70.7774 19.8027C70.7774 19.808 70.7828 19.8187 70.7828 19.824V19.84V19.8453C70.7828 19.8507 70.7828 19.8613 70.7828 19.8667V19.8773V19.8827C70.7828 19.8933 70.7828 19.904 70.7828 19.9093V19.9253C70.7881 19.936 70.7881 19.9413 70.7881 19.952C70.7881 20.7948 70.7881 21.6376 70.7881 22.475C70.7881 24.3632 72.3136 25.8887 74.1965 25.8887H74.2339C76.1168 25.8887 77.6423 24.3632 77.6423 22.475C77.6423 21.6376 77.6423 20.7948 77.6423 19.952C77.6423 19.936 77.6476 19.92 77.6476 19.8987V19.8933V19.8827C77.6476 19.872 77.6476 19.8667 77.6476 19.8667C77.6476 19.856 77.6476 19.84 77.6476 19.8293C77.6476 19.824 77.6476 19.8187 77.6476 19.8187C77.653 19.808 77.653 19.7973 77.653 19.7867C77.653 19.7813 77.653 19.776 77.653 19.7707V19.76C77.6583 19.7493 77.6583 19.7386 77.6583 19.728V19.712C77.6636 19.7013 77.6636 19.6906 77.6636 19.68C77.669 19.6746 77.669 19.6693 77.669 19.6693C77.669 19.6586 77.669 19.648 77.6743 19.6373C77.8077 18.8799 78.4317 18.2878 79.1945 18.1865C79.2105 18.1865 79.2265 18.1865 79.2425 18.1811C79.2532 18.1811 79.2638 18.1811 79.2638 18.1811C79.2745 18.1811 79.2798 18.1811 79.2905 18.1811C79.3065 18.1758 79.3172 18.1758 79.3172 18.1758C79.3385 18.1758 79.3598 18.1758 79.3545 18.1758C79.3652 18.1758 79.3705 18.1758 79.3652 18.1758C79.3759 18.1758 79.3919 18.1758 79.4025 18.1758C79.4079 18.1758 79.4132 18.1758 79.4239 18.1758C79.4452 18.1758 79.4612 18.1758 79.4825 18.1758C79.4985 18.1758 79.5145 18.1758 79.5252 18.1758C79.5412 18.1758 79.5572 18.1758 79.5572 18.1758C79.5679 18.1758 79.5732 18.1758 79.5679 18.1758C79.5785 18.1758 79.5945 18.1758 79.6052 18.1811C79.6105 18.1811 79.6159 18.1811 79.6212 18.1811H79.6372C79.6426 18.1811 79.6532 18.1811 79.6586 18.1811L79.6692 18.1865C79.6799 18.1865 79.6906 18.1865 79.6959 18.1865C79.6959 18.1865 79.7012 18.1865 79.7066 18.1865C80.4747 18.2878 81.0934 18.8799 81.2321 19.6373C81.2321 19.6533 81.2374 19.6693 81.2374 19.68C81.2428 19.696 81.2428 19.712 81.2428 19.7226C81.2481 19.7333 81.2481 19.744 81.2481 19.7547C81.2481 19.76 81.2481 19.76 81.2481 19.7653C81.2534 19.7813 81.2534 19.7973 81.2534 19.8027C81.2534 19.808 81.2534 19.8187 81.2534 19.824V19.84L81.2588 19.8453C81.2588 19.8507 81.2588 19.8613 81.2588 19.8667V19.8773V19.8827C81.2588 19.8933 81.2588 19.904 81.2588 19.9093V19.9253C81.2588 19.936 81.2588 19.9413 81.2588 19.952C81.2588 20.7948 81.2588 21.6376 81.2588 22.475C81.2588 27.0729 88.417 26.4702 90.716 24.1712C92.8922 21.9949 92.8922 18.4745 90.716 16.2982C89.6758 15.2581 88.2783 14.6767 86.8008 14.6714C86.7955 14.6714 86.7901 14.6714 86.7795 14.6714C86.7741 14.6714 86.7635 14.6714 86.7581 14.6714C85.2859 14.6767 83.8884 15.2581 82.8483 16.2982C82.8056 16.3462 82.7523 16.3782 82.6936 16.4102C82.3682 16.6769 81.9628 16.821 81.5361 16.821C80.528 16.821 79.7066 15.9995 79.7066 14.9914C79.7066 14.5647 79.8506 14.1593 80.1173 13.8339C80.1493 13.7752 80.1813 13.7272 80.2293 13.6792L87.8996 6.00894C88.6197 5.28885 89.7985 5.28885 90.5186 6.00894C91.244 6.72903 91.244 7.90784 90.5186 8.62793L88.9931 10.1535C88.9931 10.1588 88.9877 10.1588 88.9877 10.1588L88.0916 11.0549C90.0705 11.3376 91.9054 12.2497 93.335 13.6792C96.2153 16.5596 96.8767 20.9815 94.9832 24.5606C95.5326 25.27 96.3433 25.7447 97.2448 25.8621C97.3568 25.8407 97.4688 25.8301 97.5808 25.8301C102.349 25.8301 110.276 25.8301 115.039 25.8301C116.053 25.8301 116.879 26.6568 116.879 27.665C116.879 28.6784 116.053 29.5052 115.039 29.5052H97.5808C97.5488 29.5052 97.5115 29.5052 97.4848 29.4999H97.4795C95.6553 29.4465 93.9377 28.6891 92.6682 27.3929C89.3398 30.1399 82.4802 30.54 79.4505 27.1796C78.117 28.6624 76.2288 29.5052 74.2339 29.5052H74.1965C72.2016 29.5052 70.3134 28.6624 68.9799 27.1796C67.641 28.6624 65.7581 29.5052 63.7579 29.5052C62.9098 29.5052 62.067 29.5052 61.2189 29.5052C57.9971 29.5052 54.7754 29.5052 51.5536 29.5052C50.5562 29.5052 49.7188 28.7051 49.7188 27.697C49.7188 26.6888 50.5562 25.8887 51.5536 25.8887C52.4818 25.8887 53.4152 25.8887 54.3433 25.8887C55.7035 25.8887 57.0637 25.8887 58.4239 25.8887C59.3573 25.8887 60.2854 25.8887 61.2189 25.8887C62.067 25.8887 62.9098 25.8887 63.7579 25.8887C65.6408 25.8887 67.1716 24.3632 67.1716 22.475C67.1716 21.6376 67.1716 20.7948 67.1716 19.952C67.1716 19.936 67.1716 19.92 67.1716 19.8987V19.8933V19.8827C67.1716 19.872 67.1716 19.8667 67.1716 19.8667C67.1716 19.856 67.1716 19.84 67.177 19.8293Z"
                                                fill="#FD7013" />
                                            <path
                                                d="M72.1263 34.5298C73.3017 34.5298 74.2546 33.577 74.2546 32.4016C74.2546 31.2262 73.3017 30.2733 72.1263 30.2733C70.9509 30.2733 69.998 31.2262 69.998 32.4016C69.998 33.577 70.9509 34.5298 72.1263 34.5298Z"
                                                fill="#09192D" />
                                            <path
                                                d="M76.4896 34.5298C77.665 34.5298 78.6179 33.577 78.6179 32.4016C78.6179 31.2262 77.665 30.2733 76.4896 30.2733C75.3142 30.2733 74.3613 31.2262 74.3613 32.4016C74.3613 33.577 75.3142 34.5298 76.4896 34.5298Z"
                                                fill="#09192D" />
                                            <path
                                                d="M58.8978 34.5298C60.0732 34.5298 61.0261 33.577 61.0261 32.4016C61.0261 31.2262 60.0732 30.2733 58.8978 30.2733C57.7224 30.2733 56.7695 31.2262 56.7695 32.4016C56.7695 33.577 57.7224 34.5298 58.8978 34.5298Z"
                                                fill="#09192D" />
                                            <path
                                                d="M63.2611 34.5298C64.4365 34.5298 65.3894 33.577 65.3894 32.4016C65.3894 31.2262 64.4365 30.2733 63.2611 30.2733C62.0857 30.2733 61.1328 31.2262 61.1328 32.4016C61.1328 33.577 62.0857 34.5298 63.2611 34.5298Z"
                                                fill="#09192D" />
                                            <path
                                                d="M54.5345 34.5298C55.7099 34.5298 56.6628 33.577 56.6628 32.4016C56.6628 31.2262 55.7099 30.2733 54.5345 30.2733C53.3591 30.2733 52.4062 31.2262 52.4062 32.4016C52.4062 33.577 53.3591 34.5298 54.5345 34.5298Z"
                                                fill="#09192D" />
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M46.3515 16.0688C47.3276 16.0688 48.1224 16.8636 48.1224 17.8344V32.7483C48.1224 33.7244 47.3276 34.5191 46.3515 34.5191C45.3807 34.5191 44.5859 33.7244 44.5859 32.7483V17.8344C44.5859 16.8636 45.3807 16.0688 46.3515 16.0688Z"
                                                fill="#FD7013" />
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M8.38997 27.5636V32.7536C8.38997 33.7244 7.5952 34.5191 6.62441 34.5191C5.64828 34.5191 4.85352 33.7244 4.85352 32.7536V20.32C4.85352 15.2474 8.96604 11.1349 14.0387 11.1349C19.1113 11.1349 23.2239 15.2474 23.2239 20.32C23.2239 25.3927 19.1113 29.5052 14.0387 29.5052C11.9744 29.5052 9.9955 28.8171 8.38997 27.5636ZM10.0595 24.3259C11.121 25.382 12.5452 25.9687 14.0387 25.9687C17.1591 25.9687 19.6927 23.4404 19.6927 20.32C19.6927 17.1996 17.1591 14.6713 14.0387 14.6713C10.9183 14.6713 8.38997 17.1996 8.38997 20.32C8.38997 21.8136 8.97671 23.2324 10.0275 24.2939L10.0595 24.3259Z"
                                                fill="#FD7013" />
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M46.3511 11.1349C47.4713 11.1349 48.3781 12.0417 48.3781 13.1618C48.3781 14.2766 47.4713 15.1834 46.3511 15.1834C45.231 15.1834 44.3242 14.2766 44.3242 13.1618C44.3242 12.0417 45.231 11.1349 46.3511 11.1349Z"
                                                fill="#FD7013" />
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M37.6363 24.2992L37.6417 24.2939C38.6925 23.2324 39.2792 21.8135 39.2792 20.32C39.2792 17.1996 36.7509 14.6713 33.6305 14.6713C30.5101 14.6713 27.9764 17.1996 27.9764 20.32C27.9764 23.4404 30.5101 25.9687 33.6305 25.9687C34.1425 25.9687 34.6546 25.8994 35.156 25.7607L35.236 25.7394L37.4123 29.5052H33.6305C28.5578 29.5052 24.4453 25.3927 24.4453 20.32C24.4453 15.2474 28.5578 11.1349 33.6305 11.1349C38.6978 11.1349 42.8156 15.2474 42.8156 20.32V33.591L37.519 24.4219L37.6363 24.2992Z"
                                                fill="#FD7013" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_46903_16482">
                                                <rect width="126" height="42" fill="white" />
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </a>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-8">
                                <div class="relative group">
                                    <div class="flex items-center city-selection">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                            fill="none">
                                            <path
                                                d="M9.99967 1.66669C6.33301 1.66669 3.33301 4.66669 3.33301 8.33335C3.33301 12.8334 9.16634 17.9167 9.41634 18.1667C9.58301 18.25 9.83301 18.3334 9.99967 18.3334C10.1663 18.3334 10.4163 18.25 10.583 18.1667C10.833 17.9167 16.6663 12.8334 16.6663 8.33335C16.6663 4.66669 13.6663 1.66669 9.99967 1.66669ZM9.99967 16.4167C8.24967 14.75 4.99967 11.1667 4.99967 8.33335C4.99967 5.58335 7.24967 3.33335 9.99967 3.33335C12.7497 3.33335 14.9997 5.58335 14.9997 8.33335C14.9997 11.0834 11.7497 14.75 9.99967 16.4167ZM9.99967 5.00002C8.16634 5.00002 6.66634 6.50002 6.66634 8.33335C6.66634 10.1667 8.16634 11.6667 9.99967 11.6667C11.833 11.6667 13.333 10.1667 13.333 8.33335C13.333 6.50002 11.833 5.00002 9.99967 5.00002ZM9.99967 10C9.08301 10 8.33301 9.25002 8.33301 8.33335C8.33301 7.41669 9.08301 6.66669 9.99967 6.66669C10.9163 6.66669 11.6663 7.41669 11.6663 8.33335C11.6663 9.25002 10.9163 10 9.99967 10Z"
                                                fill="#90A1B9" />
                                        </svg>
                                        <p class="ms-2 me-2">انتخاب شهر</p>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="6" viewBox="0 0 8 6"
                                            fill="none">
                                            <path
                                                d="M1 1.5L3.29289 3.79289C3.68342 4.18342 4.31658 4.18342 4.70711 3.79289L7 1.5"
                                                stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <!-- City Selection Modal -->
                                    <div class="hidden group-hover:block absolute top-full left-5 z-50 mt-[15px]">
                                        <div class="border w-[262px] bg-white rounded-br-lg rounded-bl-lg shadow-lg" style="max-height: 400px;">
                                            <div class="py-5 px-3">
                                                
                                                <!-- Search Box -->
                                                <div class="relative w-full">
                                                    <input id="city-search-input-sm" type="text" 
                                                        class="w-full h-[38px] border border-gray-200 px-1.5 rounded-lg py-4 ps-4 text-sm focus:outline-none focus:border-primary-500" 
                                                        placeholder="جستجوی شهر...">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" class="absolute left-4 top-2.5">
                                                        <path d="M8.97422 15.25C7.73314 15.25 6.51994 14.882 5.48802 14.1925C4.4561 13.503 3.65182 12.523 3.17688 11.3764C2.70194 10.2297 2.57767 8.96805 2.81979 7.75082C3.06192 6.53359 3.65955 5.41549 4.53713 4.53792C5.4147 3.66035 6.5328 3.06271 7.75003 2.82059C8.96726 2.57847 10.229 2.70273 11.3756 3.17767C12.5222 3.65261 13.5022 4.45689 14.1917 5.48881C14.8812 6.52073 15.2492 7.73394 15.2492 8.97501C15.2492 9.79906 15.0869 10.615 14.7716 11.3764C14.4562 12.1377 13.994 12.8294 13.4113 13.4121C12.8286 13.9948 12.1369 14.457 11.3756 14.7724C10.6142 15.0877 9.79827 15.25 8.97422 15.25ZM8.97422 3.95835C7.98531 3.95835 7.01862 4.25159 6.19637 4.801C5.37412 5.35041 4.73326 6.1313 4.35482 7.04493C3.97639 7.95856 3.87737 8.96389 4.07029 9.9338C4.26322 10.9037 4.73942 11.7946 5.43869 12.4939C6.13795 13.1931 7.02886 13.6693 7.99877 13.8623C8.96867 14.0552 9.97401 13.9562 10.8876 13.5777C11.8013 13.1993 12.5822 12.5584 13.1316 11.7362C13.681 10.914 13.9742 9.94725 13.9742 8.95835C13.9742 7.63227 13.4474 6.3605 12.5098 5.42281C11.5721 4.48513 10.3003 3.95835 8.97422 3.95835Z" fill="#889BAD"></path>
                                                        <path d="M16.2496 16.7094C16.1675 16.7098 16.0862 16.6938 16.0103 16.6623C15.9345 16.6308 15.8657 16.5845 15.808 16.5261L12.783 13.6667C12.6726 13.5482 12.6125 13.3915 12.6153 13.2296C12.6182 13.0677 12.6838 12.9132 12.7983 12.7987C12.9128 12.6841 13.0673 12.6186 13.2292 12.6157C13.3911 12.6128 13.5478 12.6729 13.6663 12.7833L16.6913 15.6427C16.8084 15.7599 16.8741 15.9188 16.8741 16.0844C16.8741 16.25 16.8084 16.4089 16.6913 16.5261C16.6336 16.5845 16.5648 16.6308 16.489 16.6623C16.4131 16.6938 16.3318 16.7098 16.2496 16.7094Z" fill="#889BAD"></path>
                                                    </svg>
                                                </div>
                                                
                                                <div class="w-full h-[1px] bg-[#E4EBF0] my-4"></div>

                                                <!-- حالت کروسل (نمایش پیش‌فرض) دسکتاپ -->
                                                <div id="city-carousel-mode-sm">
                                                    <div class="flex items-center justify-between mb-3 px-1">
                                                        <span class="text-xs text-gray-500 font-semibold">انتخاب شهر</span>
                                                        <div class="flex gap-1">
                                                            <button id="city-prev-btn-sm" class="p-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                                                            </button>
                                                            <button id="city-next-btn-sm" class="p-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="overflow-hidden relative w-full">
                                                        <div id="city-carousel-track-sm" class="flex transition-transform duration-300 ease-in-out" style="transform: translateX(0%);">
                                                            <?php 
                                                            // گروه بندی به 8 تایی از لیست کل شهرها
                                                            $city_chunks = array_chunk($cities_menu, 8);
                                                            
                                                            foreach ($city_chunks as $chunk): ?>
                                                                <div class="w-full flex-shrink-0 grid grid-cols-2 gap-2 items-start">
                                                                    <?php foreach ($chunk as $city): ?>
                                                                        <a href="<?= esc_url( $city['slug'] ) ?>" 
                                                                        class="h-[38px] rounded-lg px-2 flex items-center justify-center cursor-pointer bg-gray-50 border border-gray-100 hover:bg-[#EDF2F5] hover:text-primary-500 hover:border-gray-200 transition-colors text-sm truncate">
                                                                            <?php echo esc_html($city['name']); ?>
                                                                        </a>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- اندیکاتور صفحات -->
                                                    <div class="flex justify-center gap-1 mt-3" id="city-carousel-dots-sm">
                                                        <?php foreach ($city_chunks as $index => $chunk): ?>
                                                            <div class="w-1.5 h-1.5 rounded-full bg-gray-200 <?php echo $index === 0 ? '!bg-EzOrange' : ''; ?>"></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <!-- حالت نتایج جستجو (پیش‌فرض مخفی) دسکتاپ -->
                                                <div id="city-search-mode-sm" class="hidden">
                                                    <div class="mb-2 text-xs text-gray-500 px-1">نتایج جستجو</div>
                                                    <div id="city-search-results-sm" class="max-h-[220px] overflow-y-auto flex flex-col gap-1 pr-1 custom-scrollbar">
                                                        <!-- نتایج با جاوااسکریپت در اینجا تزریق می‌شوند -->
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                </div>
                                <button type="button" id="search-btn"
                                    class="flex items-center justify-center relative text-sm font-semibold transition-all duration-300 ease-in-out text-gray-900 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                        fill="none" class="icon-default block">
                                        <path
                                            d="M8.53418 0.751953C10.5859 0.712459 12.5733 1.46913 14.0791 2.86328C15.5847 4.25732 16.4918 6.18007 16.6104 8.22852C16.7243 10.2001 16.0966 12.1387 14.8594 13.6709L17.0107 15.8213L17.0137 15.8242L17.0684 15.8857C17.1873 16.0358 17.2517 16.223 17.25 16.416C17.2481 16.6366 17.1599 16.8479 17.0039 17.0039C16.8479 17.1599 16.6366 17.2481 16.416 17.25C16.1954 17.2519 15.9829 17.1669 15.8242 17.0137L13.6709 14.8604C12.1387 16.0974 10.1999 16.7243 8.22852 16.6104C6.18007 16.4918 4.25732 15.5847 2.86328 14.0791C1.46913 12.5733 0.712459 10.5859 0.751953 8.53418C0.791453 6.48244 1.62315 4.52528 3.07422 3.07422C4.52528 1.62315 6.48244 0.791453 8.53418 0.751953ZM8.6875 2.43262C7.02875 2.43262 5.43756 3.09173 4.26465 4.26465C3.09173 5.43756 2.43262 7.02875 2.43262 8.6875C2.43274 10.3461 3.09186 11.9366 4.26465 13.1094C5.43756 14.2823 7.02875 14.9414 8.6875 14.9414C10.3461 14.9413 11.9366 14.2822 13.1094 13.1094C14.2822 11.9366 14.9413 10.3461 14.9414 8.6875C14.9414 7.02875 14.2823 5.43756 13.1094 4.26465C11.9366 3.09186 10.3461 2.43274 8.6875 2.43262Z"
                                            fill="#0F172B" stroke="#0F172B" stroke-width="0.5" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                        fill="none" class="icon-active hidden">
                                        <path
                                            d="M8.53418 0.751953C10.5859 0.712459 12.5733 1.46913 14.0791 2.86328C15.5847 4.25732 16.4918 6.18007 16.6104 8.22852C16.7243 10.2001 16.0966 12.1387 14.8594 13.6709L17.0107 15.8213L17.0137 15.8242L17.0684 15.8857C17.1873 16.0358 17.2517 16.223 17.25 16.416C17.2481 16.6366 17.1599 16.8479 17.0039 17.0039C16.8479 17.1599 16.6366 17.2481 16.416 17.25C16.1954 17.2519 15.9829 17.1669 15.8242 17.0137L13.6709 14.8604C12.1387 16.0974 10.1999 16.7243 8.22852 16.6104C6.18007 16.4918 4.25732 15.5847 2.86328 14.0791C1.46913 12.5733 0.712459 10.5859 0.751953 8.53418C0.791453 6.48244 1.62315 4.52528 3.07422 3.07422C4.52528 1.62315 6.48244 0.791453 8.53418 0.751953ZM8.6875 2.43262C7.02875 2.43262 5.43756 3.09173 4.26465 4.26465C3.09173 5.43756 2.43262 7.02875 2.43262 8.6875C2.43274 10.3461 3.09186 11.9366 4.26465 13.1094C5.43756 14.2823 7.02875 14.9414 8.6875 14.9414C10.3461 14.9413 11.9366 14.2822 13.1094 13.1094C14.2822 11.9366 14.9413 10.3461 14.9414 8.6875C14.9414 7.02875 14.2823 5.43756 13.1094 4.26465C11.9366 3.09186 10.3461 2.43274 8.6875 2.43262Z"
                                            fill="#FF6900" stroke="#FF6900" stroke-width="0.5" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>
                <!-- mobile header end -->

                <!-- General Overlay (for mobile nav, city selector, etc) -->
                <div id="general-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden"></div>

                <!-- Mobile Menu -->
                <div id="mobile-menu"
                    class="fixed top-0 right-0 h-full w-[290px] bg-white z-[70] transform translate-x-full transition-transform duration-300 ease-out lg:hidden">

                    <!-- Menu Content - Mega Menu System -->
                    <?php
                    // استفاده از سیستم مگامنو برای منوی موبایل
                    if (function_exists('get_option') && !empty(get_option('ez_mega_menu_header'))) {
                        get_template_part('template/layout/mobile-menu-megamenu');
                    } else {
                        // fallback به منوی قدیمی
                    ?>
                        <div class="flex-1 submenu-container mt-16">
                            <!-- Main Menu Items -->
                            <nav id="main-menu" class="py-2 flex flex-col justify-between h-full submenu-item">
                                <div>
                                    <a href="#"
                                        class="menu-item flex items-center justify-between px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100"
                                        data-submenu="escape-room">
                                        <span class="font-medium">اتاق فرار</span>
                                        <svg class="w-4 h-4 text-gray-400 mx-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </a>
                                    <a href="<?= home_url('/city/سینما-ترس/'); ?>"
                                        class="block px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100">سینما
                                        ترس
                                    </a>
                                    <a href="<?= home_url('/city/لیزرتگ/'); ?>"
                                        class="block px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100">لیزرتگ
                                    </a>
                                    <a href="<?= home_url('/city/کافه-بازی/'); ?>"
                                        class="block px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100">کافه
                                        بازی
                                    </a>
                                    <a href="<?= home_url('/blog/'); ?>"
                                        class="block px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100">بلاگ
                                        بازی
                                    </a>
                                    <a href="<?= home_url('/contact/'); ?>"
                                        class="block px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100">تماس
                                        با ما
                                    </a>
                                    <a href="<?= home_url('/about-us/'); ?>"
                                        class="block px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100">درباره
                                        ما
                                    </a>
                                    <a href="<?= home_url('/partnership/'); ?>"
                                        class="menu-item flex items-center justify-between px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors border-b border-gray-100"
                                        data-submenu="cooperation">
                                        <span class="font-medium">همکاری با ما</span>
                                        <svg class="w-4 h-4 text-gray-400 mx-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </a>
                                    <a href="<?= home_url('/terms/'); ?>" class="block px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors">قوانین
                                        و
                                        مقررات
                                    </a>
                                </div>
                            <?php } ?>
                            <!-- Menu Footer -->
                            <div class="mt-auto">
                                <!-- Licenses -->
                                <div class="flex items-center bg-[#F1F5F9] px-7 py-5">
                                    <span class="text-sm text-[#09192D] border-l border-[#E2E8F0] pl-4 ml-4">مجوزها</span>
                                    <div class="flex items-center gap-x-3">
                                        <a href="#">
                                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_49360_26047)">
                                                    <path
                                                        d="M16.547 8.75567C18.3851 9.7831 20.2275 10.8022 22.0685 11.8237C22.2675 11.9342 22.4135 12.0708 22.503 12.2431L27.3161 9.43784C27.2908 9.40592 27.2558 9.37694 27.2076 9.34993C23.5252 7.31029 19.8461 5.26426 16.1631 3.22609C16.0848 3.18287 15.9418 3.1814 15.8634 3.22462C12.1873 5.25886 8.51552 7.30095 4.84083 9.33814C4.78342 9.37006 4.73963 9.40297 4.70752 9.44275L9.52602 12.251C9.61262 12.0806 9.75468 11.946 9.94978 11.838C11.7981 10.813 13.6473 9.78899 15.4922 8.75714C15.8513 8.55627 16.1865 8.55332 16.547 8.75468V8.75567Z"
                                                        fill="#CAD5E2" />
                                                    <path
                                                        d="M27.3775 15.792C27.3775 13.7568 27.3765 11.7211 27.3794 9.68587C27.3794 9.62055 27.3751 9.56358 27.3575 9.51398L22.539 12.3222C22.5887 12.447 22.612 12.5889 22.6081 12.7515C22.5887 13.5274 22.596 14.3044 22.6052 15.0814C22.6106 15.5273 22.432 15.8323 22.0379 16.0474C20.213 17.0444 18.3949 18.0537 16.5758 19.0614C16.4045 19.1562 16.2269 19.2073 16.0562 19.2176V20.9749C16.0849 20.97 16.1116 20.9621 16.133 20.9503C17.6174 20.139 19.0954 19.3144 20.5818 18.506C20.9515 18.3051 21.2867 18.0085 21.7582 18.0443C22.1615 18.0753 22.4685 18.309 22.5775 18.7177C22.6699 19.0639 22.5069 19.4593 22.1669 19.6518C21.4838 20.0388 20.7939 20.413 20.1074 20.7931C18.9461 21.436 17.7818 22.0735 16.6254 22.7252C16.4317 22.8343 16.2444 22.8922 16.0566 22.8976V28.3452C16.1116 28.3378 16.1681 28.3167 16.2313 28.2813C19.8729 26.2515 23.5169 24.2266 27.1639 22.2071C27.3313 22.1143 27.3824 22.0116 27.3814 21.825C27.3746 19.8143 27.377 17.8032 27.377 15.7925L27.3775 15.792Z"
                                                        fill="#90A1B9" />
                                                    <path
                                                        d="M16.0566 16.1019V17.3248C16.6964 16.9653 17.3376 16.6073 17.9794 16.2512C18.8639 15.7601 19.7523 15.2759 20.6338 14.7788C20.7049 14.7386 20.7739 14.6241 20.7764 14.5421C20.7895 14.1448 20.789 13.747 20.7798 13.3492L16.0566 16.1019Z"
                                                        fill="#90A1B9" />
                                                    <path
                                                        d="M20.7775 13.2509C20.7775 13.2485 20.7775 13.246 20.7775 13.2431C20.7755 13.1645 20.7191 13.0516 20.6544 13.0152C19.1506 12.17 17.6428 11.3321 16.1317 10.4997C16.0709 10.4663 15.9575 10.4741 15.8938 10.509C14.3973 11.3341 12.9056 12.167 11.4095 12.9926C11.303 13.0516 11.2529 13.1169 11.2373 13.2195L16.0339 16.015L20.7775 13.2505V13.2509Z"
                                                        fill="#CAD5E2" />
                                                    <path
                                                        d="M28.8374 15.7944C28.8374 18.0418 28.834 20.2892 28.8418 22.5361C28.8427 22.79 28.7571 22.9378 28.5367 23.0601C24.4933 25.3001 20.4517 27.5441 16.4156 29.7979C16.1353 29.9545 15.9208 29.9614 15.6352 29.8023C11.6063 27.5519 7.57207 25.3119 3.53492 23.0773C3.28777 22.9398 3.2002 22.7748 3.2002 22.4943C3.20749 18.0074 3.20749 13.5205 3.2002 9.03359C3.2002 8.75021 3.29555 8.59011 3.54027 8.45456C7.59834 6.20766 11.653 3.95438 15.7047 1.69619C15.9285 1.57144 16.109 1.56751 16.3343 1.69324C20.4002 3.95978 24.4689 6.22092 28.5406 8.47715C28.7625 8.59993 28.8427 8.75218 28.8418 9.00363C28.834 11.2672 28.8374 13.5308 28.8374 15.7944ZM3.85846 15.7586C3.85846 17.9323 3.8604 20.1065 3.85457 22.2802C3.85457 22.4555 3.89835 22.5582 4.05744 22.6461C7.96566 24.812 11.8715 26.9813 15.7733 29.1584C15.9626 29.264 16.0954 29.2552 16.2779 29.153C20.1734 26.9813 24.0719 24.8144 27.9729 22.653C28.1388 22.5611 28.1942 22.4614 28.1937 22.2724C28.1884 17.9328 28.1879 13.5932 28.1942 9.25362C28.1942 9.05913 28.1266 8.96729 27.966 8.8784C24.0442 6.70419 20.1243 4.52605 16.2078 2.34153C16.0521 2.2546 15.947 2.28259 15.8113 2.35822C11.9002 4.53489 7.98804 6.71008 4.07301 8.87938C3.90711 8.97122 3.85359 9.07337 3.85408 9.26098C3.86089 11.4268 3.85846 13.5922 3.85846 15.7581V15.7586Z"
                                                        fill="#90A1B9" />
                                                    <path
                                                        d="M15.5839 17.5901C15.7147 17.5164 15.8461 17.4432 15.977 17.3695V16.0823L11.231 13.3163C11.2373 14.9453 11.2378 16.5744 11.2339 18.2035C11.2339 18.3449 11.2757 18.4235 11.3998 18.4918C12.8929 19.3114 14.3817 20.138 15.8763 20.9557C15.9035 20.9705 15.9395 20.9783 15.9775 20.9803V19.2196C15.6398 19.2147 15.3401 19.0467 15.1888 18.7373C14.983 18.3154 15.1421 17.84 15.5839 17.5905V17.5901Z"
                                                        fill="#90A1B9" />
                                                    <path
                                                        d="M9.94128 19.6851C9.59244 19.4931 9.42994 19.2087 9.43238 18.8095C9.43773 17.7963 9.43432 16.7836 9.43432 15.7704H9.42897C9.42897 14.7567 9.43675 13.7435 9.42508 12.7298C9.42362 12.58 9.446 12.4484 9.49076 12.332L4.66496 9.51984C4.65085 9.55864 4.64307 9.60382 4.64307 9.65932C4.64842 13.7302 4.64842 17.8012 4.64307 21.8716C4.64307 22.0347 4.69756 22.1162 4.83622 22.1928C8.4895 24.2177 12.1408 26.2466 15.7893 28.2808C15.8569 28.3186 15.9172 28.3397 15.9761 28.3461V22.8971C15.8043 22.8883 15.6321 22.8357 15.4545 22.7365C13.6189 21.7145 11.7803 20.6988 9.9403 19.6851H9.94128Z"
                                                        fill="#90A1B9" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_49360_26047">
                                                        <rect width="25.6421" height="28.3178" fill="white"
                                                            transform="translate(3.2002 1.59998)" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </a>
                                        <a href="#">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32"
                                                viewBox="0 0 33 32" fill="none">
                                                <path
                                                    d="M30.7977 15.7565C30.521 16.0154 30.251 16.2551 29.9956 16.5092C28.8776 17.6205 27.7649 18.738 26.6454 19.8483C26.5271 19.9657 26.4817 20.0855 26.4822 20.2508C26.487 22.0868 26.4856 23.9232 26.4846 25.7592C26.4846 26.0873 26.4827 26.0849 26.1548 26.0849C24.3114 26.0839 22.468 26.0815 20.6247 26.0916C20.4972 26.0921 20.3383 26.1593 20.247 26.2491C18.9305 27.5503 17.6237 28.8606 16.314 30.1685C16.2468 30.2356 16.1768 30.3004 16.0691 30.4033C15.718 30.0371 15.3843 29.6786 15.0395 29.3321C14.0128 28.3001 12.9841 27.27 11.9463 26.2491C11.8516 26.1559 11.6836 26.0911 11.5493 26.0902C9.69773 26.08 7.84663 26.0791 5.99505 26.0887C5.76566 26.0897 5.71398 26.0187 5.71495 25.8022C5.72267 23.9662 5.71591 22.1298 5.72509 20.2938C5.72606 20.0875 5.65748 19.9478 5.51936 19.8106C4.21253 18.5114 2.90909 17.2088 1.60516 15.9067C1.54914 15.8507 1.49843 15.7893 1.40039 15.6806C1.77756 15.3202 2.15425 14.9718 2.51839 14.6109C3.54125 13.5982 4.56218 12.5836 5.57248 11.5583C5.66424 11.4651 5.71302 11.2897 5.71398 11.1525C5.72267 9.32424 5.71881 7.49647 5.71833 5.66821C5.71833 5.35513 5.71929 5.35271 6.02934 5.35271C7.83263 5.35174 9.63543 5.34981 11.4387 5.35368C11.6642 5.35368 11.841 5.31647 12.0168 5.13867C13.2985 3.83899 14.5957 2.5538 15.8885 1.26426C15.9508 1.20242 16.0179 1.1454 16.1044 1.06665C16.4642 1.42274 16.8177 1.77061 17.1688 2.12138C18.1926 3.1447 19.213 4.17237 20.2441 5.18892C20.3344 5.27782 20.4938 5.33967 20.6218 5.34015C22.4729 5.3503 24.3244 5.35174 26.176 5.34353C26.4102 5.34256 26.4923 5.38798 26.4904 5.6455C26.4788 7.47327 26.4894 9.30153 26.4812 11.1298C26.4803 11.3506 26.5469 11.4999 26.7024 11.6535C28.017 12.9547 29.3228 14.2645 30.6311 15.5724C30.6707 15.612 30.7064 15.6555 30.7977 15.757V15.7565ZM25.9316 5.90834C25.7959 5.90834 25.7008 5.90834 25.6057 5.90834C23.8753 5.90834 22.1445 5.9122 20.4141 5.90109C20.2808 5.90012 20.1156 5.83007 20.02 5.73634C19.1232 4.85844 18.2356 3.97137 17.3518 3.08044C16.9452 2.67072 16.5535 2.24603 16.1416 1.81361C15.9976 1.95131 15.9044 2.03731 15.8151 2.12669C14.6193 3.32008 13.4221 4.51202 12.2317 5.71121C12.0897 5.85423 11.9492 5.91365 11.7468 5.91269C10.0406 5.90496 8.3344 5.90786 6.6277 5.90737C6.51807 5.90737 6.40796 5.90737 6.27177 5.90737C6.27177 6.02864 6.27177 6.11561 6.27177 6.2021C6.27129 7.94145 6.27419 9.68081 6.26308 11.4202C6.26212 11.554 6.19499 11.7212 6.10178 11.8163C5.13639 12.7967 4.16182 13.7678 3.18436 14.7365C2.86079 15.0569 2.52225 15.3617 2.18371 15.6801C2.28513 15.7893 2.33198 15.8434 2.3822 15.8937C3.61031 17.1223 4.836 18.3529 6.07087 19.5744C6.223 19.7251 6.2766 19.8754 6.27564 20.0821C6.26936 21.8056 6.27612 23.5285 6.26694 25.2519C6.2655 25.4833 6.3278 25.5476 6.56154 25.5461C8.31605 25.536 10.0706 25.5379 11.8255 25.5466C11.9448 25.5471 12.0936 25.6075 12.1781 25.6911C13.2956 26.7927 14.4054 27.902 15.5133 29.0133C15.704 29.2046 15.8745 29.4162 16.1155 29.6868C16.2507 29.5085 16.3222 29.3882 16.4178 29.292C17.6102 28.0957 18.8078 26.9048 19.9993 25.707C20.1161 25.5896 20.2315 25.5365 20.3991 25.537C22.1454 25.5423 23.8922 25.5355 25.6385 25.5457C25.8737 25.5471 25.935 25.4795 25.9341 25.249C25.9254 23.5096 25.9321 21.7703 25.9259 20.0309C25.9254 19.8541 25.979 19.7309 26.1026 19.6087C27.154 18.5665 28.1976 17.5171 29.248 16.474C29.4972 16.2266 29.7623 15.9961 30.0212 15.757C29.9511 15.6743 29.9323 15.6487 29.9106 15.6265C28.6472 14.3645 27.3848 13.1015 26.1176 11.8439C25.9824 11.7096 25.9239 11.5728 25.9254 11.3786C25.9345 10.1789 25.9307 8.97927 25.9312 7.77911C25.9312 7.1684 25.9312 6.55818 25.9312 5.90689L25.9316 5.90834Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M20.0113 11.7608C21.0767 12.8402 21.6277 14.1413 21.6707 15.713C21.9223 15.5367 22.1387 15.3908 22.3488 15.2371C23.4329 14.4438 24.5186 13.6524 25.5965 12.8508C25.7655 12.7252 25.8655 12.7233 26.0123 12.8837C26.4078 13.3156 26.8222 13.7302 27.2626 14.1863C26.1741 14.6989 25.1179 15.1965 24.0023 15.7217C25.1097 16.2426 26.1615 16.7368 27.2602 17.2533C26.7705 17.7621 26.3026 18.2486 25.8225 18.7472C24.4447 17.7408 23.0838 16.7475 21.6775 15.7203C21.6446 16.4938 21.521 17.1929 21.2414 17.8568C20.9647 18.5143 20.5725 19.098 20.0456 19.6546C20.2982 19.6981 20.4996 19.7353 20.7019 19.7667C22.0537 19.9744 23.4054 20.1803 24.7572 20.3875C25.1179 20.4426 25.1121 20.4431 25.1087 20.8035C25.1039 21.3582 25.1073 21.9129 25.1073 22.5269C23.955 22.1138 22.8568 21.7201 21.6871 21.3012C22.1025 22.455 22.4951 23.545 22.9128 24.7055C22.6467 24.7055 22.4202 24.704 22.1933 24.7055C21.8069 24.7089 21.4201 24.7055 21.0347 24.7238C20.8603 24.7321 20.8198 24.6581 20.7966 24.5069C20.5764 23.0589 20.3504 21.6114 20.1253 20.1638C20.1021 20.0145 20.0741 19.8657 20.0398 19.6667C19.4835 20.1721 18.9054 20.5803 18.2414 20.8543C17.574 21.1297 16.8756 21.262 16.0996 21.2833C16.3613 21.6442 16.5888 21.9612 16.8196 22.2757C17.5522 23.2749 18.2848 24.2735 19.0194 25.2708C19.0865 25.3621 19.1363 25.4355 19.0271 25.538C18.5818 25.9564 18.1424 26.3811 17.6995 26.8024C17.6831 26.8178 17.658 26.8236 17.6237 26.8401C17.1258 25.7834 16.6298 24.7301 16.1015 23.6087C15.5761 24.7209 15.0757 25.7805 14.5628 26.8661C14.0524 26.3733 13.568 25.9061 13.0715 25.4273C14.077 24.0537 15.0728 22.6946 16.1053 21.2838C15.3264 21.2606 14.6295 21.1287 13.963 20.8538C13.3024 20.5808 12.7253 20.1798 12.1564 19.658C11.8893 21.3703 11.63 23.0333 11.3716 24.6905H9.29208C9.70112 23.5556 10.0947 22.4637 10.5144 21.2992C9.35727 21.7143 8.26004 22.1076 7.09182 22.5265C7.09182 21.8365 7.08795 21.1954 7.10003 20.5547C7.10099 20.5074 7.21352 20.4325 7.28258 20.4209C7.98911 20.303 8.69662 20.1948 9.4046 20.0861C10.271 19.9527 11.1379 19.8213 12.0043 19.6874C12.0419 19.6817 12.0772 19.6609 12.1409 19.6367C11.105 18.5409 10.5554 17.2562 10.5361 15.7096C9.12691 16.7383 7.75972 17.736 6.47511 18.6733C5.95257 18.1931 5.45708 17.7379 4.93164 17.2557C6.02598 16.7417 7.07781 16.2474 8.20064 15.7203C7.09037 15.1975 6.04288 14.7047 4.94951 14.1901C5.13206 13.9935 5.29481 13.8137 5.46239 13.6384C5.70144 13.3881 5.95643 13.1523 6.18003 12.8895C6.33264 12.7097 6.44275 12.7286 6.61661 12.8576C7.85196 13.7707 9.09407 14.6747 10.3347 15.5811C10.3854 15.6183 10.4405 15.6497 10.5115 15.6956C10.526 15.6343 10.5443 15.5908 10.5453 15.5463C10.5801 14.1573 11.0954 12.9648 12.0178 11.9386C12.0656 11.885 12.1081 11.827 12.1535 11.7705L12.1472 11.7768C12.5823 11.4617 12.9977 11.1144 13.4564 10.8385C14.1939 10.3949 15.0173 10.208 15.874 10.1674C15.9281 10.165 15.9822 10.1611 16.0783 10.1558C16.0242 10.0649 15.9914 9.99683 15.9469 9.93643C15.0308 8.68361 14.1152 7.43031 13.1928 6.18232C13.0957 6.05139 13.0933 5.97843 13.2155 5.86489C13.6612 5.44938 14.0963 5.02227 14.5628 4.57294C15.0752 5.65858 15.5751 6.71766 16.1 7.82891C16.6202 6.72587 17.119 5.66873 17.6348 4.57439C18.1438 5.06237 18.6277 5.52717 19.129 6.0079C18.124 7.38199 17.1287 8.74304 16.0971 10.1534C17.6377 10.1935 18.9344 10.7259 20.0157 11.7647L20.0099 11.7594L20.0113 11.7608ZM12.6823 16.4595C12.831 16.4518 12.9571 16.4392 13.0826 16.4392C15.6499 16.4397 18.2172 16.4402 20.785 16.4464C20.9627 16.4464 21.0381 16.4034 21.054 16.2102C21.1709 14.7805 20.7778 13.5181 19.8283 12.4406C18.4331 10.8573 16.2855 10.3403 14.2823 11.0946C12.4022 11.8024 11.133 13.6717 11.1325 15.7338C11.1321 17.9954 12.6495 19.9479 14.8603 20.5301C17.0234 21.0992 19.3352 20.117 20.4349 18.1602C20.4711 18.0955 20.4948 18.0239 20.5397 17.9186C20.0205 17.9186 19.5477 17.9244 19.0749 17.9157C18.9093 17.9128 18.7919 17.9616 18.6751 18.0839C17.2233 19.6005 14.9762 19.6039 13.5265 18.0877C13.0971 17.6389 12.8204 17.1002 12.6828 16.46L12.6823 16.4595Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M20.0175 11.7655C20.1001 11.2997 20.1904 10.8354 20.2643 10.3682C20.4493 9.20039 20.627 8.03116 20.8086 6.86241C20.8144 6.82376 20.8317 6.78656 20.8429 6.75177H22.9084C22.4993 7.88573 22.1053 8.97766 21.6856 10.1411C22.8427 9.72607 23.9394 9.3323 25.0521 8.93321C25.069 8.99747 25.0864 9.03371 25.0869 9.06994C25.0961 9.64103 25.1004 10.2126 25.113 10.7837C25.1164 10.9267 25.0801 11.0001 24.9227 11.0243C23.3334 11.2668 21.745 11.5162 20.1561 11.7621C20.1093 11.7693 20.0605 11.7611 20.0122 11.7601L20.0175 11.765V11.7655Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M12.1549 11.772C10.5332 11.5236 8.91103 11.2758 7.28933 11.0279C7.18115 11.0115 7.08988 10.9946 7.09133 10.8443C7.09712 10.2259 7.09374 9.60694 7.09374 8.91506C8.25907 9.33251 9.35437 9.72531 10.5134 10.1408C10.0991 8.98802 9.70691 7.89658 9.29883 6.76068H11.3779C11.6338 8.42563 11.8912 10.1022 12.1486 11.7787L12.1549 11.7724V11.772Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M8.11675 8.76684C7.5633 8.7736 7.10016 8.32137 7.08954 7.76478C7.07891 7.19417 7.53046 6.72406 8.0955 6.71826C8.67405 6.71198 9.13961 7.17533 9.13767 7.75463C9.13574 8.30349 8.67502 8.76007 8.11675 8.76684Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M25.1097 7.7505C25.1039 8.32063 24.6394 8.77672 24.0748 8.76658C23.5025 8.75595 23.0669 8.30323 23.0684 7.72006C23.0698 7.16927 23.5286 6.72283 24.0936 6.72235C24.6693 6.72235 25.115 7.17362 25.1093 7.7505H25.1097Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M8.08861 24.7209C7.52937 24.7093 7.08072 24.2489 7.08748 23.6932C7.09424 23.1168 7.54772 22.6747 8.12434 22.6825C8.70387 22.6902 9.15348 23.1487 9.13899 23.7169C9.12451 24.2865 8.65751 24.7325 8.08861 24.7209Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M3.7876 15.713C3.78857 15.1439 4.25315 14.6834 4.81722 14.6916C5.36825 14.6999 5.82946 15.1671 5.82752 15.715C5.82559 16.2894 5.37308 16.7421 4.80273 16.7412C4.22562 16.7397 3.78663 16.2947 3.7876 15.7125V15.713Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M26.3799 15.7304C26.3765 15.1569 26.8247 14.6988 27.396 14.6916C27.9504 14.6848 28.4213 15.1559 28.4256 15.7222C28.43 16.2841 27.9741 16.7339 27.3921 16.7416C26.8421 16.7489 26.3838 16.2908 26.3804 15.7304H26.3799Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M24.0999 22.6837C24.6819 22.6919 25.1175 23.1364 25.1107 23.7148C25.104 24.2844 24.6384 24.7337 24.0685 24.7212C23.5098 24.7091 23.0645 24.2525 23.0684 23.6949C23.0722 23.12 23.5252 22.6755 24.0999 22.6837Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M17.124 4.43715C17.1192 5.0063 16.6531 5.47158 16.0949 5.46433C15.5303 5.45709 15.0681 4.98505 15.0783 4.42507C15.0884 3.8535 15.5492 3.40755 16.12 3.41576C16.6797 3.42397 17.1288 3.88104 17.1245 4.43715H17.124Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M17.1244 27.0072C17.1287 27.57 16.6656 28.0276 16.0933 28.0261C15.5365 28.0247 15.0845 27.5739 15.0782 27.0139C15.0719 26.4515 15.5375 25.98 16.0982 25.9805C16.6656 25.981 17.12 26.4356 17.1244 27.0077V27.0072Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M19.4827 14.9794H12.7158C12.8409 13.8073 14.1458 12.2042 16.135 12.2249C18.1798 12.2467 19.3992 13.9314 19.4827 14.9794Z"
                                                    fill="#90A1B9" />
                                            </svg>
                                        </a>
                                        <a href="#">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32"
                                                viewBox="0 0 33 32" fill="none">
                                                <path
                                                    d="M9.45459 21.0416C6.49976 22.0702 3.88537 23.8114 1.19971 25.4692C1.33679 25.0897 1.42285 24.6826 1.61997 24.3365C2.3254 23.0975 3.41208 22.2224 4.65084 21.5548C6.10274 20.7721 9.37954 19.1612 9.37954 19.1612C9.37954 19.1612 9.38578 18.9943 9.39178 18.8183C9.47884 16.303 9.93812 13.8762 11.0648 11.595C11.7072 10.2951 12.4757 9.10137 13.4923 8.02467C14.2868 7.18297 15.1083 6.44157 16.139 5.91551C19.5101 4.19868 21.7194 6.57825 21.8805 9.16332C22.0536 11.9313 20.9509 14.2243 18.8426 16.0247C17.2396 17.3935 15.4285 18.4584 13.4833 19.307C12.9817 19.5258 11.856 20.0461 11.856 20.0461C11.856 20.0461 11.9193 20.4741 11.9494 20.6275C12.2726 22.3188 13.0781 23.6511 14.8502 24.2126C16.3631 24.6924 17.6799 24.3001 18.7076 23.1733C19.481 22.3247 20.1504 21.3738 20.7938 20.422C21.7434 19.0179 22.8131 17.7268 24.258 16.8025C25.0425 16.3 25.913 15.9274 26.7455 15.4957C26.7455 15.4957 26.8709 15.4588 26.9024 15.5108C26.9287 15.5541 26.8766 15.6373 26.8766 15.6373C26.7976 15.7809 26.7345 15.9362 26.6365 16.066C26.1031 16.7651 25.4127 17.2578 24.5872 17.5823C24.1719 17.7465 23.7657 17.9382 23.3744 18.1526C23.2294 18.2322 23.1413 18.4112 23.0272 18.5449L23.1083 18.6481C23.4175 18.6265 23.7267 18.5931 24.0359 18.5852C24.5422 18.5724 24.6973 18.7199 24.6813 19.2175C24.6492 20.1988 24.0339 20.832 23.2944 21.3748C23.0993 21.5184 22.8691 21.6197 22.647 21.7249C21.7584 22.1457 21.074 22.7367 20.5627 23.6059C20.1554 24.3001 19.5681 24.9265 18.9517 25.4604C17.3887 26.8134 15.6176 26.9511 13.6824 26.2559C11.5011 25.4722 10.3864 23.8281 9.68096 21.7986C9.62293 21.6305 9.56489 21.4623 9.49385 21.2558L9.45459 21.0416ZM12.6815 17.4282C13.5801 16.9434 14.1157 16.6147 14.8842 16.1663C16.0529 15.4849 17.1896 14.7425 18.0431 13.6776C19.1758 12.2646 19.6402 10.6075 19.3868 8.83688C19.1797 7.85653 18.4774 7.64467 17.5078 7.69723C15.5556 8.10923 14.2338 9.73103 13.4853 11.1142C12.934 12.4701 12.3978 13.9469 12.1187 15.4002C12.0016 16.0088 11.856 17.0884 11.856 17.9075L12.6815 17.4282Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M27.7956 14.2741C27.6131 14.0492 27.6511 13.7213 27.8803 13.5417L29.4703 12.2962C29.6995 12.1166 30.0332 12.1533 30.2157 12.3782L31.4843 13.9421C31.6667 14.1669 31.6288 14.4948 31.3995 14.6744L29.8096 15.9199C29.5803 16.0995 29.2466 16.0628 29.0642 15.8379L27.7956 14.2741Z"
                                                    fill="#90A1B9" />
                                            </svg>
                                        </a>
                                        <a href="#">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                viewBox="0 0 32 32" fill="none">
                                                <path
                                                    d="M24.4482 20.242C23.9869 20.3279 23.5437 20.3873 23.1124 20.4954C21.9434 20.7891 20.947 21.4215 20.0075 22.1455C18.3575 23.4166 16.7429 24.7347 15.0689 25.9728C13.8159 26.8992 12.4395 27.6078 10.8719 27.8778C10.1408 28.0036 9.42855 27.9908 8.71025 27.7869C7.32782 27.3949 6.70585 26.3367 6.55534 25.1436C6.38602 23.7993 6.44697 22.4496 6.74197 21.1211C7.05202 19.7241 7.37787 18.3306 7.69356 16.9351C7.71952 16.8206 7.77692 16.6396 7.81925 16.4142C7.31692 16.6219 6.82174 16.8315 6.41123 17.0166C6.22479 17.1183 6.08368 17.1888 5.87203 17.2873C5.69061 17.3718 5.49425 17.4345 5.4205 17.3718C5.35766 17.3181 5.24102 17.2228 5.46452 16.9242C5.81973 16.4496 6.22158 16.0365 6.65279 15.6388C7.24617 15.0909 7.86138 14.5547 8.51233 14.0714C9.58546 13.2742 10.3482 12.1938 11.2072 11.1975C12.4922 9.70785 14.083 8.65489 15.9821 8.11338C16.8663 7.86103 17.769 7.91699 18.6307 8.25834C19.8216 8.72999 20.2701 9.74915 20.4356 10.9031C20.7446 13.0571 20.0244 14.8904 18.5012 16.4004C17.3875 17.5044 16.0348 18.1533 14.433 18.008C13.6838 17.94 12.9441 17.6997 12.2164 17.4838C11.2497 17.1965 8.80697 16.3861 8.80697 16.3861C8.80697 16.3861 8.73019 16.5145 8.58081 16.8844C8.38854 17.3606 8.2384 17.8559 8.10144 18.3512C7.81924 19.3719 7.62169 20.4098 7.71388 21.4737C7.78575 22.3043 7.9705 23.1094 8.44648 23.8237C8.98869 24.6371 10.0739 24.9209 10.8701 24.8244C12.876 24.5811 14.4962 23.5184 16.1157 22.4316C17.5986 21.4361 19.0942 20.4583 20.6099 19.5131C21.2367 19.1222 21.9227 18.8206 22.7072 18.8901C23.3781 18.9498 24.2424 19.5998 24.4482 20.242ZM19.2357 13.2201C19.2395 13.1367 19.2436 13.0534 19.2474 12.97C19.3031 10.9741 17.5639 9.57341 15.5614 9.80924C14.3453 9.95231 13.2812 10.4705 12.3232 11.2171C11.6249 11.7612 11.0217 12.3939 10.545 13.1416C10.3339 13.4728 10.3719 13.5039 10.7542 13.5697C11.042 13.6192 11.3573 13.6805 11.6365 13.7545C12.1121 13.8803 12.8368 13.9685 15.5926 14.851C16.5962 15.1724 17.1094 15.3298 17.6674 15.166C18.2634 14.9914 18.6277 14.5855 18.701 14.5006C19.1465 13.988 19.2192 13.4214 19.2357 13.2201Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M10.3587 28.7073C11.1884 28.6251 11.9319 28.5988 12.6566 28.4685C13.8938 28.2462 15.0079 27.6897 15.9915 26.9311C17.5779 25.708 19.1187 24.4256 20.6742 23.1628C21.6006 22.4102 22.5221 21.6532 23.6479 21.2033C24.651 20.8022 25.6579 21.2213 26.0643 22.2104C26.1964 22.5315 26.1531 22.598 25.8145 22.6295C24.8173 22.7215 23.941 23.1267 23.2076 23.7794C21.9377 24.9089 20.7084 26.0839 19.4622 27.2398C18.4049 28.2203 17.3412 29.1917 16.0397 29.8542C14.2057 30.7877 12.3379 30.5166 10.8132 29.0956C10.6763 28.9679 10.5284 28.8519 10.3587 28.7073Z"
                                                    fill="#90A1B9" />
                                                <path
                                                    d="M14.1602 1.59998C14.3634 2.52188 14.8853 3.23086 15.4237 3.89441C16.1134 4.74459 16.18 5.17231 15.6164 6.10885C15.1434 6.89444 14.737 7.57151 14.1259 8.25758C14.0262 8.36949 13.8422 8.59443 13.6759 8.74952C13.6135 7.88657 13.1036 7.34657 12.6088 6.79606C12.035 6.15767 11.9293 5.56097 12.3232 4.82194C12.8598 3.81517 13.3459 2.77348 13.9954 1.83505C14.0326 1.78098 14.0755 1.707 14.1602 1.59998Z"
                                                    fill="#90A1B9" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                <!-- Social Media Icons -->
                                <div class="flex items-center justify-between bg-[#E2E8F0] px-7.25 py-4">
                                    <a href="#" class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25"
                                            fill="none">
                                            <path
                                                d="M7.8 2.28949H16.2C19.4 2.28949 22 4.88949 22 8.08949V16.4895C22 18.0277 21.3889 19.503 20.3012 20.5907C19.2135 21.6784 17.7383 22.2895 16.2 22.2895H7.8C4.6 22.2895 2 19.6895 2 16.4895V8.08949C2 6.55123 2.61107 5.07598 3.69878 3.98827C4.78649 2.90056 6.26174 2.28949 7.8 2.28949ZM7.6 4.28949C6.64522 4.28949 5.72955 4.66877 5.05442 5.34391C4.37928 6.01904 4 6.93471 4 7.88949V16.6895C4 18.6795 5.61 20.2895 7.6 20.2895H16.4C17.3548 20.2895 18.2705 19.9102 18.9456 19.2351C19.6207 18.5599 20 17.6443 20 16.6895V7.88949C20 5.89949 18.39 4.28949 16.4 4.28949H7.6ZM17.25 5.78949C17.5815 5.78949 17.8995 5.92119 18.1339 6.15561C18.3683 6.39003 18.5 6.70797 18.5 7.03949C18.5 7.37101 18.3683 7.68895 18.1339 7.92337C17.8995 8.15779 17.5815 8.28949 17.25 8.28949C16.9185 8.28949 16.6005 8.15779 16.3661 7.92337C16.1317 7.68895 16 7.37101 16 7.03949C16 6.70797 16.1317 6.39003 16.3661 6.15561C16.6005 5.92119 16.9185 5.78949 17.25 5.78949ZM12 7.28949C13.3261 7.28949 14.5979 7.81627 15.5355 8.75396C16.4732 9.69164 17 10.9634 17 12.2895C17 13.6156 16.4732 14.8873 15.5355 15.825C14.5979 16.7627 13.3261 17.2895 12 17.2895C10.6739 17.2895 9.40215 16.7627 8.46447 15.825C7.52678 14.8873 7 13.6156 7 12.2895C7 10.9634 7.52678 9.69164 8.46447 8.75396C9.40215 7.81627 10.6739 7.28949 12 7.28949ZM12 9.28949C11.2044 9.28949 10.4413 9.60556 9.87868 10.1682C9.31607 10.7308 9 11.4938 9 12.2895C9 13.0851 9.31607 13.8482 9.87868 14.4108C10.4413 14.9734 11.2044 15.2895 12 15.2895C12.7956 15.2895 13.5587 14.9734 14.1213 14.4108C14.6839 13.8482 15 13.0851 15 12.2895C15 11.4938 14.6839 10.7308 14.1213 10.1682C13.5587 9.60556 12.7956 9.28949 12 9.28949Z"
                                                fill="#90A1B9" />
                                        </svg>
                                    </a>
                                    <a href="#" class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25"
                                            fill="none">
                                            <path
                                                d="M12.5 2.28949C6.98 2.28949 2.5 6.76949 2.5 12.2895C2.5 17.8095 6.98 22.2895 12.5 22.2895C18.02 22.2895 22.5 17.8095 22.5 12.2895C22.5 6.76949 18.02 2.28949 12.5 2.28949ZM17.14 9.08949C16.99 10.6695 16.34 14.5095 16.01 16.2795C15.87 17.0295 15.59 17.2795 15.33 17.3095C14.75 17.3595 14.31 16.9295 13.75 16.5595C12.87 15.9795 12.37 15.6195 11.52 15.0595C10.53 14.4095 11.17 14.0495 11.74 13.4695C11.89 13.3195 14.45 10.9895 14.5 10.7795C14.5069 10.7477 14.506 10.7147 14.4973 10.6833C14.4886 10.6519 14.4724 10.6232 14.45 10.5995C14.39 10.5495 14.31 10.5695 14.24 10.5795C14.15 10.5995 12.75 11.5295 10.02 13.3695C9.62 13.6395 9.26 13.7795 8.94 13.7695C8.58 13.7595 7.9 13.5695 7.39 13.3995C6.76 13.1995 6.27 13.0895 6.31 12.7395C6.33 12.5595 6.58 12.3795 7.05 12.1895C9.97 10.9195 11.91 10.0795 12.88 9.67949C15.66 8.51949 16.23 8.31949 16.61 8.31949C16.69 8.31949 16.88 8.33949 17 8.43949C17.1 8.51949 17.13 8.62949 17.14 8.70949C17.13 8.76949 17.15 8.94949 17.14 9.08949Z"
                                                fill="#90A1B9" />
                                        </svg>
                                    </a>
                                    <a href="#" class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25"
                                            fill="none">
                                            <path
                                                d="M9.8 15.7143L15.509 12.5L9.8 9.28571V15.7143ZM22.516 7.325C22.659 7.82857 22.758 8.50357 22.824 9.36071C22.901 10.2179 22.934 10.9571 22.934 11.6L23 12.5C23 14.8464 22.824 16.5714 22.516 17.675C22.241 18.6393 21.603 19.2607 20.613 19.5286C20.096 19.6679 19.15 19.7643 17.698 19.8286C16.268 19.9036 14.959 19.9357 13.749 19.9357L12 20C7.391 20 4.52 19.8286 3.387 19.5286C2.397 19.2607 1.759 18.6393 1.484 17.675C1.341 17.1714 1.242 16.4964 1.176 15.6393C1.099 14.7821 1.066 14.0429 1.066 13.4L1 12.5C1 10.1536 1.176 8.42857 1.484 7.325C1.759 6.36071 2.397 5.73929 3.387 5.47143C3.904 5.33214 4.85 5.23571 6.302 5.17143C7.732 5.09643 9.041 5.06429 10.251 5.06429L12 5C16.609 5 19.48 5.17143 20.613 5.47143C21.603 5.73929 22.241 6.36071 22.516 7.325Z"
                                                fill="#90A1B9" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            </nav>

                            <!-- Escape Room Submenu -->
                            <nav id="escape-room-submenu" class="py-2 hidden submenu-item">
                                <!-- Submenu Header -->
                                <div class="flex items-center bg-[#F1F5F9] rounded-lg h-9 justify-center mx-8 gap-x-3 mt-3">
                                    <button type="button"
                                        class="back-to-main-menu flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14"
                                            fill="none">
                                            <path d="M2 2L7 7L2 12" stroke="#90A1B9" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <span class="text-sm text-[#09192D]">بازگشت به منو اصلی</span>
                                    </button>
                                </div>

                                <!-- Submenu Title -->
                                <div class="px-4 py-3">
                                    <h3 class="text-2xl font-bold text-[#09192D]">اتاق فرار</h3>
                                    <hr class="mt-4">
                                </div>


                                <!-- Submenu Items -->
                                <div class="px-2 max-h-[calc(100vh-200px)] overflow-y-auto scrollbar-hide">
                                    <a href="<?= home_url('/type/اتاق-فرار-ترسناک/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">ترسناک
                                    </a>
                                    <a href="<?= home_url('/type/اکشن/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">اکشن
                                    </a>
                                    <a href="<?= home_url('/type/جنایی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">جنایی
                                    </a>
                                    <a href="<?= home_url('/type/علمی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">علمی
                                    </a>
                                    <a href="<?= home_url('/type/بقا/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">بقا
                                    </a>
                                    <a href="<?= home_url('/type/جنگی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">جنگی
                                    </a>
                                    <a href="<?= home_url('/type/فانتزی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">فانتزی
                                    </a>
                                    <a href="<?= home_url('/type/تاریخی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">تاریخی
                                    </a>
                                    <a href="<?= home_url('/type/درام/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">درام
                                    </a>
                                    <a href="<?= home_url('/type/کمدی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">کمدی
                                    </a>
                                    <a href="<?= home_url('/type/تخیلی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">تخیلی
                                    </a>
                                    <a href="<?= home_url('/type/دلهره-آور/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">دلهره آور
                                    </a>
                                    <a href="<?= home_url('/type/معمامحور/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">معمامحور
                                    </a>
                                    <a href="<?= home_url('type/اتاق-فرار-رقابتی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">رقابتی
                                    </a>
                                    <a href="<?= home_url('/type/هیجانی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">هیجانی
                                    </a>
                                    <a href="<?= home_url('/type/اتاق-فرار-تعاملی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">تعاملی
                                    </a>
                                    <a href="<?= home_url('/type/سیاسی/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">سیاسی
                                    </a>
                                </div>
                            </nav>

                            <!-- Cooperation Submenu -->
                            <nav id="cooperation-submenu" class="py-2 hidden submenu-item">
                                <!-- Submenu Header -->
                                <div class="flex items-center bg-[#F1F5F9] rounded-lg h-9 justify-center mx-8 gap-x-3 mt-3">
                                    <button type="button"
                                        class="back-to-main-menu flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14"
                                            fill="none">
                                            <path d="M2 2L7 7L2 12" stroke="#90A1B9" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <span class="text-sm text-[#09192D]">بازگشت به منو اصلی</span>
                                    </button>
                                </div>

                                <!-- Submenu Title -->
                                <div class="px-4 py-3">
                                    <h3 class="text-2xl font-bold text-[#09192D]">همکاری با ما</h3>
                                    <hr class="mt-4">
                                </div>

                                <!-- Submenu Items -->
                                <div class="px-2">
                                    <a href="<?= home_url('/ads-job/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">فرصت‌های
                                        شغلی</a>
                                    <a href="<?= home_url('/organization-sales/'); ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">همکاری
                                        تجاری</a>
                                    <a href="<?= home_url('/partnership/') ?>"
                                        class="block px-2 py-3 text-gray-800 hover:bg-gray-50 transition-colors rounded-lg">پیشنهاد
                                        همکاری</a>
                                </div>
                            </nav>
                        </div>
                </div>

                <!-- Full Screen Search Overlay -->
                <div id="search-overlay"
                    class="fixed inset-0 bg-white z-[60] hidden top-[66px] bottom-10 pb-4 lg:bottom-auto lg:absolute lg:inset-auto lg:top-full lg:left-0 lg:w-[340px] lg:max-h-[700px] lg:mt-2 lg:rounded-lg lg:shadow-xl lg:border lg:border-gray-100 lg:overflow-y-auto">
                    <!-- Search Header -->
                    <div
                        class="container lg:container-none py-3 flex items-center justify-between lg:border-b lg:border-gray-100">
                        <div class="flex w-full items-center gap-x-4.5 mx-4 lg:mx-0 lg:px-4">
                            <div class="relative grow">
                                <input id="search-main-input" type="text" placeholder="جستجو"
                                    class="w-full px-4 h-12 rounded-lg text-right border placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200" />
                                <button type="button" id="search-clear-btn"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 p-1 hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16"
                                        fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M7.63312 9.91809L12.432 14.7115C12.6867 14.9659 13.032 15.1088 13.3922 15.1088C13.7523 15.1088 14.0976 14.9659 14.3523 14.7115C14.6069 14.4572 14.75 14.1122 14.75 13.7525C14.75 13.3928 14.6069 13.0478 14.3523 12.7934L9.55159 8L14.3514 3.20657C14.4774 3.08063 14.5774 2.93112 14.6456 2.76659C14.7137 2.60206 14.7488 2.42573 14.7488 2.24766C14.7487 2.06959 14.7136 1.89327 14.6453 1.72877C14.5771 1.56427 14.477 1.41482 14.3509 1.28893C14.2249 1.16305 14.0752 1.0632 13.9105 0.995096C13.7457 0.92699 13.5692 0.891958 13.3909 0.892C13.2127 0.892042 13.0361 0.927157 12.8715 0.99534C12.7068 1.06352 12.5571 1.16344 12.4311 1.28938L7.63312 6.08281L2.83422 1.28938C2.70906 1.15983 2.55933 1.05646 2.39375 0.985328C2.22818 0.914192 2.05008 0.876706 1.86984 0.875057C1.68961 0.873408 1.51085 0.90763 1.344 0.975725C1.17714 1.04382 1.02554 1.14443 0.898029 1.27167C0.770518 1.39892 0.669655 1.55025 0.601325 1.71685C0.532994 1.88345 0.498565 2.06197 0.500046 2.242C0.501526 2.42203 0.538887 2.59996 0.609949 2.76542C0.68101 2.93087 0.784348 3.08053 0.913935 3.20567L5.71464 8L0.91484 12.7943C0.785254 12.9195 0.681915 13.0691 0.610854 13.2346C0.539793 13.4 0.502431 13.578 0.500951 13.758C0.49947 13.938 0.533899 14.1166 0.60223 14.2831C0.67056 14.4497 0.771423 14.6011 0.898934 14.7283C1.02644 14.8556 1.17805 14.9562 1.3449 15.0243C1.51175 15.0924 1.69051 15.1266 1.87075 15.1249C2.05098 15.1233 2.22908 15.0858 2.39466 15.0147C2.56023 14.9435 2.70997 14.8402 2.83512 14.7106L7.63312 9.91809Z"
                                            fill="#90A1B9" />
                                    </svg>
                                    </svg>
                                </button>
                                <button type="button" id="search-icon-btn"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                        fill="none">
                                        <path
                                            d="M13.5244 12.6895L15.834 14.9979C15.9417 15.1094 16.0013 15.2588 16 15.4138C15.9986 15.5689 15.9364 15.7172 15.8268 15.8268C15.7172 15.9364 15.5689 15.9986 15.4138 16C15.2588 16.0013 15.1094 15.9417 14.9979 15.834L12.6883 13.5244C11.179 14.8175 9.22751 15.4758 7.24334 15.3611C5.25917 15.2464 3.39654 14.3676 2.0463 12.9092C0.696062 11.4508 -0.0368252 9.52612 0.00142535 7.539C0.0396759 5.55189 0.846091 3.65682 2.25145 2.25145C3.65682 0.846091 5.55189 0.0396759 7.539 0.00142535C9.52612 -0.0368252 11.4508 0.696062 12.9092 2.0463C14.3676 3.39654 15.2464 5.25917 15.3611 7.24334C15.4758 9.22751 14.8175 11.179 13.5244 12.6883V12.6895ZM7.68704 14.1914C9.4121 14.1914 11.0665 13.5061 12.2863 12.2863C13.5061 11.0665 14.1914 9.4121 14.1914 7.68704C14.1914 5.96198 13.5061 4.30758 12.2863 3.08778C11.0665 1.86798 9.4121 1.1827 7.68704 1.1827C5.96198 1.1827 4.30758 1.86798 3.08778 3.08778C1.86798 4.30758 1.1827 5.96198 1.1827 7.68704C1.1827 9.4121 1.86798 11.0665 3.08778 12.2863C4.30758 13.5061 5.96198 14.1914 7.68704 14.1914Z"
                                            fill="#90A1B9" />
                                    </svg>
                                </button>
                            </div>
                            <button type="button" id="search-close-btn"
                                class="lg:hidden w-9.5 h-9.5 rounded-lg flex items-center justify-center bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="13" viewBox="0 0 9 13"
                                    fill="none">
                                    <path
                                        d="M0.796549 7.67436L5.21581 12.393C5.54578 12.6862 5.98242 12.846 6.43367 12.8386C6.88491 12.8311 7.3155 12.6571 7.63463 12.3532C7.95375 12.0493 8.13648 11.6392 8.14427 11.2095C8.15207 10.7797 7.98433 10.3639 7.67642 10.0497L4.48746 6.5027L7.67642 3.36054C7.98432 3.0463 8.15206 2.63047 8.14427 2.20073C8.13647 1.77099 7.95375 1.36093 7.63463 1.057C7.3155 0.753095 6.88491 0.579082 6.43367 0.571659C5.98242 0.564235 5.54578 0.72398 5.21581 1.01721L0.796549 5.33103C0.470517 5.64191 0.287388 6.06332 0.287388 6.5027C0.287388 6.94207 0.470517 7.36348 0.796549 7.67436Z"
                                        fill="#0F172B" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Search Content -->
                    <div class="flex-1 lg:flex-none overflow-y-auto px-5 py-4 lg:px-4 lg:py-3">
                        <?php
                        require_once(get_template_directory() . '/template/func/get-user-searches.php');
                        $user_searches = ez_get_user_searches();

                        if (!empty($user_searches)) :
                        ?>
                            <div id="recent-searches" class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="flex items-center gap-2 text-sm font-bold text-[#62748E]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                            <path d="M11 19.25C9.075 19.25 7.37153 18.6658 5.88958 17.4973C4.40764 16.3289 3.44514 14.8353 3.00208 13.0167C2.94097 12.7875 2.98681 12.5776 3.13958 12.3869C3.29236 12.1962 3.49861 12.0853 3.75833 12.0542C4.00278 12.0236 4.22431 12.0694 4.42292 12.1917C4.62153 12.3139 4.75903 12.4972 4.83542 12.7417C5.20208 14.1167 5.95833 15.2396 7.10417 16.1104C8.25 16.9813 9.54861 17.4167 11 17.4167C12.7875 17.4167 14.304 16.7942 15.5494 15.5494C16.7949 14.3046 17.4173 12.7881 17.4167 11C17.4161 9.21189 16.7936 7.69572 15.5494 6.4515C14.3052 5.20728 12.7887 4.58456 11 4.58333C9.94583 4.58333 8.96042 4.82778 8.04375 5.31667C7.12708 5.80556 6.35556 6.47778 5.72917 7.33333H7.33333C7.59306 7.33333 7.81092 7.42133 7.98692 7.59733C8.16292 7.77333 8.25061 7.99089 8.25 8.25C8.24939 8.50911 8.16139 8.72697 7.986 8.90358C7.81061 9.08019 7.59306 9.16789 7.33333 9.16667H3.66667C3.40694 9.16667 3.18939 9.07867 3.014 8.90267C2.83861 8.72667 2.75061 8.50911 2.75 8.25V4.58333C2.75 4.32361 2.838 4.10606 3.014 3.93067C3.19 3.75528 3.40756 3.66728 3.66667 3.66667C3.92578 3.66606 4.14364 3.75406 4.32025 3.93067C4.49686 4.10728 4.58456 4.32483 4.58333 4.58333V5.82083C5.3625 4.84306 6.31369 4.08681 7.43692 3.55208C8.56014 3.01736 9.74783 2.75 11 2.75C12.1458 2.75 13.2192 2.96786 14.2203 3.40358C15.2212 3.83931 16.0921 4.42719 16.8328 5.16725C17.5734 5.90731 18.1616 6.77814 18.5973 7.77975C19.0331 8.78136 19.2506 9.85478 19.25 11C19.2494 12.1452 19.0318 13.2186 18.5973 14.2203C18.1628 15.2219 17.5746 16.0927 16.8328 16.8328C16.0909 17.5728 15.22 18.161 14.2203 18.5973C13.2205 19.0337 12.1471 19.2512 11 19.25ZM11.9167 10.6333L14.2083 12.925C14.3764 13.0931 14.4604 13.3069 14.4604 13.5667C14.4604 13.8264 14.3764 14.0403 14.2083 14.2083C14.0403 14.3764 13.8264 14.4604 13.5667 14.4604C13.3069 14.4604 13.0931 14.3764 12.925 14.2083L10.3583 11.6417C10.2667 11.55 10.1979 11.447 10.1521 11.3328C10.1062 11.2185 10.0833 11.0999 10.0833 10.9771V7.33333C10.0833 7.07361 10.1713 6.85606 10.3473 6.68067C10.5233 6.50528 10.7409 6.41728 11 6.41667C11.2591 6.41606 11.477 6.50406 11.6536 6.68067C11.8302 6.85728 11.9179 7.07483 11.9167 7.33333V10.6333Z" fill="#62748E" />
                                        </svg>
                                        آخرین جستجوهای شما
                                    </h3>
                                    <button type="button" id="clear-recent-searches" class="p-1 hover:opacity-70 transition-opacity" title="حذف همه" data-user-id="<?php echo get_current_user_id(); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M10 6H14C14 5.46957 13.7893 4.96086 13.4142 4.58579C13.0391 4.21071 12.5304 4 12 4C11.4696 4 10.9609 4.21071 10.5858 4.58579C10.2107 4.96086 10 5.46957 10 6ZM8 6C8 4.93913 8.42143 3.92172 9.17157 3.17157C9.92172 2.42143 10.9391 2 12 2C13.0609 2 14.0783 2.42143 14.8284 3.17157C15.5786 3.92172 16 4.93913 16 6H21C21.2652 6 21.5196 6.10536 21.7071 6.29289C21.8946 6.48043 22 6.73478 22 7C22 7.26522 21.8946 7.51957 21.7071 7.70711C21.5196 7.89464 21.2652 8 21 8H20.118L19.232 18.34C19.1468 19.3385 18.69 20.2686 17.9519 20.9463C17.2137 21.6241 16.2481 22.0001 15.246 22H8.754C7.75191 22.0001 6.78628 21.6241 6.04815 20.9463C5.31002 20.2686 4.85318 19.3385 4.768 18.34L3.882 8H3C2.73478 8 2.48043 7.89464 2.29289 7.70711C2.10536 7.51957 2 7.26522 2 7C2 6.73478 2.10536 6.48043 2.29289 6.29289C2.48043 6.10536 2.73478 6 3 6H8ZM15 12C15 11.7348 14.8946 11.4804 14.7071 11.2929C14.5196 11.1054 14.2652 11 14 11C13.7348 11 13.4804 11.1054 13.2929 11.2929C13.1054 11.4804 13 11.7348 13 12V16C13 16.2652 13.1054 16.5196 13.2929 16.7071C13.4804 16.8946 13.7348 17 14 17C14.2652 17 14.5196 16.8946 14.7071 16.7071C14.8946 16.5196 15 16.2652 15 16V12ZM10 11C10.2652 11 10.5196 11.1054 10.7071 11.2929C10.8946 11.4804 11 11.7348 11 12V16C11 16.2652 10.8946 16.5196 10.7071 16.7071C10.5196 16.8946 10.2652 17 10 17C9.73478 17 9.48043 16.8946 9.29289 16.7071C9.10536 16.5196 9 16.2652 9 16V12C9 11.7348 9.10536 11.4804 9.29289 11.2929C9.48043 11.1054 9.73478 11 10 11ZM6.76 18.17C6.8026 18.6694 7.03117 19.1346 7.40044 19.4735C7.76972 19.8124 8.25278 20.0003 8.754 20H15.246C15.7469 19.9998 16.2294 19.8117 16.5983 19.4728C16.9671 19.134 17.1954 18.6691 17.238 18.17L18.11 8H5.89L6.76 18.17Z" fill="#90A1B9" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($user_searches as $search): ?>
                                        <a href="<?php echo esc_url($search['url']); ?>"
                                            class="px-3 py-2 bg-gray-100 rounded-xl text-xs font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                                            <?php echo esc_html($search['name']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        require_once(get_template_directory() . '/template/func/get-popular-searches.php');
                        $popular_searches = ez_get_popular_searches(6);

                        if (!empty($user_searches) && !empty($popular_searches)) :
                        ?>
                            <hr class="my-4 border-gray-100">
                        <?php endif; ?>

                        <?php if (!empty($popular_searches)) : ?>
                            <div id="popular-searches-section">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="22" height="22"
                                        viewBox="0 0 22 22" fill="none">
                                        <g clip-path="url(#clip0_52229_17093)">
                                            <path
                                                d="M12.8148 21.5229C12.6868 21.3577 12.6078 21.1599 12.5869 20.952C12.566 20.7441 12.604 20.5346 12.6966 20.3472C12.9386 19.877 13.0334 19.3394 12.9716 18.81C12.9211 18.4322 12.7963 18.0682 12.6044 17.7389C12.4161 17.4131 12.1629 17.1295 11.8606 16.9056C10.8325 16.133 10.1111 15.0214 9.82418 13.7679C7.47568 16.8465 8.37631 18.5941 9.50106 20.1341C9.63251 20.3148 9.7017 20.5333 9.69823 20.7568C9.69476 20.9802 9.61881 21.1964 9.48181 21.373C9.34177 21.5514 9.15388 21.6864 8.94006 21.7621C8.72867 21.8362 8.50032 21.8471 8.28281 21.7937C6.76343 21.4225 5.33343 20.7157 4.36406 19.5896C3.81496 18.9638 3.39196 18.2378 3.11831 17.4515C2.84163 16.6584 2.71889 15.8197 2.75668 14.9806C2.75668 14.9806 2.57381 11.594 6.65756 8.27611C6.65756 8.27611 11.4838 4.18136 9.80906 1.15361C9.74718 1.0047 9.72881 0.841303 9.75607 0.682376C9.78333 0.523448 9.8551 0.37551 9.96306 0.255733C10.0681 0.138547 10.2065 0.0563513 10.3596 0.0202134C10.5128 -0.0159245 10.6734 -0.00426984 10.8197 0.0536084L11.0204 0.133358C12.8383 1.28246 14.2591 2.96246 15.0904 4.94586C15.8879 6.88873 15.8824 9.15336 15.3434 11.1705C15.7903 10.769 16.1629 10.2891 16.4448 9.75011L16.4847 9.66211C16.7569 9.00623 17.6136 9.21523 17.9353 9.64423C18.0536 9.83261 21.0868 14.2409 19.4574 17.9602C18.8625 19.0877 17.9922 20.0464 16.9274 20.7474C16.035 21.3407 15.037 21.7575 13.9877 21.9752C13.7709 22.0212 13.5454 22.0031 13.3387 21.923C13.1302 21.8417 12.9487 21.7035 12.8148 21.5242V21.5229ZM10.3866 10.8515C10.5148 10.7839 10.6638 10.7671 10.8039 10.8043C10.944 10.8415 11.0649 10.93 11.1428 11.0522C11.1978 11.1347 11.2322 11.2255 11.2459 11.3245L11.3078 11.8044C11.3353 12.507 11.3271 13.2412 11.6007 13.9164C11.8839 14.6094 12.3239 15.2226 12.8822 15.7011C13.6014 16.1743 14.1581 16.8568 14.4772 17.6564C14.7797 18.4319 14.8209 19.2926 14.5927 20.0942C15.2913 19.8747 15.9405 19.5212 16.5039 19.0534L16.6456 18.9379C17.1076 18.557 17.4884 18.0812 17.7634 17.5395C18.0398 16.9991 18.2062 16.4051 18.2502 15.7946C18.3396 14.3852 17.8597 12.9704 17.1131 11.7081C16.7721 12.2031 16.3018 12.5867 15.7587 12.8136C15.4191 12.958 15.0588 13.0474 14.6917 13.0749C14.4815 13.0878 14.272 13.04 14.0881 12.9374C13.9015 12.8315 13.7485 12.6752 13.6467 12.4864C13.5628 12.334 13.5149 12.1645 13.5065 11.9907C13.4982 11.817 13.5297 11.6437 13.5986 11.484C14.1651 10.1475 14.3411 8.66523 14.1004 7.22561C13.8159 5.57843 13.027 4.06021 11.8427 2.88061C11.6282 5.91248 8.51931 8.73536 7.88131 9.34311C7.78197 9.4358 7.67923 9.52478 7.57331 9.60986C4.23756 12.3117 4.46581 14.773 4.46581 14.8816C4.40131 15.8592 4.62046 16.8347 5.09693 17.6907C5.59881 18.5776 6.32068 19.3091 7.18693 19.8082C6.18731 17.611 6.18731 14.9737 9.87643 11.1925L10.3879 10.8487L10.3866 10.8515Z"
                                                fill="#62748E" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_52229_17093">
                                                <rect width="22" height="22" fill="white" />
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    <h3 class="text-sm font-bold text-[#62748E]">جستجوهای محبوب</h3>
                                </div>

                                <!-- لیست محبوب‌ترین جستجوها -->
                                <div id="popular-searches" class="flex flex-wrap gap-2">
                                    <?php foreach ($popular_searches as $item): ?>
                                        <a href="<?php echo esc_url($item['search_url']); ?>"
                                            class="px-3 py-2 bg-gray-100 rounded-xl text-xs font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                                            <?php echo esc_html($item['search_title']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Search Results (hidden by default) -->
                        <div id="main-search-results"
                            class="max-h-[calc(100vh-200px)] lg:max-h-[500px] overflow-y-auto max-lg:pb-15 scrollbar-hide"
                            style="display: none;">

                        </div>
                    </div>
                </div>

            </div>
        </header>

        <main class="relative container mx-auto px-4 sm:px-6 md:px-8" style="padding-bottom: 40px;">

        <?php } ?>

        <!-- City Search JavaScript -->
       <script>
            document.addEventListener("DOMContentLoaded", function () {
    // داده‌های شهر از فایل PHP گرفته می‌شود
    const allCities = typeof citiesData !== 'undefined' ? citiesData : [];

    /**
     * تابع اصلی راه‌اندازی منوی شهرها
     * @param {string} suffix - مقدار 'lg' برای دسکتاپ و 'sm' برای موبایل
     */
    function initCitySelector(suffix) {
        const searchInput = document.getElementById(`city-search-input-${suffix}`);
        const carouselMode = document.getElementById(`city-carousel-mode-${suffix}`);
        const searchMode = document.getElementById(`city-search-mode-${suffix}`);
        const searchResultsContainer = document.getElementById(`city-search-results-${suffix}`);
        
        const track = document.getElementById(`city-carousel-track-${suffix}`);
        const prevBtn = document.getElementById(`city-prev-btn-${suffix}`);
        const nextBtn = document.getElementById(`city-next-btn-${suffix}`);
        const dots = document.querySelectorAll(`#city-carousel-dots-${suffix} div`);

        // اگر المان در صفحه نبود، پردازش نمی‌کند (جلوگیری از ارور در ریسپانسیوها)
        if (!searchInput || !carouselMode || !track) return;

        /* =========================================
           1. منطق سوئیچینگ سرچ و کروسل
        ========================================= */
        searchInput.addEventListener("input", function (e) {
            const query = e.target.value.trim();
            
            if (query.length > 0) {
                // مخفی کردن کروسل، نمایش جستجو
                carouselMode.classList.add("hidden");
                searchMode.classList.remove("hidden");
                
                // فیلتر سریع
                const filtered = allCities.filter(city => city.name.includes(query));
                
                // رندر نتایج
                if (filtered.length > 0) {
                    searchResultsContainer.innerHTML = filtered.map(city => `
                        <a href="${city.slug}" class="flex items-center text-sm text-slate-800 hover:bg-[#EDF2F5] hover:text-primary-500 rounded-lg px-3 py-2 transition-colors">
                            ${city.name}
                        </a>
                    `).join("");
                } else {
                    searchResultsContainer.innerHTML = `<div class="text-sm text-gray-400 text-center py-4">شهری یافت نشد.</div>`;
                }
            } else {
                // برگشت به کروسل وقتی باکس خالی شد
                searchMode.classList.add("hidden");
                carouselMode.classList.remove("hidden");
                searchResultsContainer.innerHTML = "";
            }
        });

        /* =========================================
           2. منطق کروسل و Loop
        ========================================= */
        const slidesCount = track.children.length;
        let currentIndex = 0;

        function updateCarousel() {
            // آپدیت ترنسلیت (مخصوص سایت‌های RTL)
            track.style.transform = `translateX(${currentIndex * 100}%)`;
            
            // آپدیت اندیکاتورهای پایین کروسل
            dots.forEach((dot, index) => {
                if (index === currentIndex) {
                    dot.classList.add("!bg-EzOrange");
                } else {
                    dot.classList.remove("!bg-EzOrange");
                }
            });
        }

        // دکمه بعدی (راست - هندل کردن Loop به آخر)
        if (nextBtn) {
            nextBtn.addEventListener("click", () => {
                currentIndex = (currentIndex === 0) ? slidesCount - 1 : currentIndex - 1;
                updateCarousel();
            });
        }

        // دکمه قبلی (چپ - هندل کردن Loop به اول)
        if (prevBtn) {
            prevBtn.addEventListener("click", () => {
                currentIndex = (currentIndex === slidesCount - 1) ? 0 : currentIndex + 1;
                updateCarousel();
            });
        }
    }

    // مقداردهی و اجرای ماژول برای هر دو نسخه
    initCitySelector('lg'); // دسکتاپ
    initCitySelector('sm'); // موبایل
});


       </script>