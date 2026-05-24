<?php
global $wpdb;

$user_id = get_current_user_id();
?>
<svg class="hidden">
    <symbol id="room_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 19" fill="none">
        <path d="M5.13318 17.3633L4.17297 15.6841L2.5006 16.6562C2.27759 16.7847 2.01264 16.8194 1.76404 16.7527C1.51543 16.6859 1.30354 16.5231 1.17497 16.3001C1.0464 16.0771 1.01168 15.8121 1.07846 15.5635C1.14524 15.3149 1.30804 15.103 1.53104 14.9745L14.0136 7.77793C13.636 6.64163 13.6937 5.40563 14.1754 4.30943C14.6572 3.21323 15.5287 2.33488 16.6211 1.84457C17.7135 1.35427 18.949 1.28691 20.0882 1.65556C21.2274 2.02422 22.1892 2.80262 22.7873 3.83996C23.3853 4.87729 23.577 6.09969 23.3253 7.2703C23.0735 8.44091 22.3961 9.47638 21.4244 10.1761C20.4527 10.8758 19.2559 11.1899 18.0659 11.0575C16.8758 10.9251 15.7773 10.3557 14.9831 9.45963L9.19439 12.7891L10.1665 14.4615C10.2295 14.5719 10.2702 14.6938 10.2861 14.82C10.302 14.9462 10.2929 15.0743 10.2591 15.197C10.2268 15.3201 10.1706 15.4355 10.0936 15.5368C10.0166 15.638 9.92033 15.7231 9.81038 15.7871C9.70009 15.8513 9.57818 15.893 9.45169 15.9098C9.3252 15.9267 9.19663 15.9183 9.07339 15.8852C8.95015 15.8521 8.83469 15.7949 8.73366 15.717C8.63263 15.639 8.54803 15.5418 8.48475 15.431L7.57121 13.7644L5.8895 14.7339L6.86156 16.4063C6.92465 16.5168 6.96532 16.6386 6.98122 16.7648C6.99713 16.891 6.98796 17.0192 6.95425 17.1418C6.92196 17.2649 6.8657 17.3803 6.78869 17.4816C6.71168 17.5829 6.61544 17.6679 6.50549 17.7319C6.39261 17.8032 6.2662 17.8503 6.13422 17.8703C6.00223 17.8903 5.86753 17.8828 5.73861 17.8481C5.60968 17.8135 5.48933 17.7526 5.38513 17.6691C5.28093 17.5857 5.19515 17.4815 5.13318 17.3633ZM21.3966 7.00863C21.5454 6.45472 21.5266 5.86913 21.3426 5.3259C21.1586 4.78268 20.8176 4.30623 20.3628 3.9568C19.908 3.60737 19.3597 3.40065 18.7875 3.36278C18.2152 3.32492 17.6445 3.45761 17.1476 3.74408C16.6507 4.03054 16.2499 4.45792 15.996 4.97216C15.742 5.4864 15.6462 6.06441 15.7207 6.63309C15.7952 7.20178 16.0367 7.7356 16.4146 8.16704C16.7925 8.59849 17.2898 8.90819 17.8437 9.05698C18.5865 9.25649 19.3781 9.15277 20.0444 8.76863C20.7107 8.38449 21.1971 7.7514 21.3966 7.00863Z" fill="#0F172B" stroke="#0F172B" />
    </symbol>
    <symbol id="cinema_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 29" fill="none">
        <path d="M16.9283 8.08007C16.9283 8.61588 16.8216 9.14643 16.6143 9.64145C16.407 10.1365 16.1031 10.5863 15.72 10.9651C15.337 11.344 14.8822 11.6445 14.3817 11.8496C13.8813 12.0546 13.3448 12.1601 12.8031 12.1601C12.2614 12.1601 11.725 12.0546 11.2245 11.8496C10.724 11.6445 10.2692 11.344 9.88617 10.9651C9.50312 10.5863 9.19926 10.1365 8.99195 9.64145C8.78464 9.14643 8.67794 8.61588 8.67794 8.08007C8.67794 6.99797 9.11255 5.96019 9.88617 5.19503C10.6598 4.42986 11.709 4 12.8031 4C13.8972 4 14.9464 4.42986 15.72 5.19503C16.4937 5.96019 16.9283 6.99797 16.9283 8.08007ZM11.6245 13.3259C10.843 13.3259 10.0935 13.6329 9.54096 14.1795C8.98838 14.726 8.67794 15.4673 8.67794 16.2402V22.0689C8.67794 22.4516 8.75415 22.8306 8.90223 23.1842C9.05031 23.5378 9.26735 23.859 9.54096 24.1297C9.81458 24.4003 10.1394 24.6149 10.4969 24.7614C10.8544 24.9079 11.2375 24.9832 11.6245 24.9832H21.0535C21.4404 24.9832 21.8236 24.9079 22.181 24.7614C22.5385 24.6149 22.8634 24.4003 23.137 24.1297C23.4106 23.859 23.6276 23.5378 23.7757 23.1842C23.9238 22.8306 24 22.4516 24 22.0689V16.2402C24 15.4673 23.6896 14.726 23.137 14.1795C22.5844 13.6329 21.8349 13.3259 21.0535 13.3259H11.6245ZM4 14.4206V24.1265C4.00015 24.2993 4.05208 24.4682 4.14922 24.6118C4.24636 24.7554 4.38436 24.8674 4.54579 24.9335C4.70722 24.9996 4.88483 25.0169 5.0562 24.9832C5.22757 24.9496 5.38501 24.8664 5.50863 24.7443L8.41865 21.8673C8.58447 21.7035 8.67774 21.4812 8.67794 21.2494V17.2754C8.67784 17.1601 8.65467 17.0459 8.60976 16.9395C8.56486 16.8331 8.4991 16.7365 8.41629 16.6553L5.50628 13.7992C5.38232 13.6777 5.22476 13.5952 5.05347 13.5621C4.88217 13.529 4.70479 13.5468 4.54371 13.6132C4.38262 13.6797 4.24502 13.7918 4.14827 13.9355C4.05151 14.0791 3.99992 14.2479 4 14.4206ZM21.0535 12.1601C21.8349 12.1601 22.5844 11.8531 23.137 11.3066C23.6896 10.76 24 10.0187 24 9.24581C24 8.47288 23.6896 7.73161 23.137 7.18506C22.5844 6.63852 21.8349 6.33147 21.0535 6.33147C20.272 6.33147 19.5225 6.63852 18.9699 7.18506C18.4173 7.73161 18.1069 8.47288 18.1069 9.24581C18.1069 10.0187 18.4173 10.76 18.9699 11.3066C19.5225 11.8531 20.272 12.1601 21.0535 12.1601Z" stroke="#09192D" stroke-width="2.5" />
    </symbol>
    <symbol id="laser_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 29" fill="none">
        <rect x="4.75" y="5.75" width="18.5" height="18.5" rx="9.25" stroke="#09192D" stroke-width="2.5" />
        <path d="M14 22.9999V19.7999" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
        <path d="M14 10.2V7" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
        <path d="M18 14.68H22" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
        <path d="M6.00065 14.68L9.33398 14.68" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" />
    </symbol>
    <symbol id="rage_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 19" fill="none">
        <path d="M5.13318 17.3633L4.17297 15.6841L2.5006 16.6562C2.27759 16.7847 2.01264 16.8194 1.76404 16.7527C1.51543 16.6859 1.30354 16.5231 1.17497 16.3001C1.0464 16.0771 1.01168 15.8121 1.07846 15.5635C1.14524 15.3149 1.30804 15.103 1.53104 14.9745L14.0136 7.77793C13.636 6.64163 13.6937 5.40563 14.1754 4.30943C14.6572 3.21323 15.5287 2.33488 16.6211 1.84457C17.7135 1.35427 18.949 1.28691 20.0882 1.65556C21.2274 2.02422 22.1892 2.80262 22.7873 3.83996C23.3853 4.87729 23.577 6.09969 23.3253 7.2703C23.0735 8.44091 22.3961 9.47638 21.4244 10.1761C20.4527 10.8758 19.2559 11.1899 18.0659 11.0575C16.8758 10.9251 15.7773 10.3557 14.9831 9.45963L9.19439 12.7891L10.1665 14.4615C10.2295 14.5719 10.2702 14.6938 10.2861 14.82C10.302 14.9462 10.2929 15.0743 10.2591 15.197C10.2268 15.3201 10.1706 15.4355 10.0936 15.5368C10.0166 15.638 9.92033 15.7231 9.81038 15.7871C9.70009 15.8513 9.57818 15.893 9.45169 15.9098C9.3252 15.9267 9.19663 15.9183 9.07339 15.8852C8.95015 15.8521 8.83469 15.7949 8.73366 15.717C8.63263 15.639 8.54803 15.5418 8.48475 15.431L7.57121 13.7644L5.8895 14.7339L6.86156 16.4063C6.92465 16.5168 6.96532 16.6386 6.98122 16.7648C6.99713 16.891 6.98796 17.0192 6.95425 17.1418C6.92196 17.2649 6.8657 17.3803 6.78869 17.4816C6.71168 17.5829 6.61544 17.6679 6.50549 17.7319C6.39261 17.8032 6.2662 17.8503 6.13422 17.8703C6.00223 17.8903 5.86753 17.8828 5.73861 17.8481C5.60968 17.8135 5.48933 17.7526 5.38513 17.6691C5.28093 17.5857 5.19515 17.4815 5.13318 17.3633ZM21.3966 7.00863C21.5454 6.45472 21.5266 5.86913 21.3426 5.3259C21.1586 4.78268 20.8176 4.30623 20.3628 3.9568C19.908 3.60737 19.3597 3.40065 18.7875 3.36278C18.2152 3.32492 17.6445 3.45761 17.1476 3.74408C16.6507 4.03054 16.2499 4.45792 15.996 4.97216C15.742 5.4864 15.6462 6.06441 15.7207 6.63309C15.7952 7.20178 16.0367 7.7356 16.4146 8.16704C16.7925 8.59849 17.2898 8.90819 17.8437 9.05698C18.5865 9.25649 19.3781 9.15277 20.0444 8.76863C20.7107 8.38449 21.1971 7.7514 21.3966 7.00863Z" fill="#0F172B" stroke="#0F172B" />
    </symbol>
    <symbol id="cafe_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 29" fill="none">
        <path d="M10.5007 20.3333H15.1673C16.7144 20.3333 18.1981 19.7188 19.2921 18.6248C20.3861 17.5308 21.0007 16.0471 21.0007 14.5V13.3333H22.1673C23.0956 13.3333 23.9858 12.9646 24.6422 12.3082C25.2986 11.6518 25.6673 10.7616 25.6673 9.83333C25.6673 8.90508 25.2986 8.01484 24.6422 7.35846C23.9858 6.70208 23.0956 6.33333 22.1673 6.33333H21.0007V5.16667C21.0007 4.85725 20.8777 4.5605 20.6589 4.34171C20.4402 4.12292 20.1434 4 19.834 4H5.83398C5.52457 4 5.22782 4.12292 5.00903 4.34171C4.79023 4.5605 4.66732 4.85725 4.66732 5.16667V14.5C4.66732 16.0471 5.2819 17.5308 6.37586 18.6248C7.46982 19.7188 8.95356 20.3333 10.5007 20.3333ZM21.0007 8.66667H22.1673C22.4767 8.66667 22.7735 8.78958 22.9923 9.00838C23.2111 9.22717 23.334 9.52391 23.334 9.83333C23.334 10.1428 23.2111 10.4395 22.9923 10.6583C22.7735 10.8771 22.4767 11 22.1673 11H21.0007V8.66667ZM7.00065 6.33333H18.6673V14.5C18.6673 15.4283 18.2986 16.3185 17.6422 16.9749C16.9858 17.6313 16.0956 18 15.1673 18H10.5007C9.57239 18 8.68215 17.6313 8.02578 16.9749C7.3694 16.3185 7.00065 15.4283 7.00065 14.5V6.33333ZM24.5007 22.6667H3.50065C3.19123 22.6667 2.89449 22.7896 2.67569 23.0084C2.4569 23.2272 2.33398 23.5239 2.33398 23.8333C2.33398 24.1428 2.4569 24.4395 2.67569 24.6583C2.89449 24.8771 3.19123 25 3.50065 25H24.5007C24.8101 25 25.1068 24.8771 25.3256 24.6583C25.5444 24.4395 25.6673 24.1428 25.6673 23.8333C25.6673 23.5239 25.5444 23.2272 25.3256 23.0084C25.1068 22.7896 24.8101 22.6667 24.5007 22.6667Z" fill="#0F172B" />
    </symbol>
    <symbol id="bubble_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 29" fill="none">
        <circle cx="14" cy="14.5" r="9.25" stroke="#09192D" stroke-width="2.5" />
        <path d="M9 14.5L12 17.5L19 10.5" stroke="#09192D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M14 5.5V8.5M14 20.5V23.5M5 14.5H8M20 14.5H23M8.5 8L10.5 10M17.5 19L19.5 21M8.5 21L10.5 19M17.5 10L19.5 8" stroke="#09192D" stroke-width="2" stroke-linecap="round" />
    </symbol>
