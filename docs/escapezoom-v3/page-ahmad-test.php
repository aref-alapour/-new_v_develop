<!-- Header -->
<?php

get_header();

global $wpdb;
global $post;

// امروز
$today = date('Y-m-d');
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps($dayAfterTomorrow);

$user_id = get_current_user_id();

// $ez_admin_settings = get_option('ez_admin_settings');

foreach (get_terms(['taxonomy' => 'product_cat']) as $category)
    $cities[] = ['id' => $category->term_id, 'title' => $category->name];

foreach (get_terms('product_tag') as $tag)
    $tags[] = ['id' => $tag->term_id, 'title' => $tag->name];
/*===============================================================*/
//اسلایدر تبلیغاتی
$params = [
    'slider_model' => 'wide',
    'items' => [
        [
            'md-src' => Theme_URL.'assets/images/banners/home-banners/eid-sm.avif',
            'lg-src' => Theme_URL.'assets/images/banners/home-banners/eid-lg.avif',
            'target-link' => '#',
        ],
        [
            'md-src' => Theme_URL.'assets/images/banners/home-banners/02-sm.avif',
            'lg-src' => Theme_URL.'assets/images/banners/home-banners/02-lg.avif',
            'target-link' => 'https://escapezoom.ir/blog/%d8%b3%d8%b7%d8%ad-%d8%a8%d9%86%d8%af%db%8c/',
        ],
        [
            'md-src' => Theme_URL.'assets/images/banners/home-banners/03-sm.avif',
            'lg-src' => Theme_URL.'assets/images/banners/home-banners/03-lg.avif',
            'target-link' => 'https://escapezoom.ir/city/%D9%84%DB%8C%D8%B2%D8%B1%D8%AA%DA%AF/',
        ],
        [
            'md-src' => Theme_URL.'assets/images/banners/home-banners/05-sm.avif',
            'lg-src' => Theme_URL.'assets/images/banners/home-banners/05-lg.avif',
            'target-link' => 'https://escapezoom.ir/city/%D8%B3%DB%8C%D9%86%D9%85%D8%A7-%D8%AA%D8%B1%D8%B3/',
        ]
    ],
];
get_template_part('template/banner/adv-banner', 'home adv banner', $params);
/*===============================================================*/
// اتاق فرارهای ترند
$args = [
    "source" => "home_trends"
];
$home_trends_rooms = ez_products_snapshot_swiper_html($args);
/*===============================================================*/

echo do_shortcode('[esi trend-rooms ttl="0" cache="no"]');

?>

<section class="max-w-full py-4 md:py-5 lg:py-9">
    <div class="mb-6 md:mb-8">
        <input type="hidden" id="trends-rooms" data-source="home_trends" data-params='{"schedule":-1}'>
        <div class="flex justify-between">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9" fill="none">
                            <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                  fill="#09192D" stroke="white" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div class="text-base font-bold md:text-lg">
                        <p>اتاق فرارهای <b>ترند</b> و دارای سانس</p>
                    </div>
                </h2>
            </div>
            <div class="relative hidden md:block content-center">
                <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                    <div class="flex gap-2">
                        <button type="button" data-input="trends-rooms" data-params="schedule:-1"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-5 py-1 transition" disabled>
                            همه
                        </button>
                        <button type="button" data-input="trends-rooms" data-params="schedule:[<?= $todayStart ?>,<?= $todayEnd ?>]"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                            امروز
                        </button>
                        <button type="button" data-input="trends-rooms" data-params="schedule:[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                            فردا
                        </button>
                        <button type="button" data-input="trends-rooms" data-params="schedule:[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                            پس فردا
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 md:hidden">
            <div class="relative block md:hidden">
                <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                    <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                        <button type="button" data-input="trends-rooms" data-params="schedule:-1"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled >
                            همه
                        </button>
                        <button type="button" data-input="trends-rooms" data-params="schedule:[<?= $todayStart ?>,<?= $todayEnd ?>]"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                            امروز
                        </button>
                        <button type="button" data-input="trends-rooms" data-params="schedule:[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                            فردا
                        </button>
                        <button type="button" data-input="trends-rooms" data-params="schedule:[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]"
                                class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                            پس فردا
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper products-carousel relative">
        <div id="trends-rooms-slider" class="swiper-wrapper first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300">
            <?= $home_trends_rooms ?>
        </div>
        <button class="swiper-go-prev products-carousel-prev trends-rooms-btn absolute right-0 max-lg:hidden top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
        <button class="swiper-go-next products-carousel-next trends-rooms-btn absolute left-0 max-lg:hidden top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
    </div>
</section>

