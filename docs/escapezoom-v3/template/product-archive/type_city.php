<?php
global $wpdb;

$user_id = get_current_user_id();

// امروز
$today = date('Y-m-d');
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps($dayAfterTomorrow);

$product_type = get_term($current_archive_obj->parent)->name;
$city_term = get_term($term_id);
$city_name = $city_term ? $city_term->name : '';

// حذف برخی پیشوندها از ابتدای نام دسته‌بندی شهر
$remove_prefixes = [
    'اتاق فرار',
    'لیزرتگ',
    'سینما ترس',
    'اتاق خشم',
    'فوتبال حبابی',
    'کافه بازی',
    'بردگیم',
    'برد گیم',
    'پینت بال',
];

foreach ($remove_prefixes as $prefix) {
    // اگر نام با پیشوند به همراه فاصله شروع شده باشد
    if (mb_strpos($city_name, $prefix . ' ') === 0) {
        $city_name = trim(mb_substr($city_name, mb_strlen($prefix)));
        break;
    }
    // اگر نام دقیقا با پیشوند (بدون فاصله بعدش) شروع شده باشد
    if (mb_strpos($city_name, $prefix) === 0) {
        $city_name = trim(mb_substr($city_name, mb_strlen($prefix)));
        break;
    }
}

$is_escaperoom = false;
if ($product_type == 'اتاق فرار')
    $is_escaperoom = true;

$posts_per_page = 50;
$sliderParams = [
    'slider_model' => 'normal',
];

// Slider config: false = local test images, true = production ACF
$use_production_slider = true;

$description = get_field('short-description', 'product_cat_' . $term_id);

switch ($city_name) {
    case "تهران":
        $city_name_en = "tehran";
        break;
    case "تبریز":
        $city_name_en = "tabriz";
        break;
    case "شیراز":
        $city_name_en = "shiraz";
        break;
    case "مشهد":
        $city_name_en = "mashhad";
        break;
    case "اصفهان":
        $city_name_en = "esfahan";
        break;
    case "کرج":
    default:
        $city_name_en = "other";
        break;
}