</svg>
<?php

$current_category = get_queried_object();

$category_id        = $current_category->term_id;
$product_type       = $current_category->name;
$product_type_equ   = get_product_type_equivalent($product_type);
// امروز
$today = date('Y-m-d');
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps($dayAfterTomorrow);
$is_escaperoom = false;
if ($product_type == 'اتاق فرار')
    $is_escaperoom = true;

/*===============================================================*/
// ویدئو + متن

if ($product_type == 'اتاق فرار') {
    $data[] = [
        'type'  => 'video_text',
    ];
} elseif ($product_type == 'سینما ترس') {
    $data[] = [
        'type'  => 'video_text',
    ];
} elseif ($product_type == 'اتاق خشم') {
    $data[] = [
        'type'  => 'video_text',
    ];
} elseif ($product_type == 'لیزرتگ') {
    $data[] = [
        'type'  => 'video_text',
    ];
}

$data = [];
$thumbnail_id = get_term_meta($category_id, 'thumbnail_id', true);
$poster_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';

$data[0]['data'] = [
    'title' => single_term_title('', false),
    'text' => get_field('short-description', 'product_cat_' . $current_category->term_id),
    'video' => get_field('video', 'product_cat_' . $current_category->term_id),
    'poster' => $poster_url
];
// $data[0]['data'] = [
//     'title' => single_term_title('',false),
//     'text' => get_field('short-description', 'product_cat_897' ),
//     'video' => get_field('video', 'product_cat_897' )
// ];
?>

