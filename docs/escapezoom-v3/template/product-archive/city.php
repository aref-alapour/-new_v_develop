<?php
global $wpdb;

$city_id = $post_id;

// امروز
$today = date('Y-m-d');
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps($dayAfterTomorrow);

$city_name          = get_the_title($city_id);
$city_categories    = get_post_meta($city_id, 'city_page_product_categories', true);

if (!$city_categories) // برای این صفحه حتما باید دست کم یک دسته بندی لحاظ شده باشد.
    wp_send_json_error(null, 404);

$product_types = [
    'escaperoom'    => 'اتاق فرار',
    'cinema'        => 'سینما ترس',
    'lasertag'      => 'لیزرتگ',
    'rageroom'      => 'اتاق خشم',
    'cafegame'      => 'کافه بازی',
    'bubblefootball'=> 'فوتبال حبابی',
    'paintball'     => 'پینت بال',
    'haunted_house' => 'هانتد هاوس',
];

foreach ($city_categories as $city_category) // شناسایی تایپ کتگوری های متصل به این شهر
    $city_type_cats_id[array_search(get_parent_category_name_by_child_id($city_category), $product_types)] = (int)$city_category;
/*===============================================================*/
// کالکشن ها

$items_per_page = 10;

$collections = $wpdb->get_results(
	$wpdb->prepare(
		'SELECT * FROM collections WHERE active = %d ORDER BY likes_count DESC LIMIT %d',
		1,
		(int) $items_per_page
	)
);
foreach ($collections as $collection) {

    $images = [];
    foreach (unserialize($collection->items) as $product_id)
        $images[] = wp_get_attachment_url(get_post_thumbnail_id($product_id));

    $collection_items[] =  [
        'title'         => $collection->title,
        'user_title'    => 'فاطمه خداپرست',
        'user_level'    => 2,
        'likes_count'   => (int)$collection->likes_count,
        'url'           => "/profile/" . (int)$collection->user_id,
        'count'         => count(unserialize($collection->items)),
        'items'         => $images,
    ];
}

$data[] = [
    'type'  => 'collections',
    'title' => 'کالکشن های محبوب کاربران',
    'icon'  => '',
    'url'   => '/collections/',
    'data'  => [
        'items' => $collection_items,
    ]
];

/*===============================================================*/
// محبوب ترین برندها

$brands = get_terms([
    'taxonomy'      => 'product_brand',
    'hide_empty'    => false,
    'number'        => 500,
]);

shuffle($brands);
$brands = array_slice($brands, 0, 15);

foreach ($brands as $brand) {
    $brand_id = $brand->term_id;

    $brand_img_id = get_term_meta($brand_id, 'thumbnail_id', true);
    if ($brand_img_id > 0)
        $image = wp_get_attachment_image_src($brand_img_id, 'full')[0];

    $brand_items[] = [
        'id'    => $brand_id,
        'title' => $brand->name,
        'image' => $image,
        'url'   => trim_home_url(get_term_link($brand)),
        'count' => 5,
    ];
}

$data[] = [
    'type'  => 'owners',
    'title' => 'میزبان های اسکیپ زوم',
    'icon'  => '',
    'url'   => '/brands/',
    'data'  => [
        'slide_time'    => 5,
        'items'         => $brand_items,
    ]
];

/*===============================================================*/
// کامنت ها

$comments_per_page = 10;
$args = array(
    'post_type'   => 'product',
    'status'      => 'approve',
    'number'      => $comments_per_page,
    'orderby'     => 'comment_date',
    'order'       => 'DESC',
    'parent'      => 0,
);
$comments_query = new WP_Comment_Query;
$comments = $comments_query->query($args);

$comment_items = [];

