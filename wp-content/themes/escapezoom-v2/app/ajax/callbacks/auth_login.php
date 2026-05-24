<?php
$mobile = sanitize_text_field($_POST['phone']);
if (empty($mobile)) {
    wp_send_json_error('شماره موبایل ضروری میباشد');
}
if (!ctype_digit($mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}
if (!preg_match('/^(\+98|0|0098)?9\d{9}$/', $mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}
if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
    $mobile = substr($mobile, 1);
}

if ($mobile === '9031642504') {
    wp_send_json_error('شما اجازه استفاده از این سایت را ندارید!');
}

$user = get_user_by('login', $mobile);

if ($user) {
    // --- کاربر قدیمی ---
    $currentType = 'login';
    // --- بررسی اطلاعات کاربر برای نمایش فرم هوشمند ---
    $firstname = get_user_meta($user->ID, 'first_name', true);
    $lastname = get_user_meta($user->ID, 'last_name', true);
    $user_city = get_user_meta($user->ID, 'user_city', true);

    // تعیین وضعیت نمایش (اگر خالی بود true می‌شود تا نمایش داده شود)
    $show_firstname = empty($firstname);
    $show_lastname = empty($lastname);
    $show_city = empty($user_city);
    // ----------------------------------------------------
    ob_start();
    ?>
    <!-- فرم ورود با رمز عبور -->
    <form action="#" method="post" id="password-form" class="w-full flex flex-col hidden">
        <strong class="text-[22px] text-textColor font-bold block mt-9 text-center">ورود به حساب</strong>
        <p class="text-center text-sm text-slate-500 mt-2">
            شماره موبایل: <span class="font-bold text-textColor"><?php echo esc_html($mobile); ?></span>
        </p>
        <p class="text-center text-xs text-slate-400 mt-1">
            رمز ثابت خود را وارد کنید
        </p>
        <div class="relative mt-4">
            <input type="password" id="user-password" name="user-password" class="border p-2 text-2xl rounded-xl w-full bg-white outline-primaryColor text-center pl-10" placeholder="رمز عبور" tabindex="1">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-slate-400 toggle-password" data-target="user-password">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </span>
        </div>
        <div class="error mt-2 text-red-500 text-sm"></div>
        <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl">
            ورود
        </button>
        <div class="flex flex-col gap-2 mt-4">
            <button type="button" class="btn-back-to-login flex items-center gap-2 text-slate-150 font-light-yekanbakh text-sm transition hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 4v6h6"></path>
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                </svg>
                تغییر شماره موبایل
            </button>
            <button type="button" class="btn-forgot-password flex items-center justify-center gap-2 text-primaryColor font-bold-yekanbakh text-sm border border-primaryColor rounded-lg p-2 transition hover:bg-primaryColor hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                </svg>
                فراموشی رمز عبور
            </button>
        </div>
    </form>

    <!-- فرم تایید کد OTP (برای فراموشی رمز) -->
    <form action="#" method="post" id="otp-form" class="w-full flex flex-col hidden">
        <input type="hidden" name="otp-type" id="otp-type-hidden" value="<?php echo esc_attr($currentType); ?>">
        <strong class="text-[22px] text-textColor font-bold block mt-9 text-center" id="otp-title">تایید کد</strong>
        <p class="text-slate-150 font-light-yekanbakh text-lg mt-4 text-center">
            کد ارسال شده به شماره <span id="otp-mobile-display" class="font-bold"></span> را وارد کنید.
        </p>
        <input type="hidden" name="otp-type" id="otp-type-hidden" value="reset">
        <div class="mt-3 flex gap-4 mx-10" data-number-code-form dir="ltr">
            <input id="otp-number-code-0" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-0' data-number-code-input='0' required />
            <input id="otp-number-code-1" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-1' data-number-code-input='1' required />
            <input id="otp-number-code-2" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-2' data-number-code-input='2' required />
            <input id="otp-number-code-3" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-3' data-number-code-input='3' required />
        </div>
        <div class="error mt-2 text-red-500 text-sm"></div>
        <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl">
            تایید
        </button>
        <div class="flex flex-col text-center text-textColor mt-4 text-lg counter">
            زمان باقیمانده برای درخواست مجدد
            <span class="text-primaryColor mt-4" id="timer">120 ثانیه</span>
        </div>
        <button type="button" id="resend-otp-failed-btn" class="text-red-500 my-2 hidden cursor-pointer text-sm font-bold border border-red-200 p-2 rounded hover:bg-red-50">
            کد تایید براتون ارسال نشد؟
        </button>
        <button type="button" id="resend-otp-btn" class="text-primaryColor my-2 hidden cursor-pointer">
            ارسال مجدد کد
        </button>
        <button type="button" class="btn-back-to-password flex items-center gap-2 text-slate-150 font-light-yekanbakh text-sm transition hover:text-slate-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 4v6h6"></path>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
            بازگشت به ورود با رمز عبور
        </button>
    </form>

    <!-- فرم تغییر رمز عبور جدید -->
    <form action="#" method="post" id="new-password-form" class="w-full flex flex-col hidden">
        <strong class="text-[22px] text-textColor font-bold block mt-9">تغییر رمز عبور</strong>
        <p class="text-slate-150 font-light-yekanbakh text-lg mt-4">
            لطفا رمز عبور جدید خود را وارد کنید.
        </p>
        <!-- اصلاح شده: حذف دکمه بیرونی و span اضافی -->
        <div class="relative mt-3">
            <input type="password" name="new_password" id="new-password-input" class="border p-2 text-2xl rounded-xl w-full bg-white outline-primaryColor pl-10" placeholder="رمز عبور جدید">
            
            <!-- دکمه چشم اصلاح شده -->
            <button type="button" class="toggle-password absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-primaryColor transition" data-target="new-password-input">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>

        <p class="text-xs text-slate-400 mt-2 text-justify">
            رمز عبور باید حداقل ۱۰ کاراکتر و شامل حروف بزرگ، کوچک و عدد باشد. (استفاده از علائم اختیاری است)
        </p>

        <div class="relative mt-3">
            <input type="password" name="confirm_new_password" id="confirm-new-password-input" class="border p-2 text-2xl rounded-xl w-full bg-white outline-primaryColor pl-10" placeholder="تکرار رمز عبور جدید">
            
            <!-- دکمه چشم اصلاح شده -->
            <button type="button" class="toggle-password absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-primaryColor transition" data-target="confirm-new-password-input">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>
        <div class="error mt-2 text-red-500 text-sm"></div>
        <button type="submit" class="bg-primaryColor text-white text-2xl mt-6 p-3 rounded-xl transition hover:bg-opacity-90">
            ثبت رمز عبور
        </button>
    </form>

    <!-- فرم تکمیل پروفایل (بدون رمز عبور) -->
    <form action="#" method="post" id="profile-form" class="w-full flex flex-col hidden">
        <strong class="text-[22px] text-textColor font-bold block mt-9">اطلاعات شخصی</strong>
        <p class="text-slate-150 font-light-yekanbakh text-sm mt-2" id="profile-desc">لطفا اطلاعات خود را تکمیل کنید</p>
        <div class="space-y-2">
        <?php if ($show_firstname): ?>
            <div class="grid grid-cols-3 relative">
                <label for="first-name" class="text-slate-150 font-light-yekanbakh mt-4">نام</label>
                <input type="text" id="first-name" name="first-name" class="border col-span-2 p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1" autofocus>
            </div>
        <?php endif; ?>
        
        <?php if ($show_lastname): ?>
            <div class="grid grid-cols-3 relative">
                <label for="last-name" class="text-slate-150 font-light-yekanbakh mt-4">نام خانوادگی</label>
                <input type="text" id="last-name" name="last-name" class="border col-span-2 p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1" autofocus>
            </div>
        <?php endif; ?>
           </div>
        
        <?php if ($show_city): ?>
            <label for="user-city" class="text-slate-150 font-light-yekanbakh mt-4">
                شهر خود را انتخاب کنید
            </label>
            <select id="user-city" name="user-city" class="border p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1">
                <option value="">انتخاب شهر</option>
                <?php
                $cities = get_all_cities();
                if (!empty($cities) && is_array($cities)) {
                    foreach ($cities as $city) {
                        if (isset($city['slug']) && isset($city['name'])) {
                            echo '<option value="' . esc_attr($city['slug']) . '">' . esc_html($city['name']) . '</option>';
                        }
                    }
                }
                ?>
            </select>
        <?php endif; ?>

        <div class="error mt-2 text-red-500 text-sm"></div>
        <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl" id="btn-profile-submit">
            ذخیره و ادامه
        </button>
        <button type="button" class="btn-back-to-login flex items-center gap-2 text-slate-150 font-light-yekanbakh text-sm transition hover:text-slate-600 mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 4v6h6"></path>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
            تغییر شماره موبایل
        </button>
    </form>
    <?php
    $forms_html = ob_get_clean();
    wp_send_json_success(['status' => 'old_user', 'html' => $forms_html]);

} else {
    // --- کاربر جدید ---
    $code = wp_rand(1000, 9999);
    set_transient('otp_' . $mobile, $code, 5 * MINUTE_IN_SECONDS);
    set_transient('otp_type_' . $mobile, 'register', 5 * MINUTE_IN_SECONDS);
    try {
      // ez_sendpayamak3( $mobile, 'کد تایید شما: ' . $code, '90006491' );
      ez_otp_new($mobile, $code);
    } catch ( Exception $e ) {
       // Silent fail or log
    }
    
    ob_start();
    ?>
    <!-- فرم تایید کد OTP (برای ثبت نام) -->
    <form action="#" method="post" id="otp-form" class="w-full flex flex-col hidden">
        <strong class="text-[22px] text-textColor font-bold block mt-9 text-center" id="otp-title">تایید کد</strong>
        <p class="text-slate-150 font-light-yekanbakh text-lg mt-4 text-center">
            کد ارسال شده به شماره <span id="otp-mobile-display" class="font-bold"><?php echo esc_html($mobile); ?></span> را وارد کنید.
        </p>
        <input type="hidden" name="otp-type" id="otp-type-hidden" value="register">
        <div class="mt-3 flex gap-4 mx-10" data-number-code-form dir="ltr">
            <input id="otp-number-code-0" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-0' data-number-code-input='0' required />
            <input id="otp-number-code-1" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-1' data-number-code-input='1' required />
            <input id="otp-number-code-2" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-2' data-number-code-input='2' required />
            <input id="otp-number-code-3" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-3' data-number-code-input='3' required />
        </div>
        <div class="error mt-2 text-red-500 text-sm"></div>
        <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl">
            تایید
        </button>
        <div class="flex flex-col text-center text-textColor mt-4 text-lg counter">
            زمان باقیمانده برای درخواست مجدد
            <span class="text-primaryColor mt-4" id="timer">120 ثانیه</span>
        </div>
        <button type="button" id="resend-otp-failed-btn" class="text-red-500 my-2 hidden cursor-pointer text-sm font-bold border border-red-200 p-2 rounded hover:bg-red-50">
            کد تایید براتون ارسال نشد؟
        </button>
        <button type="button" id="resend-otp-btn" class="text-primaryColor my-2 hidden cursor-pointer">
            ارسال مجدد کد
        </button>
        <button type="button" class="btn-back-to-login flex items-center gap-2 text-slate-150 font-light-yekanbakh text-sm transition hover:text-slate-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 4v6h6"></path>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
            تغییر شماره موبایل
        </button>
    </form>

    <!-- فرم تکمیل پروفایل (با رمز عبور) -->
    <form action="#" method="post" id="profile-form" class="w-full flex flex-col hidden">
        <strong class="text-[22px] text-textColor font-bold block mt-9">اطلاعات شخصی</strong>
        <p class="text-slate-150 font-light-yekanbakh text-sm mt-2" id="profile-desc">لطفا اطلاعات خود را تکمیل کنید</p>
        
        <div class="space-y-2">
            <div class="grid grid-cols-3 relative">
                <label for="first-name" class="text-slate-150 font-light-yekanbakh mt-4">نام</label>
                <input type="text" id="first-name" name="first-name" class="border col-span-2 p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1" autofocus>
            </div>
            <div class="grid grid-cols-3 relative">
                <label for="last-name" class="text-slate-150 font-light-yekanbakh mt-4">نام خانوادگی</label>
                <input type="text" id="last-name" name="last-name" class="border col-span-2 p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1" autofocus>
            </div>
        </div>
        
        <label for="user-city" class="text-slate-150 font-light-yekanbakh text-4">
            شهر خود را انتخاب کنید
        </label>
        <select id="user-city" name="user-city" class="border p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1">
            <option value="">انتخاب شهر</option>
            <?php
            $cities = get_all_cities();
            if (!empty($cities) && is_array($cities)) {
                foreach ($cities as $city) {
                    if (isset($city['slug']) && isset($city['name'])) {
                        echo '<option value="' . esc_attr($city['slug']) . '">' . esc_html($city['name']) . '</option>';
                    }
                }
            }
            ?>
        </select>

        <div id="new-user-password-fields">
            <label for="reg-password" class="text-slate-150 font-light-yekanbakh mt-4 block">
                    رمز عبور
                    </label>
                    <div class="relative mt-2">
                        <input type="password" id="reg-password" name="reg-password" class="border p-2 text-2xl rounded-xl w-full bg-white outline-primaryColor pl-10" placeholder="رمز عبور">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-slate-400 toggle-password" data-target="reg-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                    <label for="reg-confirm-password" class="text-slate-150 font-light-yekanbakh mt-4 block">
                        تکرار رمز عبور
                    </label>
                    <div class="relative mt-2">
                        <input type="password" id="reg-confirm-password" name="reg-confirm-password" class="border p-2 text-2xl rounded-xl w-full bg-white outline-primaryColor pl-10" placeholder="تکرار رمز عبور">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-slate-400 toggle-password" data-target="reg-confirm-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
            <p class="text-xs text-slate-400 mt-2 text-justify">
                رمز عبور باید حداقل ۱۰ کاراکتر و شامل حروف بزرگ، کوچک و عدد باشد. (استفاده از علائم اختیاری است)
            </p>
        </div>

        <div class="error mt-2 text-red-500 text-sm"></div>
        <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl" id="btn-profile-submit">
            ذخیره و ادامه
        </button>
        <button type="button" class="btn-back-to-login flex items-center gap-2 text-slate-150 font-light-yekanbakh text-sm transition hover:text-slate-600 mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 4v6h6"></path>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
            تغییر شماره موبایل
        </button>
    </form>
    <?php
    $forms_html = ob_get_clean();
    wp_send_json_success(['status' => 'new_user', 'html' => $forms_html]);
}