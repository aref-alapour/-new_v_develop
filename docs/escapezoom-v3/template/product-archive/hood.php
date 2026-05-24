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

// امروز
$today = date('Y-m-d');
list($todayStart, $todayEnd) = getStartAndEndTimestamps($today);

// فردا
$tomorrow = date('Y-m-d', strtotime('+1 day'));
list($tomorrowStart, $tomorrowEnd) = getStartAndEndTimestamps($tomorrow);

// پس فردا
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
list($dayAfterTomorrowStart, $dayAfterTomorrowEnd) = getStartAndEndTimestamps($dayAfterTomorrow);
$product_types = [
    'اتاق فرار',
    'سینما ترس',
    'لیزرتگ',
    'اتاق خشم',
];
$hood_name = get_term($term_id)->name;

// بررسی و حذف پیشوند اتاق فرار یا بازی های
if (mb_strpos($hood_name, 'اتاق فرار ') === 0) {
    $prefix = 'اتاق فرار';
    $hood_name = trim(mb_substr($hood_name, mb_strlen('اتاق فرار ')));
} elseif (mb_strpos($hood_name, 'بازی های ') === 0) {
    $prefix = 'بازی های';
    $hood_name = trim(mb_substr($hood_name, mb_strlen('بازی های ')));
} else {
    $prefix = 'بازی های';
}
?>
<section class="max-lg:px-4.5 mt-7.5 lg:mt-10 mb-10 gap-10 grid grid-cols-1 lg:grid-cols-5">
    <div class="lg:col-span-3">
        <h1 class="text-44 font-black mb-4"> <?= $prefix ?> <?= $hood_name ?></h1>
        <p class="text-justify"><?= get_field('short-description', 'product_tag_' . $term_id); ?></p>
    </div>
    <div class="col-span-1 lg:col-span-2 overflow-hidden rounded-2xl relative aspect-video">
        <?php
        $icon_field = get_field('icon', 'product_tag_' . $term_id);
        $image_url = !empty($icon_field['url']) ? $icon_field['url'] : get_template_directory_uri() . '/assets/images/hood-default-pic.avif';
        ?>
        <img src="<?php echo $image_url; ?>" alt="<?= $prefix ?> <?= $hood_name ?>" class="w-full h-full object-cover">
    </div>
</section>

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
$counter = 1;
foreach ($product_types as $product_type) :
    $params = [
        'product_type'  => $product_type,
        'tag'           => [$term_id],
    ];
    $args = [
        'source'    => 'hood_page',
        'params'    => $params,
    ];
    $products = ez_products_snapshot_swiper($args);
    if (!is_null($products->products) and !empty($products->products) and (strlen($products->products) > 0)):
        $name = '<span class="inline-block">' . $params['product_type'] . ' های </span> <span class="font-black inline-block">' . $hood_name . '</span>';
        $icon = null;
        switch ($params['product_type']) {
            case 'اتاق فرار':
                $icon = '#room_id';
                break;
            case 'سینما ترس':
                $icon = '#cinema_id';
                break;
            case 'لیزرتگ':
                $icon = '#laser_id';
                break;
            case 'اتاق خشم':
                $icon = '#rage_id';
                break;
            case 'کافه بردگیم':
                $icon = '#cafe_id';
                break;
            default:
                $icon = '#room_id';
                break;
        }
?>

        <section class="max-w-full py-4 md:py-5 lg:py-9">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="trends-rooms-<?= $counter ?>" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"hottest","tag":[<?= $params['tag'][0] ?>],"product_type":"<?= $params['product_type'] ?>"}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <svg class="w-7 h-7">
                                <use href="<?= $icon ?>"></use>
                            </svg>
                            <div class="text-base font-bold md:text-lg">
                                <?= $name ?>
                            </div>
                        </h2>
                    </div>
                    <div class="relative content-center hidden md:block">
                        <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                            <div class="flex gap-2">
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-8 py-1 transition" disabled>
                                    داغ ترین ها
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"topsale"'
                                    class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                                    پرفروش ترین ها
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"popular"'
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
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                    داغ ترین ها
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"topsale"'
                                    class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                                    پرفروش ترین ها
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"popular"'
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
                    <div id="trends-rooms-<?= $counter ?>-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5"> <?= $products->products ?></div>
                </div>
                <button class="embla__button embla__button--prev trends-rooms-<?= $counter ?>-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next trends-rooms-<?= $counter ?>-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
    $counter++;
