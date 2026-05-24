<?php get_header(); ?>

<?php

// جمع‌آوری فقط بازی‌های تبلیغاتی اصفهان

$promotional_data = get_option('promotional_products_isfahan', []);



$promotional_product_ids = [];

if (!empty($promotional_data) && !empty($promotional_data['types'])) {

    foreach ($promotional_data['types'] as $type => $product_ids) {

        if (is_array($product_ids)) {

            $promotional_product_ids = array_merge($promotional_product_ids, $product_ids);

        }

    }

}



// حذف تکراری‌ها و تبدیل به عدد صحیح

$promotional_product_ids = array_unique(array_map('intval', $promotional_product_ids));



// فیلتر فقط محصولات فعال (active / updated)

$filtered_promotional_ids = [];

foreach ($promotional_product_ids as $product_id) {

    $product_state = get_post_meta($product_id, 'product_state', true);

    if ($product_state === 'active' || $product_state === 'updated') {

        $filtered_promotional_ids[] = $product_id;

    }

}



// دسته‌بندی بر اساس نوع محصول

$escape_room_ids = [];

$cinema_ids      = [];



foreach ($filtered_promotional_ids as $product_id) {

    $terms        = get_the_terms($product_id, 'product_cat');

    $product_type = null;



    if ($terms && !is_wp_error($terms)) {

        if (count($terms) > 1) {

            foreach ($terms as $term) {

                if ($term->parent == 0) {

                    $product_type = $term->name;

                    break;

                }

            }

        } elseif (count($terms) === 1) {

            $term = $terms[0];

            if ($term->parent != 0) {

                $parent_term = get_term($term->parent);

                if ($parent_term && !is_wp_error($parent_term)) {

                    $product_type = $parent_term->name;

                }

            } else {

                $product_type = $term->name;

            }

        }

    }



    if ($product_type === 'اتاق فرار') {

        $escape_room_ids[] = $product_id;

    } elseif ($product_type === 'سینما ترس') {

        $cinema_ids[] = $product_id;

    }

}



// ترتیب تصادفی برای نمایش متنوع

shuffle($escape_room_ids);

shuffle($cinema_ids);

?>



<!-- showcase start -->

<div class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw]">

    <img src="<?= Theme_ASSET_URL ?>images/isfahan-day-sm-2.avif" alt="<?php the_title(); ?>" class="lg:hidden">

    <img src="<?= Theme_ASSET_URL ?>images/isfahan-day-lg-2.avif" alt="<?php the_title(); ?>" class="max-lg:hidden">

</div>

<!-- showcase end -->



<!-- content start -->

<section class="container mx-auto py-8 lg:py-12">

    <?php if (!empty($escape_room_ids) || !empty($cinema_ids)): ?>

        <!-- اتاق فرارهای پیشنهادی ترسناک و هیجانی -->

        <?php if (!empty($escape_room_ids)): ?>

            <div class="mb-12">

                <h2 class="text-xl lg:text-2xl font-extrabold mb-6 text-center">

                    اتاق فرارهای پیشنهادی ترسناک و هیجانی

                </h2>

                <div id="escape-room-grid" class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 child:box-content gap-6">

                    <div class="col-span-full text-center py-8 text-gray-500">در حال بارگذاری...</div>

                </div>

            </div>

        <?php endif; ?>



        <!-- سینما ترس های پیشنهادی -->

        <?php if (!empty($cinema_ids)): ?>

            <div class="mb-12">

                <h2 class="text-xl lg:text-2xl font-extrabold mb-6 text-center">

                    سینما ترس های پیشنهادی

                </h2>

                <div id="cinema-grid" class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 child:box-content gap-6">

                    <div class="col-span-full text-center py-8 text-gray-500">در حال بارگذاری...</div>

                </div>

            </div>

        <?php endif; ?>

    <?php else: ?>

        <div class="mb-12 text-center py-8">

            <p class="text-gray-500">هیچ بازی تبلیغاتی فعالی برای شهر اصفهان یافت نشد.</p>

        </div>

    <?php endif; ?>

</section>

<!-- content end -->



<!-- باکس تخفیف ۱۵۰ هزار تومانی نارنجی (مشابه Tehran Games) -->

