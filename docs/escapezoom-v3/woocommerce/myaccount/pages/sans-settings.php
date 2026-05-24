<?php

$current_product_id = 5104;

$instant_off = get_post_meta($current_product_id, 'instant_off', true);
$auto_disable = get_post_meta($current_product_id, 'auto_disable', true);

?>

<input type="hidden" id="current_product_id" value="<?php echo $current_product_id ?>"/>

<!-- setting sans start-->
<div class="flex justify-between items-center mb-8">
    <p class="text-heavy-btn-t1 font-bold text-txTertiary"> تنظیمات سانس های <span class="text-bold-s1 font-black mr-1 text-txDefault">ایستگاه شهر یخ</span></p>
    <div class="flex items-center">
        <p class="text-text-bold-s3 font-bold text-txSecondary max-lg:hidden">بازگشت</p>
        <div class="bg-bgTertiary lg:bg-transparent p-2 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M4.79655 9.67326L9.21581 14.3919C9.54578 14.6851 9.98242 14.8449 10.4337 14.8375C10.8849 14.83 11.3155 14.656 11.6346 14.3521C11.9538 14.0482 12.1365 13.6381 12.1443 13.2084C12.1521 12.7786 11.9843 12.3628 11.6764 12.0486L8.48746 8.5016L11.6764 5.35944C11.9843 5.0452 12.1521 4.62937 12.1443 4.19963C12.1365 3.76989 11.9538 3.35983 11.6346 3.05591C11.3155 2.752 10.8849 2.57798 10.4337 2.57056C9.98242 2.56314 9.54578 2.72288 9.21581 3.01611L4.79655 7.32994C4.47052 7.64081 4.28739 8.06222 4.28739 8.5016C4.28739 8.94097 4.47052 9.36238 4.79655 9.67326Z" fill="#0F172B" />
            </svg>
        </div>
    </div>
</div>

