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

    <div class="wrapper border p-6 rounded-2xl max-w-[400px] w-full bg-white">

        <a href="<?php echo site_url(); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="113" fill="none" viewBox="0 0 113 29" class="w-40 2xl:w-44">
                <path class="fill-primary-500" fill-rule="evenodd" d="M110.388 23.144c-.991 0-1.771-.832-1.771-1.81V7.091c0-.98.781-1.811 1.771-1.811.99 0 1.77.832 1.77 1.811v14.243c0 .979-.78 1.81-1.77 1.81Zm-5.235 0H90.997c-.035 0-.069 0-.097-.002h-.005a6.754 6.754 0 0 1-4.632-2.029c-3.211 2.644-9.819 3.029-12.737-.207a6.753 6.753 0 0 1-5.029 2.238h-.034a6.753 6.753 0 0 1-5.029-2.238 6.757 6.757 0 0 1-5.03 2.238H46.643c-.962 0-1.77-.771-1.77-1.741 0-.97.808-1.742 1.77-1.742h11.761a3.289 3.289 0 0 0 3.288-3.288V13.94l.001-.049v-.019l.001-.014.002-.035.001-.014.002-.03.002-.011.001-.012a.29.29 0 0 1 .004-.031l.002-.015.004-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.026-.002.027-.001.037-.002h.006c.014-.001.028-.002.037-.001l.02-.001h.058l.042.001.032.001.008.001a.434.434 0 0 1 .054.003l.013.001.022.002.011.001a.236.236 0 0 0 .021.002l.014.002a1.72 1.72 0 0 1 1.468 1.397l.007.041.006.042.004.028.001.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016.001.01v.009l.001.026v2.472a3.289 3.289 0 0 0 3.288 3.288h.034a3.289 3.289 0 0 0 3.288-3.288v-2.491l.001-.01.001-.014.002-.035v-.014l.003-.03.001-.011.002-.012.003-.031.002-.015.005-.033.002-.012.005-.028a1.72 1.72 0 0 1 1.468-1.396l.047-.005.018-.002.025-.002.028-.001.036-.002h.007a.233.233 0 0 1 .036-.001l.02-.001h.059l.042.001.032.001.007.001a.459.459 0 0 1 .054.003l.014.001.022.002.011.001a.206.206 0 0 0 .021.002 1.72 1.72 0 0 1 1.482 1.399l.007.041.006.042.003.028.002.012a.31.31 0 0 1 .003.034l.002.025.001.01v.01l.001.016v.01l.001.009v.026l.001.014v2.458c0 4.429 6.896 3.85 9.115 1.631a5.362 5.362 0 0 0-3.774-9.155h-.021l-.023.001a5.342 5.342 0 0 0-3.767 1.57.563.563 0 0 1-.146.106 1.764 1.764 0 0 1-2.483-2.483.581.581 0 0 1 .105-.147L81.668.503a1.788 1.788 0 0 1 2.524 0 1.79 1.79 0 0 1 0 2.525l-1.471 1.471-.007.006-.863.864a8.938 8.938 0 0 1 6.643 13.01 3.276 3.276 0 0 0 2.177 1.255 1.79 1.79 0 0 1 .326-.031h14.156c.974 0 1.77.798 1.77 1.771 0 .974-.796 1.77-1.77 1.77Zm-63.937-13.13a1.952 1.952 0 1 1 0-3.904 1.952 1.952 0 0 1 0 3.904Zm-15.893 4.912-1.054-.188.094-.527a4.242 4.242 0 0 1 4.175-3.491h.535l-3.656 3.679-.094.527Zm7.077 3.871.006-.005a5.444 5.444 0 0 0-3.868-9.275 5.444 5.444 0 1 0 1.47 10.688l.078-.022 2.095 3.628h-3.643c-4.888 0-8.852-3.963-8.852-8.85a8.852 8.852 0 0 1 17.702 0v12.79l-5.103-8.839.115-.115ZM9.162 23.811a8.821 8.821 0 0 1-5.443-1.871v5c0 .938-.766 1.704-1.704 1.704A1.707 1.707 0 0 1 .312 26.94V14.961a8.852 8.852 0 0 1 17.702 0c0 4.887-3.965 8.85-8.852 8.85Zm0-14.294a5.444 5.444 0 0 0-3.868 9.275l.03.03.002.002a5.444 5.444 0 1 0 3.836-9.307Zm32.054 1.348c.938 0 1.703.766 1.703 1.703v14.371c0 .937-.765 1.703-1.703 1.703a1.706 1.706 0 0 1-1.703-1.703V12.568c0-.938.765-1.703 1.703-1.703Z" clip-rule="evenodd" />
                <path fill-rule="evenodd" d="M47.352 24.8c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm4.204 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Zm10.511 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.921-2.052 2.052-2.052Zm4.203 0c1.13 0 2.052.921 2.052 2.052 0 1.13-.922 2.051-2.052 2.051a2.055 2.055 0 0 1-2.052-2.051c0-1.131.922-2.052 2.052-2.052Z" clip-rule="evenodd" class="fill-slate-800" />
            </svg>
        </a>

        <div class="forms flex flex-col">

            <form action="#" method="post" id="login-form" class="w-full flex flex-col">
                <strong class="text-[22px] text-textColor font-bold block mt-9">ورود | ثبت نام</strong>
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
                    ورود
                </button>
            </form>

            <form action="#" method="post" id="verify-form" class="w-full flex flex-col hidden">
                <strong class="text-[22px] text-textColor font-bold block mt-9 text-center">کد تایید را وارد
                    کنید</strong>

                <div class="mt-3 flex gap-4 mx-10" data-number-code-form dir="ltr">
                    <input id="otp-number-code-0" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-0' data-number-code-input='0' required />
                    <input id="otp-number-code-1" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-1' data-number-code-input='1' required />
                    <input id="otp-number-code-2" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-2' data-number-code-input='2' required />
                    <input id="otp-number-code-3" class="border p-2 text-2xl rounded-xl w-full bg-white text-center outline-primaryColor" type="number" min='0' max='9' name='otp-number-code-3' data-number-code-input='3' required />
                </div>
                <div class="error"></div>
                <button type="button" id="back-to-login-form" class="text-slate-150 font-light-yekanbakh text-lg mt-4">
                    ویرایش شماره همراه
                </button>
                <button type="button" id="goto-password-form" class="text-slate-500 text-sm mt-2 underline">
                    ورود با رمز ثابت
                </button>
                <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl">
                    ورود
                </button>
                <div class="flex flex-col text-center text-textColor mt-4 text-lg counter">
                    زمان باقیمانده برای درخواست مجدد
                    <span class="text-primaryColor mt-4">60 ثانیه</span>
                </div>
            </form>

            <!-- فرم ورود با رمز ثابت -->
            <form action="#" method="post" id="password-form" class="w-full flex flex-col hidden">
                <strong class="text-[22px] text-textColor font-bold block mt-9 text-center">ورود با رمز عبور</strong>
                <p class="text-center text-sm text-slate-500 mt-2">
                    رمز عبور ثابت خود را وارد کنید
                </p>
                
                <label class="mt-4">
                    <input type="password" id="user-password" name="user-password" class="border p-2 text-2xl rounded-xl w-full bg-white outline-primaryColor text-center" placeholder="رمز عبور" tabindex="1">
                </label>
                
                <div class="error mt-2 text-red-500 text-sm"></div>

                <button type="button" id="back-to-verify-form" class="text-slate-150 font-light-yekanbakh text-lg mt-4">
                    بازگشت به تایید کد
                </button>
                <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl">
                    ورود
                </button>
            </form>

            <form action="#" method="post" id="register-form" class="w-full flex flex-col hidden">
                <strong class="text-[22px] text-textColor font-bold block mt-9">اطلاعات شخصی</strong>
                <label for="first-name" class="text-slate-150 font-light-yekanbakh mt-4">
                    نام خود را وارد کنید
                </label>
                <input type="text" id="first-name" name="first-name" class="border p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1" autofocus>
                <label for="last-name" class="text-slate-150 font-light-yekanbakh mt-4">
                    نام خانوادگی خود را وارد کنید
                </label>
                <input type="text" id="last-name" name="last-name" class="border p-2 text-2xl rounded-xl phone-number w-full bg-white mt-2 outline-primaryColor" tabindex="1" autofocus>
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
                <div class="error"></div>
                <button type="submit" class="bg-primaryColor text-white text-2xl mt-4 p-3 rounded-xl">
                    ثبت نام
                </button>
            </form>

        </div>

        <div class="text-slate-200 mt-3">
            ثبت نام شما به معنی پذیرش
            <a href="<?php echo home_url('terms'); ?>" class="text-blue">قوانین و مقررات</a>
            سایت اسکیپ‌زوم است.
        </div>

    </div>

