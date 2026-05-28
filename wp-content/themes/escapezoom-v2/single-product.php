<?php

global $wpdb;
$user_id     = get_current_user_id();
$product_id  = get_the_ID();
$product_obj = get_post($product_id);
$brand_data  = get_the_terms($product_id, 'yith_product_brand')[0];

$brand_id = $brand_data->term_id ?: 0;

$params         = [
    'brand_id'         => $brand_id,
    'exclude_products' => [$product_id],
];

$args           = [
    'params'        => $params,
    'image_type'    => 'url',
    'limit'         => 10,
    'page'          => 1,
    'max_num_pages' => false,
    "format"        => 'html_swiper',
    'sort_type'     => 'popular',
    'unpin_ads'     => false,
    'badge_ads'     => false,
    'random'        => true,
    'random_memory' => '',
    'show_more'     => 0,
];
$brand_products = json_decode(ez_webservice(['type' => 'sort_products_get', 'data' => $args]));
$product_type = null;
$terms = get_the_terms($product_id, 'product_cat');
if ($terms && !is_wp_error($terms) && count($terms) > 1) {
    foreach ($terms as $term) {
        if ($term->parent == 0) {
            $product_type           = $term->name;
            $product_parent_cat_url = get_term_link($term->term_id, "product_cat");
        } else {
            $city_name       = $term->name;
            $city_id         = $term->term_id;
            $product_cat_url = get_term_link_flat($term, 'city');
        }
    }
} elseif ($terms && !is_wp_error($terms) && count($terms) == 1) {
    $product_type           = get_term($terms[0]->parent)->name;
    $city_name              = $terms[0]->name;
    $city_id                = $terms[0]->term_id;
    $product_parent_cat_url = get_term_link($terms[0]->parent, "product_cat");
    $product_cat_url        = get_term_link_flat($terms[0], 'city');
} else {
    $product_type = 'نامشخص';
    $city_name = 'نامشخص';
    $city_id = 0;
    $product_parent_cat_url = '#';
    $product_cat_url = '#';
}
// ذخیره نام اصلی شهر قبل از حذف prefix (برای breadcrumb)
$city_name_full = $city_name;

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
    'پینتبال',
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
// Make variables global for footer tracking
global $ez_product_type, $ez_city_name;
$ez_product_type = $product_type;
$ez_city_name = $city_name;
foreach (get_the_terms($product_id, 'product_tag') as $product_tag) {
    if (str_contains($product_tag->name, '|||||')) {
        $genres[] = [
            'title' => str_replace('|||||', '', $product_tag->name),
            'id'    => $product_tag->term_id,
            'url'   => get_term_link($product_tag->term_id),
        ];
    } else {
        $tags[] = [
            'title' => $product_tag->name,
            'id'    => $product_tag->term_id,
            'url'   => get_term_link($product_tag->term_id),
        ];
    }
}
$product                    = wc_get_product($product_id);
$gallery                    = $product->get_gallery_image_ids();
$product_rates              = get_post_meta($product_id, 'clone_product_rates', true);
$comments_count             = get_post_meta($product_id, 'clone_comments_count_new', true);
$comments_count_meta        = get_comments([
    'post_id' => $product_id,
    'status'  => 'approve',
    'parent'  => 0,
]);
$comments_count_meta_number = count($comments_count_meta);
$decor                      = (int) $comments_count !== 0 ? $product_rates[1094] / $comments_count / 20 : 0;
$moaama                     = (int) $comments_count !== 0 ? $product_rates[1095] / $comments_count / 20 : 0;
$tazegi                     = (int) $comments_count !== 0 ? $product_rates[1098] / $comments_count / 20 : 0;
$act                        = (int) $comments_count !== 0 ? $product_rates[1096] / $comments_count / 20 : 0;
$barkhord                   = (int) $comments_count !== 0 ? $product_rates[1097] / $comments_count / 20 : 0;
$comments_per_page          = (int) EZ_SINGLE_PRODUCT_COMMENTS_PER_PAGE;
$total_pages                = ($comments_count_meta_number > 0) ? ceil($comments_count_meta_number / $comments_per_page) : 1;
$args                       = [
    'post_type' => 'product',
    'post_id'   => $product_id,
    'status'    => 'approve',
    'number'    => $comments_per_page,
    'parent'    => 0,
];
$comments_query             = new WP_Comment_Query;
$comments                   = $comments_query->query($args);
if ($comments) {
    foreach ($comments as $comment) {
        $comment_id   = $comment->comment_ID;
        $replies_args = [
            'parent' => $comment_id,
            'status' => 'approve',
            'type'   => 'comment',
        ];

        $author_title = '';
        $user         = get_user_by('id', $comment->user_id);

        if ($user->user_firstname) {
            $author_title = $user->user_firstname;
            if ($user->user_lastname) {
                $author_title .= ' ' . $user->user_lastname;
            }
        } elseif (ctype_digit($comment->comment_author)) {
            $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);
        }

        foreach (get_comment_meta($comment_id, 'comment_rating', true) as $key => $comment_rating) {
            $comment_ratings[$key] = intval($comment_rating) / 20;
        }

        $comment_items[] = [
            'id'               => (int) $comment_id,
            'author_id'        => $comment->user_id,
            'author_title'     => $author_title,
            'author_image'     => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
            'author_level'     => 1,
            'stored_user_level' => (int) get_comment_meta( $comment_id, 'user_level', true ),
            'content'          => $comment->comment_content,
            'date'         => strtotime($comment->comment_date),
            'reply'        => (get_comments($replies_args)[0])->comment_content,
            'votes_count'  => ((int) get_comment_meta($comment_id, 'cld_like_count', true) - (int) get_comment_meta($comment_id, 'cld_dislike_count', true)),
            'rating_items' => $comment_ratings,
            'user_feeling' => get_comment_meta($comment_id, "rating", true),
        ];
    }
}
$number_min   = ! empty($numbers[0]) ? (int) min($numbers[0]) : 0;
$number_max   = ! empty($numbers[0]) ? (int) max($numbers[0]) : 0;
$options      = get_post_meta($product_id, 'product_options', true);
$tickets_sold = get_post_meta($product_id, 'tickets_sold', true);

if ($product_type == 'اتاق فرار') {
    $properties = [
        [
            'id'    => 'genre',
            'value' => $genres,
        ],
        [
            'id'    => 'capacity',
            'value' => $number_min . ' تا ' . $number_max . ' کاربر ',
        ],
        [
            'id'    => 'duration',
            'value' => (int) get_post_meta($product_id, "room_duration", true),
        ],
        [
            'id'    => 'age',
            'value' => (int) get_post_meta($product_id, "room_age_limit", true),
        ],
        [
            'id'    => 'tickets_sold',
            'value' => $tickets_sold,
        ],
        [
            'id'    => 'level',
            'value' => (int) get_post_meta($product_id, "room_level", true),
        ],
    ];
} elseif ($product_type == 'سینما ترس') {
    $properties = [
        [
            'id'    => 'display_type',
            'value' => get_post_meta($product_id, "display_type", true),
        ],
        [
            'id'    => 'capacity',
            'value' => $number_min . ' تا ' . $number_max . ' کاربر ',
        ],
        [
            'id'    => 'duration',
            'value' => (int) get_post_meta($product_id, "room_duration", true),
        ],
        [
            'id'    => 'chair_type',
            'value' => get_post_meta($product_id, "chair_type", true),
        ],
        [
            'id'    => 'age',
            'value' => (int) get_post_meta($product_id, "room_age_limit", true),
        ],
        [
            'id'    => 'tickets_sold',
            'value' => $tickets_sold,
        ],
    ];
} elseif ($product_type == 'لیزرتگ') {
    $properties = [
        [
            'id'    => 'capacity',
            'value' => $number_min . ' تا ' . $number_max . 'کاربر',
        ],
        [
            'id'    => 'duration',
            'value' => (int) get_post_meta($product_id, "room_duration", true),
        ],
        [
            'id'    => 'age',
            'value' => (int) get_post_meta($product_id, "room_age_limit", true),
        ],
        [
            'id'    => 'tickets_sold',
            'value' => $tickets_sold,
        ],
    ];
} elseif ($product_type == 'اتاق خشم') {
    $properties = [
        [
            'id'    => 'capacity',
            'value' => $number_min . ' تا ' . $number_max . 'کاربر',
        ],
        [
            'id'    => 'duration',
            'value' => (int) get_post_meta($product_id, "room_duration", true),
        ],
        [
            'id'    => 'age',
            'value' => (int) get_post_meta($product_id, "room_age_limit", true),
        ],
        [
            'id'    => 'tickets_sold',
            'value' => $tickets_sold,
        ],
        [
            'id'    => 'safety',
            'value' => (int) get_post_meta($product_id, "safety", true),
        ],
    ];
}
preg_match_all('/\d+/', get_field("room_tedad", $product_id), $numbers);


$raw_rate = ($decor + $moaama + $tazegi + $act + $barkhord) / 5;

if ($product_type != 'اتاق فرار')
    $raw_rate = $raw_rate * 5; // امتیاز غیر اتاق فرارهارو در 5 ضرب کن تا استاندارد بشه

// مدیریت امتیازهایی مثل 4.995 که نباید 5 بشوند اما همچنان رند بودن حفظ بشه
if ($raw_rate == 5)
    $rate_final = 5;
elseif (round($raw_rate, 2) == 5) {
    $factor     = pow(10, 2);
    $rate_final = floor($raw_rate * $factor) / $factor;
} else
    $rate_final = number_format(round($raw_rate, 2), 2, '.', '');

$sale_status    = get_post_meta($product_id, 'product_state', true);
$is_active      = ($sale_status == 'active' or $sale_status == 'updated') ? 1 : 0;

$data = [
    'product_id'         => $product_id,
    'type'               => get_product_type_equivalent($product_type),
    'title'              => $product->get_title(),
    'price'              => ! empty(get_post_meta($product_id, 'min_price', true)) ? (int) get_post_meta($product_id, 'min_price', true) : (int) get_field("price_asli", $product_id),
    'ads'                => get_field("special_room", $product_id) ? true : false,
    'image'              => wp_get_attachment_url(get_post_thumbnail_id($product_id)),
    'age'                => (int) get_post_meta($product_id, "room_age_limit", true),
    'tickets_sold'       => $tickets_sold,
    'level'              => (int) get_field("room_level", $product_id),
    'duration'           => (int) get_post_meta($product_id, "room_duration", true),
    'active'             => $is_active,
    'city_id'            => $city_id,
    'city_name'          => $city_name,
    'hood_name'          => get_field("room_loc", $product_id),
    'nearest_subway'     => 'میدان کتاب',
    'nearest_brt'        => 'شهید دادمان',
    'genres'             => $genres,
    'tags'               => $tags,
    'number_min'         => ! empty($numbers[0]) ? (int) min($numbers[0]) : 0,
    'number_max'         => ! empty($numbers[0]) ? (int) max($numbers[0]) : 0,
    'count_down'         => null,
    'brand'              => [
        'title' => $brand_data->name,
        'image' => wp_get_attachment_url(get_term_meta($brand_id, 'thumbnail_id', true)),
        'url'   => get_term_link($brand_id),
    ],
    'properties'         => $properties,
    'options'            => $options,
    'introduction_text'  => get_post_meta($product_id, 'product_introduction_text', true),
    'scenario'           => get_post_meta($product_id, 'product_scenario', true),
    'rules'              => get_post_meta($product_id, 'product_rules', true),
    'trailer_video'      => get_field('room_video_embed', $product_id),
    'introduction_video' => get_post_meta($product_id, 'product_introduction_video', true),
    'criticism'          => $product_obj->post_excerpt,
    'address_info'       => [
        'address' => get_field('room_address', $product_id),
        'lat'     => get_field('room_lat', $product_id),
        'long'    => get_field('room_long', $product_id),
    ],
    'gallery'            => $gallery,
    'comments'           => [
        'rate'           => $rate_final,
        'comments_count' => $comments_count_meta_number,
        'rating_items'   => [
            1 => number_format($decor, 2, '.', ''),
            2 => number_format($moaama, 2, '.', ''),
            3 => number_format($tazegi, 2, '.', ''),
            4 => number_format($act, 2, '.', ''),
            5 => number_format($barkhord, 2, '.', ''),
        ],
        'items'          => $comment_items,
        'total_pages'    => $total_pages,
    ],
    'breadcrumb'         => [
        [
            'title' => 'صفحه اصلی',
            'url'   => '/',
        ],
        [
            'title' => $product_type,
            'url'   => $product_parent_cat_url,
        ],
        [
            'title' => $city_name_full,
            'url'   => $product_cat_url,
        ],
        [
            'title' => $product->get_title(),
            'url'   => '',
        ],
    ],
    'brand_products'     => $brand_products,
];
// discount Check
$discount_check = get_field('special_discount_enable', $product_id);
$discount_percentage = null;
if ($discount_check) {
    $discount_date = get_field('special_discount_date', $product_id);
    $current_date = current_time('timestamp');
    if ($discount_date > $current_date) {
        $discount_percentage = get_field('special_discount_percentage', $product_id);
    }
}
$sale_status_title = null;
$sale_status_color = null;

if ($sale_status == 'active') {
    $sale_status_title = 'فعال';
    $sale_status_color = '#02C96F';
} elseif ($sale_status == 'deactivated') {
    $sale_status_title = 'رزرو غیرفعال';
    $sale_status_color = '#BF9A00';
} elseif ($sale_status == 'expired') {
    $sale_status_title = 'اکسپایر شده';
    $sale_status_color = '#5091FB';
} elseif ($sale_status == 'temp') {
    $sale_status_title = 'غیرفعال موقت';
    $sale_status_color = '#BF9A00';
} elseif ($sale_status == 'soon') {
    $sale_status_title = 'به زودی';
    $sale_status_color = '#F21543';
} elseif ($sale_status == 'updated') {
    $sale_status_title = 'آپدیت شد';
    $sale_status_color = '#F21543';
}

$product_publish_date = strtotime($product->get_date_created());
$one_month_ago = strtotime('-1 month');
$new_product_tag = '';

if ($product_publish_date > $one_month_ago) {
    $new_product_tag = '<span class="bg-[#F21543] text-white inline-flex px-1.5 py-1 items-center rounded-lg text-sm font-black leading-[18px]">جدید</span>';
}

$increments        = [5, 10, 20, 50, 100, 300, 500, 1000, 1500, 2000, 2500, 3000, 5000, 7500, 10000, 12500, 15000, 17500, 20000];
$nearest_increment = 0;
foreach ($increments as $increment) {
    if ($data['tickets_sold'] >= $increment) {
        $nearest_increment = $increment;
    }
}
$tickets_sold = $nearest_increment . '+';

wp_enqueue_script('persian-date');
wp_enqueue_style('lightbox-css');
wp_enqueue_script('lightbox-js');

$yearly_ranks = get_post_meta($product_id, '_ez_product_yearly_ranks', true);
$satisfaction_positive_count_display = function_exists( 'ez_product_satisfaction_percent_from_markting' )
    ? ez_product_satisfaction_percent_from_markting( (int) $product_id )
    : null;

// بررسی وجود محتوا برای هر بخش
$has_introduction = !empty($data['introduction_text']);
$has_scenario = !empty($data['scenario']);
$has_description = !empty($data['rules']);

// بررسی وجود ویدیو
$videos = [];
if (!empty($data['trailer_video'])) {
    $videos[] = [
        'title' => 'تیزر بازی',
        'embed' => $data['trailer_video']
    ];
}
if (!empty($data['introduction_video'])) {
    $videos[] = [
        'title' => 'ویدیو معرفی',
        'embed' => $data['introduction_video']
    ];
}
$has_video = !empty($videos);
?>
<?php get_header(); ?>
<!---------------------------end-box-information----------------------------------------------------------------------------->

<div id="stiky-title-mobile" class="lg:hidden sticky top-16 z-30 bg-white py-4 transition-all duration-500 ease-out" style="display: none;">
    <div class="embla-tabs overflow-hidden">
        <div class="embla__container-tabs flex gap-5">
            <?php if ($has_introduction): ?>
            <button data-section="game-introduction-section" class="tab-btn w-[85px] h-[34px] text-sm font-bold rounded-lg bg-[#F8FAFC] text-center flex items-center justify-center text-[#0F172B] transition-colors flex-shrink-0">مشخصات</button>
            <?php endif; ?>
            <button data-section="address-section" class="tab-btn w-[85px] h-[34px] text-sm font-bold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors flex-shrink-0">آدرس</button>
            <?php if ($has_description): ?>
            <button data-section="description-section" class="tab-btn w-[85px] h-[34px] text-sm font-bold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors flex-shrink-0">توضیحات</button>
            <?php endif; ?>
            <?php if ($has_video): ?>
            <button data-section="video-section" class="tab-btn w-[85px] h-[34px] text-sm font-bold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors flex-shrink-0">ویدئو</button>
            <?php endif; ?>
            <button data-section="comments-section" class="tab-btn w-[85px] h-[34px] text-sm font-bold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors flex-shrink-0">نظرات</button>
        </div>
    </div>
</div>

<!--------------------------------start-Specifications----------------------------------------------------------------------->
<!-- -------------------start-boxs-mobile-reservation----------------------- -->
<?php if ($data['active']) { ?>
    <div id="mobile-box" class="flex items-center justify-between gap-10 py-4 px-4 bg-white rounded-xl fixed bottom-0 left-1/2 right-1/2 w-screen -ml-[50vw] -mr-[50vw] lg:hidden z-20 shadow-lg translate-y-full transition-transform duration-500 ease-out">
        <div class="flex flex-col">
            <p class="text-lg font-extrabold">از <?php echo number_format($data['price']); ?> تومان</p>
        </div>
        <button class="open-sessions flex items-center text-base font-black bg-[#02C96F] text-white py-3 px-7 rounded-xl gap-2">
            مشاهده سانس ها
            <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g filter="url(#filter0_d_53025_17992)">
                    <path d="M8.06593 13.7447C8.25344 13.9319 8.5076 14.0371 8.7726 14.0371C9.0376 14.0371 9.29177 13.9319 9.47927 13.7447L13.2519 9.97399C13.4395 9.78639 13.5449 9.53196 13.5449 9.26665C13.5449 9.00135 13.4395 8.74692 13.2519 8.55932C13.0643 8.37172 12.8099 8.26634 12.5446 8.26634C12.2793 8.26634 12.0249 8.37172 11.8373 8.55932L9.7726 10.6233L9.7726 3.99999C9.7726 3.73477 9.66724 3.48042 9.47971 3.29288C9.29217 3.10535 9.03782 2.99999 8.7726 2.99999C8.50738 2.99999 8.25303 3.10535 8.06549 3.29288C7.87796 3.48042 7.7726 3.73477 7.7726 3.99999L7.7726 10.6233L5.7086 8.55932C5.61571 8.46643 5.50544 8.39275 5.38407 8.34248C5.26271 8.29221 5.13263 8.26634 5.00127 8.26634C4.8699 8.26634 4.73983 8.29221 4.61846 8.34248C4.4971 8.39275 4.38682 8.46643 4.29393 8.55932C4.20105 8.65221 4.12736 8.76248 4.07709 8.88385C4.02682 9.00521 4.00095 9.13529 4.00095 9.26666C4.00095 9.39802 4.02682 9.5281 4.07709 9.64946C4.12736 9.77083 4.20105 9.8811 4.29393 9.97399L8.06593 13.7447Z" fill="white" />
                </g>
                <defs>
                    <filter id="filter0_d_53025_17992" x="0" y="0" width="17.5449" height="19.0371" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                        <feOffset dy="1" />
                        <feGaussianBlur stdDeviation="2" />
                        <feComposite in2="hardAlpha" operator="out" />
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_53025_17992" />
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_53025_17992" result="shape" />
                    </filter>
                </defs>
            </svg>
        </button>
    </div>

    <div id="Mobile-box-selected" class="hidden items-center justify-between gap-10 p-4 bg-white rounded-xl fixed bottom-0 left-1/2 right-1/2 w-screen -ml-[50vw] -mr-[50vw] lg:hidden z-20">
        <div class="flex flex-col">
            <p class="text-xs font-bold text-[#889BAD]">انتخاب شما</p>
            <p id="mobile-selected-info" class="text-xs font-extrabold space-x-1"><!-- داینامیک --></p>
        </div>
        <div class="flex gap-2">
            <a href="#" id="mobile-confirm-payment" class="flex items-center text-sm font-extrabold bg-[#02C96F] text-white py-3 px-4 rounded-xl">
                پرداخت و ثبت رزرو
            </a>
            <button class="open-sessions flex items-center text-base font-black bg-[#F1F5F9] text-white p-4 rounded-xl">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.33333 3.16667H9.5C9.5 2.74674 9.33318 2.34401 9.03625 2.04708C8.73932 1.75015 8.33659 1.58333 7.91667 1.58333C7.49674 1.58333 7.09401 1.75015 6.79708 2.04708C6.50015 2.34401 6.33333 2.74674 6.33333 3.16667ZM4.75 3.16667C4.75 2.32681 5.08363 1.52136 5.67749 0.927495C6.27136 0.33363 7.07681 0 7.91667 0C8.75652 0 9.56197 0.33363 10.1558 0.927495C10.7497 1.52136 11.0833 2.32681 11.0833 3.16667H15.0417C15.2516 3.16667 15.453 3.25007 15.6015 3.39854C15.7499 3.54701 15.8333 3.74837 15.8333 3.95833C15.8333 4.1683 15.7499 4.36966 15.6015 4.51813C15.453 4.66659 15.2516 4.75 15.0417 4.75H14.3434L13.642 12.9358C13.5746 13.7263 13.2129 14.4626 12.6285 14.9992C12.0442 15.5357 11.2797 15.8334 10.4864 15.8333H5.34692C4.55359 15.8334 3.78913 15.5357 3.20478 14.9992C2.62043 14.4626 2.25877 13.7263 2.19133 12.9358L1.48992 4.75H0.791667C0.581704 4.75 0.38034 4.66659 0.231874 4.51813C0.0834075 4.36966 0 4.1683 0 3.95833C0 3.74837 0.0834075 3.54701 0.231874 3.39854C0.38034 3.25007 0.581704 3.16667 0.791667 3.16667H4.75ZM10.2917 7.91667C10.2917 7.7067 10.2083 7.50534 10.0598 7.35687C9.91133 7.20841 9.70996 7.125 9.5 7.125C9.29004 7.125 9.08867 7.20841 8.94021 7.35687C8.79174 7.50534 8.70833 7.7067 8.70833 7.91667V11.0833C8.70833 11.2933 8.79174 11.4947 8.94021 11.6431C9.08867 11.7916 9.29004 11.875 9.5 11.875C9.70996 11.875 9.91133 11.7916 10.0598 11.6431C10.2083 11.4947 10.2917 11.2933 10.2917 11.0833V7.91667ZM6.33333 7.125C6.5433 7.125 6.74466 7.20841 6.89313 7.35687C7.04159 7.50534 7.125 7.7067 7.125 7.91667V11.0833C7.125 11.2933 7.04159 11.4947 6.89313 11.6431C6.74466 11.7916 6.5433 11.875 6.33333 11.875C6.12337 11.875 5.92201 11.7916 5.77354 11.6431C5.62507 11.4947 5.54167 11.2933 5.54167 11.0833V7.91667C5.54167 7.7067 5.62507 7.50534 5.77354 7.35687C5.92201 7.20841 6.12337 7.125 6.33333 7.125ZM3.76833 12.8012C3.80206 13.1966 3.98301 13.5649 4.27535 13.8332C4.56769 14.1015 4.95012 14.2502 5.34692 14.25H10.4864C10.8829 14.2498 11.265 14.1009 11.557 13.8327C11.849 13.5644 12.0297 13.1963 12.0634 12.8012L12.7537 4.75H3.07958L3.76833 12.8012Z" fill="#F21543" />
                </svg>
            </button>
        </div>
    </div>

    <div id="overlay" class="fixed inset-0 bg-black/50 hidden z-30"></div>

    <div id="sessions-panel" class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-[0_-4px_10px_rgba(0,0,0,0.1)] translate-y-full transition-transform duration-300 ease-in-out z-40 lg:hidden px-7 pt-9" style="display: none;">
        <div class="relative">
            <button id="close-sessions" class="bg-white/30 w-11 h-11 flex items-center justify-center text-gray-600 hover:text-gray-800 absolute top-[-100px] right-[-5px] p-2 rounded-xl">
                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 21.9474L21.8526 3M21.9474 21.9474L3.09474 3" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <div class="flex items-center justify-between gap-2 date-scroll-container-mobile">
                <div id="today-btn-mobile" class="bg-[#5091FB] w-[60px] h-[60px] rounded-lg text-white flex justify-center items-center flex-shrink-0 cursor-pointer text-sm font-bold">
                    امروز
                </div>
                <div class="embla-dates-mobile overflow-hidden flex-1">
                    <div class="embla__container date-scroll-list-mobile">

                    </div>
                </div>

                <a href="<?php echo site_url('/r/' . $data['product_id']); ?>" id="show-all-dates-mobile" class="w-[85px] h-[61px] rounded-lg bg-[#F1F5F9] py-2 flex-shrink-0 cursor-pointer flex flex-col justify-center items-center" target="_blank">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.3809 4.74667V3C22.3809 2.73478 22.2756 2.48043 22.0881 2.29289C21.9005 2.10536 21.6462 2 21.3809 2C21.1157 2 20.8614 2.10536 20.6738 2.29289C20.4863 2.48043 20.3809 2.73478 20.3809 3V4.66667H11.3333V3C11.3333 2.73478 11.228 2.48043 11.0404 2.29289C10.8529 2.10536 10.5985 2 10.3333 2C10.0681 2 9.81376 2.10536 9.62623 2.29289C9.43869 2.48043 9.33333 2.73478 9.33333 3V4.74667C7.8471 4.98704 6.49472 5.74794 5.51775 6.89344C4.54077 8.03895 4.00283 9.49446 4 11V22.6667C4 23.4984 4.16382 24.3219 4.4821 25.0903C4.80038 25.8587 5.26689 26.5569 5.85499 27.145C7.04272 28.3327 8.65363 29 10.3333 29H21.3809C22.2127 29 23.0362 28.8362 23.8046 28.5179C24.573 28.1996 25.2712 27.7331 25.8593 27.145C26.4474 26.5569 26.9139 25.8587 27.2322 25.0903C27.5505 24.3219 27.7143 23.4984 27.7143 22.6667V11C27.7115 9.49446 27.1735 8.03895 26.1965 6.89344C25.2196 5.74794 23.8672 4.98704 22.3809 4.74667ZM25.7143 12.6667H6V11C6.00131 10.0268 6.33119 9.08255 6.93619 8.32026C7.54119 7.55796 8.38586 7.02227 9.33333 6.8V8.33333C9.33333 8.59855 9.43869 8.8529 9.62623 9.04044C9.81376 9.22798 10.0681 9.33333 10.3333 9.33333C10.5985 9.33333 10.8529 9.22798 11.0404 9.04044C11.228 8.8529 11.3333 8.59855 11.3333 8.33333V6.66667H20.3809V8.33333C20.3809 8.59855 20.4863 8.8529 20.6738 9.04044C20.8614 9.22798 21.1157 9.33333 21.3809 9.33333C21.6462 9.33333 21.9005 9.22798 22.0881 9.04044C22.2756 8.8529 22.3809 8.59855 22.3809 8.33333V6.8C23.3284 7.02227 24.1731 7.55796 24.7781 8.32026C25.3831 9.08255 25.713 10.0268 25.7143 11V12.6667Z" fill="#5091FB" />
                        <circle cx="11.1409" cy="18.1429" r="1.14286" fill="white" />
                        <circle cx="11.1409" cy="22.7143" r="1.14286" fill="white" />
                        <circle cx="15.7132" cy="18.1429" r="1.14286" fill="white" />
                        <circle cx="15.7132" cy="22.7143" r="1.14286" fill="white" />
                        <circle cx="20.2835" cy="18.1429" r="1.14286" fill="white" />
                        <circle cx="20.2835" cy="22.7143" r="1.14286" fill="white" />
                    </svg>
                    <p class="text-xs font-bold text-center">مشاهده همه</p>
                </a>
            </div>

            <!-- Session Info -->
            <div id="sessions-info-mobile" class="flex items-center mb-4 mt-3">
                <h2 class="text-xs text-blue">در حال بارگذاری...</h2>
            </div>

            <!-- Sessions List با Embla Carousel -->
            <div class="relative sessions-embla-container-mobile">
                <!-- افکت fade بالا -->
                <div class="absolute top-0 left-0 right-0 h-8 bg-gradient-to-b from-white to-transparent pointer-events-none z-10"></div>

                <!-- Embla Carousel -->
                <div class="embla-sessions-mobile max-h-[300px] pb-8">
                    <div id="sessions-list-mobile" class="embla__container-sessions time-boxes" data-min="<?php echo $data['number_min']; ?>" data-max="<?php echo $data['number_max']; ?>" data-current-page="1" data-total-pages="1">
                        <!-- سانس‌ها به صورت داینامیک لود میشن -->
                    </div>
                </div>

                <!-- افکت fade پایین -->
                <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white to-transparent pointer-events-none z-10"></div>
            </div>

            <!-- Quantity Box (نمایش داده میشه وقتی روی سانس کلیک شد) -->
            <div id="quantity-box-mobile" class="quantity-box hidden pb-5">
                <!-- محتوا به صورت داینامیک لود میشه -->
            </div>

            <!-- Review Box -->
            <div id="review-box-mobile" class="hidden justify-between items-center w-full border rounded-xl p-5 mt-7">
                <!-- محتوا به صورت داینامیک لود میشه -->
            </div>

            <!-- Payment Button -->
            <a href="#" id="go-to-checkout-mobile" class="hidden items-center justify-center text-white bg-[#02C96F] w-full gap-4 rounded-xl py-4 px-5 mt-7 text-lg font-black shadow-[0_2px_0_#01B061] transition hover:bg-[#01B061]">
                پرداخت و ثبت رزرو
                <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                    <path d="M0.402124 5.33532C0.144631 5.58141 7.88412e-08 5.91501 7.46935e-08 6.26282C7.05459e-08 6.61063 0.144631 6.94422 0.402124 7.19032L5.58679 12.1419C5.84474 12.3882 6.19458 12.5265 6.55937 12.5265C6.92416 12.5265 7.27401 12.3882 7.53196 12.1419C7.7899 11.8957 7.93481 11.5618 7.93481 11.2136C7.93481 10.8654 7.7899 10.5314 7.53196 10.2852L4.69396 7.57532L13.801 7.57532C14.1657 7.57532 14.5154 7.43704 14.7733 7.1909C15.0312 6.94476 15.176 6.61092 15.176 6.26282C15.176 5.91472 15.0312 5.58088 14.7733 5.33474C14.5154 5.0886 14.1657 4.95032 13.801 4.95032L4.69396 4.95032L7.53196 2.24132C7.65968 2.1194 7.76099 1.97467 7.83011 1.81538C7.89924 1.65609 7.93481 1.48536 7.93481 1.31294C7.93481 1.14053 7.89924 0.969802 7.83011 0.810511C7.76099 0.65122 7.65968 0.506484 7.53196 0.384568C7.40424 0.262652 7.25261 0.165945 7.08573 0.0999652C6.91886 0.0339843 6.74 2.39222e-05 6.55937 2.39201e-05C6.37875 2.39179e-05 6.19989 0.0339843 6.03302 0.0999652C5.86614 0.165945 5.71451 0.262652 5.58679 0.384568L0.402124 5.33532Z" fill="white" />
                </svg>
            </a>




        </div>
    </div>
    <!-- -------------------start-boxs-mobile-reservation----------------------- -->

    <style>
        .session-scroll-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.6rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #E2E8F0;
            font-size: 0.875rem;
            font-weight: 700;
            color: #0F172B;
            background-color: #fff;
            transition: border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .session-scroll-btn:hover {
            border-color: #94A3B8;
            color: #2563EB;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
        }

        .session-scroll-btn svg {
            width: 14px;
            height: 14px;
        }

        .session-scroll-btn.session-scroll-btn--hidden {
            display: none !important;
        }

        .line-through-orange {
            text-decoration: line-through;
            text-decoration-color: #FF7A00;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* انیمیشن برای toggle کامنت‌ها */
        .comment-text-display {
            transition: opacity 0.3s ease-in-out;
        }

        .toggle-comment-btn .chevron-icon {
            transition: transform 0.3s ease-in-out;
        }

        .scores-section {
            transition: max-height 0.5s ease-in-out, opacity 0.5s ease-in-out, margin-top 0.5s ease-in-out;
        }

        /* استایل برای دکمه‌های مرتب‌سازی نظرات */
        .sort-comments-btn {
            position: relative;
        }

        .sort-comments-btn::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 120%;
            height: 4px;
            background-color: #E4EBF0;
            transition: background-color 0.3s ease;
            border-radius: 100px;
        }

        .sort-comments-btn.active::after {
            background-color: #FD7013;
        }

        .sort-comments-btn.active {
            pointer-events: none;
            cursor: default;
            opacity: 0.88;
        }

        .user-profile-link {
            position: relative;
            text-decoration: none;
            display: inline-flex;
            color: inherit;
        }

        .user-profile-link::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #FD7013;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease-in-out;
        }

        .user-profile-link:hover::after {
            transform-origin: left;
            transform: scaleX(1);
        }

        .user-profile-link:not(:hover)::after {
            transform-origin: right;
            transform: scaleX(0);
        }

        /* انیمیشن گرادینت برای دکمه حرفه‌ای‌ها */
        .sort-comments-btn[data-sort-type="pro"] {
            background: linear-gradient(87deg, var(--Text-Default, #0F172B) 21.07%, #FD7013 30.67%, var(--Text-Default, #0F172B) 36.99%, #09192D 55.41%, #FD7013 57.63%, #0F172B 61.47%, var(--Text-Default, #0F172B) 86.25%);
            background-size: 300% 100%;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient-shift 4s linear infinite;
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 200% 50%;
            }
        }

        /* Override Embla default flex for related products */
        #related .embla__container>* {
            flex: 0 0 auto !important;
            width: 156px !important;
        }

        @media (min-width: 768px) {
            #related .embla__container>* {
                width: 200px !important;
            }
        }

        /* Smooth scroll برای date lists */
        .date-scroll-list-desktop,
        .date-scroll-list-mobile {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        /* انیمیشن برای sessions-panel */
        #sessions-panel {
            transition: transform 0.3s ease-in-out;
        }

        /* Spinner animation */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Discount badge animation */
        @keyframes discount-pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        .discount-badge-animated {
            animation: discount-pulse 2s ease-in-out infinite;
        }

        /* Embla Carousel Styles */
        .embla-sessions-mobile,
        .embla-sessions-desktop {
            overflow: hidden;
            cursor: grab;
            position: relative;
        }

        .embla-sessions-mobile:active,
        .embla-sessions-desktop:active {
            cursor: grabbing;
        }

        .embla__container-sessions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
            backface-visibility: hidden;
            touch-action: pan-x;
            padding-bottom: 210px;
            /* فاصله برای اینکه آخرین آیتم خیلی بالا نره */
        }

        .session-item {
            flex: 0 0 auto;
            min-width: 0;
            position: relative;
        }

        /* اطمینان از اینکه container در حالت عادی overflow visible نداره */
        .sessions-embla-container-mobile,
        .sessions-embla-container-desktop {
            position: relative;
        }

        /* Embla برای تقویم (dates) */
        .embla-dates-mobile,
        .embla-dates-desktop {
            overflow: hidden;
            cursor: grab;
        }

        .embla-dates-mobile:active,
        .embla-dates-desktop:active {
            cursor: grabbing;
        }

        .date-scroll-list-mobile,
        .date-scroll-list-desktop {
            display: flex;
            gap: 8px;
            padding: 0 4px;
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-y;
            -webkit-tap-highlight-color: transparent;
        }

        .date-btn {
            flex: 0 0 auto;
            min-width: 0;
        }

        /* Embla container styling */
        .embla__container {
            backface-visibility: hidden;
            display: flex;
            touch-action: pan-y;
        }

        /* Embla برای tabs موبایل */
        .embla-tabs {
            overflow: hidden;
            cursor: grab;
        }

        .embla-tabs:active {
            cursor: grabbing;
        }

        .embla__container-tabs {
            display: flex;
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-y;
            -webkit-tap-highlight-color: transparent;
            backface-visibility: hidden;
        }

        .embla__container-tabs .tab-btn {
            flex: 0 0 auto;
            min-width: 85px;
        }
    </style>