<section class="max-lg:px-4.5 mt-7.5 lg:mt-10 mb-10 gap-10 grid grid-cols-1 lg:grid-cols-5">
    <div class="lg:col-span-3">
        <h1 class="text-44 font-black mb-4"><?= $data[0]['data']['title'] ?></h1>
        <p class="text-justify"><?= $data[0]['data']['text'] ?></p>
    </div>
    <div class="col-span-1 lg:col-span-2 overflow-hidden rounded-2xl relative aspect-video">
        <button type="button" id="video-poster-btn" class="w-full h-full block absolute inset-0">
            <img src="<?= $data[0]['data']['poster'] ?>" alt="<?= $data[0]['data']['title'] ?>" class="w-full h-full object-cover">
        </button>
        <div id="video-loading" class="hidden absolute inset-0 bg-black flex items-center justify-center z-10">
            <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-white"></div>
        </div>
        <div id="video-container" class="hidden absolute inset-0">
            <?= $data[0]['data']['video'] ?>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const posterBtn = document.getElementById('video-poster-btn');
        const videoLoading = document.getElementById('video-loading');
        const videoContainer = document.getElementById('video-container');

        if (posterBtn && videoContainer && videoLoading) {
            posterBtn.addEventListener('click', function() {
                // مخفی کردن پوستر
                posterBtn.style.display = 'none';

                // نمایش لودینگ
                videoLoading.classList.remove('hidden');

                // بعد از یک تاخیر کوتاه، ویدیو رو نمایش بده
                setTimeout(function() {
                    videoLoading.classList.add('hidden');
                    videoContainer.classList.remove('hidden');

                    // اگر ویدیو iframe باشه، autoplay رو فعال کنیم و سایز رو تنظیم کنیم
                    const iframe = videoContainer.querySelector('iframe');
                    if (iframe) {
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        iframe.style.position = 'absolute';
                        iframe.style.top = '0';
                        iframe.style.left = '0';

                        const src = iframe.getAttribute('src');
                        if (src && !src.includes('autoplay')) {
                            const separator = src.includes('?') ? '&' : '?';
                            iframe.setAttribute('src', src + separator + 'autoplay=1');
                        }
                    }

                    // اگر ویدیو تگ video باشه، play کنیم و سایز رو تنظیم کنیم
                    const video = videoContainer.querySelector('video');
                    if (video) {
                        video.style.width = '100%';
                        video.style.height = '100%';
                        video.style.objectFit = 'cover';
                        video.play();
                    }
                }, 300);
            });
        }
    });
