<?php get_header(); ?>
<?php
// امروز
$today = date('Y-m-d');
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps($dayAfterTomorrow);
?>
<section class="py-4 md:py-5 lg:py-9">
    <style>
        .bg-timer-takhfif-mobile {
            background-image: url('<?= Theme_ASSET_URL ?>images/discounts/top\ nav.png');
            background-repeat: no-repeat;
            background-size: cover;
        }


        .bg-timer-takhfif {
            background-image: url('<?= Theme_ASSET_URL ?>images/discounts/Desktop\ background.png');
            background-repeat: no-repeat;
            background-size: cover;
        }

        @media (max-width: 1024px) {
            .bg-timer-takhfif {
                background-image: url('<?= Theme_ASSET_URL ?>images/discounts/Mobile\ Background.png');
                background-repeat: no-repeat;
                background-size: cover;
            }
        }


        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ffffff;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 10;
            width: 100%;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }

        .dropdown-content a {
            display: block;
            padding: 0.75rem 1rem;
            color: #4a5568;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .dropdown-content a:hover {
            background-color: #f7fafc;
        }

        .show {
            display: block;
        }
    </style>
    <!-- srart-navar-takhfif-mobile ------------------------------------------------------>
    <section class="flex justify-center mt-4 lg:hidden bg-timer-takhfif-mobile px-auto">
        <h3 class="text-[40px] font-fat-yekanbakh !leading-[1.7] text-white text-center flex justify-center">تخفیف های
            ویژه</h3>
    </section>
    <!-- end-navar-takhfif-mobile -------------------------------------------------------->

    <!-- srart-baner-header -------------------------------------------------------------->
    <section class="container mx-auto mb-[70px] mt-10 px-4 sm:px-6 md:px-8">

        <div
            class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-center pb-[50px] lg-[35px]  gap-[28px] lg:gap-[54px]"
            style="
            border-radius: 0px 0px 50px 50px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.86) 32.96%, #F5F5F5 83.33%);
            box-shadow: 3px -30px 30px 0px rgba(0, 0, 0, 0.01) inset;
          ">


            <img src="<?= Theme_ASSET_URL ?>images/discounts/Desktop II.png" alt=""
                class="w-[450px] h-[380px] hidden lg:flex" />

            <img src="<?= Theme_ASSET_URL ?>images/discounts/Mobile II.png" alt=""
                class="h-[340px] object-contain flex lg:hidden" />
            <div class="flex flex-col items-center lg:items-start px-[26px]">
                <div class="flex flex-row lg:justify-between justify-center w-full max-w-[537px] relative">
                    <h1 class="text-[34px] lg:text-5xl font-fat-yekanbakh !leading-[1.7] text-center lg:text-start">
                        تجربه‌ای هیجان انگیز <br />با تخفیف های ویژه!</h1>
                    <img src="<?= Theme_ASSET_URL ?>images/discounts/Group 119.png" alt=""
                        class="hidden lg:flex w-[57px] h-[57px] absolute top-[75px] left-0" />
                </div>
                <p class="text-lg mt-5 max-w-[547px] text-center lg:text-start text-justify">فرصتی استثنایی برای عاشقان
                    ماجراجویی! همین حالا از تخفیف‌های ویژه <span class="font-fat-yekanbakh">اسکیپ زوم</span> استفاده
                    کنید و لحظاتی پرهیجان، چالش‌برانگیز و فراموش‌نشدنی را با دوستان و خانواده خود تجربه کنید.</p>
            </div>


            <div class="absolute -bottom-8 -z-10 md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="324" height="103" viewBox="0 0 324 103" fill="none">
                    <g filter="url(#filter0_f_704_2)">
                        <path d="M78.6457 89.423C49.3683 98.3269 10 76.7771 10 46.1757C10 26.1964 26.1964 10 46.1757 10L277.001 10C297.435 10 314 26.5648 314 46.9986C314 77.8498 275.278 99.3395 245.802 90.2309C222.494 83.0283 193.768 77.5 160.5 77.5C128.488 77.5 101.018 82.619 78.6457 89.423Z"
                            fill="url(#paint0_linear_704_2)" fill-opacity="0.15" />
                    </g>
                    <defs>
                        <filter id="filter0_f_704_2" x="0" y="0" width="324" height="102.341"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                            <feGaussianBlur stdDeviation="5" result="effect1_foregroundBlur_704_2" />
                        </filter>
                        <linearGradient id="paint0_linear_704_2" x1="159.678" y1="76.8048" x2="158.67" y2="169.548"
                            gradientUnits="userSpaceOnUse">
                            <stop />
                            <stop offset="1" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="absolute flex w-full -bottom-12 -z-10 max-md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="1198" height="124" viewBox="0 0 1198 124" fill="none">
                    <g filter="url(#filter0_f_76_38)">
                        <path d="M69.0643 109.064C40.1413 112.694 14 90.2157 14 61.0659C14 35.0721 35.0721 14 61.0659 14L1136.88 14C1162.9 14 1184 35.0961 1184 61.1195C1184 90.263 1157.9 112.744 1128.98 109.141C1042.35 98.3496 856.935 80 597.628 80C338.936 80 155.145 98.2627 69.0643 109.064Z"
                            fill="url(#paint0_linear_76_38)" fill-opacity="0.15" />
                    </g>
                    <defs>
                        <filter id="filter0_f_76_38" x="0" y="0" width="1198" height="123.521"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                            <feGaussianBlur stdDeviation="7" result="effect1_foregroundBlur_76_38" />
                        </filter>
                        <linearGradient id="paint0_linear_76_38" x1="590.063" y1="74.7868" x2="589.846" y2="159.184"
                            gradientUnits="userSpaceOnUse">
                            <stop />
                            <stop offset="1" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

    </section>
    <!-- end-baner-header ----------------------------------------------------------------->

    <!-- start-navar-takhfif -------------------------------------------------------------->
    <section class="">
        <div class="bg-timer-takhfif py-5 lg:px-[60px]">
            <div class="flex flex-col items-center justify-center mx-auto my-auto lg:flex-row lg:justify-between">
                <div class="flex items-center">
                    <img src="<?= Theme_ASSET_URL ?>images/discounts/Group 119 (1).png" alt=""
                        class="w-[57px] h-[57px] hidden lg:flex" />
                    <h4 class="text-[30px] lg:text-[40px] font-fat-yekanbakh !leading-[1.7] text-white lg:mr-[32px]">
                        تخفیف‌های ویژه</h4>
                    <hr class="w-[1px] h-[52px] mx-[15px] bg-[#FD7013]" />
                    <p class="text-5 lg:text-[28px] font-black text-white">به مدت محدود</p>
                </div>

                <div class="flex flex-row items-center gap-[10px] mt-5 lg:mt-0">
                    <div class="w-[64px] h-[64px] lg:w-[86px] lg:h-[86px] bg-white flex justify-center items-center rounded-[10px]">
                        <div class="flex flex-col items-center gap-2">
                            <p id="seconds"
                                class="text-[28px] lg:text-[44px] font-fat-yekanbakh text-[#EE6003] mt-[-20px]">0</p>
                            <p class="text-lg font-black text-[#EE6003] mt-[-30px]">ثانیه</p>
                        </div>
                    </div>

                    <div class="w-[64px] h-[64px] lg:w-[86px] lg:h-[86px] bg-white flex justify-center items-center rounded-[10px]">
                        <div class="flex flex-col items-center gap-2">
                            <p id="minutes"
                                class="text-[28px] lg:text-[44px] font-fat-yekanbakh text-[#EE6003] mt-[-20px]">0</p>
                            <p class="text-lg font-black text-[#EE6003] mt-[-30px]">دقیقه</p>
                        </div>
                    </div>

                    <div class="w-[64px] h-[64px] lg:w-[86px] lg:h-[86px] bg-white flex justify-center items-center rounded-[10px]">
                        <div class="flex flex-col items-center gap-2">
                            <p id="hours"
                                class="text-[28px] lg:text-[44px] font-fat-yekanbakh text-[#EE6003] mt-[-20px]">0</p>
                            <p class="text-lg font-black text-[#EE6003] mt-[-30px]">ساعت</p>
                        </div>
                    </div>
                    <div class="w-[64px] h-[64px] lg:w-[86px] lg:h-[86px] bg-white flex justify-center items-center rounded-[10px]">
                        <div class="flex flex-col items-center gap-2">
                            <p id="days"
                                class="text-[28px] lg:text-[44px] font-fat-yekanbakh text-[#EE6003] mt-[-20px]">0</p>
                            <p class="text-lg font-black text-[#EE6003] mt-[-30px]">روز</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        </div>
    </section>
    <!-- end-navar-takhfif ----------------------------------------------------------------->

    <!-- start-slider-games ---------------------------------------------------------------->
    <?php
    $roomArgs = [
        "source"        => "home_discounts_event",
        "most_discount" => false, // اگه این true بشه به طور خودکار سورت های دیگه از کار میفته. اگه false باشه سورت های دیگه موثر میشه
        "params"        => [
            "product_type"  => "اتاق فرار",
            "schedule"      => -1,
            "city_id"       => -1,
            "sort_type"     => 'topsale', // hottest
        ]
    ];
    $discountEventRoom = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $roomArgs)));
    if (!is_null($discountEventRoom->products) and !empty($discountEventRoom->products) and (strlen($discountEventRoom->products) > 0)):
    ?>
        <section class="container mx-auto mb-[70px] mt-10 px-4 sm:px-6 md:px-8">
            <section class="max-w-full py-4 md:py-5 lg:py-9">
                <div class="mb-6 md:mb-8">
                    <input type="hidden" id="room" data-source="home_discounts_event"
                        data-params='{"schedule":-1,"city_id":-1,"product_type":"اتاق فرار"}'>
                    <div class="flex justify-between gap-4 max-lg:flex-col">
                        <div class="items-center gap-6 md:flex">
                            <h2 class="flex items-center gap-4 text-lg">
                                <span class="mb-1 block rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9"
                                        fill="none">
                                        <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                            fill="#09192D" stroke="white" stroke-width="1.5" />
                                    </svg>
                                </span>
                                اتاق فرارهای دارای سانس
                            </h2>
                        </div>
                        <div class="relative grid content-center grid-cols-2 gap-4">
                            <div class="w-full min-w-[156px] grow">
                                <div class="relative w-full max-w-xs">
                                    <div class="relative sans-dropdown-container">
                                        <button type="button"
                                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                                            <span id="cities-box-title" class="relative text-gray-700">
                                                شهر مورد نظر
                                            </span>
                                            <svg class="w-4 h-4 m-0 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                                            <div class="p-2">
                                                <input type="text"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md city-search"
                                                    placeholder="جستجوی شهر...">
                                            </div>
                                            <div class="overflow-auto max-h-60">
                                                <div id="cities-box-list" class="py-1 city-options">
                                                    <?php
                                                    $cities = cities_type('اتاق فرار');
                                                    echo '<label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                    <input type="radio" name="city_id" value="-1" class="hidden option-sans-input" data-input="room"/>
                                                    همه
                                                    </label>';
                                                    foreach ($cities as $city) {
                                                        $checked = null;
                                                        if ($city['city_id'] == json_decode($_GET['city_id'])) {
                                                            $checked = 'checked';
                                                        }
                                                        echo '<label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                    <input type="radio" name="city_id" value="[' . $city['city_id'] . ']" class="hidden option-sans-input" ' . $checked . ' data-input="room"/>
                                                    ' . $city['city_name'] . '
                                                    </label>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full min-w-[156px] grow">
                                <div class="relative w-full max-w-xs">
                                    <div class="relative sans-dropdown-container">
                                        <button type="button"
                                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                                            <span class="text-gray-700 text-nowrap">
                                                همه
                                            </span>
                                            <svg class="w-4 h-4 m-0 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                                            <div class="overflow-auto max-h-60">
                                                <div class="py-1">
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="-1"
                                                            class="hidden option-sans-input" data-input="room" />
                                                        همه
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $todayStart ?>,<?= $todayEnd ?>]"
                                                            class="hidden option-sans-input" data-input="room" />
                                                        امروز
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]"
                                                            class="hidden option-sans-input" data-input="room" />
                                                        فردا
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]"
                                                            class="hidden option-sans-input" data-input="room" />
                                                        پس فردا
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden embla_normal">
                    <div class="embla__viewport">
                        <div id="room-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[200px] child:py-2.5 child:relative">
                            <?= $discountEventRoom->products ?>
                        </div>
                    </div>
                    <button class="embla__button embla__button--prev room-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
                            </g>
                            <defs>
                                <clipPath id="arrow_aa">
                                    <path fill="#fff" d="M0 0h30v113H0z"></path>
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                    <button class="embla__button embla__button--next room-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
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


        </section>
        <svg xmlns="http://www.w3.org/2000/svg" width="1332" height="12" viewBox="0 0 1332 12" fill="none"
            class="my-[70px] container mx-auto mb-[70px] mt-10 px-4 sm:px-6 md:px-8">
            <path d="M1326 6.00012L6 6" stroke="#E4EBF0" stroke-width="11" stroke-linecap="round" />
        </svg>
    <?php endif; ?>
    <?php
    $laserArgs = [
        "source" => "home_discounts_event",
        "params" => [
            "product_type" => "لیزرتگ",
            "schedule" => -1,
            "city_id" => -1
        ]
    ];
    $discountEventLaser = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $laserArgs)));
    if (!is_null($discountEventLaser->products) and !empty($discountEventLaser->products) and (strlen($discountEventLaser->products) > 0)):
    ?>
        <section class="container mx-auto mb-[70px] mt-10 px-4 sm:px-6 md:px-8">
            <section class="max-w-full py-4 md:py-5 lg:py-9">
                <div class="mb-6 md:mb-8">
                    <input type="hidden" id="laser" data-source="home_discounts_event"
                        data-params='{"schedule":-1,"city_id":-1,"product_type":"لیزرتگ"}'>
                    <div class="flex justify-between gap-4 max-lg:flex-col">
                        <div class="items-center gap-6 md:flex">
                            <h2 class="flex items-center gap-4 text-lg">
                                <span class="mb-1 block rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9"
                                        fill="none">
                                        <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                            fill="#09192D" stroke="white" stroke-width="1.5" />
                                    </svg>
                                </span>
                                لیزرتگ‌های دارای سانس
                            </h2>
                        </div>
                        <div class="relative grid content-center grid-cols-2 gap-4">
                            <div class="w-full min-w-[156px] grow">
                                <div class="relative w-full max-w-xs">
                                    <div class="relative sans-dropdown-container">
                                        <button type="button"
                                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                                            <span id="cities-box-title" class="relative text-gray-700">
                                                شهر مورد نظر
                                            </span>
                                            <svg class="w-4 h-4 m-0 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                                            <div class="p-2">
                                                <input type="text"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md city-search"
                                                    placeholder="جستجوی شهر...">
                                            </div>
                                            <div class="overflow-auto max-h-60">
                                                <div id="cities-box-list" class="py-1 city-options">
                                                    <?php
                                                    $cities = cities_type('لیزرتگ');
                                                    echo '<label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                    <input type="radio" name="city_id" value="-1" class="hidden option-sans-input" data-input="laser"/>
                                                    همه
                                                    </label>';
                                                    foreach ($cities as $city) {
                                                        $checked = null;
                                                        if ($city['city_id'] == json_decode($_GET['city_id'])) {
                                                            $checked = 'checked';
                                                        }
                                                        echo '<label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                    <input type="radio" name="city_id" value="[' . $city['city_id'] . ']" class="hidden option-sans-input" ' . $checked . ' data-input="laser"/>
                                                    ' . $city['city_name'] . '
                                                    </label>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full min-w-[156px] grow">
                                <div class="relative w-full max-w-xs">
                                    <div class="relative sans-dropdown-container">
                                        <button type="button"
                                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                                            <span class="text-gray-700 text-nowrap">
                                                همه
                                            </span>
                                            <svg class="w-4 h-4 m-0 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                                            <div class="overflow-auto max-h-60">
                                                <div class="py-1">
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="-1"
                                                            class="hidden option-sans-input" data-input="laser" />
                                                        همه
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $todayStart ?>,<?= $todayEnd ?>]"
                                                            class="hidden option-sans-input" data-input="laser" />
                                                        امروز
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]"
                                                            class="hidden option-sans-input" data-input="laser" />
                                                        فردا
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]"
                                                            class="hidden option-sans-input" data-input="laser" />
                                                        پس فردا
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden embla_normal">
                    <div class="embla__viewport">
                        <div id="laser-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[200px] child:py-2.5 child:relative">
                            <?= $discountEventLaser->products ?>
                        </div>
                    </div>
                    <button class="embla__button embla__button--prev laser-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
                            </g>
                            <defs>
                                <clipPath id="arrow_aa">
                                    <path fill="#fff" d="M0 0h30v113H0z"></path>
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                    <button class="embla__button embla__button--next laser-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
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


        </section>
        <svg xmlns="http://www.w3.org/2000/svg" width="1332" height="12" viewBox="0 0 1332 12" fill="none"
            class="my-[70px] container mx-auto mb-[70px] mt-10 px-4 sm:px-6 md:px-8">
            <path d="M1326 6.00012L6 6" stroke="#E4EBF0" stroke-width="11" stroke-linecap="round" />
        </svg>
    <?php endif; ?>
    <?php
    $cinemaArgs = [
        "source" => "home_discounts_event",
        "params" => [
            "product_type" => "سینما ترس",
            "schedule" => -1,
            "city_id" => -1
        ]
    ];
    $discountEventCinema = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $cinemaArgs)));
    if (!is_null($discountEventCinema->products) and !empty($discountEventCinema->products) and (strlen($discountEventCinema->products) > 0)):
    ?>
        <section class="container mx-auto mb-[70px] mt-10 px-4 sm:px-6 md:px-8">
            <section class="max-w-full py-4 md:py-5 lg:py-9">
                <div class="mb-6 md:mb-8">
                    <input type="hidden" id="cinema" data-source="home_discounts_event"
                        data-params='{"schedule":-1,"city_id":-1,"product_type":"سینما ترس"}'>
                    <div class="flex justify-between gap-4 max-lg:flex-col">
                        <div class="items-center gap-6 md:flex">
                            <h2 class="flex items-center gap-4 text-lg">
                                <span class="mb-1 block rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9"
                                        fill="none">
                                        <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                            fill="#09192D" stroke="white" stroke-width="1.5" />
                                    </svg>
                                </span>
                                سینما ترس‌های دارای سانس
                            </h2>
                        </div>
                        <div class="relative grid content-center grid-cols-2 gap-4">
                            <div class="w-full min-w-[156px] grow">
                                <div class="relative w-full max-w-xs">
                                    <div class="relative sans-dropdown-container">
                                        <button type="button"
                                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                                            <span id="cities-box-title" class="relative text-gray-700">
                                                شهر مورد نظر
                                            </span>
                                            <svg class="w-4 h-4 m-0 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                                            <div class="p-2">
                                                <input type="text"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md city-search"
                                                    placeholder="جستجوی شهر...">
                                            </div>
                                            <div class="overflow-auto max-h-60">
                                                <div id="cities-box-list" class="py-1 city-options">
                                                    <?php
                                                    $cities = cities_type('سینما ترس');
                                                    echo '<label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                    <input type="radio" name="city_id" value="-1" class="hidden option-sans-input" data-input="cinema"/>
                                                    همه
                                                    </label>';
                                                    foreach ($cities as $city) {
                                                        $checked = null;
                                                        if ($city['city_id'] == json_decode($_GET['city_id'])) {
                                                            $checked = 'checked';
                                                        }
                                                        echo '<label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                    <input type="radio" name="city_id" value="[' . $city['city_id'] . ']" class="hidden option-sans-input" ' . $checked . ' data-input="cinema"/>
                                                    ' . $city['city_name'] . '
                                                    </label>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full min-w-[156px] grow">
                                <div class="relative w-full max-w-xs">
                                    <div class="relative sans-dropdown-container">
                                        <button type="button"
                                            class="sans-dropdown-button w-full bg-white border border-[#ecf2f7]/80 rounded-lg max-lg:shadow-13 h-[48px] px-4 py-2 text-right flex items-center justify-between">
                                            <span class="text-gray-700 text-nowrap">
                                                همه
                                            </span>
                                            <svg class="w-4 h-4 m-0 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div class="absolute z-50 hidden w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg sans-options">
                                            <div class="overflow-auto max-h-60">
                                                <div class="py-1">
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="-1"
                                                            class="hidden option-sans-input" data-input="cinema" />
                                                        همه
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $todayStart ?>,<?= $todayEnd ?>]"
                                                            class="hidden option-sans-input" data-input="cinema" />
                                                        امروز
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $tomorrowStart ?>,<?= $tomorrowEnd ?>]"
                                                            class="hidden option-sans-input" data-input="cinema" />
                                                        فردا
                                                    </label>
                                                    <label class="block w-full px-4 py-2 transition cursor-pointer option-sans hover:text-primary-500 hover:bg-gray-100">
                                                        <input type="radio" name="schedule"
                                                            value="[<?= $dayAfterTomorrowStart ?>,<?= $dayAfterTomorrowEnd ?>]"
                                                            class="hidden option-sans-input" data-input="cinema" />
                                                        پس فردا
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden embla_normal">
                    <div class="embla__viewport">
                        <div id="cinema-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[200px] child:py-2.5 child:relative">
                            <?= $discountEventCinema->products ?>
                        </div>
                    </div>
                    <button class="embla__button embla__button--prev cinema-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
                            </g>
                            <defs>
                                <clipPath id="arrow_aa">
                                    <path fill="#fff" d="M0 0h30v113H0z"></path>
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                    <button class="embla__button embla__button--next cinema-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
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


        </section>
        <svg xmlns="http://www.w3.org/2000/svg" width="1332" height="12" viewBox="0 0 1332 12" fill="none"
            class="my-[70px] container mx-auto mb-[70px] mt-10 px-4 sm:px-6 md:px-8">
            <path d="M1326 6.00012L6 6" stroke="#E4EBF0" stroke-width="11" stroke-linecap="round" />
        </svg>
    <?php endif; ?>
    <!-- end-slider-games ------------------------------------------------------------------>