<?php } ?>
<!-- tehran-banner -->
<?php 
$is_tehran = (mb_strtolower(trim($city_name)) === 'تهران' || mb_strtolower(trim($city_name)) === 'tehran');
if ($is_tehran): ?>
<!-- Tehran Banner - Sticky Animated Ad Banner -->
<!--<div class="tehran-banner-sticky-wrapper lg:mt-1">-->
<!--    <a href="https://escapezoom.ir/tehran-games/?utm_source=internal&utm_medium=banner&utm_campaign=safiran" class="tehran-banner-link block w-screen right-1/2 left-1/2 -ml-[50vw] -mr-[50vw] relative overflow-hidden">-->
<!--        <div class="tehran-banner-wrapper relative h-[105px] md:h-[80px] lg:h-[80px]">-->
            <!-- Animated Background with Pattern -->
<!--            <div class="tehran-banner-bg absolute inset-0"></div>-->
<!--            <div class="tehran-pattern-overlay absolute inset-0"></div>-->
            
            <!-- Animated Glow Effects -->
<!--            <div class="tehran-glow-pulse-1 absolute"></div>-->
<!--            <div class="tehran-glow-pulse-2 absolute"></div>-->

            <!-- Floating Particles - Generated by JS -->
<!--            <div class="tehran-particles absolute inset-0 pointer-events-none overflow-hidden"></div>-->

            <!-- Content Container (Constrained Width) -->
<!--            <div class="container mx-auto px-4 h-full relative z-10">-->
                <!-- Unified Responsive Layout with Order -->
<!--                <div class="tehran-banner-content">-->
                    <!-- Typing Text (Order: Desktop=1, Mobile=2) -->
<!--                    <div class="tehran-typing-container">-->
<!--                        <div class="flex items-center">-->
<!--                            <span class="tehran-typing-text font-black"></span>-->
<!--                            <span class="tehran-cursor">|</span>-->
<!--                        </div>-->
<!--                    </div>-->

                    <!-- Main Text (Order: Desktop=2, Mobile=1) -->
<!--                    <div class="tehran-main-text-container">-->
<!--                        <h2 class="tehran-main-text font-black leading-tight">-->
<!--                            <span class="tehran-word tehran-word-1">اینجا</span> -->
<!--                            <span class="tehran-word tehran-word-2 tehran-highlight-text">اتاق فرارهای</span>-->
<!--                            <span class="tehran-word tehran-word-3 tehran-highlight-text">پرطرفدار تهران</span>-->
<!--                            <span class="tehran-word tehran-word-4">انتظارتو میکشن!</span>-->
<!--                        </h2>-->
<!--                    </div>-->

                    <!-- Discount Code Badge (Order: Desktop=3, Mobile=3) -->
<!--                    <div class="tehran-code-badge-wrapper">-->
<!--                        <div class="tehran-code-badge">-->
<!--                            <div class="tehran-code-shine"></div>-->
<!--                            <div class="flex items-center gap-2">-->
<!--                                <svg class="tehran-tag-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
<!--                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>-->
<!--                                </svg>-->
<!--                                <span class="text-white font-black tracking-wider tehran-code-text">wel150</span>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
        
        <!-- Click Hint Icon (Mobile Only) -->
<!--        <div class="tehran-click-hint">-->
<!--            <div class="tehran-finger-wrapper">-->
<!--                <span class="tehran-finger-icon">👆🏻</span>-->
<!--                <div class="tehran-click-ripple"></div>-->
<!--                <div class="tehran-click-ripple tehran-click-ripple-2"></div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </a>-->
<!--</div>-->

<!-- Tehran Banner Styles -->
<style>


    /* Sticky Wrapper */
/*    .tehran-banner-sticky-wrapper {*/
/*        position: sticky;*/
/*        top: 0;*/
/*        z-index: 40;*/
/*        transition: all 0.3s ease;*/
/*    }*/

    /* Mobile: top 66px */
/*    @media (max-width: 768px) {*/
/*        .tehran-banner-sticky-wrapper {*/
/*            top: 66px;*/
/*        }*/
/*    }*/

    /* Banner Container */
/*    .tehran-banner-link {*/
/*        transition: all 0.3s ease;*/
/*    }*/

/*    .tehran-banner-wrapper {*/
/*        position: relative;*/
/*        overflow: hidden;*/
/*    }*/

    /* Animated Gradient Background with Pattern - Darker Black Friday Style */
/*    .tehran-banner-bg {*/
/*        background: linear-gradient(135deg,*/
/*                #450a0a 0%,*/
/*                #7f1d1d 20%,*/
/*                #991b1b 40%,*/
/*                #7f1d1d 60%,*/
/*                #5e0c0c 80%,*/
/*                #450a0a 100%);*/
/*        background-size: 200% 200%;*/
/*        animation: tehranBgShift 15s ease infinite;*/
/*    }*/

/*    @keyframes tehranBgShift {*/
/*        0%, 100% { background-position: 0% 50%; }*/
/*        50% { background-position: 100% 50%; }*/
/*    }*/

    /* Animated Pattern Overlay */
/*    .tehran-pattern-overlay {*/
/*        background-image: */
/*            radial-gradient(circle, rgba(255, 255, 255, 0.03) 1px, transparent 1px),*/
/*            radial-gradient(circle, rgba(255, 255, 255, 0.03) 1px, transparent 1px);*/
/*        background-size: 20px 20px, 30px 30px;*/
/*        background-position: 0 0, 10px 10px;*/
/*        animation: tehranPatternMove 20s linear infinite;*/
/*        opacity: 0.4;*/
/*    }*/

/*    @keyframes tehranPatternMove {*/
/*        0% { background-position: 0 0, 10px 10px; }*/
/*        100% { background-position: 20px 20px, 30px 30px; }*/
/*    }*/

    /* Animated Glow Pulses */
/*    .tehran-glow-pulse-1,*/
/*    .tehran-glow-pulse-2 {*/
/*        width: 300px;*/
/*        height: 300px;*/
/*        border-radius: 50%;*/
/*        filter: blur(80px);*/
/*        pointer-events: none;*/
/*        opacity: 0.3;*/
/*    }*/

/*    .tehran-glow-pulse-1 {*/
/*        left: 10%;*/
/*        top: 50%;*/
/*        transform: translateY(-50%);*/
/*        background: radial-gradient(circle, rgba(239, 68, 68, 0.6), transparent 70%);*/
/*        animation: tehranGlowPulse1 4s ease-in-out infinite;*/
/*    }*/

/*    .tehran-glow-pulse-2 {*/
/*        right: 10%;*/
/*        top: 50%;*/
/*        transform: translateY(-50%);*/
/*        background: radial-gradient(circle, rgba(220, 38, 38, 0.5), transparent 70%);*/
/*        animation: tehranGlowPulse2 5s ease-in-out infinite 1s;*/
/*    }*/

/*    @keyframes tehranGlowPulse1 {*/
/*        0%, 100% { opacity: 0.2; transform: translateY(-50%) scale(1); }*/
/*        50% { opacity: 0.4; transform: translateY(-50%) scale(1.2); }*/
/*    }*/

/*    @keyframes tehranGlowPulse2 {*/
/*        0%, 100% { opacity: 0.25; transform: translateY(-50%) scale(1); }*/
/*        50% { opacity: 0.45; transform: translateY(-50%) scale(1.3); }*/
/*    }*/

    /* Discount Code Badge - Animated & Eye-catching */
/*    .tehran-code-badge-wrapper {*/
/*        display: inline-flex;*/
/*        justify-content: center;*/
/*        flex-shrink: 0;*/
/*        order: 3;*/
/*    }*/

/*    .tehran-code-badge {*/
/*        position: relative;*/
/*        background: linear-gradient(135deg, */
/*            rgba(255, 255, 255, 0.25) 0%,*/
/*            rgba(255, 255, 255, 0.15) 50%,*/
/*            rgba(255, 255, 255, 0.25) 100%);*/
/*        backdrop-filter: blur(12px) saturate(180%);*/
/*        -webkit-backdrop-filter: blur(12px) saturate(180%);*/
/*        border: 2px solid rgba(255, 255, 255, 0.6);*/
/*        border-radius: 12px;*/
/*        padding: 6px 12px;*/
/*        box-shadow: */
/*            0 4px 20px rgba(0, 0, 0, 0.25),*/
/*            0 0 25px rgba(255, 107, 53, 0.4),*/
/*            inset 0 1px 1px rgba(255, 255, 255, 0.5),*/
/*            inset 0 -1px 1px rgba(0, 0, 0, 0.1);*/
/*        overflow: hidden;*/
/*        will-change: transform, box-shadow;*/
/*    }*/

    /* Animated gradient overlay for glass effect */
/*    .tehran-code-shine {*/
/*        position: absolute;*/
/*        top: -50%;*/
/*        left: -100%;*/
/*        width: 50%;*/
/*        height: 200%;*/
/*        background: linear-gradient(*/
/*            to right,*/
/*            transparent,*/
/*            rgba(255, 255, 255, 0.5),*/
/*            transparent*/
/*        );*/
/*        transform: skewX(-20deg);*/
/*        animation: tehranShineSweep 3.5s ease-in-out infinite;*/
/*        pointer-events: none;*/
/*    }*/

/*    @keyframes tehranShineSweep {*/
/*        0%, 20% {*/
/*            left: -100%;*/
/*            opacity: 0;*/
/*        }*/
/*        25% {*/
/*            opacity: 1;*/
/*        }*/
/*        45%, 100% {*/
/*            left: 150%;*/
/*            opacity: 0;*/
/*        }*/
/*    }*/

/*    .tehran-tag-icon {*/
/*        color: #fff;*/
/*        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));*/
/*    }*/

/*    .tehran-code-text {*/
/*        color: #fff;*/
/*        font-weight: 900;*/
/*        text-shadow: */
/*            1px 1px 3px rgba(0, 0, 0, 0.6),*/
/*            0 0 10px rgba(255, 107, 53, 0.6);*/
/*        letter-spacing: 0.1em;*/
/*    }*/

    /* Main Text Styles - 3D Bold Effect */
/*    .tehran-main-text {*/
/*        color: #ffffff;*/
/*        font-weight: 900;*/
/*        text-shadow:*/
/*            2px 2px 0 rgba(0, 0, 0, 0.3),*/
/*            3px 3px 0 rgba(0, 0, 0, 0.2),*/
/*            4px 4px 0 rgba(0, 0, 0, 0.1),*/
/*            0 0 20px rgba(255, 255, 255, 0.4);*/
/*        letter-spacing: -0.01em;*/
/*        position: absolute;*/
/*        left: 50%;*/
/*        top: 50%;*/
/*        transform: translate(-50%,-50%);*/
/*        width: 100%;*/
/*    }*/

    /* Word Styles for GSAP Animation */
/*    .tehran-word {*/
/*        display: inline-block;*/
/*        position: relative;*/
/*        visibility: hidden;*/
/*        will-change: transform, opacity;*/
/*    }*/

    /* Highlighted Text Animation - White with Glow Shadow */
/*    .tehran-highlight-text {*/
/*        position: relative;*/
/*        color: #ffffff;*/
/*        font-weight: 900;*/
/*        font-size: 1.1em;*/
/*        display: inline-block;*/
/*        padding: 0 4px;*/
/*        text-shadow: */
/*            1px 1px 2px rgba(0, 0, 0, 0.6),*/
/*            2px 2px 4px rgba(0, 0, 0, 0.4),*/
/*            0 0 10px rgba(255, 107, 53, 0.6),*/
/*            0 0 20px rgba(255, 107, 53, 0.4),*/
/*            0 4px 8px rgba(0, 0, 0, 0.3);*/
/*        filter: drop-shadow(0 3px 6px rgba(255, 107, 53, 0.5));*/
/*        animation: tehranGlowPulse 2s ease-in-out infinite;*/
/*    }*/

/*    @keyframes tehranGlowPulse {*/
/*        0%, 100% {*/
/*            text-shadow: */
/*                1px 1px 2px rgba(0, 0, 0, 0.6),*/
/*                2px 2px 4px rgba(0, 0, 0, 0.4),*/
/*                0 0 10px rgba(255, 107, 53, 0.6),*/
/*                0 0 20px rgba(255, 107, 53, 0.4),*/
/*                0 4px 8px rgba(0, 0, 0, 0.3);*/
/*            filter: drop-shadow(0 3px 6px rgba(255, 107, 53, 0.5));*/
/*        }*/
/*        50% {*/
/*            text-shadow: */
/*                1px 1px 2px rgba(0, 0, 0, 0.6),*/
/*                2px 2px 4px rgba(0, 0, 0, 0.4),*/
/*                0 0 15px rgba(255, 107, 53, 0.8),*/
/*                0 0 30px rgba(255, 107, 53, 0.6),*/
/*                0 5px 12px rgba(0, 0, 0, 0.4);*/
/*            filter: drop-shadow(0 5px 10px rgba(255, 107, 53, 0.7));*/
/*        }*/
/*    }*/


    /* Floating Particles */
/*    .tehran-particles {*/
/*        z-index: 0;*/
/*    }*/

/*    .tehran-particle {*/
/*        position: absolute;*/
/*        width: 6px;*/
/*        height: 6px;*/
/*        background: #fb923c;*/
/*        border-radius: 50%;*/
/*        box-shadow: 0 0 15px #fb923c, 0 0 30px #fb923c, 0 0 45px rgba(251, 146, 60, 0.5);*/
/*        will-change: transform, opacity;*/
/*    }*/

/*    .tehran-particle-1 {*/
/*        left: 5%;*/
/*        top: 15%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat1 12s infinite ease-in-out;*/
/*    }*/

/*    .tehran-particle-2 {*/
/*        left: 25%;*/
/*        top: 65%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat2 14s infinite ease-in-out;*/
/*        animation-delay: 1s;*/
/*    }*/

/*    .tehran-particle-3 {*/
/*        left: 55%;*/
/*        top: 25%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat3 13s infinite ease-in-out;*/
/*        animation-delay: 2s;*/
/*    }*/

/*    .tehran-particle-4 {*/
/*        left: 75%;*/
/*        top: 75%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat4 15s infinite ease-in-out;*/
/*        animation-delay: 3s;*/
/*    }*/

/*    .tehran-particle-5 {*/
/*        left: 45%;*/
/*        top: 85%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat5 11s infinite ease-in-out;*/
/*        animation-delay: 4s;*/
/*    }*/

/*    .tehran-particle-6 {*/
/*        left: 15%;*/
/*        top: 45%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat6 16s infinite ease-in-out;*/
/*        animation-delay: 5s;*/
/*    }*/

/*    .tehran-particle-7 {*/
/*        left: 65%;*/
/*        top: 10%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat1 13s infinite ease-in-out;*/
/*        animation-delay: 6s;*/
/*    }*/

/*    .tehran-particle-8 {*/
/*        left: 35%;*/
/*        top: 90%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat2 12s infinite ease-in-out;*/
/*        animation-delay: 7s;*/
/*    }*/

/*    .tehran-particle-9 {*/
/*        left: 85%;*/
/*        top: 40%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat3 14s infinite ease-in-out;*/
/*        animation-delay: 1.5s;*/
/*    }*/

/*    .tehran-particle-10 {*/
/*        left: 12%;*/
/*        top: 30%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat4 11s infinite ease-in-out;*/
/*        animation-delay: 2.5s;*/
/*    }*/

/*    .tehran-particle-11 {*/
/*        left: 40%;*/
/*        top: 20%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat5 15s infinite ease-in-out;*/
/*        animation-delay: 3.5s;*/
/*    }*/

/*    .tehran-particle-12 {*/
/*        left: 70%;*/
/*        top: 55%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat6 13s infinite ease-in-out;*/
/*        animation-delay: 4.5s;*/
/*    }*/

/*    .tehran-particle-13 {*/
/*        left: 20%;*/
/*        top: 70%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat1 14s infinite ease-in-out;*/
/*        animation-delay: 5.5s;*/
/*    }*/

/*    .tehran-particle-14 {*/
/*        left: 60%;*/
/*        top: 50%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat2 12s infinite ease-in-out;*/
/*        animation-delay: 6.5s;*/
/*    }*/

/*    .tehran-particle-15 {*/
/*        left: 90%;*/
/*        top: 20%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat3 16s infinite ease-in-out;*/
/*        animation-delay: 0.5s;*/
/*    }*/

/*    .tehran-particle-16 {*/
/*        left: 8%;*/
/*        top: 80%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat4 13s infinite ease-in-out;*/
/*        animation-delay: 7.5s;*/
/*    }*/

/*    .tehran-particle-17 {*/
/*        left: 50%;*/
/*        top: 35%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat5 14s infinite ease-in-out;*/
/*        animation-delay: 8s;*/
/*    }*/

/*    .tehran-particle-18 {*/
/*        left: 80%;*/
/*        top: 60%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat6 11s infinite ease-in-out;*/
/*        animation-delay: 8.5s;*/
/*    }*/

/*    .tehran-particle-19 {*/
/*        left: 32%;*/
/*        top: 12%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat1 13s infinite ease-in-out;*/
/*        animation-delay: 9s;*/
/*    }*/

/*    .tehran-particle-20 {*/
/*        left: 68%;*/
/*        top: 82%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat2 14s infinite ease-in-out;*/
/*        animation-delay: 9.5s;*/
/*    }*/

/*    .tehran-particle-21 {*/
/*        left: 18%;*/
/*        top: 58%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat3 12s infinite ease-in-out;*/
/*        animation-delay: 10s;*/
/*    }*/

/*    .tehran-particle-22 {*/
/*        left: 88%;*/
/*        top: 28%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat4 15s infinite ease-in-out;*/
/*        animation-delay: 0.8s;*/
/*    }*/

/*    .tehran-particle-23 {*/
/*        left: 42%;*/
/*        top: 72%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat5 11s infinite ease-in-out;*/
/*        animation-delay: 10.5s;*/
/*    }*/

/*    .tehran-particle-24 {*/
/*        left: 78%;*/
/*        top: 48%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat6 13s infinite ease-in-out;*/
/*        animation-delay: 11s;*/
/*    }*/

/*    .tehran-particle-25 {*/
/*        left: 28%;*/
/*        top: 38%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat1 14s infinite ease-in-out;*/
/*        animation-delay: 11.5s;*/
/*    }*/

/*    .tehran-particle-26 {*/
/*        left: 58%;*/
/*        top: 68%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat2 12s infinite ease-in-out;*/
/*        animation-delay: 12s;*/
/*    }*/

/*    .tehran-particle-27 {*/
/*        left: 10%;*/
/*        top: 22%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat3 15s infinite ease-in-out;*/
/*        animation-delay: 1.2s;*/
/*    }*/

/*    .tehran-particle-28 {*/
/*        left: 92%;*/
/*        top: 52%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat4 13s infinite ease-in-out;*/
/*        animation-delay: 12.5s;*/
/*    }*/

/*    .tehran-particle-29 {*/
/*        left: 48%;*/
/*        top: 18%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat5 14s infinite ease-in-out;*/
/*        animation-delay: 13s;*/
/*    }*/

/*    .tehran-particle-30 {*/
/*        left: 72%;*/
/*        top: 42%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat6 11s infinite ease-in-out;*/
/*        animation-delay: 13.5s;*/
/*    }*/

/*    .tehran-particle-31 {*/
/*        left: 22%;*/
/*        top: 78%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat1 12s infinite ease-in-out;*/
/*        animation-delay: 14s;*/
/*    }*/

/*    .tehran-particle-32 {*/
/*        left: 62%;*/
/*        top: 32%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat2 15s infinite ease-in-out;*/
/*        animation-delay: 1.8s;*/
/*    }*/

/*    .tehran-particle-33 {*/
/*        left: 38%;*/
/*        top: 52%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat3 13s infinite ease-in-out;*/
/*        animation-delay: 14.5s;*/
/*    }*/

/*    .tehran-particle-34 {*/
/*        left: 82%;*/
/*        top: 12%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat4 14s infinite ease-in-out;*/
/*        animation-delay: 15s;*/
/*    }*/

/*    .tehran-particle-35 {*/
/*        left: 52%;*/
/*        top: 88%;*/
/*        opacity: 0.1;*/
/*        animation: tehranFloat5 12s infinite ease-in-out;*/
/*        animation-delay: 15.5s;*/
/*    }*/

/*    @keyframes tehranFloat1 {*/
/*        0%, 100% {*/
/*            transform: translate(0, 0) scale(0.8);*/
/*            opacity: 0.15;*/
/*        }*/
/*        25% {*/
/*            transform: translate(60px, -50px) scale(1.4);*/
/*            opacity: 0.35;*/
/*        }*/
/*        50% {*/
/*            transform: translate(-40px, 60px) scale(0.6);*/
/*            opacity: 0.2;*/
/*        }*/
/*        75% {*/
/*            transform: translate(50px, 30px) scale(1.2);*/
/*            opacity: 0.4;*/
/*        }*/
/*    }*/

/*    @keyframes tehranFloat2 {*/
/*        0%, 100% {*/
/*            transform: translate(0, 0) scale(1);*/
/*            opacity: 0.2;*/
/*        }*/
/*        25% {*/
/*            transform: translate(-70px, 40px) scale(0.7);*/
/*            opacity: 0.25;*/
/*        }*/
/*        50% {*/
/*            transform: translate(50px, -70px) scale(1.5);*/
/*            opacity: 0.45;*/
/*        }*/
/*        75% {*/
/*            transform: translate(-30px, 50px) scale(0.9);*/
/*            opacity: 0.3;*/
/*        }*/
/*    }*/

/*    @keyframes tehranFloat3 {*/
/*        0%, 100% {*/
/*            transform: translate(0, 0) scale(0.9);*/
/*            opacity: 0.18;*/
/*        }*/
/*        25% {*/
/*            transform: translate(40px, 60px) scale(1.3);*/
/*            opacity: 0.38;*/
/*        }*/
/*        50% {*/
/*            transform: translate(-60px, -50px) scale(0.65);*/
/*            opacity: 0.22;*/
/*        }*/
/*        75% {*/
/*            transform: translate(70px, -30px) scale(1.1);*/
/*            opacity: 0.42;*/
/*        }*/
/*    }*/

/*    @keyframes tehranFloat4 {*/
/*        0%, 100% {*/
/*            transform: translate(0, 0) scale(1.1);*/
/*            opacity: 0.25;*/
/*        }*/
/*        25% {*/
/*            transform: translate(-50px, -60px) scale(0.75);*/
/*            opacity: 0.2;*/
/*        }*/
/*        50% {*/
/*            transform: translate(60px, 50px) scale(1.4);*/
/*            opacity: 0.4;*/
/*        }*/
/*        75% {*/
/*            transform: translate(-40px, -40px) scale(0.85);*/
/*            opacity: 0.28;*/
/*        }*/
/*    }*/

/*    @keyframes tehranFloat5 {*/
/*        0%, 100% {*/
/*            transform: translate(0, 0) scale(0.85);*/
/*            opacity: 0.19;*/
/*        }*/
/*        25% {*/
/*            transform: translate(55px, -45px) scale(1.35);*/
/*            opacity: 0.39;*/
/*        }*/
/*        50% {*/
/*            transform: translate(-65px, 55px) scale(0.7);*/
/*            opacity: 0.24;*/
/*        }*/
/*        75% {*/
/*            transform: translate(45px, 40px) scale(1.15);*/
/*            opacity: 0.41;*/
/*        }*/
/*    }*/

/*    @keyframes tehranFloat6 {*/
/*        0%, 100% {*/
/*            transform: translate(0, 0) scale(1.05);*/
/*            opacity: 0.21;*/
/*        }*/
/*        25% {*/
/*            transform: translate(-55px, 65px) scale(0.8);*/
/*            opacity: 0.26;*/
/*        }*/
/*        50% {*/
/*            transform: translate(65px, -55px) scale(1.45);*/
/*            opacity: 0.43;*/
/*        }*/
/*        75% {*/
/*            transform: translate(-35px, 45px) scale(0.95);*/
/*            opacity: 0.32;*/
/*        }*/
/*    }*/

    /* Unified Responsive Banner Layout */
/*    .tehran-banner-content {*/
/*        display: flex;*/
/*        align-items: center;*/
/*        justify-content: space-between;*/
/*        flex-wrap: wrap;*/
/*        height: 100%;*/
/*        width: 100%;*/
/*        gap: 12px;*/
/*        padding: 8px 0;*/
/*    }*/

    /* Typing Container */
/*    .tehran-typing-container {*/
/*        display: inline-flex;*/
/*        align-items: center;*/
/*        flex-shrink: 0;*/
/*        order: 2;*/
/*        transition: opacity 0.5s ease;*/
/*    }*/

/*    .tehran-typing-text {*/
/*        color: #ffe4e6;*/
/*        font-weight: 900;*/
/*        text-shadow:*/
/*            2px 2px 0 rgba(0, 0, 0, 0.6),*/
/*            0 0 10px rgba(255, 107, 53, 0.8),*/
/*            0 0 20px rgba(255, 107, 53, 0.5),*/
/*            0 0 30px rgba(255, 71, 87, 0.3);*/
/*        display: inline-block;*/
/*        font-size: 0.75rem;*/
/*    }*/

    /* Main Text Container */
/*    .tehran-main-text-container {*/
/*        order: 1;*/
/*        width: 100%;*/
/*        text-align: center;*/
/*        padding: 0 8px;*/
/*        transition: opacity 0.3s ease;*/
/*    }*/

/*    .tehran-main-text {*/
/*        font-size: large;*/
/*        line-height: 1.4;*/
/*        text-align: center;*/
/*    }*/

    /* Code Badge - Responsive base styles */
/*    .tehran-code-badge {*/
/*        padding: 3px 10px;*/
/*    }*/

/*    .tehran-tag-icon {*/
/*        width: 0.75rem;*/
/*        height: 0.75rem;*/
/*    }*/

/*    .tehran-code-text {*/
/*        font-size: 0.75rem;*/
/*    }*/

    /* Cursor Blink */
/*    .tehran-cursor {*/
/*        color: #fca5a5;*/
/*        font-weight: 900;*/
/*        font-size: 1.2em;*/
/*        animation: tehranCursorBlink 0.8s infinite;*/
/*        margin-right: 2px;*/
/*        display: inline-block;*/
/*    }*/

/*    @keyframes tehranCursorBlink {*/
/*        0%, 49% { opacity: 1; }*/
/*        50%, 100% { opacity: 0; }*/
/*    }*/

    /* Desktop Layout */
/*    @media (min-width: 769px) {*/
/*        .tehran-banner-content {*/
/*            flex-wrap: nowrap;*/
/*            justify-content: space-between;*/
/*            padding: 0;*/
/*        }*/

/*        .tehran-typing-container {*/
/*            order: 1;*/
/*        }*/

/*        .tehran-main-text-container {*/
/*            order: 2;*/
/*            width: auto;*/
/*            flex: 1;*/
/*            padding: 0 16px;*/
/*        }*/

/*        .tehran-main-text {*/
/*            font-size: 1.125rem;*/
/*        }*/

/*        .tehran-typing-text {*/
/*            font-size: 0.875rem;*/
/*        }*/

/*        .tehran-code-badge {*/
/*            padding: 4px 12px;*/
/*        }*/

/*        .tehran-tag-icon {*/
/*            width: 1rem;*/
/*            height: 1rem;*/
/*        }*/

/*        .tehran-code-text {*/
/*            font-size: 0.875rem;*/
/*        }*/
/*    }*/

/*    @media (min-width: 1024px) {*/
/*        .tehran-main-text {*/
/*            font-size: 1.5rem;*/
/*        }*/

/*        .tehran-typing-text {*/
/*            font-size: 20px;*/
/*        }*/

/*        .tehran-code-badge {*/
/*            padding: 6px 14px;*/
/*        }*/

/*        .tehran-code-text {*/
/*            font-size: 1rem;*/
/*        }*/
/*    }*/

    /* Mobile Specific Adjustments */
/*    @media (max-width: 768px) {*/
/*        .tehran-banner-wrapper {*/
/*            height: 90px !important;*/
/*        }*/

/*        .tehran-banner-content {*/
/*            padding: 8px 0 0 0;*/
/*            justify-content: center;*/
/*            position: relative;*/
/*        }*/

        /* Center typing text on mobile - same position as main text */
/*        .tehran-typing-container {*/
/*            position: absolute;*/
/*            left: 50%;*/
/*            top: 50%;*/
/*            transform: translate(-50%, -50%);*/
/*            order: 1;*/
/*            justify-content: center;*/
/*            width: 100%;*/
/*        }*/

/*        .tehran-typing-text {*/
/*            font-size: 18px;*/
/*        }*/

        /* Hide code badge on mobile */
/*        .tehran-code-badge-wrapper {*/
/*            display: none !important;*/
/*        }*/

        /* Main text takes center position on mobile */
/*        .tehran-main-text-container {*/
/*            position: absolute;*/
/*            left: 50%;*/
/*            top: 50%;*/
/*            transform: translate(-50%, -50%);*/
/*            order: 1;*/
/*        }*/

/*        .tehran-highlight-text {*/
/*            font-size: 1.15em;*/
/*        }*/

/*        .tehran-particle {*/
/*            width: 4px;*/
/*            height: 4px;*/
/*        }*/

        /* Reduce animation complexity on mobile */
/*        .tehran-pattern-overlay {*/
/*            animation-duration: 30s;*/
/*        }*/

/*        .tehran-glow-pulse-1,*/
/*        .tehran-glow-pulse-2 {*/
/*            display: none;*/
/*        }*/
/*    }*/

/*    @media (max-width: 640px) {*/
/*        .tehran-banner-wrapper {*/
/*            height: 60px !important;*/
/*        }*/

/*        .tehran-main-text {*/
/*            font-size: large;*/
/*        }*/

/*        .tehran-typing-text {*/
/*            font-size: 16.5px;*/
/*        }*/

/*        .tehran-particle {*/
/*            width: 3px;*/
/*            height: 3px;*/
/*        }*/
/*    }*/

/*    @media (max-width: 400px) {*/
/*        .tehran-main-text {*/
/*            font-size: 14px !important;*/
/*        }*/
/*    }*/

    /* Click Hint Icon - Mobile Only */
/*    .tehran-click-hint {*/
/*        position: absolute;*/
/*        bottom: 0;*/
/*        left: 12px;*/
/*        display: none;*/
/*        align-items: center;*/
/*        justify-content: center;*/
/*        z-index: 50;*/
/*        pointer-events: none;*/
/*    }*/

/*    .tehran-finger-wrapper {*/
/*        position: relative;*/
/*        display: flex;*/
/*        align-items: center;*/
/*        justify-content: center;*/
/*        font-size: 32px;*/
/*        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));*/
/*        animation: tehranFingerTap 1.2s ease-in-out infinite;*/
/*    }*/
/*    .tehran-finger-icon {*/
/*        position: absolute;*/
/*        left: -15px;*/
/*        z-index: 10;*/
/*    }*/
    /* Click Ripple Circles - دقیقاً زیر نوک انگشت */