<section class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] overflow-hidden my-4 lg:my-10">

    <div class="container mx-auto px-4 sm:px-6 md:px-8">

        <div id="esfahan-150-discount-box" class="relative bg-gradient-to-br from-[#FF6B47] via-[#FD6A2E] to-[#D75602] rounded-lg lg:rounded-2xl p-3 lg:p-5 shadow-xl overflow-hidden transform transition-all duration-500 hover:scale-[1.02]">

            <!-- پس‌زمینه‌های دکوراتیو -->

            <div class="hidden lg:block absolute top-0 right-0 w-36 h-36 bg-white opacity-10 rounded-full -mr-18 -mt-18 animate-pulse"></div>

            <div class="hidden lg:block absolute bottom-0 left-0 w-28 h-28 bg-white opacity-10 rounded-full -ml-14 -mb-14 animate-pulse" style="animation-delay: 1s;"></div>



            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-2.5 lg:gap-4">

                <div class="flex-1 text-center lg:text-right">

                    <h3 class="text-white text-lg lg:text-2xl font-extrabold mb-0.5 lg:mb-1.5 leading-tight">

                        تخفیف ۱۵۰ هزار تومانی ویژه رزرو از اسکیپ‌زوم

                    </h3>

                </div>



                <div class="flex flex-row items-center gap-2.5 lg:gap-3 bg-white/20 backdrop-blur-sm rounded-lg lg:rounded-xl px-3 py-2 lg:px-6 lg:py-3.5 border border-white/30 w-full lg:w-auto justify-between lg:justify-center">

                    <div class="text-right flex-1 lg:flex-none">

                        <span class="text-white/80 text-xs lg:text-sm font-medium block mb-0 lg:mb-0.5">کد تخفیف:</span>

                        <code id="esfahan-150-promo-code" class="text-white text-lg lg:text-2xl font-black select-all tracking-wider">esf150</code>

                    </div>

                    <button id="copy-esfahan-150-promo" type="button" class="flex items-center justify-center gap-1.5 bg-white text-[#D75602] px-3 py-1.5 lg:px-4 lg:py-2 rounded-lg font-bold text-xs lg:text-base hover:bg-white/90 transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg shrink-0" title="کپی کد تخفیف">

                        <svg class="copy-icon-esfahan-150 w-4 h-4 lg:w-5 lg:h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none">

                            <path d="M11.2617 14.1757C11.2617 11.397 11.2617 10.0067 12.1254 9.14396C12.9881 8.28027 14.3784 8.28027 17.1572 8.28027H20.1049C22.8836 8.28027 24.274 8.28027 25.1367 9.14396C26.0003 10.0067 26.0003 11.397 26.0003 14.1757V19.0886C26.0003 21.8673 26.0003 23.2577 25.1367 24.1204C24.274 24.9841 22.8836 24.9841 20.1049 24.9841H17.1572C14.3784 24.9841 12.9881 24.9841 12.1254 24.1204C11.2617 23.2577 11.2617 21.8673 11.2617 19.0886V14.1757Z" stroke="currentColor" stroke-width="2" />

                            <path opacity="0.5" d="M10.2798 21.0534C9.49797 21.0534 8.74821 20.7428 8.1954 20.19C7.64259 19.6372 7.33203 18.8874 7.33203 18.1057V12.2102C7.33203 8.50492 7.33203 6.65178 8.48361 5.50119C9.63519 4.35059 11.4873 4.34961 15.1926 4.34961H19.1229C19.9047 4.34961 20.6545 4.66017 21.2073 5.21298C21.7601 5.76578 22.0707 6.51555 22.0707 7.29733" stroke="currentColor" stroke-width="2" />

                        </svg>

                        <svg class="check-icon-esfahan-150 hidden w-3.5 h-3.5 lg:w-4 lg:h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">

                            <path d="M20 6L9 17l-5-5" />

                        </svg>

                        <span class="copy-text-esfahan-150 text-xs lg:text-base">کپی</span>

                        <span class="copied-text-esfahan-150 hidden text-green-600 text-xs lg:text-base">کپی شد!</span>

                    </button>

                </div>

            </div>

        </div>

    </div>

</section>



