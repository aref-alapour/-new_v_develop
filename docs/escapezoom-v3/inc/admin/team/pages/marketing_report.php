<?php

global $wpdb;

$medoo = medoo();
$medoo_queries = medoo_queries();
$games = $medoo_queries->select('products_data', [
    'product_id',
    'title',
]);
$wc_coupons = $medoo->select('wp_posts', [
    'ID',
    'post_title',
], [
    'post_type' => 'shop_coupon',
    'post_status' => 'publish'
]);
// Get all statistics in one optimized query
$stats_data = $medoo->select('wp_markting', [
    'order_refrerr',
    'order_tickets_quantity',
    'order_coupon_used'
]);

// Initialize counters
$google_referrals_count = 0;
$organic_referrals_count = 0;
$direct_referrals_count = 0;
$total_tickets = 0;
$total_coupons = 0;

// Process data in PHP instead of multiple database queries
foreach ($stats_data as $row) {
    $total_tickets += intval($row['order_tickets_quantity'] ?: 0);
    $total_coupons += intval($row['order_coupon_used'] ?: 0);

    // Use centralized mapping function
    $mapped_source = map_referral_source_to_label($row['order_refrerr']);
    switch ($mapped_source) {
        case 'سئو':
            $organic_referrals_count++;
            break;
        case 'دایرکت':
            $direct_referrals_count++;
            break;
        case 'ADs':
            $google_referrals_count++;
            break;
    }
}
$cities = get_all_cities();
// Initialize variables
$page = intval($_GET['page'] ?? 1);
$per_page = 100;

// Get total count and data for initial load
try {
    $total_count = $medoo->count('wp_markting');
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;

    // Get initial data
    $marketing_data = $medoo->select('wp_markting', [
        'customer_firstname',
        'customer_lastname',
        'customer_phone',
        'order_id',
        'order_created_at',
        'order_tickets_quantity',
        'order_paid',
        'order_refrerr',
        'order_net_profit',
        'game_name',
        'game_product_type',
        'game_city',
        'game_area'
    ], [
        'ORDER' => ['order_created_at' => 'DESC'],
        'LIMIT' => [$offset, $per_page]
    ]);
} catch (Exception $e) {
    error_log('Marketing report error: ' . $e->getMessage());
    $marketing_data = [];
    $total_count = 0;
    $total_pages = 0;
}

?>

