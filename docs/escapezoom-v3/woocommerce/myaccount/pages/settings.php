<?php
global $woocommerce, $wldb;
// Get current user
$user = wp_get_current_user();
$user_id = get_current_user_id();
$user_role = get_user_role($user_id);
/**
 * Get woocommerce countries and states
 */
$countries = new WC_Countries();
$states    = $countries->get_states($countries->get_base_country());
asort($states);
$get_city_by_state = require get_template_directory() . '/app/functions/helper/get-city-by-state.php';
$cities = [];
foreach ($states as $code => $state) {
    foreach ($get_city_by_state[$code] as $city) {
        $cities[] = $state . ' - ' . $city;
    }
}
$avatars = glob(get_template_directory() . '/assets/images/avatars/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
foreach ($avatars as $num => $avatar) {
    $avatars[$num] = str_replace(get_template_directory(), get_template_directory_uri(), $avatar);
}
$user_avatar = get_user_meta($user->ID, 'user_avatar', true) ? '/avatars/' . get_user_meta($user->ID, 'user_avatar', true) : 'default-avatar.svg';
$banks = require get_template_directory() . '/app/functions/helper/get-banks-list.php';
asort($banks);
$credit_card = get_user_meta($user->ID, 'withdrawal_owner_credit_card', true);
$shaba       = get_user_meta($user->ID, 'withdrawal_owner_shaba', true);
$shaba       = str_replace('IR', '', $shaba);
wp_dequeue_script('select2');
wp_dequeue_style('select2');

// Check if user has a password set
$has_password = !empty($user->user_pass);
?>
<div class="lg:col-span-8 2xl:col-span-9">
    <section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0">
        <h2 class="text-xl">تنظیمات حساب کاربری</h2>
        <div class="my-8 w-full border-t border-slate-100"></div>
        <form class="w-full" method="post" action="#" id="profile-form">
            <div class="grid gap-3 lg:gap-5.5 grid-cols-3">
                <div class="col-span-3 lg:col-span-1">
                    <div class="relative">
                        <input id="first_name" placeholder="نام" type="text" value="<?php echo esc_attr($user->first_name); ?>" name="first_name" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl">
                    </div>
                </div>
                <div class="col-span-3 lg:col-span-1">
                    <div class="relative">
                        <input id="last_name" placeholder="نام خانوادگی" type="text" value="<?php echo esc_attr($user->last_name); ?>" name="last_name" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl">
                    </div>
                </div>
                <div class="col-span-3 lg:col-span-1">
                    <div class="relative">
                        <input id="billing_phone" placeholder="شماره موبایل" type="text" dir="auto" disabled value="<?php echo esc_attr($user->billing_phone); ?>" name="phone" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl">
                    </div>
                </div>
                <div class="col-span-3 lg:col-span-1">
                    <div class="relative">
                        <input id="user_email" placeholder="ایمیل" type="email" dir="auto" value="<?php echo esc_attr($user->user_email); ?>" name="user_email" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl">
                    </div>
                </div>
                <div class="col-span-3 lg:col-span-1 relative">
                    <?php
                    $user_city = get_user_meta($user->ID, 'user_city', true);
                    $cities_list = get_all_cities();
                    $has_city = !empty($user_city);
                    ?>
                    <select name="user_city" id="user-city-select" class="select-box <?php echo !$has_city ? 'city-error' : ''; ?>">
                        <option value="">انتخاب شهر</option>
                        <?php
                        if (!empty($cities_list) && is_array($cities_list)) {
                            foreach ($cities_list as $city) {
                                if (isset($city['slug']) && isset($city['name'])) {
                        ?>
                                    <option value="<?php echo esc_attr($city['slug']); ?>"
                                        data-name="<?php echo esc_attr($city['name']); ?>"
                                        <?php selected($user_city, $city['slug']); ?>>
                                        <?php echo esc_html($city['name']); ?>
                                    </option>
                        <?php
                                }
                            }
                        }
                        ?>
                    </select>
                    <?php if (!$has_city) { ?>
                        <div class="city-error-message" style="position: absolute; top: 100%; left: 0; right: 0; background: #fee2e2; color: #dc2626; padding: 8px 12px; border-radius: 8px; font-size: 12px; margin-top: 4px; z-index: 10;">
                            کاربر گرامی لطفا شهر خود را انتخاب کنید.
                        </div>
                    <?php } ?>
                </div>
                <div class="col-span-3 lg:col-span-1">
                    <div class="relative h-16" style="z-index: 20;">
                        <div class="absolute z-30 bg-white text-gray-900 w-full shadow-13 outline-none ring-1 ring-inset ring-gray-100 rounded-2xl">
                            <button type="button" id="select-avatar-dropdown-button" class="flex items-center gap-5 w-full h-full h-16 py-3.5 px-6">
                                <img src="<?php bloginfo('template_url') ?>/assets/images/<?php echo $user_avatar; ?>" id="avatar-preview" width="36" height="36" class="rounded-xl mx-0" style="width: 36px; height: 36px">
                                <span class="truncate text-slate-200">انتخاب آواتار</span>
                            </button>
                            <div class="p-6 pt-0" style="display: none" id="avatars-dropdown">
                                <div class="grid grid-cols-5 gap-4 border-t pt-6">
                                    <button type="button" class="remove-avatar">
                                        <img src="<?php bloginfo('template_url') ?>/assets/images/recycle-bin.jpg" alt="Delete Avatar" class="rounded-xl">
                                    </button>
                                    <?php foreach ($avatars as $num => $avatar) { ?>
                                        <button type="button" class="select-avatar" data-avatar="<?php echo basename($avatar) ?>">
                                            <img src="<?php echo esc_attr($avatar) ?>" alt="Avatar <?php echo esc_attr($num) ?>" class="rounded-xl">
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <input type="hidden" name="avatar" id="avatar" value="<?php echo esc_attr(str_replace('/avatars/', '', $user_avatar)); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-span-3 flex items-center gap-8 text-gray-600 font-bold text-md after:border-b after:border-gray-200 after:grow">
                    بیو
                </div>
                <div class="col-span-3 text-text-3">
                    این متن در پروفایل کاربری شما به صورت عمومی نمایش داده میشود. ( مثال: سوابق تجربه، شهر محل سکونت،
                    علایق در بازی های گروهی و... )
                </div>
                <div class="col-span-3">
                    <?php
					$ez_bio_level = function_exists( 'ez_user_effective_feature_level' )
						? ez_user_effective_feature_level( (int) get_current_user_id() )
						: (int) get_user_level();
					if ( $ez_bio_level > 2 ) {
					?>
                        <textarea name="description" placeholder="متن بیو را اینجا بنویسید..." maxlength="500" id="" rows="7" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 py-2 px-6 rounded-2xl text-justify leading-7"><?php echo esc_html($user->description); ?></textarea>
                        <bdo dir="ltr">0 / 500</bdo>
                    <?php } else { ?>
                        <div class="flex gap-x-2.5 rounded-lg bg-warn-surface px-3 py-2 lg:mr-auto lg:items-center">
                            <svg class="shrink-0 max-lg:mt-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <mask id="mask0_7572_2461" maskUnits="userSpaceOnUse" x="0" y="0" width="14" height="14">
                                    <path d="M6.99999 13C7.78806 13.001 8.56856 12.8462 9.29664 12.5446C10.0247 12.243 10.686 11.8005 11.2426 11.2426C11.8005 10.686 12.243 10.0247 12.5446 9.29664C12.8462 8.56856 13.001 7.78806 13 6.99999C13.001 6.21192 12.8462 5.43142 12.5446 4.70334C12.243 3.97526 11.8005 3.31395 11.2426 2.7574C10.686 2.19945 10.0247 1.75697 9.29664 1.45538C8.56856 1.15379 7.78806 0.999033 6.99999 1C6.21192 0.999033 5.43142 1.15379 4.70334 1.45538C3.97526 1.75697 3.31395 2.19945 2.7574 2.7574C2.19945 3.31395 1.75697 3.97526 1.45538 4.70334C1.15379 5.43142 0.999033 6.21192 1 6.99999C0.999033 7.78806 1.15379 8.56856 1.45538 9.29664C1.75697 10.0247 2.19945 10.686 2.7574 11.2426C3.31395 11.8005 3.97526 12.243 4.70334 12.5446C5.43142 12.8462 6.21192 13.001 6.99999 13Z" fill="white" stroke="white" stroke-linejoin="round"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7 3.09961C7.19891 3.09961 7.38968 3.17863 7.53033 3.31928C7.67098 3.45993 7.75 3.6507 7.75 3.84961C7.75 4.04852 7.67098 4.23928 7.53033 4.37994C7.38968 4.52059 7.19891 4.59961 7 4.59961C6.80109 4.59961 6.61032 4.52059 6.46967 4.37994C6.32902 4.23928 6.25 4.04852 6.25 3.84961C6.25 3.6507 6.32902 3.45993 6.46967 3.31928C6.61032 3.17863 6.80109 3.09961 7 3.09961Z" fill="black"></path>
                                    <path d="M7.14961 9.9998V5.7998H6.54961M6.09961 9.9998H8.19961" stroke="black" stroke-linecap="round" stroke-linejoin="round"></path>
                                </mask>
                                <g mask="url(#mask0_7572_2461)">
                                    <path d="M-0.200195 -0.200195H14.1998V14.1998H-0.200195V-0.200195Z" fill="#BF9A00"></path>
                                </g>
                            </svg>
                            <p class="text-md font-bold text-yellow-900">
                                برای تغییر بیو نیاز است که به سطح 3 برسید.
                            </p>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-span-3 flex items-center gap-8 text-gray-600 font-bold text-md after:border-b after:border-gray-200 after:grow">
                    اطلاعات حساب بانکی
                </div>
                <div class="col-span-3 lg:col-span-1">
                    <select name="bank_name" class="select-box">
                        <option>نام بانک</option>
                        <?php foreach ($banks as $bank) { ?>
                            <option <?php selected(get_user_meta($user->ID, 'withdrawal_owner_bank_name', true), $bank) ?> value="<?php echo $bank; ?>"><?php echo str_replace('بانک ', '', $bank); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-span-3 lg:col-span-1">
                    <div class="relative">
                        <input id="credit_card" placeholder="شماره کارت" maxlength="16" dir="ltr" type="text" value="<?php echo $credit_card; ?>" name="credit_card" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl">
                    </div>
                </div>
                <div class="col-span-3 lg:col-span-1">
                    <div class="relative">
                        <input id="shaba" placeholder="شماره شبا" maxlength="24" dir="ltr" type="text" value="<?php echo $shaba; ?>"
                            name="shaba" <?php echo $user_role == 'compiler' ? 'disabled' : '' ?>
                            class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl">
                    </div>
                </div>
                <p class="text-md font-bold text-yellow-900 col-span-3 flex items-center ">
                    ⚠️
                    جهت دریافت تسویه حساب، کارت بانکی الزامیست به نام شما و اطلاعات ثبت شده در پنل یکسان باشد.
                </p>
                
                <!-- Password Section -->
                <div class="col-span-3 flex items-center gap-8 text-gray-600 font-bold text-md after:border-b after:border-gray-200 after:grow">
                    رمز عبور
                </div>
                <div class="col-span-3">
                    <button type="button" id="set-static-password" class="bg-primary-2 hover:bg-primary-deep text-white px-6 py-3 rounded-lg text-base font-yekan-bold mt-4">
                        تنظیم رمز ثابت
                    </button>
                    <p class="text-xs text-gray-500 mt-2">رمز عبور جدید باید 8 تا 20 کاراکتر باشد.</p>
                </div>
                <!-- End of Password Section -->
                
                
            </div>
            <button type="submit" class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-primaryColor text-white shadow-13 h-14 min-w-16 px-16 py-2 rounded-xl mr-auto mt-3 max-sm:w-full lg:mt-5.5">
                    <span class="truncate">ذخیره</span>
                </button>
        </form>
    </section>
</div>

<!-- Static Password Modal -->
<div id="staticPasswordModal" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30 hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-yekan-bold text-navyBlue">تنظیم رمز ثابت</h2>
            <button id="closeStaticPasswordModal" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="staticPasswordForm">
            <?php if ($has_password): ?>
                <div class="mb-4">
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">رمز قبلی</label>
                    <input type="password" id="old_password" name="old_password" class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2">
                </div>
            <?php endif; ?>
            <div class="mb-4">
                <label class="block text-sm font-yekan-bold text-navyBlue mb-2">رمز جدید</label>
                <input type="password" id="new_password" name="new_password" class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-yekan-bold text-navyBlue mb-2">تکرار رمز جدید</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2">
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 flex items-center justify-center gap-x-2 bg-primary-2 hover:bg-primary-deep text-white py-3 rounded-lg text-base font-yekan-bold">
                    ذخیره رمز
                </button>
                <button type="button" id="cancelStaticPassword" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 rounded-lg text-base font-yekan-bold">
                    انصراف
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .city-error .select2-container .select2-selection {
        border: 2px solid #dc2626 !important;
        border-radius: 16px !important;
    }
    .city-error-message {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3000,
        });
        
        $('#select-avatar-dropdown-button').on('click', function() {
            $(this).next().slideDown();
        });
        
        $(".select-avatar").on('click', function() {
            let avatar = $(this).data('avatar'),
                url = "<?php bloginfo('template_url') ?>/assets/images/avatars/";
            $("#avatar-preview").attr('src', url + avatar);
            $("#avatar").val(avatar);
        });
        
        $(".remove-avatar").on('click', function() {
            $("#avatar-preview").attr('src', "<?php bloginfo('template_url') ?>/assets/images/default-avatar.svg");
            $("#avatar").val('');
        });
        
        $("[name='description']").next().text(function() {
            let max = 500;
            let current = $("[name='description']").val().length;
            return `${current} / ${max}`;
        });
        
        $("[name='description']").on('input change', function() {
            let max = 500;
            let current = $(this).val().length;
            $(this).next().text(`${current}/${max}`);
        });

        $("#billing_state").on('change', function() {
            let value = $(this).val();
        });
        
        if (window.EzEnhancedSelect) {
            window.EzEnhancedSelect.init();
        }

        // Handle city selection change
        $("#user-city-select").on('change', function() {
            var selectedValue = $(this).val();
            if (selectedValue) {
                // Remove error styling
                $(this).removeClass('city-error');
                $('.city-error-message').fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
        
        $("#profile-form").on('submit', function(e) {
            e.preventDefault();
            let _ = $(this);
            let data = {
                'action': 'v2_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                'callback': 'panel_profile_save',
            };
            $.each(_.serializeArray(), function(i, field) {
                data[field.name] = field.value;
            });
            
            // Validate city selection
            if (!data.user_city || data.user_city === '') {
                $("#user-city-select").addClass('city-error');
                if (!$('.city-error-message').length) {
                    $("#user-city-select").closest('.relative').append(
                        '<div class="city-error-message" style="position: absolute; top: 100%; left: 0; right: 0; background: #fee2e2; color: #dc2626; padding: 8px 12px; border-radius: 8px; font-size: 12px; margin-top: 4px; z-index: 10;">کاربر گرامی لطفا شهر خود را انتخاب کنید.</div>'
                    );
                }
                Toast.fire({
                    icon: 'error',
                    title: 'لطفا شهر خود را انتخاب کنید.'
                });
                return;
            }
            
            var digitsExactly24 = /^\d{24}$/;
            if (typeof data?.shaba === 'string' && !digitsExactly24.test(data.shaba) && data.shaba.length > 0) {
                Toast.fire({
                    icon: 'error',
                    title: 'شماره شبا نامعتبر است. باید فقط شامل اعداد باشد و 24 رقم داشته باشد.'
                });
                return;
            }
            
            $.ajax({
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                type: 'POST',
                data: data,
                beforeSend: function() {
                    _.find('button[type="submit"]').attr('disabled', 'disabled').html('<div class="spinner" style="width: 33px;border-color: #FFF;border-width: 4px;"></div>');
                },
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: 'تغییرات با موفقیت ذخیره شد.'
                        });
                        setTimeout(() => window.location.reload(), 3000);
                    } else {
                        setTimeout(() => _.find('button[type="submit"]').removeAttr('disabled', 'disabled').html('ذخیره'), 3000);
                        Toast.fire({
                            icon: 'error',
                            title: response.data
                        });
                    }
                },
            });
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#avatars-dropdown, #select-avatar-dropdown-button').length) {
                $('#avatars-dropdown').slideUp();
            }
        });
        
        $("#first_name, #last_name").on('input', function(e) {
            let value = $(this).val();
            let regex = /[^ا-ی ]/g;
            $(this).val(value.replace(regex, ''));
        });
        
        // Static Password Modal
        $('#set-static-password').on('click', function() {
            $('#staticPasswordModal').removeClass('hidden');
        });
        
        $('#closeStaticPasswordModal, #cancelStaticPassword').on('click', function() {
            $('#staticPasswordModal').addClass('hidden');
        });
        
        $('#staticPasswordForm').on('submit', function(e) {
            e.preventDefault();
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            // Validate passwords
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();
            
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    title: 'خطا',
                    text: 'رمز جدید و تکرار آن باید یکسان باشند.',
                    icon: 'error'
                });
                return;
            }
            
            // Validate password length
            if (newPassword.length < 8 || newPassword.length > 20) {
                Swal.fire({
                    title: 'خطا',
                    text: 'رمز عبور باید بین 8 تا 20 کاراکتر باشد.',
                    icon: 'error'
                });
                return;
            }
            
            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>در حال ذخیره...');
            
            // Send AJAX request
            $.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                    'callback': 'user_edit_pass',
                    'old_password': $('#old_password').val(),
                    'new_password': newPassword,
                    'confirm_password': confirmPassword
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'موفق!',
                            text: response.data,
                            icon: 'success'
                        });
                        $('#staticPasswordModal').addClass('hidden');
                    } else {
                        Swal.fire({
                            title: 'خطا',
                            text: response.data,
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'خطا',
                        text: 'خطایی در ارتباط با سرور رخ داد.',
                        icon: 'error'
                    });
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text('ذخیره رمز');
                }
            });
        });
    });
</script>