if ($comments) {
    foreach ($comments as $comment) {
        $comment_id = $comment->comment_ID;

        $replies_args = array(
            'parent' => $comment_id,
            'status' => 'approve',
            'type'   => 'comment',
        );

        $author_title = $comment->comment_author;

        if (ctype_digit($comment->comment_author))
            $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);

        $comment_rating = get_comment_meta($comment_id, 'comment_rating', true);

        $comment_items[] = [
            'id'            => (int)$comment_id,
            'author'        => $author_title,
            'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
            'author_level'  => '',
            'product_title' => get_the_title($comment->comment_post_ID),
            'product_url'   => trim_home_url(get_permalink($comment->comment_post_ID)),
            'content'       => $comment->comment_content,
            'date'          => strtotime($comment->comment_date),
            'reply'         => isset(get_comments($replies_args)[0]) ? get_comments($replies_args)[0]->comment_content : null,
            'votes_count'   => ((int)get_comment_meta($comment_id, 'cld_like_count', true) - (int)get_comment_meta($comment_id, 'cld_dislike_count', true)),
            //'rating_items'  => $comment_rating ? array_map(fn($value) => $value / 20, get_comment_meta($comment_id, 'comment_rating', true)) : 0,
        ];
    }
}
$data[] = [
    'type'  => 'comments',
    'title' => '',
    'icon'  => '',
    'url'   => '',
    'data'  => [
        'slide_time'    => 5,
        'items'         => $comment_items
    ]
];
?>
<section class="space-y-5 lg:space-y-8">
    <?php
    $cityIcon = get_field('icon', $city_id);
    $category = $city_id;
    if (get_field('icon', $city_id) || get_field('short-description', $city_id)) { ?>
        <div class="bg-slate-100/50 w-full flex items-center justify-between py-2.5 px-4 lg:px-12.5 rounded-14 lg:rounded-20">
            <div class="lg:flex lg:items-center lg:gap-x-5">
                <h1 class="text-21 lg:text-27 text-textColor lg:pl-5 lg:border-l font-black"><?= $city_name ?></h1>
                <p class="text-11 lg:text-13 lg:leading-6 max-lg:hidden"><?= get_field('short-description', $city_id); ?></p>
            </div>
            <?php if (get_field('icon', $city_id)) { ?>
                <img src="<?= $cityIcon['url'] ?>" alt="" class="w-12 h-12 lg:w-16.5 lg:h-16.5">
            <?php } ?>
        </div>
    <?php } ?>
    <?php
    /*===============================================================*/
    //اسلایدر تبلیغاتی
    // Slider config: false = local test images, true = production ACF
    $use_production_slider = true;

    // تنظیم اسلایدها بر اساس حالت
    if ($use_production_slider) {
        $sliderItems = get_field('slider', $city_id);
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
        <div class="relative embla_fade w-full rounded-14 lg:rounded-20 overflow-hidden">
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
<?php
/*===============================================================*/
// اتاق فرار
if (isset($city_type_cats_id['escaperoom'])):
    $params = [
        'tag' => -1,
    ];
    $args = [
        'source'    => 'city_page_product_' . $city_type_cats_id['escaperoom'],
        'params'    => $params,
    ];
    $term_link = get_term_link($city_type_cats_id['escaperoom'], 'product_cat');
    $city_rooms = ez_products_snapshot_swiper($args);
    if (!is_null($city_rooms) and isset($city_rooms->products) and !empty($city_rooms->products) and (strlen($city_rooms->products) > 0)):
        $city_rooms = $city_rooms->products;
        /*===============================================================*/
?>
        <section class="max-w-full py-4 md:py-5 lg:py-9 md:mt-7.5">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="city-rooms" data-source="<?= $args['source'] ?>" data-params='{"schedule":-1}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.13221 22.3631L5.17199 20.6839L3.49962 21.6559C3.27661 21.7845 3.01166 21.8192 2.76306 21.7525C2.51446 21.6857 2.30256 21.5229 2.17399 21.2999C2.04542 21.0769 2.01071 20.8119 2.07748 20.5633C2.14426 20.3147 2.30706 20.1028 2.53007 19.9742L15.0126 12.7777C14.635 11.6414 14.6927 10.4054 15.1745 9.30922C15.6562 8.21302 16.5277 7.33467 17.6201 6.84436C18.7125 6.35405 19.948 6.2867 21.0872 6.65535C22.2264 7.024 23.1883 7.80241 23.7863 8.83974C24.3844 9.87708 24.5761 11.0995 24.3243 12.2701C24.0725 13.4407 23.3951 14.4762 22.4234 15.1759C21.4518 15.8756 20.255 16.1896 19.0649 16.0573C17.8749 15.9249 16.7763 15.3555 15.9821 14.4594L10.1934 17.7889L11.1655 19.4613C11.2286 19.5717 11.2692 19.6936 11.2851 19.8198C11.301 19.946 11.2919 20.0741 11.2582 20.1968C11.2259 20.3198 11.1696 20.4353 11.0926 20.5366C11.0156 20.6378 10.9194 20.7229 10.8094 20.7869C10.6991 20.8511 10.5772 20.8928 10.4507 20.9096C10.3242 20.9265 10.1956 20.9181 10.0724 20.885C9.94917 20.8519 9.83371 20.7947 9.73268 20.7168C9.63165 20.6388 9.54705 20.5416 9.48377 20.4308L8.57023 18.7642L6.88853 19.7337L7.86059 21.4061C7.92368 21.5165 7.96434 21.6384 7.98025 21.7646C7.99615 21.8908 7.98699 22.0189 7.95327 22.1416C7.92098 22.2647 7.86472 22.3801 7.78771 22.4814C7.7107 22.5826 7.61446 22.6677 7.50451 22.7317C7.39164 22.803 7.26523 22.8501 7.13324 22.8701C7.00125 22.8901 6.86655 22.8826 6.73763 22.8479C6.60871 22.8133 6.48835 22.7523 6.38415 22.6689C6.27996 22.5855 6.19417 22.4813 6.13221 22.3631ZM22.3957 12.0084C22.5444 11.4545 22.5256 10.8689 22.3416 10.3257C22.1576 9.78247 21.8166 9.30602 21.3618 8.95659C20.907 8.60715 20.3588 8.40043 19.7865 8.36257C19.2142 8.3247 18.6435 8.45739 18.1466 8.74386C17.6497 9.03033 17.249 9.4577 16.995 9.97195C16.741 10.4862 16.6452 11.0642 16.7197 11.6329C16.7942 12.2016 17.0357 12.7354 17.4136 13.1668C17.7915 13.5983 18.2888 13.908 18.8428 14.0568C19.5855 14.2563 20.3771 14.1526 21.0434 13.7684C21.7097 13.3843 22.1961 12.7512 22.3957 12.0084Z" fill="#0F172B" stroke="#0F172B" />
                            </svg>
                            <div class="text-17 font-bold">
                                <span class="inline-block">
                                    اتاق فرارهای
                                </span>
                                <span class="font-black inline-block">
                                    <?= str_replace("بازی های ", "", $city_name) ?>
                                </span>
                            </div>
                        </h2>
                    </div>
                    <div class="flex items-center gap-6">
                        <a href="<?= esc_url($term_link) ?>">
                            <div class="flex items-center gap-1.5 text-10 lg:gap-3.5 lg:text-12 hover:text-primary-500 transition">مشاهده همه <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                                    <path clip-rule="evenodd" d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z" fill="currentColor"></path>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                                    <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal">
                <div class="embla__viewport">
                    <div id="city-rooms-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5">
                        <?= $city_rooms ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev city-rooms-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next city-rooms-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
    <?php
    endif;
endif;
/*===============================================================*/
// سینما ترس
if (isset($city_type_cats_id['cinema'])):
    $params = [
        'tag' => -1,
    ];
    $args = [
        'source'    => 'city_page_product_' . $city_type_cats_id['cinema'],
        'params'    => $params,
    ];
    $term_link = get_term_link($city_type_cats_id['cinema'], 'product_cat');
    $city_cinema = ez_products_snapshot_swiper($args);
    if (!is_null($city_cinema) and isset($city_cinema->products) and !empty($city_cinema->products) and (strlen($city_cinema->products) > 0)):
        $city_cinema = $city_cinema->products;
        /*===============================================================*/
    ?>
        <section class="max-w-full py-4 md:py-5 lg:py-9 md:mt-7.5">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="city-rooms" data-source="<?= $args['source'] ?>" data-params='{"schedule":-1}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.9283 8.08007C16.9283 8.61588 16.8216 9.14643 16.6143 9.64145C16.407 10.1365 16.1031 10.5863 15.72 10.9651C15.337 11.344 14.8822 11.6445 14.3817 11.8496C13.8813 12.0546 13.3448 12.1601 12.8031 12.1601C12.2614 12.1601 11.725 12.0546 11.2245 11.8496C10.724 11.6445 10.2692 11.344 9.88617 10.9651C9.50312 10.5863 9.19926 10.1365 8.99195 9.64145C8.78464 9.14643 8.67794 8.61588 8.67794 8.08007C8.67794 6.99797 9.11255 5.96019 9.88617 5.19503C10.6598 4.42986 11.709 4 12.8031 4C13.8972 4 14.9464 4.42986 15.72 5.19503C16.4937 5.96019 16.9283 6.99797 16.9283 8.08007ZM11.6245 13.3259C10.843 13.3259 10.0935 13.6329 9.54096 14.1795C8.98838 14.726 8.67794 15.4673 8.67794 16.2402V22.0689C8.67794 22.4516 8.75415 22.8306 8.90223 23.1842C9.05031 23.5378 9.26735 23.859 9.54096 24.1297C9.81458 24.4003 10.1394 24.6149 10.4969 24.7614C10.8544 24.9079 11.2375 24.9832 11.6245 24.9832H21.0535C21.4404 24.9832 21.8236 24.9079 22.181 24.7614C22.5385 24.6149 22.8634 24.4003 23.137 24.1297C23.4106 23.859 23.6276 23.5378 23.7757 23.1842C23.9238 22.8306 24 22.4516 24 22.0689V16.2402C24 15.4673 23.6896 14.726 23.137 14.1795C22.5844 13.6329 21.8349 13.3259 21.0535 13.3259H11.6245ZM4 14.4206V24.1265C4.00015 24.2993 4.05208 24.4682 4.14922 24.6118C4.24636 24.7554 4.38436 24.8674 4.54579 24.9335C4.70722 24.9996 4.88483 25.0169 5.0562 24.9832C5.22757 24.9496 5.38501 24.8664 5.50863 24.7443L8.41865 21.8673C8.58447 21.7035 8.67774 21.4812 8.67794 21.2494V17.2754C8.67784 17.1601 8.65467 17.0459 8.60976 16.9395C8.56486 16.8331 8.4991 16.7365 8.41629 16.6553L5.50628 13.7992C5.38232 13.6777 5.22476 13.5952 5.05347 13.5621C4.88217 13.529 4.70479 13.5468 4.54371 13.6132C4.38262 13.6797 4.24502 13.7918 4.14827 13.9355C4.05151 14.0791 3.99992 14.2479 4 14.4206ZM21.0535 12.1601C21.8349 12.1601 22.5844 11.8531 23.137 11.3066C23.6896 10.76 24 10.0187 24 9.24581C24 8.47288 23.6896 7.73161 23.137 7.18506C22.5844 6.63852 21.8349 6.33147 21.0535 6.33147C20.272 6.33147 19.5225 6.63852 18.9699 7.18506C18.4173 7.73161 18.1069 8.47288 18.1069 9.24581C18.1069 10.0187 18.4173 10.76 18.9699 11.3066C19.5225 11.8531 20.272 12.1601 21.0535 12.1601Z" stroke="#09192D" stroke-width="2.5" />
                            </svg>
                            <div class="text-17 font-bold">
                                <span class="inline-block">
                                    سینما ترس های
                                </span>
                                <span class="font-black inline-block">
                                    <?= str_replace("بازی های ", "", $city_name) ?>
                                </span>
                            </div>
                        </h2>
                    </div>
                    <div class="flex items-center gap-6">
                        <a href="<?= esc_url($term_link) ?>">
                            <div class="flex items-center gap-1.5 text-10 lg:gap-3.5 lg:text-12 hover:text-primary-500 transition">مشاهده همه <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                                    <path clip-rule="evenodd" d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z" fill="currentColor"></path>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                                    <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal">
                <div class="embla__viewport">
                    <div id="city-rooms-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5">
                        <?= $city_cinema ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev city-rooms-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next city-rooms-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
    <?php
    endif;
endif;
/*===============================================================*/
//تخفیف های ویژه

$args = [
    'source' => 'city_page_discounts_event_' . implode(',', $city_type_cats_id),
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
/*===============================================================*/
// سایر سرگرمی ها

foreach ($city_type_cats_id as $cat_type => $city_type_cat_id) :
    if ($cat_type == 'escaperoom' or $cat_type == 'cinema') // اتاق فرار و سینماترس بالاتر ایجاده شده است.
        continue;

    $params = [
        'tag' => -1,
    ];
    $args = [
        'source'    => 'city_page_product_' . $city_type_cat_id,
        'params'    => $params,
    ];
    $term_link = get_term_link($city_type_cat_id, 'product_cat');
    $games = ez_products_snapshot_swiper($args);
    $pre_title_section = null;
    switch ($cat_type) {
        case 'cinema':
            $pre_title_section = 'سینما ترس‌های';
            break;
        case 'rageroom':
            $pre_title_section = 'اتاق خشم‌های';
            break;
        case 'lasertag':
            $pre_title_section = 'لیزرتگ‌های';
            break;
        case 'cafegame':
            $pre_title_section = 'کافه بازی های';
            break;
    }
    
    $title_section = $pre_title_section . ' <span class="font-black inline-block">' . str_replace("بازی های ", "", $city_name) . '</span>';
    if (is_null($games->products) or empty($games->products)) // اگه یک کتگوری هیچ محصولی نداشت به فرانت نفرست
        continue;
?>
    <section class="max-w-full py-4 md:py-5 lg:py-9 md:mt-7.5">
        <div class="mb-6 md:mb-8">
            <input type="hidden" id="city-<?= $cat_type ?>" data-source="<?= $args['source'] ?>" data-params='{"schedule":-1}'>
            <div class="flex justify-between">
                <div class="items-center gap-6 md:flex">
                    <h2 class="flex items-center gap-4">
                        <?php if ($cat_type == 'lasertag'): ?>
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="4.75" y="5.75" width="18.5" height="18.5" rx="9.25" stroke="#09192D" stroke-width="2.5" />
                                <path d="M14 22.9999V19.7999" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
                                <path d="M14 10.2V7" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
                                <path d="M18 14.68H22" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
                                <path d="M6.00065 14.68L9.33398 14.68" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
                            </svg>
                        <?php elseif ($cat_type == 'rageroom'): ?>
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.13221 22.3631L5.17199 20.6839L3.49962 21.6559C3.27661 21.7845 3.01166 21.8192 2.76306 21.7525C2.51446 21.6857 2.30256 21.5229 2.17399 21.2999C2.04542 21.0769 2.01071 20.8119 2.07748 20.5633C2.14426 20.3147 2.30706 20.1028 2.53007 19.9742L15.0126 12.7777C14.635 11.6414 14.6927 10.4054 15.1745 9.30922C15.6562 8.21302 16.5277 7.33467 17.6201 6.84436C18.7125 6.35405 19.948 6.2867 21.0872 6.65535C22.2264 7.024 23.1883 7.80241 23.7863 8.83974C24.3844 9.87708 24.5761 11.0995 24.3243 12.2701C24.0725 13.4407 23.3951 14.4762 22.4234 15.1759C21.4518 15.8756 20.255 16.1896 19.0649 16.0573C17.8749 15.9249 16.7763 15.3555 15.9821 14.4594L10.1934 17.7889L11.1655 19.4613C11.2286 19.5717 11.2692 19.6936 11.2851 19.8198C11.301 19.946 11.2919 20.0741 11.2582 20.1968C11.2259 20.3198 11.1696 20.4353 11.0926 20.5366C11.0156 20.6378 10.9194 20.7229 10.8094 20.7869C10.6991 20.8511 10.5772 20.8928 10.4507 20.9096C10.3242 20.9265 10.1956 20.9181 10.0724 20.885C9.94917 20.8519 9.83371 20.7947 9.73268 20.7168C9.63165 20.6388 9.54705 20.5416 9.48377 20.4308L8.57023 18.7642L6.88853 19.7337L7.86059 21.4061C7.92368 21.5165 7.96434 21.6384 7.98025 21.7646C7.99615 21.8908 7.98699 22.0189 7.95327 22.1416C7.92098 22.2647 7.86472 22.3801 7.78771 22.4814C7.7107 22.5826 7.61446 22.6677 7.50451 22.7317C7.39164 22.803 7.26523 22.8501 7.13324 22.8701C7.00125 22.8901 6.86655 22.8826 6.73763 22.8479C6.60871 22.8133 6.48835 22.7523 6.38415 22.6689C6.27996 22.5855 6.19417 22.4813 6.13221 22.3631ZM22.3957 12.0084C22.5444 11.4545 22.5256 10.8689 22.3416 10.3257C22.1576 9.78247 21.8166 9.30602 21.3618 8.95659C20.907 8.60715 20.3588 8.40043 19.7865 8.36257C19.2142 8.3247 18.6435 8.45739 18.1466 8.74386C17.6497 9.03033 17.249 9.4577 16.995 9.97195C16.741 10.4862 16.6452 11.0642 16.7197 11.6329C16.7942 12.2016 17.0357 12.7354 17.4136 13.1668C17.7915 13.5983 18.2888 13.908 18.8428 14.0568C19.5855 14.2563 20.3771 14.1526 21.0434 13.7684C21.7097 13.3843 22.1961 12.7512 22.3957 12.0084Z" fill="#0F172B" stroke="#0F172B" />
                            </svg>
                        <?php else: ?>
                            <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.13221 22.3631L5.17199 20.6839L3.49962 21.6559C3.27661 21.7845 3.01166 21.8192 2.76306 21.7525C2.51446 21.6857 2.30256 21.5229 2.17399 21.2999C2.04542 21.0769 2.01071 20.8119 2.07748 20.5633C2.14426 20.3147 2.30706 20.1028 2.53007 19.9742L15.0126 12.7777C14.635 11.6414 14.6927 10.4054 15.1745 9.30922C15.6562 8.21302 16.5277 7.33467 17.6201 6.84436C18.7125 6.35405 19.948 6.2867 21.0872 6.65535C22.2264 7.024 23.1883 7.80241 23.7863 8.83974C24.3844 9.87708 24.5761 11.0995 24.3243 12.2701C24.0725 13.4407 23.3951 14.4762 22.4234 15.1759C21.4518 15.8756 20.255 16.1896 19.0649 16.0573C17.8749 15.9249 16.7763 15.3555 15.9821 14.4594L10.1934 17.7889L11.1655 19.4613C11.2286 19.5717 11.2692 19.6936 11.2851 19.8198C11.301 19.946 11.2919 20.0741 11.2582 20.1968C11.2259 20.3198 11.1696 20.4353 11.0926 20.5366C11.0156 20.6378 10.9194 20.7229 10.8094 20.7869C10.6991 20.8511 10.5772 20.8928 10.4507 20.9096C10.3242 20.9265 10.1956 20.9181 10.0724 20.885C9.94917 20.8519 9.83371 20.7947 9.73268 20.7168C9.63165 20.6388 9.54705 20.5416 9.48377 20.4308L8.57023 18.7642L6.88853 19.7337L7.86059 21.4061C7.92368 21.5165 7.96434 21.6384 7.98025 21.7646C7.99615 21.8908 7.98699 22.0189 7.95327 22.1416C7.92098 22.2647 7.86472 22.3801 7.78771 22.4814C7.7107 22.5826 7.61446 22.6677 7.50451 22.7317C7.39164 22.803 7.26523 22.8501 7.13324 22.8701C7.00125 22.8901 6.86655 22.8826 6.73763 22.8479C6.60871 22.8133 6.48835 22.7523 6.38415 22.6689C6.27996 22.5855 6.19417 22.4813 6.13221 22.3631ZM22.3957 12.0084C22.5444 11.4545 22.5256 10.8689 22.3416 10.3257C22.1576 9.78247 21.8166 9.30602 21.3618 8.95659C20.907 8.60715 20.3588 8.40043 19.7865 8.36257C19.2142 8.3247 18.6435 8.45739 18.1466 8.74386C17.6497 9.03033 17.249 9.4577 16.995 9.97195C16.741 10.4862 16.6452 11.0642 16.7197 11.6329C16.7942 12.2016 17.0357 12.7354 17.4136 13.1668C17.7915 13.5983 18.2888 13.908 18.8428 14.0568C19.5855 14.2563 20.3771 14.1526 21.0434 13.7684C21.7097 13.3843 22.1961 12.7512 22.3957 12.0084Z" fill="#0F172B" stroke="#0F172B" />
                            </svg>
                        <?php endif; ?>
                        <div class="text-17 font-bold">
                            <span class="inline-block">
                                <?= $title_section ?>
                            </span>
                        </div>
                    </h2>
                </div>
                <div class="flex items-center gap-6">
                    <a href="<?= esc_url($term_link) ?>">
                        <div class="flex items-center gap-1.5 text-10 lg:gap-3.5 lg:text-12 hover:text-primary-500 transition">مشاهده همه <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                                <path clip-rule="evenodd" d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z" fill="currentColor"></path>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                                <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="relative overflow-hidden embla_normal">
            <div class="embla__viewport">
                <div id="city-<?= $cat_type ?>-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5 child:relative">
                    <?= $games->products ?>
                </div>
            </div>
            <button class="embla__button embla__button--prev city-<?= $cat_type ?>-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
            <button class="embla__button embla__button--next city-<?= $cat_type ?>-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
<?php
endforeach;
// کالکشن های محبوب
// get_template_part('template/layout/collections');
?>

<script>
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
</script>