</script>
<style>
    .divider-gradient::after {
        background: radial-gradient(ellipse, #2B4989 0%, transparent 70%);
        width: 400px;
        height: 100px;
        top: -40px;
        opacity: 0.2;
        filter: blur(25px);
    }
</style>
<div class="divider-gradient h-0.5 w-full max-lg:hidden my-4 opacity-50 relative after:absolute after:left-1/2 after:-translate-x-1/2 after:rounded-full after:opacity-50" style="background: radial-gradient(circle, #CAD5E2 0%, transparent 70%);"></div>

<?php
/*===============================================================*/
// تبلیغات - محصولات پیشنهادی

// $params = [
//     'product_type' => $product_type,
// ];

// $args = [
//     'source'    => 'type_page_ads',
//     'params'    => $params,
// ];
// زوم کلاب
$cities_data = get_cities_with_city_id_and_children();
$label_type = null;
if ($product_type == 'اتاق فرار') {
    $label_type = 'room';
} elseif ($product_type == 'سینما ترس') {
    $label_type = 'cinema';
} elseif ($product_type == 'اتاق خشم') {
    $label_type = 'rage-room';
} elseif ($product_type == 'لیزرتگ') {
    $label_type = 'laser';
}elseif ($product_type == 'پینت بال') {
    $label_type = 'paint-ball';
}elseif ($product_type == 'کافه بازی') {
    $label_type = 'cafe';
}elseif ($product_type == 'فوتبال حبابی') {
    $label_type = 'bubble-football';
}
$room_ids = [];
foreach ($cities_data as $city) {
    if (isset($city['children']) && is_array($city['children'])) {
        foreach ($city['children'] as $child) {
            if (isset($child['label']) && $child['label'] === $label_type) {
                $room_ids[] = $child['id'];
                break;
            }
        }
    }
}
$params = [
    'city_id' => $room_ids,
];
$city_id_term[] = $term_id;

$args = [
    'source'    => 'typecity_page_ads',
    'params'    => $params,
];
$ads_products = ez_products_snapshot_swiper($args);
if (!is_null($ads_products->products) and !empty($ads_products->products) and (strlen($ads_products->products) > 0)):
?>
    <section class="lg:max-w-full px-4 mt-8 lg:pb-6 max-lg:py-6 bg-slate-50 lg:rounded-4xl max-lg:relative max-lg:w-screen max-lg:right-1/2 max-lg:left-1/2 max-lg:-ml-50vw max-lg:-mr-50vw">
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
                    <div id="discount-events-slider" class="embla__container child:bg-white child:p-2.5 md:child:p-5 child:rounded-3xl flex gap-x-4 md:gap-x-6 child:shrink-0 child:grow-0 child:w-d176 md:child:w-d230"> <?= $ads_products->products ?> </div>
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
    </section>
<?php endif; ?>
<?php
/*===============================================================*/
// اتاق فرارهای تهران

if ($is_escaperoom) :

    $params = [
        'city_id' => [15],
    ];
    $args = [
        "source" => "home_cities_escaperoom",
        'params' => $params,
        'sort_type' => 'popular',
    ];
    $cities_rooms = ez_products_snapshot_swiper_html($args);
?>
    <section class="max-w-full py-4 md:py-5 lg:py-9 mt-6">
        <input type="hidden" id="cities-rooms" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"popular","city_id":[15],"tag":[124]}'>
        <div class="flex justify-between mb-6 md:mb-8">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <svg width="24" height="19" viewBox="0 0 24 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.13318 17.3633L4.17297 15.6841L2.5006 16.6562C2.27759 16.7847 2.01264 16.8194 1.76404 16.7527C1.51543 16.6859 1.30354 16.5231 1.17497 16.3001C1.0464 16.0771 1.01168 15.8121 1.07846 15.5635C1.14524 15.3149 1.30804 15.103 1.53104 14.9745L14.0136 7.77793C13.636 6.64163 13.6937 5.40563 14.1754 4.30943C14.6572 3.21323 15.5287 2.33488 16.6211 1.84457C17.7135 1.35427 18.949 1.28691 20.0882 1.65556C21.2274 2.02422 22.1892 2.80262 22.7873 3.83996C23.3853 4.87729 23.577 6.09969 23.3253 7.2703C23.0735 8.44091 22.3961 9.47638 21.4244 10.1761C20.4527 10.8758 19.2559 11.1899 18.0659 11.0575C16.8758 10.9251 15.7773 10.3557 14.9831 9.45963L9.19439 12.7891L10.1665 14.4615C10.2295 14.5719 10.2702 14.6938 10.2861 14.82C10.302 14.9462 10.2929 15.0743 10.2591 15.197C10.2268 15.3201 10.1706 15.4355 10.0936 15.5368C10.0166 15.638 9.92033 15.7231 9.81038 15.7871C9.70009 15.8513 9.57818 15.893 9.45169 15.9098C9.3252 15.9267 9.19663 15.9183 9.07339 15.8852C8.95015 15.8521 8.83469 15.7949 8.73366 15.717C8.63263 15.639 8.54803 15.5418 8.48475 15.431L7.57121 13.7644L5.8895 14.7339L6.86156 16.4063C6.92465 16.5168 6.96532 16.6386 6.98122 16.7648C6.99713 16.891 6.98796 17.0192 6.95425 17.1418C6.92196 17.2649 6.8657 17.3803 6.78869 17.4816C6.71168 17.5829 6.61544 17.6679 6.50549 17.7319C6.39261 17.8032 6.2662 17.8503 6.13422 17.8703C6.00223 17.8903 5.86753 17.8828 5.73861 17.8481C5.60968 17.8135 5.48933 17.7526 5.38513 17.6691C5.28093 17.5857 5.19515 17.4815 5.13318 17.3633ZM21.3966 7.00863C21.5454 6.45472 21.5266 5.86913 21.3426 5.3259C21.1586 4.78268 20.8176 4.30623 20.3628 3.9568C19.908 3.60737 19.3597 3.40065 18.7875 3.36278C18.2152 3.32492 17.6445 3.45761 17.1476 3.74408C16.6507 4.03054 16.2499 4.45792 15.996 4.97216C15.742 5.4864 15.6462 6.06441 15.7207 6.63309C15.7952 7.20178 16.0367 7.7356 16.4146 8.16704C16.7925 8.59849 17.2898 8.90819 17.8437 9.05698C18.5865 9.25649 19.3781 9.15277 20.0444 8.76863C20.7107 8.38449 21.1971 7.7514 21.3966 7.00863Z" fill="#0F172B" stroke="#0F172B" />
                    </svg>
                    <div class="text-base font-bold md:text-lg">
                        <p>اتاق فرارهای <b id="cities-rooms-title">تهران</b></p>
                    </div>
                </h2>
            </div>
            <div class="flex items-center gap-6">
                <div class="hidden md:block"></div>
                <a href="/city/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/" id="cities-rooms-link">
                    <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">
                        <span id="cities-rooms-link-text">مشاهده همه</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                            <path clip-rule="evenodd"
                                d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                            <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                                fill="currentColor"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
        <div class="grid grid-cols-3 lg:grid-cols-4 my-8 gap-x-4 lg:gap-x-11.5">
            <div class="lg:col-span-2">
                <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">شهر مورد نظر</h3>
                <div class="dropdown-container relative">
                    <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                        <span>انتخاب شهر</span>
                        <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                            <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                        <button type="button" data-input="cities-rooms" data-params="city_id:[15]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                            تهران
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="city_id:[162]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            کرج
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="city_id:[122]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            اصفهان
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="city_id:[121]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            مشهد
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="city_id:[293]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            کرمانشاه
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="city_id:[270]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            قزوین
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="city_id:[304]"
                            class="option filter-btn city-btn-filter max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            کاشان
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">سبک بازی</h3>
                <div class="dropdown-container relative">
                    <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                        <span>سبک بازی</span>
                        <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                            <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                        <button type="button" data-input="cities-rooms" data-params="tag:[124]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                            ترسناک
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="tag:[346]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            اکشن
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="tag:[342]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            درام
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="tag:[126]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            دلهره آور
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="tag:[125]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            غیرترسناک
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="tag:[178]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            هیجانی
                        </button>
                        <button type="button" data-input="cities-rooms" data-params="tag:[127]"
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            جنایی
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-nowrap text-xs text-slate-330 relative flex items-center gap-x-2 after:relative after:w-full after:h-px after:bg-edge max-md:hidden">براساس</h3>
                <div class="dropdown-container relative">
                    <button class="dropdown-button w-full text-left text-xs font-semibold rounded-xl h-10 px-3 flex items-center justify-between shadow-13 border border-edge md:hidden">
                        <span>براساس</span>
                        <svg class="m-0" xmlns="http://www.w3.org/2000/svg" width="12" height="7" viewBox="0 0 12 7" fill="none">
                            <path d="M10.3594 1.92188L6.34374 5.49133C5.96485 5.82812 5.3939 5.82812 5.01501 5.49133L0.999374 1.92188" stroke="#0A184A" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <div class="options scrollable max-md:hidden max-md:absolute max-md:z-10 max-md:w-full max-md:bg-white max-md:border max-md:border-gray-200 max-md:rounded-lg max-md:mt-1 md:flex md:gap-2 md:my-4 scrollbar-hide overflow-x-auto touch-pan-x">
                        <button type="button" data-input="cities-rooms" data-params='sort_type:"popular"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl bg-primary-500 text-slate-100 md:border md:border-primary-500 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:transition" disabled>
                            محبوب ترین ها
                        </button>
                        <button type="button" data-input="cities-rooms" data-params='sort_type:"topsale"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            پرفروش ترین ها
                        </button>
                        <button type="button" data-input="cities-rooms" data-params='sort_type:"recent"'
                            class="option filter-btn max-md:block max-md:w-full max-md:py-2.5 text-nowrap text-center text-xs font-semibold md:focus-visible:outline md:focus-visible:outline-2 md:focus-visible:outline-offset-2 md:flex-shrink-0 whitespace-nowrap md:rounded-2xl md:bg-white text-slate-350 md:border md:border-gray-50 md:h-9 md:min-w-9 md:px-3 md:px-5 md:py-1 md:hover:bg-primary-600 md:hover:text-white md:transition">
                            جدیدترین ها
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative overflow-hidden embla_normal horizontal dragFree">
            <div class="embla__viewport">
                <div id="cities-rooms-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5">
                    <?= $cities_rooms ?>
                </div>
            </div>
            <button class="embla__button embla__button--prev cities-rooms-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
            <button class="embla__button embla__button--next cities-rooms-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
