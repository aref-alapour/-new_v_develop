jQuery(document).ready(function ($) {
    const EZ_TZ = 'Asia/Tehran';

    /** Same calendar day labels as server jdate (reserve / checkout). */
    function ezTehranFmt(unixSec, part) {
        const d = new Date(unixSec * 1000);
        const base = { timeZone: EZ_TZ };
        if (part === 'dddd') {
            return new Intl.DateTimeFormat('fa-IR', Object.assign({ weekday: 'long' }, base)).format(d);
        }
        if (part === 'D' || part === 'd') {
            return new Intl.DateTimeFormat('fa-IR', Object.assign({ day: 'numeric' }, base)).format(d);
        }
        if (part === 'MMMM') {
            return new Intl.DateTimeFormat('fa-IR', Object.assign({ month: 'long' }, base)).format(d);
        }
        if (part === 'HH:mm') {
            return new Intl.DateTimeFormat('en-GB', {
                timeZone: EZ_TZ,
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }).format(d);
        }
        if (part === 'hour') {
            return parseInt(
                new Intl.DateTimeFormat('en', {
                    timeZone: EZ_TZ,
                    hour: 'numeric',
                    hour12: false,
                }).format(d),
                10
            );
        }
        return '';
    }

    function ezTehranMidnightNow() {
        const parts = new Intl.DateTimeFormat('en', {
            timeZone: EZ_TZ,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).formatToParts(new Date());
        const y = parts.find((p) => p.type === 'year').value;
        const m = parts.find((p) => p.type === 'month').value;
        const day = parts.find((p) => p.type === 'day').value;
        return Math.floor(Date.parse(`${y}-${m}-${day}T00:00:00+03:30`) / 1000);
    }

    function ezProductReviewAjaxMessage(data) {
        if (data && typeof data === 'object' && data.message) {
            return data.message;
        }
        return data;
    }

    function ezResetReviewFormToDefaults($form) {
        const colors = {
            1: '#E76262',
            2: '#DBAE41',
            3: '#9FC537',
            4: '#77BE39',
            5: '#02C96F',
        };
        $form.find('#content').val('');
        $form.find('#review_comment_id').val(0);
        $form.find('[data-rating-item]').removeClass('active').removeAttr('style');
        const items = {};
        $form.find('[data-rating-item]').each(function () {
            items[String($(this).data('rating-item'))] = true;
        });
        Object.keys(items).forEach(function (key) {
            const $btn = $form.find('[data-rating-item="' + key + '"][data-rate="100"]');
            if ($btn.length) {
                const rate = $btn.data('rate');
                $btn.addClass('active').css({
                    background: colors[rate / 20],
                    color: '#FFFFFF',
                });
            }
        });
    }

    function ezProductReviewApplyEditToForm($form) {
        const o = typeof ProductJsObject !== 'undefined' ? ProductJsObject : {};
        const edit = o.review_edit;
        const isEdit = !!(o.can_edit_review && edit && edit.comment_id);
        const colors = {
            1: '#E76262',
            2: '#DBAE41',
            3: '#9FC537',
            4: '#77BE39',
            5: '#02C96F',
        };
        $('.comment-modal-main-title').text(isEdit ? 'ویرایش نظر' : 'ارسال نظر');
        $('.comment-modal-main-title-mobile').text(isEdit ? 'ویرایش نظر' : 'ارسال نظر');
        $form.find('#comment-form-submit').text(isEdit ? 'ذخیره تغییرات' : 'ثبت نظر');
        if (!isEdit) {
            ezResetReviewFormToDefaults($form);
            return;
        }
        $form.find('#review_comment_id').val(edit.comment_id);
        $form.find('#content').val(edit.content || '');
        $form.find('[data-rating-item]').removeClass('active').removeAttr('style');
        const rates = edit.rates || {};
        Object.keys(rates).forEach(function (key) {
            const raw = rates[key];
            const rateNum = parseInt(raw, 10);
            if (!rateNum) {
                return;
            }
            const $btn = $form.find('[data-rating-item="' + key + '"][data-rate="' + rateNum + '"]');
            if ($btn.length) {
                $btn.addClass('active').css({
                    background: colors[rateNum / 20],
                    color: '#FFFFFF',
                });
            }
        });
    }

    $(".need-login").on('click', function () {
        Swal.fire({
            iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="95" height="97" viewBox="0 0 95 97" fill="none" class="-mr-2.5">
<g filter="url(#filter0_d_23347_17729)">
<mask id="path-1-inside-1_23347_17729" fill="white">
<path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z"/>
</mask>
<path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z" fill="white"/>
<path d="M70.5 31.5C70.5 48.6208 56.6208 62.5 39.5 62.5V63.5C57.1731 63.5 71.5 49.1731 71.5 31.5H70.5ZM39.5 62.5C22.3792 62.5 8.5 48.6208 8.5 31.5H7.5C7.5 49.1731 21.8269 63.5 39.5 63.5V62.5ZM8.5 31.5C8.5 14.3792 22.3792 0.5 39.5 0.5V-0.5C21.8269 -0.5 7.5 13.8269 7.5 31.5H8.5ZM39.5 0.5C56.6208 0.5 70.5 14.3792 70.5 31.5H71.5C71.5 13.8269 57.1731 -0.5 39.5 -0.5V0.5Z" fill="#5091FB" mask="url(#path-1-inside-1_23347_17729)"/>
</g>
<g filter="url(#filter1_i_23347_17729)">
<circle cx="39.8016" cy="22.45" r="9.45" fill="url(#paint0_linear_23347_17729)"/>
</g>
<g filter="url(#filter2_i_23347_17729)">
<ellipse cx="39.8" cy="43.4496" rx="16.8" ry="7.35" fill="url(#paint1_linear_23347_17729)"/>
</g>
<defs>
<filter id="filter0_d_23347_17729" x="0" y="0" width="95" height="97" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="8" dy="18"/>
<feGaussianBlur stdDeviation="8"/>
<feComposite in2="hardAlpha" operator="out"/>
<feColorMatrix type="matrix" values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0"/>
<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_23347_17729"/>
<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_23347_17729" result="shape"/>
</filter>
<filter id="filter1_i_23347_17729" x="29.3516" y="11" width="19.8984" height="20.9004" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="-1" dy="-2"/>
<feGaussianBlur stdDeviation="1.5"/>
<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
<feColorMatrix type="matrix" values="0 0 0 0 0.520833 0 0 0 0 0.689332 0 0 0 0 1 0 0 0 1 0"/>
<feBlend mode="normal" in2="shape" result="effect1_innerShadow_23347_17729"/>
</filter>
<filter id="filter2_i_23347_17729" x="22" y="34.0996" width="34.6016" height="16.7002" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="-1" dy="-2"/>
<feGaussianBlur stdDeviation="1.5"/>
<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
<feColorMatrix type="matrix" values="0 0 0 0 0.501961 0 0 0 0 0.67451 0 0 0 0 0.996078 0 0 0 1 0"/>
<feBlend mode="normal" in2="shape" result="effect1_innerShadow_23347_17729"/>
</filter>
<linearGradient id="paint0_linear_23347_17729" x1="64.2409" y1="12.5616" x2="43.2367" y2="12.0588" gradientUnits="userSpaceOnUse">
<stop stop-color="#5091FB"/>
<stop offset="1" stop-color="#3F7FF5"/>
</linearGradient>
<linearGradient id="paint1_linear_23347_17729" x1="83.2478" y1="35.7586" x2="45.9971" y2="33.7204" gradientUnits="userSpaceOnUse">
<stop stop-color="#5091FB"/>
<stop offset="1" stop-color="#3F7FF5"/>
</linearGradient>
</defs>
</svg>`,
            customClass: {
                icon: 'border-0',
                title: 'text-lg leading-5 pt-0',
                actions: 'w-full px-4',
                confirmButton: 'w-full bg-primaryColor text-white shadow-13 rounded-lg p-1',
                popup: 'rounded-2xl'
            },
            confirmButtonText: 'ورود به حساب کاربری',
            buttonsStyling: false,
            title: 'برای ارسال نظر، لطفاً به حساب کاربری خود وارد شوید.',
            width: 240,
        }).then(result => {
            if (result.isConfirmed) {
                let url = window.location.href = window.location.origin + '/panel?redirect=' + window.location.href
                window.location.href = url
            }
        })
    })

    /**
     * Initialize Toast
     */
    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-start',
        showConfirmButton: false,
        timer: 3000,
    })

    /**
     * Load comments with pagination and sorting
     * @param {boolean} append - اگر true باشه، به انتهای لیست اضافه میکنه (load more)
     */
    const loadComments = (append = false) => {
        const page = append ? parseInt($('#comments-current-page').val()) + 1 : 1;
        const sortType = $('#comments-sort-type').val() || 'newest';
        const productId = parseInt($('#comments-product-id').val()) || ProductJsObject.product_id;
        const productType = $('#comments-product-type').val() || ProductJsObject.product_type;
        const totalPages = parseInt($('#comments-total-pages').val());

        // اگر append باشه، صفحه رو آپدیت کن
        if (append) {
            $('#comments-current-page').val(page);
        }

        $.ajax({
            url: ProductJsObject.admin_ajax,
            type: 'POST',
            data: {
                action: 'v2_ajax_handler',
                nonce: ProductJsObject.nonce,
                callback: 'product_get_comments',
                product_id: productId,
                page: page,
                sort_type: sortType,
                product_type: productType
            },
            beforeSend: function () {
                if (!append) {
                    // برای sort: نمایش loading و مخفی کردن load more
                    $('#comments-list-container').html('<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#2B7FFF]"></div></div>');
                    $('#load-more-comments').hide();
                } else {
                    // برای load more: فقط متن دکمه رو عوض کن
                    $('#load-more-comments').find('p').text('در حال بارگذاری...');
                }
            },
            success: function (response) {
                // بررسی اینکه response از نوع HTML باشه نه JSON
                if (response.trim().startsWith('{') || response.trim().startsWith('[')) {
                    console.error('خطا: response به صورت JSON است');
                    alert('خطا در بارگذاری نظرات');
                    return;
                }

                if (append) {
                    // اضافه کردن به انتهای نظرات (load more)
                    $('#comments-list-container').append(response);
                    $('#load-more-comments').find('p').text('مشاهده بیشتر');
                    
                    // خواندن totalPages بعد از قرارگیری response در DOM
                    const updatedTotalPages = parseInt($('#comments-total-pages').val()) || 1;
                    
                    // اگر به آخرین صفحه رسیدیم، دکمه رو مخفی کن
                    if (page >= updatedTotalPages) {
                        $('#load-more-comments').hide();
                    } else {
                        $('#load-more-comments').show();
                    }
                } else {
                    // جایگزینی کامل نظرات (sort)
                    $('#comments-list-container').html(response);
                    $('#comments-current-page').val(1);

                    // خواندن totalPages بعد از قرارگیری response در DOM (برای اطمینان از مقدار به‌روز)
                    const updatedTotalPages = parseInt($('#comments-total-pages').val()) || 1;

                    // نمایش دکمه مشاهده بیشتر اگر صفحات بیشتر وجود داشته باشد
                    if (updatedTotalPages > 1) {
                        $('#load-more-comments').show();
                    } else {
                        $('#load-more-comments').hide();
                    }

                    // اسکرول به بخش نظرات
                    $('html, body').animate({
                        scrollTop: $('#comments-section').offset().top - 100
                    }, 500);
                }
            },
            error: function (xhr) {
                console.error('خطا در AJAX:', xhr.responseText);
                if (!append) {
                    $('#comments-list-container').html('<div class="text-center text-red-500 py-8">خطا در بارگذاری نظرات</div>');
                } else {
                    $('#load-more-comments').find('p').text('مشاهده بیشتر');
                    alert('خطا در بارگذاری نظرات');
                }
            }
        });
    };

    // Deprecated: برای سازگاری با کد قدیمی
    const GetProductComments = (page = 1, sort_type = 'newest') => {
        $('#comments-sort-type').val(sort_type);
        $('#comments-current-page').val(page);
        loadComments(false);
    }

    const GetProductCommentVote = (action, id) => {

        let temp = document.querySelector(`[data-comment-vote-id="${id}"]`).innerText.replaceAll(/\s/g, '')

        $.ajax({
            url: ProductJsObject.admin_ajax,
            type: 'POST',
            data: {
                'action': 'v2_ajax_handler',
                'nonce': ProductJsObject.nonce,
                'callback': 'product_add_comment_feedback',
                'comment_id': id,
                'type': action === 'like-comment' ? 'like' : 'dislike'
            }, beforeSend: function () {
                $(`[data-comment-vote-id="${id}"]`).text('...')
            },
            success: function (response) {

                Toast.fire({
                    icon: response.success ? 'success' : 'error',
                    title: response.data
                })

                if (!response.success) {
                    $(`[data-comment-vote-id="${id}"]`).text(temp)
                } else {
                    $(`[data-comment-vote-id="${id}"]`).text(response.data)
                }
            }
        })
    }

    const SelectDay = new Swiper('.select-day', {
        slidesPerView: 3,
        navigation: {
            nextEl: ".tomorrow",
            prevEl: ".yesterday",
        },
    })

    // Event handler برای دکمه‌های سورت
    $(document).on('click', '.sort-comments-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if ($(this).hasClass('active')) {
            return false;
        }

        const sortType = $(this).data('sort-type');

        // تغییر وضعیت active (حالا با ::after مدیریت می‌شود)
        $('.sort-comments-btn').removeClass('active');
        $(this).addClass('active');

        // ریست کردن صفحه و تنظیم sort type
        $('#comments-current-page').val(1);
        $('#comments-sort-type').val(sortType);

        // بارگذاری نظرات جدید
        loadComments(false);

        return false;
    });

    // Event handler برای دکمه مشاهده بیشتر
    $(document).on('click', '#load-more-comments', function(e) {
        e.preventDefault();

        const currentPage = parseInt($('#comments-current-page').val());
        const totalPages = parseInt($('#comments-total-pages').val());

        if (currentPage < totalPages) {
            // بارگذاری نظرات بیشتر (append mode)
            loadComments(true);
        }
    });

    // Toggle introduction text - برای نمایش/مخفی کردن متن کامل معرفی بازی
    $(document).on('click', '.toggle-introduction-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $textContainer = $btn.closest('.introduction-text-content');
        const $textDisplay = $textContainer.find('.introduction-text-display');
        const $toggleText = $btn.find('.toggle-text');
        const $chevron = $btn.find('.chevron-icon');
        const isExpanded = $btn.hasClass('expanded');
        
        if (!isExpanded) {
            // حالت باز (بیشتر)
            $btn.addClass('expanded');
            
            // محاسبه ارتفاع فعلی
            const currentHeight = $textDisplay.height();
            
            // حذف line-clamp برای نمایش کامل
            $textDisplay.removeClass('line-clamp-3');
            
            // محاسبه ارتفاع جدید
            const newHeight = $textDisplay.height();
            
            // انیمیشن
            $textDisplay.css({
                'height': currentHeight + 'px',
                'overflow': 'hidden',
                'transition': 'all 0.4s ease-in-out'
            });
            
            setTimeout(() => {
                $textDisplay.css({
                    'height': newHeight + 'px',
                    'opacity': '1'
                });
                setTimeout(() => {
                    $textDisplay.css({
                        'height': 'auto',
                        'overflow': 'visible',
                        'max-height': 'none'
                    });
                }, 400);
            }, 10);
            
            $toggleText.text('کمتر');
            $chevron.css('transform', 'rotate(180deg)');
        } else {
            // حالت بسته (کمتر)
            $btn.removeClass('expanded');
            
            // محاسبه ارتفاع فعلی
            const currentHeight = $textDisplay.height();
            
            // اضافه کردن line-clamp
            $textDisplay.addClass('line-clamp-3');
            
            // محاسبه ارتفاع جدید
            const newHeight = $textDisplay.height();
            
            // انیمیشن
            $textDisplay.css({
                'height': currentHeight + 'px',
                'overflow': 'hidden',
                'transition': 'all 0.4s ease-in-out'
            });
            
            setTimeout(() => {
                $textDisplay.css({
                    'height': newHeight + 'px',
                    'opacity': '1'
                });
                setTimeout(() => {
                    $textDisplay.css({
                        'height': 'auto',
                        'overflow': 'hidden',
                        'max-height': 'none'
                    });
                }, 400);
            }, 10);
            
            $toggleText.text('مشاهده بیشتر');
            $chevron.css('transform', 'rotate(0deg)');
        }
    });

    // بررسی و نمایش دکمه toggle برای معرفی بازی
    function checkIntroductionToggle() {
        const $introductionContent = $('.introduction-text-content');
        if ($introductionContent.length) {
            const $textDisplay = $introductionContent.find('.introduction-text-display');
            const $toggleBtn = $introductionContent.find('.toggle-introduction-btn');
            
            // اگر دکمه در حالت expanded است، بررسی نکن
            if ($toggleBtn.hasClass('expanded')) {
                return;
            }
            
            // محاسبه ارتفاع با line-clamp
            const heightWithClamp = $textDisplay.height();
            
            // حذف موقت line-clamp برای محاسبه ارتفاع کامل
            $textDisplay.removeClass('line-clamp-3');
            const fullHeight = $textDisplay.height();
            $textDisplay.addClass('line-clamp-3');
            
            // اگر ارتفاع کامل بیشتر از ارتفاع با clamp باشد، دکمه را نمایش بده
            if (fullHeight > heightWithClamp + 5) { // 5px tolerance
                $toggleBtn.removeClass('hidden');
            } else {
                $toggleBtn.addClass('hidden');
            }
        }
    }

    // اجرای بررسی بعد از لود صفحه
    $(document).ready(function() {
        setTimeout(checkIntroductionToggle, 100);
    });

    // اجرای بررسی بعد از تغییر اندازه صفحه
    $(window).on('resize', function() {
        setTimeout(checkIntroductionToggle, 100);
    });

    // Toggle scenario text - برای نمایش/مخفی کردن متن کامل سناریو
    $(document).on('click', '.toggle-scenario-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $textContainer = $btn.closest('.scenario-text-content');
        const $textDisplay = $textContainer.find('.scenario-text-display');
        const $toggleText = $btn.find('.toggle-text');
        const $chevron = $btn.find('.chevron-icon');
        const isExpanded = $btn.hasClass('expanded');
        
        if (!isExpanded) {
            // حالت باز (بیشتر)
            $btn.addClass('expanded');
            
            // محاسبه ارتفاع فعلی
            const currentHeight = $textDisplay.height();
            
            // حذف line-clamp برای نمایش کامل
            $textDisplay.removeClass('line-clamp-3');
            
            // محاسبه ارتفاع جدید
            const newHeight = $textDisplay.height();
            
            // انیمیشن
            $textDisplay.css({
                'height': currentHeight + 'px',
                'overflow': 'hidden',
                'transition': 'all 0.4s ease-in-out'
            });
            
            setTimeout(() => {
                $textDisplay.css({
                    'height': newHeight + 'px',
                    'opacity': '1'
                });
                setTimeout(() => {
                    $textDisplay.css({
                        'height': 'auto',
                        'overflow': 'visible',
                        'max-height': 'none'
                    });
                }, 400);
            }, 10);
            
            $toggleText.text('کمتر');
            $chevron.css('transform', 'rotate(180deg)');
        } else {
            // حالت بسته (کمتر)
            $btn.removeClass('expanded');
            
            // محاسبه ارتفاع فعلی
            const currentHeight = $textDisplay.height();
            
            // اضافه کردن line-clamp
            $textDisplay.addClass('line-clamp-3');
            
            // محاسبه ارتفاع جدید
            const newHeight = $textDisplay.height();
            
            // انیمیشن
            $textDisplay.css({
                'height': currentHeight + 'px',
                'overflow': 'hidden',
                'transition': 'all 0.4s ease-in-out'
            });
            
            setTimeout(() => {
                $textDisplay.css({
                    'height': newHeight + 'px',
                    'opacity': '1'
                });
                setTimeout(() => {
                    $textDisplay.css({
                        'height': 'auto',
                        'overflow': 'hidden',
                        'max-height': 'none'
                    });
                }, 400);
            }, 10);
            
            $toggleText.text('مشاهده بیشتر');
            $chevron.css('transform', 'rotate(0deg)');
        }
    });

    // بررسی و نمایش دکمه toggle برای سناریو
    function checkScenarioToggle() {
        const $scenarioContent = $('.scenario-text-content');
        if ($scenarioContent.length) {
            const $textDisplay = $scenarioContent.find('.scenario-text-display');
            const $toggleBtn = $scenarioContent.find('.toggle-scenario-btn');
            
            // اگر دکمه در حالت expanded است، بررسی نکن
            if ($toggleBtn.hasClass('expanded')) {
                return;
            }
            
            // محاسبه ارتفاع با line-clamp
            const heightWithClamp = $textDisplay.height();
            
            // حذف موقت line-clamp برای محاسبه ارتفاع کامل
            $textDisplay.removeClass('line-clamp-3');
            const fullHeight = $textDisplay.height();
            $textDisplay.addClass('line-clamp-3');
            
            // اگر ارتفاع کامل بیشتر از ارتفاع با clamp باشد، دکمه را نمایش بده
            if (fullHeight > heightWithClamp + 5) { // 5px tolerance
                $toggleBtn.removeClass('hidden');
            } else {
                $toggleBtn.addClass('hidden');
            }
        }
    }

    // اجرای بررسی بعد از لود صفحه
    $(document).ready(function() {
        setTimeout(function() {
            checkIntroductionToggle();
            checkScenarioToggle();
        }, 100);
    });

    // اجرای بررسی بعد از تغییر اندازه صفحه
    $(window).on('resize', function() {
        setTimeout(function() {
            checkIntroductionToggle();
            checkScenarioToggle();
        }, 100);
    });

    // Toggle rules text - برای نمایش/مخفی کردن متن کامل قوانین
    $(document).on('click', '.toggle-rules-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $textContainer = $btn.closest('.rules-text-content');
        const $textDisplay = $textContainer.find('.rules-text-display');
        const $toggleText = $btn.find('.toggle-text');
        const $chevron = $btn.find('.chevron-icon');
        const isExpanded = $btn.hasClass('expanded');
        
        if (!isExpanded) {
            // حالت باز (بیشتر)
            $btn.addClass('expanded');
            
            // محاسبه ارتفاع فعلی
            const currentHeight = $textDisplay.height();
            
            // حذف line-clamp برای نمایش کامل
            $textDisplay.removeClass('line-clamp-3');
            
            // محاسبه ارتفاع جدید
            const newHeight = $textDisplay.height();
            
            // انیمیشن
            $textDisplay.css({
                'height': currentHeight + 'px',
                'overflow': 'hidden',
                'transition': 'all 0.4s ease-in-out'
            });
            
            setTimeout(() => {
                $textDisplay.css({
                    'height': newHeight + 'px',
                    'opacity': '1'
                });
                setTimeout(() => {
                    $textDisplay.css({
                        'height': 'auto',
                        'overflow': 'visible',
                        'max-height': 'none'
                    });
                }, 400);
            }, 10);
            
            $toggleText.text('کمتر');
            $chevron.css('transform', 'rotate(180deg)');
        } else {
            // حالت بسته (کمتر)
            $btn.removeClass('expanded');
            
            // محاسبه ارتفاع فعلی
            const currentHeight = $textDisplay.height();
            
            // اضافه کردن line-clamp
            $textDisplay.addClass('line-clamp-3');
            
            // محاسبه ارتفاع جدید
            const newHeight = $textDisplay.height();
            
            // انیمیشن
            $textDisplay.css({
                'height': currentHeight + 'px',
                'overflow': 'hidden',
                'transition': 'all 0.4s ease-in-out'
            });
            
            setTimeout(() => {
                $textDisplay.css({
                    'height': newHeight + 'px',
                    'opacity': '1'
                });
                setTimeout(() => {
                    $textDisplay.css({
                        'height': 'auto',
                        'overflow': 'hidden',
                        'max-height': 'none'
                    });
                }, 400);
            }, 10);
            
            $toggleText.text('مشاهده بیشتر');
            $chevron.css('transform', 'rotate(0deg)');
        }
    });

    // بررسی و نمایش دکمه toggle برای قوانین
    function checkRulesToggle() {
        const $rulesContent = $('.rules-text-content');
        if ($rulesContent.length) {
            const $textDisplay = $rulesContent.find('.rules-text-display');
            const $toggleBtn = $rulesContent.find('.toggle-rules-btn');
            
            // اگر دکمه در حالت expanded است، بررسی نکن
            if ($toggleBtn.hasClass('expanded')) {
                return;
            }
            
            // محاسبه ارتفاع با line-clamp
            const heightWithClamp = $textDisplay.height();
            
            // حذف موقت line-clamp برای محاسبه ارتفاع کامل
            $textDisplay.removeClass('line-clamp-3');
            const fullHeight = $textDisplay.height();
            $textDisplay.addClass('line-clamp-3');
            
            // اگر ارتفاع کامل بیشتر از ارتفاع با clamp باشد، دکمه را نمایش بده
            if (fullHeight > heightWithClamp + 5) { // 5px tolerance
                $toggleBtn.removeClass('hidden');
            } else {
                $toggleBtn.addClass('hidden');
            }
        }
    }

    // اجرای بررسی بعد از لود صفحه
    $(document).ready(function() {
        setTimeout(function() {
            checkIntroductionToggle();
            checkScenarioToggle();
            checkRulesToggle();
        }, 100);
    });

    // اجرای بررسی بعد از تغییر اندازه صفحه
    $(window).on('resize', function() {
        setTimeout(function() {
            checkIntroductionToggle();
            checkScenarioToggle();
            checkRulesToggle();
        }, 100);
    });

    // Toggle comment text - برای نمایش/مخفی کردن متن کامل کامنت با منطق هوشمند
    $(document).on('click', '.toggle-comment-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $textContainer = $btn.closest('.comment-text-content');
        const $textDisplay = $textContainer.find('.comment-text-display');
        const $toggleText = $btn.find('.toggle-text');
        const $chevron = $btn.find('.chevron-icon');
        const $commentItem = $btn.closest('.comment-item');
        const $scoresSection = $commentItem.find('.scores-section');
        
        const fullText = $textContainer.data('full-text');
        const showLength = parseInt($textContainer.data('show-length')) || 0;
        const hasLongText = $textContainer.data('has-long-text');
        const isExpanded = $btn.hasClass('expanded');
        
        // تشخیص desktop یا mobile
        const isMobile = $textContainer.hasClass('lg:hidden');
        
        if (!isExpanded) {
            // حالت باز (بیشتر)
            $btn.addClass('expanded');
            
            // همیشه متن کامل رو نشون بده (اگر متن بلند باشه) با انیمیشن smooth
            if (hasLongText === 'true' || hasLongText === true) {
                // محاسبه ارتفاع فعلی قبل از تغییر
                const currentHeight = $textDisplay.height();
                
                // تغییر متن به صورت موقت برای محاسبه ارتفاع جدید
                const originalText = $textDisplay.text();
                $textDisplay.css('opacity', '0');
                $textDisplay.text(fullText);
                const newHeight = $textDisplay.height();
                
                // برگرداندن متن قبلی
                $textDisplay.text(originalText);
                $textDisplay.css('opacity', '1');
                
                // انیمیشن با jQuery animate برای smooth transition
                $textDisplay.css({
                    'height': currentHeight + 'px',
                    'overflow': 'hidden',
                    'transition': 'all 0.4s ease-in-out'
                });
                
                setTimeout(() => {
                    $textDisplay.css('opacity', '0');
                    setTimeout(() => {
                        $textDisplay.text(fullText);
                        $textDisplay.css({
                            'height': newHeight + 'px',
                            'opacity': '1'
                        });
                        setTimeout(() => {
                            $textDisplay.css({
                                'height': 'auto',
                                'overflow': 'visible'
                            });
                        }, 400);
                    }, 200);
                }, 10);
            }
            
            $toggleText.text('کمتر');
            $chevron.css('transform', 'rotate(180deg)');
            
            // در mobile: نمایش scores section با انیمیشن
            if (isMobile && $scoresSection.length > 0) {
                const delay = (hasLongText === 'true' || hasLongText === true) ? 600 : 0;
                setTimeout(() => {
                    $scoresSection.removeClass('max-h-0 opacity-0')
                                 .addClass('max-h-[1000px] opacity-100 mt-4');
                }, delay);
            }
        } else {
            // حالت بسته (کمتر)
            $btn.removeClass('expanded');
            
            // فقط در صورتی که متن بلند باشه، متن رو کوتاه کن با انیمیشن
            if (hasLongText === 'true' || hasLongText === true) {
                const shortText = fullText.substring(0, showLength) + '...';
                
                // محاسبه ارتفاع فعلی
                const currentHeight = $textDisplay.height();
                
                // تغییر متن موقت برای محاسبه ارتفاع کوتاه
                const originalText = $textDisplay.text();
                $textDisplay.css('opacity', '0');
                $textDisplay.text(shortText);
                const newHeight = $textDisplay.height();
                
                // برگرداندن متن قبلی
                $textDisplay.text(originalText);
                $textDisplay.css('opacity', '1');
                
                // انیمیشن
                $textDisplay.css({
                    'height': currentHeight + 'px',
                    'overflow': 'hidden',
                    'transition': 'all 0.4s ease-in-out'
                });
                
                setTimeout(() => {
                    $textDisplay.css('opacity', '0');
                    setTimeout(() => {
                        $textDisplay.text(shortText);
                        $textDisplay.css({
                            'height': newHeight + 'px',
                            'opacity': '1'
                        });
                        setTimeout(() => {
                            $textDisplay.css({
                                'height': 'auto',
                                'overflow': 'visible'
                            });
                        }, 400);
                    }, 200);
                }, 10);
            }
            
            $toggleText.text('بیشتر');
            $chevron.css('transform', 'rotate(0deg)');
            
            // در mobile: مخفی کردن scores section با انیمیشن
            if (isMobile && $scoresSection.length > 0) {
                $scoresSection.removeClass('max-h-[1000px] opacity-100 mt-4')
                             .addClass('max-h-0 opacity-0');
            }
        }
    });

    $("body")
        .on('click', '[data-action]', function () {
            let _ = $(this)
            let action = _.data('action')
            let id = _.data('id')
            let sort_type = _.data('sort-type')

            if (action === 'like-comment' || action === 'dislike-comment') {
                GetProductCommentVote(action, id);
            }

            if (action === 'get-comments') {
                GetProductComments(1, sort_type)
            }

            if (action === 'increase-members') {
                let num = parseInt(_.next().find('span').text().trim())
                num = num + 1
                if (num <= parseInt($("#product-sans-list").data('max'))) {
                    _.next().find('span').text(num)
                }

                if (num === parseInt($("#product-sans-list").data('max'))) {
                    _.attr('disabled', 'disabled')
                } else {
                    _.removeAttr('disabled')
                }

                $("[data-action='decrease-members']").removeAttr('disabled')

                $("#go-to-checkout").attr('href', function () {
                    let href = $(this).attr('href')
                    let pattern = /(quantity=)\d+/;
                    return href.replace(pattern, `$1${num}`)
                })
            }

            if (action === 'decrease-members') {
                let num = parseInt(_.prev().find('span').text().trim())
                num = num - 1

                if (num >= parseInt($("#product-sans-list").data('min'))) {
                    _.prev().find('span').text(num)
                    _.removeAttr('disabled')
                }

                if (num === parseInt($("#product-sans-list").data('min'))) {
                    _.attr('disabled', 'disabled')
                } else {
                    _.removeAttr('disabled')
                }

                $("[data-action='increase-members']").removeAttr('disabled')

                $("#go-to-checkout").attr('href', function () {
                    let href = $(this).attr('href')
                    let pattern = /(quantity=)\d+/;
                    return href.replace(pattern, `$1${num}`)
                })
            }
        })
        .on('click', "[data-rating-item]", function () {
            let rating_item = $(this).data('rating-item'),
                rate = $(this).data('rate')

            let colors = {
                1: '#E76262',
                2: '#DBAE41',
                3: '#9FC537',
                4: '#77BE39',
                5: '#02C96F',
            }

            $(`[data-rating-item='${rating_item}']`)
                .removeClass('active').removeAttr('style')

            $(`[data-rating-item='${rating_item}'][data-rate='${rate}']`)
                .addClass('active')
                .css({
                    'background': colors[rate / 20],
                    'color': '#FFFFFF'
                })
        })
        .on('submit', ".send-comment", function (e) {
            e.preventDefault()

            let _ = $(this)
            let rate = [];
            _.find(`[data-rating-item].active`).each((index, item) => {
                let i = $(item).data('rating-item')
                rate[i] = $(item).data('rate')
            })

            rate = Object.assign({}, rate);

            let content = _.find('#content')
            let reviewCommentId = parseInt(_.find('#review_comment_id').val(), 10) || 0
            let isEdit = typeof ProductJsObject !== 'undefined' && ProductJsObject.can_edit_review && reviewCommentId > 0
            let submitLabel = isEdit ? 'ذخیره تغییرات' : 'ثبت نظر'

            let ajaxData = {
                'action': 'v2_ajax_handler',
                'nonce': ProductJsObject.nonce,
                'callback': isEdit ? 'product_edit_comment' : 'product_add_comment',
                'content': content.val(),
                'rate': rate
            }
            if (isEdit) {
                ajaxData.comment_id = reviewCommentId
            } else {
                ajaxData.product_id = ProductJsObject.product_id
            }

            $.ajax({
                url: ProductJsObject.admin_ajax,
                type: 'POST',
                data: ajaxData,
                beforeSend: function () {
                    _.find('button[type="submit"]')
                        .attr('disabled', 'disabled')
                        .html('<div class="spinner mx-auto" style="border: 5px solid #FFF;border-top-color: transparent;width: 28px;height: 28px;border-radius: 50%;animation: spin 1s linear infinite;"></div>')
                },
                success: function (response) {
                    // Close modal first
                    closeCommentModal();
                    
                    // Then show message and reload
                    setTimeout(() => {
                        let msg = ezProductReviewAjaxMessage(response.data)
                        let errCode = (response.data && typeof response.data === 'object') ? response.data.code : ''

                        if (!response.success && errCode === 'already_reviewed' && ProductJsObject.my_reviews_url) {
                            msg = msg + '\n' + ProductJsObject.my_reviews_url
                        }

                        Toast.fire({
                            icon: response.success ? 'success' : 'error',
                            title: msg
                        })

                        if (response.success) {
                            let currentPage = window.location.href;
                            zebline.event.track("send_comment", {
                                'product_id': ProductJsObject.product_id,
                                'rate': rate,
                                "current_page": currentPage,
                            });

                            // Reload after 1.5 seconds
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            _.find('button[type="submit"]')
                                .removeAttr('disabled')
                                .html(submitLabel);
                        }
                    }, 400);
                },
                error: function() {
                    _.find('button[type="submit"]')
                        .removeAttr('disabled')
                        .html(submitLabel);
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'خطا در ارسال نظر'
                    });
                }
            })
        })
        // Deprecated: کد قدیمی sans-item (حذف شد)
        .on('click', "[data-reserve-timestamp]", function () {

            $("[data-reserve-timestamp]").removeClass('active border-blue bg-blue text-white').addClass('border-gray-120')

            $(this).removeClass('border-gray-120').addClass('active border-blue bg-blue text-white')



            let ariaLabel = $(this).parent().parent().attr('aria-label');
            let index = ariaLabel ? ariaLabel.split(' / ')[0] - 1 : $(this).parent().index()



            SelectDay.slideTo(index)

            setTimeout(() => {
                let date = $(this).data('reserve-timestamp')
                BuildSans(ProductJsObject.product_id, date)
            }, 1)

        })
        // Deprecated: کد قدیمی show-more-sans (حذف شد)
        .on('click', ".pagination a", function (e) {
            e.preventDefault()

            const page = $(this).attr('href').split('?comment_page=')[1]
            const sort_type = $("button[data-action='get-comments'].active").data('sort-type')

            GetProductComments(page, sort_type)
        })
        .on('click', '.comment-content', function () {
            let attr = $(this).attr('data-continue')
            if (typeof attr !== 'undefined' && attr !== false) {
                $(this).find('span').remove()
                let text2 = $(this).data('continue')

                $(this).removeAttr('data-continue')

                $(this).text(text2)
            }
        })
        .on('click', '.product-navigator a', function (e) {
            e.preventDefault()
            let target = $(this).attr('href')
            $("html, body").animate({scrollTop: $(target).offset().top - 80}, 1000);
        })
        .on('click', '.reply-button', function () {
            let _ = $(this)

            let form = _.data('target-form')

            _.addClass('hidden')
            $(`.close-reply-button[data-target-form=${form}]`).removeClass('hidden')
            $(`[data-form="${form}"]`).slideDown()
        })
        .on('click', '.close-reply-button', function () {
            let _ = $(this)

            let form = _.data('target-form')

            _.addClass('hidden')
            $(`.reply-button[data-target-form=${form}]`).removeClass('hidden')
            $(`[data-form="${form}"]`).slideUp()
        })
        .on('submit', '.submit-reply-form', function (e) {
            e.preventDefault()

            let _ = $(this)
            let data = {
                'action': 'v2_ajax_handler',
                'nonce': ProductJsObject.nonce,
                'callback': 'product_comments_reply_add',
            };

            $.each(_.serializeArray(), function (i, field) {
                data[field.name] = field.value;
            });

            $.ajax({
                url: ProductJsObject.admin_ajax,
                type: 'POST',
                data: data,
                beforeSend: function () {
                    _.find('button[type="submit"]').attr('disabled', 'disabled').html('<div class="spinner" style="width: 17px;border-color: #FFF;border-width: 2px;"></div>')
                },
                success: function (response) {
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.data.message
                    })

                    if (response.success) {
                        _.parent().prev().remove()
                        _.parent().html(response.data.reply)
                    }
                }
            })
        })
        .on('click', '.read-more-details-button', function () {
            $(this).prev().slideToggle()
            $(this).find('span').text($(this).find('span').text() === 'مشاهده بیشتر' ? 'مشاهده کمتر' : 'مشاهده بیشتر')
        })
        .on('click', '.close-notice', function () {
            $(this).parents('.notice').slideUp()
        })

    $("[data-rate='100']").addClass('active').css({'background': '#02C96F', 'color': '#FFFFFF'})

    const showSansLoading = () => {
        $('.sessions-embla-container-desktop').empty();
        let loadingHTML = '';
        for (let i = 0; i < 4; i++) {
            loadingHTML += '<div class="skeleton h-12 w-full rounded-[10px] mb-2.5"></div>';
        }
        $("#sessions-list-desktop").html(loadingHTML);
        $("#sessions-list-mobile").html(loadingHTML);
        $("#sessions-info-desktop").html('<h2 class="text-xs text-blue">در حال بارگذاری...</h2>');
        $("#sessions-info-mobile").html('<h2 class="text-xs text-blue">در حال بارگذاری...</h2>');
        $("#toggle-sessions-desktop").hide();
        $("#toggle-sessions-mobile").hide();
    };

    const applySansList = (res) => {
        if (!Array.isArray(res)) {
            res = [];
        }
        let reservable_count = 0;
        res.forEach((item) => {
            if (item.status === 'reservable') {
                reservable_count += 1;
            }
        });
        if (reservable_count > 0) {
            const infoHTML = `<h2 class="text-xs text-blue">${reservable_count} سانس قابل رزرو</h2>`;
            $("#sessions-info-desktop").html(infoHTML);
            $("#sessions-info-mobile").html(infoHTML);
            renderSessions(res, 'desktop');
            renderSessions(res, 'mobile');
        } else {
            const emptyHTML = `<div class='w-full aspect-square bg-slate-100 shadow-13 rounded-2xl flex flex-col text-center items-center justify-center text-slate-350 leading-5 text-lg'>
                    <p>در این روز سانس خالی نداریم!<br> روز دیگه ای انتخاب کن.</p></div>`;
            $('.embla__container-sessions').html(emptyHTML);
            $('.sessions-embla-container-desktop').html(emptyHTML);
            $("#sessions-info-desktop").empty();
            $("#sessions-info-mobile").empty();
        }
    };

    const legacyBuildSansAjax = (room, day) => {
        $.ajax({
            type: 'POST',
            url: ProductJsObject.reservation_ajax,
            data: {
                "type": "get_sanses",
                "data": {
                    "day_start_time": day,
                    "product_id": room
                }
            },
            success: function (response) {
                applySansList(JSON.parse(response));
            }
        });
    };

    /**
     * Build sessions for both desktop and mobile
     * @param {number} room - Product ID
     * @param {number} day - Day timestamp
     */
    const BuildSans = (room, day) => {
        showSansLoading();

        if (window.__EZ_BOOT__?.sub_secret && window.ezBookingApi?.sansDayJson) {
            window.ezBookingApi.sansDayJson(room, day)
                .then(applySansList)
                .catch(function () {
                    legacyBuildSansAjax(room, day);
                });
            return;
        }

        legacyBuildSansAjax(room, day);
    };

    /**
     * Render sessions HTML - همه سانس‌ها با scroll
     * @param {Array} sessions - لیست سانس‌ها
     * @param {string} device - 'desktop' یا 'mobile'
     * @param {number} page - شماره صفحه (deprecated - دیگه استفاده نمیشه)
     */
    const renderSessions = (sessions, device, page = 1) => {
        const isDesktop = device === 'desktop';
        const storageTarget = isDesktop ? $('.sessions-embla-container-desktop') : $('#sessions-list-mobile');
        
        // فیلتر کردن سانس‌های قابل رزرو
        const reservableSessions = sessions.filter(item => item.status === 'reservable');
        
        // ذخیره اطلاعات در container
        storageTarget.data('all-sessions', JSON.stringify(reservableSessions));
        
        // ساخت HTML سانس‌ها - همه سانس‌ها
        let out = '';
        reservableSessions.forEach((item) => {
            const timeLabel = ezTehranFmt(item.time, 'HH:mm');
            const [hours, minutes] = timeLabel.split(':');
            const hourNum = ezTehranFmt(item.time, 'hour');

            let BackgroundColor = '#5091FB';
            let is_vip = false;

            // Check VIP time (Tehran)
            if (hourNum >= 0 && hourNum < 8) {
                is_vip = true;
                BackgroundColor = "#BF9A00";
            }

            // Build price text
            let Text = '';
            if (item.off_price > 0) {
                Text = `<div class="flex items-center gap-1">
                    <span class="text-[#5091FB] text-lg font-bold">${new Intl.NumberFormat("fa-IR").format(item.off_price)}</span>
                    <span class="text-xs text-[#889BAD] line-through-orange">${new Intl.NumberFormat("fa-IR").format(item.price)} تومان</span>
                </div>`;
            } else {
                Text = `<span>${new Intl.NumberFormat("fa-IR").format(item.price)} <span class="mr-1.5 text-2xs opacity-70">تومان</span></span>`;
            }

            // Session box HTML
            out += `
                <div class="session-item" data-session-time="${item.time}" data-session-price="${item.off_price || item.price}" data-session-formatted="${hours}:${minutes}" data-is-vip="${is_vip}">
                    <div class="relative mb-2.5 flex cursor-pointer items-center justify-between rounded-[10px] transition-all overflow-hidden" style="border: 1px solid ${BackgroundColor}; color: ${BackgroundColor}">
                        <span class="text-right text-2xl px-4 py-3">
                            ${hours}:${minutes}
                             ${is_vip ? "<span class='mr-2 text-lg'>بامداد VIP</span>" : ""}
                        </span>
                        <span class="text-lg px-4 py-3 absolute left-0 top-0 flex items-center justify-center h-full flex-col leading-4">
                            ${Text}
                        </span>
                    </div>
                </div>
            `;
        });

        if (!out) {
            out = `
                <div class='w-full aspect-square bg-slate-100 shadow-13 rounded-2xl flex flex-col text-center items-center justify-center text-slate-350 leading-5 text-lg'>
                    <p>در این روز در این روز سانس خالی نداریم، روز دیگه ای انتخاب کن! <br> روز دیگری انتخاب کن.</p>
                </div>`;
        }

        if (isDesktop) {
            const $root = $('.sessions-embla-container-desktop');
            const minPlayers = parseInt($root.data('min')) || 0;
            const maxPlayers = parseInt($root.data('max')) || 0;

            const desktopMarkup = `
                <button type="button" aria-label="سانس‌های قبلی" class="session-scroll-btn session-scroll-btn--prev mb-3">
                    <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 10.5L8 5.5L13 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="relative">
                    <div class="absolute top-0 left-0 right-0 h-8 bg-gradient-to-b from-white to-transparent.pointer-events-none z-10"></div>
                    <div class="embla-sessions-desktop max-h-[270px] pt-2.5 pb-8">
                        <div id="sessions-list-desktop" class="embla__container-sessions time-boxes" data-min="${minPlayers}" data-max="${maxPlayers}" data-current-page="1" data-total-pages="1">
                            ${out}
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white to-transparent.pointer-events-none z-10"></div>
                </div>
                <button type="button" aria-label="سانس‌های بعدی" class="session-scroll-btn session-scroll-btn--next mt-3">
                    <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 5.5L8 10.5L13 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            `;
            $root.html(desktopMarkup);
            $root.find('#sessions-list-desktop').data('all-sessions', storageTarget.data('all-sessions'));
        } else {
            $('#sessions-list-mobile').html(out);
        }

        // Initialize Embla Carousel بعد از رندر سانس‌ها
        initSessionsEmbla(device);
    };
    
    // متغیرهای Embla برای سانس‌ها
    let emblaSessionsDesktop = null;
    let emblaSessionsMobile = null;

    function toggleSessionScrollButtons(show) {
        const $btns = $('.sessions-embla-container-desktop').find('.session-scroll-btn--prev, .session-scroll-btn--next');
        if (show) {
            $btns.removeClass('session-scroll-btn--hidden');
        } else {
            $btns.addClass('session-scroll-btn--hidden');
        }
    }

    function updateDesktopScrollButtons() {
        if (!emblaSessionsDesktop) {
            toggleSessionScrollButtons(false);
            return;
        }
        const shouldShow = emblaSessionsDesktop.canScrollPrev() || emblaSessionsDesktop.canScrollNext();
        toggleSessionScrollButtons(shouldShow);
    }

    /**
     * Initialize Embla Carousel for sessions - دقیقا مثل روزها ولی عمودی
     * @param {string} device - 'desktop' or 'mobile'
     */
    function initSessionsEmbla(device) {
        if (typeof EmblaCarousel === 'undefined') {
            console.warn('EmblaCarousel is not loaded');
            return;
        }
        
        const emblaNode = device === 'desktop' 
            ? document.querySelector('.embla-sessions-desktop')
            : document.querySelector('.embla-sessions-mobile');
        
        if (!emblaNode) return;
        
        // Destroy قبلی اگر وجود داره
        if (device === 'desktop' && emblaSessionsDesktop) {
            try {
                emblaSessionsDesktop.destroy();
            } catch (e) {
                console.error('Error destroying desktop sessions embla:', e);
            }
        } else if (device === 'mobile' && emblaSessionsMobile) {
            try {
                emblaSessionsMobile.destroy();
            } catch (e) {
                console.error('Error destroying mobile sessions embla:', e);
            }
        }
        
        // تنظیمات Embla - عمودی با scroll کاملا آزاد
        const options = {
            axis: 'y',
            dragFree: true,
            containScroll: false,
            align: 'start',
            inViewThreshold: 0
        };
        
        try {
            const embla = EmblaCarousel(emblaNode, options);
            
            // ذخیره instance
            if (device === 'desktop') {
                emblaSessionsDesktop = embla;
                updateDesktopScrollButtons();
                embla.on('select', updateDesktopScrollButtons);
                embla.on('resize', updateDesktopScrollButtons);
                embla.on('reInit', updateDesktopScrollButtons);
            } else {
                emblaSessionsMobile = embla;
            }
            
            // جلوگیری از کلیک در حین drag
            let isDragging = false;
            
            embla.on('pointerDown', () => {
                isDragging = false;
            });
            
            embla.on('pointerMove', () => {
                isDragging = true;
            });
            
            embla.on('pointerUp', () => {
                setTimeout(() => {
                    isDragging = false;
                }, 50);
            });
            
            // ذخیره drag state
            $(emblaNode).data('isDragging', () => isDragging);
            
        } catch (e) {
            console.error('Error initializing sessions embla:', e);
        }
    }

    // Desktop sessions navigation buttons
    $(document).on('click', '.session-scroll-btn--prev', function() {
        if (emblaSessionsDesktop) {
            emblaSessionsDesktop.scrollPrev();
        }
    });

    $(document).on('click', '.session-scroll-btn--next', function() {
        if (emblaSessionsDesktop) {
            emblaSessionsDesktop.scrollNext();
        }
    });

    // تابع کمکی برای چک کردن و آپدیت وضعیت دکمه‌های + و -
    function updateButtonStates($quantityBox, current, minPlayers, maxPlayers) {
        const $btnIncrease = $quantityBox.find('.btn-increase');
        const $btnDecrease = $quantityBox.find('.btn-decrease');
        
        // چک کردن دکمه -
        if (current <= minPlayers) {
            $btnDecrease.prop('disabled', true).css({
                'background-color': '#E2E8F0',
                'cursor': 'not-allowed'
            });
        } else {
            $btnDecrease.prop('disabled', false).css({
                'background-color': '#94A3B8',
                'cursor': 'pointer'
            });
        }
        
        // چک کردن دکمه +
        if (current >= maxPlayers) {
            $btnIncrease.prop('disabled', true).css({
                'background-color': '#E2E8F0',
                'cursor': 'not-allowed'
            });
        } else {
            $btnIncrease.prop('disabled', false).css({
                'background-color': '',
                'cursor': 'pointer'
            });
        }
    }

    // Event handler برای کلیک روی سانس
    $(document).on('click', '.session-item', function(e) {
        // چک کردن اینکه آیا در حال drag بوده یا نه (Embla state)
        const $emblaNode = $(this).closest('.embla-sessions-desktop, .embla-sessions-mobile');
        const isDraggingFunc = $emblaNode.data('isDragging');
        if (isDraggingFunc && isDraggingFunc()) {
            e.preventDefault();
            return false;
        }
        
        const $clickedSession = $(this);
        const sessionTime = $clickedSession.data('session-time');
        const sessionPrice = $clickedSession.data('session-price');
        const sessionFormatted = $clickedSession.data('session-formatted');
        const isVip = $clickedSession.data('is-vip');
        const container = $clickedSession.closest('[data-min]');
        const minPlayers = parseInt(container.data('min'));
        const maxPlayers = parseInt(container.data('max'));
        const device = container.attr('id').includes('desktop') ? 'desktop' : 'mobile';
        
        // مخفی کردن تقویم و session info
        const dateContainer = device === 'desktop' ? '.date-scroll-container-desktop' : '.date-scroll-container-mobile';
        const sessionInfo = device === 'desktop' ? '#sessions-info-desktop' : '#sessions-info-mobile';
        $(dateContainer).hide();
        $(sessionInfo).hide();
        
        // مخفی کردن Embla container (با fade effects)
        const emblaContainer = device === 'desktop' ? '.sessions-embla-container-desktop' : '.sessions-embla-container-mobile';
        $(emblaContainer).hide();
        if (device === 'desktop') {
            toggleSessionScrollButtons(false);
            $('.session-scroll-btn--prev, .session-scroll-btn--next').addClass('session-scroll-btn--hidden');
        }
        
        // نمایش باکس quantity selector با اطلاعات سانس
        const quantityBoxId = device === 'desktop' ? '#quantity-box-desktop' : '#quantity-box-mobile';
        const initialQuantity = minPlayers; // کمترین مقدار مجاز برای رزرو
        
        const quantityHTML = `
            <!-- Header با دکمه بازگشت -->
            <div class="flex items-center justify-between mb-6">
                <span class="text-base font-bold text-[#64748B]">انتخاب شما</span>
                <button type="button" class="change-session-btn bg-[#F1F5F9] w-10 h-10 rounded-lg flex items-center justify-center">
                    <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.5 1L1.5 6L6.5 11" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <hr class="my-4 border-t-2 border-[#E2E8F0]">      
            <!-- تاریخ و ساعت -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-1 text-base font-extrabold text-[#0F172A]">
                    <span>${ezTehranFmt(sessionTime, 'dddd')}</span>
                    <span class="text-[#FD7013]">${ezTehranFmt(sessionTime, 'D')}</span>
                    <span>${ezTehranFmt(sessionTime, 'MMMM')}</span>
                </div>
                <span class="text-2xl font-bold text-[#0F172A]">${sessionFormatted}</span>
            </div>
            
            <!-- انتخاب تعداد بلیت -->
            <div class="quantity-selector relative mb-6 flex items-center justify-between rounded-xl border-2 px-4 py-2 transition-all text-black border-[#02C96F]">
                <button type="button" class="btn-increase bg-[#02C96F] w-8 h-8 rounded-lg flex items-center justify-center disabled:!bg-[#CBD5E1]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 1V15M1 8H15" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <span class="font-bold text-xl text-[#0F172A] flex items-baseline gap-2">
                    <span class="players-count text-3xl">${initialQuantity}</span>
                    <span>بلیت</span>
                </span>
                <button type="button" class="btn-decrease bg-[#64748B] w-8 h-8 rounded-lg flex items-center justify-center disabled:!bg-[#CBD5E1]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="2" viewBox="0 0 16 2" fill="none">
                        <path d="M1 1H15" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>   
            </div>
            
            <!-- دکمه پرداخت -->
            <a id="quantity-checkout-btn" href="" class="flex items-center justify-center gap-2 bg-[#02C96F] text-white text-lg font-bold py-4 rounded-xl w-full">
                <span>پرداخت و ثبت رزرو</span>
                <svg class="mx-0" width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.402124 5.33532C0.144631 5.58141 7.88412e-08 5.91501 7.46935e-08 6.26282C7.05459e-08 6.61063 0.144631 6.94422 0.402124 7.19032L5.58679 12.1419C5.84474 12.3882 6.19458 12.5265 6.55937 12.5265C6.92416 12.5265 7.27401 12.3882 7.53196 12.1419C7.7899 11.8957 7.93481 11.5618 7.93481 11.2136C7.93481 10.8654 7.7899 10.5314 7.53196 10.2852L4.69396 7.57532L13.801 7.57532C14.1657 7.57532 14.5154 7.43704 14.7733 7.1909C15.0312 6.94476 15.176 6.61092 15.176 6.26282C15.176 5.91472 15.0312 5.58088 14.7733 5.33474C14.5154 5.0886 14.1657 4.95032 13.801 4.95032L4.69396 4.95032L7.53196 2.24132C7.65968 2.1194 7.76099 1.97467 7.83011 1.81538C7.89924 1.65609 7.93481 1.48536 7.93481 1.31294C7.93481 1.14053 7.89924 0.969802 7.83011 0.810511C7.76099 0.65122 7.65968 0.506484 7.53196 0.384568C7.40424 0.262652 7.25261 0.165945 7.08573 0.0999652C6.91886 0.0339843 6.74 2.39222e-05 6.55937 2.39201e-05C6.37875 2.39179e-05 6.19989 0.0339843 6.03302 0.0999652C5.86614 0.165945 5.71451 0.262652 5.58679 0.384568L0.402124 5.33532Z" fill="white"/>
                </svg>
            </a>
        `;
        
        $(quantityBoxId).html(quantityHTML).show();
        
        // ذخیره sessionTime برای استفاده در دکمه‌های + و -
        $(quantityBoxId).data('session-time', sessionTime);
        $(quantityBoxId).data('session-price', sessionPrice);
        $(quantityBoxId).data('session-formatted', sessionFormatted);
        $(quantityBoxId).data('min-players', minPlayers);
        $(quantityBoxId).data('max-players', maxPlayers);
        
        // آپدیت وضعیت دکمه‌ها برای مقدار اولیه
        updateButtonStates($(quantityBoxId), initialQuantity, minPlayers, maxPlayers);
        
        // ساخت URL checkout
        function buildCheckoutUrl(qty) {
            let checkoutUrl;
            if (window.location.hostname === "localhost") {
                checkoutUrl = "http://localhost/escapezoom_wp/checkout";
            } else {
                checkoutUrl = window.location.origin + "/checkout";
            }
            return `${checkoutUrl}/?add-to-cart=${ProductJsObject.product_id}&book=${sessionTime}&quantity=${qty}`;
        }
        
        // ست کردن لینک دکمه پرداخت
        $(quantityBoxId).find('#quantity-checkout-btn').attr('href', buildCheckoutUrl(initialQuantity));
        
        // مخفی کردن review و checkout boxes (دیگه لازم نیستن)
        $('#review-box-desktop').removeClass('flex').addClass('hidden');
        $('#go-to-checkout-desktop').removeClass('flex').addClass('hidden');
        $('#review-box-mobile').removeClass('flex').addClass('hidden');
        $('#go-to-checkout-mobile').removeClass('flex').addClass('hidden');
        
        // نمایش mobile-box-selected و مخفی کردن mobile-box (slide down)
        $('#Mobile-box-selected').removeClass('hidden').addClass('flex');
        $('#mobile-box').removeClass('translate-y-0').addClass('translate-y-full');
        
        // آپدیت کردن اطلاعات سانس برای موبایل (فقط روز، تاریخ و ساعت)
        const dayName = ezTehranFmt(sessionTime, 'dddd');
        const dayNumber = ezTehranFmt(sessionTime, 'D');
        const monthName = ezTehranFmt(sessionTime, 'MMMM');
        const selectedInfoText = `<span class="inline-block">${dayName}</span><span class="text-[#FF7A00]">${dayNumber}</span><span class="inline-block">${monthName}</span><span class="inline-block">${sessionFormatted}</span>`;
        $('#mobile-selected-info').html(selectedInfoText);
    });
    
    // Event handler برای دکمه‌های + و -
    $(document).on('click', '.btn-increase', function(e) {
        e.stopPropagation();
        const $quantityBox = $(this).closest('[id^="quantity-box"]');
        const $counter = $(this).siblings('span').find('.players-count');
        const minPlayers = parseInt($quantityBox.data('min-players'));
        const maxPlayers = parseInt($quantityBox.data('max-players'));
        const sessionTime = $quantityBox.data('session-time');
        let current = parseInt($counter.text());
        
        if (current < maxPlayers) {
            current++;
            $counter.text(current);
            updateCheckoutUrls(sessionTime, current);
            updateButtonStates($quantityBox, current, minPlayers, maxPlayers);
        }
    });
    
    $(document).on('click', '.btn-decrease', function(e) {
        e.stopPropagation();
        const $quantityBox = $(this).closest('[id^="quantity-box"]');
        const $counter = $(this).siblings('span').find('.players-count');
        const minPlayers = parseInt($quantityBox.data('min-players'));
        const maxPlayers = parseInt($quantityBox.data('max-players'));
        const sessionTime = $quantityBox.data('session-time');
        let current = parseInt($counter.text());
        
        if (current > minPlayers) {
            current--;
            $counter.text(current);
            updateCheckoutUrls(sessionTime, current);
            updateButtonStates($quantityBox, current, minPlayers, maxPlayers);
        }
    });
    
    // تابع کمکی برای آپدیت URL ها
    function updateCheckoutUrls(sessionTime, quantity) {
        let checkoutUrl;
        if (window.location.hostname === "localhost") {
            checkoutUrl = "http://localhost/escapezoom_wp/checkout";
        } else {
            checkoutUrl = window.location.origin + "/checkout";
        }
        checkoutUrl += `/?add-to-cart=${ProductJsObject.product_id}&book=${sessionTime}&quantity=${quantity}`;
        
        $('#go-to-checkout-desktop').attr('href', checkoutUrl);
        $('#go-to-checkout-mobile').attr('href', checkoutUrl);
        $('#mobile-confirm-payment').attr('href', checkoutUrl);
        $('#quantity-checkout-btn').attr('href', checkoutUrl);
        
        // آپدیت اطلاعات mobile-selected-info
        const $quantityBox = $('[id^="quantity-box-"]:visible');
        if ($quantityBox.length > 0) {
            const sessionFormatted = $quantityBox.data('session-formatted');
            const dayName = ezTehranFmt(sessionTime, 'dddd');
            const dayNumber = ezTehranFmt(sessionTime, 'D');
            const monthName = ezTehranFmt(sessionTime, 'MMMM');
            const selectedInfoText = `${dayName} <span class="text-[#FF7A00]">${dayNumber}</span> ${monthName} ${sessionFormatted}`;
            $('#mobile-selected-info').html(selectedInfoText);
        }
    }
    
    // Event handler برای دکمه "تغییر سانس"
    $(document).on('click', '.change-session-btn', function() {
        const $quantityBox = $(this).closest('[id^="quantity-box"]');
        const device = $quantityBox.attr('id').includes('desktop') ? 'desktop' : 'mobile';
        const container = device === 'desktop' ? '#sessions-list-desktop' : '#sessions-list-mobile';
        const allSessions = JSON.parse($(container).data('all-sessions') || '[]');
        
        // مخفی کردن quantity box
        $quantityBox.hide().html('');
        
        // مخفی کردن review و checkout boxes
        if (device === 'desktop') {
            $('#review-box-desktop').removeClass('flex').addClass('hidden');
            $('#go-to-checkout-desktop').removeClass('flex').addClass('hidden');
        } else {
            $('#review-box-mobile').removeClass('flex').addClass('hidden');
            $('#go-to-checkout-mobile').removeClass('flex').addClass('hidden');
            $('#Mobile-box-selected').removeClass('flex').addClass('hidden');
            
            // نمایش mobile-box فقط اگر اسکرول بیشتر از یک viewport باشد (با انیمیشن)
            const scrollTop = $(window).scrollTop();
            const viewportHeight = $(window).height();
            if (scrollTop > viewportHeight) {
                $('#mobile-box').removeClass('translate-y-full').addClass('translate-y-0');
            } else {
                $('#mobile-box').removeClass('translate-y-0').addClass('translate-y-full');
            }
            
            // پاک کردن اطلاعات سانس انتخاب شده
            $('#mobile-selected-info').text('');
        }
        
        // نمایش دوباره تقویم و session info
        const dateContainer = device === 'desktop' ? '.date-scroll-container-desktop' : '.date-scroll-container-mobile';
        const sessionInfo = device === 'desktop' ? '#sessions-info-desktop' : '#sessions-info-mobile';
        $(dateContainer).show();
        $(sessionInfo).show();
        
        // نمایش دوباره Embla container (با fade effects)
        const emblaContainer = device === 'desktop' ? '.sessions-embla-container-desktop' : '.sessions-embla-container-mobile';
        $(emblaContainer).show();
        if (device === 'desktop') {
            updateDesktopScrollButtons();
            $('.session-scroll-btn--prev, .session-scroll-btn--next').removeClass('session-scroll-btn--hidden');
        }
        
        // ReInit کردن Embla carousel برای تقویم بعد از نمایش دوباره
        // استفاده از requestAnimationFrame برای اطمینان از render کامل
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                // Force reflow برای اطمینان از محاسبات صحیح
                const emblaNode = device === 'desktop' 
                    ? document.querySelector('.embla-dates-desktop')
                    : document.querySelector('.embla-dates-mobile');
                if (emblaNode) {
                    emblaNode.offsetHeight; // Force reflow
                }
                // حالا Embla رو بساز
                initDatesEmbla();
            });
        });
        
        // Render دوباره سانس‌ها
        if (allSessions.length > 0) {
            renderSessions(allSessions, device, 1);
            if (device === 'desktop') {
                updateDesktopScrollButtons();
            }
        }
    });

    // Pagination handlers - حذف شده چون دکمه‌های قبلی/بعدی نداریم

    // Mobile panel handlers با انیمیشن fade
    $('.open-sessions').on('click', function() {
        // چک کنیم که آیا این دکمه در Mobile-box-selected است (دکمه سطل آشغال)
        const isDeleteButton = $(this).closest('#Mobile-box-selected').length > 0;
        
        if (isDeleteButton) {
            // حذف سانس انتخاب شده
            $('#mobile-selected-info').text('');
            $('#Mobile-box-selected').removeClass('flex').addClass('hidden');
            
            // نمایش mobile-box فقط اگر اسکرول بیشتر از یک viewport باشد (با انیمیشن)
            const scrollTop = $(window).scrollTop();
            const viewportHeight = $(window).height();
            if (scrollTop > viewportHeight) {
                $('#mobile-box').removeClass('translate-y-full').addClass('translate-y-0');
            } else {
                $('#mobile-box').removeClass('translate-y-0').addClass('translate-y-full');
            }
            
            // مخفی کردن quantity box
            $('#quantity-box-mobile').hide().html('');
            
            // نمایش دوباره تقویم و session info
            $('.date-scroll-container-mobile').show();
            $('#sessions-info-mobile').show();
            $('.sessions-embla-container-mobile').show();
            
            // مخفی کردن review و checkout boxes
            $('#review-box-mobile').removeClass('flex').addClass('hidden');
            $('#go-to-checkout-mobile').removeClass('flex').addClass('hidden');
        }
        
        // باز کردن پنل سانس‌ها
        const panel = $('#sessions-panel');
        
        panel.fadeIn(0, function() {
            panel.removeClass('translate-y-full').addClass('translate-y-0');
        });
        $('#overlay').fadeIn(300);
    });

    // تابع کمکی برای بستن panel
    function closeSessionsPanel() {
        const panel = $('#sessions-panel');
        
        // بعد از محو شدن کامل، slide down
        panel.removeClass('translate-y-0').addClass('translate-y-full').fadeOut(300, function() {
            $('#overlay').fadeOut(300, function() {
                panel.css('display', 'none');
            });
        });
        
        // اگر سانسی انتخاب شده بود، حالت پایین رو نمایش بده
        const mobileSelectedInfo = $('#mobile-selected-info').text();
        if (mobileSelectedInfo && mobileSelectedInfo.trim() !== '') {
            $('#mobile-box').removeClass('translate-y-0').addClass('translate-y-full');
            $('#Mobile-box-selected').removeClass('hidden').addClass('flex');
        } else {
            // اگر سانسی انتخاب نشده بود، mobile-box را بر اساس اسکرول نمایش بده (با انیمیشن)
            const scrollTop = $(window).scrollTop();
            const viewportHeight = $(window).height();
            if (scrollTop > viewportHeight) {
                $('#mobile-box').removeClass('translate-y-full').addClass('translate-y-0');
            } else {
                $('#mobile-box').removeClass('translate-y-0').addClass('translate-y-full');
            }
        }
    }

    // بستن panel با کلیک روی دکمه X
    $('#close-sessions').on('click', function(e) {
        e.stopPropagation();
        closeSessionsPanel();
    });
    
    // بستن panel با کلیک روی overlay
    $('#overlay').on('click', function() {
        // Check if comment modal is open
        if ($('#comment-modal-mobile').hasClass('translate-y-0')) {
            closeCommentModal();
        } else {
            // Otherwise close sessions panel
            closeSessionsPanel();
        }
    });

    // Populate date buttons
    function populateDateButtons() {
        // ساخت API URL
        let apiUrl = window.location.origin + '/api.php?action=get_server_time';
        if (window.location.hostname === 'localhost') {
            apiUrl = 'http://localhost/escapezoom_wp/api.php?action=get_server_time';
        }

        const requestUrl = `${apiUrl}&_=${Date.now()}`;

        fetch(requestUrl, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache',
            },
        })
            .then(response => response.json())
            .then(data => {
                const currentDate = data.current_date;
                
                // Set data-date for today buttons
                $('#today-btn-desktop, #today-btn-mobile').data('date', currentDate);
                
                let datesHTML = '';

                const dayRows = Array.isArray(data.days) && data.days.length
                    ? data.days
                    : Array.from({ length: 15 }, (_, idx) => {
                        const ts = currentDate + 86400 * (idx + 1);
                        return {
                            ts,
                            day: ezTehranFmt(ts, 'd'),
                            name: ezTehranFmt(ts, 'dddd'),
                        };
                    });

                dayRows.forEach((row) => {
                    datesHTML += `
                        <div class="date-btn w-[60px] h-[60px] rounded-lg border border-[#E2E8F0] flex flex-col justify-center items-center cursor-pointer flex-shrink-0 hover:border-[#5091FB] transition text-[#0F172B]" data-date="${row.ts}">
                            <span class="text-lg font-bold">${row.day}</span>
                            <span class="text-xs text-[#889BAD]">${row.name}</span>
                        </div>
                    `;
                });

                $('.date-scroll-list-desktop').html(datesHTML);
                $('.date-scroll-list-mobile').html(datesHTML);
                
                // فعال کردن Embla carousel برای تقویم
                initDatesEmbla();

                // Auto-click امروز
                setTimeout(() => {
                    $('#today-btn-desktop').click();
                }, 100);
            })
            .catch(error => {
                console.error('Error fetching server time:', error);
                const currentDate = ezTehranMidnightNow();
                $('#today-btn-desktop, #today-btn-mobile').data('date', currentDate);
                
                // فعال کردن Embla carousel برای تقویم
                initDatesEmbla();
                
                // Auto-click
                setTimeout(() => {
                    $('#today-btn-desktop').click();
                }, 100);
            });
    }

    // Event handler برای کلیک روی دکمه‌های تاریخ
    $(document).on('click', '#today-btn-desktop, #today-btn-mobile, .date-btn', function() {
        const $btn = $(this);
        const isDesktop = $btn.attr('id')?.includes('desktop') || $btn.closest('.date-scroll-container-desktop').length;
        const device = isDesktop ? 'desktop' : 'mobile';
        const dateTimestamp = $btn.data('date') || ezTehranMidnightNow();
        
        // Active state
        if (device === 'desktop') {
            // Reset همه دکمه‌ها
            $('#today-btn-desktop').removeClass('bg-[#5091FB] text-white').addClass('bg-[#F1F5F9] text-[#62748E]');
            $('.date-scroll-list-desktop .date-btn').removeClass('bg-[#5091FB] text-white border-[#5091FB]').addClass('border-[#E2E8F0] text-[#0F172B]');
            // برگردوندن رنگ نام روز هفته به حالت عادی
            $('.date-scroll-list-desktop .date-btn span.text-xs').removeClass('text-white').addClass('text-[#889BAD]');
            
            // فعال کردن دکمه انتخاب شده
            if ($btn.attr('id') === 'today-btn-desktop') {
                $btn.removeClass('bg-[#F1F5F9] text-[#62748E]').addClass('bg-[#5091FB] text-white');
            } else {
                $btn.removeClass('border-[#E2E8F0] text-[#0F172B]').addClass('bg-[#5091FB] text-white border-[#5091FB]');
                // تغییر رنگ نام روز هفته به سفید
                $btn.find('span.text-xs').removeClass('text-[#889BAD]').addClass('text-white');
            }
        } else {
            // Reset همه دکمه‌ها
            $('#today-btn-mobile').removeClass('bg-[#5091FB] text-white').addClass('bg-[#F1F5F9] text-[#62748E]');
            $('.date-scroll-list-mobile .date-btn').removeClass('bg-[#5091FB] text-white border-[#5091FB]').addClass('border-[#E2E8F0] text-[#0F172B]');
            // برگردوندن رنگ نام روز هفته به حالت عادی
            $('.date-scroll-list-mobile .date-btn span.text-xs').removeClass('text-white').addClass('text-[#889BAD]');
            
            // فعال کردن دکمه انتخاب شده
            if ($btn.attr('id') === 'today-btn-mobile') {
                $btn.removeClass('bg-[#F1F5F9] text-[#62748E]').addClass('bg-[#5091FB] text-white');
            } else {
                $btn.removeClass('border-[#E2E8F0] text-[#0F172B]').addClass('bg-[#5091FB] text-white border-[#5091FB]');
                // تغییر رنگ نام روز هفته به سفید
                $btn.find('span.text-xs').removeClass('text-[#889BAD]').addClass('text-white');
            }
        }
        
        // Load sessions
        BuildSans(ProductJsObject.product_id, dateTimestamp);
    });

    // Embla carousel instances برای تقویم
    let emblaDatesDesktop = null;
    let emblaDatesMobile = null;
    
    // Initialize Embla Carousel برای date lists
    function initDatesEmbla() {
        if (typeof EmblaCarousel === 'undefined') {
            console.warn('EmblaCarousel is not loaded');
            return;
        }
        
        // Desktop
        const emblaNodeDesktop = document.querySelector('.embla-dates-desktop');
        if (emblaNodeDesktop) {
            // Destroy قبلی اگر وجود داره
            if (emblaDatesDesktop) {
                try {
                    emblaDatesDesktop.destroy();
                } catch (e) {
                    console.error('Error destroying desktop embla:', e);
                }
            }
            
            const optionsDesktop = {
                axis: 'x',
                dragFree: true,
                containScroll: 'keepSnaps',
                align: 'start',
                skipSnaps: false,
                direction: 'rtl'
            };
            
            try {
                emblaDatesDesktop = EmblaCarousel(emblaNodeDesktop, optionsDesktop);
            } catch (e) {
                console.error('Error initializing desktop embla:', e);
            }
        }
        
        // Mobile
        const emblaNodeMobile = document.querySelector('.embla-dates-mobile');
        if (emblaNodeMobile) {
            // Destroy قبلی اگر وجود داره
            if (emblaDatesMobile) {
                try {
                    emblaDatesMobile.destroy();
                } catch (e) {
                    console.error('Error destroying mobile embla:', e);
                }
            }
            
            const optionsMobile = {
                axis: 'x',
                dragFree: true,
                containScroll: 'keepSnaps',
                align: 'start',
                skipSnaps: false,
                direction: 'rtl'
            };
            
            try {
                emblaDatesMobile = EmblaCarousel(emblaNodeMobile, optionsMobile);
            } catch (e) {
                console.error('Error initializing mobile embla:', e);
            }
        }
    }
    
    // Initialize: Populate dates
    populateDateButtons();

    // BuildSans(ProductJsObject.product_id, (Date.now() / 1000))

    SelectDay.on('slideChange', function () {
        setTimeout(() => $(".swiper-slide-active [data-reserve-timestamp]").get(0).click(), 1)
    })

    new Swiper('.product-gallery', {
        spaceBetween: 20,
        slidesPerView: 1,
        loop: true,
        autoplay: true,
        navigation: {
            nextEl: '.products-gallery-next',
            prevEl: '.products-gallery-prev',
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    })

    lightbox.option({
        albumLabel: "تصویر %1 از %2"
    })

    $(".criticism-read-more").on('click', function () {
        $(this).prev().toggleClass('line-clamp-4')
        $(this).text($(this).text().trim() === "مشاهده بیشتر" ? "مشاهده کمتر" : "مشاهده بیشتر")
    })

    // Toggle Menu برای مشخصات محصول با انیمیشن
    const toggleMenuBtn = document.getElementById('toggleMenu');
    const menuContainer = document.getElementById('menuContainer');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (toggleMenuBtn && menuContainer) {
        toggleMenuBtn.addEventListener('click', function() {
            const extraItems = menuContainer.querySelectorAll('.extra-item');
            const isExpanded = !extraItems[0].classList.contains('hidden');
            
            if (isExpanded) {
                // بستن با انیمیشن
                extraItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.maxHeight = item.scrollHeight + 'px';
                        item.style.opacity = '1';
                        
                        setTimeout(() => {
                            item.style.maxHeight = '0px';
                            item.style.opacity = '0';
                            item.style.marginTop = '0px';
                            item.style.marginBottom = '0px';
                        }, 10);
                        
                        setTimeout(() => {
                            item.classList.add('hidden');
                            item.classList.remove('flex');
                            item.style.maxHeight = '';
                            item.style.opacity = '';
                            item.style.marginTop = '';
                            item.style.marginBottom = '';
                        }, 310);
                    }, index * 50);
                });
                
                // تغییر آیکون و متن
                toggleMenuBtn.querySelector('span').textContent = 'مشاهده کامل';
                toggleIcon.style.transform = 'rotate(0deg)';
            } else {
                // باز کردن با انیمیشن
                extraItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.remove('hidden');
                        item.classList.add('flex');
                        item.style.maxHeight = '0px';
                        item.style.opacity = '0';
                        item.style.overflow = 'hidden';
                        item.style.transition = 'all 0.3s ease-in-out';
                        item.style.marginTop = '0px';
                        item.style.marginBottom = '0px';
                        
                        setTimeout(() => {
                            item.style.maxHeight = item.scrollHeight + 'px';
                            item.style.opacity = '1';
                            item.style.marginTop = '';
                            item.style.marginBottom = '';
                        }, 10);
                        
                        setTimeout(() => {
                            item.style.maxHeight = '';
                            item.style.overflow = '';
                        }, 310);
                    }, index * 50);
                });
                
                // تغییر آیکون و متن
                toggleMenuBtn.querySelector('span').textContent = 'بستن';
                toggleIcon.style.transform = 'rotate(180deg)';
            }
        });
    }

    // انیمیشن شمارنده برای عددها
    function animateCounter(element) {
        const target = parseFloat(element.getAttribute('data-target'));
        const decimals = parseInt(element.getAttribute('data-decimals')) || 0;
        const duration = 500;
        const step = target / (duration / 16); // 60fps
        let current = 0;

        // پیدا کردن progress bar مرتبط
        const progressBar = element.parentElement.querySelector('.animate-progress');
        let progressCurrent = 0;
        let progressTarget = 0;
        let progressStep = 0;

        if (progressBar) {
            progressTarget = parseFloat(progressBar.getAttribute('data-target-width')) || 0;
            progressStep = progressTarget / (duration / 16);
        }

        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = current.toFixed(decimals);

            // انیمیت کردن progress bar
            if (progressBar) {
                progressCurrent += progressStep;
                if (progressCurrent >= progressTarget) {
                    progressCurrent = progressTarget;
                }
                progressBar.style.width = progressCurrent + '%';
            }
        }, 16);
    }

    // استفاده از Intersection Observer برای شروع انیمیشن وقتی المنت دیده می‌شود
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                entry.target.classList.add('animated');
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // شروع مشاهده تمام المنت‌های با کلاس animate-counter
    document.querySelectorAll('.animate-counter').forEach(counter => {
        observer.observe(counter);
    });

    // Initialize Embla برای tabs
    let emblaTabs = null;
    const emblaNodeTabs = document.querySelector('.embla-tabs');
    if (emblaNodeTabs) {
        const optionsTabs = { 
            direction: 'rtl',
            align: 'start',
            containScroll: 'trimSnaps',
            dragFree: true
        };
        try {
            emblaTabs = EmblaCarousel(emblaNodeTabs, optionsTabs);
        } catch (e) {
            console.error('Error initializing tabs embla:', e);
        }
    }

    // Tab navigation
    $('.tab-btn').on('click', function(e) {
        e.preventDefault();
        
        const sectionId = $(this).data('section');
        const $section = $('#' + sectionId);
        
        if ($section.length) {
            // Remove active state from all tabs
            $('.tab-btn').removeClass('text-[#FD7013] bg-white border border-[#FD7013]').addClass('text-[#90A1B9] bg-[#F8FAFC]');
            
            // Add active state to clicked tab (both mobile and desktop)
            $('[data-section="' + sectionId + '"]').removeClass('text-[#90A1B9] bg-[#F8FAFC]').addClass('text-[#FD7013] bg-white border border-[#FD7013]');
            
            // Scroll to section
            const offset = $(window).width() < 1024 ? 80 : 100; // offset برای sticky header
            $('html, body').animate({
                scrollTop: $section.offset().top - offset
            }, 500);
        }
    });

    // Update active tab on scroll
    let sections = [];
    $('.tab-btn').each(function() {
        const sectionId = $(this).data('section');
        if ($('#' + sectionId).length) {
            sections.push(sectionId);
        }
    });

    // نمایش mobile-box و stiky-title-mobile بعد از اسکرول یک viewport
    $(window).on('scroll', function() {
        const scrollTop = $(window).scrollTop();
        const viewportHeight = $(window).height();
        
        // فقط برای موبایل (زیر 1024px)
        if ($(window).width() < 1024) {
            // اگر بیشتر از یک viewport اسکرول شده باشد
            if (scrollTop > viewportHeight) {
                // نمایش mobile-box فقط اگر Mobile-box-selected نمایش داده نشده باشد
                if (!$('#Mobile-box-selected').hasClass('flex')) {
                    $('#mobile-box').removeClass('translate-y-full').addClass('translate-y-0');
                }
                $('#stiky-title-mobile').fadeIn(300);
                // نمایش stiky-title-mobile
            } else {
                // اگر کمتر از یک viewport اسکرول شده، مخفی کن (slide up)
                $('#mobile-box').removeClass('translate-y-0').addClass('translate-y-full');
                $('#stiky-title-mobile').fadeOut(300);
            }
        } else {
            // Desktop - نمایش stiky-title-desktop
            if (scrollTop > viewportHeight) {
                $('#stiky-title-desktop').fadeIn(300);
            } else {
                $('#stiky-title-desktop').fadeOut(300);
            }
        }
        
        // Update active tab based on scroll position
        let currentSection = '';
        sections.forEach(function(sectionId) {
            const $section = $('#' + sectionId);
            if ($section.length) {
                const sectionTop = $section.offset().top - 150;
                if (scrollTop >= sectionTop) {
                    currentSection = sectionId;
                }
            }
        });
        
        if (currentSection) {
            const $activeTab = $('[data-section="' + currentSection + '"]');
            const wasActive = $activeTab.hasClass('text-[#FD7013]');
            
            $('.tab-btn').removeClass('text-[#FD7013] bg-white border border-[#FD7013]').addClass('text-[#90A1B9] bg-[#F8FAFC]');
            $activeTab.removeClass('text-[#90A1B9] bg-[#F8FAFC]').addClass('text-[#FD7013] bg-white border border-[#FD7013]');
            
            // اسکرول کروسل برای قرار دادن دکمه فعال در وسط صفحه (فقط برای موبایل)
            if ($(window).width() < 1024 && emblaTabs && !wasActive) {
                const activeButton = $activeTab[0];
                if (activeButton && activeButton.closest('.embla-tabs')) {
                    // استفاده از setTimeout برای اطمینان از اینکه DOM به‌روز شده
                    setTimeout(function() {
                        const container = emblaTabs.containerNode();
                        const viewport = emblaTabs.rootNode();
                        
                        if (container && viewport) {
                            // روش 1: استفاده از scrollIntoView برای مرکز کردن
                            activeButton.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest',
                                inline: 'center'
                            });
                            
                            // روش 2: اگر scrollIntoView کار نکرد، از محاسبه دستی استفاده می‌کنیم
                            setTimeout(function() {
                                const viewportRect = viewport.getBoundingClientRect();
                                const buttonRect = activeButton.getBoundingClientRect();
                                
                                // بررسی اینکه آیا دکمه در وسط است یا نه
                                const buttonCenterInViewport = buttonRect.left + (buttonRect.width / 2) - viewportRect.left;
                                const viewportCenter = viewportRect.width / 2;
                                const offset = Math.abs(buttonCenterInViewport - viewportCenter);
                                
                                // اگر offset بیشتر از 10 پیکسل باشد، دوباره اسکرول می‌کنیم
                                if (offset > 10) {
                                    const scrollOffset = buttonCenterInViewport - viewportCenter;
                                    const currentScrollLeft = container.scrollLeft;
                                    const newScrollLeft = currentScrollLeft - scrollOffset;
                                    
                                    // محدود کردن به محدوده مجاز
                                    const maxScroll = container.scrollWidth - container.clientWidth;
                                    const minScroll = 0;
                                    const clampedScroll = Math.max(minScroll, Math.min(maxScroll, newScrollLeft));
                                    
                                    // اسکرول مستقیم
                                    container.scrollTo({
                                        left: clampedScroll,
                                        behavior: 'smooth'
                                    });
                                }
                            }, 100);
                        }
                    }, 100);
                }
            }
        }
    });

    // Deprecated: این تابع قدیمی برای swiper بود، حذف شد چون الان سیستم جدید داریم

    // Share functionality برای دکمه‌های اشتراک گذاری
    const shareButtons = document.querySelectorAll('#go_map_ez_desktop, #go_map_ez_mobile, .share-top-btn');
    
    shareButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const title = this.getAttribute('data-title') || document.title;
            const url = window.location.href;
            const text = `${title} - اسکیپ زوم`;
            
            // بررسی پشتیبانی از Web Share API
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: title,
                        text: text,
                        url: url
                    });
                    console.log('محتوا با موفقیت به اشتراک گذاشته شد');
                } catch (err) {
                    // اگر کاربر کنسل کرد یا خطایی رخ داد
                    if (err.name !== 'AbortError') {
                        console.log('خطا در اشتراک گذاری:', err);
                        // Fallback: کپی لینک در کلیپبورد
                        copyToClipboard(url);
                    }
                }
            } else {
                // Fallback برای مرورگرهایی که Web Share API ندارن
                copyToClipboard(url);
            }
        });
    });
    
    // تابع کپی کردن لینک در کلیپبورد
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showNotification('لینک در کلیپبورد کپی شد!');
            }).catch(err => {
                console.error('خطا در کپی کردن:', err);
                fallbackCopyTextToClipboard(text);
            });
        } else {
            fallbackCopyTextToClipboard(text);
        }
    }
    
    // Fallback method برای کپی کردن
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showNotification('لینک در کلیپبورد کپی شد!');
        } catch (err) {
            console.error('خطا در کپی کردن:', err);
            showNotification('خطا در کپی کردن لینک');
        }
        
        document.body.removeChild(textArea);
    }
    
    // نمایش notification
    function showNotification(message) {
        // بررسی اگر تابع toast وجود داره از اون استفاده می‌کنیم
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            // Simple notification با alert
            alert(message);
        }
    }

    // ==================== Comment Modal Functionality ====================
    
    // Open comment modal
    $('.open-comments-btn').on('click', function(e) {
        e.preventDefault();
        
        // بررسی لاگین بودن کاربر - این تابع در خط 2 handle میشه
        // فقط مودال رو باز می‌کنیم
        
        // Check if mobile or desktop
        const isMobile = window.innerWidth < 1024;
        
        if (isMobile) {
            // Mobile - Bottom Sheet Animation (مثل sessions panel)
            const modal = $('#comment-modal-mobile');
            
            // Clone form to mobile container (with all events)
            const $form = $('#comment-form').clone(true, true);
            $('#comment-form-container-mobile').html($form);
            ezProductReviewApplyEditToForm($form);
            
            $('#overlay').fadeIn(300);
            modal.css('display', 'block');
            
            // Trigger animation
            setTimeout(() => {
                modal.removeClass('translate-y-full').addClass('translate-y-0');
            }, 10);
        } else {
            // Desktop - Center Modal Animation
            const modal = $('#comment-modal-desktop');
            const content = modal.find('.relative');
            
            $('#comment-modal-overlay').removeClass('hidden').css('display', 'block');
            modal.removeClass('hidden').css('display', 'flex');
            
            // Trigger animation
            setTimeout(() => {
                content.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            }, 10);

            ezProductReviewApplyEditToForm($('#comment-form'));
        }
    });
    
    // Close comment modal
    function closeCommentModal() {
        const isMobile = window.innerWidth < 1024;
        
        if (isMobile) {
            const modal = $('#comment-modal-mobile');
            
            modal.removeClass('translate-y-0').addClass('translate-y-full');
            
            setTimeout(() => {
                $('#overlay').fadeOut(300, function() {
                    modal.css('display', 'none');
                });
            }, 300);
        } else {
            const modal = $('#comment-modal-desktop');
            const content = modal.find('.relative');
            
            content.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            
            setTimeout(() => {
                modal.css('display', 'none').addClass('hidden');
                $('#comment-modal-overlay').css('display', 'none').addClass('hidden');
            }, 300);
        }
    }
    
    // Close button handler
    $(document).on('click', '#close-comment-modal', function(e) {
        e.stopPropagation();
        closeCommentModal();
    });
    
    // Close when clicking on overlay (Desktop)
    $('#comment-modal-overlay').on('click', function() {
        closeCommentModal();
    });
    
    // Handle ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!$('#comment-modal-desktop').hasClass('hidden') || $('#comment-modal-mobile').hasClass('translate-y-0')) {
                closeCommentModal();
            }
        }
    });

    if (typeof ProductJsObject !== 'undefined' && new URLSearchParams(window.location.search).get('open_review_modal') === '1') {
        setTimeout(function () {
            $('.open-comments-btn').first().trigger('click');
        }, 400);
    }
})
// --- اضافه شده برای جلوگیری از کلیک تکراری روی دکمه‌های پرداخت ---
jQuery(document).ready(function($) {
    $(document).on('click', '#go-to-checkout-mobile, #go-to-checkout-desktop, #quantity-checkout-btn', function (e) {
        var btn = $(this);
        
        // اگر دکمه قبلا کلیک شده و کلاس پردازش دارد، کلیک‌های بعدی را بی‌اثر کن
        if (btn.hasClass('is-processing')) {
            e.preventDefault();
            return false;
        }

        // در اولین کلیک: اضافه کردن کلاس برای کمرنگ شدن و غیرفعال شدن کلیک
        btn.addClass('is-processing opacity-50 pointer-events-none');
        
        // تغییر دادن متن دکمه به "در حال انتقال..."
        var btnSpan = btn.find('span');
        if(btnSpan.length > 0) {
            btnSpan.text('در حال انتقال...');
        } else {
            btn.text('در حال انتقال...');
        }
        
        // بعد از 5 ثانیه اگر به هر دلیلی (مثل قطعی اینترنت) کاربر منتقل نشد، دکمه به حالت اول برگردد
        setTimeout(function() {
            btn.removeClass('is-processing opacity-50 pointer-events-none');
            if(btnSpan.length > 0) {
                btnSpan.text('پرداخت و ثبت رزرو');
            } else {
                btn.text('پرداخت و ثبت رزرو');
            }
        }, 5000);
    });
});
// -----------------------------------------------------------------