<div class="marketing-report-container">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-base font-extrabold lg:text-2xl">گزارش گیری بازاریابی</h1>
        </div>
        <div class="flex gap-4">
            <button id="create-coupon-btn" class="bg-orange-600 text-white w-fit h-8.5 px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors flex items-center justify-between gap-2">
                ساخت کد تخفیف
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </button>
            <button id="marketing-report-btn" class="bg-focus-blue text-white w-fit h-8.5 px-4 py-2 rounded-lg hover:bg-blue-link transition-colors flex items-center justify-between gap-2">
                گزارش مارکتینگ
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </button>
            <button id="financial-report-btn" class="bg-green-600 text-white w-fit h-8.5 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-between gap-2">
                گزارش مالی
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <form id="marketing-report-form" method="POST" class="filter-form">
        <input type="hidden" name="page" value="1">

        <!-- Top Row -->
        <div class="grid grid-cols-16 gap-x-5 mb-5">
            <div class="col-span-3">
                <div class="game-select-container relative">
                    <div class="game-select-trigger filter-input-field cursor-pointer flex items-center justify-between" id="game-select-trigger">
                        <span class="selected-games-text">انتخاب بازی...</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" id="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                    <div class="game-select-dropdown hidden absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto" id="game-select-dropdown">
                        <!-- Search Box -->
                        <div class="p-3 border-b border-gray-200 bg-gray-50">
                            <div class="relative">
                                <input type="text" id="game-search-input" placeholder="جستجو بازی..." class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <svg class="absolute right-2 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <!-- Games List -->
                        <div id="games-list-container">
                            <?php foreach ($games as $game): ?>
                                <label class="game-option flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-game-name="<?php echo esc_attr($game['title']); ?>">
                                    <input type="checkbox" class="game-checkbox mr-3 text-blue-600 focus:ring-blue-500" value="<?php echo esc_attr($game['title']); ?>" data-game-name="<?php echo esc_attr($game['title']); ?>">
                                    <span class="game-name text-sm text-gray-700"><?php echo esc_html($game['title']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" id="selected-games-data" name="game_name" value="">
                </div>
            </div>
            <div class="col-span-3">
                <div class="coupon-select-container relative">
                    <div class="coupon-select-trigger filter-input-field cursor-pointer flex items-center justify-between" id="coupon-select-trigger">
                        <span class="selected-coupons-text">انتخاب کد تخفیف...</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" id="coupon-dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                    <div class="coupon-select-dropdown hidden absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto" id="coupon-select-dropdown">
                        <!-- Search Box -->
                        <div class="p-3 border-b border-gray-200 bg-gray-50">
                            <div class="relative">
                                <input type="text" id="coupon-search-input" placeholder="جستجو کد تخفیف..." class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <svg class="absolute right-2 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Selected Coupons Section -->
                        <div id="dropdown-selected-coupons" class="p-3 border-b border-gray-200 bg-blue-50 hidden">
                            <div class="text-xs text-gray-600 mb-2 font-medium">کدهای انتخاب شده:</div>
                            <div id="dropdown-selected-coupons-list" class="space-y-1"></div>
                        </div>

                        <!-- Search Results -->
                        <div id="coupons-list-container">
                            <div class="p-4 text-center text-gray-500 text-sm">
                                برای جستجو کد تخفیف، حداقل 3 کاراکتر وارد کنید
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="selected-coupons-data" name="discount_code" value="">
                </div>
            </div>
            <div class="col-span-3">
                <input type="text" name="user_id" id="user-id" value="" class="filter-input-field" placeholder="ID کاربر">
            </div>
            <div class="col-span-5">
                <div class="date-range-container relative">
                    <div class="date-range-trigger filter-input-field cursor-pointer flex items-center justify-between" id="date-range-trigger">
                        <span class="date-range-text">انتخاب بازه زمانی</span>
                        <div class="flex items-center gap-2">
                            <button type="button" id="clear-date-range" class="clear-date-btn hidden text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M1.5 9C1.5 6.17175 1.5 4.75725 2.379 3.879C3.258 3.00075 4.67175 3 7.5 3H10.5C13.3282 3 14.7427 3 15.621 3.879C16.4992 4.758 16.5 6.17175 16.5 9V10.5C16.5 13.3282 16.5 14.7427 15.621 15.621C14.742 16.4992 13.3282 16.5 10.5 16.5H7.5C4.67175 16.5 3.25725 16.5 2.379 15.621C1.50075 14.742 1.5 13.3282 1.5 10.5V9Z" stroke="#FF6900" stroke-width="1.5" />
                                <path d="M5.25 3V1.875M12.75 3V1.875M1.875 6.75H16.125" stroke="#FF6900" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M13.5 12.75C13.5 12.9489 13.421 13.1397 13.2803 13.2803C13.1397 13.421 12.9489 13.5 12.75 13.5C12.5511 13.5 12.3603 13.421 12.2197 13.2803C12.079 13.1397 12 12.9489 12 12.75C12 12.5511 12.079 12.3603 12.2197 12.2197C12.3603 12.079 12.5511 12 12.75 12C12.9489 12 13.1397 12.079 13.2803 12.2197C13.421 12.3603 13.5 12.5511 13.5 12.75ZM13.5 9.75C13.5 9.94891 13.421 10.1397 13.2803 10.2803C13.1397 10.421 12.9489 10.5 12.75 10.5C12.5511 10.5 12.3603 10.421 12.2197 10.2803C12.079 10.1397 12 9.94891 12 9.75C12 9.55109 12.079 9.36032 12.2197 9.21967C12.3603 9.07902 12.5511 9 12.75 9C12.9489 9 13.1397 9.07902 13.2803 9.21967C13.421 9.36032 13.5 9.55109 13.5 9.75ZM9.75 12.75C9.75 12.9489 9.67098 13.1397 9.53033 13.2803C9.38968 13.421 9.19891 13.5 9 13.5C8.80109 13.5 8.61032 13.421 8.46967 13.2803C8.32902 13.1397 8.25 12.9489 8.25 12.75C8.25 12.5511 8.32902 12.3603 8.46967 12.2197C8.61032 12.079 8.80109 12 9 12C9.19891 12 9.38968 12.079 9.53033 12.2197C9.67098 12.3603 9.75 12.5511 9.75 12.75ZM9.75 9.75C9.75 9.94891 9.67098 10.1397 9.53033 10.2803C9.38968 10.421 9.19891 10.5 9 10.5C8.80109 10.5 8.61032 10.421 8.46967 10.2803C8.32902 10.1397 8.25 9.94891 8.25 9.75C8.25 9.55109 8.32902 9.36032 8.46967 9.21967C8.61032 9.07902 8.80109 9 9 9C9.19891 9 9.38968 9.07902 9.53033 9.21967C9.67098 9.36032 9.75 9.55109 9.75 9.75ZM6 12.75C6 12.9489 5.92098 13.1397 5.78033 13.2803C5.63968 13.421 5.44891 13.5 5.25 13.5C5.05109 13.5 4.86032 13.421 4.71967 13.2803C4.57902 13.1397 4.5 12.9489 4.5 12.75C4.5 12.5511 4.57902 12.3603 4.71967 12.2197C4.86032 12.079 5.05109 12 5.25 12C5.44891 12 5.63968 12.079 5.78033 12.2197C5.92098 12.3603 6 12.5511 6 12.75ZM6 9.75C6 9.94891 5.92098 10.1397 5.78033 10.2803C5.63968 10.421 5.44891 10.5 5.25 10.5C5.05109 10.5 4.86032 10.421 4.71967 10.2803C4.57902 10.1397 4.5 9.94891 4.5 9.75C4.5 9.55109 4.57902 9.36032 4.71967 9.21967C4.86032 9.07902 5.05109 9 5.25 9C5.44891 9 5.63968 9.07902 5.78033 9.21967C5.92098 9.36032 6 9.55109 6 9.75Z" fill="#FF6900" />
                            </svg>
                        </div>
                    </div>
                    <input type="hidden" id="date-range-data" name="date_range" value="">
                </div>
            </div>
            <div class="col-span-2 flex items-center justify-between gap-x-5">
                <hr class="w-px h-d48 bg-gray-200">
                <button type="button" id="clear-filters" class="clear-filters-btn grow opacity-50 cursor-not-allowed" disabled>
                    حذف فیلتر
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="filter-bottom-row">
            <div class="filter-col-2">
                <select name="order_status" id="order-status" class="filter-input-field">
                    <option value="">وضعیت سفارش</option>
                    <option value="completed">تکمیل شده</option>
                    <option value="pending">در انتظار پرداخت</option>
                    <option value="cancelled">لغو شده</option>
                    <option value="conflict">تداخل داشته</option>
                    <option value="refunded">مسترد شده</option>
                </select>
            </div>
            <div class="filter-col-2">
                <select name="game_type" id="game-type" class="filter-input-field">
                    <option value="">تایپ بازی</option>
                    <option value="اتاق فرار">اتاق فرار</option>
                    <option value="سینما ترس">سینما ترس</option>
                    <option value="لیزرتگ">لیزرتگ</option>
                    <option value="اتاق خشم">اتاق خشم</option>
                    <option value="کافه بردگیم">کافه بردگیم</option>
                </select>
            </div>
            <div class="filter-col-2">
                <select name="source" id="source" class="filter-input-field">
                    <option value="">منبع</option>
                    <?php
                    // Show only main source categories
                    $main_sources = [
                        'seo' => 'سئو',
                        'direct' => 'دایرکت',
                        'ads' => 'ADs',
                        'social' => 'سوشال',
                        'bank' => 'درگاه بانک'
                    ];

                    foreach ($main_sources as $value => $label) {
                        echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="filter-col-2">
                <select name="city" id="city" class="filter-input-field">
                    <option value="">شهر</option>
                    <?php foreach ($cities as $city) { ?>
                        <option value="<?php echo esc_attr($city['name']); ?>"><?php echo esc_html($city['name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="filter-col-2">
                <input type="text" name="region" id="region" class="filter-input-field" placeholder="منطقه">
            </div>
            <button type="button" class="filter-submit-btn" id="show-results-btn">
                نمایش
            </button>
        </div>
    </form>

    <!-- Include Calendar Layout -->
    <?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>

    <!-- Info Bar -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                <span class="text-gray-700">تعداد کل تیکت‌ها <span class="font-semibold text-gray-900">
                        <span id="total-tickets" data-total-tickets="<?php echo number_format($total_tickets); ?>"><?php echo number_format($total_tickets); ?></span>
                        تیکت
                    </span></span>
            </div>
            <div class="w-px h-4 bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-pink-500 rounded-full"></div>
                <span class="text-gray-700">تعداد کل کدهای تخفیف <span class="font-semibold text-gray-900">
                        <span id="total-coupons" data-total-coupons="<?php echo number_format($total_coupons); ?>"><?php echo number_format($total_coupons); ?></span>
                        کد</span></span>
            </div>
            <div class=" w-px h-4 bg-gray-300">
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                <span class="text-gray-700">سرچ ارگانیک <span class="font-semibold text-gray-900">
                        <span id="total-organic-referrals" data-total-organic-referrals="<?php echo number_format($organic_referrals_count); ?>"><?php echo number_format($organic_referrals_count); ?></span>
                        سفارش</span></span>
            </div>
            <div class="w-px h-4 bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                <span class="text-gray-700">مستقیم <span class="font-semibold text-gray-900">
                        <span id="total-direct-referrals" data-total-direct-referrals="<?php echo number_format($direct_referrals_count); ?>"><?php echo number_format($direct_referrals_count); ?></span>
                        سفارش</span></span>
            </div>
            <div class="w-px h-4 bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-focus-blue rounded-full"></div>
                <span class="text-gray-700">گوگل ادز <span class="font-semibold text-gray-900">
                        <span id="total-google-referrals" data-total-google-referrals="<?php echo number_format($google_referrals_count); ?>"><?php echo number_format($google_referrals_count); ?></span>
                        سفارش</span></span>
            </div>



        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="hidden text-center py-8">
        <div class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-800 rounded-lg">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            در حال بارگذاری...
        </div>
    </div>

    <!-- Results Section -->
    <div id="results-section">
        <!-- Initial empty state - no data loaded until filters are applied -->
        <div class="text-center py-12">
            <div class="text-gray-500 space-y-4">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">برای مشاهده گزارش، فیلترهای مورد نظر را انتخاب کنید</h3>
                    <p class="text-sm text-gray-500 mt-2">لطفاً حداقل یک فیلتر انتخاب کنید و روی دکمه "نمایش نتایج" کلیک کنید</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Coupon Creation Modal -->
    <div id="coupon-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">ساخت کد تخفیف جدید</h3>
                    <button id="close-coupon-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="coupon-form" class="p-6 space-y-4">
                    <!-- First Row: Coupon Code and Discount Type -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label for="coupon-code" class="block text-sm font-medium text-gray-700 mb-2">کد تخفیف</label>
                            <div class="flex gap-2">
                                <input type="text" id="coupon-code" name="coupon_code" class="flex-1 px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="eszm-gameoff-2025" required>
                                <button type="button" id="generate-coupon-code" class="px-3 h-d43 content-center bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors text-xs">
                                    تولید
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="discount-type" class="block text-sm font-medium text-gray-700 mb-2">نوع تخفیف</label>
                            <select id="discount-type" name="discount_type" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                                <option value="percent">درصدی</option>
                                <option value="fixed_cart">مبلغ ثابت</option>
                            </select>
                        </div>
                        <div>
                            <label for="coupon-amount" class="block text-sm font-medium text-gray-700 mb-2">مقدار تخفیف</label>
                            <input type="number" id="coupon-amount" name="coupon_amount" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="10" min="0" step="0.01" required>
                            <p class="text-xs text-gray-500 mt-1">درصدی: 0-100، ثابت: تومان</p>
                        </div>
                        <div>
                            <label for="minimum-amount" class="block text-sm font-medium text-gray-700 mb-2">حداقل مبلغ سفارش</label>
                            <input type="number" id="minimum-amount" name="minimum_amount" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="0" min="0" step="0.01">
                        </div>
                        <div>
                            <label for="maximum-discount" class="block text-sm font-medium text-gray-700 mb-2">حداکثر مبلغ تخفیف (در حالت درصدی)</label>
                            <input type="number" id="maximum-discount" name="maximum_discount" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="بدون محدودیت" min="0" step="0.01">
                        </div>
                        <div>
                            <label for="usage-limit" class="block text-sm font-medium text-gray-700 mb-2">حد استفاده از کد</label>
                            <input type="number" id="usage-limit" name="usage_limit" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="بدون محدودیت" min="1">
                        </div>
                        <div>
                            <label for="usage-limit-per-user" class="block text-sm font-medium text-gray-700 mb-2">حد استفاده هر کاربر</label>
                            <input type="number" id="usage-limit-per-user" name="usage_limit_per_user" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="بدون محدودیت" min="1">
                        </div>
                        <div>
                            <label for="created-date" class="block text-sm font-medium text-gray-700 mb-2">تاریخ ایجاد</label>
                            <div class="relative">
                                <input type="text" id="created-date" name="created_date" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 persian-datepicker" placeholder="تاریخ ایجاد" readonly>
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <label for="expiry-date" class="block text-sm font-medium text-gray-700 mb-2">تاریخ انقضا</label>
                            <div class="relative">
                                <input type="text" id="expiry-date" name="expiry_date" class="w-full px-3 h-d43 content-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 persian-datepicker" placeholder="تاریخ انقضا" readonly>
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="coupon-description" class="block text-sm font-medium text-gray-700 mb-2">توضیحات</label>
                        <textarea id="coupon-description" name="coupon_description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" rows="2" placeholder="توضیحات کد تخفیف..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" id="create-coupon-submit" class="flex-1 bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700 transition-colors">
                            ایجاد کد تخفیف
                        </button>
                        <button type="button" id="cancel-coupon" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition-colors">
                            انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Marketing Report Export Modal -->
<div id="marketing-report-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">گزارش مارکتینگ</h3>
            <button id="close-marketing-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-gray-600 mb-6">نوع گزارش مورد نظر خود را انتخاب کنید:</p>
        <div class="flex gap-4">
            <button id="marketing-excel" class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Excel
            </button>
            <button id="marketing-csv" class="flex-1 bg-focus-blue text-white py-3 px-4 rounded-lg hover:bg-blue-link transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                CSV
            </button>
        </div>
    </div>
</div>

<!-- Financial Report Export Modal -->
<div id="financial-report-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">گزارش مالی</h3>
            <button id="close-financial-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-gray-600 mb-4">نوع گزارش مورد نظر خود را انتخاب کنید:</p>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6">
            <p class="text-yellow-800 text-sm">
                <svg class="inline w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                گزارش مالی فقط شامل سفارش‌های تکمیل شده است.
            </p>
        </div>
        <div class="flex gap-4">
            <button id="financial-excel" class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Excel
            </button>
            <button id="financial-csv" class="flex-1 bg-focus-blue text-white py-3 px-4 rounded-lg hover:bg-blue-link transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                CSV
            </button>
        </div>
    </div>
</div>

<style>
    .marketing-report-container {
        direction: rtl;
    }

    .marketing-report-container table {

        border-collapse: separate;
        border-spacing: 0;
    }

    .marketing-report-container th,
    .marketing-report-container td {
        text-align: right;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .marketing-report-container th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .marketing-report-container tbody tr:nth-child(even) {
        background-color: #f9fafb;
    }

    .marketing-report-container tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }

    .marketing-report-container tbody tr:hover {
        background-color: #eff6ff !important;
    }

    .marketing-report-container input,
    .marketing-report-container select {}

    /* Game name links */
    .marketing-report-container tbody td a {
        color: #2563eb;
        text-decoration: none;
    }

    .marketing-report-container tbody td a:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    /* Pagination Styles - Simple */
    .pagination-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    .pagination-btn:hover {
        background-color: #f9fafb;
        color: #374151;
    }

    .pagination-btn.active {
        color: white;
        background-color: #f97316;
        border-color: #f97316;
    }

    .pagination-btn.disabled {
        background-color: #f3f4f6;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }

    /* RTL Support for pagination */
    .space-x-reverse> :not([hidden])~ :not([hidden]) {
        --tw-space-x-reverse: 1;
        margin-right: calc(0.5rem * var(--tw-space-x-reverse));
        margin-left: calc(0.5rem * calc(1 - var(--tw-space-x-reverse)));
    }

    /* Filter Section Styles */
    .filter-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        text-align: right;
        transition: all 0.2s ease-in-out;
    }

    .filter-input:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
    }

    .filter-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        text-align: right;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: left 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-left: 2.5rem;
        transition: all 0.2s ease-in-out;
    }

    .filter-select:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
    }

    /* Info Bar Styles */
    .info-bar {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-dot {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
    }

    .info-separator {
        width: 1px;
        height: 1rem;
        background-color: #d1d5db;
    }

    /* Filter Form Styles */
    .filter-form {
        margin-bottom: 1.5rem;
    }

    .filter-top-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .filter-bottom-row {
        display: grid;
        grid-template-columns: repeat(11, 1fr);
        gap: 1.75rem;
    }

    .filter-col-2 {
        grid-column: span 2;
    }

    .filter-input-field {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        text-align: right;
        transition: all 0.2s ease-in-out;
        height: 48px;
    }

    .filter-input-field:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
    }

    /* Select dropdown arrow styling */
    .filter-input-field[type="text"] {
        /* No arrow for text inputs */
    }

    select.filter-input-field {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: left 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-left: 2.5rem;
        padding-right: 0.75rem;
        /* Remove default browser arrow */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* For Firefox */
    select.filter-input-field::-moz-appearance {
        text-indent: 1px;
        text-overflow: '';
    }

    /* For IE */
    select.filter-input-field::-ms-expand {
        display: none;
    }

    .filter-submit-btn {
        background-color: #f97316;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        height: 48px;
        width: 100%;
    }

    .filter-submit-btn:hover {
        background-color: #ea580c;
    }

    .clear-filters-btn {
        background-color: #f3f4f6;
        color: #6b7280;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .clear-filters-btn:hover {
        background-color: #e5e7eb;
    }

    /* Game Select Dropdown Styles */
    .game-select-container {
        position: relative;
    }

    .game-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 40px;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background-color: white;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .game-select-trigger:hover {
        border-color: #9ca3af;
    }

    .game-select-trigger:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .selected-games-text {
        color: #374151;
        font-size: 0.875rem;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .selected-games-text.placeholder {
        color: #9ca3af;
    }

    #dropdown-arrow {
        transition: transform 0.2s;
        flex-shrink: 0;
    }

    #dropdown-arrow.rotated {
        transform: rotate(180deg);
    }

    .game-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        z-index: 50;
        max-height: 16rem;
        overflow-y: auto;
        margin-top: 0.375rem;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Custom scrollbar for dropdown */
    .game-select-dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .game-select-dropdown::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .game-select-dropdown::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .game-select-dropdown::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .game-select-dropdown.hidden {
        display: none;
    }

    .game-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s ease-in-out;
        position: relative;
    }

    .game-option:hover {
        background-color: #f8fafc;
        transform: translateX(2px);
    }

    .game-option:last-child {
        border-bottom: none;
    }

    .game-option:active {
        background-color: #e2e8f0;
        transform: translateX(1px);
    }

    /* Selected game option styling */
    .game-option:has(.game-checkbox:checked) {
        background-color: #eff6ff;
        border-left: 3px solid #3b82f6;
    }

    .game-option:has(.game-checkbox:checked):hover {
        background-color: #dbeafe;
    }

    .game-checkbox {
        width: 1.125rem;
        height: 1.125rem;
        color: #3b82f6;
        border-radius: 0.375rem;
        border: 2px solid #d1d5db;
        background-color: white;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        position: relative;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    .game-checkbox:hover {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .game-checkbox:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .game-checkbox:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
        line-height: 1;
    }

    .game-checkbox:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .game-checkbox:active {
        transform: scale(0.95);
    }

    .game-name {
        font-size: 0.875rem;
        color: #374151;
        flex: 1;
        font-weight: 500;
        transition: color 0.2s ease-in-out;
    }

    .game-option:hover .game-name {
        color: #1f2937;
    }

    .game-option:has(.game-checkbox:checked) .game-name {
        color: #1e40af;
        font-weight: 600;
    }

    /* Coupon Select Dropdown Styles */
    .coupon-select-container {
        position: relative;
    }

    .coupon-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 40px;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background-color: white;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .coupon-select-trigger:hover {
        border-color: #9ca3af;
    }

    .coupon-select-trigger:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .selected-coupons-text {
        color: #374151;
        font-size: 0.875rem;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .selected-coupons-text.placeholder {
        color: #9ca3af;
    }

    #coupon-dropdown-arrow {
        transition: transform 0.2s;
        flex-shrink: 0;
    }

    #coupon-dropdown-arrow.rotated {
        transform: rotate(180deg);
    }

    .coupon-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        z-index: 50;
        max-height: 16rem;
        overflow-y: auto;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Custom scrollbar for coupon dropdown */
    .coupon-select-dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .coupon-select-dropdown::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .coupon-select-dropdown::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .coupon-select-dropdown::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .coupon-select-dropdown.hidden {
        display: none;
    }

    .coupon-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
    }

    .coupon-option:hover {
        background-color: #f9fafb;
    }

    .coupon-option:last-child {
        border-bottom: none;
    }

    .coupon-checkbox {
        margin-left: 0.75rem;
    }

    .coupon-name {
        font-size: 0.875rem;
        color: #374151;
        flex: 1;
        font-weight: 500;
        transition: color 0.2s ease-in-out;
    }

    .coupon-option:hover .coupon-name {
        color: #1f2937;
    }

    .coupon-option:has(.coupon-checkbox:checked) .coupon-name {
        color: #1e40af;
        font-weight: 600;
    }

    /* Selected Coupons Display (inside dropdown only) */
    .selected-coupon-option {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        cursor: pointer;
        border-bottom: 1px solid #dbeafe;
        background-color: #f8fafc;
    }

    .selected-coupon-option:hover {
        background-color: #dbeafe;
    }

    .selected-coupon-option:last-child {
        border-bottom: none;
    }

    .selected-coupon-checkbox {
        margin-left: 0.75rem;
    }

    .selected-coupon-name {
        font-size: 0.875rem;
        color: #374151;
        flex: 1;
        font-weight: 500;
    }

    /* Date Range Styles */
    .date-range-container {
        position: relative;
    }

    .date-range-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 40px;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background-color: white;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .date-range-trigger:hover {
        border-color: #9ca3af;
    }

    .date-range-trigger:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .date-range-text {
        color: #374151;
        font-size: 0.875rem;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .date-range-text.placeholder {
        color: #9ca3af;
    }

    .clear-date-btn {
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: background-color 0.2s;
    }

    .clear-date-btn:hover {
        background-color: #f3f4f6;
    }

    .clear-date-btn.hidden {
        display: none;
    }


    /* Search Box Styles */
    #game-search-input {
        font-size: 0.875rem;
        color: #374151;
    }

    #game-search-input::placeholder {
        color: #9ca3af;
    }

    #game-search-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }



    /* Table responsive */
    @media (max-width: 768px) {
        .marketing-report-container table {
            font-size: 12px;
        }

        .marketing-report-container th,
        .marketing-report-container td {
            padding: 8px 12px;
        }

        .pagination-btn {
            @apply w-8 h-8 text-xs;
        }
    }