/*    .tehran-click-ripple {*/
/*        position: absolute;*/
/*        bottom: 20px;*/
/*        left: 4px;*/
/*        z-index: 0;*/
/*        transform: translate(-50%, 50%);*/
/*        width: 20px;*/
/*        height: 20px;*/
/*        border: 3px solid rgba(255, 255, 255, 0.9);*/
/*        border-radius: 50%;*/
/*        animation: tehranClickRipple 1.2s ease-out infinite;*/
/*        box-shadow: 0 0 10px rgba(255, 107, 53, 0.6);*/
/*    }*/

/*    .tehran-click-ripple-2 {*/
/*        animation-delay: 0.4s;*/
/*        bottom: 20px;*/
/*        left: 4px;*/
/*        z-index: 0;*/
/*    }*/

    /* Finger tap animation */
/*    @keyframes tehranFingerTap {*/
/*        0%, 100% {*/
/*            transform: translateY(0) scale(1);*/
/*        }*/
/*        40% {*/
/*            transform: translateY(-6px) scale(1.05);*/
/*        }*/
/*        50% {*/
/*            transform: translateY(0) scale(0.95);*/
/*        }*/
/*        60% {*/
/*            transform: translateY(-2px) scale(1);*/
/*        }*/
/*    }*/

    /* Click Ripple animation - گسترش از مرکز */
/*    @keyframes tehranClickRipple {*/
/*        0% {*/
/*            width: 10px;*/
/*            height: 10px;*/
/*            border-width: 4px;*/
/*            opacity: 0;*/
/*            transform: translate(-50%, 50%) scale(0.5);*/
/*        }*/
/*        10% {*/
/*            opacity: 1;*/
/*        }*/
/*        100% {*/
/*            width: 50px;*/
/*            height: 50px;*/
/*            border-width: 1px;*/
/*            opacity: 0;*/
/*            transform: translate(-50%, 50%) scale(1);*/
/*        }*/
/*    }*/

    /* Show only on mobile */
/*    @media (max-width: 768px) {*/
/*        .tehran-click-hint {*/
/*            display: flex;*/
/*        }*/
/*    }*/
</style>

<script>
// NEW: GSAP Typing Animation System for Tehran Banner
// let typingTimeline;
// let mainTextTimeline;

// function initTypingAnimation() {
//     if (typeof gsap === 'undefined') {
//         setTimeout(initTypingAnimation, 100);
//         return;
//     }
    
//     const typingElement = document.querySelector('.tehran-typing-text');
//     const mainTextContainer = document.querySelector('.tehran-main-text-container');
//     const typingContainer = document.querySelector('.tehran-typing-container');
    
//     if (!typingElement || !mainTextContainer || !typingContainer) return;
    
//     const isDesktop = window.innerWidth >= 769;
    
//     // Type text animation - smooth typing effect
//     function typeText(text) {
//         const tl = gsap.timeline();
        
//         // Clear first
//         tl.call(() => {
//             typingElement.textContent = '';
//         });
        
//         // Type each character with proper timing
//         const chars = text.split('');
//         chars.forEach((char, i) => {
//             tl.to({}, {
//                 duration: 0.05, // 50ms per character (slightly faster)
//                 onStart: () => {
//                     typingElement.textContent += char;
//                 }
//             });
//         });
        
//         return tl;
//     }
    
//     // Delete text animation - smooth backspace effect
//     function deleteText() {
//         const currentText = typingElement.textContent;
//         const tl = gsap.timeline();
        
//         if (currentText.length === 0) return tl;
        
//         // Delete each character with proper timing
//         for (let i = currentText.length; i > 0; i--) {
//             tl.to({}, {
//                 duration: 0.04, // 40ms per character
//                 onStart: () => {
//                     typingElement.textContent = currentText.substring(0, i - 1);
//                 }
//             });
//         }
        
//         // Ensure complete clear
//         tl.call(() => {
//             typingElement.textContent = '';
//         });
        
//         return tl;
//     }
    
//     if (isDesktop) {
//         // Desktop: دو متن - کد تخفیف و ته هیجان
//         const text1 = 'کد تخفیف 1.5 میلیون ریالی';
//         const text2 = 'تهِ وحشت و هیجان';
        
//         typingTimeline = gsap.timeline({ repeat: -1 });
        
//         typingTimeline
//             // متن اول: کد تخفیف
//             .add(typeText(text1))
//             .to({}, { duration: 2 }) // Hold
//             .add(deleteText())
//             .to({}, { duration: 0.3 }) // Quick transition
            
//             // متن دوم: ته هیجان
//             .add(typeText(text2))
//             .to({}, { duration: 2 }) // Hold
//             .add(deleteText())
//             .to({}, { duration: 0.3 }); // Quick transition before restart
            
//     } else {
//         // Mobile: فقط یک متن - کد تخفیف
//         const mobileText = 'کد تخفیف 1.5 میلیون ریالی';
        
//         gsap.set(typingContainer, { autoAlpha: 0 }); // Start hidden
        
//         const mobileTL = gsap.timeline({ repeat: -1 });
        
//         // 1. Show and animate main text
//         mobileTL
//             .to(mainTextContainer, { autoAlpha: 1, duration: 0.3 })
//             .call(() => {
//                 // Reset and play main text animation
//                 if (mainTextTimeline) {
//                     mainTextTimeline.restart();
//                 }
//             })
//             .to({}, { duration: 2.2 }) // Wait for main text animation + hold
            
//         // 2. Fade out main text quickly
//             .to(mainTextContainer, { autoAlpha: 0, duration: 0.3 })
            
//         // 3. Show typing and animate
//             .to(typingContainer, { autoAlpha: 1, duration: 0.3 })
//             .add(typeText(mobileText))
//             .to({}, { duration: 1.5 }) // Hold typed text
//             .add(deleteText())
            
//         // 4. Hide typing and quickly show main text
//             .to(typingContainer, { autoAlpha: 0, duration: 0.3 })
//             .call(() => {
//                 // Reset main text for next loop
//                 gsap.set('.tehran-word', { autoAlpha: 0, scale: 0.3 });
//                 // Clear typing element
//                 typingElement.textContent = '';
//             })
//             .to({}, { duration: 0.3 }); // Brief pause before main text
//     }
// }

// // Initialize when ready
// if (document.readyState === 'loading') {
//     document.addEventListener('DOMContentLoaded', initTypingAnimation);
// } else {
//     initTypingAnimation();
// }

// // Generate CSS Particles
// (function() {
//     if (document.readyState === 'loading') {
//         document.addEventListener('DOMContentLoaded', generateParticles);
//     } else {
//         generateParticles();
//     }
    
//     function generateParticles() {
//         const particlesContainer = document.querySelector('.tehran-particles');
//         if (!particlesContainer) return;
        
//         const particleCount = 35;
        
//         for (let i = 1; i <= particleCount; i++) {
//             const particle = document.createElement('div');
//             particle.className = `tehran-particle tehran-particle-${i}`;
//             particlesContainer.appendChild(particle);
//         }
//     }
// })();

// // Three.js 3D Particle System (Three.js is loaded globally via functions.php for product pages)
// (function() {
//     // Wait for DOM and Three.js to be ready
//     if (document.readyState === 'loading') {
//         document.addEventListener('DOMContentLoaded', initThreeJS);
//     } else {
//         initThreeJS();
//     }
    
//     function initThreeJS() {
//         if (typeof THREE === 'undefined') {
//             console.error('Three.js library not loaded');
//             return;
//         }
        
//         const container = document.querySelector('.tehran-banner-wrapper');
//         if (!container) return;
        
//         // Setup scene
//         const scene = new THREE.Scene();
//         const camera = new THREE.PerspectiveCamera(75, container.offsetWidth / container.offsetHeight, 0.1, 1000);
//         const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        
//         renderer.setSize(container.offsetWidth, container.offsetHeight);
//         renderer.setClearColor(0x000000, 0);
//         renderer.domElement.style.position = 'absolute';
//         renderer.domElement.style.top = '0';
//         renderer.domElement.style.left = '0';
//         renderer.domElement.style.pointerEvents = 'none';
//         renderer.domElement.style.zIndex = '5';
        
//         container.insertBefore(renderer.domElement, container.firstChild);
        
//         // Create particles
//         const particlesGeometry = new THREE.BufferGeometry();
//         const particleCount = 100;
//         const positions = new Float32Array(particleCount * 3);
//         const velocities = [];
        
//         for (let i = 0; i < particleCount * 3; i += 3) {
//             positions[i] = (Math.random() - 0.5) * 20;
//             positions[i + 1] = (Math.random() - 0.5) * 10;
//             positions[i + 2] = (Math.random() - 0.5) * 10;
            
//             velocities.push({
//                 x: (Math.random() - 0.5) * 0.02,
//                 y: (Math.random() - 0.5) * 0.02,
//                 z: (Math.random() - 0.5) * 0.02
//             });
//         }
        
//         particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        
//         const particlesMaterial = new THREE.PointsMaterial({
//             color: 0xff6b35,
//             size: 0.15,
//             transparent: true,
//             opacity: 0.6,
//             blending: THREE.AdditiveBlending
//         });
        
//         const particleSystem = new THREE.Points(particlesGeometry, particlesMaterial);
//         scene.add(particleSystem);
        
//         camera.position.z = 5;
        
//         // Animation loop
//         function animate() {
//             requestAnimationFrame(animate);
            
//             const positions = particleSystem.geometry.attributes.position.array;
            
//             for (let i = 0; i < particleCount; i++) {
//                 const i3 = i * 3;
                
//                 positions[i3] += velocities[i].x;
//                 positions[i3 + 1] += velocities[i].y;
//                 positions[i3 + 2] += velocities[i].z;
                
//                 // Boundary check and bounce
//                 if (Math.abs(positions[i3]) > 10) velocities[i].x *= -1;
//                 if (Math.abs(positions[i3 + 1]) > 5) velocities[i].y *= -1;
//                 if (Math.abs(positions[i3 + 2]) > 5) velocities[i].z *= -1;
//             }
            
//             particleSystem.geometry.attributes.position.needsUpdate = true;
//             particleSystem.rotation.y += 0.001;
            
//             renderer.render(scene, camera);
//         }
        
//         animate();
    
//         // Handle resize
//         window.addEventListener('resize', () => {
//             if (container && renderer) {
//                 camera.aspect = container.offsetWidth / container.offsetHeight;
//                 camera.updateProjectionMatrix();
//                 renderer.setSize(container.offsetWidth, container.offsetHeight);
//             }
//         });
//     }
// })();

// // GSAP Animation for Tehran Banner (GSAP is loaded globally via functions.php)
// (function() {
//     // Wait for DOM and GSAP to be ready
//     if (document.readyState === 'loading') {
//         document.addEventListener('DOMContentLoaded', initGSAPAnimation);
//     } else {
//         initGSAPAnimation();
//     }
    
//     function initGSAPAnimation() {
//         // Check if GSAP is available
//         if (typeof gsap === 'undefined') {
//             console.error('GSAP library not loaded');
//             return;
//         }

//         // Initial setup - hide all words
//         gsap.set('.tehran-word', { autoAlpha: 0, scale: 0.3 });
        
//         // Detect screen size for animation behavior
//         const isDesktop = window.innerWidth >= 769;
        
//         // Create master timeline
//         const masterTimeline = gsap.timeline({
//             paused: false
//         });

//         // Animation for each word with impact pulse effect - SUPER FAST
//         masterTimeline
//             // Word 1: "اینجا" - Quick entrance
//             .to('.tehran-word-1', {
//                 duration: 0.2,
//                 autoAlpha: 1,
//                 scale: 1,
//                 ease: 'back.out(1.5)',
//             })
//             .to('.tehran-word-1', {
//                 duration: 0.1,
//                 scale: 1.04,
//                 textShadow: '0 0 20px rgba(255, 255, 255, 0.8), 2px 2px 0 rgba(0, 0, 0, 0.3)',
//                 ease: 'power2.out',
//             })
//             .to('.tehran-word-1', {
//                 duration: 0.08,
//                 scale: 1,
//                 textShadow: '2px 2px 0 rgba(0, 0, 0, 0.3), 3px 3px 0 rgba(0, 0, 0, 0.2), 4px 4px 0 rgba(0, 0, 0, 0.1), 0 0 20px rgba(255, 255, 255, 0.4)',
//                 ease: 'power2.in',
//             })
            
//             // Word 2: "اتاق فرارهای" - Quick wiggle
//             .to('.tehran-word-2', {
//                 duration: 0.25,
//                 autoAlpha: 1,
//                 scale: 1,
//                 ease: 'back.out(1.5)',
//             }, '-=0.02')
//             .to('.tehran-word-2', {
//                 duration: 0.12,
//                 scale: 1.06,
//                 x: -2,
//                 rotation: -1.5,
//                 ease: 'power2.out',
//             })
//             .to('.tehran-word-2', {
//                 duration: 0.08,
//                 scale: 1,
//                 x: 0,
//                 rotation: 0,
//                 ease: 'elastic.out(1, 0.3)',
//             })
            
//             // Word 3: "پرطرفدار تهران" - Quick shake
//             .to('.tehran-word-3', {
//                 duration: 0.25,
//                 autoAlpha: 1,
//                 scale: 1,
//                 ease: 'back.out(1.5)',
//             }, '-=0.05')
//             .to('.tehran-word-3', {
//                 duration: 0.12,
//                 scale: 1.06,
//                 x: 2,
//                 rotation: 1.5,
//                 ease: 'power2.out',
//             })
//             .to('.tehran-word-3', {
//                 duration: 0.08,
//                 scale: 1,
//                 x: 0,
//                 rotation: 0,
//                 ease: 'elastic.out(1, 0.3)',
//             })
            
//             // Word 4: "انتظارتو میکشن!" - Final punch
//             .to('.tehran-word-4', {
//                 duration: 0.3,
//                 autoAlpha: 1,
//                 scale: 1,
//                 ease: 'back.out(2)',
//             }, '-=0.02')
//             .to('.tehran-word-4', {
//                 duration: 0.15,
//                 scale: 1.08,
//                 rotation: 2,
//                 textShadow: '0 0 30px rgba(255, 255, 255, 1), 3px 3px 0 rgba(0, 0, 0, 0.4)',
//                 ease: 'power3.out',
//             })
//             .to('.tehran-word-4', {
//                 duration: 0.12,
//                 scale: 1,
//                 rotation: 0,
//                 textShadow: '2px 2px 0 rgba(0, 0, 0, 0.3), 3px 3px 0 rgba(0, 0, 0, 0.2), 4px 4px 0 rgba(0, 0, 0, 0.1), 0 0 20px rgba(255, 255, 255, 0.4)',
//                 ease: 'elastic.out(1, 0.3)',
//             })
            
//             // Hold all words visible
//             .to('.tehran-word', {
//                 duration: 0.15,
//             });
        
//         // Store timeline globally for mobile coordination
//         mainTextTimeline = masterTimeline;
        
//         if (isDesktop) {
//             // Desktop: Animation runs once, then continuous pulse on highlighted words only
//             masterTimeline.eventCallback('onComplete', () => {
//                 gsap.to('.tehran-highlight-text', {
//                     scale: 1.03,
//                     duration: 1.2,
//                     repeat: -1,
//                     yoyo: true,
//                     ease: 'sine.inOut',
//                 });
//             });
//         } else {
//             // Mobile: Don't auto-run, will be controlled by typing animation
//             masterTimeline.pause();
//             masterTimeline.eventCallback('onComplete', () => {
//                 gsap.to('.tehran-highlight-text', {
//                     scale: 1.03,
//                     duration: 1.2,
//                     repeat: -1,
//                     yoyo: true,
//                     ease: 'sine.inOut',
//                 });
//             });
//         }
        
//         // Animate Code Badge with GSAP
//         animateCodeBadge();
//     }
    
//     function animateCodeBadge() {
//         const badge = document.querySelector('.tehran-code-badge');
//         const icon = document.querySelector('.tehran-tag-icon');
        
//         if (!badge || !icon) return;
        
//         // Glass badge glow pulse - no scale to keep text sharp
//         gsap.to(badge, {
//             boxShadow: '0 6px 25px rgba(0, 0, 0, 0.3), 0 0 40px rgba(255, 107, 53, 0.8), inset 0 1px 1px rgba(255, 255, 255, 0.6), inset 0 -1px 1px rgba(0, 0, 0, 0.1)',
//             borderColor: 'rgba(255, 255, 255, 0.85)',
//             duration: 1.8,
//             repeat: -1,
//             yoyo: true,
//             ease: 'sine.inOut',
//         });
        