if ($product_type != 'سینما ترس') : ?>
    <div class="flex items-center justify-between my-3">
        <!--breadcrumb-->
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center gap-2">
                <li class="inline-flex items-center">
                    <a class="inline-flex items-center gap-1.5 font-medium text-9 text-slate-400 hover:text-primary-600 transition-colors duration-200" href="<?= home_url() ?>">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        <span>صفحه نخست</span>
                    </a>
                </li>
                <li class="inline-flex items-center gap-2">
                    <svg class="w-3 h-3 text-slate-300 rotate-180" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a class="font-medium text-9 text-slate-400 hover:text-primary-600 transition-colors duration-200" href="<?= get_term_link($current_archive_obj->parent, "product_cat") ?>">
                        <?= $product_type ?>
                    </a>
                </li>
                <li class="inline-flex items-center gap-2">
                    <svg class="w-3 h-3 text-slate-300 rotate-180" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium text-9 text-slate-600">
                        <?= $city_name ?>
                    </span>
                </li>
            </ol>
        </nav>
    </div>
    <section class="space-y-5 lg:space-y-8">
        <?php
        $category = get_queried_object()->term_id;
        if (get_field('icon', "product_cat_$category") || get_field('short-description', "product_cat_$category")) { ?>
            <div class="bg-slate-100/50 w-full flex items-center justify-between py-2.5 px-4 lg:px-12.5 rounded-14 lg:rounded-20">
                <div class="lg:flex lg:items-center lg:gap-x-5">
                    <h1 class="text-21 lg:text-27 text-textColor lg:pl-5 lg:border-l"><?= $product_type ?> <span class="font-black"><?= $city_name ?></span></h1>
                    <p class="text-11 lg:text-13 lg:leading-6 max-lg:hidden"><?= get_field('short-description', "product_cat_$category"); ?></p>
                </div>
                <?php if (get_field('icon', "product_cat_$category")) { ?>
                    <img src="<?= get_field('icon', "product_cat_$category")['url'] ?>" alt="" class="w-12 h-12 lg:w-16.5 lg:h-16.5">
                <?php } ?>
            </div>
        <?php } ?>
        <?php
        $slider_model = $sliderParams['slider_model'];

        // تنظیم اسلایدها بر اساس حالت
        if ($use_production_slider) {
            $sliderItems = get_field('slider', "product_cat_$category");
        } else {
            // تصاویر تست لوکال
            $theme_url = get_template_directory_uri();
            $sliderItems = [
                [
                    'title' => 'اسلاید 1',
                    'link' => home_url(),
                    'mobile-image' => ['url' => $theme_url . '/assets/images/slide-1-sm.jpg'],
                    'desktop-image' => ['url' => $theme_url . '/assets/images/slide-1-lg.jpg'],
                ],
                [
                    'title' => 'اسلاید 2',
                    'link' => home_url(),
                    'mobile-image' => ['url' => $theme_url . '/assets/images/slide-2-sm.jpg'],
                    'desktop-image' => ['url' => $theme_url . '/assets/images/slide-2-lg.jpg'],
                ],
            ];
        }

        if ($sliderItems): ?>
            <div class="relative embla_fade w-full rounded-14 lg:rounded-20 overflow-hidden <?= ($slider_model == 'wide') ? 'mt-7.5 lg:mt-10' : '' ?>">
                <div class="embla__viewport">
                    <div class="embla__container flex">
                        <?php
                        foreach ($sliderItems as $item):
                            $item_url = str_replace('https://escapezoom.ir', home_url(), $item['link']);
                            $parsed = parse_url($item_url);
                            $item_url = 'https://' . $_SERVER['HTTP_HOST'] . (isset($parsed['path']) ? $parsed['path'] : '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
                        ?>
                            <div class="embla__slide shrink-0 grow-0 basis-full" data-title="<?= @$item['title'] ?>">
                                <a class="block w-full" href="<?= $item_url ?>">
                                    <picture>
                                        <source media="(min-width: 1024px)" srcset="<?= $item['desktop-image']['url']; ?>" />
                                        <img class="w-full h-auto rounded-14 lg:rounded-20" src="<?= $item['mobile-image']['url']; ?>" alt="<?= @$item['title'] ?>" />
                                    </picture>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev absolute right-4 top-1/2 -translate-y-1/2 z-50 hidden md:flex items-center justify-center w-10 h-10 hover:scale-110 transition-transform" type="button" aria-label="Previous slide">
                    <svg width="12" height="18" viewBox="0 0 12 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.17676 14.7646L8.58791 9.3535C8.78317 9.15823 8.78317 8.84165 8.58791 8.64639L3.17676 3.23524" stroke="#90A1B9" stroke-width="5" stroke-linecap="round" />
                    </svg>
                </button>
                <button class="embla__button embla__button--next absolute left-4 top-1/2 -translate-y-1/2 z-50 hidden md:flex items-center justify-center w-10 h-10 hover:scale-110 transition-transform" type="button" aria-label="Next slide">
                    <svg width="12" height="18" viewBox="0 0 12 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.82324 14.7646L3.41209 9.3535C3.21683 9.15823 3.21683 8.84165 3.41209 8.64639L8.82324 3.23524" stroke="#90A1B9" stroke-width="5" stroke-linecap="round" />
                    </svg>
                </button>
                <div class="embla__dots absolute bottom-2 lg:bottom-3 left-0 right-0 mx-auto"></div>
            </div>
        <?php endif;
        ?>



    </section>
<?php endif;

/*===============================================================*/
// تبلیغات

// منطق گرفتن دیتا (همیشه اجرا می‌شود)
$params = [
    'city_id' => [$term_id],
];
$city_id_term[] = $term_id;

$args = [
    'source'    => 'typecity_page_ads',
    'params'    => $params,
];
$ads_products = ez_products_snapshot_swiper($args);

// نمایش بر اساس نوع محصول
if (!is_null($ads_products->products) and !empty($ads_products->products) and (strlen($ads_products->products) > 0)):
    if ($product_type == 'سینما ترس') {
        // نمایش مخصوص سینما ترس - بخش ترسناک و انیمیت شده?>

        <!-- Full Width Section -->
        <div class="horror-cinema-fullwidth w-screen right-1/2 left-1/2 -ml-50vw -mr-50vw relative overflow-hidden lg:mt-1">
            <section class="horror-cinema-section relative overflow-hidden py-8 md:py-12 lg:py-16">
                <!-- Animated Background with Halo Effect -->
                <div class="horror-bg-animated absolute inset-0"></div>
                <div class="horror-halo-effect absolute inset-0"></div>

                <!-- Decorative Shapes -->
                <div class="horror-shapes absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="horror-shape horror-shape-1"></div>
                    <div class="horror-shape horror-shape-2"></div>
                    <div class="horror-shape horror-shape-3"></div>
                    <div class="horror-shape horror-shape-4"></div>
                    <div class="horror-shape horror-shape-5"></div>
                </div>

                <!-- Floating Particles -->
                <div class="horror-particles absolute inset-0 opacity-40">
                    <div class="particle particle-1"></div>
                    <div class="particle particle-2"></div>
                    <div class="particle particle-3"></div>
                    <div class="particle particle-4"></div>
                    <div class="particle particle-5"></div>
                    <div class="particle particle-6"></div>
                    <div class="particle particle-7"></div>
                    <div class="particle particle-8"></div>
                </div>

                <!-- Ghost Shadows in Corners -->
                <div class="horror-ghost-shadows absolute inset-0 pointer-events-none">
                    <div class="ghost-shadow ghost-shadow-1"></div>
                    <div class="ghost-shadow ghost-shadow-2"></div>
                    <div class="ghost-shadow ghost-shadow-3"></div>
                    <div class="ghost-shadow ghost-shadow-4"></div>
                </div>

                <!-- Floating Ghosts Animation - Multiple Ghosts Moving Left to Right -->
                <div class="horror-floating-ghosts absolute inset-0 pointer-events-none z-10 overflow-hidden">
                    <!-- Ghost 1 - Black Surprised Ghost (Top) -->
                    <div class="horror-ghost-item horror-ghost-1">
                        <svg class="horror-ghost-svg" xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 429 431" width="429" height="431">
                            <style>
                                .s0 {
                                    fill: #ffffff
                                }
                            </style>
                            <path fill-rule="evenodd" class="s0" d="m150.81 9.75q1.68-0.03 3.41-0.06c14.18-0.07 23.54 4.23 33.84 13.81 8.68 9.02 13.34 19.35 16.74 31.22 2.49 6.79 7.01 10.08 13.2 13.28q3.08 1.28 6.19 2.5c9.75 3.98 13.94 11.29 18.23 20.58 3.62 8.8 4.96 17.29 6 26.69 1.19 10.06 2.58 19.48 9.58 27.23q0 0.98 0 2 1.99 1.03 4 2c1.14 0.56 1.14 0.56 2.31 1.13 8.05 2.62 14.47-0.6 21.69-4.13q3.97-2.23 7.81-4.67c2.19-1.33 2.19-1.33 5.63-2.77 3.97-2.42 4.97-6.38 6.14-10.7q0.8-3.48 1.48-6.98 0.26-1.25 0.52-2.54c0.96-4.72 1.76-9.44 2.42-14.21 2.02-13.63 6.55-26.55 17.58-35.45 8.84-6.15 18.32-10.96 28.42-14.68q1.27-0.48 2.58-0.97c8.55-2.91 17.01-3.21 25.54-0.03 2.74 1.45 4.26 3.4 5.88 6q-0.97 0.47-1.98 0.96c-8.26 4.42-11.94 9.64-15.27 18.29-2.04 10.2-3.04 20.17-3.23 30.56-0.18 5.57-0.73 11.25-3.52 16.19-2.11 1.25-2.11 1.25-4 2q0 0.98 0 2-0.98 0-2 0-0.41 1.04-0.83 2.12-1.17 2.87-2.43 5.7c-6.6 15.29-10.67 31.83-13.3 48.24-2.09 11.65-7.06 17.24-16.6 23.98q-1.4 0.97-2.84 1.96c-6.67 4.83-10.06 10.32-11.63 18.31-0.76 5.54-1.07 11.11-1.37 16.69-1.56-2.04-3.02-4.03-4.31-6.25-1.64-2.07-1.64-2.07-4.75-2.12-3.06 0.08-3.06 0.08-4.94 2.37-2.56 5.72-2.62 11.84-3 18q-1.05-1.02-2.13-2.06c-2.73-2.25-2.73-2.25-6.06-1.88-3.11 0.72-3.11 0.72-4.81 3.94-2.07 9.1 1.5 16.21 5.8 23.97 3.05 5.74 4.09 10.53 3.2 17.03-1.5 3.63-1.5 3.63-3 6-4-4-4-4-5.44-5.81-1.56-1.44-1.56-1.44-4.06-1.19-3.35 1.34-3.93 2.78-5.5 6-2.24 12.39 2.52 23.17 9.18 33.32q2 2.76 4.12 5.45c1.7 2.23 1.7 2.23 2.8 4.45 1.18 2.33 2.53 2.83 4.9 3.78q0 0.99 0 2c11.77 3.14 20.92-0.83 31.62-5.62 16.79-6.84 38.09-6.84 54.97 0.1 6.76 3.39 12.79 7.44 17.41 13.52q0 0.99 0 2c-0.84-0.29-0.84-0.29-1.69-0.59-14.88-5.08-26.61-6.5-41.31 0.59-8.97 5.62-16.24 13.97-22.56 22.38-7.18 7.72-17.88 10.69-28.13 11.12-6.1-0.12-8.81-1.49-13.31-5.5-5.23-1.74-11.12-2.12-16.32-0.09-3.39 2.01-5.52 3.89-7.97 7.04-2.69 3.3-5.88 5.05-9.71 6.8q-0.9 0.43-1.83 0.88c-8.82 4.2-16.49 6.55-26.3 6.68-1.02 0.03-1.02 0.03-2.06 0.06-6.94-0.01-11.81-2.74-16.81-7.37-9.19-9.93-15.45-23.76-15.43-37.23 0.5-4.35 1.76-7.82 3.43-11.83 2.75-6.63 4.43-12.7 3-19.94-1.44-2.77-1.44-2.77-3-5-4.01 1.38-6.75 3.31-10 6q0.21-3.63 0.44-7.25 0.06-1.02 0.12-2.07c0.21-3.35 0.59-6.44 1.44-9.68 0.44-6.32 0.49-11.61-3.38-16.87-1.56-1.36-1.56-1.36-3.59-0.91-3.02 1.16-3.78 3.42-5.08 6.24-1.77 4.72-2.87 9.62-3.95 14.54-2-1-2-1-2.63-2.7-0.44-2.74-0.43-5.29-0.36-8.06q0.02-1.67 0.05-3.39 0.06-3.51 0.15-7.01c0.11-8.46-0.66-17.01-6.21-23.84-2.33-1.33-2.33-1.33-5-1-2.92 2.45-4.89 4.83-7 8q-0.99 0-2 0-2.51-6.18-5-12.37-0.71-1.76-1.45-3.57-0.67-1.67-1.36-3.4-0.63-1.54-1.27-3.14c-0.92-2.52-0.92-2.52-0.92-4.52q-4.62-2.33-9.31-4.5-1.35-0.63-2.74-1.28c-6.35-2.63-11.93-3.45-18.7-3.59-10.38-0.43-18.88-2.26-26.54-9.75q-0.84-0.93-1.71-1.88-0.72-0.79-1.46-1.6c-3.95-5.26-4.89-11.53-6.02-17.88-2-10.63-9.3-16.45-16.88-23.62-6.66-6.48-13.17-14.36-13.78-24.08q0-3.89 0.12-7.78 0.05-2.94 0.09-5.87 0.08-4.58 0.2-9.17 0.1-4.45 0.15-8.9 0.05-1.36 0.1-2.75c0.05-6.44-1.28-9.49-5.52-14.35-4.24-3.98-8.72-7.4-14.69-7.69-4.54 0.36-7.54 2.23-11.31 4.69q-0.98 0-2 0c0.23-3.61 1.91-6.92 4.16-9.92q0.93 0.47 1.84 0.92c2.31-2.4 4.69-4.62 7.25-6.75 1.39-1.14 1.39-1.14 2.75-2.25q-0.99-0.99-1.96-1.96c8.38-6.33 16.04-7.86 26.96-7.04 13.84 2.53 26.73 9.28 36 20 4.24 6.93 7.19 13.11 6.92 21.29q-0.04 1.87-0.08 3.79-0.09 3.94-0.23 7.88c-0.13 8.06 0.49 14.41 5.39 21.04 2.51 2.33 5.05 4.26 8 6q0.85 0.56 1.73 1.13c6.23 2.39 15.69 2.05 22.02-0.04 4.69-2.77 8.2-6.7 9.84-11.98 0.45-2.32 0.55-4.46 0.57-6.83q0.01-1.39 0.03-2.82 0.01-1.49 0.02-3.03 0.04-3.2 0.09-6.4 0.06-5.03 0.1-10.06 0.05-4.87 0.12-9.73 0.01-1.48 0.01-3c0.17-9.34 3.04-16.76 7.09-25.05 2.98-6.11 4.4-10.58 4.63-17.38 0.37-5.65 1.67-8.98 4.66-13.68 1.86-3.64 1.71-7.17 1.09-11.13q-0.98-2.51-2-5 0.12-1.85 0.25-3.75c0.1-5.57-2.68-9.19-6.25-13.25-5.5-5.17-11.57-9.14-18.16-12.77q-0.91-0.61-1.84-1.23 0-0.98 0-2c6.59-1.18 13.13-1.22 19.81-1.25zm-128.81 87.92q0.84-0.7 1.67-1.34c-2.94 2.94-5.82 5.82-8.68 8.68 2.18-2.69 4.74-5.13 7.01-7.34zm159 50.33c0 4.08 0 8.04 0 12q-1.01 0-2 0c0-3.11-0.54-4.35-2-7q-1.01 0-2 0c-5.66 6.86-8.56 15.18-8 24q1.01 0 2 0c4.61-4.98 9.55-8.85 15.45-12.23 12.88-6.42 29.12-6.57 42.78-2.64q1.4 0.44 2.77 0.87 4.12 1.2 8.23 2.45 0.9 0.28 1.77 0.55c-0.33-1.72-0.33-1.72-1-4-3.72-6.69-7.87-11.83-15-15q-0.51 3.05-1 6c-1.06-3.19-2.25-6.13-4-9-11.27-6.12-27.43-1.77-38 4zm48.19-45.31q-1.11 1.17-2.19 2.31-1.4 1.08-2.75 2.13c-2.54 3.24-2.54 3.24-2.44 8.06 0.11 1.31 0.11 1.31 0.21 2.6 0.24 2.42 0.24 2.42-2.02 4.21-3.25-0.62-3.25-0.62-7-1-3.44 1.81-3.44 1.81-6 4q0 1.01 0 2-1.52 0.51-3 1c-1.19 2.06-1.19 2.06-2 4 2.69 0.8 5.25 1.49 8 2 7.01 0.44 11.97 0.39 17.52-4.44 4.67-4.92 5.93-10.39 5.83-17.02-0.05-1.28-0.05-1.28-0.1-2.54q-0.09-4.5-0.25-9c-2 0-2 0-3.81 1.69zm-76.19 14.31c1.62 8.68 4.5 16.62 11.75 22.06 6.47 1.87 12.64 1.67 18.73-1.25q1.37-0.7 2.71-1.37 2.41-1.22 4.81-2.44 0-1.01 0-2c-4.58-2.4-9.03-4.51-14-6q-1.01 1.52-2 3c-3.08 0.25-3.08 0.25-6 0-0.67-3.19-1.52-6.08-3-9-3.67-3.41-7.98-5.53-13-6q0 1.52 0 3zm-139-11q0.08 0.04 0.16 0.08 0.4-0.54 0.83-1.07-0.49 0.49-0.99 0.99zm10-10q-0.17 0.17-0.33 0.33 0.19-0.14 0.37-0.29-0.02-0.02-0.04-0.04z" />
                        </svg>
                    </div>
                    <!-- Ghost 2 - Black Angry Ghost (Middle) -->
                    <div class="horror-ghost-item horror-ghost-2">
                        <svg class="horror-ghost-svg" version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 305 329" width="305" height="329">
                            <style>
                                .s0 {
                                    fill: #faf8f8
                                }
                            </style>
                            <path fill-rule="evenodd" class="s0" d="m210 22q0 0.99 0 2 0.86 0.37 1.75 0.75c11.35 6.33 16.32 20.84 19.94 32.47 3.47 13.78 1.79 29.59-1.24 43.29-1.18 6.57-1.12 13.31 1.55 19.49 3.96 0 7.92 0 12 0q0-0.98 0-2 0.99 0 2 0 1.48-3.68 2.93-7.36c2.22-5.61 4.51-11.17 7.07-16.64q0.62-1.32 1.25-2.69c3.24-4.27 7.66-7.61 12.75-9.31 7.4-0.73 12.54 1.93 18.25 6.38 6.48 6.18 6.48 6.18 6.99 10.78 0.01 1.95-0.11 3.9-0.24 5.84q-1.97-0.49-4-1 0-1.48 0-3-1.48 0-3 0 0.49 1.08 1 2.19c1.01 2.83 1.17 4.84 1 7.81-1.77-0.1-2.18-0.12-3.48-0.51-0.9-0.72-1.23-1.48-2.08-3.49-1.44-3-1.44-3-3.44-5 0 3.06 0 6.03 0 9q0.32 0 0.64 0-0.01 0.31-0.03 0.62c-0.61 2.38-0.61 2.38-2.66 3.7q-0.96 0.34-1.95 0.68c-2.11-3.16-2.34-4.51-2.63-8.19q-0.01-0.2-0.03-0.37 0.33-0.22 0.66-0.44-0.51-2.03-1-4 0.08 1.06 0.15 2.08c0.1 1.24 0.11 1.37 0.19 2.36q-1.15 0.77-2.34 1.56c-0.73 3.07-0.73 3.07-1 6q-0.49 0-1 0 0-0.97 0-1.94c0.23-2.31 0.28-4.74 0.18-7.42q-0.09-1.34-0.18-2.64c0 3.42 0 6.75 0 10.06-0.54 5.51-2.09 10.3-5 15.94-0.67 1.35-0.67 1.35-1.35 2.73-3.9 7.53-8.71 14.4-13.65 21.27q-0.85 1.19-1.73 2.43c-5.04 6.89-11.22 12.38-17.55 18.07-2.65 2.43-5.18 4.95-7.72 7.5q-1.29 1.2-2.61 2.44c-5.91 5.61-10.25 11.07-14.37 18.17-3.26 5.47-7.02 10.58-10.79 15.71q-1.74 2.38-3.44 4.77c-8.2 11.26-17.17 20.77-27.79 28.93q0-0.51 0-1.02c-2.54 1.84-4.83 3.72-7 6q0.02 0 0.04 0.01-2.59 1.73-5.32 3.38c-4.52 2.76-8.52 5.72-12.34 9.43-1.38 1.18-1.38 1.18-3.38 1.18q-0.43 0.87-0.88 1.77-1.02 2.03-2.09 4.04c-2.68 5.08-4.34 10.18-5.91 15.69-4.16 13.76-4.16 13.76-9.12 17.5-3.5 1.09-5.57 1.54-9.03 0.23-7.76-4.84-11.5-17.1-13.52-25.51-0.51-3.1-0.61-6.06-0.66-9.19q-0.04-1.84-0.09-3.73-0.08-3.87-0.13-7.74-0.05-1.83-0.09-3.72c-0.03-1.68-0.03-1.68-0.06-3.39-0.46-3.23-1.16-4.64-3.42-6.95-2.11-0.57-2.11-0.57-4.38-0.25q-1.11 0.05-2.27 0.11c-4.52 2.19-6.51 6.91-8.23 11.46-1.01 3.21-1.9 6.45-2.79 9.7-1.62 5.56-3.65 9.49-7.33 13.98-3.16 1.58-5.5 1.34-9 1-6.88-3.5-10.09-8.95-13-16-3.61-15.33-1.13-32.23 4.95-46.5 1.86-4.43 3.43-8.97 5.05-13.5-2.64-0.66-5.28-1.32-8-2q0 0.98 0 2-1.5 1.5-3 3c-1.95 2.84-3.28 5.65-4.44 8.88-1.49 4.06-3.08 7.55-5.56 11.12q-0.79 0.27-1.33 0.45c1.11-0.92 1.26-1.12 2.33-2.45-2.25-1.12-2.25-1.12-8 0q0.19 0.38 0.37 0.75c-2.35-3.66-2.96-6.93-2.81-11.94 0.02-1.13 0.02-1.13 0.05-2.28 0.67-20.11 12.21-35.23 26.39-48.53q1.86-1.64 3.75-3.25c17.68-14.86 17.68-14.86 26.69-35.19-0.12-5.34-2.25-10.12-4.13-15.06-1.66-4.44-3.02-8.94-4.31-13.5q-0.98 0.74-2 1.5c-3.97 1.98-6.6 1.72-11 1.5q-0.49-1.97-1-4 0.84-0.45 1.71-0.92c4.71-2.49 4.71-2.49 7.29-6.89q0-1.08 0-2.19c-5.19 0.41-7.36 1.78-10.88 5.56q-1.16 1.24-2.36 2.51-0.87 0.95-1.76 1.93c-1.49-0.49-1.49-0.49-3-1-0.29-8.57-0.29-8.57 2-12q-0.98 0-2 0-0.4 0.86-0.81 1.75c-1.21 2.28-2.6 4.22-4.19 6.25q-1.97-0.98-4-2c0.04-1.99 0.19-3.54 0.57-4.92 1.28-2.02 1.47-2.22 3.16-3.9q0.96-0.95 1.89-1.87c0.97-0.95 0.97-0.95 1.92-1.88q0.74-0.73 1.46-1.43-1.01-0.51-2-1c-2.72 2.38-5.36 4.69-8 7q0.18 0.73 0.36 1.45-0.18 0.27-0.36 0.55-2.46-0.49-5-1c0.27-6.49 2.49-10.36 7-15 3.17-2.63 6.49-4.85 10-7q1.22-0.76 2.48-1.55c6.17-3.49 11.49-4.45 18.52-3.45 2.84 0.79 5.3 1.78 8 3q0 0.99 0 2 0.86 0.34 1.75 0.69c2.85 1.66 4.25 3.7 6.25 6.31q2.47 2.53 5 5 0.68 0.89 1.37 1.81c2.82 2.06 5.3 1.61 8.63 1.19 3.5-3.32 4.02-7.65 4.94-12.19 1.38-6.35 3.12-11.98 6.06-17.81q0.68-1.72 1.37-3.5c7.25-15.6 19.31-26.55 35.24-32.77 19.62-5.97 42.52-1.98 59.39 9.27zm-182 211q-0.34-0.43-0.65-0.85c1.19 0.51 1.53 0.64 2.55 0.75-0.49 0.06-1.03 0.08-1.9 0.1zm12.57-144.92q-0.25 0.41-0.57 0.92-0.32-1.27-0.64-2.55c2.32-3.36 5.11-5.25 8.64-7.45q-1.74 1.5-3.44 3.06c-2.22 2.2-3.39 3.89-3.99 6.02zm245.95 22.41c0.54 0.43 1.28 0.84 2.48 1.51q-2.96 0.49-6 1 0-0.99 0-2-0.67 0-1.36 0 0.06-1.07 0.11-2.13 0.09-1.39 0.17-2.75 0.04-1.08 0.08-2.12c1.19 2.94 1.19 2.94 3 6 0.63 0.21 1.12 0.36 1.52 0.49zm-138.52-44.12c-5.06 5.48-9.29 12.95-9.56 20.5q0.18 2.39 0.75 4.41-0.1-0.14-0.19-0.28-0.19 1.65-0.38 3.25c0.25 2.45 0.33 3.3 0.99 4.27-0.84-0.51-0.91-0.95-1.17-2.58q-0.22-0.99-0.44-1.94-1.52-0.51-3-1 0 1.52 0 3c1.91 2.94 3.74 4.82 6.62 6.81 5.4 1.9 10.94 2 16.38 0.19q0.51-1.02 1-2c-1.07 0.01-1.38 0.02-2 0.02q0-0.01 0-0.02-1.39-0.26-2.74-0.52c-0.59-0.12-0.99-0.2-1.32-0.27q2.04-0.2 4.18-0.9 0.96-0.66 1.88-1.31c2.31-1.6 4.08-3 6-5 5.3-6.46 6.65-11.82 6.41-20.12-0.63-4.46-1.67-6.27-5.16-9.13-6.58-3.54-12.57-1.87-18.25 2.62zm-8.81 24.91q1.58 2.35 3.02 4.8c2.53 2.72 4.87 2.96 8.48 3.67 1.18 0.24 1.57 0.32 2.25 0.46-2.95 0.3-5.8-0.15-8.75-1.34-2.65-2.26-4.17-4.69-5-7.59zm54.94-20.72c-4.38 6.38-4.38 6.38-4.3 21.23 0.29 5.52 1.36 8.21 5.36 12 1.31 0.88 2.11 1.41 2.99 1.67-1.05 0.43-2.09 0.93-3.18 1.54q1.01 0.79 2 1.56 0.38 0.19 0.76 0.34c1.23 0.69 2.54 1.11 4.21 1.18q0.94-0.09 1.79-0.21c1.31-0.13 2.68-0.44 4.24-0.87 1.09-0.6 1.97-1.21 2.71-1.88q0.64-0.5 1.29-1.12-0.08-0.04-0.16-0.08c0.84-1.06 1.5-2.32 2.16-3.92q-2.03 1.01-4 2-0.38-1.15-0.76-2.27c1.68-1.79 2.81-3.92 3.76-6.73 0.77-8.51 0.58-16.91-4.62-24-2.35-2.49-4.01-3.53-7.44-3.81-3.39 0.32-3.39 0.32-6.81 3.37zm4.05 34.9c1.38-0.56 2.79-1.01 4.38-1.46 3.52-0.84 3.52-0.84 6.44-3q0.12 0.37 0.24 0.73c-1.12 1.21-2.5 2.27-4.24 3.27-3.92 0.62-5.47 0.87-6.82 0.46zm-1.85 95.37q-0.17 1.1-0.33 2.17-1.01 0-2 0c-1.06 1.87-1.06 1.87-2 4q0.51 1.01 1 2c3.07-4.21 5.97-8.46 8.69-12.9 4.28-7 8.28-14.58 14.58-19.95q0.82-0.61 1.56-1.3 0 0 0 0 0 0 0 0 1.14-1.04 2-2.38c0.42-0.7 0.79-1.49 1.17-2.47q0 0 0 0-1.23 0.01-2.27 0.34c-9.71 2.83-20.53 22.56-22.4 30.49zm21.5-25.98c0.86-0.77 1.48-1.5 2-2.38q0.71-1.1 1.17-2.47 0 0 0 0-1.12 0-2.27 0.34c-3.62 1.15-5.62 5.01-6.73 8.66 1.49-0.97 2.95-1.86 4.27-2.85q0.24-0.2 0.48-0.4 0.6-0.46 1.08-0.9zm-60.83-73.83q0 0.48 0 0.98c-11.79 0.69-11.79 0.69-15.75-1.56-0.78-0.85-1.29-1.4-1.64-1.92 0.32 0.19 0.74 0.39 1.33 0.67q1.01 0.4 2.06 0.81 0.99 0.49 2 1 3.56 0.1 7.12 0.06 1.92-0.01 3.89-0.02c0.43-0.01 0.74-0.01 0.99-0.02zm15 10.98c-0.25 3.25-0.25 3.25 0 6q2.03 0 4 0c0.82-0.82 1.34-1.34 1.71-2q0.14 0 0.29 0 0.08-0.58 0.16-1.15c0.31-1.12 0.5-2.79 0.84-5.85q-0.41 2.9-0.84 5.85-0.18 0.67-0.45 1.15-0.84 0-1.71 0c-1.13-2.31-1.13-2.31-2-5q0.99-1.48 2-3c-2 1.62-2 1.62-4 4zm-88.06 24.12c-0.48 1.96-0.48 1.96-0.94 3.88q1.01 0 2 0c3.45-6.32 3.31-11.9 3-19q-1.01 0-2 0c-0.41 5.11-0.94 10.11-2.06 15.12zm126.06-31.12q0.91 0.45 1.84 0.92-0.51 0.64-1.13 1.2c-2.16 1.67-4.29 2.36-6.95 2.75-1.99 0.19-3.83-0.05-6-0.97-0.92-0.5-1.8-1.15-2.76-1.9q1.48-0.49 3-1 0.99 0.49 2 1c5.33 0.34 5.33 0.34 10-2zm-167.41 70.63q-0.84 0.98-1.65 1.93-1.47 1.72-2.94 3.44c4.18-0.78 4.18-0.78 9-6q-1.01-1.02-2-2-1.25 1.27-2.41 2.63zm114.45 80.38q3.59-2.41 6.96-4.99 0 0.98 0 1.98-0.99 0.98-2 2-0.49 0.98-1 2-1.95-0.49-3.96-0.99zm-130.04-24.01q0.19 0.08 0.35 0.15-0.53-0.71-0.98-1.4 0.32 0.63 0.63 1.25zm4 1q0.38-0.32 0.67-0.55c-0.78 0.25-1.25 0.38-1.77 0.45q0.43 0.04 1.1 0.1z" />
                        </svg>
                    </div>
                </div>

                <!-- Scattered Subtle Shapes Throughout Section -->
                <div class="horror-scattered-shapes absolute inset-0 pointer-events-none overflow-hidden">
                    <div class="scattered-shape scattered-shape-1"></div>
                    <div class="scattered-shape scattered-shape-2"></div>
                    <div class="scattered-shape scattered-shape-3"></div>
                    <div class="scattered-shape scattered-shape-4"></div>
                    <div class="scattered-shape scattered-shape-5"></div>
                    <div class="scattered-shape scattered-shape-6"></div>
                    <div class="scattered-shape scattered-shape-7"></div>
                    <div class="scattered-shape scattered-shape-8"></div>
                    <div class="scattered-shape scattered-shape-9"></div>
                    <div class="scattered-shape scattered-shape-10"></div>
                </div>

                <!-- Container for Alignment -->
                <div class="container mx-auto px-4 relative z-10">
                    <!-- Header Section with Enhanced Animation -->
                    <div class="relative z-10 mb-8 md:mb-12">
                        <div class="horror-header text-center">
                            <div class="horror-title-wrapper inline-block relative">
                                <h2 class="horror-main-title text-3xl md:text-4xl lg:text-5xl font-black mb-2 relative z-10">
                                    <span class="horror-title-text inline-block">
                                        فیلم ترسناک با کاراکترهای زنده!
                                    </span>
                                    <span class="horror-title-glow absolute inset-0 blur-xl opacity-60"></span>
                                    <span class="horror-title-shadow absolute inset-0 blur-2xl opacity-40"></span>
                                </h2>
                                <div class="horror-city-name text-xl md:text-2xl lg:text-3xl font-extrabold mt-2">
                                    <span class="horror-subtitle-text">
                                        <span class="inline-block">سینماترس‌های پیشنهادی</span>
                                        <?= $city_name ?>
                                    </span>
                                </div>
                            </div>
                            <div class="horror-divider mt-6 mx-auto w-32 h-1"></div>
                        </div>
                    </div>

                    <!-- Products List Container -->
                    <div id="horror-cinema-products-list" class="horror-products-list-wrapper grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 2xl:grid-cols-7 child:box-content gap-6 relative px-4">
                        <?= $ads_products->products ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- Custom Styles and Animations -->
        <style>
            /* Horror Section Base Styles with Halo Effect */
            .horror-cinema-section {
                background: linear-gradient(135deg,
                        rgba(15, 15, 20, 0.95) 0%,
                        rgba(25, 15, 25, 0.98) 25%,
                        rgba(20, 10, 20, 0.97) 50%,
                        rgba(25, 15, 25, 0.98) 75%,
                        rgba(15, 15, 20, 0.95) 100%);
                position: relative;
            }

            /* Animated Background */
            .horror-bg-animated {
                background: radial-gradient(ellipse at center,
                        rgba(220, 38, 38, 0.15) 0%,
                        rgba(153, 27, 27, 0.1) 30%,
                        transparent 70%);
                animation: bgPulse 8s ease-in-out infinite;
            }

            @keyframes bgPulse {

                0%,
                100% {
                    opacity: 0.6;
                    transform: scale(1);
                }

                50% {
                    opacity: 1;
                    transform: scale(1.1);
                }
            }

            /* Halo Effect */
            .horror-halo-effect {
                background: radial-gradient(circle at 50% 50%,
                        rgba(220, 38, 38, 0.2) 0%,
                        rgba(153, 27, 27, 0.1) 40%,
                        transparent 70%);
                animation: haloRotate 20s linear infinite;
            }

            /* Disable ALL animations on Mobile to prevent layout jumping and page shaking */
            @media (max-width: 767px) {

                *,
                *::before,
                *::after {
                    animation: none !important;
                    transition: none !important;
                }

                .horror-halo-effect {
                    display: none;
                }
            }

            @keyframes haloRotate {
                0% {
                    transform: rotate(0deg) scale(1);
                }

                50% {
                    transform: rotate(180deg) scale(1.2);
                }

                100% {
                    transform: rotate(360deg) scale(1);
                }
            }

            /* Decorative Shapes */
            .horror-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(40px);
                opacity: 0.3;
                animation: shapeFloat 12s ease-in-out infinite;
            }

            .horror-shape-1 {
                width: 300px;
                height: 300px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.4), transparent);
                top: -150px;
                left: 10%;
                animation-delay: 0s;
            }

            .horror-shape-2 {
                width: 400px;
                height: 400px;
                background: radial-gradient(circle, rgba(153, 27, 27, 0.3), transparent);
                top: 20%;
                right: -200px;
                animation-delay: 2s;
            }

            .horror-shape-3 {
                width: 250px;
                height: 250px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.35), transparent);
                bottom: 10%;
                left: 5%;
                animation-delay: 4s;
            }

            .horror-shape-4 {
                width: 350px;
                height: 350px;
                background: radial-gradient(circle, rgba(127, 29, 29, 0.3), transparent);
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                animation-delay: 6s;
            }

            .horror-shape-5 {
                width: 200px;
                height: 200px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.4), transparent);
                bottom: -100px;
                right: 15%;
                animation-delay: 8s;
            }

            @keyframes shapeFloat {

                0%,
                100% {
                    transform: translate(0, 0) scale(1);
                    opacity: 0.3;
                }

                25% {
                    transform: translate(30px, -30px) scale(1.1);
                    opacity: 0.4;
                }

                50% {
                    transform: translate(-20px, 20px) scale(0.9);
                    opacity: 0.35;
                }

                75% {
                    transform: translate(20px, 30px) scale(1.05);
                    opacity: 0.4;
                }
            }

            /* Enhanced Background Particles Animation */
            .horror-particles .particle {
                position: absolute;
                width: 6px;
                height: 6px;
                background: #dc2626;
                border-radius: 50%;
                box-shadow: 0 0 15px #dc2626, 0 0 30px #dc2626, 0 0 45px rgba(220, 38, 38, 0.5);
                animation: floatEnhanced 18s infinite ease-in-out;
            }

            .particle-1 {
                left: 10%;
                top: 20%;
                animation-delay: 0s;
            }

            .particle-2 {
                left: 30%;
                top: 60%;
                animation-delay: 2.5s;
            }

            .particle-3 {
                left: 60%;
                top: 30%;
                animation-delay: 5s;
            }

            .particle-4 {
                left: 80%;
                top: 70%;
                animation-delay: 7.5s;
            }

            .particle-5 {
                left: 50%;
                top: 80%;
                animation-delay: 10s;
            }

            .particle-6 {
                left: 20%;
                top: 50%;
                animation-delay: 12.5s;
            }

            .particle-7 {
                left: 70%;
                top: 15%;
                animation-delay: 15s;
            }

            .particle-8 {
                left: 40%;
                top: 90%;
                animation-delay: 17.5s;
            }

            @keyframes floatEnhanced {

                0%,
                100% {
                    transform: translateY(0) translateX(0) scale(1) rotate(0deg);
                    opacity: 0.4;
                }

                20% {
                    transform: translateY(-40px) translateX(30px) scale(1.3) rotate(90deg);
                    opacity: 0.7;
                }

                40% {
                    transform: translateY(-80px) translateX(-30px) scale(0.7) rotate(180deg);
                    opacity: 0.5;
                }

                60% {
                    transform: translateY(-50px) translateX(20px) scale(1.2) rotate(270deg);
                    opacity: 0.8;
                }

                80% {
                    transform: translateY(-20px) translateX(-10px) scale(0.9) rotate(360deg);
                    opacity: 0.6;
                }
            }

            /* Enhanced Title Animations */
            .horror-title-text {
                background: linear-gradient(135deg, #ffffff 0%, #fca5a5 30%, #dc2626 60%, #991b1b 100%);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                animation: titleGlowEnhanced 4s ease-in-out infinite, titleGradient 6s ease-in-out infinite;
                text-shadow: 0 0 30px rgba(220, 38, 38, 0.5);
                position: relative;
            }

            .horror-title-glow {
                background: linear-gradient(135deg, #dc2626 0%, #991b1b 50%, #dc2626 100%);
                filter: blur(25px);
                animation: glowPulseEnhanced 3s ease-in-out infinite;
            }

            .horror-title-shadow {
                background: radial-gradient(ellipse, rgba(220, 38, 38, 0.6), transparent);
                filter: blur(40px);
                animation: shadowPulse 5s ease-in-out infinite;
            }

            @keyframes titleGlowEnhanced {

                0%,
                100% {
                    filter: brightness(1) drop-shadow(0 0 15px rgba(220, 38, 38, 0.6));
                    transform: scale(1);
                }

                50% {
                    filter: brightness(1.4) drop-shadow(0 0 30px rgba(220, 38, 38, 1));
                    transform: scale(1.02);
                }
            }

            @keyframes titleGradient {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            @keyframes glowPulseEnhanced {

                0%,
                100% {
                    opacity: 0.4;
                    transform: scale(1) rotate(0deg);
                }

                50% {
                    opacity: 0.8;
                    transform: scale(1.15) rotate(5deg);
                }
            }

            @keyframes shadowPulse {

                0%,
                100% {
                    opacity: 0.3;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.6;
                    transform: scale(1.3);
                }
            }

            .horror-city-name {
                color: #fca5a5;
            }

            .horror-subtitle-text {
                color: #fca5a5;
                text-shadow:
                    0 0 10px rgba(220, 38, 38, 0.9),
                    0 0 20px rgba(220, 38, 38, 0.7),
                    0 0 30px rgba(220, 38, 38, 0.5);
                animation: subtitleFlickerEnhanced 5s ease-in-out infinite;
            }

            /* Ghost Shadows in Corners */
            .horror-ghost-shadows {
                z-index: 1;
            }

            .ghost-shadow {
                position: absolute;
                border-radius: 50%;
                filter: blur(60px);
                opacity: 0.15;
                animation: ghostFloat 15s ease-in-out infinite;
            }

            .ghost-shadow-1 {
                width: 200px;
                height: 200px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.6), transparent);
                top: -100px;
                left: -100px;
                animation-delay: 0s;
            }

            .ghost-shadow-2 {
                width: 250px;
                height: 250px;
                background: radial-gradient(circle, rgba(153, 27, 27, 0.5), transparent);
                top: -125px;
                right: -125px;
                animation-delay: 3s;
            }

            .ghost-shadow-3 {
                width: 220px;
                height: 220px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.55), transparent);
                bottom: -110px;
                left: -110px;
                animation-delay: 6s;
            }

            .ghost-shadow-4 {
                width: 240px;
                height: 240px;
                background: radial-gradient(circle, rgba(127, 29, 29, 0.5), transparent);
                bottom: -120px;
                right: -120px;
                animation-delay: 9s;
            }

            @keyframes ghostFloat {

                0%,
                100% {
                    transform: translate(0, 0) scale(1);
                    opacity: 0.15;
                }

                25% {
                    transform: translate(30px, -40px) scale(1.1);
                    opacity: 0.2;
                }

                50% {
                    transform: translate(-20px, 30px) scale(0.9);
                    opacity: 0.12;
                }

                75% {
                    transform: translate(40px, 20px) scale(1.05);
                    opacity: 0.18;
                }
            }

            /* Scattered Subtle Shapes Throughout Section */
            .horror-scattered-shapes {
                z-index: 1;
            }

            .scattered-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                opacity: 0.08;
                animation: scatteredShapeDrift 25s ease-in-out infinite;
            }

            .scattered-shape-1 {
                width: 150px;
                height: 150px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.4), transparent);
                top: 5%;
                left: 8%;
                animation-delay: 0s;
            }

            .scattered-shape-2 {
                width: 120px;
                height: 120px;
                background: radial-gradient(circle, rgba(153, 27, 27, 0.35), transparent);
                top: 15%;
                right: 12%;
                animation-delay: 3s;
            }

            .scattered-shape-3 {
                width: 180px;
                height: 180px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.3), transparent);
                top: 35%;
                left: 3%;
                animation-delay: 6s;
            }

            .scattered-shape-4 {
                width: 100px;
                height: 100px;
                background: radial-gradient(circle, rgba(127, 29, 29, 0.35), transparent);
                top: 45%;
                right: 8%;
                animation-delay: 9s;
            }

            .scattered-shape-5 {
                width: 160px;
                height: 160px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.4), transparent);
                top: 60%;
                left: 15%;
                animation-delay: 12s;
            }

            .scattered-shape-6 {
                width: 140px;
                height: 140px;
                background: radial-gradient(circle, rgba(153, 27, 27, 0.3), transparent);
                top: 70%;
                right: 5%;
                animation-delay: 15s;
            }

            .scattered-shape-7 {
                width: 110px;
                height: 110px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.35), transparent);
                top: 25%;
                left: 50%;
                animation-delay: 18s;
            }

            .scattered-shape-8 {
                width: 130px;
                height: 130px;
                background: radial-gradient(circle, rgba(127, 29, 29, 0.3), transparent);
                top: 55%;
                left: 60%;
                animation-delay: 21s;
            }

            .scattered-shape-9 {
                width: 90px;
                height: 90px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.4), transparent);
                top: 80%;
                left: 40%;
                animation-delay: 24s;
            }

            .scattered-shape-10 {
                width: 170px;
                height: 170px;
                background: radial-gradient(circle, rgba(153, 27, 27, 0.35), transparent);
                top: 10%;
                left: 70%;
                animation-delay: 27s;
            }

            @keyframes scatteredShapeDrift {

                0%,
                100% {
                    transform: translate(0, 0) scale(1);
                    opacity: 0.08;
                }

                20% {
                    transform: translate(60px, -80px) scale(1.15);
                    opacity: 0.12;
                }

                40% {
                    transform: translate(-50px, 70px) scale(0.85);
                    opacity: 0.06;
                }

                60% {
                    transform: translate(80px, 50px) scale(1.1);
                    opacity: 0.1;
                }

                80% {
                    transform: translate(-40px, -60px) scale(0.95);
                    opacity: 0.09;
                }
            }

            @keyframes subtitleFlickerEnhanced {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }

                20% {
                    opacity: 0.85;
                    transform: scale(0.97) translateY(-2px);
                }

                40% {
                    opacity: 1;
                    transform: scale(1.03) translateY(2px);
                }

                60% {
                    opacity: 0.9;
                    transform: scale(0.99) translateY(-1px);
                }

                80% {
                    opacity: 1;
                    transform: scale(1.01) translateY(1px);
                }
            }

            /* Products List Styles */
            .horror-products-list-wrapper article {
                position: relative;
                overflow: visible;
                transform-origin: center center;
            }

            /* Padding for article content (except first div with image) */
            .horror-products-list-wrapper article>div:not(:first-child) {
                padding-left: 10px;
                padding-right: 10px;
            }

            /* Light text colors for horror section cards */
            .horror-products-list-wrapper article {
                color: #ffffff;
            }

            .horror-products-list-wrapper article h3,
            .horror-products-list-wrapper article h3 a {
                color: #ffffff !important;
            }

            .horror-products-list-wrapper article p,
            .horror-products-list-wrapper article span:not([name="rate"]) {
                color: #e5e7eb;
            }

            .horror-products-list-wrapper article [name="address"],
            .horror-products-list-wrapper article [name="title"] {
                color: #f3f4f6 !important;
            }

            .horror-products-list-wrapper article .text-steel {
                color: #d1d5db !important;
            }

            .horror-products-list-wrapper article .text-text-charcoal {
                color: #e5e7eb !important;
            }

            .horror-products-list-wrapper article .text-muted-blue {
                color: #d1d5db !important;
            }

            /* Keep original colors for price section with bg-slate-110 - Override all white text styles */
            .horror-products-list-wrapper article .bg-slate-110 {
                color: #1f2937 !important;
            }

            /* Reset all child elements to inherit or original colors */
            .horror-products-list-wrapper article .bg-slate-110 * {
                color: inherit !important;
            }

            /* Specific styles for text-steel inside bg-slate-110 */
            .horror-products-list-wrapper article .bg-slate-110 .text-steel {
                color: #62748E !important;
            }

            /* Price inside bg-slate-110 */
            .horror-products-list-wrapper article span[name="price"] {
                color: #1f2937 !important;
                font-weight: bold !important;
            }

            .horror-products-list-wrapper article span.text-steel {
                color: #62748E !important;
            }

            /* All divs inside bg-slate-110 */
            .horror-products-list-wrapper article .bg-slate-110 div {
                color: #1f2937 !important;
            }

            /* All spans inside divs inside bg-slate-110 - override white text */
            .horror-products-list-wrapper article .bg-slate-110 div span {
                color: inherit !important;
            }

            /* text-steel inside divs inside bg-slate-110 */
            .horror-products-list-wrapper article .bg-slate-110 div .text-steel {
                color: #62748E !important;
            }

            /* Price inside divs inside bg-slate-110 */
            .horror-products-list-wrapper article .bg-slate-110 div [name="price"] {
                color: #1f2937 !important;
                font-weight: bold !important;
            }

            /* Override all white/light text styles that might affect bg-slate-110 section */
            .horror-products-list-wrapper article .bg-slate-110 p {
                color: #1f2937 !important;
            }

            .horror-products-list-wrapper article .bg-slate-110 span:not([name="rate"]) {
                color: inherit !important;
            }

            /* Ensure all nested elements maintain original colors */
            .horror-products-list-wrapper article .bg-slate-110 div * {
                color: inherit !important;
            }

            .horror-products-list-wrapper article .bg-slate-110 div div {
                color: #1f2937 !important;
            }

            .horror-products-list-wrapper article .bg-slate-110 div div span {
                color: inherit !important;
            }

            .horror-products-list-wrapper article .bg-slate-110 div div .text-steel {
                color: #62748E !important;
            }

            .horror-products-list-wrapper article .bg-slate-110 div div [name="price"] {
                color: #1f2937 !important;
                font-weight: bold !important;
            }

            /* Animation only on desktop */
            @media (min-width: 769px) {
                .horror-products-list-wrapper article {
                    animation: slideInHorrorEnhanced 1s ease-out backwards;
                }

                .horror-products-list-wrapper article:nth-child(1) {
                    animation-delay: 0.1s;
                }

                .horror-products-list-wrapper article:nth-child(2) {
                    animation-delay: 0.2s;
                }

                .horror-products-list-wrapper article:nth-child(3) {
                    animation-delay: 0.3s;
                }

                .horror-products-list-wrapper article:nth-child(4) {
                    animation-delay: 0.4s;
                }

                .horror-products-list-wrapper article:nth-child(5) {
                    animation-delay: 0.5s;
                }

                .horror-products-list-wrapper article:nth-child(6) {
                    animation-delay: 0.6s;
                }

                .horror-products-list-wrapper article:nth-child(7) {
                    animation-delay: 0.7s;
                }

                .horror-products-list-wrapper article:nth-child(8) {
                    animation-delay: 0.8s;
                }
            }

            @keyframes slideInHorrorEnhanced {
                from {
                    opacity: 0;
                    transform: translateY(50px) scale(0.85) rotateY(-10deg);
                    filter: blur(8px) brightness(0.7);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1) rotateY(0deg);
                    filter: blur(0) brightness(1);
                }
            }


            /* Enhanced Divider Animation */
            .horror-divider {
                background: linear-gradient(90deg,
                        transparent 0%,
                        rgba(220, 38, 38, 0.3) 20%,
                        rgba(220, 38, 38, 0.8) 50%,
                        rgba(220, 38, 38, 0.3) 80%,
                        transparent 100%);
                animation: dividerExpandEnhanced 3s ease-in-out infinite;
                box-shadow:
                    0 0 10px rgba(220, 38, 38, 0.6),
                    0 0 20px rgba(220, 38, 38, 0.4);
            }

            @keyframes dividerExpandEnhanced {

                0%,
                100% {
                    width: 32px;
                    opacity: 0.5;
                    transform: scaleX(1);
                }

                25% {
                    width: 80px;
                    opacity: 0.8;
                    transform: scaleX(1.2);
                }

                50% {
                    width: 100px;
                    opacity: 1;
                    transform: scaleX(1.5);
                }

                75% {
                    width: 80px;
                    opacity: 0.8;
                    transform: scaleX(1.2);
                }
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {

                .horror-main-title {
                    font-size: 2rem;
                }

                .horror-subtitle {
                    font-size: 1.125rem;
                }

                .horror-shape {
                    filter: blur(30px);
                }
            }

            /* Mobile Responsive for List */
            @media (max-width: 768px) {
                .horror-products-list-wrapper article {
                    animation: none !important;
                }
            }

            /* Fade Out Gradient at Bottom */
            .horror-fade-out {
                background: linear-gradient(to bottom,
                        transparent 0%,
                        rgba(15, 15, 20, 0.3) 20%,
                        rgba(15, 15, 20, 0.6) 50%,
                        rgba(15, 15, 20, 0.85) 80%,
                        rgba(15, 15, 20, 0.95) 100%);
            }

            /* Floating Ghosts Animation - Positioned Left and Right */
            .horror-floating-ghosts {
                overflow: visible;
            }

            .horror-ghost-item {
                position: absolute;
                width: 120px;
                height: 120px;
            }

            .horror-ghost-svg {
                width: 100%;
                height: 100%;
                filter: blur(6px);
            }

            /* Ghost 1 - Top Left of Container */
            .horror-ghost-1 {
                top: 15%;
                left: 5%;
                animation: ghostFloatLeft 8s ease-in-out infinite;
            }

            @keyframes ghostFloatLeft {
                0% {
                    transform: translateX(0) translateY(0) translateZ(0) scale(0.9);
                    opacity: 0.4;
                }

                25% {
                    transform: translateX(10px) translateY(-5px) translateZ(5px) scale(1.1);
                    opacity: 0.7;
                }

                50% {
                    transform: translateX(-5px) translateY(5px) translateZ(-5px) scale(0.85);
                    opacity: 0.5;
                }

                75% {
                    transform: translateX(8px) translateY(-3px) translateZ(3px) scale(1.05);
                    opacity: 0.65;
                }

                100% {
                    transform: translateX(0) translateY(0) translateZ(0) scale(0.9);
                    opacity: 0.4;
                }
            }

            /* Ghost 2 - Bottom Right of Container */
            .horror-ghost-2 {
                bottom: 15%;
                right: 5%;
                animation: ghostFloatRight 10s ease-in-out infinite;
                animation-delay: 1s;
            }

            @keyframes ghostFloatRight {
                0% {
                    transform: translateX(0) translateY(0) translateZ(0) scale(0.95);
                    opacity: 0.45;
                }

                20% {
                    transform: translateX(-8px) translateY(3px) translateZ(-3px) scale(0.8);
                    opacity: 0.6;
                }

                40% {
                    transform: translateX(5px) translateY(-5px) translateZ(4px) scale(1.15);
                    opacity: 0.5;
                }

                60% {
                    transform: translateX(-12px) translateY(8px) translateZ(-6px) scale(0.9);
                    opacity: 0.7;
                }

                80% {
                    transform: translateX(7px) translateY(-2px) translateZ(2px) scale(1.05);
                    opacity: 0.55;
                }

                100% {
                    transform: translateX(0) translateY(0) translateZ(0) scale(0.95);
                    opacity: 0.45;
                }
            }

            /* Responsive sizes */
            @media (min-width: 768px) {
                .horror-ghost-item {
                    width: 150px;
                    height: 150px;
                }

                .horror-ghost-1 {
                    left: 3%;
                }

                .horror-ghost-2 {
                    right: 3%;
                }
            }

            @media (min-width: 1024px) {
                .horror-ghost-item {
                    width: 180px;
                    height: 180px;
                }

                .horror-ghost-1 {
                    left: 2%;
                }

                .horror-ghost-2 {
                    right: 2%;
                }
            }

            @media (max-width: 767px) {
                .horror-ghost-item {
                    width: 100px;
                    height: 100px;
                }

                .horror-ghost-1 {
                    left: 2%;
                    animation: none;
                }

                .horror-ghost-2 {
                    right: 2%;
                    animation: none;
                }
            }
        </style>

        </section>
    <?php
    } else {
        // نمایش معمولی برای بقیه type ها ?>

<?php 
        // چک می‌کنیم آیا در تهران و اتاق فرار هستیم
        $is_tehran_escaperoom = ($city_name === 'تهران' && $product_type === 'اتاق فرار');
        ?>

        <?php if ($is_tehran_escaperoom): ?>
        <!-- Tehran Escape Room Special Section -->
        <section id="scary-proposal-section" class="max-w-full mt-8 lg:pb-6 max-lg:py-6 overflow-hidden rounded-4xl relative">
            <!-- Three.js Canvas for Particles -->
            <canvas id="scary-particles-canvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
            
            <!-- Animated Background -->
            <div class="scary-proposal-bg absolute inset-0 overflow-hidden">
                <div class="scary-bg-animated absolute inset-0"></div>
                <div class="scary-pattern-overlay absolute inset-0"></div>
                
                <!-- Animated Glow Effects -->
                <div class="scary-glow-pulse-1 absolute"></div>
                <div class="scary-glow-pulse-2 absolute"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 px-4">
                <!-- Header Section with Title and Discount Code -->
                <div class="mb-6 md:mb-8 lg:mb-6 lg:pt-4">
                    <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4">
                        <!-- Title and Icon Container -->
                        <div class="flex items-center gap-3 lg:gap-4">
                            <svg class="scary-icon flex-shrink-0" width="38" height="28" viewBox="0 0 38 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.82578 26.3566L6.31686 23.7179L3.68886 25.2454C3.33841 25.4474 2.92207 25.502 2.5314 25.3971C2.14074 25.2921 1.80777 25.0363 1.60573 24.6859C1.40369 24.3354 1.34913 23.9191 1.45407 23.5284C1.55901 23.1377 1.81483 22.8048 2.16527 22.6027L21.7807 11.2939C21.1874 9.50828 21.278 7.566 22.035 5.8434C22.7921 4.12081 24.1616 2.74054 25.8782 1.97006C27.5948 1.19958 29.5363 1.09374 31.3265 1.67304C33.1167 2.25235 34.6282 3.47556 35.568 5.10566C36.5078 6.73575 36.809 8.65667 36.4133 10.4962C36.0176 12.3357 34.9532 13.9629 33.4263 15.0624C31.8994 16.1619 30.0187 16.6555 28.1486 16.4475C26.2785 16.2395 24.5522 15.3447 23.3042 13.9366L14.2077 19.1686L15.7352 21.7966C15.8343 21.9702 15.8982 22.1617 15.9232 22.36C15.9482 22.5584 15.9338 22.7597 15.8809 22.9525C15.8301 23.1458 15.7417 23.3273 15.6207 23.4864C15.4997 23.6455 15.3484 23.7792 15.1757 23.8797C15.0023 23.9806 14.8108 24.0462 14.612 24.0726C14.4132 24.0991 14.2112 24.0859 14.0175 24.0339C13.8239 23.9819 13.6424 23.892 13.4837 23.7695C13.3249 23.647 13.192 23.4943 13.0925 23.3202L11.657 20.7012L9.01428 22.2247L10.5418 24.8528C10.6409 25.0263 10.7048 25.2178 10.7298 25.4161C10.7548 25.6145 10.7404 25.8158 10.6875 26.0086C10.6367 26.202 10.5483 26.3834 10.4273 26.5425C10.3063 26.7017 10.155 26.8353 9.98225 26.9359C9.80488 27.0479 9.60624 27.1219 9.39883 27.1534C9.19142 27.1848 8.97975 27.1729 8.77716 27.1185C8.57456 27.0641 8.38544 26.9683 8.2217 26.8372C8.05796 26.7061 7.92315 26.5424 7.82578 26.3566ZM33.3826 10.085C33.6164 9.21457 33.5869 8.29435 33.2977 7.44072C33.0085 6.58709 32.4727 5.83838 31.758 5.28927C31.0433 4.74016 30.1818 4.41532 29.2825 4.35581C28.3832 4.29631 27.4864 4.50483 26.7056 4.95499C25.9248 5.40515 25.295 6.07674 24.8959 6.88483C24.4967 7.69293 24.3462 8.60123 24.4633 9.49487C24.5804 10.3885 24.9598 11.2274 25.5537 11.9054C26.1475 12.5834 26.9291 13.07 27.7995 13.3038C28.9667 13.6174 30.2107 13.4544 31.2577 12.8507C32.3047 12.2471 33.0691 11.2522 33.3826 10.085Z" fill="#fb923c" stroke="#fb923c" />
                            </svg>
                            <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-white scary-title-text">
                                    <span class="scary-word-normal">پیشنهادهای</span>
                                    <span class="font-black scary-word-highlight">ترسناک و هیجانی</span>
                                </h2>
                        </div>

                        <!-- Discount Code Badge (Desktop - One Line) -->
                        <!--<div class="flex scary-discount-badge-desktop" onclick="copyScaryCode('wel150', this)">-->
                        <!--    <div class="scary-code-shine"></div>-->
                        <!--    <div class="flex items-center gap-3 max-lg:w-full">-->
                        <!--        <svg class="scary-tag-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
                        <!--            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>-->
                        <!--        </svg>-->
                        <!--        <span class="scary-code-text">wel150</span>-->
                        <!--        <span class="scary-divider">|</span>-->
                        <!--        <span class="scary-discount-amount">کد تخفیف 1.5 میلیون ریالی</span>-->
                        <!--        <svg class="scary-copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
                        <!--            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>-->
                        <!--        </svg>-->
                        <!--    </div>-->
                        <!--</div>-->
                    </div>
                </div>

                <!-- Products Slider -->
                <div class="rounded-tl-none rounded-tr-none lg:px-0 pb-6">
                    <div class="relative overflow-hidden embla_normal slider-event" data-slider-event="off-slider">
                        <div class="embla__viewport">
                            <div id="discount-events-slider2" class="embla__container child:bg-white child:p-2.5 md:child:p-5 child:rounded-3xl flex gap-x-4 md:gap-x-6 child:shrink-0 child:grow-0 child:w-d176 md:child:w-d230"> <?= $ads_products->products ?> </div>
                        </div>
                        <div class="hidden lg:block lg:opacity-80 [&amp;>button]:block [&amp;>button]:h-full [&amp;>button]:top-0 [&amp;>button]:translate-y-0">
                            <button class="absolute right-0 rotate-180 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--prev discount-events-btn2 top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Previous slide" aria-controls="discount-events-slider" aria-disabled="false">
                                <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                    </svg>
                                </div>
                            </button>
                            <button class="absolute left-0 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--next discount-events-btn2 top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Next slide" aria-controls="discount-events-slider" aria-disabled="false">
                                <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Scary Proposal Styles -->
        <style>
            /* Canvas positioning */
            #scary-particles-canvas {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 1;
            }

            /* Animated Gradient Background */
            .scary-bg-animated {
                background: linear-gradient(135deg,
                    #450a0a 0%,
                    #7f1d1d 20%,
                    #991b1b 40%,
                    #7f1d1d 60%,
                    #5e0c0c 80%,
                    #450a0a 100%);
                background-size: 200% 200%;
                animation: scaryBgShift 15s ease infinite;
            }

            @keyframes scaryBgShift {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }

            /* Pattern Overlay */
            .scary-pattern-overlay {
                background-image: 
                    radial-gradient(circle, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                    radial-gradient(circle, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
                background-size: 20px 20px, 30px 30px;
                background-position: 0 0, 10px 10px;
                animation: scaryPatternMove 20s linear infinite;
                opacity: 0.4;
            }

            @keyframes scaryPatternMove {
                0% { background-position: 0 0, 10px 10px; }
                100% { background-position: 20px 20px, 30px 30px; }
            }

            /* Glow Pulses */
            .scary-glow-pulse-1,
            .scary-glow-pulse-2 {
                width: 300px;
                height: 300px;
                border-radius: 50%;
                filter: blur(80px);
                pointer-events: none;
                opacity: 0.3;
            }

            .scary-glow-pulse-1 {
                left: 10%;
                top: 50%;
                transform: translateY(-50%);
                background: radial-gradient(circle, rgba(239, 68, 68, 0.6), transparent 70%);
                animation: scaryGlowPulse1 4s ease-in-out infinite;
            }

            .scary-glow-pulse-2 {
                right: 10%;
                top: 50%;
                transform: translateY(-50%);
                background: radial-gradient(circle, rgba(220, 38, 38, 0.5), transparent 70%);
                animation: scaryGlowPulse2 5s ease-in-out infinite 1s;
            }

            @keyframes scaryGlowPulse1 {
                0%, 100% { opacity: 0.2; transform: translateY(-50%) scale(1); }
                50% { opacity: 0.4; transform: translateY(-50%) scale(1.2); }
            }

            @keyframes scaryGlowPulse2 {
                0%, 100% { opacity: 0.25; transform: translateY(-50%) scale(1); }
                50% { opacity: 0.45; transform: translateY(-50%) scale(1.3); }
            }

            /* Icon Animation */
            .scary-icon {
                animation: scaryIconBounce 3s ease-in-out infinite;
            }

            @keyframes scaryIconBounce {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                25% { transform: translateY(-3px) rotate(-2deg); }
                50% { transform: translateY(0) rotate(0deg); }
                75% { transform: translateY(-3px) rotate(2deg); }
            }

            /* Title Styles with Micro Animations */
            .scary-title-text {
                text-shadow: 2px 2px 0 rgba(0, 0, 0, 0.3), 0 0 20px rgba(255, 255, 255, 0.4);
            }

            .scary-word-normal {
                display: inline-block;
                animation: scaryWordFloat 3s ease-in-out infinite;
            }

            .scary-word-highlight {
                display: inline-block;
                color: #ffffff;
                text-shadow: 
                    1px 1px 2px rgba(0, 0, 0, 0.6),
                    0 0 10px rgba(255, 107, 53, 0.6),
                    0 0 20px rgba(255, 107, 53, 0.4);
                filter: drop-shadow(0 3px 6px rgba(255, 107, 53, 0.5));
                animation: scaryWordGlow 2s ease-in-out infinite, scaryWordFloat 3s ease-in-out 0.5s infinite;
            }

            @keyframes scaryWordFloat {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-2px); }
            }

            @keyframes scaryWordGlow {
                0%, 100% {
                    text-shadow: 
                        1px 1px 2px rgba(0, 0, 0, 0.6),
                        0 0 10px rgba(255, 107, 53, 0.6),
                        0 0 20px rgba(255, 107, 53, 0.4);
                }
                50% {
                    text-shadow: 
                        1px 1px 2px rgba(0, 0, 0, 0.6),
                        0 0 15px rgba(255, 107, 53, 0.8),
                        0 0 30px rgba(255, 107, 53, 0.6);
                }
            }

            /* Discount Badge - Desktop (One Line) */
            .scary-discount-badge-desktop {
                position: relative;
                background: linear-gradient(135deg, 
                    rgba(255, 255, 255, 0.25) 0%,
                    rgba(255, 255, 255, 0.15) 50%,
                    rgba(255, 255, 255, 0.25) 100%);
                backdrop-filter: blur(12px) saturate(180%);
                -webkit-backdrop-filter: blur(12px) saturate(180%);
                border: 2px solid rgba(255, 255, 255, 0.6);
                border-radius: 16px;
                padding: 0 20px;
                height: 46px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 
                    0 4px 20px rgba(0, 0, 0, 0.25),
                    0 0 25px rgba(255, 107, 53, 0.4),
                    inset 0 1px 1px rgba(255, 255, 255, 0.5);
                overflow: hidden;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .scary-discount-badge-desktop:hover {
                transform: translateY(-2px) scale(1.02);
                box-shadow: 
                    0 6px 25px rgba(0, 0, 0, 0.3),
                    0 0 35px rgba(255, 107, 53, 0.6);
            }

            .scary-discount-badge-desktop:active {
                transform: scale(0.98);
            }

            /* Discount Badge - Mobile (Below Title) */
            .scary-discount-badge-mobile {
                position: relative;
                background: linear-gradient(135deg, 
                    rgba(255, 255, 255, 0.2) 0%,
                    rgba(255, 255, 255, 0.1) 50%,
                    rgba(255, 255, 255, 0.2) 100%);
                backdrop-filter: blur(10px) saturate(180%);
                -webkit-backdrop-filter: blur(10px) saturate(180%);
                border: 1.5px solid rgba(255, 255, 255, 0.5);
                border-radius: 12px;
                padding: 0 12px;
                height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 
                    0 2px 15px rgba(0, 0, 0, 0.2),
                    0 0 20px rgba(255, 107, 53, 0.3);
                overflow: hidden;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 4px;
            }

            .scary-discount-badge-mobile:active {
                transform: scale(0.96);
            }

            /* Shine Effect */
            .scary-code-shine {
                position: absolute;
                top: -50%;
                left: -100%;
                width: 50%;
                height: 200%;
                background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.5), transparent);
                transform: skewX(-20deg);
                animation: scaryShineSweep 3.5s ease-in-out infinite;
                pointer-events: none;
            }

            @keyframes scaryShineSweep {
                0%, 20% { left: -100%; opacity: 0; }
                25% { opacity: 1; }
                45%, 100% { left: 150%; opacity: 0; }
            }

            /* Icons and Text */
            .scary-tag-icon {
                width: 1.25rem;
                height: 1.25rem;
                color: #fff;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
                flex-shrink: 0;
            }

            .scary-tag-icon-sm {
                width: 0.875rem;
                height: 0.875rem;
                color: #fff;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
                flex-shrink: 0;
            }

            .scary-copy-icon {
                width: 1.125rem;
                height: 1.125rem;
                color: #fff;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
                flex-shrink: 0;
                transition: transform 0.2s ease;
            }

            .scary-copy-icon-sm {
                width: 0.875rem;
                height: 0.875rem;
                color: #fff;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
                flex-shrink: 0;
            }

            .scary-discount-badge-desktop:hover .scary-copy-icon {
                transform: scale(1.1);
            }

            .scary-code-text {
                color: #fff;
                font-weight: 900;
                font-size: 1rem;
                text-shadow: 
                    1px 1px 3px rgba(0, 0, 0, 0.6),
                    0 0 10px rgba(255, 107, 53, 0.6);
                letter-spacing: 0.1em;
            }

            .scary-code-text-sm {
                color: #fff;
                font-weight: 900;
                font-size: 0.75rem;
                text-shadow: 
                    1px 1px 3px rgba(0, 0, 0, 0.6),
                    0 0 10px rgba(255, 107, 53, 0.6);
                letter-spacing: 0.08em;
            }

            .scary-divider {
                color: rgba(255, 255, 255, 0.5);
                font-weight: 300;
            }

            .scary-discount-amount {
                color: #fff;
                font-weight: 700;
                font-size: 0.875rem;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
                white-space: nowrap;
            }

            /* Mobile Optimizations */
            @media (max-width: 1024px) {
                .scary-glow-pulse-1,
                .scary-glow-pulse-2 {
                    display: none;
                }
            }

            @media (max-width: 640px) {
                .scary-icon {
                    width: 32px;
                    height: 24px;
                }

                .scary-discount-amount {
                    font-size: 0.75rem;
                }
            }
        </style>

        <script>
        // Copy discount code function
        function copyScaryCode(code, element) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(function() {
                    showScaryCodeCopied(element);
                }).catch(function(err) {
                    fallbackCopyCode(code, element);
                });
            } else {
                fallbackCopyCode(code, element);
            }
        }

        function fallbackCopyCode(text, element) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.opacity = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                showScaryCodeCopied(element);
            } catch (err) {
                console.error('Failed to copy:', err);
            }

            document.body.removeChild(textArea);
        }

        // Confetti Particle System برای جشن کپی کد تخفیف
        function createConfettiExplosion(element) {
            const rect = element.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            const colors = ['#fb923c', '#f59e0b', '#ef4444', '#ec4899', '#a855f7', '#3b82f6', '#10b981', '#f97316'];
            const particleCount = 50; // تعداد پارتیکل ها
            
            const container = document.createElement('div');
            container.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;';
            document.body.appendChild(container);
            
            for (let i = 0; i < particleCount; i++) {
                createParticle(container, centerX, centerY, colors);
            }
            
            // پاک کردن container بعد از 3 ثانیه
            setTimeout(() => {
                document.body.removeChild(container);
            }, 3000);
        }
        
        function createParticle(container, startX, startY, colors) {
            const particle = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Math.random() * 10 + 5;
            const isRibbon = Math.random() > 0.5;
            
            // استایل پارتیکل
            if (isRibbon) {
                // روبان های بلند
                particle.style.cssText = `
                    position: absolute;
                    left: ${startX}px;
                    top: ${startY}px;
                    width: ${size * 0.4}px;
                    height: ${size * 2.5}px;
                    background: linear-gradient(135deg, ${color}, ${color}dd);
                    border-radius: ${size * 0.2}px;
                    pointer-events: none;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                `;
            } else {
                // کانفتی های گرد و مربع
                const shapes = ['50%', '20%', '0%'];
                const shape = shapes[Math.floor(Math.random() * shapes.length)];
                particle.style.cssText = `
                    position: absolute;
                    left: ${startX}px;
                    top: ${startY}px;
                    width: ${size}px;
                    height: ${size}px;
                    background: ${color};
                    border-radius: ${shape};
                    pointer-events: none;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                `;
            }
            
            container.appendChild(particle);
            
            // حرکت رندوم
            const angle = Math.random() * Math.PI * 2;
            const velocity = Math.random() * 6 + 4;
            const vx = Math.cos(angle) * velocity;
            const vy = Math.sin(angle) * velocity - Math.random() * 3;
            const rotation = Math.random() * 720 - 360;
            const rotationSpeed = Math.random() * 20 - 10;
            
            let x = 0;
            let y = 0;
            let currentRotation = 0;
            let currentVy = vy;
            const gravity = 0.3;
            const drag = 0.98;
            
            function animate() {
                x += vx * drag;
                y += currentVy;
                currentVy += gravity;
                currentRotation += rotationSpeed;
                
                particle.style.transform = `translate(${x}px, ${y}px) rotate(${currentRotation}deg)`;
                particle.style.opacity = Math.max(0, 1 - y / 500);
                
                if (y < 600 && parseFloat(particle.style.opacity) > 0) {
                    requestAnimationFrame(animate);
                }
            }
            
            requestAnimationFrame(animate);
        }

        function showScaryCodeCopied(element) {
            const originalHTML = element.innerHTML;
            const originalBg = element.style.background;
            const isMobile = window.innerWidth < 1024;
            
            // فعال کردن افکت confetti
            createConfettiExplosion(element);
            
            element.style.background = 'linear-gradient(135deg, rgba(34, 197, 94, 0.4), rgba(34, 197, 94, 0.25))';
            element.style.transform = 'scale(0.98)';
            
            // مرحله 1: نمایش "کپی شد!"
            // در موبایل راست‌چین و در دسکتاپ وسط‌چین
            const justifyStyle = isMobile ? 'justify-content: flex-start;' : 'justify-content: center;';
            const copiedHTML = '<div class="flex items-center text-white font-bold" style="gap: 8px; ' + justifyStyle + '"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg><span style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5); white-space: nowrap;">کپی شد!</span><span id="scary-typing-text" style="font-size: 0.875rem; font-weight: 600; text-shadow: 1px 1px 2px rgba(0,0,0,0.4); opacity: 0; white-space: nowrap;"></span></div>';
            element.innerHTML = copiedHTML;
            
            // مرحله 2: اگر موبایل بود، بعد از 400ms تایپینگ رو شروع کن
            if (isMobile) {
                setTimeout(function() {
                    const typingElement = document.getElementById('scary-typing-text');
                    if (typingElement) {
                        typingElement.style.opacity = '1';
                        typingElement.style.transition = 'opacity 0.3s ease';
                        
                        const text = 'برو حالشو ببر';
                        let index = 0;
                        
                        function typeCharacter() {
                            if (index < text.length) {
                                typingElement.textContent += text.charAt(index);
                                index++;
                                setTimeout(typeCharacter, 80); // سرعت تایپ
                            }
                        }
                        
                        typeCharacter();
                    }
                }, 400);
            }
            
            // بازگشت به حالت اول
            setTimeout(function() {
                element.style.background = originalBg;
                element.style.transform = '';
                element.innerHTML = originalHTML;
            }, isMobile ? 3500 : 2000); // در موبایل بیشتر صبر کن
        }
        </script>

        <?php else: ?>
        <!-- Default Section (برای سایر موارد) -->
        <section class="max-w-full px-4 mt-8 lg:pb-6 max-lg:py-6 bg-slate-50 rounded-4xl">
            <div class="mb-6 md:mb-8 lg:-mb-px">
                <div class="flex justify-between">
                    <div class="items-center w-full md:flex gap-0 lg:[&>h2]:bg-slate-50 lg:[&>h2]:h-full lg:[&>h2]:rounded-tr-4xl lg:[&>h2]:rounded-tl-4xl lg:[&>h2]:py-6 [&>h2_b]:text-secondary-500">
                        <h2 class="text-21 flex items-center w-full gap-4">
                            <svg width="38" height="28" viewBox="0 0 38 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.82578 26.3566L6.31686 23.7179L3.68886 25.2454C3.33841 25.4474 2.92207 25.502 2.5314 25.3971C2.14074 25.2921 1.80777 25.0363 1.60573 24.6859C1.40369 24.3354 1.34913 23.9191 1.45407 23.5284C1.55901 23.1377 1.81483 22.8048 2.16527 22.6027L21.7807 11.2939C21.1874 9.50828 21.278 7.566 22.035 5.8434C22.7921 4.12081 24.1616 2.74054 25.8782 1.97006C27.5948 1.19958 29.5363 1.09374 31.3265 1.67304C33.1167 2.25235 34.6282 3.47556 35.568 5.10566C36.5078 6.73575 36.809 8.65667 36.4133 10.4962C36.0176 12.3357 34.9532 13.9629 33.4263 15.0624C31.8994 16.1619 30.0187 16.6555 28.1486 16.4475C26.2785 16.2395 24.5522 15.3447 23.3042 13.9366L14.2077 19.1686L15.7352 21.7966C15.8343 21.9702 15.8982 22.1617 15.9232 22.36C15.9482 22.5584 15.9338 22.7597 15.8809 22.9525C15.8301 23.1458 15.7417 23.3273 15.6207 23.4864C15.4997 23.6455 15.3484 23.7792 15.1757 23.8797C15.0023 23.9806 14.8108 24.0462 14.612 24.0726C14.4132 24.0991 14.2112 24.0859 14.0175 24.0339C13.8239 23.9819 13.6424 23.892 13.4837 23.7695C13.3249 23.647 13.192 23.4943 13.0925 23.3202L11.657 20.7012L9.01428 22.2247L10.5418 24.8528C10.6409 25.0263 10.7048 25.2178 10.7298 25.4161C10.7548 25.6145 10.7404 25.8158 10.6875 26.0086C10.6367 26.202 10.5483 26.3834 10.4273 26.5425C10.3063 26.7017 10.155 26.8353 9.98225 26.9359C9.80488 27.0479 9.60624 27.1219 9.39883 27.1534C9.19142 27.1848 8.97975 27.1729 8.77716 27.1185C8.57456 27.0641 8.38544 26.9683 8.2217 26.8372C8.05796 26.7061 7.92315 26.5424 7.82578 26.3566ZM33.3826 10.085C33.6164 9.21457 33.5869 8.29435 33.2977 7.44072C33.0085 6.58709 32.4727 5.83838 31.758 5.28927C31.0433 4.74016 30.1818 4.41532 29.2825 4.35581C28.3832 4.29631 27.4864 4.50483 26.7056 4.95499C25.9248 5.40515 25.295 6.07674 24.8959 6.88483C24.4967 7.69293 24.3462 8.60123 24.4633 9.49487C24.5804 10.3885 24.9598 11.2274 25.5537 11.9054C26.1475 12.5834 26.9291 13.07 27.7995 13.3038C28.9667 13.6174 30.2107 13.4544 31.2577 12.8507C32.3047 12.2471 33.0691 11.2522 33.3826 10.085Z" fill="#F0B100" stroke="#F0B100" />
                            </svg>
                            <span class="font-bold">
                                <span class="inline-block">
                                    <?= $product_type ?> های
                                </span>
                                <span class="font-black inline-block">پیشنهادی</span>
                            </span>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="rounded-tl-none rounded-tr-none lg:px-4">
                <div class="relative overflow-hidden embla_normal slider-event" data-slider-event="off-slider">
                    <div class="embla__viewport">
                        <div id="discount-events-slider2" class="embla__container child:bg-white child:p-2.5 md:child:p-5 child:rounded-3xl flex gap-x-4 md:gap-x-6 child:shrink-0 child:grow-0 child:w-d176 md:child:w-d230"> <?= $ads_products->products ?> </div>
                    </div>
                    <div class="hidden lg:block lg:opacity-80 [&amp;>button]:block [&amp;>button]:h-full [&amp;>button]:top-0 [&amp;>button]:translate-y-0">
                        <button class="absolute right-0 rotate-180 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--prev discount-events-btn2 top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Previous slide" aria-controls="discount-events-slider" aria-disabled="false">
                            <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                </svg>
                            </div>
                        </button>
                        <button class="absolute left-0 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--next discount-events-btn2 top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Next slide" aria-controls="discount-events-slider" aria-disabled="false">
                            <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php
    }