</div>

<script>
    jQuery(document).ready(function($) {

        let timer
        let doneTimer = false
        let codeTemp = $("#verify-form .counter").html()
        let phone = $("#login-form").find('input[name="phone-number"]')
        let referrer = "<?php echo $_GET['redirect'] ?? $_SERVER['HTTP_REFERER'] ?>"

        const Toast = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 3000,
        })

        function startTimer() {
            $("#verify-form .counter span").html(`60 ثانیه`)

            let time = 60

            clearInterval(timer)
            timer = setInterval(() => {
                time = time - 1
                $("#verify-form .counter span").html(`${time} ثانیه`)

                if (time === 0) {
                    doneTimer = true
                    clearInterval(timer)
                    $("#verify-form .counter").html("زمان درخواست به اتمام رسید <a href='#' id='get-new-otp' class='text-primaryColor mt-4'>درخواست مجدد</a>")
                }
            }, 1000)
        }

        $("body").on('click', '#get-new-otp', function() {
            doneTimer = false
            $("#verify-form .counter").html(codeTemp)
            startTimer()

            $.ajax({
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                    'callback': 'auth_new_otp',
                    'phone': phone.val(),
                },
                success: function(response) {
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.data
                    })

                    // Auto focus on first OTP input after new code request
                    if (response.success) {
                        setTimeout(() => {
                            $("#otp-number-code-0").focus()
                        }, 100)
                    }
                }
            })
        })

        $("#login-form").on('submit', function(e) {
            e.preventDefault()

            let _ = $(this)
            let value = phone.val()

            let error = '';

            if (value === '') {
                error = 'شماره موبایل ضروری میباشد'
            } else if (value.length < 10 || value.length > 11) {
                error = 'شماره موبایل صحیح نیست'
            } else if (!/^(\+98|0|0098)?9\d{9}$/.test(value)) {
                value = s => s.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                value = s => s.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
                error = 'شماره موبایل صحیح نیست'
            }

            _.find('.error').html(function() {
                return error !== '' ? `<div class="span text-sm mt-2 text-primaryColor">${error}</div>` : ''
            })

            if (error === '') {
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    type: 'POST',
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'auth_login',
                        'phone': value,
                    },
                    beforeSend: function() {
                        _.find('button[type="submit"]')
                            .css('opacity', '.5')
                            .attr('disabled', 'disabled')
                            .html('<div class="spinner" style="width: 32px;border-color: #FFF;border-width: 6px; margin-inline: auto"></div>')
                    },
                    success: function(response) {
                        if (!response.success) {
                            _.find('.error')
                                .html(`<div class="span text-sm mt-2 text-primaryColor">${response.data}</div>`)
                        } else {
                            _.addClass('hidden')
                            $("#verify-form").removeClass('hidden')

                            doneTimer = false
                            $("#verify-form .counter").html(codeTemp)
                            startTimer()

                            // Auto focus on first OTP input
                            setTimeout(() => {
                                $("#otp-number-code-0").focus()
                            }, 100)
                        }

                        _.find('button[type="submit"]')
                            .css('opacity', '1')
                            .removeAttr('disabled', 'disabled')
                            .text('ورود')
                    },
                    error: function(){
                        _.addClass('hidden')
                            $("#verify-form").removeClass('hidden')

                            doneTimer = false
                            $("#verify-form .counter").html(codeTemp)
                            startTimer()

                            // Auto focus on first OTP input
                            setTimeout(() => {
                                $("#otp-number-code-0").focus()
                            }, 100)
                            _.find('button[type="submit"]')
                            .css('opacity', '1')
                            .removeAttr('disabled', 'disabled')
                            .text('ورود')
                        
                    }
                })
            }
        })

        const numberCodeForm = document.querySelector('[data-number-code-form]');
        const numberCodeInputs = [...numberCodeForm.querySelectorAll('[data-number-code-input]')];

        numberCodeForm.addEventListener('input', ({
            target
        }) => {
            if (!target.value.length) {
                return target.value = null;
            }

            const inputLength = target.value.length;
            let currentIndex = Number(target.dataset.numberCodeInput);

            if (inputLength > 1) {
                const inputValues = target.value.split('');

                inputValues.forEach((value, valueIndex) => {
                    const nextValueIndex = currentIndex + valueIndex;

                    if (nextValueIndex >= numberCodeInputs.length) {
                        return;
                    }

                    numberCodeInputs[nextValueIndex].value = value;
                });

                currentIndex += inputValues.length - 2;
            }

            const nextIndex = currentIndex + 1;

            if (nextIndex < numberCodeInputs.length) {
                numberCodeInputs[nextIndex].focus();
            } else {
                $("#verify-form").submit()
            }
        });

        numberCodeForm.addEventListener('keydown', e => {

            const {
                code,
                target
            } = e;

            const currentIndex = Number(target.dataset.numberCodeInput);
            const previousIndex = currentIndex - 1;
            const nextIndex = currentIndex + 1;

            const hasPreviousIndex = previousIndex >= 0;
            const hasNextIndex = nextIndex <= numberCodeInputs.length - 1

            switch (code) {
                case 'ArrowLeft':
                case 'ArrowUp':
                    if (hasPreviousIndex) {
                        numberCodeInputs[previousIndex].focus();
                    }
                    e.preventDefault();
                    break;

                case 'ArrowRight':
                case 'ArrowDown':
                    if (hasNextIndex) {
                        numberCodeInputs[nextIndex].focus();
                    }
                    e.preventDefault();
                    break;
                case 'Backspace':
                    if (!e.target.value.length && hasPreviousIndex) {
                        numberCodeInputs[previousIndex].value = null;
                        numberCodeInputs[previousIndex].focus();
                    }
                    break;
                default:
                    break;
            }
        });

        $("#back-to-login-form").on('click', function() {
            $("#login-form").removeClass('hidden')
            $("#verify-form").addClass('hidden')
            // Clear OTP inputs when going back
            numberCodeInputs.forEach(input => input.value = '')
        })

        $("#verify-form").on('submit', function(e) {
            e.preventDefault()

            let _ = $(this)
            let value = phone.val()

            let code = ''
            numberCodeInputs.forEach((item, index) => code += $(item).val())

            let error = ''

            if (code.length < 4) {
                error = 'کد وارد شده صحیح نیست'
            }

            _.find('.error').html(function() {
                return error !== '' ? `<div class="span text-sm mt-2 text-primaryColor">${error}</div>` : ''
            })

            if (error === '') {
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    type: 'POST',
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'auth_verify_otp',
                        'phone': value,
                        'code': code
                    },
                    beforeSend: function() {
                        _.find('button[type="submit"]')
                            .css('opacity', '.5')
                            .attr('disabled', 'disabled')
                            .html('<div class="spinner" style="width: 32px;border-color: #FFF;border-width: 6px; margin-inline: auto"></div>')
                    },
                    success: function(response) {
                        if (!response.success) {
                            _.find('.error')
                                .html(`<div class="span text-sm mt-2 text-primaryColor">${response.data}</div>`)

                            _.find('button[type="submit"]')
                                .css('opacity', '1')
                                .removeAttr('disabled', 'disabled')
                                .text('ورود')
                        } else {
                            if (!response.data.new) {
                                zebline.user.login(response.data.user_id.toString());

                                // Set user attributes in Zeblain
                                const userAttributes = {
                                    mobile: value.toString()
                                };

                                // Add user data if available
                                if (response.data.user_data) {
                                    if (response.data.user_data.firstname) {
                                        userAttributes.firstname = response.data.user_data.firstname.toString();
                                    }
                                    if (response.data.user_data.lastname) {
                                        userAttributes.lastname = response.data.user_data.lastname.toString();
                                    }
                                    if (response.data.user_data.city) {
                                        userAttributes.city = response.data.user_data.city.toString();
                                    }
                                    if (response.data.user_data.points !== undefined) {
                                        userAttributes.points = response.data.user_data.points;
                                    }
                                }

                                zebline.user.setAttributes(userAttributes);

                                Toast.fire({
                                    icon: 'success',
                                    title: 'با موفقیت وارد شدید'
                                })

                                setTimeout(() => window.location.href = referrer, 2000)
                            } else {
                                // Pre-fill existing data and hide completed fields
                                const userData = response.data.user_data || {};

                                if (userData.firstname) {
                                    $("#register-form #first-name").val(userData.firstname);
                                    $('label[for="first-name"]').hide();
                                    $("#register-form #first-name").hide();
                                }

                                if (userData.lastname) {
                                    $("#register-form #last-name").val(userData.lastname);
                                    $('label[for="last-name"]').hide();
                                    $("#register-form #last-name").hide();
                                }

                                if (userData.city) {
                                    $("#register-form #user-city").val(userData.city);
                                    $('label[for="user-city"]').hide();
                                    $("#register-form #user-city").hide();
                                }

                                _.addClass('hidden')
                                $("#register-form").removeClass('hidden')
                            }
                        }
                    }
                })
            }
        })

        $("#register-form").on('submit', function(e) {
            e.preventDefault()

            let _ = $(this)
            let value = phone.val()
            let firstname = _.find('input[name="first-name"]').val()
            let lastname = _.find('input[name="last-name"]').val()
            let city = _.find('select[name="user-city"]').val()

            let errors = []

            // Only validate visible fields
            if ($("#register-form #first-name").is(':visible') && firstname === '') {
                errors.push('فیلد نام ضروری است.')
            }

            if ($("#register-form #last-name").is(':visible') && lastname === '') {
                errors.push('فیلد نام خانوادگی ضروری است.')
            }

            if ($("#register-form #user-city").is(':visible') && city === '') {
                errors.push('لطفا شهر خود را انتخاب کنید.')
            }

            _.find('.error').html(function() {
                return errors.length > 0 ? `<div class="span text-sm mt-2 text-primaryColor">${errors.join("<br>")}</div>` : ''
            })

            if (errors.length < 1) {
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    type: 'POST',
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'auth_signup',
                        'phone': value,
                        'firstname': firstname,
                        'lastname': lastname,
                        'city': city
                    },
                    beforeSend: function() {
                        _.find('button[type="submit"]')
                            .css('opacity', '.5')
                            .attr('disabled', 'disabled')
                            .html('<div class="spinner" style="width: 32px;border-color: #FFF;border-width: 6px; margin-inline: auto"></div>')
                    },
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.data.msg
                        })
                        zebline.event.track("user_signed_up", {
                            "User ID": response.data.user_id.toString(),
                            "Referrer": referrer,
                            "Signup Date": new Date()
                        })
                        zebline.user.login(response.data.user_id.toString());
                        zebline.user.setAttributes({
                            mobile: value.toString(),
                            firstname: firstname.toString(),
                            lastname: lastname.toString(),
                            city: city.toString(),
                            points: 20
                        });
                        setTimeout(() => window.location.href = referrer, 2000)
                        _.find('button[type="submit"]').text('ورود')
                    }
                })
            }
        })

        // مدیریت نمایش فرم رمز عبور
        $("#goto-password-form").on('click', function() {
            $("#verify-form").addClass('hidden')
            $("#password-form").removeClass('hidden')
            $("#user-password").focus()
        })

        // بازگشت از فرم رمز عبور به تایید کد
        $("#back-to-verify-form").on('click', function() {
            $("#password-form").addClass('hidden')
            $("#verify-form").removeClass('hidden')
            // فوکوس روی اولین اینپوت کد تایید
            setTimeout(() => {
                $("#otp-number-code-0").focus()
            }, 100)
        })

        // مدیریت ارسال فرم رمز عبور
        $("#password-form").on('submit', function(e) {
            e.preventDefault()
            let _ = $(this)
            let value = phone.val() // شماره موبایل گرفته شده از فرم اول
            let password = _.find('input[name="user-password"]').val()
            let error = ''

            if (password === '') {
                error = 'لطفا رمز عبور را وارد کنید'
            }

            _.find('.error').html(function() {
                return error !== '' ? `<div>${error}</div>` : ''
            })

            if (error === '') {
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    type: 'POST',
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'auth_login_password', // نام تابعی که باید در PHP بسازید
                        'phone': value,
                        'password': password
                    },
                    beforeSend: function() {
                        _.find('button[type="submit"]')
                            .css('opacity', '.5')
                            .attr('disabled', 'disabled')
                            .html('<div class="spinner" style="width: 32px;border-color: #FFF;border-width: 6px; margin-inline: auto"></div>')
                    },
                    success: function(response) {
                        if (!response.success) {
                            _.find('.error')
                                .html(`<div>${response.data}</div>`)
                            _.find('button[type="submit"]')
                                .css('opacity', '1')
                                .removeAttr('disabled', 'disabled')
                                .text('ورود')
                        } else {
                            if (!response.data.new) {
                                zebline.user.login(response.data.user_id.toString());

                                // Set user attributes in Zeblain
                                const userAttributes = {
                                    mobile: value.toString()
                                };

                                // Add user data if available
                                if (response.data.user_data) {
                                    if (response.data.user_data.firstname) {
                                        userAttributes.firstname = response.data.user_data.firstname.toString();
                                    }
                                    if (response.data.user_data.lastname) {
                                        userAttributes.lastname = response.data.user_data.lastname.toString();
                                    }
                                    if (response.data.user_data.city) {
                                        userAttributes.city = response.data.user_data.city.toString();
                                    }
                                    if (response.data.user_data.points !== undefined) {
                                        userAttributes.points = response.data.user_data.points;
                                    }
                                }

                                zebline.user.setAttributes(userAttributes);

                                Toast.fire({
                                    icon: 'success',
                                    title: 'با موفقیت وارد شدید'
                                })

                                setTimeout(() => window.location.href = referrer, 2000)
                            } else {
                                // Pre-fill existing data and hide completed fields
                                const userData = response.data.user_data || {};

                                if (userData.firstname) {
                                    $("#register-form #first-name").val(userData.firstname);
                                    $('label[for="first-name"]').hide();
                                    $("#register-form #first-name").hide();
                                }

                                if (userData.lastname) {
                                    $("#register-form #last-name").val(userData.lastname);
                                    $('label[for="last-name"]').hide();
                                    $("#register-form #last-name").hide();
                                }

                                if (userData.city) {
                                    $("#register-form #user-city").val(userData.city);
                                    $('label[for="user-city"]').hide();
                                    $("#register-form #user-city").hide();
                                }

                                _.addClass('hidden')
                                $("#register-form").removeClass('hidden')
                            }
                        }
                        
                    },
                    error: function() {
                        _.find('.error').html('<div>خطایی رخ داد. لطفا مجددا تلاش کنید.</div>')
                        _.find('button[type="submit"]')
                            .css('opacity', '1')
                            .removeAttr('disabled', 'disabled')
                            .text('ورود')
                    }
                })
            }
        })

        $("#first-name, #last-name").on('input', function() {
            let value = $(this).val();
            let regex = /[^ا-ی ]/g;

            // پیدا کردن یا ساختن تگ p خطا
            let errorEl = $(this).next('.error-msg');
            if (!errorEl.length) {
                $(this).after('<p class="error-msg" style="color:red;font-size:12px;margin-top:4px;display:none;">شما فقط می‌توانید حروف فارسی وارد کنید</p>');
                errorEl = $(this).next('.error-msg');
            }

            // چک کردن وجود حروف انگلیسی
            if (/[a-zA-Z]/.test(value)) {
                errorEl.show();
            } else {
                errorEl.hide();
            }

            // حذف کاراکترهای غیر فارسی از ورودی
            $(this).val(value.replace(regex, ''));
        });

    })
</script>