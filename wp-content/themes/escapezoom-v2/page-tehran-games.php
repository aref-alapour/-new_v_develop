<?php get_header(); ?>

<style>
    .ads-content-text {
        text-align: justify !important;
        text-align-last: right !important;
        hyphens: none !important;
        word-spacing: -0.05em !important;
        letter-spacing: 0 !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
        direction: rtl;
        line-height: 36px;
    }

    @media (min-width: 1024px) {
        .ads-content-text {
            text-align: justify !important;
            text-align-last: right !important;
            word-spacing: -0.05em !important;
            letter-spacing: 0 !important;

        }
    }
</style>

<?php
// Check if ADS landing is active
$ads_active = get_option('ads_landing_active', 0);

if (!$ads_active) {
    // Show message that no campaign is running
?>
    <div class="max-w-2xl mx-auto mb-18 mt-[200px] text-center bg-white rounded-lg shadow-lg p-10">
        <div class="mb-6">
            <svg class="w-24 h-24 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">کمپینی در حال اجرا نیست</h2>
    </div>
<?php
    get_footer();
    return;
}

// Get ADS landing settings
$ads_title = get_option('ads_landing_title', '');
$ads_content = get_option('ads_landing_content', '');
$hero_bg_desktop_raw = get_option('ads_landing_hero_bg_desktop', '');
$hero_bg_mobile_raw = get_option('ads_landing_hero_bg_mobile', '');
$carousels = get_option('ads_landing_carousels', []);

// Convert attachment IDs to URLs
$hero_bg_desktop = '';
if (!empty($hero_bg_desktop_raw)) {
    if (is_numeric($hero_bg_desktop_raw)) {
        $hero_bg_desktop = wp_get_attachment_url((int)$hero_bg_desktop_raw);
    } else {
        $hero_bg_desktop = $hero_bg_desktop_raw; // Fallback for URLs
    }
}

$hero_bg_mobile = '';
if (!empty($hero_bg_mobile_raw)) {
    if (is_numeric($hero_bg_mobile_raw)) {
        $hero_bg_mobile = wp_get_attachment_url((int)$hero_bg_mobile_raw);
    } else {
        $hero_bg_mobile = $hero_bg_mobile_raw; // Fallback for URLs
    }
}

// Use page title/content if ADS settings are empty
if (empty($ads_title)) {
    $ads_title = get_the_title();
}
if (empty($ads_content)) {
    $ads_content = get_the_content();
}
?>


<!-- Hero Section -->
<?php if (!empty($hero_bg_desktop) || !empty($hero_bg_mobile)): ?>
    <div class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw]">
        <?php if (!empty($hero_bg_mobile)): ?>
            <img src="<?php echo esc_url($hero_bg_mobile); ?>" alt="" class="lg:hidden w-full h-auto object-cover">
        <?php endif; ?>
        <?php if (!empty($hero_bg_desktop)): ?>
            <img src="<?php echo esc_url($hero_bg_desktop); ?>" alt="" class="max-lg:hidden w-full h-auto object-cover">
        <?php endif; ?>
        <div class="container mx-auto">
            <?php if (!empty($ads_title) || !empty($ads_content)): ?>
                <div class="absolute top-15 lg:top-24 max-w-[600px] mx-auto px-8">
                    <div>
                        <?php if (!empty($ads_title)): ?>
                            <h1 class="text-32 lg:text-52 font-extrabold w-full leading-snug drop-shadow-lg"><?php echo esc_html($ads_title); ?></h1>
                        <?php endif; ?>
                        <?php if (!empty($ads_content)): ?>
                            <div class="text-18 font-medium w-full mt-2.5 text-justify ads-content-text">
                                <?php echo wp_kses_post($ads_content); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Fallback if no hero background -->
    <div class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] bg-gradient-to-br from-primary-500 to-primary-700 py-20">
        <div class="container mx-auto px-8 text-center">
            <?php if (!empty($ads_title)): ?>
                <h1 class="text-32 lg:text-52 font-extrabold text-black leading-snug"><?php echo esc_html($ads_title); ?></h1>
            <?php endif; ?>
            <?php if (!empty($ads_content)): ?>
                <div class="text-18 font-medium text-black text-justify ads-content-text mt-2.5">
                    <?php echo wp_kses_post($ads_content); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- First Purchase Discount Box -->