endif;

/*===============================================================*/
// اتاق فرارهای ترسناک شهر

if ($is_escaperoom) :
    $params = [
        'city_id' => [$term_id],
    ];
    $args = [
        'source'    => 'typecity_page_genre_horror',
        'params'    => $params,
    ];
    $horror_escaperooms = ez_products_snapshot_swiper($args);
    if (!is_null($horror_escaperooms->products) and !empty($horror_escaperooms->products) and (strlen($horror_escaperooms->products) > 0)) :
?>
        <section class="max-w-full py-4 md:py-5 lg:py-9">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="scary-room-product" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest","city_id":[<?= $params['city_id'][0] ?>]}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.13221 22.3631L5.17199 20.6839L3.49962 21.6559C3.27661 21.7845 3.01166 21.8192 2.76306 21.7525C2.51446 21.6857 2.30256 21.5229 2.17399 21.2999C2.04542 21.0769 2.01071 20.8119 2.07748 20.5633C2.14426 20.3147 2.30706 20.1028 2.53007 19.9742L15.0126 12.7777C14.635 11.6414 14.6927 10.4054 15.1745 9.30922C15.6562 8.21302 16.5277 7.33467 17.6201 6.84436C18.7125 6.35405 19.948 6.2867 21.0872 6.65535C22.2264 7.024 23.1883 7.80241 23.7863 8.83974C24.3844 9.87708 24.5761 11.0995 24.3243 12.2701C24.0725 13.4407 23.3951 14.4762 22.4234 15.1759C21.4518 15.8756 20.255 16.1896 19.0649 16.0573C17.8749 15.9249 16.7763 15.3555 15.9821 14.4594L10.1934 17.7889L11.1655 19.4613C11.2286 19.5717 11.2692 19.6936 11.2851 19.8198C11.301 19.946 11.2919 20.0741 11.2582 20.1968C11.2259 20.3198 11.1696 20.4353 11.0926 20.5366C11.0156 20.6378 10.9194 20.7229 10.8094 20.7869C10.6991 20.8511 10.5772 20.8928 10.4507 20.9096C10.3242 20.9265 10.1956 20.9181 10.0724 20.885C9.94917 20.8519 9.83371 20.7947 9.73268 20.7168C9.63165 20.6388 9.54705 20.5416 9.48377 20.4308L8.57023 18.7642L6.88853 19.7337L7.86059 21.4061C7.92368 21.5165 7.96434 21.6384 7.98025 21.7646C7.99615 21.8908 7.98699 22.0189 7.95327 22.1416C7.92098 22.2647 7.86472 22.3801 7.78771 22.4814C7.7107 22.5826 7.61446 22.6677 7.50451 22.7317C7.39164 22.803 7.26523 22.8501 7.13324 22.8701C7.00125 22.8901 6.86655 22.8826 6.73763 22.8479C6.60871 22.8133 6.48835 22.7523 6.38415 22.6689C6.27996 22.5855 6.19417 22.4813 6.13221 22.3631ZM22.3957 12.0084C22.5444 11.4545 22.5256 10.8689 22.3416 10.3257C22.1576 9.78247 21.8166 9.30602 21.3618 8.95659C20.907 8.60715 20.3588 8.40043 19.7865 8.36257C19.2142 8.3247 18.6435 8.45739 18.1466 8.74386C17.6497 9.03033 17.249 9.4577 16.995 9.97195C16.741 10.4862 16.6452 11.0642 16.7197 11.6329C16.7942 12.2016 17.0357 12.7354 17.4136 13.1668C17.7915 13.5983 18.2888 13.908 18.8428 14.0568C19.5855 14.2563 20.3771 14.1526 21.0434 13.7684C21.7097 13.3843 22.1961 12.7512 22.3957 12.0084Z" fill="#0F172B" stroke="#0F172B" />
                            </svg>
                            <div class="text-17 font-bold">
                                <span class="inline-block">
                                    اتاق فرارهای ترسناک
                                </span>
                                <span class="font-black inline-block">
                                    <?= $city_name ?>
                                </span>
                            </div>
                        </h2>
                    </div>
                    <div class="relative content-center hidden md:block">
                        <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                            <div class="flex gap-2">
                                <button type="button" data-input="scary-room-product" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-8 py-1 transition" disabled>
                                    داغ ترین
                                </button>
                                <button type="button" data-input="scary-room-product" data-params='sort_type:"topsale"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    پرفروش‌ترین
                                </button>
                                <button type="button" data-input="scary-room-product" data-params='sort_type:"popular"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    محبوب ترین
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:hidden">
                    <div class="relative block md:hidden">
                        <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                            <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                <button type="button" data-input="scary-room-product" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                    داغ ترین
                                </button>
                                <button type="button" data-input="scary-room-product" data-params='sort_type:"topsale"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    پرفروش‌ترین
                                </button>
                                <button type="button" data-input="scary-room-product" data-params='sort_type:"popular"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    محبوب ترین
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal">
                <div class="embla__viewport">
                    <div id="scary-room-product-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5 child:relative">
                        <?= $horror_escaperooms->products ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev scary-room-product-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next scary-room-product-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
        </section>
    <?php endif; ?>
    <?php endif;

