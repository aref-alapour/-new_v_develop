<?php
$current_date = strtotime(date('Y-m-d 00:00:00'));

$dates = [];
for ($i = 1; $i <= 45; $i++) {
    $dates[] = $current_date + (60 * 60 * 24 * $i);
}

?>
<style>
    #playing_now img {
        display: none;
    }
    /* حالت دیفالت: دکمه سمت چپ (بستن) */
    .knob-off {
        transform: translateX(0); /* در موقعیت پیش فرض left-1 باقی می ماند */
    }

    /* حالت فعال: دکمه سمت راست (باز کردن) */
    /* چون عرض کل 4rem (64px) و عرض دکمه 1.75rem (28px) است، باید حدود 28 پیکسل به راست برود */
    .knob-on {
        transform: translateX(28px); 
    }

    /* تغییر رنگ پس‌زمینه وقتی توگل روی باز کردن است */
    .bg-open-mode {
        background-color: #3b82f6 !important; /* رنگ آبی برای حالت باز کردن (اختیاری) */
    }

    #modalPhoneReserve .phone-reserve-panel {
        width: min(720px, 94vw);
    }

    #modalPhoneReserve .phone-reserve-user-results {
        z-index: 20;
    }

    #phone-reserve-new-user-fields.phone-reserve-collapsed {
        display: none;
    }

    #phone-reserve-new-user-toggle.is-active {
        background-color: #fffbeb;
        border-color: #f59e0b;
        color: #b45309;
    }

</style>
<input type="hidden" id="current_product_id">