//         // Icon subtle float
//         gsap.to(icon, {
//             y: -3,
//             duration: 1.2,
//             repeat: -1,
//             yoyo: true,
//             ease: 'sine.inOut',
//         });
//     }
// })();
</script>
<?php endif; ?>
<!-- end-tehran-banner -->
<!---------------------start-breadcrump------------------------------------------------------------------------------------>
<div class="flex justify-between items-center my-5 <?= !$is_tehran ? 'lg:mt-8' : '' ?>">

    <div class="flex items-center">
        <?php if (!empty($data['breadcrumb'])):
            $breadcrumb_count = count($data['breadcrumb']);
            foreach ($data['breadcrumb'] as $index => $breadcrumb_item):
                $is_last = ($index === $breadcrumb_count - 1);
                $is_hidden = ($index === $breadcrumb_count - 1);
        ?>
                <a href="<?php
				$bc_raw = $breadcrumb_item['url'] ?? '';
				if ( ! empty( $bc_raw ) && ! is_wp_error( $bc_raw ) && is_string( $bc_raw ) ) {
					echo esc_url( $bc_raw );
				} else {
					echo '#';
				}
				?>" class="text-sm font-bold text-[#9AA8B7] <?= $is_hidden ? 'max-lg:hidden' : '' ?>"><?= esc_html($breadcrumb_item['title']) ?></a>
                <?php if (!$is_last): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="9" viewBox="0 0 2 9" fill="none" class="mx-3 lg:mx-6 <?= $is_hidden ? 'max-lg:hidden' : '' ?>">
                        <path d="M1 0.25V8.75" stroke="#9AA8B7" />
                    </svg>
                <?php endif; ?>
        <?php endforeach;
        endif; ?>
    </div>

</div>
<!---------------------end-breadcrump-------------------------------------------------------------------------------------->
<!----------------------start-hero------------------------------------------------------------------------------------------>
<div id="hero-section" class="flex flex-col max-lg:w-screen max-lg:relative max-lg:left-1/2 max-lg:right-1/2 max-lg:-ml-[50vw] max-lg:-mr-[50vw] max-lg:bg-[#F1F5F9] max-lg:shadow-[inset_0_4px_4px_rgba(0,0,0,0.25)]">

    <div class="flex items-center gap-5 pt-8 pb-10 max-lg:px-7">
        <div class="relative overflow-hidden mx-auto rounded-[10px] lg:rounded-[24px] w-[114px] h-[140px] lg:w-[257px] lg:h-[314px] embla_fade shrink-0">
            <div class="embla__viewport relative">
                <?php if ($discount_percentage !== null): ?>
                    <div class="absolute top-2 left-2 lg:top-3 lg:left-3 z-10 bg-[#F21543] text-white rounded-full flex items-center justify-center text-2xs lg:text-base font-black w-6 h-6 lg:w-8.5 lg:h-8.5">
                        <?= $discount_percentage ?>%
                    </div>
                <?php endif; ?>
                <div class="embla__container">

                    <?php
                    // Main image first
                    ?>
                    <div class="embla__slide">
                        <img src="<?= esc_url($data['image']) ?>" alt="<?= esc_attr($data['title']) ?>" class="!w-[114px] !h-[140px] lg:!w-[257px] lg:!h-[314px] object-cover rounded-[10px] lg:rounded-[24px] shadow-md <?= ($discount_percentage !== null && $discount_percentage > 0) ? ' border-4 border-[#F21543] ': '' ?>">
                    </div>

                    <?php
                    // Gallery images
                    if (!empty($data['gallery'])) {
                        foreach ($data['gallery'] as $gallery_image_id) {
                            $gallery_image_url = wp_get_attachment_url($gallery_image_id);
                            if ($gallery_image_url) {
                    ?>
                                <div class="embla__slide">
                                    <img src="<?= esc_url($gallery_image_url) ?>" alt="<?= esc_attr($data['title']) ?>" class="!w-[114px] !h-[140px] lg:!w-[257px] lg:!h-[314px] object-cover rounded-[10px] lg:rounded-[24px] shadow-md <?= ($discount_percentage !== null && $discount_percentage > 0) ? ' border-4 border-[#F21543] ': '' ?>">
                                </div>
                    <?php
                            }
                        }
                    }
                    ?>

                </div>
            </div>

            <?php
            // محاسبه تعداد کل تصاویر (عکس اصلی + عکس‌های گالری)
            $total_images = 1; // عکس اصلی
            if (!empty($data['gallery'])) {
                $total_images += count($data['gallery']);
            }

            // نمایش دکمه‌ها فقط اگر بیش از یک عکس وجود داشته باشد
            if ($total_images > 1):
            ?>
                <div class="embla__button embla__button--prev hero-section-slide absolute top-1/2 left-2 -translate-y-1/2 z-20 bg-white/30 w-4 h-4 lg:w-8 lg:h-8 flex items-center justify-center cursor-pointer transition-all duration-300 hover:bg-white/50 hover:scale-110 rounded" id="prevBtn">
                    <svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.356413 4.97207L3.4499 8.27512C3.68087 8.48038 3.98652 8.5922 4.30239 8.587C4.61827 8.58181 4.91968 8.46 5.14307 8.24726C5.36646 8.03451 5.49436 7.74747 5.49982 7.44665C5.50527 7.14583 5.38786 6.85475 5.17232 6.63479L2.94005 4.1519L5.17232 1.95239C5.38785 1.73242 5.50527 1.44134 5.49982 1.14052C5.49436 0.839707 5.36646 0.552662 5.14307 0.339915C4.91968 0.127179 4.61827 0.00536975 4.30239 0.000173277C3.98652 -0.0050232 3.68087 0.106798 3.4499 0.31206L0.356413 3.33174C0.128191 3.54935 -1.68041e-07 3.84434 -1.81485e-07 4.1519C-1.94929e-07 4.45946 0.128191 4.75445 0.356413 4.97207Z" fill="white" />
                    </svg>
                </div>
                <div class="embla__button embla__button--next hero-section-slide absolute top-1/2 right-2 -translate-y-1/2 z-20 bg-white/30 w-4 h-4 lg:w-8 lg:h-8 flex items-center justify-center cursor-pointer transition-all duration-300 hover:bg-white/50 hover:scale-110 rounded-sm" id="nextBtn">
                    <svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.14359 4.97207L2.0501 8.27512C1.81913 8.48038 1.51348 8.5922 1.19761 8.587C0.881733 8.58181 0.580322 8.46 0.356928 8.24726C0.133544 8.03451 0.00563888 7.74747 0.00018233 7.44665C-0.00527422 7.14583 0.112144 6.85475 0.327678 6.63479L2.55995 4.1519L0.32768 1.95239C0.112146 1.73242 -0.00527244 1.44134 0.000184079 1.14052C0.0056406 0.839707 0.133544 0.552662 0.356928 0.339915C0.580323 0.127179 0.881733 0.00536975 1.19761 0.000173277C1.51348 -0.0050232 1.81913 0.106798 2.0501 0.31206L5.14359 3.33174C5.37181 3.54935 5.5 3.84434 5.5 4.1519C5.5 4.45946 5.37181 4.75445 5.14359 4.97207Z" fill="white" />
                    </svg>
                </div>
                <style>
                    .hero-section-slide {
                        display: flex !important;
                    }
                </style>
            <?php endif; ?>
        </div>
        <style>
            .embla__viewport {
                overflow: hidden;
                width: 100%;
                height: 100%;
            }

            .embla__container {
                display: flex;
                height: 100%;
                transition: transform 0.3s ease-out;
            }

            .embla__slide {
                flex: 0 0 100%;
                height: 100%;
            }

            .nav-btn.prev {
                left: 8px;
            }

            .nav-btn.next {
                right: 8px;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($total_images > 1): ?>
                    const emblaNode = document.querySelector('.embla');
                    const viewportNode = emblaNode.querySelector('.embla__viewport');
                    const prevBtn = document.getElementById('prevBtn');
                    const nextBtn = document.getElementById('nextBtn');

                    // Initialize Embla Carousel
                    const options = {
                        loop: false,
                        align: 'start',
                        containScroll: 'trimSnaps'
                    };

                    const embla = EmblaCarousel(viewportNode, options);

                    // دکمه‌های navigation
                    if (prevBtn) {
                        prevBtn.addEventListener('click', () => embla.scrollPrev());
                    }

                    if (nextBtn) {
                        nextBtn.addEventListener('click', () => embla.scrollNext());
                    }

                    // جلوگیری از drag روی تصاویر
                    const slides = emblaNode.querySelectorAll('.embla__slide');
                    slides.forEach(slide => {
                        const img = slide.querySelector('img');
                        if (img) {
                            img.addEventListener('dragstart', (e) => e.preventDefault());
                        }
                    });
                <?php endif; ?>
            });
        </script>


        <div class="flex flex-col w-full">
            <div class="flex max-lg:items-start lg:items-center justify-between mb-4 lg:mb-10 relative">
                <div class="flex items-center gap-3">
                <h1 class="flex flex-col w-full">
                        <p class="text-base font-bold text-[#62748E]"><?= esc_html($product_type) ?><span class="text-3xl inline-block font-black text-[#0F172B] pr-3 max-lg:hidden"><?= esc_html($data['title']) ?></span></p>
                        <p class="text-2xl font-black lg:hidden mt-6"><?= esc_html($data['title']) ?></p>
                    </h1>
                    <?php if ($product_publish_date > $one_month_ago): ?>
                        <div class="bg-[#F21543] text-white inline-flex px-1.5 py-1 items-center rounded-lg text-sm font-black leading-[18px] max-lg:hidden">
                            جدید
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-5">
                    <!-- <div class="flex items-center gap-3">
                        <p class="text-sm font-bold max-lg:hidden">افزودن به کالکشن</p>
                        <div class="py-[5px] px-2 bg-[#F1F5F9] max-lg:bg-white rounded-lg shadow-[0px_4px_4px_0px_rgba(0_0_0_0.25)]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="22" viewBox="0 0 17 22" fill="none">
                                <path d="M5.62691 18.3972L2.51001 20.7894C2.28771 20.9692 2.02221 21.083 1.74203 21.1186C1.46185 21.1542 1.17756 21.1103 0.919713 20.9916C0.661862 20.8729 0.440186 20.6838 0.278492 20.4447C0.116798 20.2057 0.021196 19.9256 0.00195312 19.6346V7.14137C0.0181248 6.68102 0.12193 6.22848 0.307438 5.8096C0.492946 5.39071 0.756521 5.01369 1.0831 4.70008C1.40969 4.38647 1.79288 4.14242 2.21079 3.98186C2.62869 3.8213 3.07312 3.74739 3.51869 3.76434H9.66161C10.5611 3.73136 11.4366 4.0684 12.0959 4.7015C12.7552 5.33461 13.1445 6.21208 13.1783 7.14137V19.6365C13.1596 19.9277 13.0642 20.2081 12.9026 20.4474C12.7409 20.6868 12.5191 20.876 12.2611 20.9947C12.003 21.1134 11.7185 21.1571 11.4382 21.1211C11.1578 21.0851 10.8923 20.9707 10.6703 20.7903L7.55339 18.3981C7.2736 18.1875 6.93634 18.074 6.59015 18.074C6.24396 18.074 5.9067 18.1866 5.62691 18.3972Z" fill="url(#paint0_linear_53025_22787)" />
                                <path d="M3.76367 1.81225C4.09403 1.49611 4.48166 1.25009 4.9044 1.08824C5.32715 0.926391 5.77672 0.851882 6.22744 0.868972H12.4415C13.3514 0.835724 14.237 1.17548 14.9039 1.81368C15.5709 2.45188 15.9647 3.33641 15.9989 4.27318V10.571V16.8688C15.9799 17.1624 15.8834 17.445 15.7199 17.6863C15.5564 17.9276 15.332 18.1183 15.071 18.238" stroke="#172F75" stroke-linecap="round" stroke-linejoin="round" />
                                <g filter="url(#filter0_d_53025_22787)">
                                    <path d="M2.84766 10.6562H5.66016V7.84375C5.66016 7.61997 5.74905 7.40536 5.90728 7.24713C6.06552 7.08889 6.28013 7 6.50391 7C6.72768 7 6.94229 7.08889 7.10053 7.24713C7.25876 7.40536 7.34766 7.61997 7.34766 7.84375V10.6562H10.1602C10.3839 10.6562 10.5985 10.7451 10.7568 10.9034C10.915 11.0616 11.0039 11.2762 11.0039 11.5C11.0039 11.7238 10.915 11.9384 10.7568 12.0966C10.5985 12.2549 10.3839 12.3438 10.1602 12.3438H7.34766V15.1563C7.34766 15.38 7.25876 15.5946 7.10053 15.7529C6.94229 15.9111 6.72768 16 6.50391 16C6.28013 16 6.06552 15.9111 5.90728 15.7529C5.74905 15.5946 5.66016 15.38 5.66016 15.1563V12.3438H2.84766C2.62388 12.3438 2.40927 12.2549 2.25103 12.0966C2.0928 11.9384 2.00391 11.7238 2.00391 11.5C2.00391 11.2762 2.0928 11.0616 2.25103 10.9034C2.40927 10.7451 2.62388 10.6562 2.84766 10.6562Z" fill="white" />
                                </g>
                                <defs>
                                    <filter id="filter0_d_53025_22787" x="2.00391" y="7" width="11" height="11" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                        <feOffset dx="1" dy="1" />
                                        <feGaussianBlur stdDeviation="0.5" />
                                        <feComposite in2="hardAlpha" operator="out" />
                                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.5 0" />
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_53025_22787" />
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_53025_22787" result="shape" />
                                    </filter>
                                    <linearGradient id="paint0_linear_53025_22787" x1="10.502" y1="4" x2="-3.64703" y2="32.7167" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#557BE9" />
                                        <stop offset="0.444496" stop-color="#1C398E" />
                                        <stop offset="1" stop-color="#081028" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div> -->
                    <button type="button" class="share-top-btn absolute left-0 flex items-center gap-3 cursor-pointer" data-title="<?= $product_type . ' ' . $data['title']; ?>">
                        <p class="text-sm font-bold max-lg:hidden text-nowrap">اشتراک گذاری</p>
                        <div class=" p-2 bg-[#F1F5F9] max-lg:bg-white rounded-lg shadow-[0px_4px_4px_0px_rgba(0_0_0_0.25)]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M9.27922 12.0174L5.96418 10.1856C5.53138 10.6544 4.96942 10.9805 4.35132 11.1214C3.73323 11.2624 3.08758 11.2117 2.49828 10.976C1.90899 10.7402 1.4033 10.3304 1.04693 9.7996C0.690566 9.26885 0.5 8.64175 0.5 7.99982C0.5 7.35788 0.690566 6.73079 1.04693 6.20003C1.4033 5.66927 1.90899 5.25939 2.49828 5.02366C3.08758 4.78793 3.73323 4.73725 4.35132 4.8782C4.96942 5.01915 5.53138 5.34521 5.96418 5.81402L9.28001 3.98219C9.09204 3.22703 9.18235 2.42834 9.534 1.73582C9.88566 1.0433 10.4745 0.504506 11.1902 0.220439C11.9059 -0.0636287 12.6993 -0.0734674 13.4216 0.192767C14.1439 0.459001 14.7456 0.983029 15.1139 1.66662C15.4822 2.35021 15.5918 3.14643 15.4221 3.90602C15.2524 4.66562 14.8151 5.33643 14.1922 5.79272C13.5693 6.24901 12.8036 6.45945 12.0385 6.38458C11.2734 6.30972 10.5616 5.95469 10.0363 5.38606L6.7205 7.21789C6.84803 7.7309 6.84803 8.26794 6.7205 8.78094L10.0363 10.6128C10.5618 10.0444 11.2738 9.68976 12.0389 9.61527C12.804 9.54078 13.5697 9.75159 14.1924 10.2082C14.815 10.6647 15.252 11.3357 15.4213 12.0954C15.5907 12.8551 15.4807 13.6512 15.1122 14.3346C14.7436 15.018 14.1417 15.5418 13.4193 15.8077C12.6968 16.0736 11.9035 16.0634 11.1879 15.7791C10.4724 15.4947 9.88374 14.9557 9.53236 14.2631C9.18097 13.5704 9.09097 12.7717 9.27922 12.0166M3.6581 9.59927C4.07687 9.59927 4.47848 9.43071 4.7746 9.13068C5.07071 8.83065 5.23706 8.42372 5.23706 7.99942C5.23706 7.57511 5.07071 7.16818 4.7746 6.86815C4.47848 6.56812 4.07687 6.39957 3.6581 6.39957C3.23933 6.39957 2.83771 6.56812 2.5416 6.86815C2.24549 7.16818 2.07913 7.57511 2.07913 7.99942C2.07913 8.42372 2.24549 8.83065 2.5416 9.13068C2.83771 9.43071 3.23933 9.59927 3.6581 9.59927ZM12.3424 4.79972C12.7612 4.79972 13.1628 4.63116 13.4589 4.33113C13.755 4.0311 13.9214 3.62417 13.9214 3.19987C13.9214 2.77556 13.755 2.36863 13.4589 2.0686C13.1628 1.76857 12.7612 1.60002 12.3424 1.60002C11.9237 1.60002 11.522 1.76857 11.2259 2.0686C10.9298 2.36863 10.7635 2.77556 10.7635 3.19987C10.7635 3.62417 10.9298 4.0311 11.2259 4.33113C11.522 4.63116 11.9237 4.79972 12.3424 4.79972ZM12.3424 14.3988C12.7612 14.3988 13.1628 14.2303 13.4589 13.9302C13.755 13.6302 13.9214 13.2233 13.9214 12.799C13.9214 12.3747 13.755 11.9677 13.4589 11.6677C13.1628 11.3677 12.7612 11.1991 12.3424 11.1991C11.9237 11.1991 11.522 11.3677 11.2259 11.6677C10.9298 11.9677 10.7635 12.3747 10.7635 12.799C10.7635 13.2233 10.9298 13.6302 11.2259 13.9302C11.522 14.2303 11.9237 14.3988 12.3424 14.3988Z" fill="#62748E" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <div class="p-7 bg-[#F1F5F9] rounded-2xl max-lg:hidden">
                <div class="bg-white flex items-center justify-around px-5 py-4 rounded-xl">
                    <div class="flex flex-col items-center gap-2">
                        <p class="text-sm font-bold text-[#889BAD]">لوکیشن</p>
                        <p class="text-sm font-bold"><?= esc_html($data['hood_name']) ?>.<?= esc_html($data['city_name']) ?></p>
                    </div>
                    <?php if ($product_type == 'اتاق فرار' && !empty($data['genres'])): ?>
                        <div class="w-[1px] h-[52px] bg-[#E4EBF0]"></div>
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-sm font-bold text-[#889BAD]">ژانر</p>
                            <p class="text-sm font-bold"><?php
                                                            $genre_names = array_map(function ($g) {
                                                                return $g['title'];
                                                            }, $data['genres']);
                                                            echo implode('.', $genre_names);
                                                            ?></p>
                        </div>
                    <?php endif; ?>
                    <div class="w-[1px] h-[52px] bg-[#E4EBF0]"></div>
                    <div class="flex flex-col items-center gap-2">
                        <p class="text-sm font-bold text-[#889BAD]">ظرفیت</p>
                        <p class="text-sm font-bold"><?= $data['number_min'] ?> تا <?= $data['number_max'] ?> نفر</p>
                    </div>
                    <div class="w-[1px] h-[52px] bg-[#E4EBF0]"></div>
                    <div class="flex flex-col items-center gap-2">
                        <p class="text-sm font-bold text-[#889BAD]">مدت سانس</p>
                        <p class="text-sm font-bold"><?= $data['duration'] ?>دقیقه</p>
                    </div>
                    <?php if ($product_type == 'اتاق فرار'): ?>
                        <div class="w-[1px] h-[52px] bg-[#E4EBF0]"></div>
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-sm font-bold text-[#889BAD]">میزان سختی</p>
                            <p class="text-sm font-bold">
                                <?php
                                $level = $data['level'];
                                if ($level == 1) echo 'خیلی سخت';
                                elseif ($level == 2) echo 'سخت';
                                elseif ($level == 3) echo 'متوسط';
                                elseif ($level == 4) echo 'آسان';
                                else echo 'نامشخص';
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <div class="w-[1px] h-[52px] bg-[#E4EBF0]"></div>
                    <div class="flex flex-col items-center gap-2">
                        <p class="text-sm font-bold text-[#889BAD]">مناسب سن</p>
                        <p class="text-sm font-bold"><?= $data['age'] ?>+</p>
                    </div>

                </div>

                <div class="flex items-center justify-between mt-6">
                    <div class="border border-[#E2E8F0] px-3 py-4 rounded-lg flex gap-x-15">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none" class="mx-0">
                                <path d="M7.04894 0.92705C7.3483 0.00573921 8.6517 0.00573969 8.95106 0.92705L10.0206 4.21885C10.1545 4.63087 10.5385 4.90983 10.9717 4.90983H14.4329C15.4016 4.90983 15.8044 6.14945 15.0207 6.71885L12.2205 8.75329C11.87 9.00793 11.7234 9.4593 11.8572 9.87132L12.9268 13.1631C13.2261 14.0844 12.1717 14.8506 11.388 14.2812L8.58778 12.2467C8.2373 11.9921 7.7627 11.9921 7.41221 12.2467L4.61204 14.2812C3.82833 14.8506 2.77385 14.0844 3.0732 13.1631L4.14277 9.87132C4.27665 9.4593 4.12999 9.00793 3.7795 8.75329L0.979333 6.71885C0.195619 6.14945 0.598395 4.90983 1.56712 4.90983H5.02832C5.46154 4.90983 5.8455 4.63087 5.97937 4.21885L7.04894 0.92705Z" fill="#EFC101" />
                            </svg>
                            <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">امتیاز</p>
                            <p class="text-lg font-extrabold animate-counter" data-target="<?= $data['comments']['rate'] ?>" data-decimals="2">0.0</p>
                        </div>

                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M7.99939 14C9.30801 13.9997 10.5807 13.5716 11.6234 12.7809C12.6661 11.9902 13.4218 10.8803 13.7751 9.62028C14.1285 8.36027 14.0603 7.01928 13.5808 5.80166C13.1014 4.58404 12.237 3.55656 11.1193 2.87579C10.0017 2.19503 8.6922 1.89831 7.39031 2.03087C6.08842 2.16342 4.86556 2.71797 3.90808 3.61C2.95061 4.50204 2.31102 5.68265 2.08679 6.97192C1.86256 8.26119 2.06598 9.58841 2.66606 10.7513L1.99939 14L5.24806 13.3333C6.07206 13.7593 7.00806 14 7.99939 14Z" stroke="#3F7FF5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M5 8H5.00667V8.00667H5V8ZM8 8H8.00667V8.00667H8V8ZM11 8H11.0067V8.00667H11V8Z" stroke="#3F7FF5" stroke-width="1.5" stroke-linejoin="round" />
                            </svg>
                            <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">نظرات</p>
                            <p class="text-lg font-extrabold animate-counter" data-target="<?= $data['comments']['comments_count'] ?>" data-decimals="0">0</p>
                        </div>
                        <?php if ( $satisfaction_positive_count_display !== null ): ?>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M7.70801 1.93115C7.86868 1.871 8.04409 1.86168 8.20996 1.90479L8.30859 1.93701L12.959 3.68115C13.1068 3.73658 13.2358 3.83355 13.3311 3.95947C13.4024 4.05379 13.4533 4.16149 13.4795 4.27588L13.4971 4.39307L13.5 4.48291V8.0376C13.4998 9.02764 13.232 9.99939 12.7256 10.8501C12.2191 11.7008 11.4924 12.3995 10.6221 12.8716L10.46 12.9565L8.22363 14.0737C8.16217 14.1045 8.095 14.1228 8.02637 14.1265C7.95898 14.13 7.89147 14.1194 7.82812 14.0962L7.76855 14.0708L5.54004 12.9565C4.65431 12.5137 3.90469 11.839 3.37012 11.0054C2.90245 10.276 2.61502 9.44798 2.52832 8.58936L2.50293 8.22021L2.5 8.02881V4.4624L2.50879 4.34521C2.52534 4.22903 2.56569 4.11702 2.62891 4.01709C2.71335 3.88375 2.83389 3.77598 2.97656 3.7085L3.0498 3.67822L7.70801 1.93115Z" stroke="#02C96F" />
                                    <path d="M7.13834 9.5C7.3096 9.5 7.47385 9.43967 7.59502 9.33228L9.21063 7.89852L10.8262 6.46475C10.9405 6.35606 11.0028 6.21222 10.9999 6.06358C10.997 5.91493 10.9292 5.77309 10.8107 5.66797C10.6923 5.56284 10.5324 5.50265 10.3649 5.50009C10.1974 5.49752 10.0354 5.55277 9.91288 5.6542L7.13834 8.11645L6.08712 7.18355C5.96464 7.08212 5.80256 7.02686 5.63506 7.02943C5.46756 7.032 5.30773 7.09219 5.18927 7.19731C5.07081 7.30244 5.00299 7.44428 5.0001 7.59292C4.9972 7.74157 5.05947 7.8854 5.17376 7.9941L6.68166 9.33228C6.80283 9.43967 6.96709 9.5 7.13834 9.5Z" fill="#02C96F" />
                                </svg>
                                <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">رضایت</p>
                                <p class="text-lg font-extrabold">%<span class="animate-counter" data-target="<?= $satisfaction_positive_count_display ?>" data-decimals="2">0.00</span></p>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M9.33203 7.33329C9.33203 7.15648 9.40227 6.98691 9.52729 6.86189C9.65232 6.73686 9.82189 6.66663 9.9987 6.66663C10.1755 6.66663 10.3451 6.73686 10.4701 6.86189C10.5951 6.98691 10.6654 7.15648 10.6654 7.33329V8.66663C10.6654 8.84344 10.5951 9.01301 10.4701 9.13803C10.3451 9.26305 10.1755 9.33329 9.9987 9.33329C9.82189 9.33329 9.65232 9.26305 9.52729 9.13803C9.40227 9.01301 9.33203 8.84344 9.33203 8.66663V7.33329Z" stroke="#2B7FFF" stroke-width="1.125" />
                                <path d="M9.3421 11.3333L9.8421 11.3346V11.3333H9.3421ZM9.3421 4.66663H9.8421V4.66529L9.3421 4.66663ZM9.8381 12.67L9.8421 11.3346L8.8421 11.332L8.8381 12.6673L9.8381 12.67ZM10.0101 11.1666C10.1041 11.1666 10.1788 11.2426 10.1788 11.3333H11.1788C11.1788 10.688 10.6541 10.1666 10.0101 10.1666V11.1666ZM10.0101 10.1666C9.3661 10.1666 8.8421 10.688 8.8421 11.3333H9.8421C9.8421 11.2426 9.9161 11.1666 10.0101 11.1666V10.1666ZM6.66277 3.16663H9.00277V2.16663H6.66277V3.16663ZM8.67144 12.8333H6.66277V13.8333H8.67144V12.8333ZM6.66277 12.8333C5.38877 12.8333 4.48277 12.832 3.7961 12.74C3.12277 12.65 2.73544 12.4806 2.4521 12.198L1.7461 12.9066C2.2461 13.4053 2.88077 13.6266 3.66344 13.732C4.43277 13.8353 5.41677 13.834 6.66344 13.834L6.66277 12.8333ZM6.66277 2.16663C5.4161 2.16663 4.4321 2.16529 3.66277 2.26863C2.8801 2.37396 2.2461 2.59529 1.7461 3.09396L2.45277 3.80196C2.73544 3.51929 3.12344 3.34996 3.7961 3.25996C4.48277 3.16796 5.38877 3.16663 6.66277 3.16663V2.16663ZM1.7241 7.27263C1.98144 7.41596 2.15277 7.68863 2.15277 7.99996H3.15277C3.15272 7.67364 3.06557 7.35325 2.90033 7.07187C2.73508 6.79048 2.49773 6.5583 2.21277 6.39929L1.7241 7.27263ZM1.83944 6.01329C1.89144 4.77596 2.0601 4.19263 2.45277 3.80196L1.7461 3.09396C1.05944 3.77929 0.892771 4.71996 0.839438 5.97196L1.83944 6.01329ZM2.15277 7.99996C2.15277 8.31129 1.98144 8.58396 1.7241 8.72796L2.2121 9.60129C2.49728 9.44232 2.73484 9.21007 2.90021 8.92855C3.06558 8.64703 3.15277 8.32646 3.15277 7.99996H2.15277ZM0.840104 10.028C0.893438 11.2786 1.0601 12.2213 1.7461 12.906L2.45277 12.198C2.0601 11.8073 1.89144 11.2233 1.83944 9.98596L0.840104 10.028ZM13.8461 7.99996C13.8461 7.68863 14.0174 7.41596 14.2748 7.27263L13.7868 6.39929C13.5017 6.55821 13.2642 6.79036 13.0988 7.07175C12.9335 7.35314 12.8462 7.67358 12.8461 7.99996H13.8461ZM15.1588 5.97196C15.1054 4.72129 14.9388 3.77863 14.2528 3.09396L13.5461 3.80196C13.9381 4.19263 14.1074 4.77663 14.1594 6.01396L15.1588 5.97196ZM14.2748 8.72796C14.1449 8.65583 14.0367 8.55031 13.9613 8.42231C13.886 8.29431 13.8462 8.1485 13.8461 7.99996H12.8461C12.8461 8.68929 13.2268 9.28796 13.7868 9.60063L14.2748 8.72796ZM14.1594 9.98596C14.1074 11.2233 13.9388 11.8073 13.5461 12.198L14.2528 12.906C14.9394 12.2213 15.1061 11.2793 15.1588 10.028L14.1594 9.98596ZM13.7868 9.60063C13.9761 9.70663 14.1001 9.77596 14.1834 9.82863C14.2248 9.85463 14.2434 9.86863 14.2488 9.87263C14.2581 9.88063 14.2241 9.85663 14.1921 9.79929L15.0654 9.31063C15.0193 9.23062 14.9584 9.16005 14.8861 9.10263C14.8323 9.05901 14.7758 9.01892 14.7168 8.98263C14.6054 8.91196 14.4528 8.82729 14.2748 8.72729L13.7868 9.60063ZM15.1588 10.028C15.1634 9.91396 15.1688 9.79596 15.1648 9.69729C15.162 9.56238 15.1278 9.42997 15.0648 9.31063L14.1921 9.79863C14.1588 9.73863 14.1634 9.6973 14.1654 9.7413C14.1663 9.75907 14.1663 9.78885 14.1654 9.83063L14.1594 9.98596L15.1588 10.028ZM14.2748 7.27263C14.4528 7.17263 14.6054 7.08796 14.7168 7.01729C14.7753 6.98025 14.8318 6.9402 14.8861 6.89729C14.9582 6.83981 15.0188 6.76924 15.0648 6.68929L14.1921 6.20129C14.2241 6.14329 14.2581 6.11929 14.2481 6.12729C14.2274 6.14321 14.2058 6.15791 14.1834 6.17129C14.1001 6.22463 13.9768 6.29329 13.7868 6.39929L14.2748 7.27263ZM14.1594 6.01396L14.1654 6.16863C14.1663 6.21041 14.1663 6.24018 14.1654 6.25796C14.1634 6.30196 14.1588 6.26063 14.1921 6.20063L15.0654 6.68863C15.1282 6.56923 15.1622 6.43682 15.1648 6.30196C15.1671 6.19169 15.1649 6.08138 15.1581 5.97129L14.1594 6.01396ZM1.7241 8.72729C1.5461 8.82729 1.39344 8.91129 1.2821 8.98196C1.22312 9.01825 1.16656 9.05834 1.11277 9.10196C1.04067 9.15944 0.980053 9.23068 0.934104 9.31063L1.80677 9.79796C1.77477 9.85596 1.74077 9.87996 1.7501 9.87196C1.7561 9.86729 1.7741 9.85396 1.81544 9.82796C1.89877 9.77463 2.0221 9.70596 2.2121 9.59996L1.7241 8.72729ZM1.83944 9.98529L1.83344 9.82996C1.83244 9.80019 1.83244 9.7704 1.83344 9.74063C1.83544 9.69663 1.8401 9.73796 1.80677 9.79796L0.933438 9.30996C0.870687 9.42936 0.836674 9.56177 0.834104 9.69663C0.830104 9.79529 0.835438 9.9133 0.840771 10.0273L1.83944 9.98529ZM2.2121 6.39863C2.02277 6.29263 1.89877 6.22329 1.81544 6.17063C1.79281 6.15726 1.771 6.14257 1.7501 6.12663C1.74077 6.11863 1.77477 6.14263 1.80677 6.19996L0.933438 6.68863C0.989438 6.78729 1.06344 6.85529 1.11277 6.89663C1.1661 6.94063 1.22544 6.98063 1.2821 7.01663C1.39344 7.08729 1.5461 7.17196 1.7241 7.27196L2.2121 6.39863ZM0.839438 5.97196C0.834771 6.08596 0.829438 6.20396 0.833438 6.30263C0.838104 6.40263 0.853438 6.54729 0.933438 6.68929L1.80677 6.20129C1.8401 6.26129 1.83477 6.30263 1.83277 6.25863C1.83177 6.22886 1.83177 6.19906 1.83277 6.16929L1.83877 6.01396L0.839438 5.97196ZM9.84144 4.66529L9.8361 2.99729L8.83677 3.00063L8.84144 4.66796L9.84144 4.66529ZM10.0094 4.83329C9.98744 4.83347 9.96562 4.82929 9.94525 4.82099C9.92487 4.8127 9.90634 4.80045 9.89072 4.78495C9.87511 4.76946 9.86271 4.75103 9.85425 4.73072C9.84579 4.71041 9.84144 4.68863 9.84144 4.66663H8.84144C8.84144 5.31196 9.36544 5.83329 10.0094 5.83329V4.83329ZM10.1794 4.66663C10.1794 4.75729 10.1048 4.83329 10.0108 4.83329V5.83329C10.6548 5.83329 11.1794 5.31196 11.1794 4.66663H10.1794ZM10.1794 3.01063V4.66663H11.1794V3.01063H10.1794ZM11.0061 3.17929C12.4648 3.21663 13.1188 3.37396 13.5474 3.80196L14.2541 3.09396C13.5121 2.35396 12.4628 2.21663 11.0314 2.17929L11.0061 3.17929ZM11.1794 3.01063C11.1794 3.10396 11.1021 3.18129 11.0061 3.17929L11.0314 2.17929C10.9205 2.17654 10.8102 2.196 10.7069 2.23654C10.6037 2.27709 10.5096 2.33788 10.4302 2.41536C10.3508 2.49283 10.2877 2.58541 10.2446 2.68764C10.2016 2.78988 10.1794 2.89969 10.1794 3.01063H11.1794ZM9.00277 3.16663C8.95868 3.16645 8.91713 3.14881 8.88602 3.11758C8.85491 3.08634 8.83744 3.04405 8.83744 2.99996L9.83744 2.99729C9.83673 2.77674 9.74862 2.56547 9.59242 2.40976C9.43621 2.25406 9.22466 2.16663 9.0041 2.16663L9.00277 3.16663ZM11.3761 13.8093C12.6261 13.756 13.5688 13.5893 14.2534 12.906L13.5461 12.198C13.1554 12.5886 12.5708 12.758 11.3348 12.81L11.3761 13.8093ZM10.1794 11.3333V12.6513H11.1794V11.3333H10.1794ZM8.83877 12.6666C8.83877 12.7473 8.83877 12.8093 8.83677 12.862C8.83544 12.9153 8.83277 12.946 8.8301 12.9653C8.82744 12.9846 8.82677 12.978 8.8341 12.9586C8.84703 12.9302 8.86486 12.9042 8.88677 12.882L9.59277 13.59C9.72125 13.4571 9.80153 13.2851 9.82077 13.1013C9.83877 12.97 9.83744 12.8126 9.8381 12.67L8.83877 12.6666ZM8.67144 13.8333C8.8141 13.8333 8.9721 13.8346 9.10344 13.8166C9.24944 13.7966 9.43544 13.7466 9.59277 13.59L8.8861 12.882C8.90883 12.8602 8.93524 12.8426 8.9641 12.83C8.98277 12.822 8.98944 12.8233 8.97077 12.8253C8.9362 12.8289 8.90151 12.8311 8.86677 12.832C8.81344 12.8333 8.75144 12.8333 8.67144 12.8333V13.8333ZM11.3348 12.81C11.2548 12.8133 11.1928 12.816 11.1401 12.8166C11.0874 12.8173 11.0568 12.8166 11.0388 12.8146C11.0208 12.8126 11.0288 12.8113 11.0488 12.8193C11.0734 12.8286 11.1021 12.846 11.1281 12.87L10.4361 13.592C10.5988 13.748 10.7888 13.7946 10.9408 13.8093C11.0754 13.8226 11.2334 13.8153 11.3761 13.8093L11.3348 12.81ZM10.1794 12.6513C10.1794 12.7966 10.1781 12.958 10.1968 13.0933C10.2174 13.244 10.2714 13.434 10.4361 13.592L11.1281 12.87C11.1548 12.8953 11.1721 12.924 11.1828 12.948C11.1908 12.968 11.1894 12.9746 11.1874 12.956C11.1838 12.9207 11.1816 12.8854 11.1808 12.85C11.1794 12.7966 11.1794 12.7333 11.1794 12.6513H10.1794Z" fill="#2B7FFF" />
                            </svg>
                            <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">خرید</p>
                            <p class="text-lg font-extrabold"><span class="ml-0.5">+</span> <span class="animate-counter" data-target="<?= $tickets_sold ?>" data-decimals="0">0</span></p>
                        </div>

                    </div>

                    <!-- <div class="flex items-center px-3 py-4 border border-[#E2E8F0] rounded-lg gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4.4448 5.09124C4.37355 5.85124 4.32355 7.19624 4.7723 7.76874C4.7723 7.76874 4.56105 6.29124 6.4548 4.43749C7.2173 3.69124 7.39355 2.67624 7.1273 1.91499C6.97605 1.48374 6.6998 1.12749 6.4598 0.878737C6.3198 0.732487 6.4273 0.491237 6.63105 0.499987C7.86355 0.554987 9.86105 0.897487 10.7098 3.02749C11.0823 3.96249 11.1098 4.92874 10.9323 5.91124C10.8198 6.53874 10.4198 7.93374 11.3323 8.10499C11.9836 8.22749 12.2986 7.70999 12.4398 7.33749C12.4986 7.18249 12.7023 7.14374 12.8123 7.26749C13.9123 8.51874 14.0061 9.99249 13.7786 11.2612C13.3386 13.7137 10.8548 15.4987 8.3873 15.4987C5.3048 15.4987 2.85105 13.735 2.2148 10.5425C1.95855 9.25374 2.08855 6.70374 4.07605 4.90374C4.22355 4.76874 4.4648 4.88874 4.4448 5.09124Z" fill="url(#paint0_radial_53025_22427)" />
                            <path d="M9.51384 9.67751C8.37759 8.21501 8.88634 6.54626 9.16509 5.88126C9.20259 5.79376 9.10259 5.71126 9.02384 5.76501C8.53509 6.09751 7.53384 6.88001 7.06759 7.98126C6.43634 9.47001 6.48134 10.1988 6.85509 11.0888C7.08009 11.625 6.81884 11.7388 6.68759 11.7588C6.56009 11.7788 6.44259 11.6938 6.34884 11.605C6.07919 11.346 5.887 11.0171 5.79384 10.655C5.77384 10.5775 5.67259 10.5563 5.62634 10.62C5.27634 11.1038 5.09509 11.88 5.08634 12.4288C5.05884 14.125 6.46009 15.5 8.15509 15.5C10.2913 15.5 11.8476 13.1375 10.6201 11.1625C10.2638 10.5875 9.92884 10.2113 9.51384 9.67751Z" fill="url(#paint1_radial_53025_22427)" />
                            <defs>
                                <radialGradient id="paint0_radial_53025_22427" cx="0" cy="0" r="1" gradientTransform="matrix(-8.82337 -0.0382934 -0.0629107 14.4774 7.77677 15.5376)" gradientUnits="userSpaceOnUse">
                                    <stop offset="0.314" stop-color="#FF9800" />
                                    <stop offset="0.662" stop-color="#FF6D00" />
                                    <stop offset="0.972" stop-color="#F44336" />
                                </radialGradient>
                                <radialGradient id="paint1_radial_53025_22427" cx="0" cy="0" r="1" gradientTransform="matrix(-0.0932483 9.23158 6.94746 0.070167 8.27258 6.75731)" gradientUnits="userSpaceOnUse">
                                    <stop offset="0.214" stop-color="#FFF176" />
                                    <stop offset="0.328" stop-color="#FFF27D" />
                                    <stop offset="0.487" stop-color="#FFF48F" />
                                    <stop offset="0.672" stop-color="#FFF7AD" />
                                    <stop offset="0.793" stop-color="#FFF9C4" />
                                    <stop offset="0.822" stop-color="#FFF8BD" stop-opacity="0.804" />
                                    <stop offset="0.863" stop-color="#FFF6AB" stop-opacity="0.529" />
                                    <stop offset="0.91" stop-color="#FFF38D" stop-opacity="0.209" />
                                    <stop offset="0.941" stop-color="#FFF176" stop-opacity="0" />
                                </radialGradient>
                            </defs>
                        </svg>
                        <p class="text-sm font-bold">بیشترین فروش در 30 روز گذشته</p>
                    </div> -->



                </div>
            </div>

            <div class="flex justify-between lg:hidden">
                <?php if ($data['active']) { ?>
                    <p class="text-base font-black"><span class="text-sm font-bold text-[#62748E] ml-2">از</span><?php echo number_format($data['price']); ?><span class="text-sm font-bold 
                        text-[#62748E] mr-2">تومان</span></p>

                    <button class="open-sessions bg-[#02C96F] text-white w-[70px] h-[28px] rounded-md flex items-center justify-center">رزرو</button>
                <?php } else { ?>
                    <div class="flex flex-col">
                        <span class="mt-1 inline-flex items-center justify-center px-4 py-1 text-xs font-extrabold text-white rounded-md" style="background-color: <?= $sale_status_color ?: '#F21543' ?>;">
                            <?= $sale_status_title ?: 'رزرو غیرفعال' ?>
                        </span>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>

    <div class="lg:hidden w-full h-[40px] bg-white rounded-tr-[80px] rounded-tl-[80px] absolute bottom-[-25px] shadow-[0px_-10px_10px_0px_rgba(0,0,0,0.10)]"></div>
</div>
<!----------------------end-hero-------------------------------------------------------------------------------------------->

<!---------------------------start-box-information-------------------------------------------------------------------------->
<div class="border border-[#E2E8F0] px-3 py-4 rounded-lg flex justify-between mt-2.5 mb-5 relative lg:hidden">
    <div class="flex max-lg:flex-col items-center">
        <div class="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none" class="mx-0">
                <path d="M7.04894 0.92705C7.3483 0.00573921 8.6517 0.00573969 8.95106 0.92705L10.0206 4.21885C10.1545 4.63087 10.5385 4.90983 10.9717 4.90983H14.4329C15.4016 4.90983 15.8044 6.14945 15.0207 6.71885L12.2205 8.75329C11.87 9.00793 11.7234 9.4593 11.8572 9.87132L12.9268 13.1631C13.2261 14.0844 12.1717 14.8506 11.388 14.2812L8.58778 12.2467C8.2373 11.9921 7.7627 11.9921 7.41221 12.2467L4.61204 14.2812C3.82833 14.8506 2.77385 14.0844 3.0732 13.1631L4.14277 9.87132C4.27665 9.4593 4.12999 9.00793 3.7795 8.75329L0.979333 6.71885C0.195619 6.14945 0.598395 4.90983 1.56712 4.90983H5.02832C5.46154 4.90983 5.8455 4.63087 5.97937 4.21885L7.04894 0.92705Z" fill="#EFC101" />
            </svg>
            <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">امتیاز</p>
        </div>
        <p class="text-lg font-extrabold animate-counter" data-target="<?= $data['comments']['rate'] ?>" data-decimals="2">0.0</p>
    </div>

    <div class="h-[44px] w-[1px] bg-[#F1F5F9]"></div>

    <div class="flex max-lg:flex-col items-center">
        <div class="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M7.99939 14C9.30801 13.9997 10.5807 13.5716 11.6234 12.7809C12.6661 11.9902 13.4218 10.8803 13.7751 9.62028C14.1285 8.36027 14.0603 7.01928 13.5808 5.80166C13.1014 4.58404 12.237 3.55656 11.1193 2.87579C10.0017 2.19503 8.6922 1.89831 7.39031 2.03087C6.08842 2.16342 4.86556 2.71797 3.90808 3.61C2.95061 4.50204 2.31102 5.68265 2.08679 6.97192C1.86256 8.26119 2.06598 9.58841 2.66606 10.7513L1.99939 14L5.24806 13.3333C6.07206 13.7593 7.00806 14 7.99939 14Z" stroke="#3F7FF5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5 8H5.00667V8.00667H5V8ZM8 8H8.00667V8.00667H8V8ZM11 8H11.0067V8.00667H11V8Z" stroke="#3F7FF5" stroke-width="1.5" stroke-linejoin="round" />
            </svg>
            <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">نظرات</p>
        </div>
        <p class="text-lg font-extrabold animate-counter" data-target="<?= $data['comments']['comments_count'] ?>" data-decimals="0">0</p>
    </div>

    <?php if ( $satisfaction_positive_count_display !== null ): ?>
        <div class="h-[44px] w-[1px] bg-[#F1F5F9]"></div>
        <div class="flex max-lg:flex-col items-center">
            <div class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M7.70801 1.93115C7.86868 1.871 8.04409 1.86168 8.20996 1.90479L8.30859 1.93701L12.959 3.68115C13.1068 3.73658 13.2358 3.83355 13.3311 3.95947C13.4024 4.05379 13.4533 4.16149 13.4795 4.27588L13.4971 4.39307L13.5 4.48291V8.0376C13.4998 9.02764 13.232 9.99939 12.7256 10.8501C12.2191 11.7008 11.4924 12.3995 10.6221 12.8716L10.46 12.9565L8.22363 14.0737C8.16217 14.1045 8.095 14.1228 8.02637 14.1265C7.95898 14.13 7.89147 14.1194 7.82812 14.0962L7.76855 14.0708L5.54004 12.9565C4.65431 12.5137 3.90469 11.839 3.37012 11.0054C2.90245 10.276 2.61502 9.44798 2.52832 8.58936L2.50293 8.22021L2.5 8.02881V4.4624L2.50879 4.34521C2.52534 4.22903 2.56569 4.11702 2.62891 4.01709C2.71335 3.88375 2.83389 3.77598 2.97656 3.7085L3.0498 3.67822L7.70801 1.93115Z" stroke="#02C96F" />
                    <path d="M7.13834 9.5C7.3096 9.5 7.47385 9.43967 7.59502 9.33228L9.21063 7.89852L10.8262 6.46475C10.9405 6.35606 11.0028 6.21222 10.9999 6.06358C10.997 5.91493 10.9292 5.77309 10.8107 5.66797C10.6923 5.56284 10.5324 5.50265 10.3649 5.50009C10.1974 5.49752 10.0354 5.55277 9.91288 5.6542L7.13834 8.11645L6.08712 7.18355C5.96464 7.08212 5.80256 7.02686 5.63506 7.02943C5.46756 7.032 5.30773 7.09219 5.18927 7.19731C5.07081 7.30244 5.00299 7.44428 5.0001 7.59292C4.9972 7.74157 5.05947 7.8854 5.17376 7.9941L6.68166 9.33228C6.80283 9.43967 6.96709 9.5 7.13834 9.5Z" fill="#02C96F" />
                </svg>
                <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">رضایت</p>
            </div>
            <p class="text-lg font-extrabold"><span class="animate-counter" data-target="<?= $satisfaction_positive_count_display ?>" data-decimals="2">0.00</span>%</p>
        </div>
    <?php endif; ?>

    <div class="h-[44px] w-[1px] bg-[#F1F5F9]"></div>

    <div class="flex max-lg:flex-col items-center">
        <div class="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M9.33203 7.33329C9.33203 7.15648 9.40227 6.98691 9.52729 6.86189C9.65232 6.73686 9.82189 6.66663 9.9987 6.66663C10.1755 6.66663 10.3451 6.73686 10.4701 6.86189C10.5951 6.98691 10.6654 7.15648 10.6654 7.33329V8.66663C10.6654 8.84344 10.5951 9.01301 10.4701 9.13803C10.3451 9.26305 10.1755 9.33329 9.9987 9.33329C9.82189 9.33329 9.65232 9.26305 9.52729 9.13803C9.40227 9.01301 9.33203 8.84344 9.33203 8.66663V7.33329Z" stroke="#2B7FFF" stroke-width="1.125" />
                <path d="M9.3421 11.3333L9.8421 11.3346V11.3333H9.3421ZM9.3421 4.66663H9.8421V4.66529L9.3421 4.66663ZM9.8381 12.67L9.8421 11.3346L8.8421 11.332L8.8381 12.6673L9.8381 12.67ZM10.0101 11.1666C10.1041 11.1666 10.1788 11.2426 10.1788 11.3333H11.1788C11.1788 10.688 10.6541 10.1666 10.0101 10.1666V11.1666ZM10.0101 10.1666C9.3661 10.1666 8.8421 10.688 8.8421 11.3333H9.8421C9.8421 11.2426 9.9161 11.1666 10.0101 11.1666V10.1666ZM6.66277 3.16663H9.00277V2.16663H6.66277V3.16663ZM8.67144 12.8333H6.66277V13.8333H8.67144V12.8333ZM6.66277 12.8333C5.38877 12.8333 4.48277 12.832 3.7961 12.74C3.12277 12.65 2.73544 12.4806 2.4521 12.198L1.7461 12.9066C2.2461 13.4053 2.88077 13.6266 3.66344 13.732C4.43277 13.8353 5.41677 13.834 6.66344 13.834L6.66277 12.8333ZM6.66277 2.16663C5.4161 2.16663 4.4321 2.16529 3.66277 2.26863C2.8801 2.37396 2.2461 2.59529 1.7461 3.09396L2.45277 3.80196C2.73544 3.51929 3.12344 3.34996 3.7961 3.25996C4.48277 3.16796 5.38877 3.16663 6.66277 3.16663V2.16663ZM1.7241 7.27263C1.98144 7.41596 2.15277 7.68863 2.15277 7.99996H3.15277C3.15272 7.67364 3.06557 7.35325 2.90033 7.07187C2.73508 6.79048 2.49773 6.5583 2.21277 6.39929L1.7241 7.27263ZM1.83944 6.01329C1.89144 4.77596 2.0601 4.19263 2.45277 3.80196L1.7461 3.09396C1.05944 3.77929 0.892771 4.71996 0.839438 5.97196L1.83944 6.01329ZM2.15277 7.99996C2.15277 8.31129 1.98144 8.58396 1.7241 8.72796L2.2121 9.60129C2.49728 9.44232 2.73484 9.21007 2.90021 8.92855C3.06558 8.64703 3.15277 8.32646 3.15277 7.99996H2.15277ZM0.840104 10.028C0.893438 11.2786 1.0601 12.2213 1.7461 12.906L2.45277 12.198C2.0601 11.8073 1.89144 11.2233 1.83944 9.98596L0.840104 10.028ZM13.8461 7.99996C13.8461 7.68863 14.0174 7.41596 14.2748 7.27263L13.7868 6.39929C13.5017 6.55821 13.2642 6.79036 13.0988 7.07175C12.9335 7.35314 12.8462 7.67358 12.8461 7.99996H13.8461ZM15.1588 5.97196C15.1054 4.72129 14.9388 3.77863 14.2528 3.09396L13.5461 3.80196C13.9381 4.19263 14.1074 4.77663 14.1594 6.01396L15.1588 5.97196ZM14.2748 8.72796C14.1449 8.65583 14.0367 8.55031 13.9613 8.42231C13.886 8.29431 13.8462 8.1485 13.8461 7.99996H12.8461C12.8461 8.68929 13.2268 9.28796 13.7868 9.60063L14.2748 8.72796ZM14.1594 9.98596C14.1074 11.2233 13.9388 11.8073 13.5461 12.198L14.2528 12.906C14.9394 12.2213 15.1061 11.2793 15.1588 10.028L14.1594 9.98596ZM13.7868 9.60063C13.9761 9.70663 14.1001 9.77596 14.1834 9.82863C14.2248 9.85463 14.2434 9.86863 14.2488 9.87263C14.2581 9.88063 14.2241 9.85663 14.1921 9.79929L15.0654 9.31063C15.0193 9.23062 14.9584 9.16005 14.8861 9.10263C14.8323 9.05901 14.7758 9.01892 14.7168 8.98263C14.6054 8.91196 14.4528 8.82729 14.2748 8.72729L13.7868 9.60063ZM15.1588 10.028C15.1634 9.91396 15.1688 9.79596 15.1648 9.69729C15.162 9.56238 15.1278 9.42997 15.0648 9.31063L14.1921 9.79863C14.1588 9.73863 14.1634 9.6973 14.1654 9.7413C14.1663 9.75907 14.1663 9.78885 14.1654 9.83063L14.1594 9.98596L15.1588 10.028ZM14.2748 7.27263C14.4528 7.17263 14.6054 7.08796 14.7168 7.01729C14.7753 6.98025 14.8318 6.9402 14.8861 6.89729C14.9582 6.83981 15.0188 6.76924 15.0648 6.68929L14.1921 6.20129C14.2241 6.14329 14.2581 6.11929 14.2481 6.12729C14.2274 6.14321 14.2058 6.15791 14.1834 6.17129C14.1001 6.22463 13.9768 6.29329 13.7868 6.39929L14.2748 7.27263ZM14.1594 6.01396L14.1654 6.16863C14.1663 6.21041 14.1663 6.24018 14.1654 6.25796C14.1634 6.30196 14.1588 6.26063 14.1921 6.20063L15.0654 6.68863C15.1282 6.56923 15.1622 6.43682 15.1648 6.30196C15.1671 6.19169 15.1649 6.08138 15.1581 5.97129L14.1594 6.01396ZM1.7241 8.72729C1.5461 8.82729 1.39344 8.91129 1.2821 8.98196C1.22312 9.01825 1.16656 9.05834 1.11277 9.10196C1.04067 9.15944 0.980053 9.23068 0.934104 9.31063L1.80677 9.79796C1.77477 9.85596 1.74077 9.87996 1.7501 9.87196C1.7561 9.86729 1.7741 9.85396 1.81544 9.82796C1.89877 9.77463 2.0221 9.70596 2.2121 9.59996L1.7241 8.72729ZM1.83944 9.98529L1.83344 9.82996C1.83244 9.80019 1.83244 9.7704 1.83344 9.74063C1.83544 9.69663 1.8401 9.73796 1.80677 9.79796L0.933438 9.30996C0.870687 9.42936 0.836674 9.56177 0.834104 9.69663C0.830104 9.79529 0.835438 9.9133 0.840771 10.0273L1.83944 9.98529ZM2.2121 6.39863C2.02277 6.29263 1.89877 6.22329 1.81544 6.17063C1.79281 6.15726 1.771 6.14257 1.7501 6.12663C1.74077 6.11863 1.77477 6.14263 1.80677 6.19996L0.933438 6.68863C0.989438 6.78729 1.06344 6.85529 1.11277 6.89663C1.1661 6.94063 1.22544 6.98063 1.2821 7.01663C1.39344 7.08729 1.5461 7.17196 1.7241 7.27196L2.2121 6.39863ZM0.839438 5.97196C0.834771 6.08596 0.829438 6.20396 0.833438 6.30263C0.838104 6.40263 0.853438 6.54729 0.933438 6.68929L1.80677 6.20129C1.8401 6.26129 1.83477 6.30263 1.83277 6.25863C1.83177 6.22886 1.83177 6.19906 1.83277 6.16929L1.83877 6.01396L0.839438 5.97196ZM9.84144 4.66529L9.8361 2.99729L8.83677 3.00063L8.84144 4.66796L9.84144 4.66529ZM10.0094 4.83329C9.98744 4.83347 9.96562 4.82929 9.94525 4.82099C9.92487 4.8127 9.90634 4.80045 9.89072 4.78495C9.87511 4.76946 9.86271 4.75103 9.85425 4.73072C9.84579 4.71041 9.84144 4.68863 9.84144 4.66663H8.84144C8.84144 5.31196 9.36544 5.83329 10.0094 5.83329V4.83329ZM10.1794 4.66663C10.1794 4.75729 10.1048 4.83329 10.0108 4.83329V5.83329C10.6548 5.83329 11.1794 5.31196 11.1794 4.66663H10.1794ZM10.1794 3.01063V4.66663H11.1794V3.01063H10.1794ZM11.0061 3.17929C12.4648 3.21663 13.1188 3.37396 13.5474 3.80196L14.2541 3.09396C13.5121 2.35396 12.4628 2.21663 11.0314 2.17929L11.0061 3.17929ZM11.1794 3.01063C11.1794 3.10396 11.1021 3.18129 11.0061 3.17929L11.0314 2.17929C10.9205 2.17654 10.8102 2.196 10.7069 2.23654C10.6037 2.27709 10.5096 2.33788 10.4302 2.41536C10.3508 2.49283 10.2877 2.58541 10.2446 2.68764C10.2016 2.78988 10.1794 2.89969 10.1794 3.01063H11.1794ZM9.00277 3.16663C8.95868 3.16645 8.91713 3.14881 8.88602 3.11758C8.85491 3.08634 8.83744 3.04405 8.83744 2.99996L9.83744 2.99729C9.83673 2.77674 9.74862 2.56547 9.59242 2.40976C9.43621 2.25406 9.22466 2.16663 9.0041 2.16663L9.00277 3.16663ZM11.3761 13.8093C12.6261 13.756 13.5688 13.5893 14.2534 12.906L13.5461 12.198C13.1554 12.5886 12.5708 12.758 11.3348 12.81L11.3761 13.8093ZM10.1794 11.3333V12.6513H11.1794V11.3333H10.1794ZM8.83877 12.6666C8.83877 12.7473 8.83877 12.8093 8.83677 12.862C8.83544 12.9153 8.83277 12.946 8.8301 12.9653C8.82744 12.9846 8.82677 12.978 8.8341 12.9586C8.84703 12.9302 8.86486 12.9042 8.88677 12.882L9.59277 13.59C9.72125 13.4571 9.80153 13.2851 9.82077 13.1013C9.83877 12.97 9.83744 12.8126 9.8381 12.67L8.83877 12.6666ZM8.67144 13.8333C8.8141 13.8333 8.9721 13.8346 9.10344 13.8166C9.24944 13.7966 9.43544 13.7466 9.59277 13.59L8.8861 12.882C8.90883 12.8602 8.93524 12.8426 8.9641 12.83C8.98277 12.822 8.98944 12.8233 8.97077 12.8253C8.9362 12.8289 8.90151 12.8311 8.86677 12.832C8.81344 12.8333 8.75144 12.8333 8.67144 12.8333V13.8333ZM11.3348 12.81C11.2548 12.8133 11.1928 12.816 11.1401 12.8166C11.0874 12.8173 11.0568 12.8166 11.0388 12.8146C11.0208 12.8126 11.0288 12.8113 11.0488 12.8193C11.0734 12.8286 11.1021 12.846 11.1281 12.87L10.4361 13.592C10.5988 13.748 10.7888 13.7946 10.9408 13.8093C11.0754 13.8226 11.2334 13.8153 11.3761 13.8093L11.3348 12.81ZM10.1794 12.6513C10.1794 12.7966 10.1781 12.958 10.1968 13.0933C10.2174 13.244 10.2714 13.434 10.4361 13.592L11.1281 12.87C11.1548 12.8953 11.1721 12.924 11.1828 12.948C11.1908 12.968 11.1894 12.9746 11.1874 12.956C11.1838 12.9207 11.1816 12.8854 11.1808 12.85C11.1794 12.7966 11.1794 12.7333 11.1794 12.6513H10.1794Z" fill="#2B7FFF" />
            </svg>
            <p class="text-sm font-bold text-[#62748E] mr-1 ml-4">خرید</p>
        </div>
        <p class="text-lg font-extrabold"><span class="ml-0.5">+</span><span class="animate-counter" data-target="<?= $tickets_sold ?>" data-decimals="0">0</span></p>
    </div>
</div>

<!-- <div class="flex items-center  py-4 rounded-lg gap-2 lg:hidden">
    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
        <g clip-path="url(#clip0_54216_19411)">
            <path d="M3.89018 4.4548C3.82783 5.1198 3.78408 6.29668 4.17674 6.79762C4.17674 6.79762 3.9919 5.5048 5.64893 3.88277C6.31612 3.2298 6.47033 2.34168 6.23737 1.67558C6.10502 1.29824 5.8633 0.986521 5.6533 0.768865C5.5308 0.640896 5.62487 0.429802 5.80315 0.437458C6.88158 0.485583 8.6294 0.785271 9.37205 2.64902C9.69799 3.46715 9.72205 4.31262 9.56674 5.1723C9.4683 5.72137 9.1183 6.94199 9.91674 7.09183C10.4866 7.19902 10.7622 6.74621 10.8858 6.42027C10.9372 6.28465 11.1155 6.25074 11.2117 6.35902C12.1742 7.45387 12.2563 8.7434 12.0572 9.85355C11.6722 11.9995 9.49893 13.5614 7.33987 13.5614C4.64268 13.5614 2.49565 12.0181 1.93893 9.22465C1.71471 8.09699 1.82846 5.86574 3.56752 4.29074C3.69658 4.17262 3.90768 4.27762 3.89018 4.4548Z" fill="url(#paint0_radial_54216_19411)" />
            <path d="M8.32363 8.46781C7.32941 7.18812 7.77457 5.72797 8.01847 5.14609C8.05129 5.06953 7.96379 4.99734 7.89488 5.04437C7.46722 5.33531 6.59113 6.02 6.18316 6.98359C5.63082 8.28625 5.67019 8.9239 5.99722 9.70265C6.1941 10.1719 5.9655 10.2714 5.85066 10.2889C5.7391 10.3064 5.63629 10.232 5.55425 10.1544C5.31831 9.92777 5.15015 9.63993 5.06863 9.32312C5.05113 9.25531 4.96254 9.23672 4.92207 9.2925C4.61582 9.71578 4.45722 10.395 4.44957 10.8752C4.4255 12.3594 5.6516 13.5625 7.13472 13.5625C9.00394 13.5625 10.3657 11.4953 9.2916 9.76719C8.97988 9.26406 8.68676 8.93484 8.32363 8.46781Z" fill="url(#paint1_radial_54216_19411)" />
        </g>
        <defs>
            <radialGradient id="paint0_radial_54216_19411" cx="0" cy="0" r="1" gradientTransform="matrix(-7.72045 -0.0335068 -0.0550468 12.6677 6.80565 13.5954)" gradientUnits="userSpaceOnUse">
                <stop offset="0.314" stop-color="#FF9800" />
                <stop offset="0.662" stop-color="#FF6D00" />
                <stop offset="0.972" stop-color="#F44336" />
            </radialGradient>
            <radialGradient id="paint1_radial_54216_19411" cx="0" cy="0" r="1" gradientTransform="matrix(-0.0815922 8.07763 6.07902 0.0613961 7.23753 5.91263)" gradientUnits="userSpaceOnUse">
                <stop offset="0.214" stop-color="#FFF176" />
                <stop offset="0.328" stop-color="#FFF27D" />
                <stop offset="0.487" stop-color="#FFF48F" />
                <stop offset="0.672" stop-color="#FFF7AD" />
                <stop offset="0.793" stop-color="#FFF9C4" />
                <stop offset="0.822" stop-color="#FFF8BD" stop-opacity="0.804" />
                <stop offset="0.863" stop-color="#FFF6AB" stop-opacity="0.529" />
                <stop offset="0.91" stop-color="#FFF38D" stop-opacity="0.209" />
                <stop offset="0.941" stop-color="#FFF176" stop-opacity="0" />
            </radialGradient>
            <clipPath id="clip0_54216_19411">
                <rect width="14" height="14" fill="white" />
            </clipPath>
        </defs>
    </svg>
    <p class="text-sm font-bold">بیشترین فروش در 30 روز گذشته</p>
</div> -->
<h2 id="specifications-title" class="text-xl font-black mb-4 lg:hidden">مشخصات</h2>

<div class="w-full h-full border border-[#F1F5F9] bg-[#F1F5F9] px-5 pt-5 pb-2 lg:hidden rounded-xl">
    <div id="menuContainer" class="bg-white flex flex-col px-4 py-3 rounded-xl overflow-hidden transition-all duration-500 ease-in-out">
        <?php if ($product_type == 'اتاق فرار' && !empty($data['genres'])): ?>
            <div class="menu-item flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.54491 14.4234C2.57741 14.9321 2.90832 15.3266 3.41384 15.3954C3.82748 15.4518 4.43523 15.5 5.30682 15.5C6.17841 15.5 6.78645 15.4518 7.1998 15.3954C7.70502 15.3266 8.03652 14.9321 8.06873 14.4234C8.09355 14.0366 8.11364 13.48 8.11364 12.6932C8.11364 11.9064 8.09355 11.3497 8.06873 10.963C8.03623 10.4542 7.70532 10.0598 7.1998 9.99095C6.78616 9.93452 6.17841 9.88636 5.30682 9.88636C4.43523 9.88636 3.82748 9.93452 3.41384 9.99095C2.90861 10.0598 2.57711 10.4542 2.54491 10.963C2.52009 11.3497 2.5 11.9064 2.5 12.6932C2.5 13.48 2.52009 14.0366 2.54491 14.4234ZM2.54491 3.57664C2.57741 3.06786 2.90832 2.67343 3.41384 2.60459C3.82748 2.54816 4.43523 2.5 5.30682 2.5C6.17841 2.5 6.78645 2.54816 7.1998 2.60459C7.70502 2.67343 8.03652 3.06786 8.06873 3.57664C8.09355 3.96339 8.11364 4.52002 8.11364 5.30682C8.11364 6.09361 8.09355 6.65025 8.06873 7.037C8.03623 7.54577 7.70532 7.9402 7.1998 8.00905C6.78616 8.06548 6.17841 8.11364 5.30682 8.11364C4.43523 8.11364 3.82748 8.06548 3.41384 8.00905C2.90861 7.9402 2.57711 7.54577 2.54491 7.037C2.52009 6.65025 2.5 6.09361 2.5 5.30682C2.5 4.52002 2.52009 3.96339 2.54491 3.57664ZM14.4234 15.4551C14.9321 15.4226 15.3266 15.0917 15.3954 14.5862C15.4518 14.1725 15.5 13.5648 15.5 12.6932C15.5 11.8216 15.4518 11.2135 15.3954 10.8002C15.3266 10.295 14.9321 9.96348 14.4234 9.93127C14.0366 9.90645 13.48 9.88636 12.6932 9.88636C11.9064 9.88636 11.3497 9.90645 10.963 9.93127C10.4542 9.96377 10.0598 10.2947 9.99095 10.8002C9.93452 11.2138 9.88636 11.8216 9.88636 12.6932C9.88636 13.5648 9.93452 14.1728 9.99095 14.5862C10.0598 15.0914 10.4542 15.4229 10.963 15.4551C11.3497 15.4799 11.9064 15.5 12.6932 15.5C13.48 15.5 14.0366 15.4799 14.4234 15.4551Z" stroke="#FF6900" stroke-width="1.125" stroke-linejoin="round" />
                        <path d="M12.4329 2.67839C12.4584 2.63193 12.4958 2.59316 12.5414 2.56616C12.587 2.53915 12.639 2.5249 12.692 2.5249C12.745 2.5249 12.797 2.53915 12.8426 2.56616C12.8882 2.59316 12.9257 2.63193 12.9511 2.67839L13.5397 3.75444C13.7023 4.05187 13.9469 4.29642 14.2443 4.4591L15.3204 5.04764C15.3668 5.0731 15.4056 5.11059 15.4326 5.15618C15.4596 5.20176 15.4739 5.25377 15.4739 5.30676C15.4739 5.35974 15.4596 5.41175 15.4326 5.45734C15.4056 5.50293 15.3668 5.54041 15.3204 5.56587L14.2443 6.15442C13.9469 6.31709 13.7023 6.56164 13.5397 6.85908L12.9511 7.93512C12.9257 7.98159 12.8882 8.02036 12.8426 8.04736C12.797 8.07436 12.745 8.08861 12.692 8.08861C12.639 8.08861 12.587 8.07436 12.5414 8.04736C12.4958 8.02036 12.4584 7.98159 12.4329 7.93512L11.8444 6.85908C11.6817 6.56164 11.4371 6.31709 11.1397 6.15442L10.0636 5.56587C10.0172 5.54041 9.97841 5.50293 9.95141 5.45734C9.9244 5.41175 9.91016 5.35974 9.91016 5.30676C9.91016 5.25377 9.9244 5.20176 9.95141 5.15618C9.97841 5.11059 10.0172 5.0731 10.0636 5.04764L11.1397 4.4591C11.4371 4.29642 11.6817 4.05187 11.8444 3.75444L12.4329 2.67839Z" stroke="#FF6900" stroke-width="1.125" />
                    </svg>
                    <p class="whitespace-nowrap">ژانر</p>
                </div>
                <div class="w-full h-[1px] bg-[#E2E8F0] mx-3"></div>
                <p class="">
                    <?php
                    $genre_names = array_map(function ($g) {
                        return $g['title'];
                    }, $data['genres']);
                    echo implode('.', $genre_names);
                    ?>
                </p>
            </div>
        <?php endif; ?>
        <div class="menu-item flex justify-between items-center">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M3.82732 3.02128C4.9387 1.93214 6.435 1.32559 7.99106 1.33345C9.54712 1.34131 11.0372 1.96294 12.1375 3.06326C13.2379 4.16357 13.8595 5.65367 13.8673 7.20973C13.8752 8.76579 13.2687 10.2621 12.1795 11.3735L9.05788 14.4951C8.77819 14.7747 8.3989 14.9318 8.00342 14.9318C7.60793 14.9318 7.22864 14.7747 6.94895 14.4951L3.82732 11.3735C2.71983 10.2659 2.09766 8.76369 2.09766 7.19737C2.09766 5.63106 2.71983 4.12889 3.82732 3.02128Z" stroke="#FF6900" stroke-width="1.5" stroke-linejoin="round" />
                    <path d="M8.00086 9.43459C9.23643 9.43459 10.2381 8.43296 10.2381 7.1974C10.2381 5.96183 9.23643 4.96021 8.00086 4.96021C6.7653 4.96021 5.76367 5.96183 5.76367 7.1974C5.76367 8.43296 6.7653 9.43459 8.00086 9.43459Z" stroke="#FF6900" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="shrink-0">لوکیشن</p>
            </div>
            <div class="w-full h-[1px] bg-[#E2E8F0] mx-3"></div>
            <p class="shrink-0"><?= esc_html($data['hood_name']) ?>.<?= esc_html($data['city_name']) ?></p>
        </div>

        <div class="menu-item flex justify-between items-center">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M3.375 12.75H3C2.80109 12.75 2.61032 12.6709 2.46967 12.5303C2.32902 12.3896 2.25 12.1989 2.25 12C2.25 11.4032 2.48705 10.8309 2.90901 10.409C3.33097 9.98701 3.90326 9.74996 4.5 9.74996H5.25M5.25 7.46246C4.97461 7.40614 4.71541 7.28862 4.49157 7.1186C4.26774 6.94858 4.08499 6.7304 3.95685 6.48022C3.82872 6.23004 3.75847 5.95424 3.75131 5.67325C3.74415 5.39225 3.80026 5.11324 3.91549 4.85685C4.03072 4.60047 4.20212 4.37327 4.41701 4.19207C4.6319 4.01087 4.88478 3.88031 5.15694 3.81003C5.4291 3.73976 5.71358 3.73157 5.98933 3.78609C6.26508 3.8406 6.52505 3.95641 6.75 4.12496M14.625 12.75H15C15.1989 12.75 15.3897 12.6709 15.5303 12.5303C15.671 12.3896 15.75 12.1989 15.75 12C15.75 11.4032 15.5129 10.8309 15.091 10.409C14.669 9.98701 14.0967 9.74996 13.5 9.74996H12.75M12.75 7.46246C13.0254 7.40614 13.2846 7.28862 13.5084 7.1186C13.7323 6.94858 13.915 6.7304 14.0431 6.48022C14.1713 6.23004 14.2415 5.95424 14.2487 5.67325C14.2558 5.39225 14.1997 5.11324 14.0845 4.85685C13.9693 4.60047 13.7979 4.37327 13.583 4.19207C13.3681 4.01087 13.1152 3.88031 12.8431 3.81003C12.5709 3.73976 12.2864 3.73157 12.0107 3.78609C11.7349 3.8406 11.4749 3.95641 11.25 4.12496M11.625 14.25H6.375C6.17609 14.25 5.98532 14.1709 5.84467 14.0303C5.70402 13.8896 5.625 13.6989 5.625 13.5C5.625 12.9032 5.86205 12.3309 6.28401 11.909C6.70597 11.487 7.27826 11.25 7.875 11.25H10.125C10.7217 11.25 11.294 11.487 11.716 11.909C12.1379 12.3309 12.375 12.9032 12.375 13.5C12.375 13.6989 12.296 13.8896 12.1553 14.0303C12.0147 14.1709 11.8239 14.25 11.625 14.25ZM10.875 7.12496C10.875 7.62224 10.6775 8.09916 10.3258 8.45079C9.97419 8.80242 9.49728 8.99996 9 8.99996C8.50272 8.99996 8.02581 8.80242 7.67417 8.45079C7.32254 8.09916 7.125 7.62224 7.125 7.12496C7.125 6.62768 7.32254 6.15077 7.67417 5.79914C8.02581 5.44751 8.50272 5.24996 9 5.24996C9.49728 5.24996 9.97419 5.44751 10.3258 5.79914C10.6775 6.15077 10.875 6.62768 10.875 7.12496Z" stroke="#FF6900" stroke-width="1.2" stroke-linecap="round" />
                </svg>
                <p class="shrink-0">ظرفیت</p>
            </div>
            <div class="w-full h-[1px] bg-[#E2E8F0] mx-3"></div>
            <p class="shrink-0"><?= $data['number_min'] ?> تا <?= $data['number_max'] ?> نفر</p>
        </div>

        <div class="menu-item flex justify-between items-center extra-item hidden">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M8.08333 3.00191C7.89782 3.00191 7.74242 2.93905 7.61714 2.81333C7.49186 2.68762 7.42901 2.53222 7.42857 2.34714C7.42813 2.16207 7.49099 2.00667 7.61714 1.88095C7.74329 1.75524 7.89869 1.69238 8.08333 1.69238H10.7024C10.8879 1.69238 11.0435 1.75524 11.1692 1.88095C11.2949 2.00667 11.3576 2.16207 11.3571 2.34714C11.3567 2.53222 11.2938 2.68784 11.1686 2.81399C11.0433 2.94014 10.8879 3.00278 10.7024 3.00191H8.08333ZM9.39286 10.2043C9.57837 10.2043 9.73399 10.1414 9.8597 10.0157C9.98541 9.89 10.0481 9.7346 10.0476 9.54952V6.93048C10.0476 6.74496 9.98476 6.58956 9.85905 6.46429C9.73333 6.33901 9.57793 6.27615 9.39286 6.27571C9.20778 6.27528 9.05238 6.33814 8.92667 6.46429C8.80095 6.59044 8.73809 6.74583 8.73809 6.93048V9.54952C8.73809 9.73504 8.80095 9.89065 8.92667 10.0164C9.05238 10.1421 9.20778 10.2047 9.39286 10.2043ZM9.39286 15.4424C8.58532 15.4424 7.82405 15.287 7.10905 14.9762C6.39405 14.6654 5.7694 14.2424 5.23512 13.7073C4.70083 13.1721 4.27808 12.5472 3.96685 11.8327C3.65562 11.1181 3.5 10.3571 3.5 9.54952C3.5 8.74198 3.65562 7.98071 3.96685 7.26571C4.27808 6.55071 4.70083 5.92607 5.23512 5.39179C5.7694 4.8575 6.39426 4.43474 7.1097 4.12351C7.82514 3.81228 8.58619 3.65667 9.39286 3.65667C10.0694 3.65667 10.7187 3.76579 11.3408 3.98405C11.9628 4.2023 12.5466 4.51877 13.0923 4.93345L13.5506 4.47512C13.6706 4.35508 13.8234 4.29506 14.0089 4.29506C14.1944 4.29506 14.3472 4.35508 14.4673 4.47512C14.5873 4.59516 14.6473 4.74794 14.6473 4.93345C14.6473 5.11897 14.5873 5.27175 14.4673 5.39179L14.0089 5.85012C14.4236 6.39575 14.7401 6.97958 14.9583 7.60161C15.1766 8.22363 15.2857 8.87294 15.2857 9.54952C15.2857 10.3571 15.1301 11.1183 14.8189 11.8333C14.5076 12.5483 14.0849 13.173 13.5506 13.7073C13.0163 14.2415 12.3914 14.6645 11.676 14.9762C10.9606 15.2879 10.1995 15.4433 9.39286 15.4424ZM9.39286 14.1329C10.6587 14.1329 11.7391 13.6854 12.6339 12.7906C13.5288 11.8958 13.9762 10.8154 13.9762 9.54952C13.9762 8.28365 13.5288 7.20329 12.6339 6.30845C11.7391 5.41361 10.6587 4.96619 9.39286 4.96619C8.12698 4.96619 7.04663 5.41361 6.15179 6.30845C5.25694 7.20329 4.80952 8.28365 4.80952 9.54952C4.80952 10.8154 5.25694 11.8958 6.15179 12.7906C7.04663 13.6854 8.12698 14.1329 9.39286 14.1329Z" fill="#FF6900" />
                </svg>
                <p class="shrink-0">مدت سانس</p>
            </div>
            <div class="w-full h-[1px] bg-[#E2E8F0] mx-3"></div>
            <p class="shrink-0"><?= $data['duration'] ?>دقیقه</p>
        </div>

        <?php if ($product_type == 'اتاق فرار'): ?>
        <div class="menu-item flex justify-between items-center extra-item hidden">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M12.1788 6.28503H5.82031C4.57767 6.28503 3.57031 7.29239 3.57031 8.53503V13.6875C3.57031 14.9302 4.57767 15.9375 5.82031 15.9375H12.1788C13.4215 15.9375 14.4288 14.9302 14.4288 13.6875V8.53503C14.4288 7.29239 13.4215 6.28503 12.1788 6.28503Z" stroke="#FF6900" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M11.4114 6.285V4.47525C11.4114 3.83535 11.1572 3.22166 10.7048 2.76918C10.2523 2.3167 9.63859 2.0625 8.99869 2.0625C8.35879 2.0625 7.74509 2.3167 7.29262 2.76918C6.84014 3.22166 6.58594 3.83535 6.58594 4.47525V6.285" stroke="#FF6900" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M9 12.2362C9.62132 12.2362 10.125 11.7325 10.125 11.1112C10.125 10.4899 9.62132 9.98621 9 9.98621C8.37868 9.98621 7.875 10.4899 7.875 11.1112C7.875 11.7325 8.37868 12.2362 9 12.2362Z" fill="#FF6900" />
                </svg>
                <p class="shrink-0">میزان سختی</p>
            </div>
            <div class="w-full h-[1px] bg-[#E2E8F0] mx-3"></div>
            <p class="shrink-0">
                <?php
                $level = $data['level'];
                if ($level == 1) echo 'خیلی سخت';
                elseif ($level == 2) echo 'سخت';
                elseif ($level == 3) echo 'متوسط';
                elseif ($level == 4) echo 'آسان';
                else echo 'نامشخص';
                ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="menu-item flex justify-between items-center extra-item hidden">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="mx-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M5.80667 8.36127C5.63728 8.36127 5.47482 8.42856 5.35504 8.54834C5.23526 8.66812 5.16797 8.83058 5.16797 8.99997C5.16797 9.16937 5.23526 9.33183 5.35504 9.45161C5.47482 9.57139 5.63728 9.63868 5.80667 9.63868H12.1937C12.3631 9.63868 12.5256 9.57139 12.6454 9.45161C12.7652 9.33183 12.8324 9.16937 12.8324 8.99997C12.8324 8.83058 12.7652 8.66812 12.6454 8.54834C12.5256 8.42856 12.3631 8.36127 12.1937 8.36127H5.80667Z" fill="#FF6900" />
                    <path d="M15.8132 9C15.8132 10.8069 15.0955 12.5398 13.8178 13.8174C12.5401 15.0951 10.8073 15.8129 9.00037 15.8129C7.19348 15.8129 5.4606 15.0951 4.18294 13.8174C2.90528 12.5398 2.1875 10.8069 2.1875 9C2.1875 7.19312 2.90528 5.46024 4.18294 4.18258C5.4606 2.90492 7.19348 2.18713 9.00037 2.18713C10.8073 2.18713 12.5401 2.90492 13.8178 4.18258C15.0955 5.46024 15.8132 7.19312 15.8132 9ZM14.5358 9C14.5358 7.53191 13.9526 6.12394 12.9145 5.08584C11.8764 4.04774 10.4685 3.46455 9.00037 3.46455C7.53227 3.46455 6.12431 4.04774 5.08621 5.08584C4.04811 6.12394 3.46491 7.53191 3.46491 9C3.46491 10.4681 4.04811 11.8761 5.08621 12.9142C6.12431 13.9523 7.53227 14.5355 9.00037 14.5355C10.4685 14.5355 11.8764 13.9523 12.9145 12.9142C13.9526 11.8761 14.5358 10.4681 14.5358 9Z" fill="#FF6900" />
                </svg>
                <p class="shrink-0">مناسب سن</p>
            </div>
            <div class="w-full h-[1px] bg-[#E2E8F0] mx-3"></div>
            <p class="shrink-0"><?= $data['age'] ?>+</p>
        </div>

    </div>

    <button id="toggleMenu" class="flex justify-center items-center gap-2 text-[#889BAD] text-sm font-bold mt-3 mx-auto">
        <span>مشاهده کامل</span>
        <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mx-0">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

</div>
<!--------------------------------end-Specifications------------------------------------------------------------------------>

<div class="flex w-full gap-9 mt-5">

    <!--------------------------start-right-section--------------------------------------------------------------------------->
    <div class="flex flex-col grow overflow-hidden">

        <!-- <div id="stiky-title-desktop" class="max-lg:hidden rounded-2xl gap-8 mb-1 sticky top-4 z-30 bg-white py-4 px-4 flex items-center justify-between shadow-lg" style="display: none;">
            <?php if ($has_introduction): ?>
            <button data-section="game-introduction-section" class="tab-btn w-[148px] h-[38px] text-sm font-extrabold rounded-lg bg-[#F8FAFC] text-center flex items-center justify-center text-[#0F172B] transition-colors">مشخصات</button>
            <?php endif; ?>
            <button data-section="address-section" class="tab-btn w-[148px] h-[38px] text-sm font-extrabold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors">آدرس</button>
            <?php if ($has_description): ?>
            <button data-section="description-section" class="tab-btn w-[148px] h-[38px] text-sm font-extrabold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors">توضیحات</button>
            <?php endif; ?>
            <?php if ($has_video): ?>
            <button data-section="video-section" class="tab-btn w-[148px] h-[38px] text-sm font-extrabold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors">ویدئو</button>
            <?php endif; ?>
            <button data-section="comments-section" class="tab-btn w-[148px] h-[38px] text-sm font-extrabold rounded-lg bg-[#F8FAFC] text-center text-[#90A1B9] flex items-center justify-center transition-colors">نظرات</button>
        </div> -->

        <!---------------------------start-Game introduction------------------------->
        <?php if ($has_introduction): ?>
        <section id="game-introduction-section" class="rounded-2xl max-lg:mb-0 max-lg:rounded-none max-lg:shadow-none  max-lg:border-0 ">
            <div class="relative">
                <h2 class="!text-2xl mb-2">معرفی بازی</h2>
                <div class="introduction-wrapper">
                    <?php
                    // پردازش متن برای حفظ فرمت‌بندی
                    // چون از wp_editor استفاده می‌شود، متن به صورت HTML ذخیره می‌شود
                    $introduction_text = $data['introduction_text'] ?? '';

                    // اگر متن خالی است، چیزی نمایش نده
                    if (!empty($introduction_text)) {
                        // استفاده از wpautop برای تبدیل خطوط جدید به <p> و <br>
                        // این تابع HTML موجود را حفظ می‌کند (بولت‌ها، لینک‌ها و...)
                        $introduction_text = wpautop($introduction_text);
                        // تبدیل <br /> به <br> برای سازگاری
                        $introduction_text = str_replace(['<br />', '<br/>'], '<br>', $introduction_text);
                        // حذف <p> و </p> اضافی که ممکن است با line-clamp مشکل ایجاد کند
                        // اما محتوای داخل <p> را حفظ می‌کنیم
                        $introduction_text = preg_replace('/<p[^>]*>/', '', $introduction_text);
                        $introduction_text = str_replace('</p>', '<br>', $introduction_text);
                        // حذف <br> اضافی در ابتدا و انتها
                        $introduction_text = trim($introduction_text);
                        $introduction_text = preg_replace('/^(<br\s*\/?>)+/', '', $introduction_text);
                        $introduction_text = preg_replace('/(<br\s*\/?>)+$/', '', $introduction_text);
                    }
                    ?>
                    <?php if (!empty($introduction_text)): ?>
                        <div class="introduction-text-content" data-full-text="<?= esc_attr($data['introduction_text']) ?>">
                            <div class="introduction-text-display text-justify font-medium leading-8 text-sm line-clamp-3">
                                <?= wp_kses_post($introduction_text) ?>
                            </div>
                            <button type="button" class="toggle-introduction-btn inline-flex items-center gap-1 text-[#5091FB] font-medium text-sm transition-colors mt-2 focus:outline-none hover:text-[#3F7FF5] hidden">
                                <span class="toggle-text">مشاهده بیشتر</span>
                                <svg class="chevron-icon w-3 h-3 transition-transform duration-300" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.75 0.75L4.15 3.3C4.50556 3.56667 4.99444 3.56667 5.35 3.3L8.75 0.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        <!--------------------------end-Game introduction---------------------------->
        <?php if (!empty($yearly_ranks) && is_array($yearly_ranks) && !empty($yearly_ranks[0]['desc'])): ?>
            <div class="w-full h-[1px] bg-[#E4EBF0] my-5"></div>

            <!---------------------------start-honors------------------------------------>

            <section>
                <h2 class="text-lg font-bold mb-5">افتخارات</h2>
                <?php foreach ($yearly_ranks as $year_rank): ?>
                    <div class="flex items-center mb-4">
                        <div class="flex justify-start items-center">
                            <img src="<?= Theme_URL ?>/assets/images/best/<?= $year_rank['year'] ?>.avif" alt="" class="w-6">
                            <div class="flex flex-col mr-4">
                                <span class="font-bold text-transparent bg-clip-text [background-image:linear-gradient(13deg,#FFF0DA_-24.37%,#94641E_27.68%,#E8C650_113.04%,#EAC885_131.19%,#FBF5D1_138.1%,#BD9A4B_141.54%,#ECD8A3_141.56%)] leading-none">تندیس زِد</span>
                                <span class="font-bold text-transparent bg-clip-text [background-image:linear-gradient(13deg,#FFF0DA_-24.37%,#94641E_27.68%,#E8C650_113.04%,#EAC885_131.19%,#FBF5D1_138.1%,#BD9A4B_141.54%,#ECD8A3_141.56%)] leading-none"><?= $year_rank['year'] ?></span>
                            </div>
                            <div class="w-px mx-5 bg-gradient-to-t from-white via-slate-110 to-white h-12.5"></div>
                        </div>
                        <p class="text-lg max-lg:text-lg font-bold">
                            رتبه <span class="text-2xl border-b border-b-black"><?= $year_rank['rank'] ?></span>
                            <?= $year_rank['desc'] ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </section>
            <div class="w-full h-[1px] bg-[#E4EBF0] my-5"></div>
        <?php endif; ?>
        <!---------------------------end-honors-------------------------------------->

        

        <!----------------------------start-Facilities------------------------------->
        <style>
            /* مخفی کردن اسکرول‌بار در تمام مرورگرها */
            .scroll-container {
                -ms-overflow-style: none;
                /* IE و Edge */
                scrollbar-width: none;
                /* Firefox */
            }

            .scroll-container::-webkit-scrollbar {
                display: none;
                /* Chrome، Safari */
            }

            /* فعال‌سازی اسکرول لمسی طبیعی در موبایل */
            .scroll-container {
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-x: contain;
                cursor: grab;
            }

            .scroll-container:active {
                cursor: grabbing;
            }

            .scroll-container.dragging {
                cursor: grabbing;
            }

            /* استایل برای محدود کردن خطوط متن */
            .line-clamp-3 {
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
                line-clamp: 3;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* استایل برای متن معرفی بازی */
            .introduction-text-display {
                transition: all 0.4s ease-in-out;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .introduction-text-display.expanded {
                display: block;
                -webkit-line-clamp: unset !important;
                line-clamp: unset !important;
                overflow: visible !important;
            }

            .introduction-text-display p {
                margin: 0;
                padding: 0;
            }

            .introduction-text-display ul,
            .introduction-text-display ol {
                margin: 0.5em 0;
                padding-right: 1.5em;
            }

            .introduction-text-display li {
                margin: 0.25em 0;
            }

            .toggle-introduction-btn {
                transition: all 0.3s ease;
            }

            .toggle-introduction-btn .chevron-icon {
                transition: transform 0.3s ease-in-out;
            }

            .toggle-introduction-btn.expanded .chevron-icon {
                transform: rotate(180deg);
            }

            /* استایل برای متن سناریو */
            .scenario-text-display {
                transition: all 0.4s ease-in-out;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .scenario-text-display.expanded {
                display: block;
                -webkit-line-clamp: unset !important;
                line-clamp: unset !important;
                overflow: visible !important;
            }

            .scenario-text-display p {
                margin: 0;
                padding: 0;
            }

            .scenario-text-display ul,
            .scenario-text-display ol {
                margin: 0.5em 0;
                padding-right: 1.5em;
            }

            .scenario-text-display li {
                margin: 0.25em 0;
            }

            .toggle-scenario-btn {
                transition: all 0.3s ease;
            }

            .toggle-scenario-btn .chevron-icon {
                transition: transform 0.3s ease-in-out;
            }

            .toggle-scenario-btn.expanded .chevron-icon {
                transform: rotate(180deg);
            }

            /* استایل برای متن قوانین */
            .rules-text-display {
                transition: all 0.4s ease-in-out;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .rules-text-display.expanded {
                display: block;
                -webkit-line-clamp: unset !important;
                line-clamp: unset !important;
                overflow: visible !important;
            }

            .rules-text-display p {
                margin: 0;
                padding: 0;
            }

            .rules-text-display ul,
            .rules-text-display ol {
                margin: 0.5em 0;
                padding-right: 1.5em;
            }

            .rules-text-display li {
                margin: 0.25em 0;
            }

            /* استایل برای خطوط با تیک */
            .rules-line-item {
                margin-bottom: 0.5em;
            }

            .rules-check-icon {
                width: 16px;
                height: 16px;
                flex-shrink: 0;
                margin-top: 0.25em;
            }

            .toggle-rules-btn {
                transition: all 0.3s ease;
            }

            .toggle-rules-btn .chevron-icon {
                transition: transform 0.3s ease-in-out;
            }

            .toggle-rules-btn.expanded .chevron-icon {
                transform: rotate(180deg);
            }

            .line-clamp-4 {
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 4;
                line-clamp: 4;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        </style>

        <script>
            // افزودن قابلیت drag با موس برای کروسل
            document.addEventListener('DOMContentLoaded', function() {
                const scrollContainer = document.querySelector('.scroll-container');
                if (scrollContainer) {
                    let isDown = false;
                    let startX;
                    let scrollLeft;

                    scrollContainer.addEventListener('mousedown', (e) => {
                        isDown = true;
                        scrollContainer.classList.add('dragging');
                        startX = e.pageX - scrollContainer.offsetLeft;
                        scrollLeft = scrollContainer.scrollLeft;
                    });

                    scrollContainer.addEventListener('mouseleave', () => {
                        isDown = false;
                        scrollContainer.classList.remove('dragging');
                    });

                    scrollContainer.addEventListener('mouseup', () => {
                        isDown = false;
                        scrollContainer.classList.remove('dragging');
                    });

                    scrollContainer.addEventListener('mousemove', (e) => {
                        if (!isDown) return;
                        e.preventDefault();
                        const x = e.pageX - scrollContainer.offsetLeft;
                        const walk = (x - startX) * 2; // سرعت اسکرول
                        scrollContainer.scrollLeft = scrollLeft - walk;
                    });
                }
            });
        </script>

        <?php if ($data['options']) { ?>
            <section class="max-lg:relative max-lg:w-screen max-lg:left-1/2 max-lg:right-1/2 max-lg:-ml-[50vw] max-lg:-mr-[50vw] max-lg:pr-6">
                <h2 class="text-lg font-bold mb-5">امکانات</h2>
                <div class="relative overflow-hidden embla_normal horizontal dragFree">
                    <div class="embla__viewport">
                        <div class="embla__container child:ml-5">
                            <?php printOptions($data['options']); ?>
                        </div>
                    </div>
                </div>
            </section>
            <div class="w-full h-[1px] bg-[#E4EBF0] my-5"></div>
        <?php } ?>
        <!----------------------------end-Facilities---------------------------------->

        

        <!----------------------------start-adress------------------------------------>
        <section id="address-section">
            <h2 class="text-xl font-extrabold text-[#4E5C6D]">آدرس</h2>
            <p class="text-sm font-medium lg:hidden"><?= $data['city_name']; ?>، <?= $data['address_info']['address'] ?></p>
            <div class="flex max-lg:flex-col items-center justify-between max-lg:hidden">
                <div class="flex flex-col lg:gap-[50px] justify-between">
                    <p class="text-sm font-medium"><?= $data['city_name']; ?>، <?= $data['address_info']['address'] ?></p>
                    <div class="flex justify-between ">
                        <div class="flex items-center gap-3">
                            <p class="text-sm font-medium text-[#889BAD]">میزبان</p>
                            <p class="text-sm font-medium"><?= $data['brand']['title'] ?></p>
                        </div>
                        <?php
                        $brand_image_url = Theme_URL . 'assets/images/brand-default-icon.png';
                        if ($data['brand']['image']) {
                            $brand_image_url = $data['brand']['image'];
                        }
                        ?>
                        <img src="<?= $brand_image_url ?>" alt="<?= $data['brand']['title'] ?>" class="w-[34px] h-[34px] mx-0 rounded-md object-cover" />
                    </div>
                    <div class="flex flex-wrap items-center gap-4 max-lg:hidden">
                        <button type="button" id="go_map_ez_desktop" data-title="<?= $product_type . ' ' . $data['title']; ?>" data-url="https://maps.google.com/?q=<?= (float) $data['address_info']['lat'] ?>,<?= (float) $data['address_info']['long'] ?>" class="share flex items-center bg-[#0F172B] text-white py-3 px-4 gap-3 rounded-lg">
                            اشتراک گذاری با دوستان
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.3633 0.568481C11.6522 0.483534 11.9586 0.477887 12.25 0.553833C12.5419 0.629951 12.8077 0.784331 13.0186 1.00012L13.0947 1.08411C13.4613 1.51401 13.5974 2.10708 13.4277 2.66125L10.5654 12.3029L10.5645 12.3058C10.3706 12.9431 9.83872 13.4047 9.1709 13.4904H9.16895C9.08431 13.5008 8.99848 13.5001 8.95703 13.5001C8.37479 13.5001 7.83836 13.2039 7.53125 12.6886V12.6876L5.68848 9.64172C5.44333 9.23694 5.4432 8.73952 5.66504 8.34094C5.52162 8.42016 5.36414 8.47341 5.19922 8.49329C4.91173 8.52793 4.62071 8.46425 4.37402 8.31262L1.35449 6.45618C1.06465 6.27812 0.834155 5.99606 0.688477 5.69934C0.543608 5.40419 0.455149 5.03678 0.523438 4.6759C0.578155 4.36982 0.718487 4.08571 0.927734 3.85559C1.1368 3.62568 1.40588 3.45867 1.70508 3.37512L11.3633 0.567505V0.568481Z" stroke="white" />
                            </svg>
                        </button>
                        <a href="https://escapezoom.ir/geo.php?g=<?= $data['address_info']['lat'] ?>,<?= $data['address_info']['long'] ?>" class="flex items-center bg-[#0F172B] text-white py-3 px-4 gap-3 rounded-lg">
                            مسیریابی
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 1.37512C7.78114 1.37512 8.50967 1.52297 9.19043 1.81653C9.88074 2.11427 10.4758 2.51567 10.9795 3.01965C11.4834 3.52395 11.8849 4.11948 12.1836 4.80969C12.4778 5.48967 12.6261 6.21792 12.625 6.99915C12.6239 7.78137 12.4753 8.51077 12.1826 9.19153C11.8862 9.88098 11.4856 10.4756 10.9805 10.9796C10.4747 11.4843 9.87949 11.8865 9.19043 12.1847C8.51174 12.4784 7.78282 12.6259 7 12.6251L6.70898 12.6183C6.03641 12.5859 5.40435 12.4405 4.80957 12.1837C4.11984 11.8859 3.5243 11.484 3.01953 10.9796C2.51511 10.4755 2.1137 9.88033 1.81641 9.19055C1.52329 8.51026 1.37541 7.78173 1.375 7.00012C1.37462 6.21859 1.5225 5.48992 1.81641 4.80969C2.11467 4.11942 2.51655 3.52399 3.02051 3.01965C3.5242 2.51567 4.11926 2.11427 4.80957 1.81653C5.49033 1.52297 6.21886 1.37512 7 1.37512Z" stroke="white" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div id="map" class="w-[520px] h-[205px] rounded-lg border overflow-hidden z-[3]"></div>
            </div>

            <div class="lg:hidden relative h-[200px] mx-auto mt-3 p-3">
                <div id="map-mobile" class="w-full h-[200px] rounded-xl absolute top-0 right-0 z-[3]"></div>
                <div class="flex items-center justify-between">
                    <button type="button" id="go_map_ez_mobile" data-title="<?= $product_type . ' ' . $data['title']; ?>" data-url="https://maps.google.com/?q=<?= (float) $data['address_info']['lat'] ?>,<?= (float) $data['address_info']['long'] ?>" class="share absolute bottom-3 right-3 flex items-center justify-between bg-[#0F172B] text-white py-3 px-4 gap-3 rounded-lg z-[4]">
                        اشتراک گذاری با دوستان
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                            <path d="M11.3633 0.568481C11.6522 0.483534 11.9586 0.477887 12.25 0.553833C12.5419 0.629951 12.8077 0.784331 13.0186 1.00012L13.0947 1.08411C13.4613 1.51401 13.5974 2.10708 13.4277 2.66125L10.5654 12.3029L10.5645 12.3058C10.3706 12.9431 9.83872 13.4047 9.1709 13.4904H9.16895C9.08431 13.5008 8.99848 13.5001 8.95703 13.5001C8.37479 13.5001 7.83836 13.2039 7.53125 12.6886V12.6876L5.68848 9.64172C5.44333 9.23694 5.4432 8.73952 5.66504 8.34094C5.52162 8.42016 5.36414 8.47341 5.19922 8.49329C4.91173 8.52793 4.62071 8.46425 4.37402 8.31262L1.35449 6.45618C1.06465 6.27812 0.834155 5.99606 0.688477 5.69934C0.543608 5.40419 0.455149 5.03678 0.523438 4.6759C0.578155 4.36982 0.718487 4.08571 0.927734 3.85559C1.1368 3.62568 1.40588 3.45867 1.70508 3.37512L11.3633 0.567505V0.568481Z" stroke="white" />
                        </svg>
                    </button>
                    <a href="https://escapezoom.ir/geo.php?g=<?= $data['address_info']['lat'] ?>,<?= $data['address_info']['long'] ?>" class="absolute bottom-3 left-3 flex items-center justify-between bg-[#0F172B] text-white py-3 px-4 gap-3 rounded-lg w-full max-w-[120px] z-[4]">
                        مسیریابی
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                            <path d="M7 1.37512C7.78114 1.37512 8.50967 1.52297 9.19043 1.81653C9.88074 2.11427 10.4758 2.51567 10.9795 3.01965C11.4834 3.52395 11.8849 4.11948 12.1836 4.80969C12.4778 5.48967 12.6261 6.21792 12.625 6.99915C12.6239 7.78137 12.4753 8.51077 12.1826 9.19153C11.8862 9.88098 11.4856 10.4756 10.9805 10.9796C10.4747 11.4843 9.87949 11.8865 9.19043 12.1847C8.51174 12.4784 7.78282 12.6259 7 12.6251L6.70898 12.6183C6.03641 12.5859 5.40435 12.4405 4.80957 12.1837C4.11984 11.8859 3.5243 11.484 3.01953 10.9796C2.51511 10.4755 2.1137 9.88033 1.81641 9.19055C1.52329 8.51026 1.37541 7.78173 1.375 7.00012C1.37462 6.21859 1.5225 5.48992 1.81641 4.80969C2.11467 4.11942 2.51655 3.52399 3.02051 3.01965C3.5242 2.51567 4.11926 2.11427 4.80957 1.81653C5.49033 1.52297 6.21886 1.37512 7 1.37512Z" stroke="white" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="lg:hidden w-full h-[1px] bg-[#E4EBF0] my-4"></div>

            <div class="lg:hidden flex justify-between">
                <div class="flex items-center gap-3">
                    <p class="text-[#889BAD] text-sm font-black">میزبان</p>
                    <p class="text-base font-bold"><?= $data['brand']['title'] ?></p>
                </div>
                <img src="<?= $brand_image_url ?>" alt="<?= $data['brand']['title'] ?>" class="w-[34px] h-[34px] rounded-md object-cover" />
            </div>

        </section>

        <script>
            // Initialize Leaflet map for desktop
            var map = L.map('map').setView([<?= round((float) $data['address_info']['lat'], 3) ?>, <?= round((float) $data['address_info']['long'], 3) ?>], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            let primaryIcon = L.icon({
                iconUrl: "<?= Theme_URL ?>assets/images/escapezoom-marker-icon.png",
                iconSize: [28, 34]
            });
            L.marker({
                lat: <?= round((float) $data['address_info']['lat'], 3) ?>,
                lon: <?= round((float) $data['address_info']['long'], 3) ?>
            }, {
                icon: primaryIcon
            }).addTo(map).bindPopup("لوکیشن اتاق فرار <br> <?= $data['title'] ?>");

            // Initialize Leaflet map for mobile
            var mapMobile = L.map('map-mobile').setView([<?= round((float) $data['address_info']['lat'], 3) ?>, <?= round((float) $data['address_info']['long'], 3) ?>], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapMobile);
            L.marker({
                lat: <?= round((float) $data['address_info']['lat'], 3) ?>,
                lon: <?= round((float) $data['address_info']['long'], 3) ?>
            }, {
                icon: primaryIcon
            }).addTo(mapMobile).bindPopup("لوکیشن اتاق فرار <br> <?= $data['title'] ?>");

            // Share button functionality
            jQuery(document).ready(function($) {
                $('#go_map_ez_desktop, #go_map_ez_mobile').on('click', function() {
                    var title = $(this).data('title');
                    var url = $(this).data('url');

                    if (navigator.share) {
                        navigator.share({
                            title: title,
                            url: url
                        }).catch(function(error) {
                            console.log('Error sharing:', error);
                        });
                    } else {
                        // Fallback: copy to clipboard
                        var tempInput = $('<input>');
                        $('body').append(tempInput);
                        tempInput.val(url).select();
                        document.execCommand('copy');
                        tempInput.remove();
                        alert('لینک کپی شد!');
                    }
                });
            });
        </script>
        <!---------------------------end-adress--------------------------------------->

        <div class="w-full h-[1px] bg-[#E4EBF0] my-5"></div>

        <!--------------------------start-Description--------------------------------->

        <section>
            <p class="text-xl font-black lg:hidden">توضیحات </p>

            <div class="relative">

                <?php if ($has_scenario): ?>
                <section class="pt-4 rounded-2xl max-lg:rounded-none max-lg:shadow-none max-lg:border-0" id="scenario">
                    <div class="relative">
                        <div class="scenario-wrapper">
                            <h2 class="!my-3 text-lg font-bold">سناریو</h2>
                            <?php
                            // پردازش متن برای حفظ فرمت‌بندی
                            // چون از wp_editor استفاده می‌شود، متن به صورت HTML ذخیره می‌شود
                            $scenario_text = $data['scenario'] ?? '';

                            // اگر متن خالی است، چیزی نمایش نده
                            if (!empty($scenario_text)) {
                                // استفاده از wpautop برای تبدیل خطوط جدید به <p> و <br>
                                // این تابع HTML موجود را حفظ می‌کند (بولت‌ها، لینک‌ها و...)
                                $scenario_text = wpautop($scenario_text);
                                // تبدیل <br /> به <br> برای سازگاری
                                $scenario_text = str_replace(['<br />', '<br/>'], '<br>', $scenario_text);
                                // حذف <p> و </p> اضافی که ممکن است با line-clamp مشکل ایجاد کند
                                // اما محتوای داخل <p> را حفظ می‌کنیم
                                $scenario_text = preg_replace('/<p[^>]*>/', '', $scenario_text);
                                $scenario_text = str_replace('</p>', '<br>', $scenario_text);
                                // حذف <br> اضافی در ابتدا و انتها
                                $scenario_text = trim($scenario_text);
                                $scenario_text = preg_replace('/^(<br\s*\/?>)+/', '', $scenario_text);
                                $scenario_text = preg_replace('/(<br\s*\/?>)+$/', '', $scenario_text);
                            }
                            ?>
                            <?php if (!empty($scenario_text)): ?>
                                <div class="scenario-text-content" data-full-text="<?= esc_attr($data['scenario']) ?>">
                                    <div class="scenario-text-display text-sm leading-9 font-medium line-clamp-3">
                                        <?= wp_kses_post($scenario_text) ?>
                                    </div>
                                    <button type="button" class="toggle-scenario-btn inline-flex items-center gap-1 text-[#5091FB] font-medium text-sm transition-colors mt-2 focus:outline-none hover:text-[#3F7FF5] hidden">
                                        <span class="toggle-text">مشاهده بیشتر</span>
                                        <svg class="chevron-icon w-3 h-3 transition-transform duration-300" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0.75 0.75L4.15 3.3C4.50556 3.56667 4.99444 3.56667 5.35 3.3L8.75 0.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        </svg>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <?php if ($has_description): ?>
                <section id="description-section" class="pt-4 rounded-2xl max-lg:rounded-none max-lg:shadow-none max-lg:border-0 max-lg:pb-0">
                    <div class="relative">
                        <div class="rules-wrapper">
                            <h2 class="!my-3 text-lg font-bold">توضیحات لازم به مطالعه قبل از رزرو</h2>
                            <?php
                            // پردازش متن برای حفظ فرمت‌بندی
                            // چون از wp_editor استفاده می‌شود، متن به صورت HTML ذخیره می‌شود
                            $rules_text = $data['rules'] ?? '';

                            // اگر متن خالی است، چیزی نمایش نده
                            if (!empty($rules_text)) {
                                // استفاده از wpautop برای تبدیل خطوط جدید به <p> و <br>
                                // این تابع HTML موجود را حفظ می‌کند (بولت‌ها، لینک‌ها و...)
                                $rules_text = wpautop($rules_text);
                                // تبدیل <br /> به <br> برای سازگاری
                                $rules_text = str_replace(['<br />', '<br/>'], '<br>', $rules_text);
                                // حذف <p> و </p> اضافی که ممکن است با line-clamp مشکل ایجاد کند
                                // اما محتوای داخل <p> را حفظ می‌کنیم
                                $rules_text = preg_replace('/<p[^>]*>/', '', $rules_text);
                                $rules_text = str_replace('</p>', '<br>', $rules_text);
                                // حذف <br> اضافی در ابتدا و انتها
                                $rules_text = trim($rules_text);
                                $rules_text = preg_replace('/^(<br\s*\/?>)+/', '', $rules_text);
                                $rules_text = preg_replace('/(<br\s*\/?>)+$/', '', $rules_text);

                                // اضافه کردن تیک مشکی در ابتدای هر خط
                                // تقسیم متن به خطوط بر اساس <br>
                                $lines = explode('<br>', $rules_text);
                                $lines_with_check = array();
                                $check_icon = '<svg class="rules-check-icon flex-shrink-0 mt-1" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.3333 4L6 11.3333L2.66667 8" stroke="#0F172B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (empty($line)) {
                                        $lines_with_check[] = '';
                                        continue;
                                    }
                                    // اگر خط شامل HTML خاص است (مثل <ul>, <li>, <ol> و...)، تیک را اضافه نکن
                                    if (preg_match('/^<(ul|ol|li|div|p|h[1-6]|strong|em|a|span)[\s>]/i', $line)) {
                                        $lines_with_check[] = $line;
                                        continue;
                                    }
                                    // اضافه کردن تیک مشکی در ابتدای خط
                                    $lines_with_check[] = '<span class="rules-line-item flex items-start gap-2">' . $check_icon . '<span class="flex-1">' . $line . '</span></span>';
                                }
                                $rules_text = implode('<br>', $lines_with_check);
                            }
                            ?>
                            <?php if (!empty($rules_text)): ?>
                                <div class="rules-text-content" data-full-text="<?= esc_attr($data['rules']) ?>">
                                    <div class="rules-text-display text-sm leading-7 font-medium line-clamp-3 child:!list-disc">
                                        <?= wp_kses_post($rules_text) ?>
                                    </div>
                                    <button type="button" class="toggle-rules-btn inline-flex items-center gap-1 text-[#5091FB] font-medium text-sm transition-colors mt-2 focus:outline-none hover:text-[#3F7FF5] hidden">
                                        <span class="toggle-text">مشاهده بیشتر</span>
                                        <svg class="chevron-icon w-3 h-3 transition-transform duration-300" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0.75 0.75L4.15 3.3C4.50556 3.56667 4.99444 3.56667 5.35 3.3L8.75 0.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        </svg>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <?php if ($data['criticism']): ?>
                    <section class="pt-4 rounded-2xl max-lg:rounded-none max-lg:shadow-none max-lg:border-0" id="criticism">
                        <div class="relative">
                            <div class="overflow-hidden transition-all delay-0 duration-[2000ms] ease-in-out">
                                <h2 class="!my-3 text-lg font-bold">نقد و بررسی</h2>
                                <div class="text-justify font-medium leading-[38px] text-sm criticism-content line-clamp-4">
                                    <?= $data['criticism'] ?>
                                </div>
                                <button type="button" class="criticism-read-more inline-flex items-center gap-1 text-[#889BAD] align-baseline mt-2">
                                    <span class="criticism-btn-text">بیشتر</span>
                                    <svg class="arrow-icon transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5" fill="none">
                                        <path d="M1 1.5L4.4 4.05C4.75556 4.31667 5.24444 4.31667 5.6 4.05L9 1.5" stroke="#09192D" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </section>

                    <script>
                        jQuery(document).ready(function($) {
                            $('.criticism-read-more').on('click', function() {
                                var content = $('.criticism-content');
                                var btn = $(this);
                                var btnText = btn.find('.criticism-btn-text');
                                var arrow = btn.find('.arrow-icon');

                                if (content.hasClass('line-clamp-4')) {
                                    content.removeClass('line-clamp-4');
                                    btnText.text('مشاهده کمتر');
                                    arrow.css('transform', 'rotate(180deg)');
                                } else {
                                    content.addClass('line-clamp-4');
                                    btnText.text('مشاهده بیشتر');
                                    arrow.css('transform', 'rotate(0deg)');
                                }
                            });
                        });
                    </script>
                <?php endif; ?>

                <script>
                    jQuery(document).ready(function($) {
                        // $.getJSON("https://api.ipify.org/?format=json", function(e){
                        //     $('<input type="hidden" id="user_ip">').val(e.ip).appendTo('body');
                        // });

                        $.getJSON("/ip.php", function(e){
                            $('<input type="hidden" id="user_ip">').val(e.ip).appendTo('body');
                        });

                        var temp = setInterval(function() {
                            if ($('#user_ip').length) {

                                if (typeof window.applyEzAjaxBoot === 'function') {
                                    window.applyEzAjaxBoot();
                                }
                                if (window.ezBookingApi?.productSetView) {
                                    window.ezBookingApi.productSetView(
                                        <?php echo (int) $product_id; ?>,
                                        $('#user_ip').val()
                                    );
                                }

                                clearInterval(temp);
                            }
                        }, 10);
                    });
                </script>

                <script>
                    jQuery(document).ready(function($) {
                        // تابع عمومی برای بررسی و نمایش دکمه "مشاهده بیشتر"
                        function checkContentOverflow(contentSelector, buttonSelector) {
                            var content = $(contentSelector);
                            var button = $(buttonSelector);

                            if (content.length && content[0].scrollHeight > content[0].clientHeight) {
                                // محتوا overflow دارد، دکمه را نمایش بده
                                button.show();
                            }
                        }

                        // بررسی محتوا بعد از render کامل
                        setTimeout(function() {
                            // بررسی بخش سناریو
                            checkContentOverflow('.scenario-content', '.scenario-read-more');

                            // بررسی بخش توضیحات
                            checkContentOverflow('.rules-content', '.rules-read-more');

                            // بررسی بخش نقد
                            checkContentOverflow('.criticism-content', '.criticism-read-more');
                        }, 100);

                        // رویداد کلیک برای بخش سناریو
                        $('.scenario-read-more').on('click', function() {
                            var content = $('.scenario-content');
                            var btn = $(this);
                            var btnText = btn.find('.btn-text');
                            var arrow = btn.find('.arrow-icon');

                            if (content.hasClass('line-clamp-3')) {
                                content.removeClass('line-clamp-3').css({
                                    'transition': 'max-height 0.5s ease-in-out',
                                    'max-height': '5000px'
                                });
                                btnText.text('مشاهده کمتر');
                                arrow.css('transform', 'rotate(180deg)');
                            } else {
                                content.addClass('line-clamp-3').css({
                                    'max-height': ''
                                });
                                btnText.text('مشاهده بیشتر');
                                arrow.css('transform', 'rotate(0deg)');

                                // اسکرول به بالای بخش
                                $('html, body').animate({
                                    scrollTop: content.offset().top - 100
                                }, 500);
                            }
                        });

                        // رویداد کلیک برای بخش توضیحات
                        $('.rules-read-more').on('click', function() {
                            var content = $('.rules-content');
                            var btn = $(this);
                            var btnText = btn.find('.btn-text');
                            var arrow = btn.find('.arrow-icon');

                            if (content.hasClass('line-clamp-3')) {
                                content.removeClass('line-clamp-3').css({
                                    'transition': 'max-height 0.5s ease-in-out',
                                    'max-height': '5000px'
                                });
                                btnText.text('مشاهده کمتر');
                                arrow.css('transform', 'rotate(180deg)');
                            } else {
                                content.addClass('line-clamp-3').css({
                                    'max-height': ''
                                });
                                btnText.text('مشاهده بیشتر');
                                arrow.css('transform', 'rotate(0deg)');

                                // اسکرول به بالای بخش
                                $('html, body').animate({
                                    scrollTop: content.offset().top - 100
                                }, 500);
                            }
                        });
                    });
                </script>

            </div>
        </section>
        <!-- -------------------------end-Description--------------------------------->



        <!---------------------------start-video--------------------------------------->
        <?php if ($has_video): ?>
            <div class="w-full h-[1px] bg-[#E4EBF0] my-5"></div>
            <section id="video-section" class="max-lg:relative max-lg:w-screen max-lg:left-1/2 max-lg:right-1/2 max-lg:-ml-[50vw] max-lg:-mr-[50vw]">
                <h2 class="text-xl font-black mb-4 max-lg:pr-6">ویدئو</h2>
                <div class="relative overflow-hidden <?php echo count($videos) > 1 ? 'embla_normal horizontal dragFree max-lg:pr-6' : '' ?>">
                    <div class="embla__viewport">
                        <div class="embla__container <?php echo count($videos) > 1 ? 'child:ml-5' : 'flex justify-center' ?>">
                            <?php foreach ($videos as $index => $video): ?>
                                <div class="video-item w-[305px] lg:w-[366px] h-[175px] lg:h-[210px] rounded-xl border overflow-hidden relative cursor-pointer shrink-0" data-video-index="<?= $index ?>">
                                    <div class="video-placeholder absolute inset-0 flex items-center justify-center" style="background-image: url('<?= esc_url($data['image']) ?>'); background-size: cover; background-position: center;">
                                        <!-- Overlay مشکی شفاف -->
                                        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
                                        <!-- محتوای روی overlay -->
                                        <div class="text-center relative z-10">
                                            <div class="mb-3">
                                                <svg class="w-16 h-16 mx-auto text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                            </div>
                                            <p class="text-white text-sm font-bold drop-shadow-lg"><?= $video['title'] ?></p>
                                        </div>
                                    </div>
                                    <div class="video-iframe-container absolute inset-0 hidden"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

            <script>
                jQuery(document).ready(function($) {
                    var videos = <?= json_encode($videos) ?>;
                    var activeIframe = null;
                    var isDragging = false;
                    var dragStartX = 0;
                    var dragStartY = 0;
                    var isTouchDrag = false;

                    // تابع برای استخراج video ID از URL های مختلف
                    function getYouTubeID(url) {
                        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                        var match = url.match(regExp);
                        return (match && match[2].length === 11) ? match[2] : null;
                    }

                    function getAparatID(url) {
                        var match = url.match(/aparat\.com\/v\/([a-zA-Z0-9]+)/);
                        return match ? match[1] : null;
                    }

                    // تابع برای متوقف کردن همه ویدیوها
                    function stopAllVideos() {
                        $('.video-iframe-container iframe').each(function() {
                            var iframe = $(this)[0];
                            try {
                                // برای YouTube
                                if (iframe.src && iframe.src.indexOf('youtube.com') !== -1) {
                                    iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                                }
                                // برای Aparat
                                if (iframe.src && iframe.src.indexOf('aparat.com') !== -1) {
                                    iframe.contentWindow.postMessage('{"event":"command","func":"pause","args":""}', '*');
                                }
                            } catch (e) {
                                // ignore cross-origin errors
                            }
                        });
                    }

                    // تابع برای حذف iframe
                    function removeIframe(container) {
                        container.html('').addClass('hidden');
                        container.siblings('.video-placeholder').removeClass('hidden');
                    }

                    // تابع برای مدیریت pointer-events روی iframe
                    function setIframePointerEvents(enabled) {
                        $('.video-iframe-container iframe').each(function() {
                            var $iframe = $(this);
                            var currentStyle = $iframe.attr('style') || '';

                            // به‌روزرسانی pointer-events در style
                            if (currentStyle.indexOf('pointer-events') !== -1) {
                                currentStyle = currentStyle.replace(/pointer-events:\s*[^;]+;?/g, '');
                            }

                            currentStyle += (currentStyle ? ' ' : '') + 'pointer-events: ' + (enabled ? 'auto' : 'none') + ';';
                            $iframe.attr('style', currentStyle);
                        });
                    }

                    // رویداد کلیک روی هر ویدیو
                    $('.video-item').on('click', function(e) {
                        // اگر در حال drag هستیم، کلیک را نادیده بگیر
                        if (isDragging || isTouchDrag) {
                            return;
                        }

                        // اگر روی iframe کلیک شده، اجازه بده iframe خودش رویداد را مدیریت کند
                        if ($(e.target).closest('.video-iframe-container').length > 0) {
                            return;
                        }

                        var $this = $(this);
                        var videoIndex = $this.data('video-index');
                        var video = videos[videoIndex];
                        var $placeholder = $this.find('.video-placeholder');
                        var $iframeContainer = $this.find('.video-iframe-container');

                        // اگر این ویدیو قبلاً لود شده، فقط play کن
                        if ($iframeContainer.find('iframe').length > 0) {
                            // فعال کردن pointer-events برای iframe تا دکمه‌ها کار کنند
                            setIframePointerEvents(true);
                            return;
                        }

                        // متوقف کردن همه ویدیوهای دیگر
                        stopAllVideos();

                        var embedHtml = (video.embed || video.url || '').trim();
                        var iframeUrl = '';

                        if (embedHtml) {
                            $placeholder.addClass('hidden');
                            $iframeContainer.removeClass('hidden').html(embedHtml);
                            activeIframe = $iframeContainer.find('iframe').get(0) || null;

                            // بعد از لود iframe، pointer-events را فعال کن
                            setTimeout(function() {
                                setIframePointerEvents(true);
                            }, 500);
                            return;
                        }

                        var videoUrl = video.url || '';

                        // تشخیص نوع ویدیو و ساخت iframe
                        if (videoUrl.indexOf('youtube.com') !== -1 || videoUrl.indexOf('youtu.be') !== -1) {
                            var videoId = getYouTubeID(videoUrl);
                            if (videoId) {
                                iframeUrl = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&enablejsapi=1';
                            }
                        } else if (videoUrl.indexOf('aparat.com') !== -1) {
                            var videoId = getAparatID(videoUrl);
                            if (videoId) {
                                iframeUrl = 'https://www.aparat.com/video/video/embed/videohash/' + videoId + '/vt/frame?autoplay=true';
                            }
                        } else if (videoUrl) {
                            iframeUrl = videoUrl + (videoUrl.indexOf('?') !== -1 ? '&' : '?') + 'autoplay=1';
                        }

                        if (iframeUrl) {
                            var iframe = $('<iframe>', {
                                src: iframeUrl,
                                frameborder: '0',
                                allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
                                allowfullscreen: true,
                                class: 'w-full h-full'
                            });

                            $placeholder.addClass('hidden');
                            $iframeContainer.html(iframe).removeClass('hidden');
                            activeIframe = iframe[0];

                            // بعد از لود iframe، pointer-events را فعال کن
                            iframe.on('load', function() {
                                setTimeout(function() {
                                    setIframePointerEvents(true);
                                }, 500);
                            });
                        }
                    });

                    // پیدا کردن container صحیح
                    var $videoSection = $('#video-section');
                    var scrollContainer = $videoSection.find('.embla__viewport').length > 0 ?
                        $videoSection.find('.embla__viewport') :
                        $videoSection.find('.embla__container').parent();

                    // رویداد scroll برای متوقف کردن ویدیوها
                    var scrollTimeout;
                    scrollContainer.on('scroll', function() {
                        clearTimeout(scrollTimeout);
                        scrollTimeout = setTimeout(function() {
                            stopAllVideos();
                        }, 150);
                    });

                    // افزودن قابلیت drag با موس (برای دسکتاپ)
                    var isDown = false;
                    var startX;
                    var scrollLeft;

                    scrollContainer.on('mousedown', function(e) {
                        // اگر روی iframe کلیک شده، drag نکن
                        if ($(e.target).closest('.video-iframe-container').length > 0) {
                            return;
                        }
                        isDown = true;
                        isDragging = false;
                        scrollContainer.addClass('dragging');
                        $videoSection.addClass('dragging');
                        startX = e.pageX - scrollContainer.offset().left;
                        scrollLeft = scrollContainer.scrollLeft();
                        // غیرفعال کردن pointer-events روی iframe هنگام drag
                        setIframePointerEvents(false);
                    });

                    scrollContainer.on('mouseleave mouseup', function() {
                        if (isDown) {
                            isDown = false;
                            scrollContainer.removeClass('dragging');
                            $videoSection.removeClass('dragging');
                            // فعال کردن دوباره pointer-events
                            setTimeout(function() {
                                setIframePointerEvents(true);
                            }, 100);
                        }
                    });

                    scrollContainer.on('mousemove', function(e) {
                        if (!isDown) return;
                        isDragging = true;
                        e.preventDefault();
                        var x = e.pageX - scrollContainer.offset().left;
                        var walk = (x - startX) * 2;
                        scrollContainer.scrollLeft(scrollLeft - walk);
                    });

                    // افزودن قابلیت drag با تاچ (برای موبایل)
                    var touchStartX = 0;
                    var touchStartY = 0;
                    var touchScrollLeft = 0;
                    var touchStartTime = 0;
                    var touchStartedOnIframe = false;
                    var touchDragTimeout = null;

                    scrollContainer.on('touchstart', function(e) {
                        var touch = e.originalEvent.touches[0];
                        touchStartX = touch.pageX;
                        touchStartY = touch.pageY;
                        touchScrollLeft = scrollContainer.scrollLeft();
                        touchStartTime = Date.now();
                        isTouchDrag = false;

                        // بررسی اینکه آیا تاچ روی iframe بوده یا نه
                        var $target = $(e.target);
                        var $iframeContainer = $target.closest('.video-iframe-container');
                        touchStartedOnIframe = $iframeContainer.length > 0 && $iframeContainer.find('iframe').length > 0;

                        if (touchStartedOnIframe) {
                            // اگر روی iframe تاچ شده، ابتدا pointer-events را فعال نگه دار
                            // تا دکمه‌ها کار کنند، اما اگر drag تشخیص داده شد، غیرفعال می‌کنیم
                            setIframePointerEvents(true);

                            // یک timeout برای تشخیص اینکه آیا drag است یا نه
                            touchDragTimeout = setTimeout(function() {
                                if (!isTouchDrag) {
                                    // اگر تا الان drag نشده، احتمالاً کلیک است
                                    // pointer-events را فعال نگه دار
                                    setIframePointerEvents(true);
                                }
                            }, 150);
                        } else {
                            // اگر روی iframe تاچ نشده، برای drag کاروسل آماده شو
                            scrollContainer.addClass('dragging');
                            $videoSection.addClass('dragging');
                            setIframePointerEvents(false);
                        }
                    });

                    scrollContainer.on('touchmove', function(e) {
                        var touch = e.originalEvent.touches[0];
                        var deltaX = Math.abs(touch.pageX - touchStartX);
                        var deltaY = Math.abs(touch.pageY - touchStartY);

                        // اگر حرکت افقی بیشتر از عمودی باشد و بیشتر از 15px، drag است
                        if (deltaX > 15 && deltaX > deltaY) {
                            isTouchDrag = true;

                            // اگر timeout تنظیم شده بود، آن را لغو کن
                            if (touchDragTimeout) {
                                clearTimeout(touchDragTimeout);
                                touchDragTimeout = null;
                            }

                            e.preventDefault();
                            var walk = (touch.pageX - touchStartX) * 1.5;
                            scrollContainer.scrollLeft(touchScrollLeft - walk);
                            scrollContainer.addClass('dragging');
                            $videoSection.addClass('dragging');

                            // اگر drag شروع شده، pointer-events را غیرفعال کن
                            setIframePointerEvents(false);
                        } else if (deltaY > 15) {
                            // اگر حرکت عمودی بیشتر باشد، scroll عمودی است
                            // اگر timeout تنظیم شده بود، آن را لغو کن
                            if (touchDragTimeout) {
                                clearTimeout(touchDragTimeout);
                                touchDragTimeout = null;
                            }

                            // برای scroll عمودی، pointer-events را فعال نگه دار
                            scrollContainer.removeClass('dragging');
                            $videoSection.removeClass('dragging');
                            setIframePointerEvents(true);
                        }
                    });

                    scrollContainer.on('touchend touchcancel', function(e) {
                        // اگر timeout تنظیم شده بود، آن را لغو کن
                        if (touchDragTimeout) {
                            clearTimeout(touchDragTimeout);
                            touchDragTimeout = null;
                        }

                        scrollContainer.removeClass('dragging');
                        $videoSection.removeClass('dragging');

                        var touchEndTime = Date.now();
                        var timeDiff = touchEndTime - touchStartTime;

                        // اگر drag انجام شده
                        if (isTouchDrag) {
                            isTouchDrag = false;
                            // بعد از کمی تاخیر، pointer-events را فعال کن
                            setTimeout(function() {
                                setIframePointerEvents(true);
                            }, 300);
                        } else if (touchStartedOnIframe) {
                            // اگر روی iframe تاچ شده بود و drag نشده، احتمالاً کلیک روی دکمه است
                            // pointer-events را فعال نگه دار
                            setIframePointerEvents(true);
                        } else {
                            // اگر drag نبوده و روی iframe هم نبوده، pointer-events را فعال کن
                            setTimeout(function() {
                                setIframePointerEvents(true);
                            }, 100);
                        }

                        touchStartedOnIframe = false;
                    });

                    // برای دکمه‌های داخل iframe، مطمئن شو که pointer-events فعال است
                    $(document).on('touchstart', '.video-iframe-container', function(e) {
                        // اگر روی iframe تاچ شده، pointer-events را فعال کن
                        setIframePointerEvents(true);
                    });
                });
            </script>

            <style>
                #video-section .embla__viewport {
                    cursor: grab;
                    -webkit-overflow-scrolling: touch;
                    overscroll-behavior-x: contain;
                    touch-action: pan-x;
                }

                #video-section .embla__viewport:active,
                #video-section .embla__viewport.dragging {
                    cursor: grabbing;
                }

                #video-section .embla__viewport::-webkit-scrollbar {
                    display: none;
                }

                .video-item {
                    transition: transform 0.2s ease;
                    touch-action: manipulation;
                }

                .video-item:hover {
                    transform: scale(1.02);
                }

                /* مدیریت pointer-events برای iframe */
                .video-iframe-container {
                    position: relative;
                    width: 100%;
                    height: 100%;
                    overflow: hidden;
                }

                /* Native Video Player Styles */
                .native-video-player-container {
                    position: relative;
                    width: 100%;
                    height: 100%;
                    background: #000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .native-video-player {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                    outline: none;
                }

                .native-video-player::-webkit-media-controls {
                    display: flex !important;
                }

                .native-video-player::-webkit-media-controls-panel {
                    background: rgba(0, 0, 0, 0.7);
                }

                .native-video-player::-webkit-media-controls-play-button {
                    display: flex;
                }

                /* برای موبایل: کنترل‌های بهتر */
                @media (max-width: 1024px) {
                    .native-video-player {
                        -webkit-tap-highlight-color: transparent;
                    }
                }

                .video-iframe-container iframe,
                .video-iframe {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    border: 0;
                    pointer-events: auto;
                    touch-action: manipulation;
                }

                /* حذف استایل‌های inline اضافی از embed های قدیمی */
                .video-iframe-container .r1_iframe_embed {
                    position: relative;
                    width: 100%;
                    height: 100%;
                    padding-top: 0 !important;
                }

                .video-iframe-container .r1_iframe_embed iframe {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    border: 0;
                }

                /* وقتی در حال drag هستیم، iframe نباید رویدادها را بگیرد */
                #video-section.dragging .video-iframe-container iframe,
                .embla__viewport.dragging .video-iframe-container iframe {
                    pointer-events: none !important;
                }

                /* برای موبایل: وقتی کاربر روی iframe تاچ می‌کند، باید بتواند با دکمه‌ها تعامل کند */
                @media (max-width: 1024px) {

                    .video-iframe-container iframe,
                    .video-iframe {
                        pointer-events: auto;
                    }
                }
            </style>
                    <div class="w-full h-[1px] bg-[#E4EBF0] my-5"></div>

        <?php endif; ?>
        <!---------------------------end-video------------------------------------------>




        <!------------------------start-comments----------------------------------------->
        <section id="comments-section">

            <h2 class="text-xl font-black">نظرات</h2>

            <div class="flex justify-between items-center mt-4">
                <div class="flex flex-col items-start">
                    <p class="text-base font-bold ">نظرشما برای ما مهم است.</p>
                    <p class="text-2xs font-bold text-[#62748E]">تجربه خود را به اشتراک بگذارید و امتیاز بگیرید. </p>
                </div>

                <a class="<?php echo is_user_logged_in() ? 'open-comments-btn' : 'need-login'; ?> flex items-center justify-center gap-2 bg-[#2B7FFF] py-3 w-full max-lg:max-w-[118px] lg:w-[225px] lg:max-w-[225px] lg:min-w-[225px] rounded-lg text-white">ثبت نظر
                    <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                        <g clip-path="url(#clip0_53025_21882)">
                            <path d="M12.1182 3.48608C7.50056 3.48608 4.01367 7.27168 4.01367 11.5945C4.01372 16.6199 8.12281 19.7244 12.1328 19.7244C13.4609 19.7243 14.9363 19.3713 16.1396 18.6609L16.1475 18.656C16.3399 18.539 16.4403 18.4881 16.5166 18.4646C16.5638 18.4501 16.5848 18.4501 16.623 18.4626L16.6426 18.4685L18.1211 18.907C18.5482 19.0408 19.0102 18.9521 19.3379 18.6501C19.6741 18.3402 19.7981 17.8714 19.6719 17.4167L19.668 17.4031L19.1738 15.7488L19.1689 15.7312L19.1621 15.7126C19.1561 15.6955 19.1554 15.683 19.1553 15.6765L19.1562 15.6746L19.166 15.657C19.8503 14.3982 20.2519 12.9992 20.252 11.6169C20.252 7.34683 16.8395 3.48626 12.1182 3.48608Z" stroke="#BEDBFF" stroke-width="1.5" />
                            <path d="M12.1477 12.5687C12.6709 12.5613 13.091 12.1405 13.091 11.6166C13.091 11.1 12.6636 10.6718 12.1477 10.6792C11.9036 10.6888 11.6727 10.7926 11.5034 10.9687C11.3341 11.1448 11.2396 11.3796 11.2396 11.6239C11.2396 11.8682 11.3341 12.103 11.5034 12.2792C11.6727 12.4553 11.9036 12.559 12.1477 12.5687Z" fill="#BEDBFF" />
                            <path d="M8.75044 12.5687C9.27367 12.5687 9.69372 12.1405 9.69372 11.6239C9.69372 11.1 9.27367 10.6792 8.75044 10.6792C8.50633 10.6888 8.27544 10.7926 8.10614 10.9687C7.93684 11.1448 7.84228 11.3796 7.84228 11.6239C7.84228 11.8682 7.93684 12.103 8.10614 12.2792C8.27544 12.4553 8.50633 12.559 8.75044 12.5687Z" fill="#BEDBFF" />
                            <path d="M16.4883 11.6239C16.4883 12.1413 16.0609 12.5687 15.545 12.5687C15.2952 12.5667 15.0561 12.4665 14.8796 12.2897C14.7031 12.1129 14.6033 11.8738 14.6017 11.6239C14.6017 11.1 15.0218 10.6792 15.545 10.6792C16.0682 10.6792 16.4883 11.1 16.4883 11.6239Z" fill="#BEDBFF" />
                            <circle cx="7.21094" cy="7.21094" r="7.21094" transform="matrix(-1 0 0 1 14.5 0.236084)" fill="white" />
                            <path d="M10.6543 8.29046V6.68887H8.02589V4.08203H6.46893V6.68887H3.92422V8.29046H6.46893V10.8121H8.02589V8.29046H10.6543Z" fill="#2B7FFF" />
                        </g>
                        <defs>
                            <clipPath id="clip0_53025_21882">
                                <rect width="21" height="21" fill="white" transform="matrix(-1 0 0 1 21 0.000488281)" />
                            </clipPath>
                        </defs>
                    </svg>
                </a>
            </div>




            <div class="w-full h-[1px] bg-[#E4EBF0] my-5"></div>

            <h2 class="text-lg font-bold mb-5">خلاصه نظرات</h2>

            <div class="w-full flex max-lg:flex-col justify-between lg:items-center py-5 px-8 lg:px-12 bg-[#F1F5F9] rounded-xl lg:gap-x-[150px]">
                <div class="flex justify-between items-center lg:hidden">
                    <div class="flex items-center gap-3">
                        <svg width="28" height="27" viewBox="0 0 28 27" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                            <path d="M12.0159 1.38193C12.6146 -0.460692 15.2214 -0.460687 15.8201 1.38193L17.7347 7.27454C18.0025 8.09859 18.7704 8.65651 19.6368 8.65651H25.8327C27.7701 8.65651 28.5757 11.1357 27.0082 12.2745L21.9957 15.9164C21.2947 16.4257 21.0014 17.3284 21.2692 18.1524L23.1838 24.0451C23.7825 25.8877 21.6735 27.4199 20.1061 26.2811L15.0935 22.6393C14.3926 22.13 13.4434 22.13 12.7424 22.6393L7.72984 26.2811C6.16241 27.4199 4.05346 25.8877 4.65216 24.0451L6.56679 18.1524C6.83453 17.3284 6.54122 16.4257 5.84024 15.9164L0.827687 12.2745C-0.739741 11.1357 0.0658154 8.65651 2.00326 8.65651H8.19912C9.06557 8.65651 9.83348 8.09859 10.1012 7.27454L12.0159 1.38193Z" fill="#EFC101" />
                        </svg>
                        <p class="text-4xl font-bold animate-counter" data-target="<?= $data['comments']['rate'] ?>" data-decimals="2">0.0</p>
                    </div>
                    <p class="text-[#62748E]">از <?php echo esc_html($data['comments']['comments_count']) ?>رای</p>
                </div>
                <div class="flex <?= ($product_type == 'اتاق فرار') ? ' flex-col ' : ' justify-between w-full ' ?> max-lg:hidden">
                    <div class="flex items-end justify-between gap-x-12.5">
                        <p class="text-2xl font-black">میانگین امتیاز</p>
                        <p class="text-[88px] lg:leading-[60px] font-extrabold animate-counter" data-target="<?= $data['comments']['rate'] ?>" data-decimals="2">0.0</p>
                    </div>
                    <div class="flex justify-between items-center lg:mt-4">
                        <p class="text-base font-bold text-[#62748E]"><?php echo $product_type . ' ' . $data['title'] ?> از <span class="mx-2"><?php echo esc_html($data['comments']['comments_count']) ?></span> رای</p>
                        <div class="flex flex-row-reverse items-center justify-end gap-0.5 max-lg:hidden" dir="ltr">
                            <?php for ($i = 0; $i < floor($data['comments']['rate']); $i++) { ?>
                                <span class="transition text-[#EFC101]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="14" viewBox="0 0 24 24">
                                        <path d="M17.918 14.32a1.1 1.1 0 00-.319.97l.89 4.92a1.08 1.08 0 01-.45 1.08 1.1 1.1 0 01-1.17.08l-4.43-2.31a1.13 1.13 0 00-.5-.13h-.27a.812.812 0 00-.27.09l-4.43 2.32c-.22.11-.468.15-.71.11a1.112 1.112 0 01-.89-1.27l.89-4.92a1.119 1.119 0 00-.32-.98l-3.61-3.5a1.08 1.08 0 01-.27-1.13c.134-.396.476-.685.89-.75l4.97-.72c.377-.04.71-.27.88-.61l2.19-4.49c.051-.1.118-.192.2-.27l.09-.07a.671.671 0 01.16-.13l.11-.04.17-.07h.42c.376.04.707.264.88.6l2.22 4.47c.16.327.47.554.83.61l4.97.72c.42.06.77.35.91.75.13.401.017.841-.29 1.13l-3.74 3.54z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            <?php }
                            if ($data['comments']['rate'] > floor($data['comments']['rate']) + .25) { ?>
                                <span class="transition text-[#EFC101]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 26 26" fill="none">
                                        <path d="M11.5734 4.39058C12.0224 3.00861 13.9776 3.00861 14.4266 4.39058L15.5819 7.9463C15.7827 8.56434 16.3587 8.98278 17.0085 8.98278H20.7472C22.2003 8.98278 22.8045 10.8422 21.6289 11.6963L18.6042 13.8939C18.0785 14.2758 17.8585 14.9529 18.0593 15.5709L19.2146 19.1266C19.6637 20.5086 18.0819 21.6578 16.9064 20.8037L13.8817 18.6061C13.3559 18.2242 12.6441 18.2242 12.1183 18.6061L9.09364 20.8037C7.91807 21.6578 6.33635 20.5086 6.78538 19.1266L7.9407 15.5709C8.14151 14.9529 7.92153 14.2758 7.3958 13.8939L4.37111 11.6963C3.19554 10.8422 3.79971 8.98278 5.25279 8.98278H8.9915C9.64134 8.98278 10.2173 8.56434 10.4181 7.9463L11.5734 4.39058Z" fill="#D5DCE1" />
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.0001 3.35547C12.399 3.35547 11.798 3.70096 11.5735 4.39194L10.4182 7.94767C10.2174 8.5657 9.64143 8.98414 8.99159 8.98414H5.25287C3.79979 8.98414 3.19563 10.8436 4.3712 11.6977L7.39588 13.8952C7.92161 14.2772 8.1416 14.9543 7.94079 15.5723L6.78546 19.128C6.33643 20.51 7.91815 21.6592 9.09372 20.8051L12.1184 18.6075C12.3813 18.4165 12.6907 18.321 13.0001 18.321V3.35547Z" fill="#EFC101" />
                                    </svg>
                                </span>
                            <?php }
                            for ($i = 0; $i < 5 - (floor($data['comments']['rate']) + 1); $i++) { ?>
                                <span class="transition text-[#D5DCE1]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="14" viewBox="0 0 24 24">
                                        <path d="M17.918 14.32a1.1 1.1 0 00-.319.97l.89 4.92a1.08 1.08 0 01-.45 1.08 1.1 1.1 0 01-1.17.08l-4.43-2.31a1.13 1.13 0 00-.5-.13h-.27a.812.812 0 00-.27.09l-4.43 2.32c-.22.11-.468.15-.71.11a1.112 1.112 0 01-.89-1.27l.89-4.92a1.119 1.119 0 00-.32-.98l-3.61-3.5a1.08 1.08 0 01-.27-1.13c.134-.396.476-.685.89-.75l4.97-.72c.377-.04.71-.27.88-.61l2.19-4.49c.051-.1.118-.192.2-.27l.09-.07a.671.671 0 01.16-.13l.11-.04.17-.07h.42c.376.04.707.264.88.6l2.22 4.47c.16.327.47.554.83.61l4.97.72c.42.06.77.35.91.75.13.401.017.841-.29 1.13l-3.74 3.54z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if ($product_type == 'اتاق فرار') { ?>
                    <div class="flex flex-col max-lg:mt-4">
                        <div class="max-lg:w-full lg:w-72">
                            <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                <span class="text-xs nowrap lg:text-sm">فضاسازی</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg animate-counter" data-target="<?php echo $data['comments']['rating_items'][1] ?>" data-decimals="1">0.0</span>
                                        <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                            <div class="h-1 rounded-full bg-[#2B7FFF] animate-progress" data-target-width="<?php echo $data['comments']['rating_items'][1] * 20 ?>" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                <span class="text-xs nowrap lg:text-sm">کیفیت معما</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg animate-counter" data-target="<?php echo $data['comments']['rating_items'][2] ?>" data-decimals="1">0.0</span>
                                        <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                            <div class="h-1 rounded-full bg-[#2B7FFF] animate-progress" data-target-width="<?php echo $data['comments']['rating_items'][2] * 20 ?>" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                <span class="text-xs nowrap lg:text-sm">تازگی و خلاقیت</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg animate-counter" data-target="<?php echo $data['comments']['rating_items'][3] ?>" data-decimals="1">0.0</span>
                                        <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                            <div class="h-1 rounded-full bg-[#2B7FFF] animate-progress" data-target-width="<?php echo $data['comments']['rating_items'][5] * 20 ?>" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                <span class="text-xs nowrap lg:text-sm">بازیگردانی و اکت</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg animate-counter" data-target="<?php echo $data['comments']['rating_items'][4] ?>" data-decimals="1">0.0</span>
                                        <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                            <div class="h-1 rounded-full bg-[#2B7FFF] animate-progress" data-target-width="<?php echo $data['comments']['rating_items'][3] * 20 ?>" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                <span class="text-xs nowrap lg:text-sm">برخورد پرسنل</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg animate-counter" data-target="<?php echo $data['comments']['rating_items'][5] ?>" data-decimals="1">0.0</span>
                                        <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                            <div class="h-1 rounded-full bg-[#2B7FFF] animate-progress" data-target-width="<?php echo $data['comments']['rating_items'][4] * 20 ?>" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="max-lg:hidden w-full h-[1px] bg-[#E4EBF0] my-5"></div>

            <h2 class="text-lg font-bold mb-4 mt-8">نظرات بازیکنان</h2>

            <div class="flex justify-start items-center">
                <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                    <path d="M16.5 11.3339L13.1667 14.6673M13.1667 14.6673L9.83333 11.3339M13.1667 14.6673V1.33392M9.83333 1.33392H1.5M9.83333 4.66726H4M9.83333 8.00059H6.5" stroke="#09192D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mr-2">مرتب سازی:</p>

                <div class="h-full w-[1px] bg-[#E2E8F0] mx-5"></div>

                <button type="button" class="sort-comments-btn active text-sm font-bold ml-8 pb-1 cursor-pointer" data-sort-type="newest">جدیدها</button>

                <button type="button" class="sort-comments-btn text-sm font-bold ml-8 pb-1 cursor-pointer" data-sort-type="pro">حرفه‌ای ها</button>
            </div>

            <!-------------comments-list----------->

            <div id="comments-list-container">
                <?php foreach ($data['comments']['items'] as $comment) {
                    // محاسبه rate
                    $rate = $comment['user_feeling'];
                    if ($product_type !== 'اتاق فرار') {
                        $rate = max($rate, 0.2);
                        $rate = ceil((float) $rate * 5);
                    } else {
                        $rate = max($rate, 1);
                        $rate = ceil((float) $rate);
                    }
                    $rate_str   = match ((string) $rate) {
                        '1' => 'خیلی بد',
                        '2' => 'خوب نبود',
                        '3' => 'معمولی بود',
                        '4' => 'خوب بود',
                        default => 'عالی بود',
                    };
                    $rate_color = match ((string) $rate) {
                        '1' => '#F21543',
                        '2' => '#FD7013',
                        '3' => '#BF9A00',
                        '4' => '#3F7FF5',
                        default => '#049654',
                    };
                    $rate_img   = match ((string) $rate) {
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        default => '5',
                    };
                ?>

                    <div class="border border-[#E4EBF0] flex justify-between items-start rounded-2xl max-lg:pt-7 lg:p-7 mt-8">
                        <div class="flex flex-col w-full">
                            <div class="flex justify-between items-center max-lg:px-7">
                                <?php if ( ! empty( $comment['author_id'] ) ) : ?>
                                    <a href="<?php echo esc_url( home_url( '/profile/' . (int) $comment['author_id'] ) ); ?>" class="user-profile-link flex items-center gap-3 relative">
                                        <div class="w-[40px] h-[40px] rounded-xl overflow-hidden bg-[#EEE8E8] flex items-center justify-center">
                                            <?php
                                            echo get_avatar(
                                                (int) $comment['author_id'],
                                                64,
                                                '',
                                                $comment['author_title'],
                                                ['class' => 'w-full h-full object-cover rounded-xl']
                                            );
                                            ?>
                                        </div>
                                        <p class="text-sm font-bold"><?php echo esc_html( $comment['author_title'] ); ?></p>
                                    </a>
                                <?php else : ?>
                                    <div class="flex items-center gap-3">
                                        <div class="w-[40px] h-[40px] rounded-xl overflow-hidden bg-[#EEE8E8] flex items-center justify-center">
                                            <?php
                                            echo get_avatar(
                                                $comment['author_title'],
                                                64,
                                                '',
                                                $comment['author_title'],
                                                ['class' => 'w-full h-full object-cover rounded-xl']
                                            );
                                            ?>
                                        </div>
                                        <p class="text-sm font-bold"><?php echo esc_html( $comment['author_title'] ); ?></p>
                                    </div>
                                <?php endif; ?>
                                <span class="max-lg:text-base">
                                    <?php if ($comment['author_id']) {
                                        if ($comment['date'] > COMMENT_NEW_VER_TIMESTAMP) {
                                            if ( function_exists( 'ez_comment_badge_by_stored_level' ) ) {
                                                ez_comment_badge_by_stored_level( (int) $comment['author_id'], 'px-2 py-0.5 text-xs font-bold mr-2.5 rounded-xl', (int) ( $comment['stored_user_level'] ?? 0 ) );
                                            } else {
                                                user_badge_by_level( 1, 'px-2 py-0.5 text-xs font-bold mr-2.5 rounded-xl', 'user_level' );
                                            }
                                        } else {
                                            user_badge_by_level(0, 'px-2 py-0.5 text-xs font-bold text-white mr-2.5 rounded-xl');
                                        }
                                    } ?>
                                </span>
                            </div>

                            <div class="w-full h-[1px] bg-[#E4EBF0] my-3"></div>

                            <div class="comment-item max-lg:px-7">
                                <?php
                                // محاسبه هوشمند طول نمایش
                                $content_length = mb_strlen($comment['content']);

                                // Desktop: base 500, threshold 530, min hidden 30%
                                $desktop_base = 500;
                                $desktop_threshold = 530;
                                $desktop_show_length = $desktop_base;
                                $desktop_has_long_text = false;
                                $desktop_needs_toggle = false;

                                if ($content_length > $desktop_threshold) {
                                    $desktop_show_length = floor($content_length * 0.7); // نمایش 70%، مخفی 30%
                                    $desktop_has_long_text = true;
                                    $desktop_needs_toggle = true;
                                }

                                // Mobile: base 200, threshold 230, min hidden 30%
                                // در موبایل همیشه دکمه بیشتر داریم (برای نمایش scores)
                                $mobile_base = 200;
                                $mobile_threshold = 230;
                                $mobile_show_length = $mobile_base;
                                $mobile_has_long_text = false;

                                if ($content_length > $mobile_threshold) {
                                    $mobile_show_length = floor($content_length * 0.7); // نمایش 70%، مخفی 30%
                                    $mobile_has_long_text = true;
                                }

                                // در موبایل همیشه دکمه نمایش داده میشه (برای scores)
                                $mobile_needs_toggle = ($product_type == 'اتاق فرار');
                                ?>

                                <!-- Desktop version -->
                                <p class="comment-text-content hidden lg:block text-base leading-9"
                                    data-full-text="<?php echo esc_attr($comment['content']); ?>"
                                    data-show-length="<?php echo $desktop_show_length; ?>"
                                    data-has-long-text="<?php echo $desktop_has_long_text ? 'true' : 'false'; ?>">
                                    <span class="comment-text-display">
                                        <?php
                                        if ($desktop_needs_toggle) {
                                            echo esc_html(mb_substr($comment['content'], 0, $desktop_show_length)) . '...';
                                        } else {
                                            echo esc_html($comment['content']);
                                        }
                                        ?>
                                    </span>
                                    <?php if ($desktop_needs_toggle) { ?>
                                        <button class="toggle-comment-btn inline-flex items-center gap-1 text-[#889BAD] font-medium text-sm transition-colors ml-1 focus:outline-none hover:text-[#5091FB]">
                                            <span class="toggle-text">بیشتر</span>
                                            <svg class="chevron-icon w-3 h-3 transition-transform duration-300" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.75 0.75L4.15 3.3C4.50556 3.56667 4.99444 3.56667 5.35 3.3L8.75 0.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                            </svg>
                                        </button>
                                    <?php } ?>
                                </p>

                                <!-- Mobile version -->
                                <p class="comment-text-content lg:hidden text-sm leading-9"
                                    data-full-text="<?php echo esc_attr($comment['content']); ?>"
                                    data-show-length="<?php echo $mobile_show_length; ?>"
                                    data-has-long-text="<?php echo $mobile_has_long_text ? 'true' : 'false'; ?>">
                                    <span class="comment-text-display">
                                        <?php
                                        if ($mobile_has_long_text) {
                                            echo esc_html(mb_substr($comment['content'], 0, $mobile_show_length)) . '...';
                                        } else {
                                            echo esc_html($comment['content']);
                                        }
                                        ?>
                                    </span>
                                    <?php if ($mobile_needs_toggle) { ?>
                                        <button class="toggle-comment-btn inline-flex items-center gap-1 text-[#889BAD] font-medium text-sm transition-colors ml-1 focus:outline-none hover:text-[#5091FB]">
                                            <span class="toggle-text">بیشتر</span>
                                            <svg class="chevron-icon w-3 h-3 transition-transform duration-300" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.75 0.75L4.15 3.3C4.50556 3.56667 4.99444 3.56667 5.35 3.3L8.75 0.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                            </svg>
                                        </button>
                                    <?php } ?>
                                </p>

                                <?php if ($product_type == 'اتاق فرار') { ?>
                                    <div class="scores-section lg:hidden overflow-hidden transition-all duration-500 ease-in-out max-h-0 opacity-0">
                                        <div class="w-full h-[1px] bg-[#E4EBF0] mb-4"></div>
                                        <div class="flex flex-col space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs lg:text-sm whitespace-nowrap">فضاسازی</span>
                                                <span class="min-w-36">
                                                    <div class="flex items-center justify-center" dir="ltr">
                                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1094]; ?></span>
                                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                                            <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1094] * 20; ?>%;"></div>
                                                        </div>
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs lg:text-sm whitespace-nowrap">کیفیت معما</span>
                                                <span class="min-w-36">
                                                    <div class="flex items-center justify-center" dir="ltr">
                                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1095]; ?></span>
                                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                                            <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1095] * 20; ?>%;"></div>
                                                        </div>
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs lg:text-sm whitespace-nowrap">تازگی و خلاقیت</span>
                                                <span class="min-w-36">
                                                    <div class="flex items-center justify-center" dir="ltr">
                                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1098]; ?></span>
                                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                                            <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1098] * 20; ?>%;"></div>
                                                        </div>
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs lg:text-sm whitespace-nowrap">بازیگردانی و اکت</span>
                                                <span class="min-w-36">
                                                    <div class="flex items-center justify-center" dir="ltr">
                                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1096]; ?></span>
                                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                                            <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1096] * 20; ?>%;"></div>
                                                        </div>
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs lg:text-sm whitespace-nowrap">برخورد پرسنل</span>
                                                <span class="min-w-36">
                                                    <div class="flex items-center justify-center" dir="ltr">
                                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1097]; ?></span>
                                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                                            <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1097] * 20; ?>%;"></div>
                                                        </div>
                                                    </div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="flex justify-between items-center border-t botder-t-[#E4EBF0] lg:border lg:border-[#E4EBF0] lg:rounded-xl p-2 lg:my-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?php bloginfo('template_url'); ?>/assets/images/emojis/<?= $rate_img ?>.png" draggable="false" alt="<?php echo $rate; ?>" class="w-8 h-auto">
                                    <p class="text-sm font-bold" style="color: <?= esc_attr($rate_color) ?>"><?php echo esc_html($rate_str) ?></p>
                                </div>
                                <p class="text-xs font-bold text-[#889BAD]"><?php echo human_time_diff($comment['date'], current_time('U')) ?> پیش</p>
                            </div>

                            <?php if ($comment['reply']) { ?>
                                <div class="bg-[#F3F9FC] rounded-lg flex flex-col p-2 max-lg:hidden">
                                    <p class="text-sm font-black">پاسخ مجموعه</p>
                                    <div class="flex mt-3">
                                        <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0 ml-3 w-4 h-[14px]">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.707C5.89426 13.5194 5.99957 13.2651 5.99957 13C5.99957 12.7348 5.89426 12.4805 5.70679 12.293L3.41379 9.99997H8.99979C10.8563 9.99997 12.6368 9.26247 13.9495 7.94972C15.2623 6.63696 15.9998 4.85649 15.9998 2.99997V0.999969C15.9998 0.734753 15.8944 0.480398 15.7069 0.292862C15.5194 0.105326 15.265 -3.05176e-05 14.9998 -3.05176e-05C14.7346 -3.05176e-05 14.4802 0.105326 14.2927 0.292862C14.1051 0.480398 13.9998 0.734753 13.9998 0.999969V2.99997C13.9998 4.32605 13.473 5.59782 12.5353 6.5355C11.5976 7.47319 10.3259 7.99997 8.99979 7.99997H3.41379L5.70679 5.70697C5.8023 5.61472 5.87848 5.50438 5.93089 5.38237C5.9833 5.26037 6.01088 5.12915 6.01204 4.99637C6.01319 4.86359 5.98789 4.73191 5.93761 4.60902C5.88733 4.48612 5.81307 4.37447 5.71918 4.28057C5.62529 4.18668 5.51364 4.11243 5.39074 4.06215C5.26784 4.01187 5.13616 3.98656 5.00339 3.98772C4.87061 3.98887 4.73939 4.01646 4.61738 4.06887C4.49538 4.12128 4.38503 4.19746 4.29279 4.29297L0.292786 8.29297C0.105315 8.4805 0 8.73481 0 8.99997C0 9.26513 0.105315 9.51944 0.292786 9.70697L4.29279 13.707C4.48031 13.8944 4.73462 13.9998 4.99979 13.9998C5.26495 13.9998 5.51926 13.8944 5.70679 13.707Z" fill="#889BAD"></path>
                                        </svg>
                                        <p class="text-[#62748E]"><?= $comment['reply']; ?></p>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <?php if ($product_type == 'اتاق فرار') { ?>
                            <div class="h-full w-[1px] bg-[#E4EBF0] mx-7 max-lg:hidden"></div>

                            <div class="flex flex-col max-lg:hidden">
                                <div class="max-lg:w-full lg:w-72">
                                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                        <span class="text-xs nowrap lg:text-sm">فضاسازی</span>
                                        <span class="min-w-36">
                                            <div class="flex items-center justify-center" dir="ltr">
                                                <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1094]; ?></span>
                                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                                    <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1094] * 20; ?>%;"></div>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                        <span class="text-xs nowrap lg:text-sm">کیفیت معما</span>
                                        <span class="min-w-36">
                                            <div class="flex items-center justify-center" dir="ltr">
                                                <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1095]; ?></span>
                                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                                    <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1095] * 20; ?>%;"></div>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                        <span class="text-xs nowrap lg:text-sm">تازگی و خلاقیت</span>
                                        <span class="min-w-36">
                                            <div class="flex items-center justify-center" dir="ltr">
                                                <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1098]; ?></span>
                                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                                    <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1098] * 20; ?>%;"></div>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                        <span class="text-xs nowrap lg:text-sm">بازیگردانی و اکت</span>
                                        <span class="min-w-36">
                                            <div class="flex items-center justify-center" dir="ltr">
                                                <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1096]; ?></span>
                                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                                    <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1096] * 20; ?>%;"></div>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                                        <span class="text-xs nowrap lg:text-sm">برخورد پرسنل</span>
                                        <span class="min-w-36">
                                            <div class="flex items-center justify-center" dir="ltr">
                                                <span class="mr-2.5 min-w-9 text-lg"><?php echo $comment['rating_items'][1097]; ?></span>
                                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                                    <div class="h-1 rounded-full bg-[#2B7FFF]" style="width: <?php echo $comment['rating_items'][1097] * 20; ?>%;"></div>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ($comment['reply']) { ?>
                        <div class="bg-[#F3F9FC] rounded-lg flex flex-col p-2 lg:hidden max-lg:mt-5">
                            <p class="text-sm font-extrabold">پاسخ مجموعه</p>
                            <div class="flex mt-3">
                                <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0 ml-3 w-4 h-[14px]">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.707C5.89426 13.5194 5.99957 13.2651 5.99957 13C5.99957 12.7348 5.89426 12.4805 5.70679 12.293L3.41379 9.99997H8.99979C10.8563 9.99997 12.6368 9.26247 13.9495 7.94972C15.2623 6.63696 15.9998 4.85649 15.9998 2.99997V0.999969C15.9998 0.734753 15.8944 0.480398 15.7069 0.292862C15.5194 0.105326 15.265 -3.05176e-05 14.9998 -3.05176e-05C14.7346 -3.05176e-05 14.4802 0.105326 14.2927 0.292862C14.1051 0.480398 13.9998 0.734753 13.9998 0.999969V2.99997C13.9998 4.32605 13.473 5.59782 12.5353 6.5355C11.5976 7.47319 10.3259 7.99997 8.99979 7.99997H3.41379L5.70679 5.70697C5.8023 5.61472 5.87848 5.50438 5.93089 5.38237C5.9833 5.26037 6.01088 5.12915 6.01204 4.99637C6.01319 4.86359 5.98789 4.73191 5.93761 4.60902C5.88733 4.48612 5.81307 4.37447 5.71918 4.28057C5.62529 4.18668 5.51364 4.11243 5.39074 4.06215C5.26784 4.01187 5.13616 3.98656 5.00339 3.98772C4.87061 3.98887 4.73939 4.01646 4.61738 4.06887C4.49538 4.12128 4.38503 4.19746 4.29279 4.29297L0.292786 8.29297C0.105315 8.4805 0 8.73481 0 8.99997C0 9.26513 0.105315 9.51944 0.292786 9.70697L4.29279 13.707C4.48031 13.8944 4.73462 13.9998 4.99979 13.9998C5.26495 13.9998 5.51926 13.8944 5.70679 13.707Z" fill="#889BAD"></path>
                                </svg>
                                <p class="text-[#62748E]"><?= $comment['reply']; ?></p>
                            </div>
                        </div>
                    <?php } ?>

                <?php } ?>
            </div>

            <?php if ($data['comments']['total_pages'] > 1) { ?>
                <div class="flex items-center justify-center gap-2 mt-8 cursor-pointer" id="load-more-comments" data-current-page="1" data-total-pages="<?php echo $data['comments']['total_pages']; ?>" data-product-id="<?php echo $product_id; ?>" data-product-type="<?php echo $product_type; ?>">
                    <p class="text-[#3F7FF5] font-bold">مشاهده بیشتر</p>
                    <svg width="11" height="6" viewBox="0 0 11 6" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                        <path d="M1.5 1.5L4.9 4.05C5.25556 4.31667 5.74444 4.31667 6.1 4.05L9.5 1.5" stroke="#3F7FF5" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </div>
            <?php } ?>


            <input type="hidden" id="comments-current-page" value="1">
            <input type="hidden" id="comments-sort-type" value="newest">
            <input type="hidden" id="comments-total-pages" value="<?php echo $data['comments']['total_pages']; ?>">
            <input type="hidden" id="comments-product-id" value="<?php echo $product_id; ?>">
            <input type="hidden" id="comments-product-type" value="<?php echo $product_type; ?>">
        </section>
        <!------------------------end-comments--------------------------------------------------------->


        <div class="w-full h-[1px] my-10 bg-[#E4EBF0]"></div>

        <!-----------------------start-slider-games---------------------------------------------------->
        <?php if ($data['brand_products']->products && $data['active']): ?>
            <div class="<?php if (!$tags): ?>max-lg:mb-12<?php endif; ?> max-lg:w-screen max-lg:relative max-lg:ml-[-50vw] max-lg:mr-[-50vw] max-lg:left-1/2 max-lg:right-1/2 max-lg:pr-7" id="related">
                <div class="mb-6 md:mb-8">
                    <h2 class="text-base md:text-lg font-bold">
                        سایر بازی‌های
                        <span class="mx-2 text-sm text-[#62748E] font-bold"><?php echo esc_html($data['brand']['title']); ?></span>
                    </h2>
                </div>
                <div class="relative overflow-hidden embla_normal horizontal dragFree" style="max-width: 960px;">
                    <div class="embla__viewport">
                        <div class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:max-w-[156px] md:child:max-w-[200px] child:py-2.5"> <?= $data['brand_products']->products ?> </div>
                    </div>
                    <button class="embla__button embla__button--prev absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none max-lg:hidden -mr-px hidden" type="button">
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
                    <button class="embla__button embla__button--next absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none max-lg:hidden -ml-px hidden" type="button">
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
        <?php endif; ?>
        <!-----------------------end-slider-games------------------------------------------------------>


        <!----------------------start-Related pages---------------------------------------------------->
        <?php if ($tags): ?>
            <div class="w-full h-[1px] my-10 bg-[#E4EBF0]"></div>
            <h2 class="text-xl font-black">صفحات مرتبط</h2>

            <div class="flex gap-3 mt-5 max-lg:mb-12 flex-wrap">
                <?php foreach ($tags as $tag): ?>
                    <a href="<?= $tag['url'] ?>" class="p-2 rounded-lg bg-[#F1F5F9] text-sm font-bold"><?= $tag['title'] ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!----------------------end-Related pages------------------------------------------------------>

    </div>
    <!--------------------------end-right-section----------------------------------------------------------------------------->


    <!--------------------------start-left-section---------------------------------------------------------------------------->
    <!----------------start-Calendar--------------------------------------->
    <div class="flex flex-col min-w-[374px] max-w-[374px] mt-[53px] max-lg:hidden">
        <?php if ($data['active']) { ?>
            <div class="sticky z-10 top-4">
                <div class="flex items-center justify-between gap-2 date-scroll-container-desktop">
                    <div id="today-btn-desktop" class="bg-[#5091FB] w-[60px] h-[60px] rounded-lg text-white flex justify-center items-center flex-shrink-0 cursor-pointer text-sm font-bold">
                        امروز
                    </div>
                    <div class="embla-dates-desktop overflow-hidden flex-1">
                        <div class="embla__container date-scroll-list-desktop">

                        </div>
                    </div>

                    <a href="<?php echo site_url('/r/' . $data['product_id']); ?>" id="show-all-dates-desktop" class="w-[85px] h-[61px] rounded-lg bg-[#F1F5F9] py-2 flex-shrink-0 cursor-pointer flex flex-col justify-center items-center" target="_blank">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.3809 4.74667V3C22.3809 2.73478 22.2756 2.48043 22.0881 2.29289C21.9005 2.10536 21.6462 2 21.3809 2C21.1157 2 20.8614 2.10536 20.6738 2.29289C20.4863 2.48043 20.3809 2.73478 20.3809 3V4.66667H11.3333V3C11.3333 2.73478 11.228 2.48043 11.0404 2.29289C10.8529 2.10536 10.5985 2 10.3333 2C10.0681 2 9.81376 2.10536 9.62623 2.29289C9.43869 2.48043 9.33333 2.73478 9.33333 3V4.74667C7.8471 4.98704 6.49472 5.74794 5.51775 6.89344C4.54077 8.03895 4.00283 9.49446 4 11V22.6667C4 23.4984 4.16382 24.3219 4.4821 25.0903C4.80038 25.8587 5.26689 26.5569 5.85499 27.145C7.04272 28.3327 8.65363 29 10.3333 29H21.3809C22.2127 29 23.0362 28.8362 23.8046 28.5179C24.573 28.1996 25.2712 27.7331 25.8593 27.145C26.4474 26.5569 26.9139 25.8587 27.2322 25.0903C27.5505 24.3219 27.7143 23.4984 27.7143 22.6667V11C27.7115 9.49446 27.1735 8.03895 26.1965 6.89344C25.2196 5.74794 23.8672 4.98704 22.3809 4.74667ZM25.7143 12.6667H6V11C6.00131 10.0268 6.33119 9.08255 6.93619 8.32026C7.54119 7.55796 8.38586 7.02227 9.33333 6.8V8.33333C9.33333 8.59855 9.43869 8.8529 9.62623 9.04044C9.81376 9.22798 10.0681 9.33333 10.3333 9.33333C10.5985 9.33333 10.8529 9.22798 11.0404 9.04044C11.228 8.8529 11.3333 8.59855 11.3333 8.33333V6.66667H20.3809V8.33333C20.3809 8.59855 20.4863 8.8529 20.6738 9.04044C20.8614 9.22798 21.1157 9.33333 21.3809 9.33333C21.6462 9.33333 21.9005 9.22798 22.0881 9.04044C22.2756 8.8529 22.3809 8.59855 22.3809 8.33333V6.8C23.3284 7.02227 24.1731 7.55796 24.7781 8.32026C25.3831 9.08255 25.713 10.0268 25.7143 11V12.6667Z" fill="#5091FB" />
                            <circle cx="11.1409" cy="18.1429" r="1.14286" fill="white" />
                            <circle cx="11.1409" cy="22.7143" r="1.14286" fill="white" />
                            <circle cx="15.7132" cy="18.1429" r="1.14286" fill="white" />
                            <circle cx="15.7132" cy="22.7143" r="1.14286" fill="white" />
                            <circle cx="20.2835" cy="18.1429" r="1.14286" fill="white" />
                            <circle cx="20.2835" cy="22.7143" r="1.14286" fill="white" />
                        </svg>
                        <p class="text-xs font-bold text-center">مشاهده همه</p>
                    </a>
                </div>

                <!-- Session Info -->
                <div id="sessions-info-desktop" class="flex items-center mb-4 mt-3">
                    <h2 class="text-xs text-blue">در حال بارگذاری...</h2>
                </div>

                <div class="sessions-embla-container-desktop" data-min="<?php echo $data['number_min']; ?>" data-max="<?php echo $data['number_max']; ?>"></div>

                <!-- Quantity Box (نمایش داده میشه وقتی روی سانس کلیک شد) -->
                <div id="quantity-box-desktop" class="quantity-box hidden">
                    <!-- محتوا به صورت داینامیک لود میشه -->
                </div>

                <!-- Review Box -->
                <div id="review-box-desktop" class="hidden justify-between items-center w-full border rounded-xl p-5 mt-7">
                    <!-- محتوا به صورت داینامیک لود میشه -->
                </div>

                <!-- Payment Button -->
                <a href="#" id="go-to-checkout-desktop" class="hidden items-center justify-center text-white bg-[#02C96F] w-full gap-4 rounded-xl py-4 px-5 mt-7 text-lg font-black shadow-[0_2px_0_#01B061] transition hover:bg-[#01B061]">
                    پرداخت و ثبت رزرو
                    <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-0">
                        <path d="M0.402124 5.33532C0.144631 5.58141 7.88412e-08 5.91501 7.46935e-08 6.26282C7.05459e-08 6.61063 0.144631 6.94422 0.402124 7.19032L5.58679 12.1419C5.84474 12.3882 6.19458 12.5265 6.55937 12.5265C6.92416 12.5265 7.27401 12.3882 7.53196 12.1419C7.7899 11.8957 7.93481 11.5618 7.93481 11.2136C7.93481 10.8654 7.7899 10.5314 7.53196 10.2852L4.69396 7.57532L13.801 7.57532C14.1657 7.57532 14.5154 7.43704 14.7733 7.1909C15.0312 6.94476 15.176 6.61092 15.176 6.26282C15.176 5.91472 15.0312 5.58088 14.7733 5.33474C14.5154 5.0886 14.1657 4.95032 13.801 4.95032L4.69396 4.95032L7.53196 2.24132C7.65968 2.1194 7.76099 1.97467 7.83011 1.81538C7.89924 1.65609 7.93481 1.48536 7.93481 1.31294C7.93481 1.14053 7.89924 0.969802 7.83011 0.810511C7.76099 0.65122 7.65968 0.506484 7.53196 0.384568C7.40424 0.262652 7.25261 0.165945 7.08573 0.0999652C6.91886 0.0339843 6.74 2.39222e-05 6.55937 2.39201e-05C6.37875 2.39179e-05 6.19989 0.0339843 6.03302 0.0999652C5.86614 0.165945 5.71451 0.262652 5.58679 0.384568L0.402124 5.33532Z" fill="white" />
                    </svg>
                </a>
            </div>
        <?php } else { ?>
            <div class="">
                <div class="border rounded-2xl bg-white p-6 text-center shadow-13">
                    <p class="text-lg font-black text-[#0F172B] mb-3">رزرو این بازی فعال نیست</p>
                    <span class="inline-flex items-center justify-center px-4 py-1 text-xs font-extrabold text-white rounded-md mb-3" style="background-color: <?= $sale_status_color ?: '#F21543' ?>;">
                        <?= $sale_status_title ?: 'رزرو غیرفعال' ?>
                    </span>
                    <p class="text-sm font-medium text-[#62748E]">به محض فعال شدن دوباره، سانس‌ها در این بخش نمایش داده می‌شوند.</p>
                </div>
            </div>

            <?php if ($data['brand_products']->products): ?>
                <section class="max-w-full py-4 md:py-5 lg:py-9 md:mt-7.5">
                    <div class="mb-6 md:mb-8">
                        <div class="flex justify-between">
                            <div class="items-center gap-6 md:flex">
                                <div class="flex items-center gap-4">
                                    <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9"
                                            fill="none">
                                            <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                                fill="#09192D" stroke="white" stroke-width="1.5" />
                                        </svg>
                                    </div>
                                    <h2 class="text-base font-bold md:text-lg">
                                        سایر بازی‌های
                                        <span class="text-2xs"><?= $data['brand']['title'] ?></span>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative overflow-hidden embla_normal horizontal dragFree">
                        <div class="embla__viewport">
                            <div class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:max-w-[156px] md:child:max-w-[200px] child:py-2.5"> <?= $data['brand_products']->products ?> </div>
                        </div>
                        <button class="embla__button embla__button--prev absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                        <button class="embla__button embla__button--next absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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

            <?php
            $genres = [];
            $hood = null;
            $genre = null;
            foreach (get_the_terms($product_id, 'product_tag') as $product_tag)
                if (str_contains($product_tag->name, '|||||'))
                    $genre = $product_tag->term_id;
                else
                    $hood = $product_tag->term_id;

            if ($hood || $genre) {

                if ($hood) {

                    $params = [
                        'tag' => [$hood],
                    ];
                } else if ($genre) {
                    $params = [
                        'tag' => [$genre],
                    ];
                }

                $params['city_id'] = -1;
                $args = [
                    'params'    => $params,
                    'format'    => 'html_swiper',
                    'sort_type' => 'hottest',
                    'limit'     => 5,
                    'random'    => true,
                ];
                $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)))->products;
            }

            if ($products) : ?>
                <section class="mt-8">
                    <div class="mb-6 md:mb-8">
                        <div class="flex justify-between">
                            <div class="items-center gap-6 md:flex">
                                <div class="flex items-center gap-4">
                                    <div class="mb-1 rounded md:rounded-xl text-primaryColor bg-primaryColor aspect-square flex items-center justify-center px-0.5 md:p-2 shadow-4 max-md:w-5 max-md:h-5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="9" viewBox="0 0 17 9"
                                            fill="none">
                                            <path d="M14.9168 5.56397L14.9169 4.81397L14.1669 4.81385L12.5397 4.81359L11.7897 4.81347L11.7896 5.56347L11.7892 7.4979C11.7867 7.57881 11.7531 7.65527 11.6961 7.71146C11.638 7.76865 11.5603 7.80032 11.4798 7.80031C11.3993 7.8003 11.3217 7.7686 11.2636 7.71139C11.2066 7.65518 11.1731 7.57872 11.1705 7.49782L11.1708 5.56337L11.1709 4.81337L10.4209 4.81325L8.84163 4.81299L8.26025 4.8129L8.11522 5.3759C7.90262 6.20117 7.39731 6.91985 6.6946 7.39757C5.99195 7.87526 5.14 8.07939 4.29837 7.97207C3.45672 7.86474 2.68256 7.45323 2.1212 6.81409C1.5598 6.17488 1.24986 5.35193 1.25 4.49944C1.25014 3.64695 1.56033 2.8241 2.12194 2.18508C2.6835 1.54611 3.45779 1.13485 4.29947 1.02779C5.14113 0.920743 5.99302 1.12515 6.69552 1.60305C7.39808 2.081 7.90317 2.79984 8.1155 3.62518L8.26035 4.18823L8.84173 4.18832L15.2263 4.18934C15.2263 4.18934 15.2264 4.18934 15.2264 4.18934C15.3078 4.1894 15.3862 4.22182 15.4443 4.28016L15.9755 3.75066L15.4443 4.28016C15.5026 4.33858 15.5357 4.41823 15.5357 4.50173C15.5357 4.50177 15.5357 4.5018 15.5357 4.50184L15.5352 7.4985C15.5326 7.57941 15.4991 7.65587 15.442 7.71206L15.9684 8.24633L15.442 7.71207C15.384 7.76925 15.3063 7.80092 15.2258 7.80091C15.1453 7.8009 15.0677 7.7692 15.0096 7.712C14.9526 7.65578 14.9191 7.57931 14.9165 7.49841L14.9168 5.56397ZM4.73803 1.62501C3.97643 1.62488 3.2464 1.92818 2.70842 2.46749C2.17052 3.00673 1.86864 3.73772 1.86851 4.49954C1.86839 5.26136 2.17004 5.99245 2.70777 6.53186C3.24558 7.07134 3.97551 7.37487 4.73711 7.375C5.49871 7.37512 6.22874 7.07182 6.76671 6.53251C7.30462 5.99327 7.6065 5.26228 7.60662 4.50046C7.60674 3.73864 7.30509 3.00755 6.76736 2.46814C6.22956 1.92866 5.49963 1.62513 4.73803 1.62501Z"
                                                fill="#09192D" stroke="white" stroke-width="1.5" />
                                        </svg>
                                    </div>
                                    <h2 class="text-base font-bold md:text-lg">بازی‌های مشابه</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative overflow-hidden embla_normal horizontal dragFree">
                        <div class="embla__viewport">
                            <div id="trends-rooms-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:max-w-[156px] md:child:max-w-[200px] child:py-2.5"> <?= $products ?> </div>
                        </div>
                        <button class="embla__button embla__button--prev trends-rooms-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                        <button class="embla__button embla__button--next trends-rooms-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
            endif; ?>
        <?php } ?>
    </div>