/*===============================================================*/
// اتاق فرارهای غیرترسناک شهر

if ($is_escaperoom) :
    $params = [
        'city_id' => [$term_id],
    ];
    $args = [
        'source'    => 'typecity_page_genre_nonhorror',
        'params'    => $params,
    ];
    $nonhorror_escaperooms = ez_products_snapshot_swiper($args);

    if (!is_null($nonhorror_escaperooms->products) and !empty($nonhorror_escaperooms->products) and (strlen($nonhorror_escaperooms->products) > 0)):
    ?>
        <section class="max-w-full py-4 md:py-5 lg:py-9">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="noscary-room-product" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest","city_id":[<?= $params['city_id'][0] ?>]}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.13221 22.3631L5.17199 20.6839L3.49962 21.6559C3.27661 21.7845 3.01166 21.8192 2.76306 21.7525C2.51446 21.6857 2.30256 21.5229 2.17399 21.2999C2.04542 21.0769 2.01071 20.8119 2.07748 20.5633C2.14426 20.3147 2.30706 20.1028 2.53007 19.9742L15.0126 12.7777C14.635 11.6414 14.6927 10.4054 15.1745 9.30922C15.6562 8.21302 16.5277 7.33467 17.6201 6.84436C18.7125 6.35405 19.948 6.2867 21.0872 6.65535C22.2264 7.024 23.1883 7.80241 23.7863 8.83974C24.3844 9.87708 24.5761 11.0995 24.3243 12.2701C24.0725 13.4407 23.3951 14.4762 22.4234 15.1759C21.4518 15.8756 20.255 16.1896 19.0649 16.0573C17.8749 15.9249 16.7763 15.3555 15.9821 14.4594L10.1934 17.7889L11.1655 19.4613C11.2286 19.5717 11.2692 19.6936 11.2851 19.8198C11.301 19.946 11.2919 20.0741 11.2582 20.1968C11.2259 20.3198 11.1696 20.4353 11.0926 20.5366C11.0156 20.6378 10.9194 20.7229 10.8094 20.7869C10.6991 20.8511 10.5772 20.8928 10.4507 20.9096C10.3242 20.9265 10.1956 20.9181 10.0724 20.885C9.94917 20.8519 9.83371 20.7947 9.73268 20.7168C9.63165 20.6388 9.54705 20.5416 9.48377 20.4308L8.57023 18.7642L6.88853 19.7337L7.86059 21.4061C7.92368 21.5165 7.96434 21.6384 7.98025 21.7646C7.99615 21.8908 7.98699 22.0189 7.95327 22.1416C7.92098 22.2647 7.86472 22.3801 7.78771 22.4814C7.7107 22.5826 7.61446 22.6677 7.50451 22.7317C7.39164 22.803 7.26523 22.8501 7.13324 22.8701C7.00125 22.8901 6.86655 22.8826 6.73763 22.8479C6.60871 22.8133 6.48835 22.7523 6.38415 22.6689C6.27996 22.5855 6.19417 22.4813 6.13221 22.3631ZM22.3957 12.0084C22.5444 11.4545 22.5256 10.8689 22.3416 10.3257C22.1576 9.78247 21.8166 9.30602 21.3618 8.95659C20.907 8.60715 20.3588 8.40043 19.7865 8.36257C19.2142 8.3247 18.6435 8.45739 18.1466 8.74386C17.6497 9.03033 17.249 9.4577 16.995 9.97195C16.741 10.4862 16.6452 11.0642 16.7197 11.6329C16.7942 12.2016 17.0357 12.7354 17.4136 13.1668C17.7915 13.5983 18.2888 13.908 18.8428 14.0568C19.5855 14.2563 20.3771 14.1526 21.0434 13.7684C21.7097 13.3843 22.1961 12.7512 22.3957 12.0084Z" fill="#0F172B" stroke="#0F172B" />
                            </svg>
                            <div class="text-17 font-bold">
                                <span class="inline-block">
                                    اتاق فرارهای غیرترسناک و هیجانی
                                </span>
                                <span class="font-black inline-block">
                                    <?= $city_name ?>
                                </span>
                            </div>
                        </h2>
                    </div>
                    <div class="relative content-center hidden md:block">
                        <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                            <div class="flex gap-2">
                                <button type="button" data-input="noscary-room-product" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-8 py-1 transition" disabled>
                                    داغ ترین
                                </button>
                                <button type="button" data-input="noscary-room-product" data-params='sort_type:"topsale"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    پرفروش‌ترین
                                </button>
                                <button type="button" data-input="noscary-room-product" data-params='sort_type:"popular"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    محبوب ترین
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:hidden">
                    <div class="relative block md:hidden">
                        <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                            <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                <button type="button" data-input="noscary-room-product" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                    داغ ترین
                                </button>
                                <button type="button" data-input="noscary-room-product" data-params='sort_type:"topsale"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    پرفروش‌ترین
                                </button>
                                <button type="button" data-input="noscary-room-product" data-params='sort_type:"popular"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    محبوب ترین
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal">
                <div class="embla__viewport">
                    <div id="noscary-room-product-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5 child:relative">
                        <?= $nonhorror_escaperooms->products ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev noscary-room-product-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next noscary-room-product-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
        </section>
    <?php endif; ?>
    <?php endif;

