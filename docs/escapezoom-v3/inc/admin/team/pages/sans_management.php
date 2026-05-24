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

</style>
<input type="hidden" id="current_product_id">

<div class="overflow-hidden">

    <div class="flex justify-between items-center">

        <h1 class="text-base font-extrabold lg:text-2xl">مدیریت سانس</h1>

        <div class="relative w-d582 h-d58" style="z-index: 99;">
            <input id="gameSearch" class="w-d582 h-d58 border border-slate-105 bg-white rounded-xl outline-none px-6 py-5 text-xs font-yekan-bold text-navyBlue" placeholder="جست و جو بازی" />
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" fill="none" class="absolute w-4 h-4 top-5 left-6">
                <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
            </svg>
            <div id="lg-search-result-list" class="max-h-75 divide-y divide-slate-105 overflow-y-auto px-4 py-5" style="background: #f3f3f3;display: none"></div>
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
                    <span class="lg:text-22 lg:font-extrabold">
                        امروز
                    </span>
                </button>
            </div>

            <div class="swiper date-picker max-w-d1200">
                <div class="swiper-wrapper py-2">
                    <?php foreach ($dates as $index => $date) { ?>
                        <div class="swiper-slide" dir="ltr">
                            <button type="button" data-datepicker="<?php echo esc_attr($date); ?>"
                                class="flex h-16 w-16 shrink-0 flex-col items-center justify-center gap-y-2 rounded-xl border border-rail bg-white leading-none lg:h-21">
                                <span class="lg:order-1 lg:text-34 lg:font-extrabold">
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

    <div class="after_load px-d121 mt-10" style="display: none">
        <div class="flex justify-between items-center w-full gap-x-10">
            <h3 id="current_product_title" class="text-xl font-bold text-orangee"></h3>
            <!-- بخش انتخاب بازه زمانی و دکمه‌های عملیات گروهی -->
            <div class="flex flex-col md:flex-row items-center gap-4 bg-white grow">
                <!-- تریگر تقویم بازه زمانی (کپی شده از فایل گزارش) -->
                <div class="date-range-container relative w-full">
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
                <div class="grid grid-cols-2 gap-2 w-full">
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
        <hr class="w-full border-slate-105 border-4 rounded-lg mt-5">
    </div>

    <div id="sessionsContainer" class="after_load mt-8 grid grid-cols-2 gap-x-4.5 gap-y-8 lg:grid-cols-4 lg:gap-x-12.5 px-d121" style="display: none"></div>

    <div id="playing_now" class="after_load mt-12.5" style="display: none"></div>

    <div class="initial font-extrabold text-center text-grayy mt-54">برای یافتن بازی مورد نظر، از بخش <br> جستجوی بالا استفاده کنید...</div>

</div>
<!-- -----------modal------------------------------------- -->
<div id="modalOverlayInfo" class="fixed inset-0 z-50 hidden backdrop-blur-sm bg-white/30">
    <div class="flex flex-col p-d30 rounded-xl bg-white w-d355 h-d194 border border-rail shadow-rail-lip absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex justify-between">
            <div class="flex flex-col">
                <div class="flex gap-x-4">
                    <p class="text-lg font-bold text-navyBlue" name="name"></p>
                    <div class="rounded-3xl text-xs font-bold py-1 px-2.5" name="level_title" style=""></div>
                </div>
                <a href="tel:" class="text-base font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer" name="phone"></a>
            </div>
            <a href="tel:" class="text-base font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer" name="phone-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 46 46" fill="none">
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
        <hr class="text-slate-105 my-d20" />
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

        <div class="flex justify-start gap-d13">
            <p class="text-sm font-bold text-grayy">تاریخ رزرو</p>
            <div class="flex justify-center gap-2 text-base font-bold text-navyBlue">
                <p class="text-base font-bold text-navyBlue" name="date"></p>
            </div>
        </div>

    </div>
</div>
<?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/calendar-module.js"></script>

<script>
    jQuery(document).ready(function($) {

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
            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": "sans_management_web",
                    "data": { "day_start_time": day, "product_id": room }
                },
                beforeSend: function() {
                    $(`[data-datepicker="${day}"]`).attr('disabled', 'disabled');
                    let out = "";
                    for (let i = 0; i < 8; i++) {
                        out += "<div class='w-full h-29 skeleton rounded-xl'></div>";
                    }
                    $("#sessionsContainer").html(out);
                },
                success: function(response) {
                    $(`[data-datepicker="${day}"]`).removeAttr('disabled');
                    // چاپ پاسخ (که شامل کارت‌های سانس و قالب مخفی دکمه رادیویی است) در کانتینر سانس‌ها
                    $("#sessionsContainer").html(response);
                    
                    // بلافاصله دکمه‌های رادیویی را استخراج و به جای درست منتقل می‌کنیم
                    extractAndMoveRadioButtons(); 
                    
                    datePickerSwiper.update();
                }
            });
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

            $("[data-datepicker]").removeClass('active border-primary-700 bg-primary-500 text-white').addClass('border-rail bg-white');
            $(this).removeClass('border-rail bg-white').addClass('active border-primary-700 bg-primary-500 text-white');

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

            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": `${action}_sans`,
                    "data": { "sans_time": parseInt(time), "product_id": parseInt(product) }
                },
                beforeSend: function() {
                    $this.attr('disabled', 'disabled');
                    $this.html("<div class='spinner' style='margin: 11px auto 0;width: 16px;border: 2px solid rgba(127 127 127 / 50%);display: inline-flex;'></div>");
                },
                success: function() {
                    BuildSans(product, currentDate);
                }
            });
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

            // ارسال درخواست به سرور
            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": actionType,
                    "data": { "day_start_time": day_start_time, "product_id": product_id }
                },
                beforeSend: function() {
                    // قفل کردن تقویم در زمان لود و نمایش اسکلتون
                    $(`[data-datepicker="${day_start_time}"]`).attr('disabled', 'disabled');
                    let out = "<div class='w-full h-29 skeleton rounded-xl'></div>".repeat(8);
                    $("#sessionsContainer").html(out);
                },
                success: function(response) {
                    // ساخت مجدد سانس‌ها پس از موفقیت (وضعیت و رنگ دکمه‌های رادیویی هم اتوماتیک آپدیت می‌شود)
                    BuildSans(product_id, day_start_time);
                    $(`[data-datepicker="${day_start_time}"]`).removeAttr('disabled');
                },
                error: function(xhr) {
                    alert("خطایی در تغییر وضعیت گروهی رخ داد.");
                    $('input[name="bulk_action"]').prop('disabled', false);
                    BuildSans(product_id, day_start_time);
                    $(`[data-datepicker="${day_start_time}"]`).removeAttr('disabled');
                }
            });
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

    });
</script>