</style>
<!-- Include Calendar Module -->
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/calendar-module.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form submission
        const form = document.querySelector('form');
        const clearFiltersBtn = document.getElementById('clear-filters');
        const showResultsBtn = document.getElementById('show-results-btn');

        // Function to log all filters
        function logAllFilters() {
            const formData = new FormData(form);
            const filters = {};

            // Log all form fields
            for (let [key, value] of formData.entries()) {
                if (key !== 'page' && value && value.trim() !== '') {
                    filters[key] = value;
                }
            }
            return filters;
        }



        // Show results button click handler
        if (showResultsBtn) {
            showResultsBtn.addEventListener('click', function() {
                const filters = logAllFilters();

                loadData(1);
                showClearFiltersButton();
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted!');
            const filters = logAllFilters();
            loadData(1);
            showClearFiltersButton();
        });

        // Function to reset summary statistics to default values
        function resetSummaryStatistics() {
            // Get default values from data attributes
            const totalTicketsEl = document.getElementById('total-tickets');
            if (totalTicketsEl && totalTicketsEl.dataset.totalTickets) {
                totalTicketsEl.textContent = totalTicketsEl.dataset.totalTickets;
            }

            const totalCouponsEl = document.getElementById('total-coupons');
            if (totalCouponsEl && totalCouponsEl.dataset.totalCoupons) {
                totalCouponsEl.textContent = totalCouponsEl.dataset.totalCoupons;
            }

            const organicEl = document.getElementById('total-organic-referrals');
            if (organicEl && organicEl.dataset.totalOrganicReferrals) {
                organicEl.textContent = organicEl.dataset.totalOrganicReferrals;
            }

            const directEl = document.getElementById('total-direct-referrals');
            if (directEl && directEl.dataset.totalDirectReferrals) {
                directEl.textContent = directEl.dataset.totalDirectReferrals;
            }

            const googleEl = document.getElementById('total-google-referrals');
            if (googleEl && googleEl.dataset.totalGoogleReferrals) {
                googleEl.textContent = googleEl.dataset.totalGoogleReferrals;
            }

        }

        // Function to show clear filters button
        function showClearFiltersButton() {
            if (clearFiltersBtn) {
                clearFiltersBtn.classList.remove('hidden');
            }
        }

        // Function to hide clear filters button
        function hideClearFiltersButton() {
            if (clearFiltersBtn) {
                clearFiltersBtn.classList.add('hidden');
            }
        }

        // Check if any filter has value on page load
        function checkFiltersOnLoad() {
            const filterInputs = form.querySelectorAll('input:not([name="page"]), select');
            let hasFilters = false;

            filterInputs.forEach(input => {
                if (input.value && input.value.trim() !== '') {
                    hasFilters = true;
                }
            });

            if (hasFilters) {
                showClearFiltersButton();
            } else {
                hideClearFiltersButton();
            }
        }

        // Check filters on page load
        checkFiltersOnLoad();

        // Add event listeners for all form fields
        function addFormEventListeners() {
            // Get all form inputs except page and hidden inputs
            const formInputs = form.querySelectorAll('input:not([name="page"]):not([type="hidden"]), select');

            console.log('Found form inputs:', formInputs.length);

            formInputs.forEach(input => {
                console.log('Adding listener to:', input.name, input.type);

                input.addEventListener('change', function() {
                    console.log('Form field changed:', input.name, 'value:', input.value);
                    updateClearFiltersButton();
                });

                input.addEventListener('input', function() {
                    console.log('Form field input:', input.name, 'value:', input.value);
                    updateClearFiltersButton();
                });
            });
        }

        // Initialize form event listeners
        addFormEventListeners();

        // Game select dropdown functionality
        const gameSelectTrigger = document.getElementById('game-select-trigger');
        const gameSelectDropdown = document.getElementById('game-select-dropdown');
        const dropdownArrow = document.getElementById('dropdown-arrow');
        const selectedGamesText = document.querySelector('.selected-games-text');
        const selectedGamesData = document.getElementById('selected-games-data');
        const gameSearchInput = document.getElementById('game-search-input');
        const gamesListContainer = document.getElementById('games-list-container');
        const userIdInput = document.getElementById('user-id');
        const regionInput = document.getElementById('region');
        const orderStatusInput = document.getElementById('order-status');
        const gameTypeInput = document.getElementById('game-type');
        const sourceInput = document.getElementById('source');
        const cityInput = document.getElementById('city');

        // Add event listeners for individual form fields
        if (userIdInput) {
            userIdInput.addEventListener('input', function() {
                updateClearFiltersButton();
            });
        }

        if (regionInput) {
            regionInput.addEventListener('input', function() {
                updateClearFiltersButton();
            });
        }

        if (orderStatusInput) {
            orderStatusInput.addEventListener('change', function() {
                updateClearFiltersButton();
            });
        }

        if (gameTypeInput) {
            gameTypeInput.addEventListener('change', function() {
                updateClearFiltersButton();
            });
        }

        if (sourceInput) {
            sourceInput.addEventListener('change', function() {
                updateClearFiltersButton();
            });
        }

        if (cityInput) {
            cityInput.addEventListener('change', function() {
                updateClearFiltersButton();
            });
        }
        let allGameOptions = [];
        let filteredGameOptions = [];

        // Initialize
        function initializeGameSelect() {
            allGameOptions = Array.from(document.querySelectorAll('.game-option'));
            filteredGameOptions = [...allGameOptions];

            // Make sure all games are initially visible
            allGameOptions.forEach(option => {
                option.style.display = 'block';
            });

            // Sort games: selected first, then unselected
            sortGameOptions();
        }

        // Toggle dropdown
        if (gameSelectTrigger) {
            gameSelectTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropdown();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.game-select-container')) {
                closeDropdown();
            }
        });

        // Search functionality
        if (gameSearchInput) {
            gameSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                filterGames(searchTerm);
            });

            // Prevent search input from closing dropdown
            gameSearchInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Handle checkbox changes - use event delegation
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('game-checkbox')) {
                updateSelectedGames();
                // Force re-render and sort
                setTimeout(() => {
                    sortGameOptions();
                }, 10);
            }
        });

        function toggleDropdown() {
            if (gameSelectDropdown.classList.contains('hidden')) {
                openDropdown();
            } else {
                closeDropdown();
            }
        }

        function openDropdown() {
            gameSelectDropdown.classList.remove('hidden');
            dropdownArrow.classList.add('rotated');
            initializeGameSelect();
            // Ensure proper sorting when opening
            setTimeout(() => {
                sortGameOptions();
                if (gameSearchInput) {
                    gameSearchInput.focus();
                }
            }, 50);
        }

        function closeDropdown() {
            gameSelectDropdown.classList.add('hidden');
            dropdownArrow.classList.remove('rotated');
            // Clear search when closing
            if (gameSearchInput) {
                gameSearchInput.value = '';
            }
        }

        function filterGames(searchTerm) {
            // First sort all options, then filter
            allGameOptions.sort((a, b) => {
                const aCheckbox = a.querySelector('.game-checkbox');
                const bCheckbox = b.querySelector('.game-checkbox');
                const aSelected = aCheckbox ? aCheckbox.checked : false;
                const bSelected = bCheckbox ? bCheckbox.checked : false;

                if (aSelected && !bSelected) return -1;
                if (!aSelected && bSelected) return 1;
                return 0;
            });

            // Re-append sorted elements to maintain DOM order
            const container = allGameOptions[0]?.parentNode;
            if (container) {
                allGameOptions.forEach(option => {
                    container.appendChild(option);
                });
            }

            filteredGameOptions = allGameOptions.filter(option => {
                const gameName = option.dataset.gameName.toLowerCase();
                return gameName.includes(searchTerm);
            });

            renderFilteredGames();
        }

        function renderFilteredGames() {
            // Hide all games first
            allGameOptions.forEach(option => {
                option.style.display = 'none';
            });

            // Show only filtered games
            filteredGameOptions.forEach(option => {
                option.style.display = 'block';
            });
        }

        function sortGameOptions() {
            // Sort all options: selected first, then unselected
            allGameOptions.sort((a, b) => {
                const aCheckbox = a.querySelector('.game-checkbox');
                const bCheckbox = b.querySelector('.game-checkbox');
                const aSelected = aCheckbox ? aCheckbox.checked : false;
                const bSelected = bCheckbox ? bCheckbox.checked : false;

                if (aSelected && !bSelected) return -1;
                if (!aSelected && bSelected) return 1;
                return 0;
            });

            // Re-append sorted elements to maintain DOM order
            const container = allGameOptions[0]?.parentNode;
            if (container) {
                allGameOptions.forEach(option => {
                    container.appendChild(option);
                });
            }

            // Always re-render if dropdown is open
            if (!gameSelectDropdown.classList.contains('hidden')) {
                // Update filtered options based on current search
                const searchTerm = gameSearchInput ? gameSearchInput.value.toLowerCase().trim() : '';
                if (searchTerm) {
                    filteredGameOptions = allGameOptions.filter(option => {
                        const gameName = option.dataset.gameName.toLowerCase();
                        return gameName.includes(searchTerm);
                    });
                } else {
                    filteredGameOptions = [...allGameOptions];
                }
                renderFilteredGames();
            }
        }

        function updateSelectedGames() {
            // Get selected games from allGameOptions (the source of truth)
            const selectedGamesArray = allGameOptions
                .map(option => {
                    const checkbox = option.querySelector('.game-checkbox');
                    return checkbox && checkbox.checked ? checkbox.dataset.gameName : null;
                })
                .filter(name => name !== null);

            // Update the Set
            selectedGames.clear();
            selectedGamesArray.forEach(game => selectedGames.add(game));

            // Update display text
            if (selectedGamesArray.length === 0) {
                selectedGamesText.textContent = 'انتخاب بازی...';
                selectedGamesText.classList.add('placeholder');
            } else if (selectedGamesArray.length === 1) {
                selectedGamesText.textContent = selectedGamesArray[0];
                selectedGamesText.classList.remove('placeholder');
            } else {
                selectedGamesText.textContent = `${selectedGamesArray.length} بازی انتخاب شده`;
                selectedGamesText.classList.remove('placeholder');
            }

            // Update hidden input with selected games
            selectedGamesData.value = selectedGamesArray.join(',');

            // Update clear filters button
            updateClearFiltersButton();
        }

        function clearSelectedGames() {
            // Clear all game checkboxes in the original list
            allGameOptions.forEach(option => {
                const checkbox = option.querySelector('.game-checkbox');
                if (checkbox) {
                    checkbox.checked = false;
                }
            });

            // Clear the Set
            selectedGames.clear();

            // Update display
            selectedGamesText.textContent = 'انتخاب بازی...';
            selectedGamesText.classList.add('placeholder');
            selectedGamesData.value = '';

            // Update clear filters button
            updateClearFiltersButton();
        }

        // Coupon select dropdown functionality
        const couponSelectTrigger = document.getElementById('coupon-select-trigger');
        const couponSelectDropdown = document.getElementById('coupon-select-dropdown');
        const couponDropdownArrow = document.getElementById('coupon-dropdown-arrow');
        const selectedCouponsText = document.querySelector('.selected-coupons-text');
        const selectedCouponsData = document.getElementById('selected-coupons-data');
        const couponSearchInput = document.getElementById('coupon-search-input');
        const couponsListContainer = document.getElementById('coupons-list-container');
        const dropdownSelectedCoupons = document.getElementById('dropdown-selected-coupons');
        const dropdownSelectedCouponsList = document.getElementById('dropdown-selected-coupons-list');

        // Date range elements
        const dateRangeTrigger = document.getElementById('date-range-trigger');
        const dateRangeText = document.querySelector('.date-range-text');
        const clearDateBtn = document.getElementById('clear-date-range');
        const dateRangeData = document.getElementById('date-range-data');

        let selectedGames = new Set();
        let selectedCoupons = new Set();
        let searchTimeout;
        let selectedDateRange = null;

        // Toggle dropdown
        if (couponSelectTrigger) {
            couponSelectTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleCouponDropdown();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.coupon-select-container')) {
                closeCouponDropdown();
            }
        });

        // Search functionality with debounce
        if (couponSearchInput) {
            couponSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim();

                // Clear previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Set new timeout for search
                searchTimeout = setTimeout(() => {
                    if (searchTerm.length >= 3) {
                        searchCoupons(searchTerm);
                    } else if (searchTerm.length === 0) {
                        showPlaceholder();
                    } else {
                        showMinLengthMessage();
                    }
                }, 300); // 300ms delay
            });

            // Prevent search input from closing dropdown
            couponSearchInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Handle checkbox changes - use event delegation
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('coupon-checkbox')) {
                const couponName = e.target.dataset.couponName;
                const isChecked = e.target.checked;

                if (isChecked) {
                    selectedCoupons.add(couponName);
                } else {
                    selectedCoupons.delete(couponName);
                }

                updateSelectedCoupons();
            }

            // Handle selected coupon checkbox changes
            if (e.target.classList.contains('selected-coupon-checkbox')) {
                const couponName = e.target.dataset.couponName;
                const isChecked = e.target.checked;

                if (!isChecked) {
                    selectedCoupons.delete(couponName);
                    updateSelectedCoupons();
                }
            }
        });

        // Handle remove button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-coupon-btn')) {
                const couponName = e.target.dataset.couponName;
                selectedCoupons.delete(couponName);
                updateSelectedCoupons();
            }
        });

        // Date range functionality
        if (dateRangeTrigger) {
            dateRangeTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                openDatePicker();
            });
        }

        if (clearDateBtn) {
            clearDateBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                clearDateRange();
            });
        }

        function toggleCouponDropdown() {
            if (couponSelectDropdown.classList.contains('hidden')) {
                openCouponDropdown();
            } else {
                closeCouponDropdown();
            }
        }

        function openCouponDropdown() {
            couponSelectDropdown.classList.remove('hidden');
            couponDropdownArrow.classList.add('rotated');
            showPlaceholder();
            // Focus search input when opening
            setTimeout(() => {
                if (couponSearchInput) {
                    couponSearchInput.focus();
                }
            }, 100);
        }

        function closeCouponDropdown() {
            couponSelectDropdown.classList.add('hidden');
            couponDropdownArrow.classList.remove('rotated');
            // Clear search when closing
            if (couponSearchInput) {
                couponSearchInput.value = '';
            }
            showPlaceholder();
        }

        function showPlaceholder() {
            couponsListContainer.innerHTML = `
                <div class="p-4 text-center text-gray-500 text-sm">
                    برای جستجو کد تخفیف، حداقل 3 کاراکتر وارد کنید
                </div>
            `;
        }

        function showMinLengthMessage() {
            couponsListContainer.innerHTML = `
                <div class="p-4 text-center text-gray-500 text-sm">
                    حداقل 3 کاراکتر برای جستجو وارد کنید
                </div>
            `;
        }

        function searchCoupons(searchTerm) {
            // Show loading
            couponsListContainer.innerHTML = `
                <div class="p-4 text-center text-gray-500 text-sm">
                    در حال جستجو...
                </div>
            `;

            // Simulate AJAX call to search coupons
            // In real implementation, you would make an AJAX call here
            // For now, we'll use the PHP data that's already available
            const allCoupons = <?php echo json_encode($wc_coupons); ?>;

            const filteredCoupons = allCoupons.filter(coupon =>
                coupon.post_title.toLowerCase().includes(searchTerm.toLowerCase())
            );

            renderSearchResults(filteredCoupons, searchTerm);
        }

        function renderSearchResults(coupons, searchTerm) {
            if (coupons.length === 0) {
                couponsListContainer.innerHTML = `
                    <div class="p-4 text-center text-gray-500 text-sm">
                        هیچ کد تخفیفی با عبارت "${searchTerm}" یافت نشد
                    </div>
                `;
                return;
            }

            couponsListContainer.innerHTML = '';
            coupons.forEach(coupon => {
                const isSelected = selectedCoupons.has(coupon.post_title);
                const option = document.createElement('label');
                option.className = 'coupon-option flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                option.setAttribute('data-coupon-name', coupon.post_title);

                option.innerHTML = `
                    <input type="checkbox" class="coupon-checkbox mr-3 text-blue-600 focus:ring-blue-500" 
                           value="${coupon.post_title}" 
                           data-coupon-name="${coupon.post_title}"
                           ${isSelected ? 'checked' : ''}>
                    <span class="coupon-name text-sm text-gray-700">${coupon.post_title}</span>
                `;

                couponsListContainer.appendChild(option);
            });
        }

        function updateSelectedCoupons() {
            const selectedArray = Array.from(selectedCoupons);

            // Update display text
            if (selectedArray.length === 0) {
                selectedCouponsText.textContent = 'انتخاب کد تخفیف...';
                selectedCouponsText.classList.add('placeholder');
                dropdownSelectedCoupons.classList.add('hidden');
            } else if (selectedArray.length === 1) {
                selectedCouponsText.textContent = selectedArray[0];
                selectedCouponsText.classList.remove('placeholder');
                dropdownSelectedCoupons.classList.remove('hidden');
            } else {
                selectedCouponsText.textContent = `${selectedArray.length} کد تخفیف انتخاب شده`;
                selectedCouponsText.classList.remove('placeholder');
                dropdownSelectedCoupons.classList.remove('hidden');
            }

            // Update hidden input with selected coupons
            selectedCouponsData.value = selectedArray.join(',');

            // Update selected coupons display (only inside dropdown)
            updateDropdownSelectedCoupons(selectedArray);

            // Update clear filters button
            updateClearFiltersButton();
        }

        function updateDropdownSelectedCoupons(selectedArray) {
            dropdownSelectedCouponsList.innerHTML = '';
            selectedArray.forEach(couponName => {
                const option = document.createElement('label');
                option.className = 'selected-coupon-option flex items-center p-2 hover:bg-blue-100 cursor-pointer border-b border-blue-200 last:border-b-0';
                option.setAttribute('data-coupon-name', couponName);

                option.innerHTML = `
                    <input type="checkbox" class="selected-coupon-checkbox mr-3 text-blue-600 focus:ring-blue-500" 
                           value="${couponName}" 
                           data-coupon-name="${couponName}"
                           checked>
                    <span class="selected-coupon-name text-sm text-gray-700">${couponName}</span>
                `;

                dropdownSelectedCouponsList.appendChild(option);
            });
        }

        // Date picker functions using calendar module
        function openDatePicker() {
            calendar.openCalendarModal();
        }

        // Initialize calendar module
        const calendar = new PersianCalendar({
            onDateRangeSelected: function(dateRange) {
                applyCustomDateRange(dateRange);
            },
            onDateRangeCleared: function() {
                clearDateRange();
            }
        });

        function setDateRange(startDate, endDate) {
            selectedDateRange = {
                start: startDate,
                end: endDate
            };
            const displayText = `${startDate} - ${endDate}`;

            dateRangeText.textContent = displayText;
            dateRangeText.classList.remove('placeholder');
            clearDateBtn.classList.remove('hidden');
            dateRangeData.value = displayText;

            updateClearFiltersButton();
        }

        // Apply custom date range
        function applyCustomDateRange(dateRange) {
            if (dateRange && dateRange.startDate && dateRange.endDate) {
                const persianMonths = [
                    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
                ];

                const startStr = `${dateRange.startDate.day} ${persianMonths[dateRange.startDate.month - 1]} ${dateRange.startDate.year}`;
                const endStr = `${dateRange.endDate.day} ${persianMonths[dateRange.endDate.month - 1]} ${dateRange.endDate.year}`;

                // Set selectedDateRange
                selectedDateRange = {
                    start: startStr,
                    end: endStr,
                    startDate: dateRange.startDate,
                    endDate: dateRange.endDate
                };

                dateRangeText.textContent = `${startStr} - ${endStr}`;
                dateRangeText.classList.remove('placeholder');
                clearDateBtn.classList.remove('hidden');
                dateRangeData.value = `${startStr} - ${endStr}`;

                updateClearFiltersButton();
            }
        }


        function updateDateRangeDisplay() {
            if (selectedDateRange !== null) {
                // Date range is selected, show it
                const displayText = `${selectedDateRange.start} - ${selectedDateRange.end}`;
                dateRangeText.textContent = displayText;
                dateRangeText.classList.remove('placeholder');
                clearDateBtn.classList.remove('hidden');
                dateRangeData.value = displayText;
            } else {
                // No date range selected, show placeholder
                dateRangeText.textContent = 'انتخاب بازه زمانی';
                dateRangeText.classList.add('placeholder');
                clearDateBtn.classList.add('hidden');
                dateRangeData.value = '';
            }
        }

        function clearDateRange() {
            selectedDateRange = null;
            updateDateRangeDisplay();
            updateClearFiltersButton();
        }

        function updateClearFiltersButton() {
            const clearFiltersBtn = document.getElementById('clear-filters');
            let hasFilters = false;
            const activeFormFilters = [];

            // Check individual form fields by ID
            const formFields = [{
                    element: userIdInput,
                    name: 'user_id'
                },
                {
                    element: regionInput,
                    name: 'region'
                },
                {
                    element: orderStatusInput,
                    name: 'order_status'
                },
                {
                    element: gameTypeInput,
                    name: 'game_type'
                },
                {
                    element: sourceInput,
                    name: 'source'
                },
                {
                    element: cityInput,
                    name: 'city'
                }
            ];

            formFields.forEach(field => {
                if (field.element) {
                    const value = field.element.value ? field.element.value.trim() : '';

                    if (value !== '') {
                        hasFilters = true;
                        activeFormFilters.push(`${field.name}: ${value}`);
                    }
                }
            });

            // Check selected games
            if (selectedGames && selectedGames.size > 0) {
                hasFilters = true;
                activeFormFilters.push(`selectedGames: ${Array.from(selectedGames).join(', ')}`);
            }

            // Check selected coupons
            if (selectedCoupons && selectedCoupons.size > 0) {
                hasFilters = true;
                activeFormFilters.push(`selectedCoupons: ${Array.from(selectedCoupons).join(', ')}`);
            }

            // Check date range
            if (selectedDateRange !== null) {
                hasFilters = true;
                activeFormFilters.push(`selectedDateRange: ${JSON.stringify(selectedDateRange)}`);
            }


            if (hasFilters) {
                clearFiltersBtn.disabled = false;
                clearFiltersBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                clearFiltersBtn.disabled = true;
                clearFiltersBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }


        // Report buttons functionality
        const marketingReportBtn = document.getElementById('marketing-report-btn');
        const financialReportBtn = document.getElementById('financial-report-btn');

        // Marketing report modal
        const marketingModal = document.getElementById('marketing-report-modal');
        const closeMarketingModal = document.getElementById('close-marketing-modal');
        const marketingExcelBtn = document.getElementById('marketing-excel');
        const marketingCsvBtn = document.getElementById('marketing-csv');

        // Financial report modal
        const financialModal = document.getElementById('financial-report-modal');
        const closeFinancialModal = document.getElementById('close-financial-modal');
        const financialExcelBtn = document.getElementById('financial-excel');
        const financialCsvBtn = document.getElementById('financial-csv');

        // Open marketing report modal
        if (marketingReportBtn) {
            marketingReportBtn.addEventListener('click', function() {
                marketingModal.classList.remove('hidden');
            });
        }

        // Open financial report modal
        if (financialReportBtn) {
            financialReportBtn.addEventListener('click', function() {
                financialModal.classList.remove('hidden');
            });
        }

        // Close marketing modal
        if (closeMarketingModal) {
            closeMarketingModal.addEventListener('click', function() {
                marketingModal.classList.add('hidden');
            });
        }

        // Close financial modal
        if (closeFinancialModal) {
            closeFinancialModal.addEventListener('click', function() {
                financialModal.classList.add('hidden');
            });
        }

        // Close modals when clicking outside
        if (marketingModal) {
            marketingModal.addEventListener('click', function(e) {
                if (e.target === marketingModal) {
                    marketingModal.classList.add('hidden');
                }
            });
        }

        if (financialModal) {
            financialModal.addEventListener('click', function(e) {
                if (e.target === financialModal) {
                    financialModal.classList.add('hidden');
                }
            });
        }

        // Marketing report export functionality
        if (marketingExcelBtn) {
            marketingExcelBtn.addEventListener('click', function() {
                marketingModal.classList.add('hidden');
                exportData('excel', 'marketing');
            });
        }

        if (marketingCsvBtn) {
            marketingCsvBtn.addEventListener('click', function() {
                marketingModal.classList.add('hidden');
                exportData('csv', 'marketing');
            });
        }

        // Financial report export functionality
        if (financialExcelBtn) {
            financialExcelBtn.addEventListener('click', function() {
                financialModal.classList.add('hidden');
                exportData('excel', 'financial');
            });
        }

        if (financialCsvBtn) {
            financialCsvBtn.addEventListener('click', function() {
                financialModal.classList.add('hidden');
                exportData('csv', 'financial');
            });
        }

        // Sort state management
        let currentSortBy = 'order_created_at';
        let currentSortOrder = 'DESC';

        function sortTable(column) {
            if (currentSortBy === column) {
                // Toggle sort order
                currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                // New column, default to DESC
                currentSortBy = column;
                currentSortOrder = 'DESC';
            }
            loadData(1); // Reset to first page when sorting
        }

        // Load data function
        function loadData(page) {
            const loadingIndicator = document.getElementById('loading-indicator');
            const resultsSection = document.getElementById('results-section');

            // Show loading
            if (loadingIndicator) {
                loadingIndicator.classList.remove('hidden');
            }
            if (resultsSection) {
                resultsSection.style.opacity = '0.5';
            }

            // Prepare form data - only include non-empty values
            const formData = new FormData();

            // Add required fields
            formData.append('action', 'team_ajax_handler');
            formData.append('callback', 'marketing_report_search');
            formData.append('nonce', '<?php echo wp_create_nonce('team-ajax-nonce'); ?>');
            formData.append('page', page);
            formData.append('per_page', 100);

            // Add sort parameters
            formData.append('sort_by', currentSortBy);
            formData.append('sort_order', currentSortOrder);

            // Add form fields only if they have values (exclude page field)
            const form = document.getElementById('marketing-report-form');
            const formElements = form.elements;

            for (let element of formElements) {
                if (element.name && element.name !== 'page' && element.value && element.value.trim() !== '') {
                    let value = element.value;

                    // Handle calendar date range
                    if (element.name === 'date_range') {

                        const dateRange = calendar.getSelectedDateRange();
                        if (dateRange.startDate && dateRange.endDate) {
                            const startDateStr = dateRange.startGregorian.getFullYear() + '-' +
                                String(dateRange.startGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                                String(dateRange.startGregorian.getDate()).padStart(2, '0');
                            const endDateStr = dateRange.endGregorian.getFullYear() + '-' +
                                String(dateRange.endGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                                String(dateRange.endGregorian.getDate()).padStart(2, '0');

                            formData.append('date_range', 'calendar');
                            formData.append('start_date', startDateStr);
                            formData.append('end_date', endDateStr);
                            continue; // Skip adding the original date_range element
                        }
                    }

                    formData.append(element.name, value);
                }
            }

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data.html) {
                        if (resultsSection) {
                            resultsSection.innerHTML = data.data.html;
                        }

                        // Update sort state from server response
                        if (data.data.sort_by) {
                            currentSortBy = data.data.sort_by;
                        }
                        if (data.data.sort_order) {
                            currentSortOrder = data.data.sort_order;
                        }

                        // Update summary statistics
                        if (data.data.summary_stats) {
                            const stats = data.data.summary_stats;

                            // Update total tickets
                            const totalTicketsEl = document.getElementById('total-tickets');
                            if (totalTicketsEl) {
                                totalTicketsEl.textContent = stats.total_tickets;
                            }

                            // Update total coupons
                            const totalCouponsEl = document.getElementById('total-coupons');
                            if (totalCouponsEl) {
                                totalCouponsEl.textContent = stats.total_coupons;
                            }

                            // Update organic referrals
                            const organicEl = document.getElementById('total-organic-referrals');
                            if (organicEl) {
                                organicEl.textContent = stats.organic_referrals;
                            }

                            // Update direct referrals
                            const directEl = document.getElementById('total-direct-referrals');
                            if (directEl) {
                                directEl.textContent = stats.direct_referrals;
                            }

                            // Update google referrals
                            const googleEl = document.getElementById('total-google-referrals');
                            if (googleEl) {
                                googleEl.textContent = stats.google_ads_referrals;
                            }
                        }

                        // Check if filters are applied after AJAX
                        checkFiltersAfterAjax();
                    } else {
                        if (resultsSection) {
                            resultsSection.innerHTML = '<div class="text-center py-12 text-red-600">خطا در بارگذاری داده‌ها. لطفاً دوباره تلاش کنید.</div>';
                        }
                    }
                })
                .catch(error => {
                    if (resultsSection) {
                        resultsSection.innerHTML = '<div class="text-center py-12 text-red-600">خطا در بارگذاری داده‌ها. لطفاً دوباره تلاش کنید.</div>';
                    }
                })
                .finally(() => {
                    // Hide loading
                    if (loadingIndicator) {
                        loadingIndicator.classList.add('hidden');
                    }
                    if (resultsSection) {
                        resultsSection.style.opacity = '1';
                    }
                });
        }

        // Check filters after AJAX
        function checkFiltersAfterAjax() {
            const filterInputs = form.querySelectorAll('input:not([name="page"]), select');
            let hasFilters = false;

            filterInputs.forEach(input => {
                if (input.value && input.value.trim() !== '') {
                    hasFilters = true;
                }
            });

            if (hasFilters) {
                showClearFiltersButton();
            } else {
                hideClearFiltersButton();
            }
        }

        // Display data function
        function displayData(data) {
            const infoBar = document.getElementById('info-bar');
            const dataTable = document.getElementById('data-table');
            const pagination = document.getElementById('pagination');
            const emptyState = document.getElementById('empty-state');
            const tableBody = document.getElementById('table-body');

            if (data.marketing_data && data.marketing_data.length > 0) {
                // Show info bar
                if (infoBar) {
                    document.getElementById('info-text').textContent =
                        `نمایش ${data.marketing_data.length.toLocaleString()} رکورد از ${data.total_count.toLocaleString()} رکورد کل (صفحه ${data.page} از ${data.total_pages})`;
                    document.getElementById('last-update').textContent = new Date().toLocaleString('fa-IR');
                    infoBar.style.display = 'block';
                }

                // Show data table
                if (dataTable) {
                    dataTable.style.display = 'block';
                }

                // Populate table
                if (tableBody) {
                    tableBody.innerHTML = data.marketing_data.map((row, index) => `
                    <tr class="${index % 2 == 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${row.game_product_type || '-'}</td>
                        <td class="px-4 py-3 text-sm text-blue-600 border-b border-gray-100">
                            <a href="#" class="hover:text-blue-800 hover:underline">${row.game_name || '-'}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${(row.customer_firstname + ' ' + row.customer_lastname).trim() || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${row.customer_phone || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${row.order_id}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${formatDate(row.order_created_at)}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${(row.order_tickets_quantity || 0).toLocaleString()}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${(row.order_paid || 0).toLocaleString()}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${row.order_refrerr || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${(row.order_net_profit || 0).toLocaleString()}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100">${getSourceName(row.order_refrerr)}</td>
                    </tr>
                `).join('');
                }

                // Show pagination
                if (data.total_pages > 1 && pagination) {
                    pagination.innerHTML = generatePagination(data.page, data.total_pages);
                    pagination.style.display = 'flex';
                } else if (pagination) {
                    pagination.style.display = 'none';
                }

                // Hide empty state
                if (emptyState) {
                    emptyState.style.display = 'none';
                }
            } else {
                // Show empty state
                if (emptyState) {
                    emptyState.style.display = 'block';
                }

                // Hide other elements
                if (infoBar) infoBar.style.display = 'none';
                if (dataTable) dataTable.style.display = 'none';
                if (pagination) pagination.style.display = 'none';
            }
        }

        // Generate pagination HTML
        function generatePagination(currentPage, totalPages) {
            let html = '';

            // Previous button
            if (currentPage > 1) {
                html += `<button onclick="loadData(${currentPage - 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border  rounded-lg hover:bg-gray-50 hover:text-gray-700">قبلی</button>`;
            }

            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            for (let i = startPage; i <= endPage; i++) {
                const isActive = i === currentPage;
                html += `<button onclick="loadData(${i})" class="px-3 py-2 text-sm font-medium ${isActive ? 'text-white bg-orange-500 border-orange-500' : 'text-gray-500 bg-white  hover:bg-gray-50 hover:text-gray-700'} border rounded-lg">${i}</button>`;
            }

            // Next button
            if (currentPage < totalPages) {
                html += `<button onclick="loadData(${currentPage + 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border  rounded-lg hover:bg-gray-50 hover:text-gray-700">بعدی</button>`;
            }

            return html;
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const persianYear = date.getFullYear() + 621;
            return `${persianYear}.${String(date.getMonth() + 1).padStart(2, '0')}.${String(date.getDate()).padStart(2, '0')}`;
        }

        // Get source name using centralized mapping
        function getSourceName(source) {
            // This should match the PHP mapping function
            const sourceMap = {
                'google': 'سئو',
                'bing.com': 'سئو',
                'search.yahoo.com': 'سئو',
                'duckduckgo.com': 'سئو',
                'gerdoo.me': 'سئو',
                'presearch.com': 'سئو',
                'r.search.yahoo.com': 'سئو',
                'petalsearch.com': 'سئو',
                'zarebin.ir': 'سئو',
                'search.brave.com': 'سئو',
                'search.pawxy.com': 'سئو',
                'web.splus.ir': 'سئو',
                'search.app': 'سئو',
                'com.google.android.googlequicksearchbox': 'سئو',
                '(direct)': 'دایرکت',
                'escapezoom.co': 'ADs',
                'ir.medu.shad': 'درگاه بانک',
                'bpm.shaparak.ir': 'درگاه بانک',
                'sep.shaparak.ir': 'درگاه بانک',
                'sepehr.shaparak.ir': 'درگاه بانک',
                'asan.shaparak.ir': 'درگاه بانک',
                'ikc.shaparak.ir': 'درگاه بانک',
                'instagram.com': 'سوشال',
                'l.instagram.com': 'سوشال',
                'org.telegram.messenger': 'سوشال',
                'shaadbin.ir': 'سوشال',
                'ir.eitaa.messenger': 'سوشال',
                'org.telegram.plus': 'سوشال',
                'org.telegram.messenger.web': 'سوشال',
                'ir.ilmili.telegraph': 'سوشال',
                'the.best.gram': 'سوشال',
                'org.telegram.messenges': 'سوشال',
                'com.xplus.messenger': 'سوشال',
                'com.rahamessenger.pro': 'سوشال',
                'instagram': 'سوشال',
                'com.ita.plus.tel': 'سوشال',
                'insta': 'سوشال',
                'com.skygram.bestt': 'سوشال',
                'web.telegram.org': 'سوشال'
            };
            return sourceMap[source] || source || 'مستقیم';
        }

        // Show error
        function showError(message) {
            const emptyState = document.getElementById('empty-state');
            if (emptyState) {
                emptyState.innerHTML = `
                <div class="text-red-500 space-y-2">
                    <p class="text-lg font-semibold">خطا</p>
                    <p>${message}</p>
                </div>
            `;
                emptyState.style.display = 'block';
            }
        }

        // Export data function
        function exportData(exportType, reportType = 'marketing') {
            const formData = new FormData();

            // Add required fields
            formData.append('action', 'team_ajax_handler');
            formData.append('callback', reportType === 'financial' ? 'financial_report_export' : 'marketing_report_export');
            formData.append('nonce', '<?php echo wp_create_nonce('team-ajax-nonce'); ?>');
            formData.append('export_type', exportType);
            formData.append('report_type', reportType);

            // Add specific filter fields manually
            const selectedGamesData = document.getElementById('selected-games-data');
            const selectedCouponsData = document.getElementById('selected-coupons-data');
            const dateRangeData = document.getElementById('date-range-data');
            const userIdInput = document.getElementById('user-id');
            const regionInput = document.getElementById('region');
            const orderStatusInput = document.getElementById('order-status');
            const gameTypeInput = document.getElementById('game-type');
            const sourceInput = document.getElementById('source');
            const cityInput = document.getElementById('city');

            // Add game names if selected
            if (selectedGamesData && selectedGamesData.value && selectedGamesData.value.trim() !== '') {
                formData.append('game_name', selectedGamesData.value);
            }

            // Add discount codes if selected
            if (selectedCouponsData && selectedCouponsData.value && selectedCouponsData.value.trim() !== '') {
                formData.append('discount_code', selectedCouponsData.value);
            }

            // Add date range if selected
            if (dateRangeData && dateRangeData.value && dateRangeData.value.trim() !== '') {
                const dateRange = calendar.getSelectedDateRange();
                if (dateRange && dateRange.startDate && dateRange.endDate) {
                    const startDateStr = dateRange.startGregorian.getFullYear() + '-' +
                        String(dateRange.startGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                        String(dateRange.startGregorian.getDate()).padStart(2, '0');
                    const endDateStr = dateRange.endGregorian.getFullYear() + '-' +
                        String(dateRange.endGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                        String(dateRange.endGregorian.getDate()).padStart(2, '0');

                    formData.append('date_range', 'calendar');
                    formData.append('start_date', startDateStr);
                    formData.append('end_date', endDateStr);
                }
            }

            // Add other form fields
            if (userIdInput && userIdInput.value && userIdInput.value.trim() !== '') {
                formData.append('user_id', userIdInput.value);
            }
            if (regionInput && regionInput.value && regionInput.value.trim() !== '') {
                formData.append('region', regionInput.value);
            }
            if (orderStatusInput && orderStatusInput.value && orderStatusInput.value.trim() !== '') {
                formData.append('order_status', orderStatusInput.value);
            }
            if (gameTypeInput && gameTypeInput.value && gameTypeInput.value.trim() !== '') {
                formData.append('game_type', gameTypeInput.value);
            }
            if (sourceInput && sourceInput.value && sourceInput.value.trim() !== '') {
                formData.append('source', sourceInput.value);
            }
            if (cityInput && cityInput.value && cityInput.value.trim() !== '') {
                formData.append('city', cityInput.value);
            }

            // Create a temporary form for download
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = '<?php echo admin_url('admin-ajax.php'); ?>';
            tempForm.style.display = 'none';

            // Debug: Log all form data being sent
            console.log('=== Export Form Data ===');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            console.log('========================');

            // Add all form data as hidden inputs
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                tempForm.appendChild(input);
            }

            document.body.appendChild(tempForm);
            tempForm.submit();
            document.body.removeChild(tempForm);
        }

        // Change page function for pagination
        function changePage(page) {
            // Show loading indicator
            const loadingIndicator = document.getElementById('loading-indicator');
            const resultsSection = document.getElementById('results-section');

            if (loadingIndicator) {
                loadingIndicator.classList.remove('hidden');
            }
            if (resultsSection) {
                resultsSection.style.opacity = '0.5';
            }

            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.history.pushState({}, '', url);

            // Update the hidden page input in the form
            const form = document.getElementById('marketing-report-form');
            const pageInput = form.querySelector('input[name="page"]');
            if (pageInput) {
                pageInput.value = page;
            }

            // Load data for new page via AJAX - only include non-empty values
            const formData = new FormData();

            // Add required fields
            formData.append('action', 'team_ajax_handler');
            formData.append('callback', 'marketing_report_search');
            formData.append('nonce', '<?php echo wp_create_nonce('team-ajax-nonce'); ?>');
            formData.append('page', page);
            formData.append('per_page', 100);

            // Add form fields only if they have values (exclude page field)
            const formElements = form.elements;

            for (let element of formElements) {
                if (element.name && element.name !== 'page' && element.value && element.value.trim() !== '') {
                    // Handle calendar date range
                    if (element.name === 'date_range') {
                        const dateRange = calendar.getSelectedDateRange();
                        if (dateRange.startDate && dateRange.endDate) {
                            const startDateStr = dateRange.startGregorian.getFullYear() + '-' +
                                String(dateRange.startGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                                String(dateRange.startGregorian.getDate()).padStart(2, '0');
                            const endDateStr = dateRange.endGregorian.getFullYear() + '-' +
                                String(dateRange.endGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                                String(dateRange.endGregorian.getDate()).padStart(2, '0');

                            formData.append('date_range', 'calendar');
                            formData.append('start_date', startDateStr);
                            formData.append('end_date', endDateStr);
                            continue; // Skip adding the original date_range element
                        }
                    }

                    formData.append(element.name, element.value);
                }
            }

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.html) {
                        if (resultsSection) {
                            resultsSection.innerHTML = data.data.html;
                        }

                        // Update summary statistics
                        if (data.data.summary_stats) {
                            const stats = data.data.summary_stats;

                            // Update total tickets
                            const totalTicketsEl = document.getElementById('total-tickets');
                            if (totalTicketsEl) {
                                totalTicketsEl.textContent = stats.total_tickets;
                            }

                            // Update total coupons
                            const totalCouponsEl = document.getElementById('total-coupons');
                            if (totalCouponsEl) {
                                totalCouponsEl.textContent = stats.total_coupons;
                            }

                            // Update organic referrals
                            const organicEl = document.getElementById('total-organic-referrals');
                            if (organicEl) {
                                organicEl.textContent = stats.organic_referrals;
                            }

                            // Update direct referrals
                            const directEl = document.getElementById('total-direct-referrals');
                            if (directEl) {
                                directEl.textContent = stats.direct_referrals;
                            }

                            // Update google referrals
                            const googleEl = document.getElementById('total-google-referrals');
                            if (googleEl) {
                                googleEl.textContent = stats.google_ads_referrals;
                            }
                        }
                    } else {
                        if (resultsSection) {
                            resultsSection.innerHTML = '<div class="text-center py-12 text-red-600">خطا در بارگذاری داده‌ها. لطفاً دوباره تلاش کنید.</div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    if (resultsSection) {
                        resultsSection.innerHTML = '<div class="text-center py-12 text-red-600">خطا در بارگذاری داده‌ها. لطفاً دوباره تلاش کنید.</div>';
                    }
                })
                .finally(() => {
                    // Hide loading indicator
                    if (loadingIndicator) {
                        loadingIndicator.classList.add('hidden');
                    }
                    if (resultsSection) {
                        resultsSection.style.opacity = '1';
                    }
                });
        }
        // Clear filters
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {

                // Reset individual form fields by ID
                const formFields = [{
                        element: userIdInput,
                        name: 'user_id'
                    },
                    {
                        element: regionInput,
                        name: 'region'
                    },
                    {
                        element: orderStatusInput,
                        name: 'order_status'
                    },
                    {
                        element: gameTypeInput,
                        name: 'game_type'
                    },
                    {
                        element: sourceInput,
                        name: 'source'
                    },
                    {
                        element: cityInput,
                        name: 'city'
                    }
                ];

                formFields.forEach(field => {
                    if (field.element) {
                        if (field.element.type === 'checkbox') {
                            field.element.checked = false;
                        } else {
                            field.element.value = '';
                        }
                    }
                });

                // Clear game selections
                clearSelectedGames();

                // Clear coupon selections
                selectedCoupons.clear();
                updateSelectedCoupons();

                // Clear date range
                selectedDateRange = null;
                updateDateRangeDisplay();

                // Reset page to 1
                const pageInput = form.querySelector('input[name="page"]');
                if (pageInput) {
                    pageInput.value = 1;
                }

                // Reset summary statistics to default values
                resetSummaryStatistics();

                // Clear results section
                const resultsSection = document.getElementById('results-section');
                if (resultsSection) {
                    resultsSection.innerHTML = '<div class="text-center py-12 text-gray-500">برای مشاهده نتایج، فیلترهای مورد نظر را انتخاب کرده و روی دکمه "نمایش" کلیک کنید.</div>';
                }

                // Hide clear filters button
                hideClearFiltersButton();

            });
        }

        // Coupon Modal Functionality
        const createCouponBtn = document.getElementById('create-coupon-btn');
        const couponModal = document.getElementById('coupon-modal');
        const closeCouponModal = document.getElementById('close-coupon-modal');
        const cancelCoupon = document.getElementById('cancel-coupon');
        const couponForm = document.getElementById('coupon-form');
        const createCouponSubmit = document.getElementById('create-coupon-submit');
        const generateCouponCodeBtn = document.getElementById('generate-coupon-code');

        // Open modal
        if (createCouponBtn) {
            createCouponBtn.addEventListener('click', function() {
                couponModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Initialize Persian datepickers
                setTimeout(() => {
                    initializePersianDatepickers();
                }, 100);
            });
        }

        // Close modal functions
        function closeModal() {
            couponModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            couponForm.reset();
        }

        if (closeCouponModal) {
            closeCouponModal.addEventListener('click', closeModal);
        }

        if (cancelCoupon) {
            cancelCoupon.addEventListener('click', closeModal);
        }

        // Close modal when clicking outside
        if (couponModal) {
            couponModal.addEventListener('click', function(e) {
                if (e.target === couponModal) {
                    closeModal();
                }
            });
        }

        // Generate coupon code automatically
        if (generateCouponCodeBtn) {
            generateCouponCodeBtn.addEventListener('click', function() {
                const couponCodeInput = document.getElementById('coupon-code');
                const timestamp = Date.now().toString().slice(-6);
                const randomStr = Math.random().toString(36).substring(2, 6).toUpperCase();
                const generatedCode = 'eszm-gameoff-' + randomStr + timestamp;
                couponCodeInput.value = generatedCode;
            });
        }

        // Initialize Persian datepickers
        function initializePersianDatepickers() {
            const dateInputs = document.querySelectorAll('.persian-datepicker');

            dateInputs.forEach(input => {
                // Simple Persian datepicker implementation
                input.addEventListener('click', function() {
                    showPersianDatePicker(this);
                });
            });
        }

        // Simple Persian datepicker function
        function showPersianDatePicker(input) {
            // Create a simple date picker modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-6 w-80">
                    <h3 class="text-lg font-semibold mb-4">انتخاب تاریخ شمسی</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">سال</label>
                            <input type="number" id="persian-year" class="w-full px-3 py-2 border rounded-md" value="1404" min="1404" max="1405">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">ماه</label>
                            <select id="persian-month" class="w-full px-3 py-2 border rounded-md">
                                <option value="1">فروردین</option>
                                <option value="2">اردیبهشت</option>
                                <option value="3">خرداد</option>
                                <option value="4">تیر</option>
                                <option value="5">مرداد</option>
                                <option value="6">شهریور</option>
                                <option value="7">مهر</option>
                                <option value="8">آبان</option>
                                <option value="9">آذر</option>
                                <option value="10">دی</option>
                                <option value="11">بهمن</option>
                                <option value="12">اسفند</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">روز</label>
                            <input type="number" id="persian-day" class="w-full px-3 py-2 border rounded-md" value="1" min="1" max="31">
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button id="persian-date-ok" class="flex-1 bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700">تایید</button>
                        <button id="persian-date-cancel" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">انصراف</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Handle OK button
            modal.querySelector('#persian-date-ok').addEventListener('click', function() {
                const year = modal.querySelector('#persian-year').value;
                const month = modal.querySelector('#persian-month').value;
                const day = modal.querySelector('#persian-day').value;

                const persianDate = `${year}/${month.padStart(2, '0')}/${day.padStart(2, '0')}`;
                input.value = persianDate;

                document.body.removeChild(modal);
            });

            // Handle Cancel button
            modal.querySelector('#persian-date-cancel').addEventListener('click', function() {
                document.body.removeChild(modal);
            });

            // Close on background click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            });
        }

        // Form submission
        if (couponForm) {
            couponForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = createCouponSubmit;
                const originalText = submitBtn.textContent;

                // Get form values for validation
                const couponCode = document.getElementById('coupon-code').value.trim();
                const discountType = document.getElementById('discount-type').value;
                const couponAmount = parseFloat(document.getElementById('coupon-amount').value);

                // Validate required fields
                if (!couponCode) {
                    alert('کد تخفیف الزامی است!');
                    document.getElementById('coupon-code').focus();
                    return;
                }

                if (!couponAmount || couponAmount <= 0) {
                    alert('مقدار تخفیف الزامی است و باید بیشتر از صفر باشد!');
                    document.getElementById('coupon-amount').focus();
                    return;
                }

                // Validate percentage discount
                if (discountType === 'percent' && couponAmount > 100) {
                    alert('تخفیف درصدی نمی‌تواند بیشتر از 100 درصد باشد!');
                    document.getElementById('coupon-amount').focus();
                    return;
                }

                // Disable button and show loading
                submitBtn.disabled = true;
                submitBtn.textContent = 'در حال ایجاد...';

                // Prepare form data
                const formData = new FormData(couponForm);
                formData.append('action', 'team_ajax_handler');
                formData.append('callback', 'create_woocommerce_coupon');
                formData.append('nonce', '<?php echo wp_create_nonce('team-ajax-nonce'); ?>');

                // Convert Persian dates to Gregorian before sending
                const convertPersianToGregorian = (persianDate) => {
                    if (!persianDate) return '';

                    const parts = persianDate.split('/');
                    if (parts.length !== 3) return '';

                    const j_y = parseInt(parts[0]);
                    const j_m = parseInt(parts[1]);
                    const j_d = parseInt(parts[2]);

                    // Persian to Gregorian conversion
                    let jy = j_y;
                    let jm = j_m;
                    let jd = j_d;

                    jy += 1595;
                    let days = -355668 + (365 * jy) + Math.floor(jy / 33) * 8 + Math.floor(((jy % 33) + 3) / 4) + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
                    let g_y = 400 * Math.floor(days / 146097);
                    days %= 146097;
                    if (days > 36524) {
                        g_y += 100 * Math.floor(--days / 36524);
                        days %= 36524;
                        if (days >= 365) days++;
                    }
                    g_y += 4 * Math.floor(days / 1461);
                    days %= 1461;
                    if (days > 365) {
                        g_y += Math.floor((days - 1) / 365);
                        days = (days - 1) % 365;
                    }
                    let g_d = days + 1;
                    let sal_a = [0, 31, ((g_y % 4 === 0 && g_y % 100 !== 0) || (g_y % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                    let g_m = 0;
                    for (; g_m < 13 && g_d > sal_a[g_m]; g_m++) g_d -= sal_a[g_m];

                    const year = g_y.toString().padStart(4, '0');
                    const month = g_m.toString().padStart(2, '0');
                    const day = g_d.toString().padStart(2, '0');

                    return `${year}-${month}-${day}`;
                };

                // Convert dates if they exist
                const createdDate = document.getElementById('created-date').value;
                const expiryDate = document.getElementById('expiry-date').value;

                if (createdDate) {
                    const convertedCreated = convertPersianToGregorian(createdDate);
                    if (convertedCreated) {
                        formData.delete('created_date');
                        formData.append('created_date', convertedCreated + ' 00:00:00');
                    }
                }

                if (expiryDate) {
                    const convertedExpiry = convertPersianToGregorian(expiryDate);
                    if (convertedExpiry) {
                        formData.delete('expiry_date');
                        formData.append('expiry_date', convertedExpiry + ' 23:59:59');
                    }
                }
                // Send AJAX request
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Show success toast
                            Swal.fire({
                                icon: 'success',
                                title: 'موفق!',
                                text: data.data.message || 'کد تخفیف با موفقیت ایجاد شد',
                                timer: 3000,
                                showConfirmButton: false
                            });
                            closeModal();
                        } else {
                            // Show error toast
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا!',
                                text: data.data || 'خطای نامشخص',
                                timer: 5000
                            });
                        }
                    })
                    .catch(error => {
                        // Show error toast
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا در ارتباط!',
                            text: error.message,
                            timer: 5000
                        });
                    })
                    .finally(() => {
                        // Re-enable button
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
            });
        }

        // Make functions global
        window.loadData = loadData;
        window.changePage = changePage;
        window.sortTable = sortTable;
    });
</script>