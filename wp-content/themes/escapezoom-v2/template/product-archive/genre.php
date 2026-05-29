<?php
$product_types = [
    'اتاق فرار',
    'سینما ترس',
    'لیزرتگ',
    'اتاق خشم',
    'کافه بازی',
    'فوتبال حبابی',
    'پینت بال',
    'هانتد هاوس',
];

$genre_name = str_replace('|||||', '', get_term($term_id)->name);
$cities = get_all_cities();
$featuredCities = [];
$searchCities = [];
foreach ($cities as $city) {
    if ($city['is_featured'] === 'on') {
        $featuredCities[] = $city;
    } else {
        $searchCities[] = $city;
    }
}
$image_id = get_term_meta($term_id, 'tag-image-id', true);
$image_url = $image_id ? wp_get_attachment_url($image_id) : Theme_ASSET_URL . 'images/ShowUp/default-genre.avif';
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
    <symbol id="cafe_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 29" fill="none">
        <path d="M10.5007 20.3333H15.1673C16.7144 20.3333 18.1981 19.7188 19.2921 18.6248C20.3861 17.5308 21.0007 16.0471 21.0007 14.5V13.3333H22.1673C23.0956 13.3333 23.9858 12.9646 24.6422 12.3082C25.2986 11.6518 25.6673 10.7616 25.6673 9.83333C25.6673 8.90508 25.2986 8.01484 24.6422 7.35846C23.9858 6.70208 23.0956 6.33333 22.1673 6.33333H21.0007V5.16667C21.0007 4.85725 20.8777 4.5605 20.6589 4.34171C20.4402 4.12292 20.1434 4 19.834 4H5.83398C5.52457 4 5.22782 4.12292 5.00903 4.34171C4.79023 4.5605 4.66732 4.85725 4.66732 5.16667V14.5C4.66732 16.0471 5.2819 17.5308 6.37586 18.6248C7.46982 19.7188 8.95356 20.3333 10.5007 20.3333ZM21.0007 8.66667H22.1673C22.4767 8.66667 22.7735 8.78958 22.9923 9.00838C23.2111 9.22717 23.334 9.52391 23.334 9.83333C23.334 10.1428 23.2111 10.4395 22.9923 10.6583C22.7735 10.8771 22.4767 11 22.1673 11H21.0007V8.66667ZM7.00065 6.33333H18.6673V14.5C18.6673 15.4283 18.2986 16.3185 17.6422 16.9749C16.9858 17.6313 16.0956 18 15.1673 18H10.5007C9.57239 18 8.68215 17.6313 8.02578 16.9749C7.3694 16.3185 7.00065 15.4283 7.00065 14.5V6.33333ZM24.5007 22.6667H3.50065C3.19123 22.6667 2.89449 22.7896 2.67569 23.0084C2.4569 23.2272 2.33398 23.5239 2.33398 23.8333C2.33398 24.1428 2.4569 24.4395 2.67569 24.6583C2.89449 24.8771 3.19123 25 3.50065 25H24.5007C24.8101 25 25.1068 24.8771 25.3256 24.6583C25.5444 24.4395 25.6673 24.1428 25.6673 23.8333C25.6673 23.5239 25.5444 23.2272 25.3256 23.0084C25.1068 22.7896 24.8101 22.6667 24.5007 22.6667Z" fill="#0F172B" />
    </symbol>
    <symbol id="rage_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 19" fill="none">
        <path d="M5.13318 17.3633L4.17297 15.6841L2.5006 16.6562C2.27759 16.7847 2.01264 16.8194 1.76404 16.7527C1.51543 16.6859 1.30354 16.5231 1.17497 16.3001C1.0464 16.0771 1.01168 15.8121 1.07846 15.5635C1.14524 15.3149 1.30804 15.103 1.53104 14.9745L14.0136 7.77793C13.636 6.64163 13.6937 5.40563 14.1754 4.30943C14.6572 3.21323 15.5287 2.33488 16.6211 1.84457C17.7135 1.35427 18.949 1.28691 20.0882 1.65556C21.2274 2.02422 22.1892 2.80262 22.7873 3.83996C23.3853 4.87729 23.577 6.09969 23.3253 7.2703C23.0735 8.44091 22.3961 9.47638 21.4244 10.1761C20.4527 10.8758 19.2559 11.1899 18.0659 11.0575C16.8758 10.9251 15.7773 10.3557 14.9831 9.45963L9.19439 12.7891L10.1665 14.4615C10.2295 14.5719 10.2702 14.6938 10.2861 14.82C10.302 14.9462 10.2929 15.0743 10.2591 15.197C10.2268 15.3201 10.1706 15.4355 10.0936 15.5368C10.0166 15.638 9.92033 15.7231 9.81038 15.7871C9.70009 15.8513 9.57818 15.893 9.45169 15.9098C9.3252 15.9267 9.19663 15.9183 9.07339 15.8852C8.95015 15.8521 8.83469 15.7949 8.73366 15.717C8.63263 15.639 8.54803 15.5418 8.48475 15.431L7.57121 13.7644L5.8895 14.7339L6.86156 16.4063C6.92465 16.5168 6.96532 16.6386 6.98122 16.7648C6.99713 16.891 6.98796 17.0192 6.95425 17.1418C6.92196 17.2649 6.8657 17.3803 6.78869 17.4816C6.71168 17.5829 6.61544 17.6679 6.50549 17.7319C6.39261 17.8032 6.2662 17.8503 6.13422 17.8703C6.00223 17.8903 5.86753 17.8828 5.73861 17.8481C5.60968 17.8135 5.48933 17.7526 5.38513 17.6691C5.28093 17.5857 5.19515 17.4815 5.13318 17.3633ZM21.3966 7.00863C21.5454 6.45472 21.5266 5.86913 21.3426 5.3259C21.1586 4.78268 20.8176 4.30623 20.3628 3.9568C19.908 3.60737 19.3597 3.40065 18.7875 3.36278C18.2152 3.32492 17.6445 3.45761 17.1476 3.74408C16.6507 4.03054 16.2499 4.45792 15.996 4.97216C15.742 5.4864 15.6462 6.06441 15.7207 6.63309C15.7952 7.20178 16.0367 7.7356 16.4146 8.16704C16.7925 8.59849 17.2898 8.90819 17.8437 9.05698C18.5865 9.25649 19.3781 9.15277 20.0444 8.76863C20.7107 8.38449 21.1971 7.7514 21.3966 7.00863Z" fill="#0F172B" stroke="#0F172B" />
    </symbol>