<script>

    jQuery(document).ready(function($) {

        // کپی کد تخفیف ۱۵۰ هزار تومانی

        $('#copy-esfahan-150-promo').on('click', function() {

            const promoCode = $('#esfahan-150-promo-code').text();

            const $btn = $(this);

            const $copyIcon = $btn.find('.copy-icon-esfahan-150');

            const $checkIcon = $btn.find('.check-icon-esfahan-150');

            const $copyText = $btn.find('.copy-text-esfahan-150');

            const $copiedText = $btn.find('.copied-text-esfahan-150');



            function done() {

                $copyIcon.addClass('hidden');

                $checkIcon.removeClass('hidden');

                $copyText.addClass('hidden');

                $copiedText.removeClass('hidden');



                setTimeout(function() {

                    $copyIcon.removeClass('hidden');

                    $checkIcon.addClass('hidden');

                    $copyText.removeClass('hidden');

                    $copiedText.addClass('hidden');

                }, 2000);

            }



            if (navigator.clipboard && window.isSecureContext) {

                navigator.clipboard.writeText(promoCode).then(done).catch(function(err) {

                    console.error('Failed to copy:', err);

                });

            } else {

                const textArea = document.createElement('textarea');

                textArea.value = promoCode;

                textArea.style.position = 'fixed';

                textArea.style.opacity = '0';

                document.body.appendChild(textArea);

                textArea.select();

                try {

                    document.execCommand('copy');

                    done();

                } catch (err) {

                    console.error('Failed to copy:', err);

                }

                document.body.removeChild(textArea);

            }

        });



        // آدرس وب‌سرویس

        let baseUrlWebService = 'https://' + location.hostname + '/web-service/web-service.php';

        if (location.hostname === 'wo.escapezoom.local') {

            baseUrlWebService = 'http://' + location.hostname + '/web-service/web-service.php';

        }



        // لود اتاق فرارهای تبلیغاتی

        <?php if (!empty($escape_room_ids)): ?>

        $.ajax({

            type: 'POST',

            url: baseUrlWebService,

            data: {

                "type": "get_by_products_id",

                "data": {

                    "products_id": <?php echo json_encode($escape_room_ids); ?>,

                    "format": "html_list"

                }

            },

            dataType: "json",

            success: function(productsHtml) {

                $('#escape-room-grid').html(productsHtml);

            },

            error: function(xhr, status, error) {

                console.error('Error loading escape rooms:', error);

                $('#escape-room-grid').html('<div class="col-span-full text-center text-gray-500 py-8">خطا در بارگذاری بازی‌ها</div>');

            }

        });

        <?php endif; ?>



        // لود سینما ترس‌های تبلیغاتی

        <?php if (!empty($cinema_ids)): ?>

        $.ajax({

            type: 'POST',

            url: baseUrlWebService,

            data: {

                "type": "get_by_products_id",

                "data": {

                    "products_id": <?php echo json_encode($cinema_ids); ?>,

                    "format": "html_list"

                }

            },

            dataType: "json",

            success: function(productsHtml) {

                $('#cinema-grid').html(productsHtml);

            },

            error: function(xhr, status, error) {

                console.error('Error loading cinema games:', error);

                $('#cinema-grid').html('<div class="col-span-full text-center text-gray-500 py-8">خطا در بارگذاری بازی‌ها</div>');

            }

        });

        <?php endif; ?>

    });

</script>



<div class="lg:w-screen relative lg:left-1/2 lg:right-1/2 lg:-ml-[50vw] lg:-mr-[50vw]">

    <svg width="359" height="154" viewBox="0 0 359 154" fill="none" xmlns="http://www.w3.org/2000/svg" class="absolute max-lg:right-[-30px] max-lg:bottom-[-170px] lg:bottom-0 lg:right-0">

        <path d="M358.391 0C261.504 91.6257 143.109 117.989 0 121.23C74.0692 146.376 155.244 163.684 358.391 147.357V0Z" fill="url(#paint0_linear_53684_17896)" />

        <path d="M358.391 4.55566C306.56 76.0397 252.468 122.505 162.687 152.284C212.791 155.08 275.417 154.026 358.391 147.357V4.55566Z" fill="url(#paint1_linear_53684_17896)" />

        <defs>

            <linearGradient id="paint0_linear_53684_17896" x1="341.865" y1="-92.9415" x2="-336.881" y2="316.281" gradientUnits="userSpaceOnUse">

                <stop stop-color="#F39F21" />

                <stop offset="0.430858" stop-color="#FD7013" />

                <stop offset="1" stop-color="#5072FB" />

            </linearGradient>

            <linearGradient id="paint1_linear_53684_17896" x1="-114.634" y1="255.39" x2="-126.177" y2="154.864" gradientUnits="userSpaceOnUse">

                <stop stop-color="#FD7013" />

                <stop offset="1" stop-color="#F21543" />

            </linearGradient>

        </defs>

    </svg>

</div>

<?php get_footer(); ?>