endforeach;

$params = [
    'tag' => [$term_id],
    'city_id' => -1
];
$args = [
    'source'    => 'genre_page',
    'params'    => $params,
];
$genre_name = str_replace('|||||', '', get_term($term_id)->name);
$products = ez_products_snapshot_swiper($args);

if (!is_null($products->products) and !empty($products->products) and (strlen($products->products) > 0)): ?>
    <div class="mb-6 md:mb-8 mt-8">
        <input type="hidden" id="trends-rooms" data-source="genre_page" data-params='{"sort_type":"hottest","city_id":-1,"tag":[<?= $params['tag'][0] ?>]}'>
        <div class="flex justify-between">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <svg class="w-7 h-7">
                        <use href="#room_id"></use>
                    </svg>
                    <div class="text-base font-bold md:text-lg">
                        <span class="inline-block">بهترین بازی های </span> <span class="font-black inline-block"><?= $hood_name ?></span>
                    </div>
                </h2>
            </div>
            <div class="relative content-center hidden md:block">
                <div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
                    <div class="flex gap-2">
                        <button type="button" data-input="trends-rooms" data-params='sort_type:"hottest"'
                            class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-8 py-1 transition" disabled>
                            داغ ترین ها
                        </button>
                        <button type="button" data-input="trends-rooms" data-params='sort_type:"topsale"'
                            class="flex-shrink-0 px-3 py-1 text-12 font-semibold text-center transition bg-white border filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-8 hover:bg-primary-600 hover:text-white">
                            پرفروش ترین ها
                        </button>
                        <button type="button" data-input="trends-rooms" data-params='sort_type:"popular"'
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
                        <button type="button" data-input="trends-rooms" data-params='sort_type:"hottest"'
                            class="filter-btn text-nowrap text-center text-12 font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                            داغ ترین ها
                        </button>
                        <button type="button" data-input="trends-rooms" data-params='sort_type:"topsale"'
                            class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                            پرفروش ترین ها
                        </button>
                        <button type="button" data-input="trends-rooms" data-params='sort_type:"popular"'
                            class="w-full px-3 py-1 -m-px text-12 font-semibold text-center filter-btn text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 text-slate-350 h-9 min-w-9 md:px-5">
                            محبوب ترین ها
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="trends-rooms-slider" class="grid grid-cols-2 child:box-content child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d190 child:py-2.5 justify-between max-lg:gap-5.5 sm:grid-cols-3 lg:grid-cols-5 2xl:grid-cols-6 3xl:grid-cols-7 gap-6 mt-8">
        <?= $products->products ?>
    </section>
<?php else: ?>
    <section class="text-center mt-4 text-2xl font-extrabold">سرگرمی یافت نشد.</section>
<?php endif; ?>

<?php
/*===============================================================*/
// تخفیف های ویژه بر اساس برچسب

$args = [
    'source' => 'hood_page_discounts_' . $term_id,
];
$discount_products = ez_products_snapshot_swiper($args);
if (!is_null($discount_products->products) and !empty($discount_products->products) and (strlen($discount_products->products) > 0)): ?>
    <div class="max-lg:w-screen max-lg:right-1/2 max-lg:left-1/2 max-lg:-ml-50vw max-lg:-mr-50vw relative lg:hidden overflow-hidden mt-8">
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
            <input type="hidden" id="discount-events-lg" data-source="<?= $args['source'] ?>" data-params='{"schedule":-1}'>
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
// کالکشن های محبوب
//get_template_part('template/layout/collections');
?>




<div class="mx-auto mt-4 relative overflow-hidden" style="height: 352px">
    <?php do_action('woocommerce_archive_description'); ?>
    <button type="button" class="show-more absolute bottom-0 w-full right-0" style="background: linear-gradient(180deg,rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 80%);">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="25" height="50.000000pt" viewBox="0 0 50.000000 50.000000" preserveAspectRatio="xMidYMid meet">
            <g transform="translate(0.000000,50.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
                <path d="M71 286 c-19 -22 -5 -38 88 -99 101 -66 86 -67 203 11 71 47 85 66 67 88 -19 23 -40 16 -111 -32 l-67 -46 -71 46 c-76 49 -91 54 -109 32z" />
            </g>
        </svg>
    </button>
</div>

<script>
    jQuery(document).ready(function($) {
        $("body").on('click', '.show-more', function() {
            $(this).parent().removeAttr('style')
            $(this).remove()
        })
    })
</script>

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