</svg>
<section class="mt-10 grid lg:flex lg:gap-x-17 max-lg:px-4.5">
    <div class="lg:mt-3 lg:w-[57%] shrink-0">
        <h2 class="text-34 lg:text-44">
            <span>بازی‌های<span>
                    <span class="font-black text-44 lg:text-54"><?= $genre_name ?></span>
        </h2>
        <p class="lg:max-w-[580px] font-medium mt-4"><?= get_field('short-description', 'product_tag_ ' . $term_id); ?></p>
        <div class="lg:hidden">
            <img src="<?= $image_url ?>" alt="بازی‌های <?= $genre_name ?>" srcset="">
        </div>
        <!-- citySearch start -->
        <style>
            .embla-cities {
                position: relative;
            }

            .embla-cities .embla__viewport {
                overflow: hidden;
                width: 100%;
                box-sizing: border-box;
            }

            .embla-cities .embla__container {
                display: flex;
                touch-action: pan-y pinch-zoom;
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
                margin-left: 0;
                margin-right: 0;
            }

            .embla-cities .embla__slide {
                flex: 0 0 77px;
                min-width: 0;
            }

            .embla-cities .prev-city-btn,
            .embla-cities .next-city-btn {
                transition: opacity 0.3s ease, transform 0.2s ease;
            }

            .embla-cities .prev-city-btn:disabled,
            .embla-cities .next-city-btn:disabled {
                pointer-events: none;
            }

            .embla-cities .prev-city-btn:hover:not(:disabled),
            .embla-cities .next-city-btn:hover:not(:disabled) {
                transform: scale(1.05);
            }
        </style>
        <div class="my-10 lg:flex lg:justify-between w-full max-lg:max-w-[calc(100vw-4rem)]">
            <input type="hidden" id="default_city" value="tehran" data-room="15" data-cinema="913" data-laser="1147" data-range-room="1047">
            <div class="embla-cities relative w-full lg:max-w-[500px] flex items-center gap-x-2.5">
            <button class="next-city-btn !w-[38px] h-[38px] shrink-0 cursor-pointer hover:scale-105 transition rounded-[8px] bg-slate-100 text-[#889BAD]">
                    <svg class="mx-auto rotate-180 " xmlns="http://www.w3.org/2000/svg" width="8" height="14" viewBox="0 0 8 14" fill="none">
                        <path d="M6.5 12L1.85355 7.35355C1.65829 7.15829 1.65829 6.84171 1.85355 6.64645L6.5 2" stroke="#0F172B" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </button>
                <div class="embla__viewport overflow-hidden">
                    <div class="embla__container flex gap-x-2.5 will-change-transform">
                        <?php foreach ($featuredCities as $city): ?>
                            <button
                                class="city-btn embla__slide flex-[0_0_77px] cursor-pointer hover:scale-105 transition w-[77px] h-[38px] rounded-[8px] bg-slate-100 shrink-0 text-[#889BAD] <?= ($city['slug'] == 'tehran') ? '!bg-primary-500 !text-white' : '' ?>"
                                id="<?php echo esc_attr($city['slug']); ?>"
                                <?php foreach ($city["children"] as $game): ?>
                                data-<?= $game["label"] ?>='<?= $game["id"] ?>'
                                <?php endforeach; ?>>
                                <?php echo esc_html($city['name']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="prev-city-btn !w-[38px] h-[38px] shrink-0 cursor-pointer hover:scale-105 transition rounded-[8px] bg-slate-100 text-[#889BAD]">
                    <svg class="mx-auto" xmlns="http://www.w3.org/2000/svg" width="8" height="14" viewBox="0 0 8 14" fill="none">
                        <path d="M6.5 12L1.85355 7.35355C1.65829 7.15829 1.65829 6.84171 1.85355 6.64645L6.5 2" stroke="#0F172B" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </button>       
            </div>
            <div id="city-search-container" class="relative max-lg:mt-5">
                <form id="city-search-form">
                    <div class="relative">
                        <div id="city-selected" class="absolute inset-0 px-6 flex items-center justify-between bg-white rounded-lg border text-sm h-[48px] lg:h-[58px] w-full lg:w-[250px] hidden">
                            <span>شهر تو <strong class="text-primary-500">قم</strong></span>
                            <button id="city-remove" type="button" class="text-primary-500 text-xl font-bold leading-none">×</button>
                        </div>
                        <input id="city-search" class="text-gray-900 block w-full border bg-white p-1.5 text-sm outline-none placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 py-2 px-3.5 pl-12 h-[38px] rounded-lg placeholder:text-2xs lg:w-[250px]" placeholder="شهر خود را از اینجا انتخاب کن..." data-path="search" type="text" value="" name="s">
                        <div class="absolute left-0 top-0 flex h-full items-center pl-3.5" id="search-form-icon">
                            <button type="submit">
                                <svg class="text-slate-200" xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24">
                                    <circle cx="11.767" cy="11.767" r="8.989" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                                    <path d="M18.018 18.485L21.542 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
                <div id="city-search-list" class="absolute z-10 mt-4 w-full rounded-lg border bg-white shadow-[0px_4px_4px_0px_rgba(0,0,0,.1)] hidden">
                    <div class="relative">
                        <button id="city-search-close" class="text-red-600 hover:text-primary-600 leading-[10px] transition absolute text-[25px] left-3 top-2">×</button>
                        <div id="city-search-result-list" class="max-h-75 overflow-y-auto px-4 py-5 child:block hover:child:bg-[#EDF2F5] child:w-full child:text-right space-y-1.5 child:text-sm child:py-1.5 child:px-5 child:rounded-lg child:leading-6">
                            <?php foreach ($searchCities as $city): ?>
                                <button
                                    class="city-btn"
                                    id="<?php echo esc_attr($city['slug']); ?>"
                                    <?php foreach ($city["children"] as $game): ?>
                                    data-<?= $game["label"] ?>='<?= $game["id"] ?>'
                                    <?php endforeach; ?>>
                                    <?php echo esc_html($city['name']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- citySearch end -->
    </div>
    <div class="lg:w-[43%] shrink-0 max-lg:hidden">
        <img src="<?= $image_url ?>" alt="بازی‌های <?= $genre_name ?>" srcset="">
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
<div class="divider-gradient h-0.5 w-full max-lg:hidden my-4 opacity-50 relative after:absolute after:left-1/2 after:-translate-x-1/2 after:rounded-[50%] after:opacity-50" style="background: radial-gradient(circle, #CAD5E2 0%, transparent 70%);"></div>

<?php
$city_data = get_option("suggested_products_tehran", []);
$product_ids = isset($city_data['products']) ? $city_data['products'] : [];
$city_genre_ids = [];
if ($product_ids && !empty($product_ids)) {
    $args = [
        'tag' => [get_term($term_id)->slug],
        'limit' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'status' => 'publish',
        'include' => $product_ids // فقط محصولاتی که ID آن‌ها در product_ids است
    ];
    $products = wc_get_products($args);
    foreach ($products as $product) {
        $city_genre_ids[] = $product->get_id();
    }
}
if ($city_genre_ids && !empty($city_genre_ids)) {
    $purchased_products_html = json_decode(ez_webservice([
        'type' => 'get_by_products_id',
        'data' => [
            'products_id' => $city_genre_ids,
            'format'      => 'html_swiper',
        ],
    ]));
}
?>

<section id="games_suggests" class="max-w-full py-8 md:py-5 max-md:px-8 max-md:bg-[#E4EBF0] max-md:max-w-none max-md:-ml-4 max-md:-mr-4 <?= ($city_genre_ids && !empty($city_genre_ids)) ? '' : 'hidden' ?>">
    <div class="mb-6 md:mb-8 lg:-mb-px">
        <div class="flex justify-between">
            <div class="flex">
                <div class="items-center md:flex gap-0 lg:[&amp;>h2]:bg-slate-50 lg:[&amp;>h2]:h-full lg:[&amp;>h2]:rounded-tr-4xl lg:[&amp;>h2]:pr-8 [&amp;>h2_b]:text-secondary-500">
                    <h2 class="flex items-center gap-4 relative z-1">
                        <svg class="w-8 lg:w-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44" fill="none">
                            <path d="M12.2232 14.001C12.0273 16.091 11.8898 19.7897 13.1238 21.3641C13.1238 21.3641 12.5429 17.301 17.7507 12.2032C19.8476 10.151 20.3323 7.35971 19.6001 5.26628C19.1841 4.08034 18.4245 3.10065 17.7645 2.41659C17.3795 2.0144 17.6751 1.35096 18.2354 1.37503C21.6248 1.52628 27.1179 2.46815 29.452 8.32565C30.4763 10.8969 30.552 13.5541 30.0638 16.256C29.7545 17.9816 28.6545 21.8178 31.1638 22.2888C32.9548 22.6257 33.821 21.2025 34.2095 20.1782C34.371 19.7519 34.9313 19.6453 35.2338 19.9857C38.2588 23.4266 38.5166 27.4794 37.891 30.9685C36.681 37.7128 29.8507 42.6216 23.0651 42.6216C14.5882 42.6216 7.84039 37.7713 6.09071 28.9919C5.38602 25.4478 5.74352 18.4353 11.2091 13.4853C11.6148 13.1141 12.2782 13.4441 12.2232 14.001Z" fill="url(#paint0_radial_42242_13176)" />
                            <path d="M26.163 26.6131C23.0384 22.5913 24.4374 18.0022 25.204 16.1734C25.3071 15.9328 25.0321 15.7059 24.8155 15.8538C23.4715 16.7681 20.718 18.92 19.4359 21.9484C17.6999 26.0425 17.8237 28.0466 18.8515 30.4941C19.4702 31.9688 18.7518 32.2816 18.3909 32.3366C18.0402 32.3916 17.7171 32.1578 17.4593 31.9138C16.7178 31.2016 16.1893 30.297 15.933 29.3013C15.878 29.0881 15.5996 29.0297 15.4724 29.205C14.5099 30.5353 14.0115 32.67 13.9874 34.1791C13.9118 38.8438 17.7652 42.625 22.4265 42.625C28.3012 42.625 32.5809 36.1281 29.2052 30.6969C28.2255 29.1156 27.3043 28.0809 26.163 26.6131Z" fill="url(#paint1_radial_42242_13176)" />
                            <defs>
                                <radialGradient id="paint0_radial_42242_13176" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(21.3861 42.7285) rotate(-179.751) scale(24.2645 39.8132)">
                                    <stop offset="0.314" stop-color="#FF9800" />
                                    <stop offset="0.662" stop-color="#FF6D00" />
                                    <stop offset="0.972" stop-color="#F44336" />
                                </radialGradient>
                                <radialGradient id="paint1_radial_42242_13176" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(22.7496 18.5826) rotate(90.5787) scale(25.3881 19.1065)">
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
                        <div class="text-xl space-x-1 space-x-reverse">
                            <span>پیشنهاد بازی‌های</span>
                            <span class="font-black"><?= $genre_name ?></span>
                            <span class="font-black city-name">تهران</span>
                        </div>
                    </h2>
                    <div class="hidden md:block" style="margin-right: -260px; margin-bottom: -2px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="689" height="72" viewBox="0 0 689 72" fill="none">
                            <path d="M85.6614 11.3294C95.5028 3.16133 108.347 0 121.136 0L688.5 0V72H0C48.4187 66.4056 54.3516 37.3155 85.6614 11.3294Z" fill="#EFF3F7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rounded-tr-none lg:py-8 lg:px-6 lg:bg-slate-50 rounded-4xl">
        <div class="relative w-full max-sm:max-w-[calc(100%+2rem)] max-sm:w-[calc(100%+2rem)] max-sm:-mr-4">
            <div class="relative overflow-hidden embla_normal slider-event" data-slider-event="discount-slider">
                <div class="embla__viewport">
                    <div id="genre-games-slider" class="embla__container child:bg-white child:p-2.5 md:child:p-5 child:rounded-3xl flex gap-x-4 md:gap-x-6 child:shrink-0 child:grow-0 child:w-[176px] md:child:w-[230px]" style="transform: translate3d(0px, 0px, 0px);">
                        <?php if ($city_genre_ids && !empty($city_genre_ids)): ?>
                            <?= $purchased_products_html ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hidden lg:block lg:opacity-80 [&amp;>button]:block [&amp;>button]:h-full [&amp;>button]:top-0 [&amp;>button]:translate-y-0">
                    <button class="absolute right-0 rotate-180 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--prev genre-games-btn top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Previous slide" aria-controls="genre-games-slider" aria-disabled="false" style="display: none;">
                        <div class="flex h-full items-center justify-center rounded-full bg-white p-4.5 text-slate-150">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                            </svg>
                        </div>
                    </button>
                    <button class="absolute left-0 -translate-y-1/2 appearance-none cursor-pointer embla__button embla__button--next genre-games-btn top-1/2 touch-manipulation" type="button" tabindex="0" aria-label="Next slide" aria-controls="genre-games-slider" aria-disabled="false" style="display: block;">
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

<?php
$counter = 1;
echo '<section id="games-container">';
foreach ($product_types as $product_type) :
    $city_id = null;
    switch ($product_type) {
        case 'اتاق فرار':
            $city_id = 15;
            break;
        case 'سینما ترس':
            $city_id = 913;
            break;
        case 'لیزرتگ':
            $city_id = 1147;
            break;
        case 'اتاق خشم':
            $city_id = 1047;
            break;
    }
    $params = [
        'product_type'  => $product_type,
        'tag'           => [$term_id],
        'sort_type'     => 'topsale',
        "city_id" => [15, 1074, 913, 1147],
    ];
    $args = [
        'source'    => 'cat_sansyab',
        'params'    => $params,
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));
    if (!is_null($products->products) and !empty($products->products) and (strlen($products->products) > 0)):
        $name = $params['product_type'] . ' های';
        if ($product_type == 'اتاق فرار') {
            $name = $params['product_type'] . 'های <span class="font-black">' . $genre_name . '<span> ';
        }
        $icon = null;
        switch ($product_type) {
            case 'اتاق فرار':
                $icon = '<svg width="24" height="19"><use href="#room_id"></use></svg>';
                break;
            case 'سینما ترس':
                $icon = '<svg width="28" height="29"><use href="#cinema_id"></use></svg>';
                break;
            case 'لیزرتگ':
                $icon = '<svg width="28" height="29"><use href="#laser_id"></use></svg>';
                break;
            case 'کافه بردگیم':
                $icon = '<svg width="28" height="29"><use href="#cafe_id"></use></svg>';
                break;
            case 'اتاق خشم':
                $icon = '<svg width="28" height="29"><use href="#rage_id"></use></svg>';
                break;
            default:
                $icon = '<svg width="24" height="19"><use href="#room_id"></use></svg>';
                break;
        }
?>
        <section class="max-w-full py-4 md:py-5 lg:py-9">
            <div class="mb-6 md:mb-8">
                <input type="hidden" id="trends-rooms-<?= $counter ?>" data-source="<?= $args['source'] ?>" data-params='{"sort_type":"topsale","city_id":[<?= $city_id ?>],"tag":[<?= $params['tag'][0] ?>],"product_type":"<?= $params['product_type'] ?>"}'>
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <?php if ($product_type == 'اتاق فرار'): ?>
                            <h1 class="flex items-center gap-4">
                                <?= $icon ?>
                                <div class="text-base font-bold md:text-lg">
                                    <?= $name ?>
                                    <span class="font-black city-name">تهران</span>
                                </div>
                            </h1>
                        <?php else: ?>
                            <h2 class="flex items-center gap-4">
                                <?= $icon ?>
                                <div class="text-base font-bold md:text-lg">
                                    <?= $name ?>
                                    <span class="font-black city-name">تهران</span>
                                </div>
                            </h2>
                        <?php endif; ?>
                    </div>
                    <div class="relative hidden md:block content-center">
                        <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                            <div class="flex gap-2">
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-5 py-1 transition" disabled>
                                    داغ ترین
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"topsale"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                                    پرفروش‌ترین
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"recent"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                                    جدیدترین
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
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                    داغ ترین
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"topsale"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                                    پرفروش‌ترین
                                </button>
                                <button type="button" data-input="trends-rooms-<?= $counter ?>" data-params='sort_type:"recent"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                                    جدیدترین
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden embla_normal horizontal dragFree">
                <div class="embla__viewport">
                    <div id="trends-rooms-<?= $counter ?>-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[190px] child:py-2.5"> <?= $products->products ?></div>
                </div>
                <button class="embla__button embla__button--prev trends-rooms-<?= $counter ?>-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                <button class="embla__button embla__button--next trends-rooms-<?= $counter ?>-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
?>
</section>

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
        let baseUrlWebService = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler"';
        let suggestUrlWebService = '/wp-admin/admin-ajax.php?action=v2_ajax_handler&callback=get_suggested_games';
        if (location.hostname === 'localhost') {
            baseUrlWebService = 'http://' + location.hostname + ':8080/wp-admin/admin-ajax.php?action=v2_ajax_handler';
            suggestUrlWebService = '/wp-admin/admin-ajax.php?action=v2_ajax_handler&callback=get_suggested_games';
        } else if (location.hostname === 'dev.escapezoom.local') {
            baseUrlWebService = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler"';
            suggestUrlWebService = '/wp-admin/admin-ajax.php?action=v2_ajax_handler&callback=get_suggested_games';
        }
        let termId = <?= $term_id ?>;
        let genreName = "<?= $genre_name ?>";

        // راه‌اندازی Embla Carousel برای شهرها
        const initCitiesCarousel = () => {
            if (typeof window.EmblaCarousel === 'undefined') {
                console.warn('EmblaCarousel not loaded');
                return;
            }

            const emblaNode = document.querySelector('.embla-cities');
            const viewportNode = emblaNode.querySelector('.embla__viewport');
            const prevBtn = emblaNode.querySelector('.prev-city-btn');
            const nextBtn = emblaNode.querySelector('.next-city-btn');

            const options = {
                align: 'start',
                direction: 'rtl',
                dragFree: true,
                containScroll: 'trimSnaps'
            };

            const emblaCities = window.EmblaCarousel(viewportNode, options);

            // تابع به‌روزرسانی وضعیت دکمه‌ها
            const updateButtons = () => {
                const canScrollPrev = emblaCities.canScrollPrev();
                const canScrollNext = emblaCities.canScrollNext();

                // دکمه قبل: همیشه نمایش داده می‌شود اما disable می‌شود در ابتدا
                if (prevBtn) {
                    prevBtn.style.display = 'block';
                    prevBtn.disabled = !canScrollPrev;
                    if (canScrollPrev) {
                        prevBtn.style.opacity = '1';
                        prevBtn.style.cursor = 'pointer';
                    } else {
                        prevBtn.style.opacity = '0.4';
                        prevBtn.style.cursor = 'not-allowed';
                    }
                }

                // دکمه بعد: همیشه نمایش داده می‌شود اما disable می‌شود در انتها
                if (nextBtn) {
                    nextBtn.style.display = 'block';
                    nextBtn.disabled = !canScrollNext;
                    if (canScrollNext) {
                        nextBtn.style.opacity = '1';
                        nextBtn.style.cursor = 'pointer';
                    } else {
                        nextBtn.style.opacity = '0.4';
                        nextBtn.style.cursor = 'not-allowed';
                    }
                }
            };

            // Event listeners برای دکمه‌ها
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    if (emblaCities.canScrollPrev()) {
                        emblaCities.scrollPrev();
                    }
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (emblaCities.canScrollNext()) {
                        emblaCities.scrollNext();
                    }
                });
            }

            // به‌روزرسانی دکمه‌ها در هر تغییر
            emblaCities.on('select', updateButtons);
            emblaCities.on('reInit', updateButtons);
            emblaCities.on('settle', updateButtons);

            // فراخوانی اولیه
            updateButtons();
        };

        // راه‌اندازی carousel پس از بارگذاری صفحه
        initCitiesCarousel();

        $("body").on('click', '.show-more', function() {
            $(this).parent().removeAttr('style')
            $(this).remove()
        })
        let $searchInput = $("#city-search");
        let $citySearchList = $("#city-search-list");
        let $searchBox = $("#city-search-list");
        let $searchListClose = $("#city-search-close");
        let $items = $("#city-search-result-list button");
        let $citySelected = $("#city-selected");
        let $cityRemove = $("#city-remove");
        let $searchFormIcon = $('#search-form-icon');

        // تایپ کردن در سرچ
        $searchInput.on("input", function() {
            let val = $(this).val().trim();
            if (val.length > 0) {
                $searchBox.removeClass("hidden");

                // فیلتر شهرها
                $items.each(function() {
                    let text = $(this).text();
                    if (text.includes(val)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

            } else {
                $searchBox.addClass("hidden");
                $items.show();
            }
        });

        // کلیک روی شهر → انتخاب شهر
        $items.on("click", function() {
            let cityName = $(this).text();

            // به‌روزرسانی تمام المان‌هایی که کلاس city-name دارند
            $('.city-name').text(cityName);

            // نمایش تگ انتخاب شهر
            $citySelected.find("strong").text(cityName);
            $('.city-btn').removeClass('!bg-primary-500 !text-white');
            $citySelected.removeClass("hidden");

            // مخفی کردن input و لیست
            $searchBox.addClass("hidden");
            $searchFormIcon.addClass("hidden");

            // دریافت data attributes از دکمه انتخاب شده
            let element = this;
            let dataAttrs = [];
            Array.from(element.attributes).forEach(attr => {
                if (attr.name.startsWith('data-')) {
                    let key = attr.name.replace(/^data-/, '');
                    dataAttrs.push({
                        key: key,
                        value: attr.value
                    });
                }
            });

            // به‌روزرسانی اسلایدرهای محصولات
            updateGenreGamesSlider(dataAttrs, cityName);
        });

        // حذف شهر انتخاب شده
        $cityRemove.on("click", function() {
            $citySelected.addClass("hidden");
            let defultCity = $('#default_city').val();
            let $defaultCityBtn = $(`#${defultCity}`);
            $('.city-btn').removeClass('!bg-primary-500 !text-white');
            $defaultCityBtn.addClass('!bg-primary-500 !text-white');
            let cityName = $defaultCityBtn.text().trim();
            $('.city-name').text(cityName);
            $searchInput.val("").removeClass("hidden").focus();
            $searchBox.removeClass("hidden");
            $searchFormIcon.removeClass("hidden");
            $items.show();

            // استخراج data attributes از دکمه شهر پیش‌فرض
            if ($defaultCityBtn.length) {
                let dataAttrs = [];
                Array.from($defaultCityBtn[0].attributes).forEach(attr => {
                    if (attr.name.startsWith('data-')) {
                        let key = attr.name.replace(/^data-/, '');
                        dataAttrs.push({
                            key: key,
                            value: attr.value
                        });
                    }
                });
                // بارگذاری محصولات با ajax
                updateGenreGamesSlider(dataAttrs, cityName);
            }
        });
        // فوکوس روی input → باز کردن لیست
        $searchInput.on("focus", function() {
            if ($(this).val().trim().length > 0) {
                $searchBox.removeClass("hidden");
            }
        });
        $searchListClose.on('click', function() {
            $citySearchList.addClass("hidden");
            $searchInput.val("").removeClass("hidden").focus();
        })
        let citiesActive = function name(params) {

        }
        $('.city-btn').on('click', function() {
            $('#genre-games-slider').empty().html(`
                <div class="flex items-center justify-center py-16">
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex justify-center">
                            <lottie-player
                                src="https://assets2.lottiefiles.com/packages/lf20_usmfx6bp.json"
                                background="transparent"
                                speed="1"
                                style="width: 64px; height: 64px;"
                                loop
                                autoplay
                            ></lottie-player>
                        </div>
                        <p class="text-lg">در حال بارگذاری محصولات<span class="loading-dots"></span></p>
                    </div>
                </div>
            `);
            let element = this;
            let buttonId = $(this).attr('id');
            let cityName = $(this).text().trim();

            // به‌روزرسانی تمام المان‌هایی که کلاس city-name دارند
            $('.city-name').text(cityName);

            let parentDiv = $(this).parent();
            let filterButtons = parentDiv.find('.city-btn');
            filterButtons.removeClass('!bg-primary-500 !text-white');
            $(this).addClass('!bg-primary-500 !text-white');

            $.ajax({
                type: 'POST',
                url: suggestUrlWebService,
                data: {
                    'tag_id': termId,
                    'slug': buttonId,
                },
                success: function(response) {
                    let productIds = [];
                    try {
                        let res = typeof response === 'string' ? JSON.parse(response) : response;
                        if (Array.isArray(res.product_ids) && res.product_ids.length > 0) {
                            productIds = res.product_ids.map(function(item) {
                                if (typeof item === 'number') return item;
                                if (item && typeof item === 'object' && 'product_id' in item) {
                                    return Number(item.product_id);
                                }
                                return null;
                            }).filter(function(id) {
                                return id !== null;
                            });
                            if (productIds.length > 0) {
                                $.ajax({
                                    type: 'POST',
                                    url: baseUrlWebService,
                                    data: {
                                        "type": "get_by_products_id",
                                        "data": {
                                            "products_id": productIds,
                                            "format": "html_swiper"
                                        }
                                    },
                                    dataType: "json",
                                    success: function(data) {
                                        if (data && data.length > 0) {
                                            $('#games_suggests').removeClass('hidden');
                                            $('#genre-games-slider').empty().html(data);
                                        } else {
                                            $('#games_suggests').addClass('hidden');
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        $('#games_suggests').addClass('hidden');
                                    }
                                });
                            } else {
                                $('#games_suggests').addClass('hidden');
                            }
                        } else {
                            $('#games_suggests').addClass('hidden');
                        }
                    } catch (e) {
                        $('#games_suggests').addClass('hidden');
                    }
                },
            });


            let dataAttrs = [];
            Array.from(element.attributes).forEach(attr => {
                if (attr.name.startsWith('data-')) {
                    // فقط حذف پیشوند data-
                    let key = attr.name.replace(/^data-/, '');
                    dataAttrs.push({
                        key: key,
                        value: attr.value
                    });
                }
            });

            // به‌روزرسانی اسلایدرهای محصولات بر اساس data attributes
            updateGenreGamesSlider(dataAttrs, cityName);
        })

        function updateGenreGamesSlider(dataAttrs, cityName) {
            // ابتدا همه section ها را پاک می‌کنیم و loading JSON نمایش می‌دهیم
            showLoadingAnimation();

            // mapping data attributes به product types
            const attrToProductType = {
                'room': 'اتاق فرار',
                'cinema': 'سینما ترس',
                'laser': 'لیزرتگ',
                'rage-room': 'اتاق خشم'
            };

            // اگر cityName پاس نشده، از عنصر موجود بگیر
            if (!cityName) {
                cityName = $('.city-name').first().text() || 'تهران';
            }



            // جمع‌آوری تمام product types که باید ساخته شوند
            let validProductTypes = [];
            dataAttrs.forEach(function(attr) {
                if (attr.value && attr.value !== '' && attrToProductType[attr.key]) {
                    validProductTypes.push({
                        type: attrToProductType[attr.key],
                        cityId: parseInt(attr.value),
                        attributeKey: attr.key // برای debug
                    });
                }
            });
            if (validProductTypes.length === 0) {
                hideLoadingAnimation();
                $('#dynamic-sliders-container').html('<div class="text-center py-12">هیچ محصولی برای این شهر یافت نشد.</div>');
                return;
            }

            // ساخت تمام اسلایدرها
            createAllSliders(validProductTypes, cityName);
        }

        function showLoadingAnimation() {
            // اسلایدر اول (genre-games-slider) را دست نمی‌زنیم - باید از جای دیگری ساخته شود

            // پاک کردن تمام section های dynamic (فقط آنهایی که در dynamic-sliders-container هستند)
            $('#dynamic-sliders-container').remove();

            // ایجاد container برای اسلایدرهای جدید با انیمیشن لودینگ
            if ($('#dynamic-sliders-container').length === 0) {
                $('#games-container').empty();
            }

            // نمایش انیمیشن لودینگ JSON (می‌توانید Lottie یا هر انیمیشن دیگری استفاده کنید)
            $('#games-container').html(`
                <div class="flex items-center justify-center py-16">
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex justify-center">
                            <lottie-player
                                src="https://assets2.lottiefiles.com/packages/lf20_usmfx6bp.json"
                                background="transparent"
                                speed="1"
                                style="width: 64px; height: 64px;"
                                loop
                                autoplay
                            ></lottie-player>
                        </div>
                        <p class="text-lg">در حال بارگذاری محصولات<span class="loading-dots"></span></p>
                    </div>
                </div>
            `);
        }

        function hideLoadingAnimation() {
            // پاک کردن انیمیشن لودینگ
            $('#games-container').empty();
        }

        function createAllSliders(productTypes, cityName) {
            hideLoadingAnimation();

            // ساخت اسلایدر برای هر product type
            productTypes.forEach(function(item, index) {
                createSliderForProductType(item.type, item.cityId, cityName, index + 1);
            });
        }

        function createSliderForProductType(productType, cityId, cityName, counter) {
            // گرفتن آیکون اصلی
            let productIcon = getProductTypeIcon(productType);

            // ایجاد HTML برای اسلایدر جدید با ساختار کامل
            let sliderHtml = `
                <section class="max-w-full py-4 md:py-5 lg:py-9">
                    <div class="mb-6 md:mb-8">
                        <input type="hidden" id="trends-rooms-${counter}" data-source="cat_sansyab" data-params='{"sort_type":"topsale","city_id":[${cityId}],"tag":[${termId}],"product_type":"${productType}"}'>
                        <div class="flex justify-between">
                            <div class="items-center gap-6 md:flex">
                                ${productType === 'اتاق فرار' 
                                    ? `<h1 class="flex items-center gap-4">
                                            ${productIcon}
                                            <div class="text-base font-bold md:text-lg">
                                                اتاق فرارهای <span class="font-black city-name">${genreName}</span> <span class="font-black city-name">${cityName}</span>
                                            </div>
                                        </h1>`
                                    : `<h2 class="flex items-center gap-4">
                                            ${productIcon}
                                            <div class="text-base font-bold md:text-lg">
                                                ${productType}‌های <span class="font-black city-name">${cityName}</span>
                                            </div>
                                        </h2>`
                                }
                            </div>
                            <div class="relative hidden md:block content-center">
                                <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                                    <div class="flex gap-2">
                                        <button type="button" data-input="trends-rooms-${counter}" data-params='sort_type:"hottest"'
                                            class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-primary-500 text-slate-100 border border-primary-500 h-9 min-w-9 px-3 md:px-5 py-1 transition" disabled>
                                            داغ ترین
                                        </button>
                                        <button type="button" data-input="trends-rooms-${counter}" data-params='sort_type:"topsale"'
                                            class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                                            پرفروش‌ترین
                                        </button>
                                        <button type="button" data-input="trends-rooms-${counter}" data-params='sort_type:"recent"'
                                            class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0 whitespace-nowrap rounded-2xl bg-white text-slate-350 border border-gray-50 h-9 min-w-9 px-3 md:px-5 py-1 hover:bg-primary-600 hover:text-white transition">
                                            جدیدترین
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 md:hidden">
                    <div class="relative block md:hidden">
                        <div class="scrollbar-hide overflow-x-auto transition-all duration-200">
                            <div class="flex border-gray-110 justify-between gap-0 overflow-hidden rounded-lg border">
                                <button type="button" data-input="trends-rooms-${counter}" data-params='sort_type:"hottest"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px bg-primary-500 text-white w-full h-9 min-w-9 px-3 md:px-5 py-1" disabled>
                                    داغ ترین
                                </button>
                                <button type="button" data-input="trends-rooms-${counter}" data-params='sort_type:"topsale"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                                    پرفروش‌ترین
                                </button>
                                <button type="button" data-input="trends-rooms-${counter}" data-params='sort_type:"recent"'
                                    class="filter-btn text-nowrap text-center text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 -m-px text-slate-350 w-full h-9 min-w-9 px-3 md:px-5 py-1">
                                    جدیدترین
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="relative overflow-hidden embla_normal horizontal dragFree">
                        <div class="embla__viewport">
                            <div id="trends-rooms-${counter}-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12 last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[190px] child:py-2.5">
                                <div class="flex items-center justify-center w-full">
                                    <div class="text-center">لطفا منتظر بمانید<span class="loading-dots"></span></div>
                                </div>
                            </div>
                        </div>
                        <button class="embla__button embla__button--prev trends-rooms-${counter}-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                                <g clip-path="url(#arrow_${counter}_prev)">
                                    <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                    <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                </g>
                                <defs><clipPath id="arrow_${counter}_prev"><path fill="#fff" d="M0 0h30v113H0z"></path></clipPath></defs>
                            </svg>
                        </button>
                        <button class="embla__button embla__button--next trends-rooms-${counter}-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                                <g clip-path="url(#arrow_${counter}_next)">
                                    <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                    <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                </g>
                                <defs><clipPath id="arrow_${counter}_next"><path fill="#fff" d="M0 0h30v113H0z"></path></clipPath></defs>
                            </svg>
                        </button>
                    </div>
                </section>
            `;

            // اضافه کردن اسلایدر به container
            $('#games-container').append(sliderHtml);

            // گرفتن reference به section جدید که اضافه شده
            let newSection = $('#games-container section').last();

            // فراخوانی AJAX برای دریافت محصولات
            loadProductsForSlider(counter, cityId, productType, newSection);
        }

        function getProductTypeIcon(productType) {
            // استفاده از آیکون‌های اصلی موجود در کد
            switch (productType) {
                case 'اتاق فرار':
                    return `<svg width="24" height="19"><use href="#room_id"></use></svg>`;
                case 'سینما ترس':
                    return `<svg width="28" height="29"><use href="#cinema_id"></use></svg>`;
                case 'لیزرتگ':
                    return `<svg width="28" height="29"><use href="#laser_id"></use></svg>`;
                case 'اتاق خشم':
                    return `<svg width="28" height="29"><use href="#rage_id"></use></svg>`;
                case 'کافه بردگیم':
                    return `<svg width="28" height="29"><use href="#cafe_id"></use></svg>`;
                default:
                    return `<svg width="24" height="19"><use href="#room_id"></use></svg>`;
            }
        }

        function initializeNewSlider(sectionElement) {

            // بررسی وجود EmblaCarousel
            if (typeof window.EmblaCarousel === 'undefined') {
                return;
            }

            // پیدا کردن carousel در این section
            const carousel = sectionElement.find('.embla_normal')[0];
            if (!carousel) {
                return;
            }

            const viewportNode = carousel.querySelector('.embla__viewport');
            const prevBtn = carousel.querySelector('.embla__button--prev');
            const nextBtn = carousel.querySelector('.embla__button--next');

            if (!viewportNode) {
                return;
            }

            const options = {
                axis: 'x',
                dragFree: true,
                direction: 'rtl',
                align: 'center',
            };

            try {
                // ایجاد اسلایدر جدید
                const embla = window.EmblaCarousel(viewportNode, options);

                // تابع به‌روزرسانی دکمه‌ها
                const updateButtons = () => {
                    const isWide = window.innerWidth > 720;
                    if (prevBtn) prevBtn.style.display = isWide && embla.canScrollPrev() ? 'block' : 'none';
                    if (nextBtn) nextBtn.style.display = isWide && embla.canScrollNext() ? 'block' : 'none';
                };

                // event listeners
                embla.on('select', updateButtons);
                embla.on('reInit', updateButtons);
                updateButtons();

                if (prevBtn) prevBtn.addEventListener('click', () => embla.scrollPrev());
                if (nextBtn) nextBtn.addEventListener('click', () => embla.scrollNext());

                window.addEventListener('resize', updateButtons);
            } catch (error) {}
        }

        function loadProductsForSlider(counter, cityId, productType, sectionElement) {
            $.ajax({
                type: 'POST',
                url: baseUrlWebService,
                data: {
                    "type": "sort_products_get",
                    "data": {
                        "source": "cat_sansyab",
                        "params": {
                            "sort_type": "topsale",
                            "city_id": [cityId],
                            "tag": [termId],
                            "product_type": productType,
                            "page": 1
                        }
                    }
                },
                dataType: "json",
                success: function(data) {
                    if (data.products && data.products.length > 0) {
                        // پیدا کردن slider container داخل section و پر کردن آن
                        let sliderContainer = sectionElement.find('.embla__container');
                        sliderContainer.html(data.products);

                        // راه‌اندازی مجدد اسلایدر Embla
                        setTimeout(function() {
                            initializeNewSlider(sectionElement);
                        }, 100);
                    } else {
                        // اگر محصولی پیدا نشد، کل section را حذف می‌کنیم
                        sectionElement.remove();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // در صورت خطا هم section را حذف می‌کنیم
                    sectionElement.remove();
                }
            });
        }

        // تابع برای بارگذاری HTML بازی‌های پیشنهادی
        function loadSuggestedGamesHTML(productIds, cityName) {
            $.ajax({
                type: 'POST',
                url: baseUrlWebService,
                data: {
                    "type": "get_by_products_id",
                    "data": {
                        "products_id": productIds,
                        "format": "html_swiper"
                    }
                },
                dataType: "json",
                success: function(data) {
                    if (data && data.length > 0) {
                        // پر کردن genre-games-slider با HTML دریافتی
                        $('#genre-games-slider').html(data);

                        // نمایش section
                        $('#games_suggests').removeClass('hidden');

                        // به‌روزرسانی نام شهر در section
                        $('#games_suggests .city-name').text(cityName);

                        // راه‌اندازی مجدد اسلایدر اگر نیاز باشد
                        setTimeout(function() {
                            // اگر اسلایدر Embla راه‌اندازی شده، آن را مجدداً راه‌اندازی کن
                            if (typeof window.EmblaCarousel !== 'undefined') {
                                const viewport = document.querySelector('#games_suggests .embla__viewport');
                                if (viewport) {
                                    const embla = window.EmblaCarousel(viewport, {
                                        axis: 'x',
                                        dragFree: true,
                                        direction: 'rtl',
                                        align: 'start'
                                    });

                                    // به‌روزرسانی دکمه‌های navigation
                                    const updateButtons = () => {
                                        const prevBtn = document.querySelector('#games_suggests .embla__button--prev');
                                        const nextBtn = document.querySelector('#games_suggests .embla__button--next');
                                        const isWide = window.innerWidth > 720;

                                        if (prevBtn) prevBtn.style.display = isWide && embla.canScrollPrev() ? 'block' : 'none';
                                        if (nextBtn) nextBtn.style.display = isWide && embla.canScrollNext() ? 'block' : 'none';
                                    };

                                    embla.on('select', updateButtons);
                                    embla.on('reInit', updateButtons);
                                    updateButtons();

                                    const prevBtn = document.querySelector('#games_suggests .embla__button--prev');
                                    const nextBtn = document.querySelector('#games_suggests .embla__button--next');

                                    if (prevBtn) prevBtn.addEventListener('click', () => embla.scrollPrev());
                                    if (nextBtn) nextBtn.addEventListener('click', () => embla.scrollNext());
                                }
                            }
                        }, 100);
                    } else {
                        console.log('هیچ محصولی برای نمایش یافت نشد');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('خطا در دریافت HTML بازی‌های پیشنهادی:', error);
                }
            });
        }



    })
</script>