</div>

<?php if (is_user_logged_in()):
    $ez_logged_uid           = get_current_user_id();
    $ez_pid_modal            = get_the_ID();
    $ez_review_comment_id    = function_exists( 'ez_get_user_product_review_comment_id' ) ? ez_get_user_product_review_comment_id( $ez_logged_uid, $ez_pid_modal ) : 0;
    $ez_can_edit_review      = false;
    if ( $ez_review_comment_id && function_exists( 'ez_user_may_review_product_in_window' ) ) {
        $ez_can_edit_review = ! is_wp_error( ez_user_may_review_product_in_window( $ez_logged_uid, $ez_pid_modal, wp_get_current_user() ) );
    }
    $ez_review_title_modal   = ( $ez_can_edit_review && $ez_review_comment_id ) ? 'ویرایش نظر' : 'ارسال نظر';
    $ez_review_submit_label  = ( $ez_can_edit_review && $ez_review_comment_id ) ? 'ذخیره تغییرات' : 'ثبت نظر';
    ?>
    <!-- Comment Modal - Unified for Mobile & Desktop -->
    <!-- Desktop: Center Modal -->
    <div id="comment-modal-overlay" class="hidden fixed inset-0 bg-black/50 z-[9999] max-lg:hidden"></div>
    <div id="comment-modal-desktop" class="hidden fixed inset-0 z-[9999] items-center justify-center max-lg:hidden">
        <div class="relative bg-white rounded-2xl p-8 w-full max-w-[800px] max-h-[90vh] overflow-y-auto mx-4 transform transition-all duration-300 scale-95 opacity-0">
            <!-- Close Button -->
            <button type="button" id="close-comment-modal" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <!-- Header -->
            <div class="flex items-center justify-between mb-6 mt-4">
                <h3 class="text-2xl font-black comment-modal-main-title"><?php echo esc_html( $ez_review_title_modal ); ?></h3>
                <span class="text-sm text-[#62748E]">برای <?php echo $data['title'] ?></span>
            </div>

            <!-- Form -->
            <form id="comment-form" class="send-comment" method="post">
                <input type="hidden" name="review_comment_id" id="review_comment_id" value="<?php echo $ez_can_edit_review ? (int) $ez_review_comment_id : 0; ?>" />
                <div class="mb-6 max-lg:mb-4">
                    <textarea
                        id="content"
                        name="content"
                        rows="6"
                        class="block w-full p-4 text-sm text-gray-900 border border-gray-200 outline-none rounded-xl placeholder:text-slate-300 focus:border-[#2B7FFF] focus:ring-2 focus:ring-[#2B7FFF]/20 transition-all max-lg:rows-5"
                        placeholder="لطفا نکات مثبت و منفی <?php echo $product_type; ?> را در این قسمت بنویسید..."
                        required></textarea>
                </div>

                <?php if ($product_type == 'اتاق فرار') { ?>
                    <div class="mb-6 max-lg:mb-4">
                        <div class="space-y-4 max-lg:space-y-3">
                            <div class="flex items-center justify-between max-lg:gap-2">
                                <label class="text-sm font-semibold min-w-[140px] max-lg:text-xs max-lg:min-w-0">فضاسازی</label>
                                <div class="flex gap-2 max-lg:gap-1.5">
                                    <?php for ($j = 1; $j <= 5; $j++) { ?>
                                        <button type="button"
                                            class="w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $j == 5 ? 'active' : ''; ?>"
                                            data-rate="<?php echo $j * 20; ?>"
                                            data-rating-item="1094">
                                            <?php echo $j; ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="flex items-center justify-between max-lg:gap-2">
                                <label class="text-sm font-semibold min-w-[140px] max-lg:text-xs max-lg:min-w-0">تازگی و خلاقیت</label>
                                <div class="flex gap-2 max-lg:gap-1.5">
                                    <?php for ($j = 1; $j <= 5; $j++) { ?>
                                        <button type="button"
                                            class="w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $j == 5 ? 'active' : ''; ?>"
                                            data-rate="<?php echo $j * 20 ?>"
                                            data-rating-item="1098">
                                            <?php echo $j; ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="flex items-center justify-between max-lg:gap-2">
                                <label class="text-sm font-semibold min-w-[140px] max-lg:text-xs max-lg:min-w-0">کیفیت معما</label>
                                <div class="flex gap-2 max-lg:gap-1.5">
                                    <?php for ($j = 1; $j <= 5; $j++) { ?>
                                        <button type="button"
                                            class="w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $j == 5 ? 'active' : ''; ?>"
                                            data-rate="<?php echo $j * 20; ?>"
                                            data-rating-item="1095">
                                            <?php echo $j; ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="flex items-center justify-between max-lg:gap-2">
                                <label class="text-sm font-semibold min-w-[140px] max-lg:text-xs max-lg:min-w-0">بازیگردانی و اکت</label>
                                <div class="flex gap-2 max-lg:gap-1.5">
                                    <?php for ($j = 1; $j <= 5; $j++) { ?>
                                        <button type="button"
                                            class="w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $j == 5 ? 'active' : ''; ?>"
                                            data-rate="<?php echo $j * 20; ?>"
                                            data-rating-item="1096">
                                            <?php echo $j; ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="flex items-center justify-between max-lg:gap-2">
                                <label class="text-sm font-semibold min-w-[140px] max-lg:text-xs max-lg:min-w-0">برخورد پرسنل</label>
                                <div class="flex gap-2 max-lg:gap-1.5">
                                    <?php for ($j = 1; $j <= 5; $j++) { ?>
                                        <button type="button"
                                            class="w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $j == 5 ? 'active' : ''; ?>"
                                            data-rate="<?php echo $j * 20; ?>"
                                            data-rating-item="1097">
                                            <?php echo $j; ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <!-- برای محصولاتی که اتاق فرار نیستن (بوردگیم و ...) -->
                    <div class="mb-6 max-lg:mb-4">
                        <div class="flex items-center justify-between max-lg:items-center max-lg:justify-between">
                            <div class="flex items-center justify-between gap-2 max-lg:mb-0 lg:mb-3">
                                <h3 class="text-sm max-lg:text-xs font-semibold">امتیاز</h3>
                            </div>
                            <div class="relative max-lg:[&_.overflow-x-auto]:pb-0.75">
                                <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                                    <div class="flex gap-2">
                                        <?php for ($j = 1; $j <= 5; $j++) { ?>
                                            <button type="button"
                                                class="flex-shrink-0 px-3 py-1 text-lg font-semibold text-center transition-all duration-150 bg-white border text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-5 max-lg:rounded-md max-lg:text-slate-900 max-lg:shadow-13 lg:h-10 lg:py-2 lg:leading-4 <?php echo $j == 5 ? 'active' : ''; ?>"
                                                data-rate="<?php echo $j * 20; ?>"
                                                data-rating-item="1098">
                                                <?php echo $j; ?>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <button type="submit" id="comment-form-submit" class="w-full bg-[#2B7FFF] text-white py-4 max-lg:py-3.5 rounded-xl font-bold text-lg max-lg:text-base hover:bg-[#1e5fbf] transition-colors flex items-center justify-center">
                    <?php echo esc_html( $ez_review_submit_label ); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Mobile: Bottom Sheet -->
    <div id="comment-modal-mobile" class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-[0_-4px_10px_rgba(0,0,0,0.1)] translate-y-full transition-transform duration-300 ease-in-out z-[9999] lg:hidden px-7 pt-9 max-h-[90vh]" style="display: none;">
        <div class="relative">
            <button type="button" id="close-comment-modal" class="bg-white/30 w-11 h-11 flex items-center justify-center hover:bg-white/50 absolute -top-25 right-0 rounded-xl transition-all">
                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 21.9474L21.8526 3M21.9474 21.9474L3.09474 3" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-black comment-modal-main-title-mobile"><?php echo esc_html( $ez_review_title_modal ); ?></h3>
                <span class="text-sm text-[#62748E]">برای <?php echo $data['title'] ?></span>
            </div>

            <!-- Form - Same as desktop, will be cloned -->
            <div id="comment-form-container-mobile" class="pb-6"></div>
        </div>
    </div>
<?php endif; ?>
<?php get_footer(); ?>