<section class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] overflow-hidden my-3 lg:my-6">
    <div class="container mx-auto px-4 sm:px-6 md:px-8">
        <div id="first-purchase-discount-box" class="relative bg-gradient-to-br from-[#FF6B47] via-[#FD6A2E] to-[#D75602] rounded-lg lg:rounded-2xl p-3 lg:p-5 shadow-xl overflow-hidden transform transition-all duration-500 hover:scale-[1.02]">
            <!-- Animated Background Elements -->
            <div class="hidden lg:block absolute top-0 right-0 w-36 h-36 bg-white opacity-10 rounded-full -mr-18 -mt-18 animate-pulse"></div>
            <div class="hidden lg:block absolute bottom-0 left-0 w-28 h-28 bg-white opacity-10 rounded-full -ml-14 -mb-14 animate-pulse" style="animation-delay: 1s;"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-2.5 lg:gap-4">
                <!-- Text Section -->
                <div class="flex-1 text-center lg:text-right">
                    <h3 class="text-white text-lg lg:text-2xl font-extrabold mb-0.5 lg:mb-1.5 animate-fade-in leading-tight">
                        تخفیف 1.500.000 ریالی اولین خرید شما
                    </h3>
                </div>

                <!-- Discount Code Section -->
                <div class="flex flex-row items-center gap-2.5 lg:gap-3 bg-white/20 backdrop-blur-sm rounded-lg lg:rounded-xl px-3 py-2 lg:px-6 lg:py-3.5 border border-white/30 w-full lg:w-auto justify-between lg:justify-center">
                    <div class="text-right flex-1 lg:flex-none">
                        <span class="text-white/80 text-xs lg:text-sm font-medium block mb-0 lg:mb-0.5">کد تخفیف:</span>
                        <code id="first-purchase-promo-code" class="text-white text-lg lg:text-2xl font-black select-all tracking-wider">wel150</code>
                    </div>
                    <button id="copy-first-purchase-promo" type="button" class="flex items-center justify-center gap-1.5 bg-white text-[#D75602] px-3 py-1.5 lg:px-4 lg:py-2 rounded-lg font-bold text-xs lg:text-base hover:bg-white/90 transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg shrink-0" title="کپی کد تخفیف">
                        <svg class="copy-icon-first-purchase w-4 h-4 lg:w-5 lg:h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none">
                            <path d="M11.2617 14.1757C11.2617 11.397 11.2617 10.0067 12.1254 9.14396C12.9881 8.28027 14.3784 8.28027 17.1572 8.28027H20.1049C22.8836 8.28027 24.274 8.28027 25.1367 9.14396C26.0003 10.0067 26.0003 11.397 26.0003 14.1757V19.0886C26.0003 21.8673 26.0003 23.2577 25.1367 24.1204C24.274 24.9841 22.8836 24.9841 20.1049 24.9841H17.1572C14.3784 24.9841 12.9881 24.9841 12.1254 24.1204C11.2617 23.2577 11.2617 21.8673 11.2617 19.0886V14.1757Z" stroke="currentColor" stroke-width="2" />
                            <path opacity="0.5" d="M10.2798 21.0534C9.49797 21.0534 8.74821 20.7428 8.1954 20.19C7.64259 19.6372 7.33203 18.8874 7.33203 18.1057V12.2102C7.33203 8.50492 7.33203 6.65178 8.48361 5.50119C9.63519 4.35059 11.4873 4.34961 15.1926 4.34961H19.1229C19.9047 4.34961 20.6545 4.66017 21.2073 5.21298C21.7601 5.76578 22.0707 6.51555 22.0707 7.29733" stroke="currentColor" stroke-width="2" />
                        </svg>
                        <svg class="check-icon-first-purchase hidden w-3.5 h-3.5 lg:w-4 lg:h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5" />
                        </svg>
                        <span class="copy-text-first-purchase text-xs lg:text-base">کپی</span>
                        <span class="copied-text-first-purchase hidden text-green-600 text-xs lg:text-base">کپی شد!</span>
                    </button>
                </div>
            </div>

            <!-- Decorative Sparkles - Hidden on mobile -->
            <div class="hidden lg:block absolute top-3 left-3 w-1.5 h-1.5 bg-white rounded-full animate-sparkle" style="animation-delay: 0s;"></div>
            <div class="hidden lg:block absolute top-6 right-10 w-1 h-1 bg-white rounded-full animate-sparkle" style="animation-delay: 0.5s;"></div>
            <div class="hidden lg:block absolute bottom-4 right-6 w-1.5 h-1.5 bg-white rounded-full animate-sparkle" style="animation-delay: 1s;"></div>
            <div class="hidden lg:block absolute bottom-8 left-10 w-1 h-1 bg-white rounded-full animate-sparkle" style="animation-delay: 1.5s;"></div>
        </div>
    </div>
</section>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes sparkle {

        0%,
        100% {
            opacity: 0;
            transform: scale(0);
        }

        50% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.8s ease-out;
    }

    .animate-sparkle {
        animation: sparkle 2s ease-in-out infinite;
    }

    #first-purchase-discount-box {
        animation: fade-in 0.8s ease-out;
    }
