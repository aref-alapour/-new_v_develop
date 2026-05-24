<?php

add_shortcode('home-discounts-event', function(){
    
    $args = [
        "source" => "home_discounts_event",
    ];

    $discountEvent = ez_products_snapshot_swiper_html($args);
    
    ob_start(); ?>
    
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
                                        <b>تخفیف داغ هفته</b> و دارای سانس
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
    <?php return ob_get_clean();
});