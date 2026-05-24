<?php

add_shortcode('city-escape-rooms', function(){
    
    $params = [
        'city_id' => [15],
    ];
    
    $args = [
        "source" => "home_cities_escaperoom",
        'params' => $params,
        'sort_type' => 'popular',
    ];
    
    $cities_rooms = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)))->products;
    
    ?>
    <section class="max-w-full py-4 md:py-5 lg:py-9">
        <input type="hidden" id="cities-rooms" data-source="home_cities_escaperoom" data-params='{"sort_type":"popular","city_id":[15],"tag":[124]}'>
        <div class="flex justify-between mb-6 md:mb-8">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9"
                             fill="none">
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24"
                             class="max-lg:hidden">
                            <path clip-rule="evenodd"
                                  d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                                  fill="currentColor"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="lg:hidden">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
        <div class="grid grid-cols-3 lg:grid-cols-4 my-8 gap-x-4 lg:gap-x-11.5">
            <div class="lg:col-span-2">
                <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-[#E8EDF1] max-md:hidden">شهر مورد نظر</h3>
                <div class="dropdown-container relative">
                    <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-[#E8EDF1] md:hidden">
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
                <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-[#E8EDF1] max-md:hidden">سبک بازی</h3>
                <div class="dropdown-container relative">
                    <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-[#E8EDF1] md:hidden">
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
                <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-[#E8EDF1] max-md:hidden">براساس</h3>
                <div class="dropdown-container relative">
                    <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-[#E8EDF1] md:hidden">
                        <span>براساس</span>
                        <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                            <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                        <button type="button" data-input="cities-rooms" data-params='sort_type:"hottest"'
                                class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                            داغ ترین ها
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
            <div id="cities-rooms-slider" class="swiper-wrapper first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px]">
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
    <?php return ob_get_clean();
});