/*===============================================================*/
// اتاق فرارهای هیجانی شهر

if (get_current_user_id() == 3325) :

    if ($is_escaperoom) :
        $params = [
            'city_id' => [$term_id],
        ];
        $args = [
            'source'    => 'typecity_page_genre_exciting',
            'params'    => $params,
        ];
        $nonhorror_escaperooms = ez_products_snapshot_swiper($args);

        if (!is_null($nonhorror_escaperooms->products) and !empty($nonhorror_escaperooms->products) and (strlen($nonhorror_escaperooms->products) > 0)) : ?>
            <section class="max-w-full py-4 md:py-5 lg:py-9">
                <div class="mb-6 md:mb-8">
                    <input type="hidden" id="noscary-room-product" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest","city_id":[<?= $params['city_id'][0] ?>]}'>
                    <div class="flex justify-between">
                        <div class="items-center gap-6 md:flex">
                            <h2 class="flex items-center gap-4">
                                <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9" fill="none">
                                        <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                            fill="#09192D" stroke="white" stroke-width="1.5" />
                                    </svg>
                                </div>
                                <div class="text-13 font-bold md:text-15">
                                    اتاق فرارهای هیجانی <?= $city_name ?>
                                </div>
                            </h2>
                        </div>
                        <div class="relative content-center hidden md:block">
                            <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                                <div class="flex gap-2">
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"hottest"'
                                        class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-5 py-1 transition" disabled>
                                        داغ ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"topsale"'
                                        class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-5 hover:bg-primary-600 hover:text-white">
                                        پرفروش‌ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"popular"'
                                        class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-5 hover:bg-primary-600 hover:text-white">
                                        محبوب ترین
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:hidden">
                        <div class="relative block md:hidden">
                            <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                                <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"hottest"'
                                        class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                        داغ ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"topsale"'
                                        class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                        پرفروش‌ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"popular"'
                                        class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                        محبوب ترین
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden embla_normal">
                    <div class="embla__viewport">
                        <div id="noscary-room-product-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5 child:relative">
                            <?= $nonhorror_escaperooms->products ?>
                        </div>
                    </div>
                    <button class="embla__button embla__button--prev noscary-room-product-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                    <button class="embla__button embla__button--next noscary-room-product-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
            </section>
        <?php endif; ?>
        <?php endif;

