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
</style>
<input type="hidden" id="current_product_id">

<div class="overflow-hidden">

    <div class="flex justify-between items-center">

        <h1 class="text-base font-extrabold lg:text-2xl">مدیریت سانس</h1>

        <div class="relative w-[582px] h-[58px]" style="z-index: 99;">
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
        <div class="flex justify-between items-center w-full">
            <h3 id="current_product_title" class="text-xl font-bold text-orangee"></h3>
            <div class="flex items-center justify-center gap-4">
                <p class="text-sm font-medium text-[#90A1B9]">بستن همه سانس ها</p>
                <div id="toggleSwitch" class="w-16 h-9 rounded-full relative cursor-pointer transition-all duration-300 bg-on">
                    <div id="knob" class="absolute top-1 left-1 w-7 h-7 bg-white rounded-full shadow-md transition-all duration-300 knob-on"></div>
                </div>
            </div>
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

<script>
    jQuery(document).ready(function($) {

        let day_start_time = "<?php echo $current_date; ?>";

        const datePickerSwiper = new Swiper('.date-picker', {
            slidesPerView: 4.5,
            freeMode: true,
            breakpoints: {
                540: {
                    slidesPerView: 5.5,
                },
                650: {
                    slidesPerView: 6.5,
                },
                1280: {
                    slidesPerView: 9.6,
                },
            },
        });

        const BuildSans = (room, day) => {
            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": "sans_management_web",
                    "data": {
                        "day_start_time": day,
                        "product_id": room
                    }
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
                    $("#sessionsContainer").html(response);
                    // Update Swiper to recalculate dimensions after content change
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

            $("[data-datepicker]").removeClass('active border-primary-700 bg-primary-500 text-white').addClass('border-[#DBE2EA] bg-white');
            $(this).removeClass('border-[#DBE2EA] bg-white').addClass('active border-primary-700 bg-primary-500 text-white');

            BuildSans(product_id, date);

            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": `check_playing`,
                    "data": {
                        "day_start_time": date,
                        "product_id": product_id
                    }
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
                    "data": {
                        "sans_time": parseInt(time),
                        "product_id": parseInt(product)
                    }
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

        $("body").on('click', "#toggleSwitch", function() {

            let product_id = $("#current_product_id").val(),
                day_start_time = "<?php echo $current_date; ?>";

            if ($('#knob').hasClass('knob-on')) { // باز کردن هم سانس ها


            } else { // بستن همه سانس ها

                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                    data: {
                        "type": "close_all_sanses",
                        "data": {
                            "day_start_time": day_start_time,
                            "product_id": product_id
                        }
                    },
                    beforeSend: function() {
                        $(`[data-datepicker="${day_start_time}"]`).attr('disabled', 'disabled');
                        let out = "";
                        for (let i = 0; i < 8; i++) {
                            out += "<div class='w-full h-29 skeleton rounded-xl'></div>";
                        }

                        $("#sessionsContainer").html(out);
                    },
                    success: function(response) {
                        $(`[data-datepicker="${day_start_time}"]`).removeAttr('disabled');
                        $("#sessionsContainer").html(response);
                        // Update Swiper to recalculate dimensions after content change
                        datePickerSwiper.update();
                    }
                });

            }


            // BuildSans(product_id, date);
        });

        $('body').on('input', "#gameSearch", function() {

            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": `game_search`,
                    "data": {
                        "term": $(this).val(),
                    }
                },
                success: function(data) {
                    $('#lg-search-result-list').show().html(data);
                }
            });
        });

        $('body').on('click', ".openModalInfo", function() {

            var raw = $(this).attr('data-user-info');

            if (raw.indexOf('&quot;') !== -1 || raw.indexOf('&amp;') !== -1) {
                var txt = document.createElement('textarea');
                txt.innerHTML = raw;
                raw = txt.value;
            }

            try {
                var info = JSON.parse(raw);
                console.log(info);

                // Populate modal with user information using name attributes
                $('#modalOverlayInfo').find('[name="name"]').text(info.name); // نام کاربر

                // Format phone number for calling
                let formattedPhone = info.phone;
                if (info.phone) {
                    if (info.phone.startsWith('0')) {
                        // Remove leading 0 and add +98
                        formattedPhone = '+98' + info.phone.substring(1);
                    } else if (info.phone.startsWith('9')) {
                        // Add +98 prefix
                        formattedPhone = '+98' + info.phone;
                    }
                }

                $('#modalOverlayInfo').find('[name="phone"]').text(info.phone).attr('href', 'tel:' + formattedPhone); // شماره تلفن
                $('#modalOverlayInfo').find('[name="phone-icon"]').attr('href', 'tel:' + formattedPhone); // آیکون تلفن
                $('#modalOverlayInfo').find('[name="order_id"]').text(info.order_id); // کد رزرو
                $('#modalOverlayInfo').find('[name="quantity"]').text(info.quantity); // تعداد
                $('#modalOverlayInfo').find('[name="date"]').text(info.date || ''); // تاریخ رزرو

                // Set level title and color
                if (info.level_title) {
                    $('#modalOverlayInfo').find('[name="level_title"]').text(info.level_title);
                }

                // Set level color with 20% opacity for background
                if (info.level_color) {
                    // Clean the color string - remove brackets and quotes
                    let cleanColor = info.level_color.replace(/[\[\]"]/g, '');

                    // Ensure it starts with #
                    if (!cleanColor.startsWith('#')) {
                        cleanColor = '#' + cleanColor;
                    }

                    // Convert hex to rgba with 20% opacity
                    let hexColor = cleanColor;
                    if (hexColor.startsWith('#')) {
                        hexColor = hexColor.substring(1);
                    }
                    const r = parseInt(hexColor.substr(0, 2), 16);
                    const g = parseInt(hexColor.substr(2, 2), 16);
                    const b = parseInt(hexColor.substr(4, 2), 16);
                    const rgbaColor = `rgba(${r}, ${g}, ${b}, 0.2)`;

                    $('#modalOverlayInfo').find('[name="level_title"]').css({
                        'background-color': rgbaColor,
                        'color': cleanColor
                    });
                }

                // Show the modal
                $('#modalOverlayInfo').removeClass('hidden').show();

            } catch (e) {
                console.error('Invalid JSON in data-user-info:', e, raw);
            }

        });

        // Close modal functionality
        $('body').on('click', "#closeModalInfo", function() {
            $('#modalOverlayInfo').addClass('hidden').hide();
        });

        // Close modal when clicking outside
        $('body').on('click', "#modalOverlayInfo", function(e) {
            if (e.target === this) {
                $('#modalOverlayInfo').addClass('hidden').hide();
            }
        });

        // Close modal with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && !$('#modalOverlayInfo').hasClass('hidden')) {
                $('#modalOverlayInfo').addClass('hidden').hide();
            }
        });

    });
</script>