/*===============================================================*/
// سرگرمی های مختلف در شهرهای مختلف (سینماترس تهران، اتاق خشم تهران ....)

if (!$is_escaperoom) :
    $type_city_list = [
        'lasertag'      => [1147, 1149, 1158, 1196, 1219, 1148, 1156],
        'rageroom'      => [1186, 1074],
        'cinema'        => [913, 1009, 926, 1004, 1176, 904, 918],
        'bubblefootball' => [1371, 1375, 1370, 1376, 1372, 1377, 1374, 1373],
        'cafegame'      => [1324, 1318, 1346, 1337, 1325, 1331],
        'paintball'     => [1353, 1355, 1357, 1363, 1362, 1368, 1364, 1369, 1366, 1367],
        'haunted_house' => [],
    ];

    foreach ($type_city_list[$product_type_equ] as $type_city_item): ?>
        <?php
        $params = [
            'schedule' => -1,
        ];
        $args = [
            'source'    => 'type_page_cat_' . $product_type_equ . '_' . $type_city_item,
            'params'    => $params,
        ];
        $city_name = get_term($type_city_item)->name;
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
        $games_products = ez_products_snapshot_swiper($args);
        if (!is_null($games_products->products) and !empty($games_products->products) and (strlen($games_products->products) > 0)) : ?>
            <section class="max-w-full py-4 md:py-5 lg:py-9 mt-7.5 lg:mt-12">
                <div class="mb-6 md:mb-8">
                    <input type="hidden" id="games-<?= $type_city_item ?>" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest"}'>
                    <div class="flex justify-between">
                        <div class="items-center gap-6 md:flex">
                            <h2 class="flex items-center gap-4">
                                <?php
                                $icon_id = '';
                                switch ($product_type_equ) {
                                    case 'lasertag':
                                        $icon_id = 'laser_id';
                                        $icon_size = 'width="28" height="29"';
                                        break;
                                    case 'cinema':
                                        $icon_id = 'cinema_id';
                                        $icon_size = 'width="28" height="29"';
                                        break;
                                    case 'rageroom':
                                        $icon_id = 'rage_id';
                                        $icon_size = 'width="24" height="19"';
                                        break;
                                    case 'cafegame':
                                        $icon_id = 'cafe_id';
                                        $icon_size = 'width="28" height="29"';
                                        break;
                                    case 'bubblefootball':
                                        $icon_id = 'bubble_id';
                                        $icon_size = 'width="28" height="29"';
                                        break;
                                    default:
                                        $icon_id = 'room_id';
                                        $icon_size = 'width="24" height="19"';
                                        break;
                                }
                                ?>
                                <svg <?= $icon_size ?>>
                                    <use href="#<?= $icon_id ?>"></use>
                                </svg>
                                <div class="text-base font-bold md:text-lg">
                                    <?= $product_type . ' های <b>' . $city_name . '</b> و دارای سانس' ?>
                                </div>
                            </h2>
                        </div>
                        <div class="relative content-center hidden md:block">
                            <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                                <div class="flex gap-2">
                                    <button type="button" data-input="games-<?= $type_city_item ?>" data-params='sort_type:"hottest"'
                                        class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-8 py-1 transition" disabled>
                                        داغ ترین ها
                                    </button>
                                    <button type="button" data-input="games-<?= $type_city_item ?>" data-params='sort_type:"topsale"'
                                        class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                        پرفروش ترین ها
                                    </button>
                                    <button type="button" data-input="games-<?= $type_city_item ?>" data-params='sort_type:"popular"'
                                        class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                        محبوب ترین ها
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:hidden">
                        <div class="relative block md:hidden">
                            <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                                <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                    <button type="button" data-input="games-<?= $type_city_item ?>" data-params='sort_type:"hottest"'
                                        class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                        داغ ترین ها
                                    </button>
                                    <button type="button" data-input="games-<?= $type_city_item ?>" data-params='sort_type:"topsale"'
                                        class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                        پرفروش ترین ها
                                    </button>
                                    <button type="button" data-input="games-<?= $type_city_item ?>" data-params='sort_type:"popular"'
                                        class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                        محبوب ترین ها
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden embla_normal horizontal dragFree">
                    <div class="embla__viewport">
                        <div id="games-<?= $type_city_item ?>-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5">
                            <?= $games_products->products ?>
                        </div>
                    </div>
                    <button class="embla__button embla__button--prev games-<?= $type_city_item ?>-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                    <button class="embla__button embla__button--next games-<?= $type_city_item ?>-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
    <?php endforeach; ?>