endif;

/*===============================================================*/
// اتاق فرارهای خانوادگی شهر

if (get_current_user_id() == 3325) :

    if ($is_escaperoom) :
        $params = [
            'city_id' => [$term_id],
        ];
        $args = [
            'source'    => 'typecity_page_genre_family',
            'params'    => $params,
        ];
        $nonhorror_escaperooms = ez_products_snapshot_swiper($args);

        if (!is_null($nonhorror_escaperooms->products) and !empty($nonhorror_escaperooms->products) and (strlen($nonhorror_escaperooms->products) > 0)) : ?>
            <section class="max-w-full py-4 md:py-5 lg:py-9">
                <div class="mb-6 md:mb-8">
                    <input type="hidden" id="noscary-room-product" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest","city_id":[<?= $params['city_id'][0] ?>]}'>
                    <div class="flex justify-between">
                        <div class="items-center gap-6 md:flex">
                            <h2 class="flex items-center gap-4">
                                <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9" fill="none">
                                        <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                            fill="#09192D" stroke="white" stroke-width="1.5" />
                                    </svg>
                                </div>
                                <div class="text-13 font-bold md:text-15">
                                    اتاق فرارهای غیرترسناک , خانوادگی <?= $city_name ?>
                                </div>
                            </h2>
                        </div>
                        <div class="relative content-center hidden md:block">
                            <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                                <div class="flex gap-2">
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"hottest"'
                                        class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-5 py-1 transition" disabled>
                                        داغ ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"topsale"'
                                        class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-5 hover:bg-primary-600 hover:text-white">
                                        پرفروش‌ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"popular"'
                                        class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-5 hover:bg-primary-600 hover:text-white">
                                        محبوب ترین
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:hidden">
                        <div class="relative block md:hidden">
                            <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                                <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"hottest"'
                                        class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                        داغ ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"topsale"'
                                        class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                        پرفروش‌ترین
                                    </button>
                                    <button type="button" data-input="noscary-room-product" data-params='sort_type:"popular"'
                                        class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                        محبوب ترین
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden embla_normal">
                    <div class="embla__viewport">
                        <div id="noscary-room-product-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5 child:relative">
                            <?= $nonhorror_escaperooms->products ?>
                        </div>
                    </div>
                    <button class="embla__button embla__button--prev noscary-room-product-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                    <button class="embla__button embla__button--next noscary-room-product-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
            </section>
        <?php endif; ?>
    <?php endif;

endif;

/*===============================================================*/
//تخفیف های ویژه

// نمایش تخفیفات فقط برای type های غیر از سینما ترس
if ($product_type != 'سینما ترس') :
    $args = [
        'source' => 'city_page_discounts_event_' . $term_id,
    ];
    $discount_products = ez_products_snapshot_swiper($args);
    if (!is_null($discount_products->products) and !empty($discount_products->products) and (strlen($discount_products->products) > 0)): ?>
        <div class="max-lg:w-screen max-lg:right-1/2 max-lg:left-1/2 max-lg:-ml-50vw max-lg:-mr-50vw relative lg:hidden overflow-hidden">
            <input type="hidden" id="discount-events" data-source="<?= $args['source'] ?>" data-params='{"schedule":-1}'>
            <div class="flex justify-between relative">
                <div class="flex">
                    <div class="items-center md:flex gap-0 lg:[&>h2]:bg-slate-50 lg:[&>h2]:h-full lg:[&>h2]:rounded-tr-4xl [&>h2_b]:text-secondary-500">
                        <h2 class="flex items-center gap-4 absolute top-5 right-5">
                            <div>
                                <img alt="" loading="lazy" width="44" height="44" decoding="async"
                                    data-nimg="1"
                                    class="w-8 h-8 lg:w-11 lg:h-11 object-cover"
                                    src="<?= Theme_ASSET_URL ?>/images/icons/off-icon.avif">
                            </div>
                            <span class="text-25 font-black">
                                <b>تخفیف داغ هفته</b>
                            </span>
                        </h2>
                        <div class="lg:-190-dlg: lg:-z-1">
                            <svg width="402" height="89" viewBox="0 0 402 89" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M95.503 13.2961C105.826 3.85027 119.824 0 133.816 0L402 0V89H0C53.9409 82.0339 60.2022 45.5955 95.503 13.2961Z" fill="#EFF3F7" />
                            </svg>
                        </div>
                        <div class="absolute top-0 left-0 -z-">
                            <img src="<?= Theme_ASSET_URL ?>images/off-top-back-sm.avif" alt="" class="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="max-w-full py-4 md:py-5 lg:py-9 max-md:bg-surface-sunken max-md:max-w-none max-md:-ml-4 max-md:-mr-4 max-md:py-2.5 max-md:px-8 relative">
            <div class="mb-6 md:mb-8 lg:-mb-px max-lg:hidden">
                <input type="hidden" id="discount-events" data-source="<?= $args['source'] ?>" data-params='{"schedule":-1}'>
                <div class="flex justify-between relative">
                    <div class="flex">
                        <div class="items-center md:flex gap-0 lg:[&>h2]:bg-slate-50 lg:[&>h2]:h-full lg:[&>h2]:rounded-tr-4xl lg:[&>h2]:pr-8 [&>h2_b]:text-secondary-500">
                            <h2 class="flex items-center gap-4">
                                <div class="hidden md:block">
                                    <img alt="" loading="lazy" width="44" height="44" decoding="async"
                                        data-nimg="1"
                                        class="w-11 h-11 object-cover"
                                        src="<?= Theme_ASSET_URL ?>/images/icons/off-icon.avif">
                                </div>
                                <span class="text-29 font-bold">
                                    <b>تخفیف داغ هفته</b>
                                </span>
                            </h2>
                            <div class="lg:-190-dlg: lg:-z-1">
                                <svg class="max-lg:hidden" width="528" height="89" viewBox="0 0 528 89" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M109.377 11.1254C119.447 3.22854 132.246 0 145.043 0L528 0V89H0C61.6012 81.8773 67.5282 43.9431 109.377 11.1254Z" fill="#EFF3F7" />
                                </svg>
                                <svg class="lg:hidden" width="402" height="89" viewBox="0 0 402 89" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M95.503 13.2961C105.826 3.85027 119.824 0 133.816 0L402 0V89H0C53.9409 82.0339 60.2022 45.5955 95.503 13.2961Z" fill="#EFF3F7" />
                                </svg>
                            </div>
                            <div class="absolute lg:top-px lg:left-0 lg:-z-lg:">
                                <div class="absolute left-12 top-6 countdown-timer rounded-2xl">
                                    <div class="flex items-center gap-2">
                                        <!-- Seconds -->
                                        <div class="countdown-card rounded-xl w-12 h-12 flex items-center justify-center">
                                            <span class="text-21 font-bold text-red-600 countdown-seconds">00</span>
                                        </div>
                                        <span class="text-white text-17 font-bold">:</span>
                                        <!-- Minutes -->
                                        <div class="countdown-card rounded-xl w-12 h-12 flex items-center justify-center">
                                            <span class="text-21 font-bold text-red-600 countdown-minutes">00</span>
                                        </div>
                                        <span class="text-white text-17 font-bold">:</span>
                                        <!-- Hours -->
                                        <div class="countdown-card rounded-xl w-12 h-12 flex items-center justify-center">
                                            <span class="text-21 font-bold text-red-600 countdown-hours">00</span>
                                        </div>
                                        <span class="text-white text-17 font-bold">:</span>
                                        <!-- Days -->
                                        <div class="countdown-card rounded-xl w-12 h-12 flex items-center justify-center">
                                            <span class="text-21 font-bold text-red-600 countdown-days">00</span>
                                        </div>
                                    </div>
                                </div>
                                <img src="<?= Theme_ASSET_URL ?>images/off-top-back-lg.avif" alt="" class="max-lg:hidden">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:py-8 lg:px-25.5 lg:bg-slate-50 rounded-4xl rounded-tr-none">
                <div class="relative w-full max-sm:max-w-bleed-2 max-sm:w-bleed-2 max-sm:-mr-4">
                    <div class="relative overflow-hidden embla_normal slider-event" data-slider-event="discount-slider">
                        <div class="embla__viewport">
                            <div id="discount-events-slider" class="embla__container child:bg-white child:p-2.5 md:child:p-5 child:rounded-3xl flex gap-x-4 md:gap-x-6 child:shrink-0 child:grow-0 child:w-d176 md:child:w-d230"> <?= $discount_products->products ?> </div>
                        </div>
                        <div class="hidden lg:block lg:opacity-80 [&amp;>button]:block [&amp;>button]:h-full [&amp;>button]:top-0 [&amp;>button]:translate-y-0">
                            <button class="absolute right-0 rotate-180 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--prev discount-events-btn top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Previous slide" aria-controls="discount-events-slider" aria-disabled="false">
                                <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                    </svg>
                                </div>
                            </button>
                            <button class="absolute left-0 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--next discount-events-btn top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Next slide" aria-controls="discount-events-slider" aria-disabled="false">
                                <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="md:hidden relative max-lg:w-screen max-lg:right-1/2 max-lg:left-1/2 max-lg:-ml-50vw max-lg:-mr-50vw bg-cover bg-center bg-no-repeat mb-12" style="background: url('<?= Theme_ASSET_URL ?>images/off-bg-sm-bottom.avif')">
            <div class="flex items-center justify-center gap-2 py-4">
                <!-- Seconds -->
                <div class="countdown-card rounded-xl w-10 h-10 flex items-center justify-center">
                    <span class="text-21 font-bold text-red-600 countdown-seconds">00</span>
                </div>
                <span class="text-white text-17 font-bold">:</span>
                <!-- Minutes -->
                <div class="countdown-card rounded-xl w-10 h-10 flex items-center justify-center">
                    <span class="text-21 font-bold text-red-600 countdown-minutes">00</span>
                </div>
                <span class="text-white text-17 font-bold">:</span>
                <!-- Hours -->
                <div class="countdown-card rounded-xl w-10 h-10 flex items-center justify-center">
                    <span class="text-21 font-bold text-red-600 countdown-hours">00</span>
                </div>
                <span class="text-white text-17 font-bold">:</span>
                <!-- Days -->
                <div class="countdown-card rounded-xl w-10 h-10 flex items-center justify-center">
                    <span class="text-21 font-bold text-red-600 countdown-days">00</span>
                </div>
            </div>
        </div>
