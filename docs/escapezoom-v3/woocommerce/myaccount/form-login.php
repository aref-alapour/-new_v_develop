<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>
<div class="w-screen h-screen flex items-center justify-center">
    <div class="wrapper border p-6 rounded-2xl max-w-d400 w-full bg-white">
        <a href="<?php echo site_url(); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="113" fill="none" viewBox="0 0 113 29" class="w-40 2xl:w-44">
                <path class="fill-primary-500" fill-rule="evenodd" d="M110.388 23.144c-.991 0-1.771-.832-1.771-1.81V7.091c0-.98.781-1.811 1.771-1.811.99 0 1.77.832 1.77 1.811v14.243c0 .979-.78 1.81-1.77 1.81Zm-5.235 0H90.997c-.035 0-.069 0-.097-.002h-.005a6.754 6.754 0 0 1-4.632-2.029c-3.211 2.644-9.819 3.029-12.737-.207a6.753 6.753 0 0 1-5.029 2.238h-.034a6.753 6.753 0 0 1-5.029-2.238 6.757 6.757 0 0 1-5.03 2.238H46.643c-.962 0-1.77-.771-1.77-1.741 0-.97.808-1.742 1.77-1.742h11.761a3.289 3.289 0 0 0 3.288-3.288V13.94l.001-.049v-.019l.001-.014.002-.035.001-.014.002-.03.002-.011.001-.012a.29.29 0 0 1 .004-.031l.002-.015.004-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.026-.002.027-.001.037-.002h.006c.014-.001.028-.002.037-.001l.02-.001h.058l.042.001.032.001.008.001a.434.434 0 0 1 .054.003l.013.001.022.002.011.001a.236.236 0 0 0 .021.002l.014.002a1.72 1.72 0 0 1 1.468 1.397l.007.041.006.042.004.028.001.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016.001.01v.009l.001.026v2.472a3.289 3.289 0 0 0 3.288 3.288h.034a3.289 3.289 0 0 0 3.288-3.288v-2.491l.001-.01.001-.014.002-.035v-.014l.003-.03.001-.011.002-.012.003-.031.002-.015.005-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.025-.002.028-.001.036-.002h.007a.233.233 0 0 1 .036-.001l.02-.001h.059l.042.001.032.001.007.001a.459.459 0 0 1 .054.003l.014.001.022.002.011.001a.206.206 0 0 0 .021.002 1.72 1.72 0 0 1 1.482 1.399l.007.041.006.042.003.028.002.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016v.01l.001.009v.026l.001.014v2.458c0 4.429 6.896 3.85 9.115 1.631a5.362 5.362 0 0 0-3.774-9.155h-.021l-.023.001a5.342 5.342 0 0 0-3.767 1.57.563.563 0 0 1-.146.106 1.764 1.764 0 0 1-2.483-2.483.581.581 0 0 1 .105-.147L81.668.503a1.788 1.788 0 0 1 2.524 0 1.79 1.79 0 0 1 0 2.525l-1.471 1.471-.007.006-.863.864a8.938 8.938 0 0 1 6.643 13.01 3.276 3.276 0 0 0 2.177 1.255 1.79 1.79 0 0 1 .326-.031h14.156c.974 0 1.77.798 1.77 1.771 0 .974-.796 1.77-1.77 1.77Zm-63.937-13.13a1.952 1.952 0 1 1 0-3.904 1.952 1.952 0 0 1 0 3.904Zm-15.893 4.912-1.054-.188.094-.527a4.242 4.242 0 0 1 4.175-3.491h.535l-3.656 3.679-.094.527Zm7.077 3.871.006-.005a5.444 5.444 0 0 0-3.868-9.275 5.444 5.444 0 1 0 1.47 10.688l.078-.022 2.095 3.628h-3.643c-4.888 0-8.852-3.963-8.852-8.85a8.852 8.852 0 0 1 17.702 0v12.79l-5.103-8.839.115-.115ZM9.162 23.811a8.821 8.821 0 0 1-5.443-1.871v5c0 .938-.766 1.704-1.704 1.704A1.707 1.707 0 0 1 .312 26.94V14.961a8.852 8.852 0 0 1 17.702 0c0 4.887-3.965 8.85-8.852 8.85Zm0-14.294a5.444 5.444 0 0 0-3.868 9.275l.03.03.002.002a5.444 5.444 0 1 0 3.836-9.307Zm32.054 1.348c.938 0 1.703.766 1.703 1.703v14.371c0 .937-.765 1.703-1.703 1.703a1.706 1.706 0 0 1-1.703-1.703V12.568c0-.938.765-1.703 1.703-1.703Z" clip-rule="evenodd" />
                <path fill-rule="evenodd" d="M47.352 24.8c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm10.511 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.921-2.052 2.052-2.052Zm4.203 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Z" clip-rule="evenodd" class="fill-slate-800" />
            </svg>
        </a>
       <div class="forms flex flex-col">
            <form action="#" method="post" id="login-form" class="w-full flex flex-col">
                <strong class="text-22 text-textColor font-bold block mt-9">ورود | ثبت نام</strong>
                <p class="text-slate-150 font-light-yekanbakh text-lg mt-4">
                    سلام
                    <br>
                    لطفا شماره موبایل خود را وارد کنید
                </p>
                <label class="mt-3">
                    <input type="number" maxlength="11" name="phone-number" class="border p-2 text-2xl rounded-xl phone-number w-full bg-white outline-primaryColor" dir="ltr" tabindex="1" autofocus>
                </label>
                <div class="error"></div>
                <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl">
                    ادامه
                </button>
            </form>
        </div>
        <div id="support-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full mx-4 text-center relative">
                <button id="btn-close-modal" class="text-slate-400 text-xl bg-gray-100 w-6 h-6 left-3 hover:text-slate-600">×</button>
                <h3 class="text-lg font-bold text-textColor mb-4">دریافت کد تایید</h3>
                <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                    اگه منتظر موندی و پیامکی دریافت نکردی، بدون خارج شدن از این صفحه، می تونی با همین شماره <strong class="text-primaryColor">02191307900</strong> تماس بگیری و کد یکبار مصرفت رو دریافت کنی.
                </p>
                <div class="flex flex-col gap-3">
                    <a href="tel:02191307900" class="block w-full bg-green-500 text-white py-3 rounded-xl hover:bg-green-600 font-bold-yekanbakh text-lg">تماس با پشتیبانی</a>
                    <button id="btn-modal-resend" class="block w-full bg-primaryColor text-white py-3 rounded-xl hover:bg-blue-700 font-bold-yekanbakh text-lg">ارسال مجدد کد</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 1. ذخیره فرم اول در متغیر برای بازگشت سریع
    let initialLoginHtml = $('.forms').html();
    
    let phone = $("#login-form").find('input[name="phone-number"]');
    let currentMobile = '';
    let currentType = ''; 
    let timerInterval;
    let isNewUserFlow = false;

    const Toast = Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        timer: 3000,
    });

    // --- تابع مدیریت وضعیت دکمه (Loading State) ---
    function toggleButtonState(btn, isLoading, defaultText) {
        if (isLoading) {
            if (!btn.data('original-text')) {
                btn.data('original-text', btn.text());
            }
            btn.css('opacity', '.5')
               .attr('disabled', 'disabled')
               .html('<div class="spinner" style="width: 32px;border-color: #FFF;border-width: 6px; margin-inline: auto"></div>');
        } else {
            btn.css('opacity', '1')
               .removeAttr('disabled', 'disabled');
            
            if (defaultText) {
                btn.text(defaultText);
            } else {
                btn.text(btn.data('original-text'));
            }
        }
    }

    // --- مدیریت نمایش/مخفی کردن رمز عبور (اصلاح شده) ---
    $(document).on('click', '.toggle-password', function() {
        // پیدا کردن اینپوت مرتبط با دکمه (چه بر اساس ID چه بر اساس data-target)
        let targetId = $(this).data('target');
        let input;
        
        if (targetId) {
            input = $('#' + targetId);
        } else {
            // اگر ID نداشت، اینپوت هم‌سطح قبلی را پیدا کن
            input = $(this).siblings('input[type="password"], input[type="text"]');
        }

        if (input.length) {
            let type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            
            // تغییر رنگ آیکون
            if (type === 'text') {
                $(this).removeClass('text-slate-400').addClass('text-primaryColor');
            } else {
                $(this).removeClass('text-primaryColor').addClass('text-slate-400');
            }
        }
    });

    // --- 1. مدیریت فرم ورود شماره موبایل ---
    $(document).on('submit', '#login-form', function(e) {
        e.preventDefault();
        let _ = $(this);
        let value = _.find('input[name="phone-number"]').val();
        let errorDiv = _.find('.error');
        let submitBtn = _.find('button[type="submit"]');

        if (!value || value.length < 10) {
            errorDiv.text('شماره موبایل صحیح نیست').show();
            return;
        }
        errorDiv.hide();

        currentMobile = value;

        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: 'POST',
            data: {
                'action': 'v2_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce'); ?>",
                'phone': value,
                'callback': 'auth_login'
            },
            beforeSend: function() {
                toggleButtonState(submitBtn, true);
            },
            success: function(response) {
                if (!response.success) {
                    errorDiv.text(response.data).show();
                    toggleButtonState(submitBtn, false);
                } else {
                    if (response.data.status === 'old_user') {
                        $('.forms').append(response.data.html);
                        $('#password-form').removeClass('hidden');
                        $('#login-form').addClass('hidden');
                    } else if (response.data.status === 'new_user') {
                        isNewUserFlow = true;
                        $('.forms').append(response.data.html);
                        $('#otp-form').removeClass('hidden');
                        $('#login-form').addClass('hidden');
                        currentType = 'register'; 
                        startTimer(120);
                    }
                }
            },
            error: function() {
                errorDiv.text('خطا در ارتباط با سرور').show();
                toggleButtonState(submitBtn, false);
            }
        });
    });

    // --- مدیریت دکمه بازگشت به ابتدای فرم (تغییر شماره) ---
    $(document).on('click', '.btn-back-to-login', function() {
        $('.forms').html(initialLoginHtml);
        currentMobile = '';
        currentType = '';
        isNewUserFlow = false;
        clearInterval(timerInterval);
    });

    // --- مدیریت دکمه بازگشت به فرم رمز عبور ---
    $(document).on('click', '.btn-back-to-password', function() {
        $('#otp-form').addClass('hidden');
        $('#password-form').removeClass('hidden');
        $('#password-form').find('.error').hide();
        $('input[data-number-code-input]').val('');
        clearInterval(timerInterval);
    });

    // --- 2. مدیریت فرم ورود با رمز عبور ---
    $(document).on('submit', '#password-form', function(e) {
        e.preventDefault();
        let _ = $(this);
        let password = _.find('#user-password').val();
        let errorDiv = _.find('.error');
        let submitBtn = _.find('button[type="submit"]');

        if (!password) {
            errorDiv.text('لطفا رمز عبور را وارد کنید').show();
            return;
        }

        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: 'POST',
            data: {
                'action': 'v2_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce'); ?>",
                'phone': currentMobile,
                'password': password,
                'callback': 'auth_login_password'
            },
            beforeSend: function() {
                toggleButtonState(submitBtn, true);
            },
            success: function(response) {
                if (!response.success) {
                    errorDiv.text(response.data).show();
                    toggleButtonState(submitBtn, false);
                } else {
                    if (response.data.redirect) {
                        Toast.fire({ icon: 'success', title: 'با موفقیت وارد شدید' });
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    } else if (response.data.profile_incomplete) {
                        $('#password-form').addClass('hidden');
                        $('#profile-form').removeClass('hidden');
                    }
                }
            },
            error: function() {
                errorDiv.text('خطایی رخ داد. لطفا مجددا تلاش کنید.').show();
                toggleButtonState(submitBtn, false);
            }
        });
    });

    // --- دکمه فراموشی رمز عبور ---
    $(document).on('click', '.btn-forgot-password', function() {
        $('#password-form').addClass('hidden');
        $('#otp-form').removeClass('hidden');
        $('#otp-title').text('بازیابی رمز عبور');
        $('#otp-mobile-display').text(currentMobile);
        currentType = 'reset';
        startTimer(120);
        sendOtpAjax(currentMobile, 'reset');
    });

    // --- تابع ارسال کد OTP ---
    function sendOtpAjax(mobile, type) {
        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: 'POST',
            data: {
                'action': 'v2_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce'); ?>",
                'callback': 'auth_send_otp',
                'phone': mobile,
                'type': type
            },
            success: function(response) {
                if (!response.success) {
                    let activeForm = $('.forms form:not(.hidden)');
                    activeForm.find('.error').text(response.data).show();
                }
            },
            error: function() {
                Toast.fire({ icon: 'error', title: 'خطا در ارتباط با سرور' });
            }
        });
    }

    // --- مدیریت اینپوت‌های OTP ---
    $(document).on('keyup', 'input[data-number-code-input]', function(e) {
        let index = $(this).data('number-code-input');
        let val = $(this).val();
        
        if (val.length === 1) {
            if (index < 3) {
                $('input[data-number-code-input="' + (index + 1) + '"]').focus();
            }
        } else if (e.keyCode === 8 && val.length === 0) {
            if (index > 0) {
                $('input[data-number-code-input="' + (index - 1) + '"]').focus();
            }
        }
    });

    // --- مدیریت Paste در اینپوت OTP ---
    $(document).on('paste', 'input[data-number-code-input]', function(e) {
        e.preventDefault();
        let pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        pasteData = pasteData.replace(/\D/g, '').substring(0, 4);

        if (pasteData.length > 0) {
            let inputs = $('input[data-number-code-input]');
            inputs.val('');
            for (let i = 0; i < pasteData.length; i++) {
                $(inputs[i]).val(pasteData[i]);
            }
            let focusIndex = Math.min(pasteData.length, 3);
            $(inputs[focusIndex]).focus();

            if (pasteData.length === 4) {
                $('#otp-form').trigger('submit');
            }
        }
    });

    // --- مدیریت فرم OTP ---
    $(document).on('submit', '#otp-form', function(e) {
        e.preventDefault();
        let _ = $(this);
        let code = '';
        $('input[data-number-code-input]').each(function() {
            code += $(this).val();
        });
        let errorDiv = _.find('.error');
        let submitBtn = _.find('button[type="submit"]');

        if (code.length !== 4) {
            errorDiv.text('کد ۴ رقمی را کامل وارد کنید').show();
            return;
        }

        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: 'POST',
            data: {
                'action': 'v2_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce'); ?>",
                'callback': 'auth_verify_otp',
                'phone': currentMobile,
                'code': code,
                'type': currentType
            },
            beforeSend: function() {
                toggleButtonState(submitBtn, true);
            },
            success: function(response) {
                if (!response.success) {
                    errorDiv.text(response.data).show();
                    toggleButtonState(submitBtn, false);
                } else {
                    if (currentType === 'register') {
                        $('#otp-form').addClass('hidden');
                        $('#profile-form').removeClass('hidden');
                    } else if (currentType === 'reset') {
                        $('#otp-form').addClass('hidden');
                        $('#new-password-form').removeClass('hidden');
                    }
                }
            },
            error: function() {
                errorDiv.text('خطا در ارتباط با سرور').show();
                toggleButtonState(submitBtn, false);
            }
        });
    });

    // --- تایمر و ارسال مجدد ---
    function startTimer(duration) {
        let timer = duration, minutes, seconds;
        clearInterval(timerInterval);
        
        $('#timer').text(timer + ' ثانیه');
        $('#timer').parent('.counter').show();
        $('#resend-otp-failed-btn').addClass('hidden');
        
        timerInterval = setInterval(function () {
            if (--timer < 0) {
                clearInterval(timerInterval);
                $('#timer').parent('.counter').hide();
                $('#resend-otp-failed-btn').removeClass('hidden');
            } else {
                $('#timer').text(timer + ' ثانیه');
            }
        }, 1000);
    }

    $(document).on('click', '#resend-otp-failed-btn', function() {
        $('#support-modal').removeClass('hidden');
    });

    $(document).on('click', '#btn-close-modal', function() {
        $('#support-modal').addClass('hidden');
    });

    $(document).on('click', '#btn-modal-resend', function() {
        $('#support-modal').addClass('hidden');
        sendOtpAjax(currentMobile, currentType);
        startTimer(120);
    });

        // --- مدیریت فرم پروفایل ---
    $(document).on('submit', '#profile-form', function(e) {
        e.preventDefault();
        let _ = $(this);
        let errorDiv = _.find('.error');
        let submitBtn = _.find('button[type="submit"]');
        
        // پیدا کردن تمام اینپوت‌های داخل فرم که مخفی نیستند
        let visibleInputs = _.find('input, select').filter(function() {
            return $(this).css('display') !== 'none' && $(this).css('visibility') !== 'hidden';
        });

        let isValid = true;
        let firstErrorInput = null;

        // بررسی اینکه آیا فیلدهای موجود خالی هستند یا خیر
        visibleInputs.each(function() {
            let val = $(this).val();
            if (!val || val.trim() === '') {
                isValid = false;
                if (!firstErrorInput) firstErrorInput = $(this);
            }
        });

        if (!isValid) {
            errorDiv.text('لطفا تمام فیلدهای خالی را پر کنید').show();
            if (firstErrorInput) {
                firstErrorInput.focus();
            }
            return;
        }

                // اگر کاربر جدید است، رمز عبور را هم چک کن
        if (isNewUserFlow) {
            let pass = $('#reg-password').val();
            let confirm = $('#reg-confirm-password').val();
            
            // چک کردن اینکه اینپوت رمز عبور در صفحه هست یا خیر
            if ($('#reg-password').length > 0 && $('#reg-password').css('display') !== 'none') {
                if (!pass) {
                    errorDiv.text('لطفا رمز عبور را وارد کنید').show();
                    return;
                }
                
                // --- اعتبارسنجی فرانت‌اند (مشابه فرم تغییر رمز) ---
                // ۱. حداقل ۱۰ کاراکتر
                if (pass.length < 10) {
                    errorDiv.text('رمز عبور باید حداقل ۱۰ کاراکتر باشد').show();
                    return;
                }
                // ۲. شامل حروف بزرگ، کوچک و عدد
                let hasUpper = /[A-Z]/.test(pass);
                let hasLower = /[a-z]/.test(pass);
                let hasNumber = /[0-9]/.test(pass);
                if (!hasUpper || !hasLower || !hasNumber) {
                    errorDiv.text('رمز عبور باید شامل حروف بزرگ، کوچک و عدد باشد').show();
                    return;
                }
                // ----------------------------------------------

                if (pass !== confirm) {
                    errorDiv.text('تکرار رمز عبور مطابقت ندارد').show();
                    return;
                }
            }
        }

        let fname = $('#first-name').val();
        let lname = $('#last-name').val();
        let city = $('#user-city').val();

        let callbackName = isNewUserFlow ? 'auth_register_new' : 'auth_signup';
        let postData = {
            'action': 'v2_ajax_handler',
            'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce'); ?>",
            'callback': callbackName,
            'phone': currentMobile,
            'firstname': fname,
            'lastname': lname,
            'city': city
        };

        if (isNewUserFlow) {
            postData.password = $('#reg-password').val();
            postData.confirm_password = $('#reg-confirm-password').val();
        }

        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: 'POST',
            data: postData,
            beforeSend: function() {
                toggleButtonState(submitBtn, true);
            },
            success: function(response) {
                if (!response.success) {
                    errorDiv.text(response.data).show();
                    toggleButtonState(submitBtn, false);
                } else {
                    if (response.data.redirect) {
                        Toast.fire({ icon: 'success', title: response.data.message || 'عملیات موفقیت آمیز بود' });
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    }
                }
            },
            error: function() {
                errorDiv.text('خطا در ذخیره اطلاعات.').show();
                toggleButtonState(submitBtn, false);
            }
        });
    });

    // --- مدیریت فرم تغییر رمز عبور جدید ---
    $(document).on('submit', '#new-password-form', function(e) {
        e.preventDefault();
        let _ = $(this);
        let pass = $('#new-password-input').val();
        let confirm = $('#confirm-new-password-input').val();
        let errorDiv = _.find('.error');
        let submitBtn = _.find('button[type="submit"]');

        if (!pass) {
            errorDiv.text('لطفا رمز عبور جدید را وارد کنید').show();
            return;
        }
        
        // --- اعتبارسنجی فرانت‌اند ---
        // ۱. حداقل ۱۰ کاراکتر
        if (pass.length < 10) {
            errorDiv.text('رمز عبور باید حداقل ۱۰ کاراکتر باشد').show();
            return;
        }
        // ۲. شامل حروف بزرگ، کوچک و عدد
        let hasUpper = /[A-Z]/.test(pass);
        let hasLower = /[a-z]/.test(pass);
        let hasNumber = /[0-9]/.test(pass);

        if (!hasUpper || !hasLower || !hasNumber) {
            errorDiv.text('رمز عبور باید شامل حروف بزرگ، کوچک و عدد باشد').show();
            return;
        }
        // ------------------------------

        if (pass !== confirm) {
            errorDiv.text('تکرار رمز عبور مطابقت ندارد').show();
            return;
        }

        $.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: 'POST',
            data: {
                'action': 'v2_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce'); ?>",
                'callback': 'auth_reset_password_final',
                'phone': currentMobile,
                'password': pass
            },
            beforeSend: function() {
                toggleButtonState(submitBtn, true);
            },
            success: function(response) {
                if (!response.success) {
                    errorDiv.text(response.data).show();
                    toggleButtonState(submitBtn, false);
                } else {
                    Toast.fire({ icon: 'success', title: response.data.message || 'رمز عبور تغییر کرد' });
                    setTimeout(() => {
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        } else if (response.data.profile_incomplete) {
                            $('#new-password-form').addClass('hidden');
                            $('#profile-form').removeClass('hidden');
                        }
                    }, 1000);
                }
            },
            error: function() {
                errorDiv.text('خطا در ارتباط با سرور').show();
                toggleButtonState(submitBtn, false);
            }
        });
    });
    // --- اعتبارسنجی ورودی فارسی ---
    $(document).on('input', '#first-name, #last-name', function() {
        let value = $(this).val();
        let regex = /[^ا-ی ]/g;
        let errorEl = $(this).next('.error-msg');
        if (!errorEl.length) {
            $(this).after('<p class="error-msg absolute" style="color:red;font-size:10px;top: 100%; left: 50px;display:none;">شما فقط می‌توانید حروف فارسی وارد کنید</p>');
            errorEl = $(this).next('.error-msg');
        }
        if (/[a-zA-Z]/.test(value)) {
            errorEl.show();
        } else {
            errorEl.hide();
        }
        $(this).val(value.replace(regex, ''));
    });
});
</script>