<?php endif; ?>
<?php
/*===============================================================*/
//تخفیف های ویژه

$args = [
    'source' => 'type_page_discounts_event_' . $product_type_equ,
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
<?php endif; ?>
<?php
/*===============================================================*/
// اتاق فرارهای ترسناک

if ($is_escaperoom) :
    $args = [
        'source' => 'type_page_escaperoom_genre_horror',
    ];
    $scary_escape = ez_products_snapshot_swiper($args);
    if (!is_null($scary_escape->products) and !empty($scary_escape->products) and (strlen($scary_escape->products) > 0)) :
?>
        <section class="max-w-full py-4 md:py-5 lg:py-9 md:mt-7.5">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="scary-rooms" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest"}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg width="24" height="19">
                                <use href="#room_id"></use>
                            </svg>
                            <div class="text-base font-bold md:text-lg">
                                <span class="inline-block">
                                    اتاق فرارهای
                                </span>
                                <span class="font-black inline-block">
                                    ترسناک
                                </span>
                            </div>
                        </h2>
                    </div>
                    <div class="relative content-center hidden md:block">
                        <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                            <div class="flex gap-2">
                                <button type="button" data-input="scary-rooms" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-8 py-1 transition" disabled>
                                    داغ ترین ها
                                </button>
                                <button type="button" data-input="scary-rooms" data-params='sort_type:"topsale"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    پرفروش ترین ها
                                </button>
                                <button type="button" data-input="scary-rooms" data-params='sort_type:"popular"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    محبوب ترین ها
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:hidden">
                    <div class="relative block md:hidden">
                        <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                            <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                <button type="button" data-input="scary-rooms" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                    داغ ترین ها
                                </button>
                                <button type="button" data-input="scary-rooms" data-params='sort_type:"topsale"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    پرفروش ترین ها
                                </button>
                                <button type="button" data-input="scary-rooms" data-params='sort_type:"popular"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    محبوب ترین ها
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal horizontal dragFree">
                <div class="embla__viewport">
                    <div id="scary-rooms-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5">
                        <?= $scary_escape->products ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev scary-rooms-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next scary-rooms-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
<?php endif; ?>
<?php
/*===============================================================*/
// اتاق فرارهای غیرترسناک

if ($is_escaperoom) :
    $args = [
        'source' => 'type_page_escaperoom_genre_nonhorror',
    ];
    $noscary_escape = ez_products_snapshot_swiper($args);
    if (!is_null($noscary_escape->products) and !empty($noscary_escape->products) and (strlen($noscary_escape->products) > 0)) :
?>
        <section class="max-w-full py-4 md:py-5 lg:py-9 md:mt-7.5">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="noscary-rooms" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest"}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg width="24" height="19">
                                <use href="#room_id"></use>
                            </svg>
                            <div class="text-base font-bold md:text-lg">
                                <span class="inline-block">
                                    اتاق فرارهای
                                </span>
                                <span class="font-black inline-block">
                                    غیرترسناک و هیجانی
                                </span>
                            </div>
                        </h2>
                    </div>
                    <div class="relative content-center hidden md:block">
                        <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                            <div class="flex gap-2">
                                <button type="button" data-input="noscary-rooms" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-8 py-1 transition" disabled>
                                    داغ ترین ها
                                </button>
                                <button type="button" data-input="noscary-rooms" data-params='sort_type:"topsale"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    پرفروش ترین ها
                                </button>
                                <button type="button" data-input="noscary-rooms" data-params='sort_type:"popular"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    محبوب ترین ها
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:hidden">
                    <div class="relative block md:hidden">
                        <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                            <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                <button type="button" data-input="noscary-rooms" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                    داغ ترین ها
                                </button>
                                <button type="button" data-input="noscary-rooms" data-params='sort_type:"topsale"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    پرفروش ترین ها
                                </button>
                                <button type="button" data-input="noscary-rooms" data-params='sort_type:"popular"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    محبوب ترین ها
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal horizontal dragFree">
                <div class="embla__viewport">
                    <div id="noscary-rooms-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5">
                        <?= $noscary_escape->products ?>
                    </div>
                </div>
                <button class="embla__button embla__button--prev noscary-rooms-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next noscary-rooms-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
<?php endif; ?>
<?php
/*===============================================================*/
// کالکشن های محبوب

//get_template_part('template/layout/collections');
/*===============================================================*/
// FAQ

$data_faq = [];
foreach (get_field('faq', 'product_cat_' . $current_category->term_id) as $item) {
    $data_faq[] = [
        'question' => $item['title'],
        'answer' => $item['description']
    ];
}
if ($data_faq):
?>
    <style>
        /* جلوگیری از پرش صفحه در موبایل هنگام باز شدن accordion */
        @media (max-width: 768px) {
            .accordion-item {
                scroll-margin-top: 0 !important;
                scroll-margin-bottom: 0 !important;
            }
            .accordion-title {
                scroll-margin-top: 0 !important;
                scroll-margin-bottom: 0 !important;
                outline: none;
                -webkit-tap-highlight-color: transparent;
            }
            .accordion-title:focus {
                outline: none;
            }
            .accordion-title:active {
                outline: none;
            }
            html {
                scroll-behavior: auto !important;
            }
            body {
                scroll-behavior: auto !important;
            }
            /* جلوگیری از scroll snap */
            .accordion {
                scroll-snap-type: none !important;
            }
            .accordion-item {
                scroll-snap-align: none !important;
            }
            /* جلوگیری از scroll into view */
            .accordion-item * {
                scroll-margin: 0 !important;
            }
        }
    </style>
    <section class="py-4 md:py-5 lg:py-9">
        <div class="flex items-center gap-4 mb-10">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none" class="m-0">
                <path
                    d="M18.4098 8.33609C18.0859 7.9975 17.7507 7.64859 17.6244 7.3418C17.5075 7.06078 17.5006 6.595 17.4937 6.14383C17.4809 5.30508 17.4671 4.35461 16.8062 3.69375C16.1454 3.03289 15.1949 3.01914 14.3562 3.00625C13.905 2.99937 13.4392 2.9925 13.1582 2.87562C12.8523 2.7493 12.5025 2.41414 12.1639 2.09016C11.5709 1.52039 10.8972 0.875 10 0.875C9.10281 0.875 8.42992 1.52039 7.83609 2.09016C7.4975 2.41414 7.14859 2.7493 6.8418 2.87562C6.5625 2.9925 6.095 2.99937 5.64383 3.00625C4.80508 3.01914 3.85461 3.03289 3.19375 3.69375C2.53289 4.35461 2.52344 5.30508 2.50625 6.14383C2.49937 6.595 2.4925 7.06078 2.37562 7.3418C2.2493 7.64773 1.91414 7.9975 1.59016 8.33609C1.02039 8.92906 0.375 9.60281 0.375 10.5C0.375 11.3972 1.02039 12.0701 1.59016 12.6639C1.91414 13.0025 2.2493 13.3514 2.37562 13.6582C2.4925 13.9392 2.49937 14.405 2.50625 14.8562C2.51914 15.6949 2.53289 16.6454 3.19375 17.3062C3.85461 17.9671 4.80508 17.9809 5.64383 17.9937C6.095 18.0006 6.56078 18.0075 6.8418 18.1244C7.14773 18.2507 7.4975 18.5859 7.83609 18.9098C8.42906 19.4796 9.10281 20.125 10 20.125C10.8972 20.125 11.5701 19.4796 12.1639 18.9098C12.5025 18.5859 12.8514 18.2507 13.1582 18.1244C13.4392 18.0075 13.905 18.0006 14.3562 17.9937C15.1949 17.9809 16.1454 17.9671 16.8062 17.3062C17.4671 16.6454 17.4809 15.6949 17.4937 14.8562C17.5006 14.405 17.5075 13.9392 17.6244 13.6582C17.7507 13.3523 18.0859 13.0025 18.4098 12.6639C18.9796 12.0709 19.625 11.3972 19.625 10.5C19.625 9.60281 18.9796 8.92992 18.4098 8.33609ZM17.4173 11.7126C17.0056 12.1423 16.5794 12.5866 16.3534 13.1323C16.1368 13.6565 16.1273 14.2555 16.1187 14.8355C16.1102 15.4371 16.1007 16.067 15.8334 16.3334C15.5662 16.5998 14.9405 16.6102 14.3355 16.6187C13.7555 16.6273 13.1565 16.6368 12.6323 16.8534C12.0866 17.0794 11.6423 17.5056 11.2126 17.9173C10.7829 18.3289 10.3438 18.75 10 18.75C9.65625 18.75 9.21367 18.3272 8.78742 17.9173C8.36117 17.5073 7.91344 17.0794 7.36773 16.8534C6.84352 16.6368 6.24453 16.6273 5.66445 16.6187C5.06289 16.6102 4.43297 16.6007 4.16656 16.3334C3.90016 16.0662 3.88984 15.4405 3.88125 14.8355C3.87266 14.2555 3.8632 13.6565 3.64664 13.1323C3.42062 12.5866 2.99437 12.1423 2.58273 11.7126C2.17109 11.2829 1.75 10.8438 1.75 10.5C1.75 10.1562 2.17281 9.71367 2.58273 9.28742C2.99266 8.86117 3.42062 8.41344 3.64664 7.86773C3.8632 7.34352 3.87266 6.74453 3.88125 6.16445C3.88984 5.56289 3.8993 4.93297 4.16656 4.66656C4.43383 4.40016 5.05945 4.38984 5.66445 4.38125C6.24453 4.37266 6.84352 4.3632 7.36773 4.14664C7.91344 3.92062 8.35773 3.49437 8.78742 3.08273C9.21711 2.67109 9.65625 2.25 10 2.25C10.3438 2.25 10.7863 2.67281 11.2126 3.08273C11.6388 3.49266 12.0866 3.92062 12.6323 4.14664C13.1565 4.3632 13.7555 4.37266 14.3355 4.38125C14.9371 4.38984 15.567 4.3993 15.8334 4.66656C16.0998 4.93383 16.1102 5.55945 16.1187 6.16445C16.1273 6.74453 16.1368 7.34352 16.3534 7.86773C16.5794 8.41344 17.0056 8.85773 17.4173 9.28742C17.8289 9.71711 18.25 10.1562 18.25 10.5C18.25 10.8438 17.8272 11.2863 17.4173 11.7126ZM11.0312 14.9688C11.0312 15.1727 10.9708 15.3721 10.8575 15.5417C10.7441 15.7113 10.5831 15.8434 10.3946 15.9215C10.2062 15.9996 9.99886 16.02 9.79881 15.9802C9.59877 15.9404 9.41502 15.8422 9.2708 15.698C9.12657 15.5537 9.02836 15.37 8.98857 15.1699C8.94877 14.9699 8.9692 14.7625 9.04725 14.5741C9.1253 14.3857 9.25748 14.2246 9.42707 14.1113C9.59666 13.998 9.79604 13.9375 10 13.9375C10.2735 13.9375 10.5358 14.0462 10.7292 14.2395C10.9226 14.4329 11.0312 14.6952 11.0312 14.9688ZM13.4375 8.78125C13.4375 10.2748 12.255 11.5252 10.6875 11.8131V11.875C10.6875 12.0573 10.6151 12.2322 10.4861 12.3611C10.3572 12.4901 10.1823 12.5625 10 12.5625C9.81766 12.5625 9.6428 12.4901 9.51386 12.3611C9.38493 12.2322 9.3125 12.0573 9.3125 11.875V11.1875C9.3125 11.0052 9.38493 10.8303 9.51386 10.7014C9.6428 10.5724 9.81766 10.5 10 10.5C11.137 10.5 12.0625 9.72656 12.0625 8.78125C12.0625 7.83594 11.137 7.0625 10 7.0625C8.86305 7.0625 7.9375 7.83594 7.9375 8.78125V9.125C7.9375 9.30734 7.86507 9.4822 7.73614 9.61114C7.6072 9.74007 7.43234 9.8125 7.25 9.8125C7.06766 9.8125 6.8928 9.74007 6.76386 9.61114C6.63493 9.4822 6.5625 9.30734 6.5625 9.125V8.78125C6.5625 7.07539 8.10422 5.6875 10 5.6875C11.8958 5.6875 13.4375 7.07539 13.4375 8.78125Z"
                    fill="#09192D" />
            </svg>
            <p class="text-xl">سوالات متداول</p>
            <div class="grow h-1 bg-slate-100 rounded-full"></div>
        </div>
        <div class="flex flex-col gap-10 max-w-full w-4/5 mx-auto">
            <div class="accordion flex flex-col gap-3">
                <?php foreach ($data_faq as $item): ?>
                    <div class="accordion-item bg-white border shadow-13 rounded-3xl cursor-pointer">
                        <div class="accordion-title flex justify-between items-center p-4 w-full">
                            <p><?= $item['question'] ?></p>
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M11.3529 2.5C11.3529 1.11929 10.2337 0 8.85294 0C7.47223 0 6.35294 1.11929 6.35294 2.5V6.64706H2.5C1.11929 6.64706 0 7.76635 0 9.14706C0 10.5278 1.11929 11.6471 2.5 11.6471H6.35294V15.5C6.35294 16.8807 7.47223 18 8.85294 18C10.2337 18 11.3529 16.8807 11.3529 15.5V11.6471H15.5C16.8807 11.6471 18 10.5278 18 9.14706C18 7.76635 16.8807 6.64706 15.5 6.64706H11.3529V2.5Z"
                                        fill="#FD7013" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="5" viewBox="0 0 17 5" fill="none" class="hidden">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0 2.5C0 1.11929 1.11929 0 2.5 0H14.5C15.8807 0 17 1.11929 17 2.5C17 3.88071 15.8807 5 14.5 5H2.5C1.11929 5 0 3.88071 0 2.5Z"
                                        fill="#FD7013" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordion-content p-4 pt-0 hidden w-full"><?= $item['answer'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<style>
    .loading-dots {
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }
    
    .loading-dots span {
        display: inline-block;
        animation: loading-dots 1.4s infinite ease-in-out both;
        font-size: inherit;
    }
    
    .loading-dots span:nth-child(1) {
        animation-delay: -0.32s;
    }
    
    .loading-dots span:nth-child(2) {
        animation-delay: -0.16s;
    }
    
    .loading-dots span:nth-child(3) {
        animation-delay: 0s;
    }
    
    @keyframes loading-dots {
        0%, 80%, 100% {
            opacity: 0.3;
            transform: scale(0.8);
        }
        40% {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
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

    // City button filter functionality (same as home page)
    jQuery(document).ready(function($) {
        $('.city-btn-filter').on('click', function() {
            let id = '#' + $(this).attr('data-input') + '-link'
            let textId = id + '-text'
            let term_id = $(this).attr('data-params')
            let city_id = term_id.match(/\[(\d+)\]/)[1];
            let originalText = $(textId).text();
            
            // Show loading animation
            $(textId).html('<span class="loading-dots"><span>.</span><span>.</span><span>.</span></span>');
            
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    action: 'get_category_link',
                    category_id: city_id
                },
                success: function(response) {
                    if (response) {
                        let link = response;
                        $(id).attr('href', link)
                    }
                    // Restore original text
                    $(textId).text(originalText);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error: ' + textStatus);
                    // Restore original text on error too
                    $(textId).text(originalText);
                }
            });
        })
    })
</script>