<div class="overflow-hidden">

    <div class="flex justify-between items-center">

        <h1 class="text-base font-extrabold lg:text-2xl">مدیریت سانس</h1>

        <div class="relative w-[582px] h-[58px]" style="z-index: 9;">
            <input id="gameSearch" class="w-[582px] h-[58px] border border-[#E4EBF0] bg-[#FAFDFF] rounded-xl outline-none px-6 py-5 text-xs font-yekan-bold text-navyBlue" placeholder="جست و جو بازی" />
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" fill="none" class="absolute w-4 h-4 top-5 left-6">
                <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
            </svg>
            <div id="lg-search-result-list" class="max-h-75 divide-y divide-[#E4EBF0] overflow-y-auto px-4 py-5" style="background: #f3f3f3;display: none"></div>
        </div>

        <p class="text-lg font-yekan-fat text-navyBlue">
            <span class="text-base font-medium text-grayy">امروز </span>
            <span class="text-orangee font-extrabold"><?= jdate('d') ?></span>.
            <span class="font-extrabold"><?= jdate('F') ?></span>.
            <span class="font-extrabold"><?= jdate('Y') ?></span>
        </p>

    </div>

    <div class="flex max-w-full">
        <div class="after_load flex items-center py-2 mt-8 overflow-x-hidden" style="display: none">

            <div class="py-2">
                <button id="today_btn" type="button" data-datepicker="<?php echo esc_attr($current_date); ?>"
                    class="flex flex-col items-center justify-center w-16 h-16 leading-none text-white border active shrink-0 gap-y-2 rounded-xl border-primary-700 bg-primary-500 lg:h-21">
                    <span class="lg:text-[22px] lg:font-extrabold">
                        امروز
                    </span>
                </button>
            </div>

            <div class="swiper date-picker max-w-[1200px]">
                <div class="swiper-wrapper py-2">
                    <?php foreach ($dates as $index => $date) { ?>
                        <div class="swiper-slide" dir="ltr">
                            <button type="button" data-datepicker="<?php echo esc_attr($date); ?>"
                                class="flex h-16 w-16 shrink-0 flex-col items-center justify-center gap-y-2 rounded-xl border border-[#DBE2EA] bg-white leading-none lg:h-21">
                                <span class="lg:order-1 lg:text-[34px] lg:font-extrabold">
                                    <?php echo esc_html(jdate('d', $date)) ?>
                                </span>
                                <span class="lg:font-extrabold">
                                    <?php echo esc_html(jdate('l', $date)) ?>
                                </span>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="after_load px-[121px] mt-10" style="display: none">
        <div class="flex justify-between items-center w-full gap-x-10">
            <h3 id="current_product_title" class="text-xl font-bold text-orangee"></h3>
            <!-- بخش انتخاب بازه زمانی و دکمه‌های عملیات گروهی -->
            <div class="flex flex-col md:flex-row items-center gap-4 bg-white grow">
                <!-- تریگر تقویم بازه زمانی (کپی شده از فایل گزارش) -->
                <div class="date-range-container relative grow">
                    <div class="date-range-trigger filter-input-field cursor-pointer flex items-center justify-between p-2 border border-gray-300 rounded-md" id="date-range-trigger">
                        <span class="date-range-text text-gray-600">انتخاب بازه زمانی</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <input type="hidden" id="date-range-data" name="date_range" value="">
                </div>
                <!-- دکمه‌های عملیات -->
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" id="btn-bulk-close-range" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md transition flex-1 md:flex-none">
                        بستن سانس‌ها
                    </button>
                    <button type="button" id="btn-bulk-open-range" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md transition flex-1 md:flex-none">
                        باز کردن سانس‌ها
                    </button>
                </div>
            </div>
            <div id="toggle_close_open"></div>
        </div>
        <hr class="w-full border-[#E4EBF0] border-4 rounded-lg mt-5">
    </div>

    <div id="sessionsContainer" class="after_load mt-8 grid grid-cols-2 gap-x-4.5 gap-y-8 lg:grid-cols-4 lg:gap-x-12.5 px-[121px]" style="display: none"></div>

    <div id="playing_now" class="after_load mt-12.5" style="display: none"></div>

    <div class="initial font-extrabold text-center text-grayy mt-54">برای یافتن بازی مورد نظر، از بخش <br> جستجوی بالا استفاده کنید...</div>

</div>
<!-- -----------modal------------------------------------- -->
<div id="modalOverlayInfo" class="fixed inset-0 z-50 hidden backdrop-blur-sm bg-white/30">
    <div class="flex flex-col p-[30px] rounded-xl bg-white w-[355px] h-[194px] border border-[#DBE2EA] shadow-[0px_1px_0px_0px_#DBE2EA] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex justify-between">
            <div class="flex flex-col">
                <div class="flex gap-x-4">
                    <p class="text-lg font-bold text-navyBlue" name="name"></p>
                    <div class="rounded-3xl text-xs font-bold py-1 px-2.5" name="level_title" style=""></div>
                </div>
                <a href="tel:" class="text-base font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer" name="phone"></a>
            </div>
            <a href="tel:" class="text-base font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer" name="phone-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="46" height="46" viewBox="0 0 46 46" fill="none">
                    <rect width="46" height="46" rx="23" fill="#02C96F" />
                    <g filter="url(#filter0_d_630_652)">
                        <path d="M29.9295 34.4998C28.9272 34.4998 27.5192 34.1373 25.4107 32.9596C22.8469 31.5221 20.8637 30.1949 18.3137 27.6521C15.8551 25.1955 14.6586 23.605 12.9841 20.5584C11.0924 17.1187 11.4149 15.3156 11.7753 14.545C12.2046 13.624 12.8383 13.0731 13.6573 12.5263C14.1225 12.2216 14.6148 11.9604 15.128 11.746C15.1793 11.7239 15.2271 11.7028 15.2697 11.6838C15.5239 11.5693 15.909 11.3963 16.3968 11.5812C16.7224 11.7033 17.013 11.9534 17.468 12.4026C18.401 13.3226 19.676 15.3716 20.1464 16.3778C20.4622 17.056 20.6712 17.5037 20.6717 18.0058C20.6717 18.5937 20.3759 19.047 20.017 19.5363C19.9497 19.6282 19.8829 19.716 19.8182 19.8012C19.4275 20.3146 19.3417 20.463 19.3982 20.7279C19.5127 21.2603 20.3667 22.8451 21.77 24.2452C23.1734 25.6452 24.7129 26.4451 25.2475 26.5591C25.5237 26.6181 25.6752 26.5288 26.2051 26.1242C26.2811 26.0662 26.3592 26.0061 26.4408 25.9461C26.9882 25.5389 27.4206 25.2509 27.9947 25.2509H27.9977C28.4974 25.2509 28.9251 25.4676 29.6338 25.8249C30.5581 26.2911 32.669 27.5494 33.5949 28.4833C34.0452 28.9371 34.2963 29.2267 34.419 29.5517C34.6039 30.0409 34.4298 30.4244 34.3163 30.6811C34.2973 30.7237 34.2763 30.7705 34.2542 30.8223C34.0381 31.3345 33.7752 31.8256 33.4691 32.2896C32.9232 33.1059 32.3702 33.7379 31.4469 34.1676C30.9728 34.3919 30.454 34.5055 29.9295 34.4998Z" fill="white" />
                    </g>
                    <defs>
                        <filter id="filter0_d_630_652" x="7.5" y="11.5" width="31" height="31" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="4" />
                            <feGaussianBlur stdDeviation="2" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_630_652" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_630_652" result="shape" />
                        </filter>
                    </defs>
                </svg>
            </a>

            <button id="closeModalInfo" class="absolute top-2 left-2 text-red-500 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <hr class="text-[#E4EBF0] my-[20px]" />
        <div class="flex justify-between">
            <div class="flex gap-2">
                <p class="text-grayy text-sm font-bold">کد رزرو</p>
                <p class="text-base font-bold text-navyBlue" name="order_id"></p>
            </div>

            <div class="flex gap-2">
                <p class="text-grayy text-sm font-bold">تعداد</p>
                <p class="text-base font-bold text-navyBlue" name="quantity"></p>
            </div>
        </div>

        <div class="flex justify-start gap-[13px]">
            <p class="text-sm font-bold text-grayy">تاریخ رزرو</p>
            <div class="flex justify-center gap-2 text-base font-bold text-navyBlue">
                <p class="text-base font-bold text-navyBlue" name="date"></p>
            </div>
        </div>

    </div>
</div>

<!-- مودال رزرو تلفنی -->
<div id="modalPhoneReserve" class="fixed inset-0 z-[60] hidden backdrop-blur-sm bg-white/30">
    <div class="phone-reserve-panel flex flex-col gap-3 p-5 sm:p-6 rounded-xl bg-white max-h-[90vh] overflow-y-auto border border-[#DBE2EA] shadow-lg absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex justify-between items-start gap-3 border-b border-[#E4EBF0] pb-3">
            <div>
                <h3 class="text-lg font-extrabold text-navyBlue">رزرو تلفنی</h3>
                <p id="phone-reserve-sans-label" class="text-sm text-grayy mt-0.5"></p>
            </div>
            <button type="button" id="closeModalPhoneReserve" class="text-red-500 hover:text-red-600 shrink-0 p-1" aria-label="بستن">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <input type="hidden" id="phone-reserve-product-id" value="">
        <input type="hidden" id="phone-reserve-sans-ts" value="">
        <input type="hidden" id="phone-reserve-user-id" value="">

        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[240px] relative z-10">
                <label class="text-sm font-bold text-navyBlue block mb-1">جستجوی پلیر (موبایل)</label>
                <div class="flex gap-2">
                    <input type="text" id="phone-reserve-user-search" class="flex-1 min-w-0 border border-[#DBE2EA] rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#1447E6]" placeholder="09123456789" autocomplete="off" inputmode="numeric" />
                    <button type="button" id="phone-reserve-user-search-btn" class="shrink-0 h-[42px] w-[42px] flex items-center justify-center rounded-lg border border-[#DBE2EA] bg-[#FAFDFF] hover:bg-[#E8F0FF] text-navyBlue disabled:opacity-50" title="جستجو">
                        <svg id="phone-reserve-search-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <svg id="phone-reserve-search-spinner" class="h-5 w-5 animate-spin hidden text-[#1447E6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    <button type="button" id="phone-reserve-new-user-toggle" class="shrink-0 h-[42px] px-3 rounded-lg border border-[#DBE2EA] bg-white hover:bg-amber-50 text-xs font-yekan-bold text-navyBlue whitespace-nowrap">
                        کاربر جدید
                    </button>
                </div>
                <p id="phone-reserve-user-search-status" class="text-xs absolute text-[#1447E6] font-bold mt-1 hidden">در حال جستجو…</p>
                <p id="phone-reserve-search-hint" class="text-xs text-amber-700 font-bold mt-1 hidden"></p>
                <div id="phone-reserve-user-results" class="phone-reserve-user-results absolute left-0 right-0 top-full mt-1 max-h-36 overflow-y-auto border border-[#E4EBF0] rounded-lg hidden bg-white shadow-md divide-y divide-[#E4EBF0]"></div>
                <p id="phone-reserve-user-selected" class="text-sm text-green-700 font-bold mt-2 absolute hidden"></p>
            </div>
            <div class="w-28 shrink-0">
                <label class="text-sm font-bold text-navyBlue block mb-1">تعداد نفر</label>
                <input type="number" id="phone-reserve-quantity" min="1" max="30" value="1" class="w-full border border-[#DBE2EA] rounded-lg px-3 py-2.5 text-sm outline-none text-center" />
            </div>
        </div>

        <div id="phone-reserve-new-user-fields" class="phone-reserve-collapsed overflow-hidden rounded-lg border border-amber-200 bg-amber-50/90">
            <div class="p-3 space-y-3">
                <p class="text-xs font-bold text-amber-800">ثبت مشتری جدید — با ثبت رزرو، حساب customer ساخته می‌شود.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-bold text-navyBlue block mb-1">نام <span class="text-pinkk">*</span></label>
                        <input type="text" id="phone-reserve-first-name" class="w-full border border-[#DBE2EA] rounded-lg px-3 py-2 text-sm outline-none bg-white" autocomplete="off" />
                    </div>
                    <div>
                        <label class="text-sm font-bold text-navyBlue block mb-1">نام خانوادگی <span class="text-pinkk">*</span></label>
                        <input type="text" id="phone-reserve-last-name" class="w-full border border-[#DBE2EA] rounded-lg px-3 py-2 text-sm outline-none bg-white" autocomplete="off" />
                    </div>
                </div>
            </div>
        </div>

        <div>
            <p class="text-sm font-bold text-navyBlue mb-2 mt-4">نوع پرداخت</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label class="flex items-start gap-2 cursor-pointer rounded-lg border border-[#DBE2EA] p-2.5 phone-reserve-pay-option has-[:checked]:border-[#1447E6] has-[:checked]:bg-[#FAFDFF]">
                    <input type="radio" name="phone_reserve_payment_type" value="partial" class="mt-0.5 shrink-0" checked />
                    <span class="flex-1 min-w-0">
                        <span class="block text-xs sm:text-sm font-bold text-navyBlue">پیش‌پرداخت (بیعانه)</span>
                        <span id="phone-reserve-price-partial" class="block text-sm sm:text-base font-extrabold text-orangee mt-0.5">—</span>
                        <span id="phone-reserve-hint-partial" class="block text-[11px] text-grayy mt-0.5 leading-tight"></span>
                    </span>
                </label>
                <label class="flex items-start gap-2 cursor-pointer rounded-lg border border-[#DBE2EA] p-2.5 phone-reserve-pay-option has-[:checked]:border-[#1447E6] has-[:checked]:bg-[#FAFDFF]">
                    <input type="radio" name="phone_reserve_payment_type" value="complete" class="mt-0.5 shrink-0" />
                    <span class="flex-1 min-w-0">
                        <span class="block text-xs sm:text-sm font-bold text-navyBlue">پرداخت کامل</span>
                        <span id="phone-reserve-price-complete" class="block text-sm sm:text-base font-extrabold text-orangee mt-0.5">—</span>
                        <span id="phone-reserve-hint-complete" class="block text-[11px] text-grayy mt-0.5 leading-tight"></span>
                    </span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-stretch">
            <div class="rounded-lg bg-[#FAFDFF] border border-[#E4EBF0] p-3 flex flex-col justify-center">
                <p class="text-xs text-grayy font-bold">مبلغ دریافت تلفنی</p>
                <p id="phone-reserve-selected-amount" class="text-lg sm:text-xl font-extrabold text-navyBlue mt-0.5">—</p>
                <p id="phone-reserve-unit-price" class="text-[11px] text-grayy mt-0.5"></p>
            </div>
            <div class="flex flex-col">
                <label class="text-sm font-bold text-navyBlue block mb-1">یادداشت (اختیاری)</label>
                <textarea id="phone-reserve-note" rows="2" class="flex-1 min-h-[72px] w-full border border-[#DBE2EA] rounded-lg px-3 py-2 text-sm outline-none resize-none"></textarea>
            </div>
        </div>

        <p id="phone-reserve-quote-error" class="text-sm text-red-600 hidden"></p>

        <button type="button" id="phone-reserve-submit" class="w-full py-3 rounded-lg bg-[#1447E6] hover:bg-[#0f38b8] text-white font-yekan-bold text-sm disabled:opacity-50">
            ثبت رزرو تلفنی
        </button>
    </div>
</div>

<?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/calendar-module.js"></script>

<script>
    jQuery(document).ready(function($) {

    const teamAjaxUrl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
    const teamAjaxNonce = "<?php echo esc_js( wp_create_nonce( 'team-ajax-nonce' ) ); ?>";

    let phoneReserveQuoteXhr = null;
    let phoneReserveUserSearchXhr = null;
    let phoneReserveLastQuote = null;

    function injectPhoneReserveButtons() {
        $('#sessionsContainer').find('.toggle-btn').each(function() {
            const $toggle = $(this);
            const $card = $toggle.closest('div');
            if ($card.find('.openModalInfo').length || $card.find('.btn-phone-reserve').length) {
                return;
            }
            const label = ($toggle.text() || '').trim();
            if (label !== 'باز') {
                return;
            }
            const tsRaw = $toggle.data('timestamp');
            if (!tsRaw) return;
            const parts = String(tsRaw).split('.');
            const sansTs = parts[0];
            const productId = $toggle.data('product');
            const timeLabel = $toggle.closest('div').find('p, span, h4').first().text().trim() || '';
            const btn = $('<button type="button" class="btn-phone-reserve mt-2 w-full py-1.5 rounded-lg bg-[#1447E6] hover:bg-[#0f38b8] text-white text-xs font-yekan-bold">رزرو تلفنی</button>');
            btn.attr({
                'data-product-id': productId,
                'data-sans-ts': sansTs,
                'data-sans-label': timeLabel
            });
            $toggle.after(btn);
        });
    }

    function formatPriceDisplay(amount) {
        if (amount == null || isNaN(amount)) return '—';
        try {
            return Number(amount).toLocaleString('fa-IR') + ' تومان';
        } catch (e) {
            return String(amount) + ' تومان';
        }
    }

    function updatePhoneReserveSelectedAmount(data) {
        if (!data) return;
        const type = $('input[name="phone_reserve_payment_type"]:checked').val() || 'partial';
        const amount = type === 'complete' ? data.complete_amount : data.partial_amount;
        $('#phone-reserve-selected-amount').text(formatPriceDisplay(amount));
        $('.phone-reserve-pay-option').removeClass('ring-2 ring-[#1447E6]');
        $('input[name="phone_reserve_payment_type"]:checked').closest('.phone-reserve-pay-option').addClass('ring-2 ring-[#1447E6]');
    }

    function applyPhoneReserveQuote(data) {
        phoneReserveLastQuote = data;
        $('#phone-reserve-quote-error').addClass('hidden').text('');
        $('#phone-reserve-price-partial').text(data.partial_formatted || formatPriceDisplay(data.partial_amount));
        $('#phone-reserve-price-complete').text(data.complete_formatted || formatPriceDisplay(data.complete_amount));
        $('#phone-reserve-hint-partial').text(data.partial_hint || '');
        $('#phone-reserve-hint-complete').text(data.complete_hint || '');
        if (data.asli_formatted) {
            $('#phone-reserve-unit-price').text('قیمت هر نفر: ' + data.asli_formatted);
        }
        updatePhoneReserveSelectedAmount(data);
    }

    function fetchPhoneReserveQuote() {
        const productId = $('#phone-reserve-product-id').val();
        const sansTs = $('#phone-reserve-sans-ts').val();
        const quantity = parseInt($('#phone-reserve-quantity').val(), 10) || 1;

        if (!productId || !sansTs) return;

        if (phoneReserveQuoteXhr) phoneReserveQuoteXhr.abort();

        $('#phone-reserve-price-partial, #phone-reserve-price-complete, #phone-reserve-selected-amount').text('…');

        phoneReserveQuoteXhr = $.ajax({
            type: 'POST',
            url: teamAjaxUrl,
            data: {
                action: 'team_ajax_handler',
                nonce: teamAjaxNonce,
                callback: 'sans_phone_reserve',
                operation: 'quote',
                product_id: productId,
                sans_time: sansTs,
                quantity: quantity
            },
            success: function(res) {
                if (res && res.success && res.data) {
                    applyPhoneReserveQuote(res.data);
                } else {
                    const msg = (res && res.data) ? res.data : 'خطا در محاسبه قیمت';
                    $('#phone-reserve-quote-error').removeClass('hidden').text(msg);
                }
            },
            error: function() {
                $('#phone-reserve-quote-error').removeClass('hidden').text('خطا در ارتباط با سرور برای محاسبه قیمت.');
            }
        });
    }

    function phoneReserveSetSearchLoading(isLoading) {
        const $btn = $('#phone-reserve-user-search-btn');
        $('#phone-reserve-search-icon').toggleClass('hidden', isLoading);
        $('#phone-reserve-search-spinner').toggleClass('hidden', !isLoading);
        $btn.prop('disabled', isLoading);
        $('#phone-reserve-user-search-status').toggleClass('hidden', !isLoading);
    }

    function phoneReserveCollapseNewUserPanel() {
        const $panel = $('#phone-reserve-new-user-fields');
        if ($panel.is(':visible')) {
            $panel.slideUp(180, function() {
                $panel.addClass('phone-reserve-collapsed');
            });
        } else {
            $panel.addClass('phone-reserve-collapsed');
        }
        $('#phone-reserve-new-user-toggle').removeClass('is-active');
        $('#phone-reserve-first-name, #phone-reserve-last-name').val('');
    }

    function phoneReserveExpandNewUserPanel() {
        $('#phone-reserve-user-id').val('');
        $('#phone-reserve-user-selected').addClass('hidden').text('');
        $('#phone-reserve-user-results').hide().empty();
        $('#phone-reserve-search-hint').addClass('hidden').text('');
        const $panel = $('#phone-reserve-new-user-fields');
        $panel.removeClass('phone-reserve-collapsed');
        if ($panel.is(':hidden') || $panel.hasClass('phone-reserve-collapsed')) {
            $panel.removeClass('phone-reserve-collapsed').hide().slideDown(200);
        }
        $('#phone-reserve-new-user-toggle').addClass('is-active');
    }

    function phoneReserveClearPlayerSelection() {
        $('#phone-reserve-user-id').val('');
        $('#phone-reserve-user-selected').addClass('hidden').text('');
        $('#phone-reserve-user-results').hide().empty();
        $('#phone-reserve-search-hint').addClass('hidden').text('');
    }

    function runPhoneReserveUserSearch() {
        const term = $('#phone-reserve-user-search').val().trim();
        const digits = term.replace(/\D/g, '');

        if (digits.length < 10) {
            alert('شماره موبایل را کامل وارد کنید (حداقل ۱۰ رقم) و سپس جستجو را بزنید.');
            return;
        }

        if (phoneReserveUserSearchXhr) {
            phoneReserveUserSearchXhr.abort();
        }

        phoneReserveSetSearchLoading(true);
        phoneReserveCollapseNewUserPanel();
        $('#phone-reserve-user-results').hide().empty();

        phoneReserveUserSearchXhr = $.ajax({
            type: 'POST',
            url: teamAjaxUrl,
            data: {
                action: 'team_ajax_handler',
                nonce: teamAjaxNonce,
                callback: 'transactions_user_search',
                phone: term
            },
            success: function(html) {
                phoneReserveSetSearchLoading(false);
                const $list = $('#phone-reserve-user-results');
                $list.html(html || '').show();
                const notFound = (html || '').indexOf('کاربری یافت نشد') !== -1;

                if (notFound) {
                    phoneReserveClearPlayerSelection();
                    $('#phone-reserve-search-hint')
                        .text('کاربری با این شماره نیست. برای ثبت مشتری جدید دکمه «کاربر جدید» را بزنید.')
                        .removeClass('hidden');
                } else {
                    $('#phone-reserve-search-hint').addClass('hidden').text('');
                    $list.find('.team_trans_user_search_item')
                        .removeClass('team_trans_user_search_item')
                        .addClass('phone-reserve-user-pick');
                }
            },
            error: function(xhr, status) {
                phoneReserveSetSearchLoading(false);
                if (status !== 'abort') {
                    $('#phone-reserve-search-hint')
                        .text('خطا در جستجو. دوباره تلاش کنید.')
                        .removeClass('hidden');
                }
            }
        });
    }

    function openPhoneReserveModal($btn) {
        phoneReserveLastQuote = null;
        if (phoneReserveUserSearchXhr) {
            phoneReserveUserSearchXhr.abort();
        }
        phoneReserveSetSearchLoading(false);
        $('#phone-reserve-product-id').val($btn.data('product-id'));
        $('#phone-reserve-sans-ts').val($btn.data('sans-ts'));
        $('#phone-reserve-user-search').val('');
        phoneReserveClearPlayerSelection();
        phoneReserveCollapseNewUserPanel();
        $('#phone-reserve-new-user-fields').addClass('phone-reserve-collapsed').hide();
        $('#phone-reserve-quantity').val(1);
        $('#phone-reserve-note').val('');
        $('input[name="phone_reserve_payment_type"][value="partial"]').prop('checked', true);
        $('#phone-reserve-sans-label').text($btn.data('sans-label') || '');
        $('#phone-reserve-quote-error').addClass('hidden').text('');
        $('#modalPhoneReserve').removeClass('hidden').show();
        fetchPhoneReserveQuote();
    }

    // Initialize calendar module
    const calendar = new PersianCalendar({
        onDateRangeSelected: function(dateRange) {
            // این تابع زمانی که کاربر بازه را انتخاب و تایید میکند اجرا میشود
            // میتوانید تاریخ را در فیلد مخفی قرار دهید یا متن دکمه را عوض کنید
            if (dateRange && dateRange.startDate && dateRange.endDate) {
                const startDateStr = dateRange.startDate.year + '/' + 
                                    dateRange.startDate.month.toString().padStart(2, '0') + '/' + 
                                    dateRange.startDate.day.toString().padStart(2, '0');
                const endDateStr = dateRange.endDate.year + '/' + 
                                    dateRange.endDate.month.toString().padStart(2, '0') + '/' + 
                                    dateRange.endDate.day.toString().padStart(2, '0');
                
                document.getElementById('date-range-data').value = startDateStr + ' - ' + endDateStr;
                document.getElementById('date-range-trigger').innerText = startDateStr + ' تا ' + endDateStr;
            }
        }
    });

    // تابعی که با کلیک روی دکمه، تقویم را باز میکند
    function openDatePicker() {
        calendar.openCalendarModal();
    }

        // --- تابع جدید: استخراج دکمه‌های رادیویی از خروجی AJAX و انتقال به دایو اختصاصی ---
        function extractAndMoveRadioButtons() {
            let radioTemplate = $("#radio-toggle-template");
            if (radioTemplate.length > 0) {
                $("#toggle_close_open").html(radioTemplate.html());
                // حذف قالب از داخل کانتینر سانس‌ها تا در جای تکراری نمایش داده نشود
                radioTemplate.remove(); 
            } else {
                $("#toggle_close_open").empty();
            }
        }

        const datePickerSwiper = new Swiper('.date-picker', {
            slidesPerView: 4.5,
            freeMode: true,
            breakpoints: {
                540: { slidesPerView: 5.5 },
                650: { slidesPerView: 6.5 },
                1280: { slidesPerView: 9.6 },
            },
        });

        const BuildSans = (room, day) => {
            const showSkeleton = () => {
                $(`[data-datepicker="${day}"]`).attr('disabled', 'disabled');
                let out = "";
                for (let i = 0; i < 8; i++) {
                    out += "<div class='w-full h-29 skeleton rounded-xl'></div>";
                }
                $("#sessionsContainer").html(out);
            };
            const onHtml = (html) => {
                $(`[data-datepicker="${day}"]`).removeAttr('disabled');
                $("#sessionsContainer").html(html);
                extractAndMoveRadioButtons();
                injectPhoneReserveButtons();
                datePickerSwiper.update();
            };

            if (window.__EZ_BOOT__?.sub_secret && window.ezBookingApi?.sansManagementWeb) {
                showSkeleton();
                window.ezBookingApi.sansManagementWeb(parseInt(room, 10), parseInt(day, 10))
                    .then((html) => { if (html != null) onHtml(html); })
                    .catch(() => {
                        $(`[data-datepicker="${day}"]`).removeAttr('disabled');
                        $("#sessionsContainer").html('<p class="text-center text-slate-500 p-4">خطا در بارگذاری سانس‌ها.</p>');
                    });
                return;
            }

            console.error('[EZ Booking] Gateway not configured on team sans-management');
            $("#sessionsContainer").html('<p class="text-center text-slate-500 p-4">پیکربندی رزرو در دسترس نیست.</p>');
        }

        $('body').on('click', ".team_sans_game_search_item", function() {
            let product_id = $(this).data('id');
            let title = $(this).data('title');

            $("#current_product_id").val(product_id);
            $("#current_product_title").html(title);

            $('#lg-search-result-list').html('').hide();
            $('#gameSearch').val('');

            $('#today_btn').click();

            $('.after_load').show();
            $('.initial').hide();
        });

        $("body").on('click', "[data-datepicker]", function() {
            let product_id = $("#current_product_id").val();
            let date = $(this).data('datepicker');

            $('#playing_now').html('');

            $("[data-datepicker]").removeClass('active border-primary-700 bg-primary-500 text-white').addClass('border-[#DBE2EA] bg-white');
            $(this).removeClass('border-[#DBE2EA] bg-white').addClass('active border-primary-700 bg-primary-500 text-white');

            // فراخوانی ساخت سانس‌ها (دکمه‌های رادیویی هم اتوماتیک در این مرحله ساخته می‌شوند)
            BuildSans(product_id, date);

            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": `check_playing`,
                    "data": { "day_start_time": date, "product_id": product_id }
                },
                success: function(data) {
                    $('#playing_now').html(data);
                }
            });
        });

        $('body').on('click', ".toggle-btn", function() {
            let $this = $(this),
                action = $this.data('room-action'),
                product = $this.data('product'),
                currentDate = $this.data('timestamp').split('.')[1],
                time = $this.data('timestamp').split('.')[0];
            const spinner = "<div class='spinner' style='margin: 11px auto 0;width: 16px;border: 2px solid rgba(127 127 127 / 50%);display: inline-flex;'></div>";

            if (window.__EZ_BOOT__?.sub_secret && window.ezBookingApi?.toggleSans) {
                $this.attr('disabled', 'disabled').html(spinner);
                window.ezBookingApi.toggleSans(action, parseInt(product, 10), parseInt(time, 10))
                    .then(() => BuildSans(product, currentDate))
                    .catch(() => console.error('[EZ Booking] toggleSans failed'))
                    .finally(() => $this.removeAttr('disabled'));
                return;
            }

            console.error('[EZ Booking] Gateway not configured for toggle sans');
        });

        // --- رویداد جدید: کنترل کلیک روی دکمه‌های رادیویی "باز کردن همه" و "بستن همه" ---
        $(document).on('change', 'input[name="bulk_action"]', function() {
            let selectedAction = $(this).val(); // مقدار 'open_all' یا 'close_all'
            let actionType = selectedAction === 'open_all' ? "open_all_sanses" : "close_all_sanses";
            
            let product_id = $("#current_product_id").val();
            let active_date_elem = $("[data-datepicker].active");
            
            // گرفتن تاریخ روز انتخاب شده
            let day_start_time = active_date_elem.length > 0 
                ? active_date_elem.data('datepicker') 
                : "<?php echo $current_date; ?>";

            // غیرفعال کردن دکمه‌های رادیویی حین ارسال درخواست
            $('input[name="bulk_action"]').prop('disabled', true);

            $(`[data-datepicker="${day_start_time}"]`).attr('disabled', 'disabled');
            let out = "<div class='w-full h-29 skeleton rounded-xl'></div>".repeat(8);
            $("#sessionsContainer").html(out);

            const finishBulk = () => {
                $('input[name="bulk_action"]').prop('disabled', false);
                $(`[data-datepicker="${day_start_time}"]`).removeAttr('disabled');
            };

            if (window.__EZ_BOOT__?.sub_secret && window.ezBookingApi?.bulkToggleDay) {
                window.ezBookingApi.bulkToggleDay(actionType, parseInt(product_id, 10), parseInt(day_start_time, 10))
                    .then((response) => {
                        if (response && response.success) {
                            BuildSans(product_id, day_start_time);
                        } else {
                            alert((response && response.data && response.data.error) || 'خطایی در تغییر وضعیت گروهی رخ داد.');
                            BuildSans(product_id, day_start_time);
                        }
                    })
                    .catch(() => {
                        alert('خطایی در تغییر وضعیت گروهی رخ داد.');
                        BuildSans(product_id, day_start_time);
                    })
                    .finally(finishBulk);
                return;
            }

            finishBulk();
            alert('پیکربندی رزرو در دسترس نیست.');
        });

        // ... جستجوی بازی (بدون تغییر) ...
        $('body').on('input', "#gameSearch", function() {
            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": `game_search`,
                    "data": { "term": $(this).val() }
                },
                success: function(data) {
                    $('#lg-search-result-list').show().html(data);
                }
            });
        });

        // ... مدال info (بدون تغییر) ...
        $('body').on('click', ".openModalInfo", function() {
            var raw = $(this).attr('data-user-info');
            if (raw.indexOf('&quot;') !== -1 || raw.indexOf('&amp;') !== -1) {
                var txt = document.createElement('textarea');
                txt.innerHTML = raw;
                raw = txt.value;
            }
            try {
                var info = JSON.parse(raw);
                $('#modalOverlayInfo').find('[name="name"]').text(info.name);
                let formattedPhone = info.phone;
                if (info.phone) {
                    if (info.phone.startsWith('0')) {
                        formattedPhone = '+98' + info.phone.substring(1);
                    } else if (info.phone.startsWith('9')) {
                        formattedPhone = '+98' + info.phone;
                    }
                }
                $('#modalOverlayInfo').find('[name="phone"]').text(info.phone).attr('href', 'tel:' + formattedPhone);
                $('#modalOverlayInfo').find('[name="phone-icon"]').attr('href', 'tel:' + formattedPhone);
                $('#modalOverlayInfo').find('[name="order_id"]').text(info.order_id);
                $('#modalOverlayInfo').find('[name="quantity"]').text(info.quantity);
                $('#modalOverlayInfo').find('[name="date"]').text(info.date || '');

                if (info.level_title) {
                    $('#modalOverlayInfo').find('[name="level_title"]').text(info.level_title);
                }

                if (info.level_color) {
                    let cleanColor = info.level_color.replace(/[\[\]"]/g, '');
                    if (!cleanColor.startsWith('#')) cleanColor = '#' + cleanColor;
                    let hexColor = cleanColor.startsWith('#') ? cleanColor.substring(1) : cleanColor;
                    const r = parseInt(hexColor.substr(0, 2), 16);
                    const g = parseInt(hexColor.substr(2, 2), 16);
                    const b = parseInt(hexColor.substr(4, 2), 16);
                    const rgbaColor = `rgba(${r}, ${g}, ${b}, 0.2)`;
                    $('#modalOverlayInfo').find('[name="level_title"]').css({
                        'background-color': rgbaColor,
                        'color': cleanColor
                    });
                }
                $('#modalOverlayInfo').removeClass('hidden').show();
            } catch (e) {
                console.error('Invalid JSON in data-user-info:', e, raw);
            }
        });

        $('body').on('click', "#closeModalInfo", function() { $('#modalOverlayInfo').addClass('hidden').hide(); });
        $('body').on('click', "#modalOverlayInfo", function(e) { if (e.target === this) { $('#modalOverlayInfo').addClass('hidden').hide(); } });
        $(document).on('keydown', function(e) { if (e.key === 'Escape' && !$('#modalOverlayInfo').hasClass('hidden')) { $('#modalOverlayInfo').addClass('hidden').hide(); } });
        
        // فعال‌سازی تریگر تقویم بازه زمانی
        const dateRangeTrigger = document.getElementById('date-range-trigger');
        if (dateRangeTrigger) {
            dateRangeTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                if(typeof calendar !== 'undefined') {
                    calendar.openCalendarModal();
                } else {
                    alert("ماژول تقویم بارگذاری نشده است.");
                }
            });
        }

        // تابع ارسال درخواست گروهی بر اساس بازه زمانی
        function handleBulkRangeAction(actionType) {
            let dateRange = $("#date-range-data").val(); // فرمت نمونه: 1403/01/01 - 1403/01/15
            let product_id = $("#current_product_id").val();

            if (!dateRange || !dateRange.includes('-')) {
                alert("لطفاً ابتدا یک بازه زمانی معتبر انتخاب کنید.");
                return;
            }

            if (!product_id) {
                alert("محصولی انتخاب نشده است.");
                return;
            }

            // جدا کردن تاریخ شروع و پایان
            let dates = dateRange.split('-');
            let startDate = dates[0].trim();
            let endDate = dates[1].trim();

            let actionName = actionType === 'close' ? 'بستن' : 'باز کردن';
            if (!confirm(`آیا از ${actionName} تمام سانس‌های (فروخته نشده) از تاریخ ${startDate} تا ${endDate} مطمئن هستید؟`)) {
                return;
            }

            // ارسال به بک‌اند
            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": "bulk_date_range_action",
                    "data": { 
                        "start_date": startDate, 
                        "end_date": endDate, 
                        "product_id": product_id,
                        "action": actionType
                    }
                },
                beforeSend: function() {
                    $("#btn-bulk-close-range, #btn-bulk-open-range").prop('disabled', true).css('opacity', '0.5');
                },
                success: function(response) {
                    $("#btn-bulk-close-range, #btn-bulk-open-range").prop('disabled', false).css('opacity', '1');
                    alert("عملیات با موفقیت انجام شد.");
                    
                    // رفرش کردن سانس‌های روزی که در حال نمایش است
                    let active_date = $("[data-datepicker].active").data('datepicker');
                    if(active_date) BuildSans(product_id, active_date);
                },
                error: function() {
                    alert("خطایی رخ داد. لطفا دوباره تلاش کنید.");
                    $("#btn-bulk-close-range, #btn-bulk-open-range").prop('disabled', false).css('opacity', '1');
                }
            });
        }

        // رویداد کلیک دکمه‌ها
        $('#btn-bulk-close-range').on('click', function() {
            handleBulkRangeAction('close');
        });

        $('#btn-bulk-open-range').on('click', function() {
            handleBulkRangeAction('open');
        });

        // --- رزرو تلفنی ---
        $('body').on('click', '.btn-phone-reserve', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openPhoneReserveModal($(this));
        });

        $('#closeModalPhoneReserve').on('click', function() {
            $('#modalPhoneReserve').addClass('hidden').hide();
        });

        $('#modalPhoneReserve').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden').hide();
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && !$('#modalPhoneReserve').hasClass('hidden')) {
                $('#modalPhoneReserve').addClass('hidden').hide();
            }
        });

        $('body').on('change input', '#phone-reserve-quantity', function() {
            fetchPhoneReserveQuote();
        });

        $('body').on('change', 'input[name="phone_reserve_payment_type"]', function() {
            if (phoneReserveLastQuote) {
                updatePhoneReserveSelectedAmount(phoneReserveLastQuote);
            } else {
                fetchPhoneReserveQuote();
            }
        });

        $('#phone-reserve-user-search-btn').on('click', function() {
            runPhoneReserveUserSearch();
        });

        $('#phone-reserve-user-search').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                runPhoneReserveUserSearch();
            }
        });

        $('#phone-reserve-user-search').on('input', function() {
            phoneReserveClearPlayerSelection();
            $('#phone-reserve-search-hint').addClass('hidden').text('');
            $('#phone-reserve-user-results').hide().empty();
        });

        $('#phone-reserve-new-user-toggle').on('click', function() {
            const $panel = $('#phone-reserve-new-user-fields');
            if ($panel.is(':visible') && !$panel.hasClass('phone-reserve-collapsed')) {
                phoneReserveCollapseNewUserPanel();
            } else {
                phoneReserveExpandNewUserPanel();
            }
        });

        $('body').on('click', '.phone-reserve-user-pick', function(e) {
            e.preventDefault();
            const uid = $(this).data('id');
            const label = $(this).text().trim();
            $('#phone-reserve-user-id').val(uid);
            $('#phone-reserve-user-selected').removeClass('hidden').text('پلیر انتخاب‌شده: ' + label);
            $('#phone-reserve-user-results').hide().empty();
            $('#phone-reserve-search-hint').addClass('hidden').text('');
            phoneReserveCollapseNewUserPanel();
        });

        $('#phone-reserve-submit').on('click', function() {
            const productId = $('#phone-reserve-product-id').val();
            const sansTs = $('#phone-reserve-sans-ts').val();
            let userId = $('#phone-reserve-user-id').val();
            const phoneSearch = $('#phone-reserve-user-search').val().trim();
            const firstName = $('#phone-reserve-first-name').val().trim();
            const lastName = $('#phone-reserve-last-name').val().trim();
            const quantity = parseInt($('#phone-reserve-quantity').val(), 10) || 1;
            const paymentType = $('input[name="phone_reserve_payment_type"]:checked').val();
            const note = $('#phone-reserve-note').val();
            const selectedLabel = $('#phone-reserve-selected-amount').text();
            const isNewUserPanelOpen = $('#phone-reserve-new-user-fields').is(':visible')
                && !$('#phone-reserve-new-user-fields').hasClass('phone-reserve-collapsed');
            const isNewUser = !userId && isNewUserPanelOpen;

            if (!userId) {
                if (!phoneSearch || phoneSearch.replace(/\D/g, '').length < 10) {
                    alert('شماره موبایل را وارد کنید، جستجو کنید و پلیر را انتخاب کنید — یا «کاربر جدید» را باز کنید.');
                    return;
                }
                if (isNewUserPanelOpen && (!firstName || !lastName)) {
                    alert('برای مشتری جدید، نام و نام خانوادگی الزامی است.');
                    return;
                }
                if (!isNewUserPanelOpen) {
                    alert('پلیر انتخاب نشده است. جستجو کنید و از لیست انتخاب کنید، یا فرم «کاربر جدید» را باز کنید.');
                    return;
                }
            }

            const confirmMsg = isNewUser
                ? 'مشتری جدید ساخته می‌شود و رزرو تلفنی با مبلغ «' + selectedLabel + '» ثبت شود؟'
                : 'رزرو تلفنی با مبلغ «' + selectedLabel + '» ثبت شود؟';
            if (!confirm(confirmMsg)) {
                return;
            }

            const $btn = $(this).prop('disabled', true);
            const postData = {
                action: 'team_ajax_handler',
                nonce: teamAjaxNonce,
                callback: 'sans_phone_reserve',
                operation: 'reserve',
                product_id: productId,
                sans_time: sansTs,
                user_id: userId || 0,
                quantity: quantity,
                payment_type: paymentType,
                note: note
            };
            if (!userId) {
                postData.phone = phoneSearch;
                postData.first_name = firstName;
                postData.last_name = lastName;
            }

            $.ajax({
                type: 'POST',
                url: teamAjaxUrl,
                data: postData,
                success: function(res) {
                    $btn.prop('disabled', false);
                    if (res && res.success) {
                        alert((res.data && res.data.message) ? res.data.message : 'رزرو ثبت شد.');
                        $('#modalPhoneReserve').addClass('hidden').hide();
                        const product_id = $("#current_product_id").val();
                        const active_date = $("[data-datepicker].active").data('datepicker');
                        if (product_id && active_date) BuildSans(product_id, active_date);
                    } else {
                        alert((res && res.data) ? res.data : 'خطا در ثبت رزرو');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    alert('خطا در ارتباط با سرور.');
                }
            });
        });

    });
</script>