<!--advance-search-->
<div class="extra-line my-4 md:hidden"></div>
<section class="max-w-full py-4 md:py-5 lg:py-9">
    <form action="<?= home_url('/game-finder/') ?>" method="get">
        <div class="rounded-xl flex max-lg:flex-col w-full px-6 lg:px-16 py-12 items-center gap-4 bg-gradient-to-t from-transparent from-75% to-slate-700/40 to-200% shadow-9">
            <h2 class="text-xl font-extrabold xl:text-2xl text-justify">سانــــــس یـــــــــــــاب</h2>
            <div class="grid grid-cols-3 gap-x-4 w-full">
                <div class="w-full relative">
                    <div class="relative w-full max-w-xs">
                        <div class="sans-dropdown-container relative">
                            <button type="button"
                                    class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                                    <span class="text-gray-700 text-nowrap">
                                        نوع سرگرمی
                                    </span>
                                <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                                <div class="max-h-60 overflow-auto">
                                    <div class="city-options py-1">
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="product_type" value="اتاق فرار" class="hidden option-sans-input" checked/>
                                            اتاق فرار
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="product_type" value="سینما ترس" class="hidden option-sans-input"/>
                                            سینما ترس
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="product_type" value="لیزرتگ" class="hidden option-sans-input"/>
                                            لیزرتگ
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="product_type" value="اتاق خشم" class="hidden option-sans-input"/>
                                            اتاق خشم
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full">
                    <div class="relative w-full max-w-xs">
                        <div class="sans-dropdown-container relative">
                            <button type="button"
                                    class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                                    <span id="cities-box-title" class="text-gray-700 relative">
                                        شهر
                                    </span>
                                <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                                <div class="p-2">
                                    <input type="text"
                                           class="city-search w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                           placeholder="جستجوی شهر...">
                                </div>
                                <div class="max-h-60 overflow-auto">
                                    <div id="cities-box-list" class="city-options py-1">
                                        <?php
                                        $cities = cities_type('اتاق فرار');
                                        foreach ($cities as $city) {
                                            echo '<label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4   py-2">
                                                    <input type="radio" name="city_id" value="'.$city['city_id'].'" class="hidden option-sans-input"/>
                                                    '.$city['city_name'].'
                                                    </label>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full">
                    <div class="relative w-full max-w-xs">
                        <div class="sans-dropdown-container relative">
                            <button type="button"
                                    class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                                    <span class="text-gray-700">
                                        تعداد <span class="max-lg:hidden">نفرات</span>
                                    </span>
                                <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                                <div class="max-h-60 overflow-auto">
                                    <div class="city-options py-1">
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="2" class="hidden option-sans-input"/>
                                            + 2 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="3" class="hidden option-sans-input"/>
                                            + 3 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="4" class="hidden option-sans-input"/>
                                            + 4 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="5" class="hidden option-sans-input"/>
                                            + 5 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="6" class="hidden option-sans-input"/>
                                            + 6 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="7" class="hidden option-sans-input"/>
                                            + 7 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="8" class="hidden option-sans-input"/>
                                            + 8 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="9" class="hidden option-sans-input"/>
                                            + 9 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="10" class="hidden option-sans-input"/>
                                            + 10 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="11" class="hidden option-sans-input"/>
                                            + 11 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="12" class="hidden option-sans-input"/>
                                            + 12 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="13" class="hidden option-sans-input"/>
                                            + 13 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="14" class="hidden option-sans-input"/>
                                            + 14 نفر
                                        </label>
                                        <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                            <input type="radio" name="count" value="15" class="hidden option-sans-input"/>
                                            + 15 نفر
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex h-12 max-xl:mt-2 max-lg:w-full min-w-64 items-center rounded-xl border border-slate-100 py-1 max-lg:px-3 lg:pr-6 text-xs xl:min-w-68 2xl:min-w-84">
                    <span class="ml-1.5 text-nowrap border-l border-slate-100 pl-1.5 xl:pl-4.5">سانـــس آزاد
                        بـرای:</span>
                <div class="relative text-slate-350 max-lg:w-full">
                    <div class="scrollbar-hide overflow-x-auto transition-all duration-200 max-w-full">
                        <div class="flex gap-2 max-lg:flex max-lg:justify-between max-lg:w-full">
                            <label class="flex-shrink-0 whitespace-nowrap rounded-2xl cursor-pointer max-lg:grow" for="time-2">
                                <input type="radio" name="schedule" value="[<?= $todayStart ?>,<?= $todayEnd ?>]" class="hidden peer" id="time-2" checked/>
                                <span class="text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  px-2 rounded-2xl peer-checked:text-slate-700">امروز</span>
                            </label>

                            <label class="flex-shrink-0 whitespace-nowrap rounded-2xl cursor-pointer max-lg:grow" for="time-3">
                                <input type="radio" name="schedule" value="[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]" class="hidden peer" id="time-3"/>
                                <span class="text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  px-2 rounded-2xl peer-checked:text-slate-700">فردا</span>
                            </label>

                            <label class="flex-shrink-0 whitespace-nowrap rounded-2xl cursor-pointer max-lg:grow" for="time-4">
                                <input type="radio" name="schedule" value="[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]" class="hidden peer" id="time-4"/>
                                <span class="text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  px-2 rounded-2xl peer-checked:text-slate-700">پس فردا</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit"
                    class="flex gap-4 max-lg:w-full items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-primary-600 text-white shadow-14 hover:bg-primary-500 focus-visible:outline-primary-600 h-12 px-6 py-1 mr-auto min-w-30 rounded-lg shadow-none">
                <span class="truncate">جستجو</span>
            </button>
        </div>
    </form>
</section>
<?php
/*===============================================================*/
// اتاق فرارهای شهر
$params = [
    'city_id' => [15],
];
$args = [
    "source" => "home_cities_escaperoom",
    'params' => $params,
    'sort_type' => 'popular',
];
$cities_rooms = ez_products_snapshot_swiper_html($args);
?>
<section class="max-w-full py-4 md:py-5 lg:py-9">
    <input type="hidden" id="cities-rooms" data-source="home_cities_escaperoom" data-params='{"sort_type":"popular","city_id":[15],"tag":[124]}'>
    <div class="flex justify-between mb-6 md:mb-8">
        <div class="items-center gap-6 md:flex">
            <h2 class="flex items-center gap-4">
                <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9" fill="none">
                        <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                              fill="#09192D" stroke="white" stroke-width="1.5"/>
                    </svg>
                </div>
                <div class="text-base font-bold md:text-lg">
                    <p>اتاق فرارهای <b id="cities-rooms-title">تهران</b></p>
                </div>
            </h2>
        </div>
        <div class="flex items-center gap-6">
            <a href="<?= home_url('/city/اتاق-فرار-تهران') ?>" id="cities-rooms-link">
                <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">مشاهده همه
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                        <path clip-rule="evenodd"
                              d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round"></path>
                        <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                              fill="currentColor"></path>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                        <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                    </svg>
                </div>
            </a>
        </div>
    </div>
    <div class="grid grid-cols-3 lg:grid-cols-4 my-8 gap-x-4 lg:gap-x-11.5">
        <div class="lg:col-span-2">
            <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">شهر مورد نظر</h3>
            <div class="dropdown-container relative">
                <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                    <span>انتخاب شهر</span>
                    <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                        <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                    <button type="button" data-input="cities-rooms" data-params="city_id:[15]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                        تهران
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="city_id:[162]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        کرج
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="city_id:[122]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        اصفهان
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="city_id:[121]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        مشهد
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="city_id:[293]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        کرمانشاه
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="city_id:[270]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        قزوین
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="city_id:[304]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        کاشان
                    </button>
                </div>
            </div>
        </div>
        <div>
            <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">سبک بازی</h3>
            <div class="dropdown-container relative">
                <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                    <span>سبک بازی</span>
                    <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                        <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                    <button type="button" data-input="cities-rooms" data-params="tag:[124]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                        ترسناک
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="tag:[346]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        اکشن
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="tag:[342]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        درام
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="tag:[126]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        دلهره آور
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="tag:[125]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        غیرترسناک
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="tag:[178]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        هیجانی
                    </button>
                    <button type="button" data-input="cities-rooms" data-params="tag:[127]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        جنایی
                    </button>
                </div>
            </div>
        </div>
        <div>
            <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">براساس</h3>
            <div class="dropdown-container relative">
                <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                    <span>براساس</span>
                    <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                        <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                    <button type="button" data-input="cities-rooms" data-params='sort_type:"popular"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                        محبوب ترین ها
                    </button>
                    <button type="button" data-input="cities-rooms" data-params='sort_type:"topsale"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        پرفروش ترین ها
                    </button>
                    <button type="button" data-input="cities-rooms" data-params='sort_type:"recent"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        جدیدترین ها
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper products-carousel relative">
        <div id="cities-rooms-slider" class="swiper-wrapper first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300">
            <?= $cities_rooms ?>
        </div>
        <button class="swiper-go-prev products-carousel-prev cities-rooms-btn absolute right-0 max-lg:hidden top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
        <button class="swiper-go-next products-carousel-next cities-rooms-btn absolute left-0 max-lg:hidden top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
    </div>
</section>
<?php
/*===============================================================*/
// بنر جستجو روی نقشه
get_template_part('template/banner/map-banner');
/*===============================================================*/
?>
<!--<section class="max-w-full py-4 md:py-5 lg:py-9 md:hidden">
    <form action="<?php /*= home_url('/game-finder/') */?>" method="get">
        <div class="rounded-xl flex max-lg:flex-col w-full px-6 lg:px-16 py-12 items-center gap-4 bg-gradient-to-t from-transparent from-75% to-slate-700/40 to-200% shadow-9">
            <h2 class="text-xl font-extrabold xl:text-2xl text-justify">سانــــــس یـــــــــــــاب</h2>
            <div class="flex items-center gap-x-4 w-full grow">
                <div class="grow">
                    <div class="w-full">
                        <div class="relative w-full max-w-xs">
                            <div class="sans-dropdown-container relative">
                                <button type="button"
                                        class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                                    <span class="text-gray-700">
                                        نوع سرگرمی
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                                    <div class="max-h-60 overflow-auto">
                                        <div class="city-options py-1">
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_type" value="40" class="hidden option-sans-input" checked/>
                                                اتاق فرار
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_type" value="60" class="hidden option-sans-input"/>
                                                سینما ترس
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_type" value="75" class="hidden option-sans-input"/>
                                                لیزرتگ
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_type" value="100" class="hidden option-sans-input"/>
                                                اتاق خشم
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grow">
                    <div class="w-full">
                        <div class="relative w-full max-w-xs">
                            <div class="sans-dropdown-container relative">
                                <button type="button"
                                        class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                                    <span class="text-gray-700">
                                        شهر
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                                    <div class="p-2">
                                        <input type="text"
                                               class="city-search w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                               placeholder="جستجوی شهر...">
                                    </div>
                                    <div class="max-h-60 overflow-auto">
                                        <div class="city-options py-1">
                                            <label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_location" value="15" class="hidden option-sans-input"/>
                                                تهران
                                            </label>
                                            <label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_location" value="16" class="hidden option-sans-input" checked/>
                                                کرج
                                            </label>
                                            <label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_location" value="17" class="hidden option-sans-input"/>
                                                مشهد
                                            </label>
                                            <label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_location" value="18" class="hidden option-sans-input"/>
                                                اصفهان
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grow">
                    <div class="w-full">
                        <div class="relative w-full max-w-xs">
                            <div class="sans-dropdown-container relative">
                                <button type="button"
                                        class="sans-dropdown-button w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between">
                                    <span class="text-gray-700">
                                        تعداد <span class="max-lg:hidden">نفرات</span>
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div class="sans-options absolute hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                                    <div class="max-h-60 overflow-auto">
                                        <div class="city-options py-1">
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="40" class="hidden option-sans-input" checked/>
                                                + 2 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="60" class="hidden option-sans-input"/>
                                                + 3 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="75" class="hidden option-sans-input"/>
                                                + 4 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 5 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 6 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 7 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 8 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 9 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 10 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 11 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 12 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 13 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 14 نفر
                                            </label>
                                            <label class="option-sans block w-full  hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2 ">
                                                <input type="radio" name="game_time" value="100" class="hidden option-sans-input"/>
                                                + 15 نفر
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex h-12 max-xl:mt-2 max-lg:w-full min-w-64 items-center rounded-xl border border-slate-100 py-1 max-lg:px-3 lg:pr-6 text-xs xl:min-w-68 2xl:min-w-84">
                    <span class="ml-1.5 text-nowrap border-l border-slate-100 pl-1.5 xl:pl-4.5">سانـــس آزاد
                        بـرای:</span>
                <div class="relative text-slate-350 max-lg:w-full">
                    <div class="scrollbar-hide overflow-x-auto transition-all duration-200 max-w-full">
                        <div class="flex gap-2 max-lg:flex max-lg:justify-between max-lg:w-full">
                            <label class="flex-shrink-0 whitespace-nowrap rounded-2xl cursor-pointer max-lg:grow" for="time-2">
                                <input type="radio" name="time" value="2" class="hidden peer" id="time-2" checked/>
                                <span class="text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  px-2 rounded-2xl peer-checked:text-slate-700">امروز</span>
                            </label>

                            <label class="flex-shrink-0 whitespace-nowrap rounded-2xl cursor-pointer max-lg:grow" for="time-3">
                                <input type="radio" name="time" value="3" class="hidden peer" id="time-3"/>
                                <span class="text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  px-2 rounded-2xl peer-checked:text-slate-700">فردا</span>
                            </label>

                            <label class="flex-shrink-0 whitespace-nowrap rounded-2xl cursor-pointer max-lg:grow" for="time-4">
                                <input type="radio" name="time" value="4" class="hidden peer" id="time-4"/>
                                <span class="text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2  px-2 rounded-2xl peer-checked:text-slate-700">پس فردا</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit"
                    class="flex gap-4 max-lg:w-full items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-primary-600 text-white shadow-14 hover:bg-primary-500 focus-visible:outline-primary-600 h-12 px-6 py-1 mr-auto min-w-30 rounded-lg shadow-none">
                <span class="truncate">جستجو</span>
            </button>
        </div>
    </form>
</section>-->
<?php
// سینماترس های تهران
$params = [
    'city_id' => [913],
];
$args = [
    "source" => "home_cities_cinema",
    'params' => $params,
    'sort_type' => 'popular',
];
$cities_cinema = ez_products_snapshot_swiper_html($args);
/*===============================================================*/
?>
<section class="max-w-full py-4 md:py-5 lg:py-9">
    <div class="mb-6 md:mb-8">
        <input type="hidden" id="cities-cinema" data-source="home_cities_cinema" data-params='{"sort_type":"popular","city_id":[913]}'>
        <div class="flex justify-between">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9" fill="none">
                            <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                  fill="#09192D" stroke="white" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div class="text-base font-bold md:text-lg">
                        <p>سینما ترس‌های <b id="cities-cinema-title">تهران</b></p>
                    </div>
                </h2>
            </div>
            <div class="flex items-center gap-6">
                <div class="hidden md:block"></div>
                <a href="<?= home_url('/city/سینما-ترس-تهران') ?>" id="cities-cinema-link">
                    <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">مشاهده همه
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                            <path clip-rule="evenodd"
                                  d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                                  fill="currentColor"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-5 my-8 gap-x-4 lg:gap-x-11.5">
        <div class="lg:col-span-2">
            <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">شهر مورد نظر</h3>
            <div class="dropdown-container relative">
                <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                    <span>انتخاب شهر</span>
                    <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                        <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                    <button type="button" data-input="cities-cinema" data-params="city_id:[913]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                        تهران
                    </button>
                    <button type="button" data-input="cities-cinema" data-params="city_id:[1009]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        کرج
                    </button>
                    <button type="button" data-input="cities-cinema" data-params="city_id:[918]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        اصفهان
                    </button>
                    <button type="button" data-input="cities-cinema" data-params="city_id:[904]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        مشهد
                    </button>
                    <button type="button" data-input="cities-cinema" data-params="city_id:[926]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        کرمانشاه
                    </button>
                    <button type="button" data-input="cities-cinema" data-params="city_id:[925]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        سنندج
                    </button>
                    <button type="button" data-input="cities-cinema" data-params="city_id:[1004]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        رشت
                    </button>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2">
            <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">براساس</h3>
            <div class="dropdown-container relative">
                <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                    <span>براساس</span>
                    <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                        <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                    <button type="button" data-input="cities-cinema" data-params='sort_type:"popular"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                        محبوب ترین ها
                    </button>
                    <button type="button" data-input="cities-cinema" data-params='sort_type:"topsale"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        پرفروش ترین ها
                    </button>
                    <button type="button" data-input="cities-cinema" data-params='sort_type:"recent"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        جدیدترین ها
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper products-carousel relative">
        <div id="cities-cinema-slider" class="swiper-wrapper first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300">
            <?= $cities_cinema ?>
        </div>
        <button class="swiper-go-prev products-carousel-prev cities-cinema-btn absolute right-0 max-lg:hidden top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
        <button class="swiper-go-next products-carousel-next cities-cinema-btn absolute left-0 max-lg:hidden top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
    </div>
</section>
<h1 class="text-base font-bold md:text-lg">اسکیپ زوم، مرجع معرفی و رزرو بازی های گروهی</h1>
<?php
/*===============================================================*/
// آیکون ژانرهای اتاق فرار
get_template_part('template/banner/genres-icons');
/*===============================================================*/
// تخفیف ویژه

$args = [
    "source" => "home_discounts_event",
];
$discountEvent = ez_products_snapshot_swiper_html($args);
/*===============================================================*/
?>
<section class="max-w-full py-8 md:py-5 max-md:px-8 max-md:bg-slate-105 max-md:max-w-none max-md:-ml-4 max-md:-mr-4">
    <div class="mb-6 md:mb-8 lg:-mb-px">
        <input type="hidden" id="discount-events" data-source="home_discounts_event" data-params='{"schedule":-1}'>
        <div class="flex justify-between">
            <div class="flex">
                <div class="items-center md:flex gap-0 lg:[&>h2]:bg-slate-50 lg:[&>h2]:h-full lg:[&>h2]:rounded-tr-4xl lg:[&>h2]:pr-8 [&>h2_b]:text-secondary-500">
                    <h2 class="flex items-center gap-4">
                        <div class="hidden md:block">
                            <img alt="[object Object]" loading="lazy" width="32" height="32" decoding="async"
                                 data-nimg="1"
                                 src="<?php echo esc_url(ez_theme_asset_uri('images/genres/Takhfif.svg')); ?>"
                                 style="color: transparent;">
                        </div>
                        <span class="text-base font-bold md:text-lg">
                            <div>
                                <b>پیشنهاد داغ هفته</b> و دارای سانس
                            </div>
                        </span>
                    </h2>
                    <div class="hidden md:block" style="margin-right: -2px; margin-bottom: -2px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="178" height="90" fill="none" viewBox="0 0 178 90" class="max-lg:hidden">
                            <path fill="#F0F3F6" d="M178 0C79.702 0 99.638 89.1 0 89.1h178V0Z"></path>
                        </svg>
                    </div>
                </div>
                <div class="relative hidden md:block content-center">
                    <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                        <div class="flex gap-2">
                            <button type="button" data-input="discount-events" data-params="schedule:-1"
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-5 py-1 transition" disabled>
                                همه
                            </button>
                            <button type="button" data-input="discount-events" data-params="schedule:[<?= $todayStart ?>,<?= $todayEnd ?>]"
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                                امروز
                            </button>
                            <button type="button" data-input="discount-events" data-params="schedule:[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]"
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                                فردا
                            </button>
                            <button type="button" data-input="discount-events" data-params="schedule:[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]"
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                                پس فردا
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <a href="<?= home_url('/discounts/'); ?>">
                    <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">مشاهده همه
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                            <path clip-rule="evenodd"
                                  d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                                  fill="currentColor"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
        <div class="relative block md:hidden my-8">
            <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                    <button type="button" data-input="discount-events" data-params="schedule:-1"
                            class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                        همه
                    </button>
                    <button type="button" data-input="discount-events" data-params="schedule:[<?= $todayStart ?>,<?= $todayEnd ?>]"
                            class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-white text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                        امروز
                    </button>
                    <button type="button" data-input="discount-events" data-params="schedule:[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]"
                            class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-white text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                        فردا
                    </button>
                    <button type="button" data-input="discount-events" data-params="schedule:[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]"
                            class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-white text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                        پس فردا
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="lg:py-8 lg:px-25.5 lg:bg-slate-50 rounded-4xl rounded-tr-none">
        <div class="relative w-full max-sm:max-w-bleed-2 max-sm:w-bleed-2 max-sm:-mr-4">
            <div class="swiper event-carousel relative">
                <div id="discount-events-slider" class="swiper-wrapper child:bg-white child:p-2.5 child:rounded-2xl">
                    <?= $discountEvent ?>
                </div>
            </div>
            <div class="hidden lg:block [&>button]:block [&>button]:-mx-19 [&>button]:h-full [&>button]:top-0 [&>button]:translate-y-0">
                <button class="event-carousel-prev discount-events-btn absolute right-0 top-1/2 -translate-y-1/2 rotate-180 cursor-pointer touch-manipulation appearance-none"
                        type="button">
                    <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </button>
                <button class="event-carousel-next discount-events-btn absolute left-0 top-1/2 -translate-y-1/2 cursor-pointer touch-manipulation appearance-none"
                        type="button">
                    <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </button>
            </div>
        </div>
    </div>
</section>
<?php
/*===============================================================*/
// لیزرتگ های تهران

$params = [
    'city_id' => [1149],
];
$args = [
    "source" => "home_cities_lasertag",
    'params' => $params,
    'sort_type' => 'popular',
];
$cities_lasertag = ez_products_snapshot_swiper_html($args);
/*===============================================================*/
?>
<section class="max-w-full py-4 md:py-5 lg:py-9 max-lg:mt-8">
    <div class="mb-6 md:mb-8">
        <input type="hidden" id="cities-lasertag" data-source="home_cities_lasertag" data-params='{"sort_type":"popular","city_id":[1149]}'>
        <div class="flex justify-between">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9" fill="none">
                            <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                  fill="#09192D" stroke="white" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div class="text-base font-bold md:text-lg">
                        <p>لیزرتگ‌های <b id="cities-lasertag-title">کرج</b></p>
                    </div>
                </h2>
            </div>
            <div class="flex items-center gap-6">
                <div class="hidden md:block"></div>
                <a href="<?= home_url('/city/لیزرتگ-کرج') ?>" id="cities-lasertag-link">
                    <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">مشاهده همه
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                            <path clip-rule="evenodd"
                                  d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                                  fill="currentColor"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-5 my-8 gap-x-4 lg:gap-x-11.5">
        <div class="lg:col-span-2">
            <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">شهر مورد نظر</h3>
            <div class="dropdown-container relative">
                <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                    <span>انتخاب شهر</span>
                    <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                        <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                    <button type="button" data-input="cities-lasertag" data-params="city_id:[1147]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        تهران
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params="city_id:[1149]"
                        class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                        کرج
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params="city_id:[1148]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        اصفهان
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params="city_id:[1156]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        مشهد
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params="city_id:[1151]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        اردبیل
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params="city_id:[1158]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        قم
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params="city_id:[1150]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        گرگان
                    </button>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2">
            <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">براساس</h3>
            <div class="dropdown-container relative">
                <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                    <span>براساس</span>
                    <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                        <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                    <button type="button" data-input="cities-lasertag" data-params='sort_type:"popular"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                        محبوب ترین ها
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params='sort_type:"topsale"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        پرفروش ترین ها
                    </button>
                    <button type="button" data-input="cities-lasertag" data-params='sort_type:"recent"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                        جدیدترین ها
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper products-carousel relative">
        <div id="cities-lasertag-slider" class="swiper-wrapper first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300">
            <?= $cities_lasertag ?>
        </div>
        <button class="swiper-go-prev products-carousel-prev cities-lasertag-btn absolute right-0 max-lg:hidden top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
        <button class="swiper-go-next products-carousel-next cities-lasertag-btn absolute left-0 max-lg:hidden top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-0.5"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                <g clip-path="url(#arrow_aa)">
                    <path fill="#BFCBD9" fill-rule="evenodd"
                          d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                          clip-rule="evenodd"></path>
                    <path fill="#fff" fill-rule="evenodd"
                          d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                          clip-rule="evenodd"></path>
                    <path fill="#9FB3CB" fill-rule="evenodd"
                          d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                          clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="arrow_aa">
                        <path fill="#fff" d="M0 0h30v113H0z"></path>
                    </clipPath>
                </defs>
            </svg>
        </button>
    </div>
</section>
<?php
/*===============================================================*/
// محبوب ترین مجموعه ها
get_template_part('template/layout/brands');
/*===============================================================*/
// کالکشن های محبوب
get_template_part('template/layout/collections');
/*===============================================================*/
// مجله خبری و تصویری
$args = array(
    'posts_per_page' => 10,
    'tax_query' => array(
        array(
            'taxonomy' => 'category', // نام دسته‌بندی استاندارد وردپرس
            'field'    => 'term_id', // نوع شناسه: می‌تواند 'term_id'، 'slug' یا 'name' باشد
            'terms'    => 1, // آیدی دسته‌بندی که می‌خواهید پست‌ها را از آن بگیرید
        ),
    ),
);

$query = new WP_Query($args);
if ($query->have_posts()) :
    ?>
    <section class="max-w-full py-4 md:py-5 lg:py-9">
        <div class="mb-6 md:mb-8">
            <div class="flex justify-between">
                <div class="items-center gap-6 md:flex">
                    <h3 class="flex items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 22 23" class="max-md:hidden">
                            <path fill-rule="evenodd" fill="currentColor"
                                  d="M10.1 22.498c-3.577 0-7.398-2.643-9.12-6.459a.908.908 0 0 1-.118-.247A11.012 11.012 0 0 1 0 11.499c0-2.447.798-4.692 2.135-6.514l.02-.026C4.154 2.253 7.358.499 10.1.499c1.265 0 1.617.044 1.972.078a.883.883 0 0 1 .223.016 10.947 10.947 0 0 1 8.656 6.221c.106.118.17.271.206.443.542 1.304-.057 2.736-.057 4.242 0 6.1-4 10.999-11 10.999zm7.71-5.103-2.208-3.836-3.976 6.905a8.948 8.948 0 0 0 6.184-3.069zm-3.396-5.899-1.726-2.997H9.315l-1.779 3.089 1.676 2.91h3.473l1.729-3.002zm-5.086 8.832 2.205-3.829h-2.72a.954.954 0 0 1-.489 0H3.502a8.942 8.942 0 0 0 5.826 3.829zM2.876 7.621C2.319 8.793 1.1 10.105 1.1 11.499c0 1.055 1.087 2.061 1.416 2.999h4.32l-3.96-6.877zm1.257-1.943 2.215 3.847 4.028-6.995a8.958 8.958 0 0 0-6.243 3.148zm8.538-3.008-2.204 3.829h2.621a.946.946 0 0 1 .487 0h4.923a8.938 8.938 0 0 0-5.827-3.829zm6.813 5.829h-4.42l4.014 6.972c.585-1.197.022-2.541.022-3.972 0-1.056.712-2.062.384-3z">
                            </path>
                        </svg>
                        <span class="text-base font-bold md:text-lg">
                                <span class="text-md">مجله بازی</span>
                            </span>
                    </h3>
                    <div class="hidden md:block">
                        <div class="relative hidden md:block">
                            <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                                <div class="flex gap-2">
                                    <button type="button" class="blog-slider-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl text-primary-500 hover:text-primary-600 px-3 transition" data-source="1" disabled>
                                        وبلاگ
                                    </button>
                                    <!--<button type="button" class="blog-slider-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl px-3">
                                        ویدیو تیزرها
                                    </button>-->
                                    <button type="button" class="blog-slider-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl hover:text-primary-600 px-3 transition" data-source="953">
                                        مجله خبری
                                    </button>
                                    <!--<button type="button" class="blog-slider-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl hover:text-primary-600 px-3 transition" data-source="954">
                                        نقد و بررسی اتاق‌ها
                                    </button>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden md:block"></div>
                    <a href="<?= home_url('/blog/') ?>">
                        <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">مشاهده همه
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                                <path clip-rule="evenodd"
                                      d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                      stroke-linejoin="round"></path>
                                <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                                      fill="currentColor"></path>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                                <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="swiper blogs-carousel relative pb-6">
            <div class="swiper-wrapper" id="blog-slider">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="swiper-slide relative grow-0 shrink-0 w-d264 h-d300">
                        <a class="relative block w-full overflow-hidden rounded-lg lg:rounded-3xl shadow-8  max-lg:[&_.ez-post-category]:hidden max-lg:[&_.ez-post-desc]:hidden max-lg:[&_.ez-post-info]:hidden w-d264 h-d300"
                           href="<?php the_permalink(); ?>">
                            <img alt="Product" loading="lazy" width="281" height="328" decoding="async"
                                 data-nimg="1" class="h-full w-full object-cover"
                                 src="<?= get_the_post_thumbnail_url($post->ID) ?>" style="color: transparent;">
                            <div class="absolute right-0 top-0 flex h-full w-full flex-col justify-between bg-slate-900/60 p-6 text-white/90 max-lg:justify-center">
                                <div class="ez-post-category">
                                    <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium inline">بلاگ</span>
                                </div>
                                <div class="text-center">
                                    <h2 class="text-lg font-extrabold text-white lg:text-xl line-clamp-2"><?= get_the_title() ?></h2>
                                    <p class="ez-post-desc mx-auto mt-3.5 max-w-d500 text-2xs leading-6 lg:mt-5 line-clamp-4"><?= get_the_excerpt() ?></p>
                                    <div class="ez-post-info mt-4 flex items-center justify-center gap-5 text-xs lg:mt-6">
                                        <span><?php comments_number( '0', '1', '%' ) ?> دیدگاه</span>
                                        <span class="h-3.5 border-l border-white/40"></span>
                                        <span><?php the_author(); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            <button class="blogs-carousel-prev blog-btn-slider absolute right-0 max-lg:hidden top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-0.5"
                    type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                    <g clip-path="url(#arrow_aa)">
                        <path fill="#BFCBD9" fill-rule="evenodd"
                              d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                              clip-rule="evenodd"></path>
                        <path fill="#fff" fill-rule="evenodd"
                              d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                              clip-rule="evenodd"></path>
                        <path fill="#9FB3CB" fill-rule="evenodd"
                              d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                              clip-rule="evenodd"></path>
                    </g>
                    <defs>
                        <clipPath id="arrow_aa">
                            <path fill="#fff" d="M0 0h30v113H0z"></path>
                        </clipPath>
                    </defs>
                </svg>
            </button>
            <button class="blogs-carousel-next blog-btn-slider absolute left-0 max-lg:hidden top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-0.5"
                    type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                    <g clip-path="url(#arrow_aa)">
                        <path fill="#BFCBD9" fill-rule="evenodd"
                              d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z"
                              clip-rule="evenodd"></path>
                        <path fill="#fff" fill-rule="evenodd"
                              d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z"
                              clip-rule="evenodd"></path>
                        <path fill="#9FB3CB" fill-rule="evenodd"
                              d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z"
                              clip-rule="evenodd"></path>
                    </g>
                    <defs>
                        <clipPath id="arrow_aa">
                            <path fill="#fff" d="M0 0h30v113H0z"></path>
                        </clipPath>
                    </defs>
                </svg>
            </button>
        </div>
    </section>
<?php
endif;
wp_reset_postdata();
/*===============================================================*/
// آخرین کامنت ها
get_template_part('template/layout/comments');
?>
<?php get_footer(); ?>
<script>
    let blogsCarousel = new Swiper( '.blogs-carousel', {
        slidesPerView: 1.3,
        spaceBetween:  20,
        navigation:    {
            nextEl: '.blogs-carousel-next',
            prevEl: '.blogs-carousel-prev',
        },
        breakpoints:   {
            768:  {
                slidesPerView: 1.7,
                spaceBetween:  20,
            },
            1024: {
                slidesPerView: 2.7,
                spaceBetween:  20,
            },
            1280: {
                slidesPerView: 3.7,
                spaceBetween:  20,
            },
            1536: {
                slidesPerView: 4.7,
                spaceBetween:  20,
            },
        },
    } )
    jQuery(document).ready(function ($) {
        let sliderBtnActive = function (){
            $('.blog-slider-btn').removeClass('text-primary-500')
            $('.blog-slider-btn').attr('disabled',false)
        }
        $('.blog-slider-btn').on('click', function(){
            sliderBtnActive();
            $(this).addClass('text-primary-500');
            $(this).attr('disabled',true)
            let dataSource = $(this).attr('data-source')
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'blog-cat-slider',
                    'data-source': dataSource
                },
                beforeSend: function () {
                    $('.blog-btn-slider').hide();
                    $("#blog-slider").empty().append('<div style="height:300px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">لطفا منتظر باشید...</div>')
                },
                success: function (response) {
                    if (response.data.length > 0) {
                        $("#blog-slider").empty()
                        // حلقه برای اضافه کردن هر عنصر به DOM
                        $.each(response.data, function(index, data) {
                            let slideHtml = `
                                <div class="swiper-slide relative grow-0 shrink-0 w-d264 h-d300">
                        <a class="relative block w-full overflow-hidden rounded-lg lg:rounded-3xl shadow-8  max-lg:[&_.ez-post-category]:hidden max-lg:[&_.ez-post-desc]:hidden max-lg:[&_.ez-post-info]:hidden w-d264 h-d300"
                           href="${data.url}">
                            <img alt="Product" loading="lazy" width="281" height="328" decoding="async"
                                 data-nimg="1" class="h-full w-full object-cover"
                                 src="${data.image_url}" style="color: transparent;">
                            <div class="absolute right-0 top-0 flex h-full w-full flex-col justify-between bg-slate-900/60 p-6 text-white/90 max-lg:justify-center">
                                <div class="ez-post-category">
                                    <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium inline">${data.cat_title}</span>
                                </div>
                                <div class="text-center">
                                    <h2 class="text-lg font-extrabold text-white lg:text-xl line-clamp-2">${data.title}</h2>
                                    <p class="ez-post-desc mx-auto mt-3.5 max-w-d500 text-2xs leading-6 lg:mt-5 line-clamp-4">${data.excerpt}</p>
                                    <div class="ez-post-info mt-4 flex items-center justify-center gap-5 text-xs lg:mt-6">
                                        <span>0 دیدگاه</span>
                                        <span class="h-3.5 border-l border-white/40"></span>
                                        <span>نویسنده</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>`;
                            // افزودن کد HTML به div مربوطه
                            $("#blog-slider").append(slideHtml);
                            $('.blog-btn-slider').show();
                        });
                        setTimeout(function (){
                            let blogsCarousel = new Swiper( '.blogs-carousel', {
                                slidesPerView: 1.3,
                                spaceBetween:  20,
                                navigation:    {
                                    nextEl: '.blogs-carousel-next',
                                    prevEl: '.blogs-carousel-prev',
                                },
                                breakpoints:   {
                                    768:  {
                                        slidesPerView: 1.7,
                                        spaceBetween:  20,
                                    },
                                    1024: {
                                        slidesPerView: 2.7,
                                        spaceBetween:  20,
                                    },
                                    1280: {
                                        slidesPerView: 3.7,
                                        spaceBetween:  20,
                                    },
                                    1536: {
                                        slidesPerView: 4.7,
                                        spaceBetween:  20,
                                    },
                                },
                            } )
                        },50)
                    } else {
                        $("#blog-slider").empty().append('<div style="height:300px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">لطفا منتظر باشید...</div>')
                    }
                }
            })
        })
        $('.city-btn-filter').on('click',function (){
            let id = '#'+$(this).attr('data-input')+'-link'
            let term_id = $(this).attr('data-params')
            let city_id = term_id.match(/\[(\d+)\]/)[1];

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url( 'admin-ajax.php' )?>", // در وردپرس ajaxurl به طور خودکار تعریف شده است
                data: {
                    action: 'get_category_link',
                    category_id: city_id // city_id به عنوان شناسه کتگوری ارسال می‌شود
                },
                success: function (response) {
                    if (response) {
                        let link = response;
                        $(id).attr('href', link)
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error: ' + textStatus);
                }
            });
        })
    })
</script>