</style>

<!-- Carousels Section -->
<?php if (!empty($carousels) && is_array($carousels)): ?>
    <div class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] overflow-hidden">
        <div class="container mx-auto max-lg:pr-8 lg:pb-8" id="ads-carousels-container">
            <?php
            $section_counter = 1;
            $total_carousels = 0;
            // Count total valid carousels first
            foreach ($carousels as $carousel) {
                if (!empty($carousel['games']) && is_array($carousel['games']) && count($carousel['games']) > 0) {
                    $total_carousels++;
                }
            }
            $current_index = 0;
            foreach ($carousels as $carousel):
                if (empty($carousel['games']) || !is_array($carousel['games']) || count($carousel['games']) === 0) {
                    continue;
                }
                $current_index++;
                $is_last = ($current_index === $total_carousels);

            ?>
                <?php
                $carousel_bg_url = '';
                $carousel_bg_style = '';
                if (!empty($carousel['background'])) {
                    if (is_numeric($carousel['background'])) {
                        $carousel_bg_url = wp_get_attachment_url((int)$carousel['background']);
                    } else {
                        $carousel_bg_url = $carousel['background']; // Fallback for URLs
                    }
                }
                if (!empty($carousel_bg_url)) {
                    $carousel_bg_style = 'background-image: url(' . esc_url($carousel_bg_url) . '); background-size: contain; background-position: top center; background-repeat: no-repeat;';
                }
                ?>
                <section class="max-w-full py-2 md:py-3 lg:py-4 md:pb-10 lg:pb-12 relative" data-section="<?php echo $section_counter; ?>" style="<?php echo $carousel_bg_style; ?>">
                    <div class="mb-6 md:mb-8">
                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="39" viewBox="0 0 33 39" fill="none">
                            <g filter="url(#filter0_f_22_67582)">
                                <path d="M21.3214 32.5287C23.6191 29.0254 27.4643 22.2419 25.7411 18.3295C24.7877 16.1647 22.7067 14.6023 19.9559 13.986C17.2051 13.3697 14.0099 13.7499 11.0731 15.0431C8.13634 16.3362 5.6986 18.4364 4.29616 20.8815C2.89373 23.3266 2.64148 25.9165 3.5949 28.0812C5.31805 31.9937 12.9169 33.7379 17.0546 34.4075C17.8118 34.5353 18.6652 34.4143 19.4521 34.0678C20.2391 33.7213 20.9045 33.1734 21.3214 32.5287Z" fill="url(#paint0_linear_22_67582)" fill-opacity="0.8" />
                            </g>
                            <path d="M22.3512 31.6064C26.3884 27.9489 30.7917 21.8657 30.7917 15.0084C30.7917 11.2143 29.3275 7.57549 26.7212 4.8926C24.1148 2.20972 22.6646 0.0078125 18.9787 0.0078125C15.2928 0.0078125 10.9875 1.04983 6.81818 5.56522C4.21185 8.24811 3.34375 11.1492 3.34375 14.9434C3.34375 21.8007 12.7515 32.8981 17.2414 34.3943C18.2838 34.7416 18.6859 32.6576 19.6735 32.6576C20.6612 32.6576 21.6143 32.2834 22.3512 31.6064Z" fill="#D75602" />
                            <g filter="url(#filter1_i_22_67582)">
                                <path d="M21.2351 33.6388C25.1715 29.8289 32.1747 22.0451 32.1747 14.902C32.1747 10.9498 30.7471 7.15937 28.2059 4.3647C25.6648 1.57003 22.2182 0 18.6245 0C15.0307 0 11.5842 1.57003 9.043 4.3647C6.50183 7.15937 5.07422 10.9498 5.07422 14.902C5.07422 22.0451 12.0752 29.8289 16.0138 33.6388C16.7322 34.3441 17.6615 34.7338 18.6245 34.7338C19.5874 34.7338 20.5167 34.3441 21.2351 33.6388Z" fill="url(#paint1_linear_22_67582)" />
                            </g>
                            <g filter="url(#filter2_dd_22_67582)">
                                <path d="M26.5543 25.3712C25.0553 22.7224 23.5569 20.073 22.0484 17.4076C22.6673 16.7742 23.1216 16.0534 23.3692 15.2049C24.1922 12.3827 22.4735 9.41069 19.6794 8.83072C16.8154 8.23617 14.1166 10.203 13.7595 13.1452C13.3392 16.6098 16.4046 19.4215 19.7699 18.6529C19.9365 18.6147 20.0562 18.5821 20.163 18.7757C20.7146 19.7712 21.2791 20.7584 21.8382 21.7498C21.8593 21.7872 21.8681 21.833 21.8946 21.91C21.8001 21.9156 21.7273 21.9253 21.6546 21.9239C20.3977 21.9086 19.1367 21.9551 17.8845 21.8636C14.4362 21.6117 11.4504 18.8409 10.7982 15.3582C9.95955 10.8808 12.723 6.66413 17.1037 5.73866C21.5485 4.79932 25.9748 8.01625 26.5624 12.6179C26.6094 12.9835 26.6379 13.3547 26.6386 13.7231C26.6441 17.504 26.642 21.2843 26.6413 25.0652C26.6413 25.1637 26.6298 25.2629 26.6237 25.3615C26.6005 25.3649 26.5774 25.3677 26.5543 25.3712Z" fill="url(#paint2_linear_22_67582)" />
                                <path d="M19.1217 9.89563C18.3368 10.683 17.6111 11.4094 16.8875 12.1378C16.6535 12.373 16.41 12.5999 16.1971 12.8531C16.0006 13.0869 15.838 13.3512 15.604 13.6821C15.4258 13.6516 15.1313 13.6003 14.8396 13.5503C14.9042 11.3275 16.9256 9.61119 19.1217 9.89563Z" fill="url(#paint3_linear_22_67582)" />
                            </g>
                            <defs>
                                <filter id="filter0_f_22_67582" x="1.02735" y="11.6973" width="27.1445" height="24.7598" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                                    <feGaussianBlur stdDeviation="1" result="effect1_foregroundBlur_22_67582" />
                                </filter>
                                <filter id="filter1_i_22_67582" x="5.07422" y="0" width="27.6016" height="34.7334" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dx="0.5" />
                                    <feGaussianBlur stdDeviation="0.3" />
                                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
                                    <feColorMatrix type="matrix" values="0 0 0 0 0.843137 0 0 0 0 0.337255 0 0 0 0 0.00784314 0 0 0 1 0" />
                                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_22_67582" />
                                </filter>
                                <filter id="filter2_dd_22_67582" x="5.64844" y="0.575195" width="25.9922" height="29.7959" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset />
                                    <feGaussianBlur stdDeviation="2.5" />
                                    <feComposite in2="hardAlpha" operator="out" />
                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_22_67582" />
                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dx="-1" dy="1" />
                                    <feGaussianBlur stdDeviation="0.5" />
                                    <feComposite in2="hardAlpha" operator="out" />
                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                                    <feBlend mode="normal" in2="effect1_dropShadow_22_67582" result="effect2_dropShadow_22_67582" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_22_67582" result="shape" />
                                </filter>
                                <linearGradient id="paint0_linear_22_67582" x1="25.8189" y1="32.2655" x2="9.65729" y2="18.8933" gradientUnits="userSpaceOnUse">
                                    <stop />
                                    <stop offset="1" stop-opacity="0" />
                                </linearGradient>
                                <linearGradient id="paint1_linear_22_67582" x1="18.4388" y1="10.1806" x2="10.947" y2="38.311" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FC6F13" />
                                    <stop offset="1" stop-color="#D75602" />
                                </linearGradient>
                                <linearGradient id="paint2_linear_22_67582" x1="16.399" y1="16.3731" x2="14.6677" y2="20.8822" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="white" />
                                    <stop offset="1" stop-color="#DBDBDB" />
                                </linearGradient>
                                <linearGradient id="paint3_linear_22_67582" x1="16.399" y1="16.3731" x2="14.6677" y2="20.8822" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="white" />
                                    <stop offset="1" stop-color="#DBDBDB" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <h2 class="text-center text-28">
                            <?php if (!empty($carousel['subtitle'])): ?>
                                <span class="font-medium inline-block"><?php echo esc_html($carousel['subtitle']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($carousel['title'])): ?>
                                <span class="font-black inline-block"><?php echo esc_html($carousel['title']); ?></span>
                            <?php else: ?>
                                <span class="font-black">کروسل <?php echo $section_counter; ?></span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div class="relative overflow-hidden embla_normal horizontal dragFree">
                        <div class="embla__viewport">
                            <div id="ads-carousel-<?php echo $section_counter; ?>-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12 last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[190px] child:py-2.5">
                                <div class="ez-loader">
                                    <span class="ez-dot ez-dot-1"></span>
                                    <span class="ez-dot ez-dot-2"></span>
                                    <span class="ez-dot ez-dot-3"></span>
                                    <span class="ez-dot ez-dot-4"></span>
                                </div>
                            </div>
                            <button class="embla__button embla__button--prev <?php echo $section_counter; ?>-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                                    <g clip-path="url(#arrow_aa)">
                                        <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                        <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                        <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="arrow_aa">
                                            <path fill="#fff" d="M0 0h30v113H0z"></path>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                            <button class="embla__button embla__button--next <?php echo $section_counter; ?>-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                                    <g clip-path="url(#arrow_aa)">
                                        <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                        <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                        <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="arrow_aa">
                                            <path fill="#fff" d="M0 0h30v113H0z"></path>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>
                <?php if (!$is_last): ?>
                    <!-- Divider between carousels -->
                    <div class="mx-auto mb-8 md:mb-10 lg:mb-12 max-w-full px-4">
                        <div class="h-[2px] bg-gradient-to-r from-transparent via-gray-300 to-transparent rounded-full" style="box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15), 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 50px;"></div>
                    </div>
                <?php endif; ?>
            <?php
                $section_counter++;
            endforeach;
            ?>
        </div>
    </div>
<?php endif; ?>

<!-- Call-Z Banner -->
<section class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] mb-25 md:mb-16 lg:mb-20 lg:mt-8">
    <div class="container mx-auto px-4 sm:px-6 md:px-8">
        <a href="tel:02191307900" class="block w-full">
            <picture>
                <source media="(min-width: 1024px)" srcset="<?php echo esc_url(Theme_ASSET_URL . 'images/call-z-lg.jpg'); ?>" />
                <img class="w-full h-auto rounded-[14px] lg:rounded-[20px]" src="<?php echo esc_url(Theme_ASSET_URL . 'images/call-z-sm.jpg'); ?>" alt="تماس با اسکیپ زوم" />
            </picture>
        </a>
    </div>
</section>

<!-- Games from Tehran -->
<?php
// Category IDs to filter (15, 913, 1147, 1074)
$category_ids = [15, 913, 1147, 1074];
?>

<div class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] overflow-hidden">
    <div class="container mx-auto max-lg:pr-8 lg:pb-8">
        <!-- Tehran Games Section -->
        <section class="max-w-full py-4 md:py-5 lg:py-9 mt-7.5 lg:mt-12">
            <div class="mb-6 md:mb-8">
                <svg xmlns="http://www.w3.org/2000/svg" width="33" height="39" viewBox="0 0 33 39" fill="none">
                    <g filter="url(#filter0_f_22_67582)">
                        <path d="M21.3214 32.5287C23.6191 29.0254 27.4643 22.2419 25.7411 18.3295C24.7877 16.1647 22.7067 14.6023 19.9559 13.986C17.2051 13.3697 14.0099 13.7499 11.0731 15.0431C8.13634 16.3362 5.6986 18.4364 4.29616 20.8815C2.89373 23.3266 2.64148 25.9165 3.5949 28.0812C5.31805 31.9937 12.9169 33.7379 17.0546 34.4075C17.8118 34.5353 18.6652 34.4143 19.4521 34.0678C20.2391 33.7213 20.9045 33.1734 21.3214 32.5287Z" fill="url(#paint0_linear_22_67582)" fill-opacity="0.8" />
                    </g>
                    <path d="M22.3512 31.6064C26.3884 27.9489 30.7917 21.8657 30.7917 15.0084C30.7917 11.2143 29.3275 7.57549 26.7212 4.8926C24.1148 2.20972 22.6646 0.0078125 18.9787 0.0078125C15.2928 0.0078125 10.9875 1.04983 6.81818 5.56522C4.21185 8.24811 3.34375 11.1492 3.34375 14.9434C3.34375 21.8007 12.7515 32.8981 17.2414 34.3943C18.2838 34.7416 18.6859 32.6576 19.6735 32.6576C20.6612 32.6576 21.6143 32.2834 22.3512 31.6064Z" fill="#D75602" />
                    <g filter="url(#filter1_i_22_67582)">
                        <path d="M21.2351 33.6388C25.1715 29.8289 32.1747 22.0451 32.1747 14.902C32.1747 10.9498 30.7471 7.15937 28.2059 4.3647C25.6648 1.57003 22.2182 0 18.6245 0C15.0307 0 11.5842 1.57003 9.043 4.3647C6.50183 7.15937 5.07422 10.9498 5.07422 14.902C5.07422 22.0451 12.0752 29.8289 16.0138 33.6388C16.7322 34.3441 17.6615 34.7338 18.6245 34.7338C19.5874 34.7338 20.5167 34.3441 21.2351 33.6388Z" fill="url(#paint1_linear_22_67582)" />
                    </g>
                    <g filter="url(#filter2_dd_22_67582)">
                        <path d="M26.5543 25.3712C25.0553 22.7224 23.5569 20.073 22.0484 17.4076C22.6673 16.7742 23.1216 16.0534 23.3692 15.2049C24.1922 12.3827 22.4735 9.41069 19.6794 8.83072C16.8154 8.23617 14.1166 10.203 13.7595 13.1452C13.3392 16.6098 16.4046 19.4215 19.7699 18.6529C19.9365 18.6147 20.0562 18.5821 20.163 18.7757C20.7146 19.7712 21.2791 20.7584 21.8382 21.7498C21.8593 21.7872 21.8681 21.833 21.8946 21.91C21.8001 21.9156 21.7273 21.9253 21.6546 21.9239C20.3977 21.9086 19.1367 21.9551 17.8845 21.8636C14.4362 21.6117 11.4504 18.8409 10.7982 15.3582C9.95955 10.8808 12.723 6.66413 17.1037 5.73866C21.5485 4.79932 25.9748 8.01625 26.5624 12.6179C26.6094 12.9835 26.6379 13.3547 26.6386 13.7231C26.6441 17.504 26.642 21.2843 26.6413 25.0652C26.6413 25.1637 26.6298 25.2629 26.6237 25.3615C26.6005 25.3649 26.5774 25.3677 26.5543 25.3712Z" fill="url(#paint2_linear_22_67582)" />
                        <path d="M19.1217 9.89563C18.3368 10.683 17.6111 11.4094 16.8875 12.1378C16.6535 12.373 16.41 12.5999 16.1971 12.8531C16.0006 13.0869 15.838 13.3512 15.604 13.6821C15.4258 13.6516 15.1313 13.6003 14.8396 13.5503C14.9042 11.3275 16.9256 9.61119 19.1217 9.89563Z" fill="url(#paint3_linear_22_67582)" />
                    </g>
                    <defs>
                        <filter id="filter0_f_22_67582" x="1.02735" y="11.6973" width="27.1445" height="24.7598" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                            <feGaussianBlur stdDeviation="1" result="effect1_foregroundBlur_22_67582" />
                        </filter>
                        <filter id="filter1_i_22_67582" x="5.07422" y="0" width="27.6016" height="34.7334" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dx="0.5" />
                            <feGaussianBlur stdDeviation="0.3" />
                            <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0.843137 0 0 0 0 0.337255 0 0 0 0 0.00784314 0 0 0 1 0" />
                            <feBlend mode="normal" in2="shape" result="effect1_innerShadow_22_67582" />
                        </filter>
                        <filter id="filter2_dd_22_67582" x="5.64844" y="0.575195" width="25.9922" height="29.7959" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset />
                            <feGaussianBlur stdDeviation="2.5" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                            <feBlend mode="normal" in="BackgroundImageFix" result="effect1_dropShadow_22_67582" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dx="-1" dy="1" />
                            <feGaussianBlur stdDeviation="0.5" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                            <feBlend mode="normal" in="effect1_dropShadow_22_67582" result="effect2_dropShadow_22_67582" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_22_67582" result="shape" />
                        </filter>
                        <linearGradient id="paint0_linear_22_67582" x1="25.8189" y1="32.2655" x2="9.65729" y2="18.8933" gradientUnits="userSpaceOnUse">
                            <stop />
                            <stop offset="1" stop-opacity="0" />
                        </linearGradient>
                        <linearGradient id="paint1_linear_22_67582" x1="18.4388" y1="10.1806" x2="10.947" y2="38.311" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FC6F13" />
                            <stop offset="1" stop-color="#D75602" />
                        </linearGradient>
                        <linearGradient id="paint2_linear_22_67582" x1="16.399" y1="16.3731" x2="14.6677" y2="20.8822" gradientUnits="userSpaceOnUse">
                            <stop stop-color="white" />
                            <stop offset="1" stop-color="#DBDBDB" />
                        </linearGradient>
                        <linearGradient id="paint3_linear_22_67582" x1="16.399" y1="16.3731" x2="14.6677" y2="20.8822" gradientUnits="userSpaceOnUse">
                            <stop stop-color="white" />
                            <stop offset="1" stop-color="#DBDBDB" />
                        </linearGradient>
                    </defs>
                </svg>
                <h2 class="text-center text-28">
                    <span class="font-black">کلیه بازی‌های تهران</span>
                </h2>
            </div>
            <div id="tehran-games-container">
                <div class="ez-loader">
                    <span class="ez-dot ez-dot-1"></span>
                    <span class="ez-dot ez-dot-2"></span>
                    <span class="ez-dot ez-dot-3"></span>
                    <span class="ez-dot ez-dot-4"></span>
                </div>
            </div>
            <div id="tehran-games-load-more" class="flex items-center justify-center gap-2 mt-8 cursor-pointer" style="display: none;">
                <p class="text-[#3F7FF5] font-bold">مشاهده بیشتر</p>
                <svg width="11" height="6" viewBox="0 0 11 6" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                    <path d="M1.5 1.5L4.9 4.05C5.25556 4.31667 5.74444 4.31667 6.1 4.05L9.5 1.5" stroke="#3F7FF5" stroke-width="3" stroke-linecap="round" />
                </svg>
            </div>
        </section>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Copy first purchase promo code functionality
        $('#copy-first-purchase-promo').on('click', function() {
            const promoCode = $('#first-purchase-promo-code').text();
            const $btn = $(this);
            const $copyIcon = $btn.find('.copy-icon-first-purchase');
            const $checkIcon = $btn.find('.check-icon-first-purchase');
            const $copyText = $btn.find('.copy-text-first-purchase');
            const $copiedText = $btn.find('.copied-text-first-purchase');

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(promoCode).then(function() {
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
                }).catch(function(err) {
                    console.error('Failed to copy:', err);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = promoCode;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
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
                } catch (err) {
                    console.error('Failed to copy:', err);
                }
                document.body.removeChild(textArea);
            }
        });

        // Base URL for web service
        let baseUrlWebService = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler"';
        if (location.hostname === 'localhost') {
            baseUrlWebService = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler"';
        }

        // Loader styles
        const loaderStyles = `
        <style id="ez-loader-styles">
            .ez-loader { direction: ltr; display: flex; justify-content: center; align-items: center; gap: 8px; padding: 40px; }
            .ez-dot { width: 14px; height: 14px; border-radius: 50%; display: inline-block; animation: ez-bounce 1.2s infinite ease-in-out both; }
            .ez-dot-1 { background: #2D3748; animation-delay: -0.32s; }
            .ez-dot-2 { background: #FD6A2E; animation-delay: -0.16s; }
            .ez-dot-3 { background: #FFEDE6; animation-delay: 0s; }
            .ez-dot-4 { background: #FF6B47; animation-delay: 0.16s; }
            @keyframes ez-bounce {
                0%, 80%, 100% { transform: scale(0); }
                40% { transform: scale(1); }
            }
            .ads-content-text {
                text-align-last: right;
                word-break: break-word;
                overflow-wrap: break-word;
            }
            @media (min-width: 1024px) {
                .ads-content-text {
                    text-align-last: justify;
                    word-spacing: -0.02em;
                    letter-spacing: 0.01em;
                }
            }
        </style>
    `;
        if (!document.getElementById('ez-loader-styles')) {
            $('head').append(loaderStyles);
        }

        // Function to initialize Embla carousel
        function initializeEmbla(sliderId) {
            if (typeof window.EmblaCarousel === 'undefined') return;

            const carousel = document.querySelector(`#${sliderId}`).closest('.embla_normal');
            if (!carousel) return;

            const viewportNode = carousel.querySelector('.embla__viewport');
            const prevBtn = carousel.querySelector('.embla__button--prev');
            const nextBtn = carousel.querySelector('.embla__button--next');

            if (!viewportNode) return;

            const embla = window.EmblaCarousel(viewportNode, {
                axis: 'x',
                dragFree: true,
                direction: 'rtl',
                align: 'start'
            });

            const updateButtons = () => {
                const isWide = window.innerWidth > 720;
                if (prevBtn) prevBtn.style.display = isWide && embla.canScrollPrev() ? 'block' : 'none';
                if (nextBtn) nextBtn.style.display = isWide && embla.canScrollNext() ? 'block' : 'none';
            };

            embla.on('select', updateButtons);
            embla.on('reInit', updateButtons);
            updateButtons();

            if (prevBtn) prevBtn.addEventListener('click', () => embla.scrollPrev());
            if (nextBtn) nextBtn.addEventListener('click', () => embla.scrollNext());

            window.addEventListener('resize', updateButtons);
        }

        // Load carousels
        <?php
        $section_counter = 1;
        foreach ($carousels as $carousel):
            if (empty($carousel['games']) || !is_array($carousel['games']) || count($carousel['games']) === 0) {
                continue;
            }
            // Shuffle games array to randomize order on each page load
            $shuffled_games = $carousel['games'];
            shuffle($shuffled_games);
            $product_ids = array_map('intval', $shuffled_games);
        ?>
                (function(counter, ids) {
                    $.ajax({
                        type: 'POST',
                        url: baseUrlWebService,
                        data: {
                            "type": "get_by_products_id",
                            "data": {
                                "products_id": ids,
                                "format": "html_swiper"
                            }
                        },
                        dataType: "json",
                        success: function(productsHtml) {
                            $(`#ads-carousel-${counter}-slider`).html(productsHtml);
                            console.log(`✅ ADS Carousel ${counter} loaded`);

                            // Initialize Embla for this slider
                            setTimeout(() => {
                                initializeEmbla(`ads-carousel-${counter}-slider`);
                            }, 100);
                        },
                        error: function(xhr, status, error) {
                            console.error(`❌ Error loading ADS carousel ${counter}:`, error);
                            $(`#ads-carousel-${counter}-slider`).html('<p>خطا در بارگذاری</p>');
                        }
                    });
                })(<?php echo $section_counter; ?>, <?php echo json_encode($product_ids); ?>);
        <?php
            $section_counter++;
        endforeach;
        ?>

        // Variables for pagination
        let tehranGamesCurrentPage = 1;
        let tehranGamesTotalPages = 1;
        let tehranGamesLoading = false;

        // Function to load Tehran games
        function loadTehranGames(page = 1, append = false) {
            if (tehranGamesLoading) return;
            tehranGamesLoading = true;

            if (!append) {
                $('#tehran-games-load-more').hide();
            } else {
                $('#tehran-games-load-more').find('p').text('در حال بارگذاری...');
            }

            $.ajax({
                type: 'POST',
                url: baseUrlWebService,
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "params": {
                            "city_id": <?php echo json_encode($category_ids); ?>
                        },
                        "image_type": "url",
                        "limit": 50,
                        "page": page,
                        "max_num_pages": true,
                        "format": "html_list",
                        "sort_type": "popular",
                        "unpin_ads": false,
                        "badge_ads": false,
                        "random": false,
                        "random_memory": "",
                        "show_more": 0
                    }
                },
                dataType: "json",
                success: function(response) {
                    tehranGamesLoading = false;

                    // Debug log
                    console.log('Response:', response);

                    let productsHtml = null;
                    if (typeof response === 'string') {
                        productsHtml = response;
                    } else if (response && response.products) {
                        productsHtml = response.products;
                    }

                    // Check if productsHtml is valid (string with content)
                    const hasProducts = productsHtml && (typeof productsHtml === 'string' ? productsHtml.trim().length > 0 : productsHtml.length > 0);

                    // Get total pages
                    if (response && response.max_num_pages) {
                        tehranGamesTotalPages = parseInt(response.max_num_pages);
                        console.log('Total pages from response:', tehranGamesTotalPages);
                    } else if (hasProducts && !append) {
                        // Fallback: if we got products and limit is 50, assume there might be more
                        tehranGamesTotalPages = 2; // Assume at least 2 pages if we got products
                        console.log('Total pages fallback:', tehranGamesTotalPages);
                    }

                    if (hasProducts) {
                        if (!append) {
                            // First load - create grid
                            $('#tehran-games-container').empty().append('<section id="tehran-games-grid" class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 child:box-content gap-6"></section>');
                            $('#tehran-games-container #tehran-games-grid').append(productsHtml);
                            tehranGamesCurrentPage = 1;
                        } else {
                            // Load more - append to existing grid
                            $('#tehran-games-grid').append(productsHtml);
                            tehranGamesCurrentPage = page;
                        }

                        // Show/hide load more button
                        console.log('Current page:', tehranGamesCurrentPage, 'Total pages:', tehranGamesTotalPages);
                        if (tehranGamesCurrentPage < tehranGamesTotalPages && tehranGamesTotalPages > 1) {
                            $('#tehran-games-load-more').find('p').text('مشاهده بیشتر');
                            $('#tehran-games-load-more').show();
                            console.log('✅ Load more button shown');
                        } else {
                            $('#tehran-games-load-more').hide();
                            console.log('❌ Load more button hidden - Last page reached');
                        }

                        console.log('✅ Tehran games loaded - Page ' + tehranGamesCurrentPage + ' of ' + tehranGamesTotalPages);
                    } else {
                        if (!append) {
                            $('#tehran-games-container').html('<div class="rounded-xl px-16 py-12 border border-slate-100 text-center shadow-12">موردی یافت نشد.</div>');
                        }
                        $('#tehran-games-load-more').hide();
                        console.log('❌ No products found');
                    }
                },
                error: function(xhr, status, error) {
                    tehranGamesLoading = false;
                    console.error('❌ Error loading Tehran games:', error);
                    console.error('Response:', xhr.responseText);
                    if (!append) {
                        $('#tehran-games-container').html('<div class="rounded-xl px-16 py-12 border border-slate-100 text-center shadow-12">خطا در بارگذاری بازی‌ها</div>');
                    }
                    $('#tehran-games-load-more').find('p').text('مشاهده بیشتر');
                }
            });
        }

        // Initial load
        loadTehranGames(1, false);

        // Load more button click handler
        $('#tehran-games-load-more').on('click', function() {
            if (tehranGamesCurrentPage < tehranGamesTotalPages && !tehranGamesLoading) {
                loadTehranGames(tehranGamesCurrentPage + 1, true);
            }
        });
    });
</script>

<?php get_footer(); ?>