<div class="border border-borderDefault rounded-2xl max-lg:p-5 p-6 shadow-1">
    <div class="flex max-lg:flex-col max-lg:items-start items-center gap-5 mb-5">
        <p class="text-heavy-btn-t1 font-bold">ثبت تخفیف آنی</p>
        <p class="text-text-bold-s3 font-bold text-txSecondary">برای ثبت تخفیف آنی زمان و درصد تخفیف را تعیین کنید:</p>
    </div>

    <div class="py-6 px-5 bg-bgErrorDoubleSubdued rounded-xl flex flex-col">
        <div class="flex max-lg:flex-col justify-between">
            <p class="text-bold-b2 font-bold">روزهای کاری (شنبه تا چهارشنبه، به جز تعطیلات رسمی)</p>

            <div class="flex max-lg:flex-col justify-between items-center lg:gap-5">
                <div class="flex justify-between gap-3 lg:gap-5 max-lg:my-5">
                    <div class="relative">
                        <div class="dropdown-toggle w-full max-lg:w-d158 lg:w-d162 flex justify-between items-center text-txSecondary bg-white px-5 py-3 rounded-lg shadow-1 cursor-pointer">
                            <span class="instant_off_hour" data-value="<?php echo $instant_off['normals']['hour']; ?>"><?php echo $instant_off['normals']['hour'] ?> ساعت مانده</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>

                        <div class="dropdown-menu absolute top-full mt-2 hidden flex-col bg-white p-3 rounded-2xl w-full max-lg:w-d158 lg:w-d162 shadow-1 z-10">
                            <div class="flex justify-between items-center">
                                <p class="text-text-bold-s3 font-bold">از ساعت</p>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                    <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="w-full h-d1 bg-bgSecondary my-4"></div>
                            <div class="dropdown-item px-5 py-2 text-bgPrimary bg-bgSecondary rounded-2xl cursor-pointer text-bold-b2 font-black" data-value="4">4 ساعت مانده</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="3">3 ساعت مانده</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="2">2 ساعت مانده</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="1">1 ساعت مانده</div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="dropdown-toggle w-full max-lg:w-d158 lg:w-d162 flex justify-between items-center text-txSecondary bg-white px-5 py-3 rounded-lg shadow-1 cursor-pointer">
                            <span class="instant_off_percentage" data-value="<?php echo $instant_off['normals']['percentage'] ?>"><?php echo $instant_off['normals']['percentage'] ?> درصد</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>

                        <div class="dropdown-menu absolute top-full mt-2 hidden flex-col bg-white p-3 rounded-2xl w-full max-lg:w-d158 lg:w-d162 shadow-1 z-10">
                            <div class="flex justify-between items-center">
                                <p class="text-text-bold-s3 font-bold">درصد تخفیف</p>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                    <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="w-full h-d1 bg-bgSecondary my-4"></div>
                            <div class="dropdown-item px-5 py-2 text-bgPrimary bg-bgSecondary rounded-2xl cursor-pointer text-bold-b2 font-black" data-value="15">15 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="20">20 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="25">25 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="40">40 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="50">50 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="60">60 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="70">70 درصد</div>
                        </div>
                    </div>
                </div>
                <button id="sans_settings_instant_off_btn" data-type="normals" class="bg-accent-450 w-full p-2 px-d50 rounded-lg text-white text-text-bold-s3 font-black" style="box-shadow: 0 2px 0 0 var(--Text-Success-Secondary, #02A159)">ثبت</button>
            </div>
        </div>

        <div class="w-full h-d1 bg-bgSecondary my-8"></div>

        <div class="flex max-lg:flex-col justify-between">
            <p class="text-bold-b2 font-bold">روزهای تعطیل (پنجشنبه، جمعه و تعطیلات رسمی)</p>

            <div class="flex max-lg:flex-col justify-between items-center lg:gap-5">
                <div class="flex justify-between gap-3 lg:gap-5 max-lg:my-5">
                    <div class="relative">
                        <div class="dropdown-toggle w-full max-lg:w-d158 lg:w-d162 flex justify-between items-center text-txSecondary bg-white px-5 py-3 rounded-lg shadow-1 cursor-pointer">
                            <span class="instant_off_hour" data-value="<?php echo $instant_off['holidays']['hour']; ?>"><?php echo $instant_off['holidays']['hour'] ?> ساعت مانده</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>

                        <div class="dropdown-menu absolute top-full mt-2 hidden flex-col bg-white p-3 rounded-2xl w-full max-lg:w-d158 lg:w-d162 shadow-1 z-10">
                            <div class="flex justify-between items-center">
                                <p class="text-text-bold-s3 font-bold">از ساعت</p>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                    <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="w-full h-d1 bg-bgSecondary my-4"></div>
                            <div class="dropdown-item px-5 py-2 text-bgPrimary bg-bgSecondary rounded-2xl cursor-pointer text-bold-b2 font-black" data-value="4">4 ساعت مانده</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="3">3 ساعت مانده</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="2">2 ساعت مانده</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="1">1 ساعت مانده</div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="dropdown-toggle w-full max-lg:w-d158 lg:w-d162 flex justify-between items-center text-txSecondary bg-white px-5 py-3 rounded-lg shadow-1 cursor-pointer">
                            <span class="instant_off_percentage" data-value="<?php echo $instant_off['holidays']['percentage'] ?>"><?php echo $instant_off['holidays']['percentage'] ?> درصد</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>

                        <div class="dropdown-menu absolute top-full mt-2 hidden flex-col bg-white p-3 rounded-2xl w-full max-lg:w-d158 lg:w-d162 shadow-1 z-10">
                            <div class="flex justify-between items-center">
                                <p class="text-text-bold-s3 font-bold">درصد تخفیف</p>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                                    <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="w-full h-d1 bg-bgSecondary my-4"></div>
                            <div class="dropdown-item px-5 py-2 text-bgPrimary bg-bgSecondary rounded-2xl cursor-pointer text-bold-b2 font-black" data-value="15">15 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="20">20 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="25">25 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="40">40 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="50">50 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="60">60 درصد</div>
                            <div class="dropdown-item px-5 py-2 text-txDefault cursor-pointer text-bold-b2 font-black" data-value="70">70 درصد</div>
                        </div>
                    </div>
                </div>
                <button id="sans_settings_instant_off_btn" data-type="holidays" class="bg-accent-450 w-full p-2 px-d50 rounded-lg text-white text-text-bold-s3 font-black" style="box-shadow: 0 2px 0 0 var(--Text-Success-Secondary, #02A159)">ثبت</button>
            </div>
        </div>
    </div>
</div>