<?php endif;
endif; // end if product_type != 'سینما ترس'

/*===============================================================*/
// اتاق فرارهای جدید

if ($is_escaperoom) :
    $params = [
        'city_id' => [$term_id],
        'sort_type' => 'recent',
    ];
    $args = [
        'source'        => 'typecity_page_genre_', // بدون ژانر فقط جدیدترین ها
        'params'        => $params,
        'active_soon'   => true,
    ];
    $nonhorror_escaperooms = ez_products_snapshot_swiper($args);

    if (!is_null($nonhorror_escaperooms->products) and !empty($nonhorror_escaperooms->products) and (strlen($nonhorror_escaperooms->products) > 0)): ?>
        <section class="max-w-full py-4 md:py-5 lg:py-9">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="noscary-room-product" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest","city_id":[<?= $params['city_id'][0] ?>]}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.13221 22.3631L5.17199 20.6839L3.49962 21.6559C3.27661 21.7845 3.01166 21.8192 2.76306 21.7525C2.51446 21.6857 2.30256 21.5229 2.17399 21.2999C2.04542 21.0769 2.01071 20.8119 2.07748 20.5633C2.14426 20.3147 2.30706 20.1028 2.53007 19.9742L15.0126 12.7777C14.635 11.6414 14.6927 10.4054 15.1745 9.30922C15.6562 8.21302 16.5277 7.33467 17.6201 6.84436C18.7125 6.35405 19.948 6.2867 21.0872 6.65535C22.2264 7.024 23.1883 7.80241 23.7863 8.83974C24.3844 9.87708 24.5761 11.0995 24.3243 12.2701C24.0725 13.4407 23.3951 14.4762 22.4234 15.1759C21.4518 15.8756 20.255 16.1896 19.0649 16.0573C17.8749 15.9249 16.7763 15.3555 15.9821 14.4594L10.1934 17.7889L11.1655 19.4613C11.2286 19.5717 11.2692 19.6936 11.2851 19.8198C11.301 19.946 11.2919 20.0741 11.2582 20.1968C11.2259 20.3198 11.1696 20.4353 11.0926 20.5366C11.0156 20.6378 10.9194 20.7229 10.8094 20.7869C10.6991 20.8511 10.5772 20.8928 10.4507 20.9096C10.3242 20.9265 10.1956 20.9181 10.0724 20.885C9.94917 20.8519 9.83371 20.7947 9.73268 20.7168C9.63165 20.6388 9.54705 20.5416 9.48377 20.4308L8.57023 18.7642L6.88853 19.7337L7.86059 21.4061C7.92368 21.5165 7.96434 21.6384 7.98025 21.7646C7.99615 21.8908 7.98699 22.0189 7.95327 22.1416C7.92098 22.2647 7.86472 22.3801 7.78771 22.4814C7.7107 22.5826 7.61446 22.6677 7.50451 22.7317C7.39164 22.803 7.26523 22.8501 7.13324 22.8701C7.00125 22.8901 6.86655 22.8826 6.73763 22.8479C6.60871 22.8133 6.48835 22.7523 6.38415 22.6689C6.27996 22.5855 6.19417 22.4813 6.13221 22.3631ZM22.3957 12.0084C22.5444 11.4545 22.5256 10.8689 22.3416 10.3257C22.1576 9.78247 21.8166 9.30602 21.3618 8.95659C20.907 8.60715 20.3588 8.40043 19.7865 8.36257C19.2142 8.3247 18.6435 8.45739 18.1466 8.74386C17.6497 9.03033 17.249 9.4577 16.995 9.97195C16.741 10.4862 16.6452 11.0642 16.7197 11.6329C16.7942 12.2016 17.0357 12.7354 17.4136 13.1668C17.7915 13.5983 18.2888 13.908 18.8428 14.0568C19.5855 14.2563 20.3771 14.1526 21.0434 13.7684C21.7097 13.3843 22.1961 12.7512 22.3957 12.0084Z" fill="#0F172B" stroke="#0F172B" />
                            </svg>
                            <div class="text-17 font-bold">
                                <span class="inline-block">
                                    جدیدترین اتاق فرارهای
                                </span>
                                <span class="font-black inline-block">
                                    <?= $city_name ?>
                                </span>
                            </div>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal">
                <div class="embla__viewport">
                    <div id="noscary-room-product-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5 child:relative">
                        <?= $nonhorror_escaperooms->products ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev noscary-room-product-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next noscary-room-product-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
        </section>
    <?php endif; ?>
<?php endif;

// کالکشن های محبوب (فقط برای type های غیر از سینما ترس)
if ($product_type != 'سینما ترس') {
  //  get_template_part("template/layout/collections");
}

if ($product_type != 'سینما ترس') : ?>
    <!-- بخش همه برای type های غیر از سینما ترس -->

    <div class="pb-12">
        <div class="flex justify-between">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.13221 22.3631L5.17199 20.6839L3.49962 21.6559C3.27661 21.7845 3.01166 21.8192 2.76306 21.7525C2.51446 21.6857 2.30256 21.5229 2.17399 21.2999C2.04542 21.0769 2.01071 20.8119 2.07748 20.5633C2.14426 20.3147 2.30706 20.1028 2.53007 19.9742L15.0126 12.7777C14.635 11.6414 14.6927 10.4054 15.1745 9.30922C15.6562 8.21302 16.5277 7.33467 17.6201 6.84436C18.7125 6.35405 19.948 6.2867 21.0872 6.65535C22.2264 7.024 23.1883 7.80241 23.7863 8.83974C24.3844 9.87708 24.5761 11.0995 24.3243 12.2701C24.0725 13.4407 23.3951 14.4762 22.4234 15.1759C21.4518 15.8756 20.255 16.1896 19.0649 16.0573C17.8749 15.9249 16.7763 15.3555 15.9821 14.4594L10.1934 17.7889L11.1655 19.4613C11.2286 19.5717 11.2692 19.6936 11.2851 19.8198C11.301 19.946 11.2919 20.0741 11.2582 20.1968C11.2259 20.3198 11.1696 20.4353 11.0926 20.5366C11.0156 20.6378 10.9194 20.7229 10.8094 20.7869C10.6991 20.8511 10.5772 20.8928 10.4507 20.9096C10.3242 20.9265 10.1956 20.9181 10.0724 20.885C9.94917 20.8519 9.83371 20.7947 9.73268 20.7168C9.63165 20.6388 9.54705 20.5416 9.48377 20.4308L8.57023 18.7642L6.88853 19.7337L7.86059 21.4061C7.92368 21.5165 7.96434 21.6384 7.98025 21.7646C7.99615 21.8908 7.98699 22.0189 7.95327 22.1416C7.92098 22.2647 7.86472 22.3801 7.78771 22.4814C7.7107 22.5826 7.61446 22.6677 7.50451 22.7317C7.39164 22.803 7.26523 22.8501 7.13324 22.8701C7.00125 22.8901 6.86655 22.8826 6.73763 22.8479C6.60871 22.8133 6.48835 22.7523 6.38415 22.6689C6.27996 22.5855 6.19417 22.4813 6.13221 22.3631ZM22.3957 12.0084C22.5444 11.4545 22.5256 10.8689 22.3416 10.3257C22.1576 9.78247 21.8166 9.30602 21.3618 8.95659C20.907 8.60715 20.3588 8.40043 19.7865 8.36257C19.2142 8.3247 18.6435 8.45739 18.1466 8.74386C17.6497 9.03033 17.249 9.4577 16.995 9.97195C16.741 10.4862 16.6452 11.0642 16.7197 11.6329C16.7942 12.2016 17.0357 12.7354 17.4136 13.1668C17.7915 13.5983 18.2888 13.908 18.8428 14.0568C19.5855 14.2563 20.3771 14.1526 21.0434 13.7684C21.7097 13.3843 22.1961 12.7512 22.3957 12.0084Z" fill="#0F172B" stroke="#0F172B" />
                    </svg>
                    <div class="text-17 font-bold">
                        <span class="inline-block">
                            همه
                        </span>
                        <span class="inline-block">
                            <?= $product_type ?>
                        </span>
                        <span class="inline-block">
                            های
                        </span>
                        <span class="font-black inline-block">
                            <?= $city_name ?>
                        </span>
                    </div>
                </h2>
            </div>
        </div>
        <div id="product-container"></div>
    </div>