</section>


<?php get_footer(); ?>
<script>
    function updateTimer() {
        const now = new Date();
        const targetTime = new Date();

        // تنظیم زمان هدف به 24:00
        targetTime.setHours(24, 0, 0, 0);

        // اگر زمان فعلی از 24:00 گذشته باشد، هدف را به روز بعد منتقل کنید
        if (now > targetTime) {
            targetTime.setDate(targetTime.getDate() + 1);
        }

        const diff = targetTime - now; // تفاوت زمان تا 24:00

        let hours = Math.floor(diff / (1000 * 60 * 60));
        let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((diff % (1000 * 60)) / 1000);

        // نمایش زمان در عناصر HTML
        document.getElementById('hours').textContent = hours;
        document.getElementById('minutes').textContent = minutes;
        document.getElementById('seconds').textContent = seconds;
    }

    setInterval(updateTimer, 1000); // هر ثانیه تایمر را آپدیت کنید
    updateTimer(); // تایمر را بلافاصله اجرا کنید

    jQuery(document).ready(function($) {
        $('.option-sans-input').on('click', function() {
            let inputName = $(this).attr('data-input')
            let params = $(this).val();
            let keyParams = $(this).attr('name');
            let input = $(`#${inputName}`);
            let inputSource = input.attr('data-source');
            let currentParams = JSON.parse(input.attr('data-params'));
            $.each(currentParams, function(key, value) {
                if (key === keyParams) {
                    currentParams[key] = JSON.parse(params);
                }
            });
            let resultString = JSON.stringify(currentParams);
            input.attr('data-params', resultString);
            $.ajax({
                type: 'POST',
                url: 'https://' + location.hostname + '/web-service/web-service.php',
                data: {
                    "async": false,
                    "type": "sort_products_get",
                    "data": {
                        "source": inputSource,
                        "params": currentParams
                    }
                },
                dataType: "json",
                beforeSend: function() {
                    $(`.${inputName}-btn`).hide();
                    $(`#${inputName}-slider`).empty().append('<div style="height:350px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">لطفا منتظر باشید...</div>')
                },
                success: function(data) {
                    if (data.products) {
                        setTimeout(function() {
                            $(`#${inputName}-slider`).empty().append(data.products);
                            $(`.${inputName}-btn`).show();
                        }, 1);
                    } else {
                        $(`#${inputName}-slider`).empty().append('<div style="height:350px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">سرگرمی یافت نشد</div>')
                    }
                },
            });
        })

        var today = new Date();
        var dayOfWeek = today.getDay(); // 0: Sunday, 1: Monday, ..., 6: Saturday  
        var mondayIndex = 0; // Monday is 1 in getDay()

        // محاسبه اختلاف روزها  
        var difference;
        if (dayOfWeek <= mondayIndex) {
            difference = mondayIndex - dayOfWeek; // اگر امروز قبل از دوشنبه است  
        } else {
            difference = 7 - (dayOfWeek - mondayIndex); // اگر امروز بعد از دوشنبه است  
        }

        document.getElementById('days').textContent = difference;

    })
</script>