<div class="border border-borderDefault rounded-2xl p-6 shadow-1 mt-7">
    <div class="flex max-lg:flex-col max-lg:items-start items-center gap-5 mb-5">
        <p class="text-heavy-btn-t1 font-bold">زمان غیرقابل رزو شدن سانس ها</p>
        <p class="text-text-bold-s3 font-bold text-txSecondary">زمان غیرقابل رزرو شدن، قبل از شروع سانس را انتخاب کنید:</p>
    </div>

    <div class="py-6 px-5 bg-bgTertiary rounded-xl flex flex-col">
        <div class="flex max-lg:flex-col justify-between">

            <div class="flex justify-between max-lg:justify-around flex-wrap items-center lg:gap-d60" id="auto-disable-options">
                <div class="flex items-center gap-2">
                    <input type="checkbox" data-value="15" class="auto-disable-option w-5 h-5 rounded-full appearance-none border border-gray-400 checked:bg-green-500 checked:border-green-500 checked:after:content-['✔'] checked:after:text-white checked:after:flex checked:after:items-center checked:after:justify-center checked:after:font-bold checked:after:text-xs" <?php echo (intval($auto_disable) === 15) ? 'checked' : '';?> />
                    <p class="text-text-bold-s3 font-bold text-txTertiary">15 دقیقه</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" data-value="30" class="auto-disable-option w-5 h-5 rounded-full appearance-none border border-gray-400 checked:bg-green-500 checked:border-green-500 checked:after:content-['✔'] checked:after:text-white checked:after:flex checked:after:items-center checked:after:justify-center checked:after:font-bold checked:after:text-xs" <?php echo (intval($auto_disable) === 30) ? 'checked' : '';?> />
                    <p class="text-text-bold-s3 font-bold text-txTertiary">30 دقیقه</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" data-value="60" class="auto-disable-option w-5 h-5 rounded-full appearance-none border border-gray-400 checked:bg-green-500 checked:border-green-500 checked:after:content-['✔'] checked:after:text-white checked:after:flex checked:after:items-center checked:after:justify-center checked:after:font-bold checked:after:text-xs" <?php echo (intval($auto_disable) === 60) ? 'checked' : '';?> />
                    <p class="text-text-bold-s3 font-bold text-txTertiary">60 دقیقه</p>
                </div>

                <div class="flex items-center gap-2 max-lg:mt-10">
                    <input type="checkbox" data-value="120" class="auto-disable-option w-5 h-5 rounded-full appearance-none border border-gray-400 checked:bg-green-500 checked:border-green-500 checked:after:content-['✔'] checked:after:text-white checked:after:flex checked:after:items-center checked:after:justify-center checked:after:font-bold checked:after:text-xs" <?php echo (intval($auto_disable) === 120) ? 'checked' : '';?> />
                    <p class="text-text-bold-s3 font-bold text-txTertiary">120 دقیقه</p>
                </div>

                <div class="flex items-center gap-2 max-lg:mt-10">
                    <input type="checkbox" data-value="180" class="auto-disable-option w-5 h-5 rounded-full appearance-none border border-gray-400 checked:bg-green-500 checked:border-green-500 checked:after:content-['✔'] checked:after:text-white checked:after:flex checked:after:items-center checked:after:justify-center checked:after:font-bold checked:after:text-xs" <?php echo (intval($auto_disable) === 180) ? 'checked' : '';?> />
                    <p class="text-text-bold-s3 font-bold text-txTertiary">180 دقیقه</p>
                </div>
            </div>

            <button id="auto_disable_submit" class="bg-accent-450 py-2 px-d80 rounded-lg text-white text-text-bold-s3 font-black max-lg:mt-7" style="box-shadow: 0 2px 0 0 var(--Text-Success-Secondary, #02A159)">ثبت</button>

        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $(".expand-menu").on("click", function(e) {
            e.preventDefault();

            let $btn = $(this);
            let $submenu = $btn.closest("div").find(".submenu");
            let $icon = $btn.find("svg").last(); // فلش سمت راست

            // نمایش/مخفی کردن منو
            $submenu.slideToggle(200);

            // تغییر وضعیت aria
            let expanded = $btn.attr("aria-expanded") === "true";
            $btn.attr("aria-expanded", !expanded);

        });

        $("body").on('click', '#sans_settings_instant_off_btn', function(e) {

            let hour        = $(this).siblings('div').find('.instant_off_hour').data('value');
            let percentage  = $(this).siblings('div').find('.instant_off_percentage').data('value');

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                    'callback': 'panel_sans_settings_update',
                    'product_id': $("#current_product_id").val(),
                    'type': $(this).data('type'),
                    'hour': hour,
                    'percentage': percentage,
                },
                success: function(data) {
                    console.log(data);

                }
            });
        });
    });

    jQuery(document).ready(function($) {

        // باز و بسته کردن منو
        $(".dropdown-toggle").on("click", function(e) {
            e.stopPropagation(); // جلوگیری از بسته شدن سریع
            let menu = $(this).siblings(".dropdown-menu");
            $(".dropdown-menu").not(menu).hide(); // بستن منوهای دیگر
            menu.toggle();
        });

        // انتخاب آیتم
        $(".dropdown-item").on("click", function() {
            let value = $(this).text();
            let parent = $(this).closest(".relative");
            let span = parent.find('.dropdown-toggle span');

            span.text(value);
            span.attr('data-value', $(this).data('value'));

            parent.find(".dropdown-menu").hide();
        });

        // بستن منو با کلیک بیرون
        jQuery(document).on("click", function($) {
            $(".dropdown-menu").hide();
        });

        // enforce single selection for auto-disable options
        $("body").on('change', '#auto-disable-options .auto-disable-option', function() {
            if ($(this).is(':checked')) {
                $('#auto-disable-options .auto-disable-option').not(this).prop('checked', false);
            }
        });

        // submit auto-disable
        $("body").on('click', '#auto_disable_submit', function(e) {
            e.preventDefault();

            let selected = $('#auto-disable-options .auto-disable-option:checked').first();
            if (!selected.length) {
                alert('یک گزینه را انتخاب کنید.');
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                    'callback': 'panel_auto_disable_update',
                    'product_id': $("#current_product_id").val(),
                    'auto_disable': selected.data('value')
                },
                success: function(resp) {
                    console.log(resp);
                }
            });
        });
    });
</script>
<!-- setting sans end-->