<?php
else : ?>

    <!-- بخش مخصوص سینما ترس با متن انیمیشنی و shape های جذاب -->
    <div class="horror-all-section relative">
        <div class="container mx-auto px-4">
            <div class="horror-all-wrapper relative">
                <!-- Decorative Shapes -->
                <div class="horror-all-shapes absolute inset-0 pointer-events-none">
                    <div class="horror-all-shape horror-all-shape-1"></div>
                    <div class="horror-all-shape horror-all-shape-2"></div>
                    <div class="horror-all-shape horror-all-shape-3"></div>
                    <div class="horror-all-shape horror-all-shape-4"></div>
                    <div class="horror-all-shape horror-all-shape-5"></div>
                </div>

                <!-- Horror Icons -->
                <div class="horror-all-icons absolute inset-0 pointer-events-none z-5">
                    <div class="horror-icon horror-icon-1">
                        <svg class="w-16 h-16 md:w-20 md:h-20 lg:w-24 lg:h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="horror-icon horror-icon-2">
                        <svg class="w-12 h-12 md:w-16 md:h-16 lg:w-20 lg:h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </div>
                    <div class="horror-icon horror-icon-3">
                        <svg class="w-14 h-14 md:w-18 md:h-18 lg:w-22 lg:h-22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div class="horror-icon horror-icon-4">
                        <svg class="w-10 h-10 md:w-14 md:h-14 lg:w-18 lg:h-18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <div class="horror-icon horror-icon-5">
                        <svg class="w-18 h-18 md:w-22 md:h-22 lg:w-26 lg:h-26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Simple Title -->
                <div class="horror-all-text-wrapper text-center relative z-10 text-3xl md:text-4xl flex items-center justify-center">
                    <span class="inline-block font-extrabold ml-1">لیست</span>
                    <h1 class="font-black">
                        <span class="inline-block ml-1">سینماترس‌های</span>
                        <?= $city_name ?>
                    </h1>
                </div>
                <?php 
                $list_description = get_term_meta($term_id, '_ez_product_cat_list_description', true);
                if ($list_description) : ?>
                    <div class="mt-6 mb-4 text-center relative z-10 max-w-d500">
                        <p class="text-sm md:text-base text-slate-300 leading-relaxed max-w-3xl mx-auto">
                            <?= nl2br(esc_html($list_description)) ?>
                        </p>
                    </div>
                <?php endif; ?>
                <!-- Product Container -->
                <div id="product-container" class="relative z-10 mt-8"></div>
            </div>
        </div>

        <style>
            /* Horror All Section Styles */
            .horror-all-section {
                min-height: 200px;
            }

            .horror-all-wrapper {
                padding: 40px 0;
            }

            /* Decorative Shapes */
            .horror-all-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(50px);
                opacity: 0.2;
                animation: horrorAllShapeFloat 20s ease-in-out infinite;
            }

            .horror-all-shape-1 {
                width: 300px;
                height: 300px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.6), transparent);
                top: -150px;
                left: 10%;
                animation-delay: 0s;
            }

            .horror-all-shape-2 {
                width: 250px;
                height: 250px;
                background: radial-gradient(circle, rgba(153, 27, 27, 0.5), transparent);
                top: 20%;
                right: 15%;
                animation-delay: 4s;
            }

            .horror-all-shape-3 {
                width: 200px;
                height: 200px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.55), transparent);
                bottom: 10%;
                left: 20%;
                animation-delay: 8s;
            }

            .horror-all-shape-4 {
                width: 180px;
                height: 180px;
                background: radial-gradient(circle, rgba(127, 29, 29, 0.5), transparent);
                top: 50%;
                left: 5%;
                animation-delay: 12s;
            }

            .horror-all-shape-5 {
                width: 220px;
                height: 220px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.5), transparent);
                bottom: 20%;
                right: 10%;
                animation-delay: 16s;
            }

            @keyframes horrorAllShapeFloat {

                0%,
                100% {
                    transform: translate(0, 0) scale(1);
                    opacity: 0.2;
                }

                25% {
                    transform: translate(40px, -50px) scale(1.1);
                    opacity: 0.3;
                }

                50% {
                    transform: translate(-30px, 40px) scale(0.9);
                    opacity: 0.15;
                }

                75% {
                    transform: translate(50px, 30px) scale(1.05);
                    opacity: 0.25;
                }
            }

            /* Horror Icons Styles */
            .horror-all-icons {
                z-index: 5;
            }

            .horror-icon {
                position: absolute;
                color: rgba(220, 38, 38, 0.3);
                filter: drop-shadow(0 0 10px rgba(220, 38, 38, 0.4));
                animation: horrorIconFloat 8s ease-in-out infinite;
            }

            .horror-icon-1 {
                top: 10%;
                left: 5%;
                animation-delay: 0s;
            }

            .horror-icon-2 {
                top: 20%;
                right: 8%;
                animation-delay: 1.5s;
            }

            .horror-icon-3 {
                bottom: 30%;
                left: 10%;
                animation-delay: 3s;
            }

            .horror-icon-4 {
                bottom: 15%;
                right: 12%;
                animation-delay: 4.5s;
            }

            .horror-icon-5 {
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                animation-delay: 6s;
            }

            @keyframes horrorIconFloat {

                0%,
                100% {
                    transform: translate(0, 0) rotate(0deg) scale(1);
                    opacity: 0.3;
                }

                25% {
                    transform: translate(20px, -30px) rotate(5deg) scale(1.1);
                    opacity: 0.5;
                }

                50% {
                    transform: translate(-15px, 25px) rotate(-5deg) scale(0.9);
                    opacity: 0.4;
                }

                75% {
                    transform: translate(25px, 15px) rotate(3deg) scale(1.05);
                    opacity: 0.45;
                }
            }

            /* Animated Text with Enhanced Effects */
            .horror-all-title {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
                gap: 12px 16px;
            }

            .horror-all-word {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                position: relative;
                background: linear-gradient(135deg,
                        #ffffff 0%,
                        #fca5a5 25%,
                        #dc2626 50%,
                        #991b1b 75%,
                        #dc2626 100%);
                background-size: 300% 300%;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                animation: horrorAllWordPulse 3s ease-in-out infinite, horrorAllGradient 8s ease-in-out infinite;
                text-shadow: 0 0 30px rgba(220, 38, 38, 0.5);
            }

            .horror-all-word-1 {
                animation-delay: 0s;
            }

            .horror-all-word-2 {
                animation-delay: 0.3s;
            }

            .horror-all-word-3 {
                animation-delay: 0.6s;
            }

            .horror-all-word-4 {
                animation-delay: 0.9s;
            }

            .horror-all-word-5 {
                animation-delay: 1.2s;
            }

            .horror-icon-inline {
                display: inline-block;
                font-size: 0.9em;
                line-height: 1;
                animation: horrorIconInlinePulse 2s ease-in-out infinite;
                filter: drop-shadow(0 0 10px rgba(220, 38, 38, 0.8)) brightness(1.2);
                -webkit-text-fill-color: initial !important;
                background: none !important;
                color: #dc2626 !important;
                opacity: 1 !important;
                text-shadow:
                    0 0 10px rgba(220, 38, 38, 0.9),
                    0 0 20px rgba(220, 38, 38, 0.6);
                position: relative;
                z-index: 1;
            }

            .horror-icon-inline-1 {
                animation-delay: 0s;
            }

            .horror-icon-inline-2 {
                animation-delay: 0.4s;
            }

            .horror-icon-inline-3 {
                animation-delay: 0.8s;
            }

            .horror-icon-inline-4 {
                animation-delay: 1.2s;
            }

            @keyframes horrorAllWordPulse {

                0%,
                100% {
                    filter: brightness(1) drop-shadow(0 0 20px rgba(220, 38, 38, 0.7));
                    transform: scale(1) translateY(0);
                }

                50% {
                    filter: brightness(1.5) drop-shadow(0 0 40px rgba(220, 38, 38, 1));
                    transform: scale(1.08) translateY(-5px);
                }
            }

            @keyframes horrorIconInlinePulse {

                0%,
                100% {
                    transform: scale(1) rotate(0deg);
                    opacity: 1;
                }

                25% {
                    transform: scale(1.2) rotate(-10deg);
                    opacity: 0.9;
                }

                50% {
                    transform: scale(0.9) rotate(10deg);
                    opacity: 1;
                }

                75% {
                    transform: scale(1.15) rotate(-5deg);
                    opacity: 0.95;
                }
            }

            @keyframes horrorAllGradient {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            /* Additional Word Animations */
            .horror-all-word::before {
                content: '';
                position: absolute;
                inset: -5px;
                background: radial-gradient(ellipse, rgba(220, 38, 38, 0.4), transparent);
                filter: blur(15px);
                opacity: 0;
                animation: horrorAllWordShadow 3s ease-in-out infinite;
                z-index: -1;
            }

            .horror-all-word-1::before {
                animation-delay: 0s;
            }

            .horror-all-word-2::before {
                animation-delay: 0.2s;
            }

            .horror-all-word-3::before {
                animation-delay: 0.4s;
            }

            .horror-all-word-4::before {
                animation-delay: 0.6s;
            }

            .horror-all-word-5::before {
                animation-delay: 0.8s;
            }

            @keyframes horrorAllWordShadow {

                0%,
                100% {
                    opacity: 0;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.6;
                    transform: scale(1.2);
                }
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .horror-all-title {
                    font-size: 2rem;
                    gap: 8px 12px;
                }

                .horror-all-shape {
                    filter: blur(40px);
                }
            }
        </style>
    </div>
<?php endif; 

/*===============================================================*/
// سوالات متداول (FAQ)

$faqs = get_term_meta($term_id, '_ez_product_cat_faqs', true);
if (!empty($faqs) && is_array($faqs)) : ?>
    <section class="faq-section mt-12 mb-8 lg:mb-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl lg:text-3xl font-bold text-textColor mb-6 lg:mb-8 text-center">
                سوالات متداول
            </h2>
            <div class="max-w-4xl mx-auto">
                <?php 
                $faq_count = 0;
                foreach ($faqs as $index => $faq) : 
                    $question = isset($faq['question']) ? trim($faq['question']) : '';
                    $answer = isset($faq['answer']) ? trim($faq['answer']) : '';
                    
                    if (empty($question) || empty($answer)) {
                        continue; // اگر سوال یا پاسخ خالی بود، نمایش نده
                    }
                    $faq_count++;
                ?>
                    <div class="faq-item <?= $faq_count > 1 ? 'border-t border-slate-100 pt-6 mt-6' : '' ?>">
                        <div class="faq-question mb-3 lg:mb-4">
                            <h3 class="text-base lg:text-lg font-bold text-textColor">
                                <?= esc_html($question) ?>
                            </h3>
                        </div>
                        <div class="faq-answer">
                            <p class="text-sm lg:text-base text-slate-600 leading-6 lg:leading-7">
                                <?= nl2br(esc_html($answer)) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
    // price filter range
    function roundToNearestThousand(num) {
        return Math.round(num / 1000) * 1000;
    }
    const rangeInput = document.querySelectorAll(".range-input input"),
        priceInput = document.querySelectorAll(".price-input input"),
        range = document.querySelector(".slider .progress");
    let priceGap = 1000;
    rangeInput.forEach((input) => {
        input.addEventListener("input", (e) => {
            let minVal = parseInt(rangeInput[0].value),
                maxVal = parseInt(rangeInput[1].value);

            if (maxVal - minVal < priceGap) {
                if (e.target.className === "range-min") {
                    rangeInput[0].value = maxVal - priceGap;
                } else {
                    rangeInput[1].value = minVal + priceGap;
                }
            } else {
                priceInput[0].setAttribute('value', roundToNearestThousand(minVal).toLocaleString());
                priceInput[1].setAttribute('value', roundToNearestThousand(maxVal).toLocaleString());
                //priceInput[1].value = rangeInput[1].value;
                range.style.right = (minVal / rangeInput[0].max) * 100 + "%";
                range.style.left = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
            }
        });
    });
    jQuery(document).ready(function($) {

        let baseUrlWebService = 'https://' + location.hostname + '/web-service/web-service.php'
        if (location.hostname === 'localhost' || location.hostname === 'wo.escapezoom.local') {
            baseUrlWebService = 'http://' + location.hostname + '/web-service/web-service.php'
        }

        $('.adv-banner').on('click', function() {
            var title = $(this).data('title');
            var href = $(this).find('a').attr('href');
            var currentPage = window.location.href;
            zebline.event.track("adv_banner_click", {
                "title": title,
                "href": href,
                "current_page": currentPage,
            });
        });

        $('form#game-finder-form').on('submit', function(e) {
            e.preventDefault()
            let product_type = $('input[name="product_type"]:checked').val();
            let city_id = $('input[name="city_id"]:checked').val();
            if (!city_id) {
                city_id = -1
            } else {
                city_id = [city_id]
            }
            let count = $('input[name="count"]:checked').val();
            if (!count) {
                count = -1
            }
            let age = $('input[name="age"]:checked').val();
            if (!age) {
                age = -1
            }
            let duration = $('input[name="duration"]:checked').val();
            if (!duration) {
                duration = -1
            }
            let minPrice = parseInt(($('input[name="min_price"]').val() || '0').replace(/,/g, ""), 10);
            let maxPrice = parseInt(($('input[name="max_price"]').val() || '400000').replace(/,/g, ""), 10);
            let scheduleStart = $('input[name="schedule_start"]').val();
            let scheduleEnd = $('input[name="schedule_end"]').val();
            let sort_type = $('input[name="sort_type_final"]').val();
            let currentPage = window.location.href;
            let currentPageTitle = '<?= get_the_title() ?>';
            let currentPageId = <?= get_the_ID() ?>;
            $('#product-container').empty()
            let zeblineData = {
                current_page: currentPage,
                current_page_title: currentPageTitle,
                current_page_id: currentPageId,
                product_type: product_type,
                sort_type: sort_type,
                city_id: city_id,
                count: count,
                age: age,
                duration: duration,
                schedule: [
                    scheduleStart,
                    scheduleEnd
                ],
                price: [
                    minPrice,
                    maxPrice
                ],
            };
            //zebline.event.track('sansyab', zeblineData);
            $.ajax({
                type: 'POST',
                url: baseUrlWebService,
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "source": "home_quick_search",
                        "params": {
                            //   "sort_type":"recent", // sort_type:-1. it's -1 until the user presses sort buttons.
                            "sort_type": sort_type,
                            "page": 1,
                            "product_type": product_type,
                            "city_id": city_id,
                            "count": count,
                            "age": age,
                            "duration": duration,
                            "schedule": [
                                scheduleStart,
                                scheduleEnd
                            ],
                            "price": [
                                minPrice,
                                maxPrice
                            ],
                        }
                    },
                },
                dataType: "json",
                beforeSend: function() {
                    $('#product-container').empty().append('<div class="text-center">لطفا منتظر بمانید<span class="loading-dots"></span></div>')
                },
                success: function(data) {
                    if ((data.products).length > 0) {
                        $('#product-container').empty().append('<section id="product_list_container" class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 2xl:grid-cols-7 child:box-content gap-6"></section>')
                        $('#product-container #product_list_container').append(data.products)
                    } else {
                        $('#product-container').empty().append('<div class="px-16 py-12 text-center border rounded-xl border-slate-100 shadow-12">موردی یافت نشد.</div>')
                    }
                },
            });
        })
        $('#product-container').empty();
        $.ajax({
            type: 'POST',
            url: baseUrlWebService,
            data: {
                "type": "sort_products_get",
                "data": {
                    "source": "home_quick_search",
                    "params": {
                        "product_type": "<?= $product_type ?>",
                        "city_id": <?= json_encode($city_id_term) ?>,
                        "count": -1,
                        "schedule": -1
                    }
                }
            },
            dataType: "json",
            beforeSend: function() {
                $('#product-container').empty().append('<div class="text-center">لطفا منتظر بمانید<span class="loading-dots"></span></div>')
            },
            success: function(data) {
                if ((data.products).length > 0) {
                    $('#product-container').empty().append(`<div><span>${(data.products_id).length}</span> مورد یافت شد:</div>`).append('<section id="product_list_container" class="grid grid-cols-2 justify-between max-lg:gap-5.5 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 2xl:grid-cols-7 child:box-content gap-6"></section>')
                    $('#product-container #product_list_container').append(data.products)
                } else {
                    $('#product-container').empty().append('<div class="px-16 py-12 text-center border rounded-xl border-slate-100 shadow-12">موردی یافت نشد.</div>')
                }
            },
        });
        $('.schedule-btn').on('click', function() {
            let value = $(this).val().slice(1, -1).split(",")
            let day = $(this).attr('data-day')
            let startDay = 8;
            let endDay = 24;
            if (day === 'today') {
                let time = new Date();
                startDay = parseInt(String(time.getHours()).padStart(2, '0')) + 1;
            }
            let schedule_start = value[0];
            let schedule_end = value[1];
            $('input[name=schedule_start]').val(schedule_start).attr('data-limit', schedule_start)
            $('input[name=schedule_end]').val(schedule_end).attr('data-limit', schedule_end)
            $('#min-clock').text(startDay).attr('data-min', startDay).attr('data-max', endDay)
            $('#max-clock').text(endDay).attr('data-min', startDay).attr('data-max', endDay)
        })
        $('input[name=input-rate]').on('click', function() {
            let rate = $(this).val()
            $('#rating').val(rate)
        })
        $('input[name=sort]').on('click', function() {
            let sort = $(this).val()
            $('input[name=sort_type_final]').val(sort)
        })
        $('.operator-clock').on('click', function() {
            let clock = $(this).data('clock');
            let param = $(this).data('params');
            let currentTime = parseInt($('#' + clock).text());
            let minTime = parseInt($('#' + clock).attr('data-min'));
            let maxTime = parseInt($('#' + clock).attr('data-max'));

            if (param === 'plus') {
                if (currentTime < maxTime) {
                    currentTime++;
                }
            } else if (param === 'minus') {
                if (currentTime > minTime) {
                    currentTime--;
                }
            }

            $('#' + clock).text(currentTime);

            // Update schedule_start or schedule_end
            if (clock === 'min-clock') {
                let scheduleStart = $('input[name=schedule_start]');
                let limit = parseInt(scheduleStart.attr('data-limit'));
                let newValue = limit + (currentTime * 3600);
                scheduleStart.val(newValue);
            } else if (clock === 'max-clock') {
                let scheduleCurrent = $('input[name=schedule_start]');
                let scheduleEnd = $('input[name=schedule_end]');
                let limit = parseInt(scheduleCurrent.attr('data-limit'));
                let newValue = limit + (currentTime * 3600);
                scheduleEnd.val(newValue);
            }

            // Sync min and max values
            if (clock === 'min-clock') {
                $('#max-clock').attr('data-min', currentTime);
            } else if (clock === 'max-clock') {
                $('#min-clock').attr('data-max', currentTime);
            }
        });
    })

    // Weekly Countdown Timer (Saturday to Saturday)
    function initWeeklyCountdown() {
        function toPersianDigits(str) {
            return str.replace(/[0-9]/g, function(w) {
                return ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'][parseInt(w)];
            });
        }

        function pad(num) {
            return num.toString().padStart(2, '0');
        }

        function getWeekEndTime() {
            const now = new Date();
            const currentDay = now.getDay(); // 0 = Sunday, 6 = Saturday

            // Calculate days until next Saturday (start of new week)
            let daysUntilSaturday;
            if (currentDay === 6) { // If today is Saturday
                daysUntilSaturday = 7; // Next Saturday (7 days from now)
            } else {
                daysUntilSaturday = (6 - currentDay + 7) % 7; // Days until next Saturday
            }

            const nextSaturday = new Date(now);
            nextSaturday.setDate(now.getDate() + daysUntilSaturday);
            nextSaturday.setHours(0, 0, 0, 0); // Start of Saturday (00:00:00)

            return nextSaturday;
        }

        function updateCountdown() {
            const now = new Date();
            const target = getWeekEndTime();
            const difference = target.getTime() - now.getTime();

            if (difference <= 0) {
                // Timer expired, reset to next week
                clearInterval(window.weeklyCountdownInterval);
                initWeeklyCountdown();
                return;
            }

            const days = Math.floor(difference / (1000 * 60 * 60 * 24));
            const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

            // Update all countdown elements on the page
            const daysElements = document.querySelectorAll('.countdown-days');
            const hoursElements = document.querySelectorAll('.countdown-hours');
            const minutesElements = document.querySelectorAll('.countdown-minutes');
            const secondsElements = document.querySelectorAll('.countdown-seconds');

            daysElements.forEach(el => el.textContent = toPersianDigits(pad(days)));
            hoursElements.forEach(el => el.textContent = toPersianDigits(pad(hours)));
            minutesElements.forEach(el => el.textContent = toPersianDigits(pad(minutes)));
            secondsElements.forEach(el => el.textContent = toPersianDigits(pad(seconds)));
        }

        // Update immediately
        updateCountdown();

        // Update every second
        window.weeklyCountdownInterval = setInterval(updateCountdown, 1000);
    }

    // Initialize countdown when page loads
    document.addEventListener('DOMContentLoaded', initWeeklyCountdown);

    // Copy discount code function
    function copyDiscountCode() {
        const discountCode = 'wel150';
        const copyBtn = event.currentTarget;
        const successMsg = copyBtn.querySelector('.copy-success');
        
        // Copy to clipboard
        navigator.clipboard.writeText(discountCode).then(function() {
            // Show success message
            successMsg.classList.remove('opacity-0');
            successMsg.classList.add('opacity-100');
            
            // Change button icon temporarily
            const icon = copyBtn.querySelector('svg');
            const originalPath = icon.innerHTML;
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
            copyBtn.classList.add('bg-green-500');
            
            // Reset after 2 seconds
            setTimeout(function() {
                successMsg.classList.remove('opacity-100');
                successMsg.classList.add('opacity-0');
                icon.innerHTML = originalPath;
                copyBtn.classList.remove('bg-green-500');
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = discountCode;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                successMsg.classList.remove('opacity-0');
                successMsg.classList.add('opacity-100');
                setTimeout(function() {
                    successMsg.classList.remove('opacity-100');
                    successMsg.classList.add('opacity-0');
                }, 2000);
            } catch (err) {
                console.error('Fallback copy failed: ', err);
            }
            document.body.removeChild(textArea);
        });
    }
</script>