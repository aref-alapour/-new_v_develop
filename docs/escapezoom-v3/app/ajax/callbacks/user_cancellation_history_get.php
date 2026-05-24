<?php

/**
 * User Cancellation History Get Callback
 * 
 * Returns cancellation history for the current user's owned games
 * Only shows history related to games owned by the current user
 */

global $wpdb;
$medoo = medoo();

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Check if user has permission
if (!current_user_can('administrator') && !has_role('compiler')) {
    wp_die('شما دسترسی لازم برای مشاهده این صفحه را ندارید.');
}

$status = sanitize_text_field($_POST['status']) ?: 'all';
$page_num = max(1, intval(sanitize_text_field($_POST['page'] ?? 1)));
$search = sanitize_text_field($_POST['search'] ?? '');

$requests_per_page = 10;
$offset = ($page_num - 1) * $requests_per_page;

$now = time();

// Get user's owned products
$owned_products = [];
if (current_user_can('administrator')) {
    // Administrators can see all products
    $owned_products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
} else {
    // Owners can only see their own products
    $owned_products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'user_ebtal',
                'value' => $user_id,
                'compare' => '='
            ],
            [
                'key' => 'sans_manager',
                'value' => $user_id,
                'compare' => '='
            ]
        ]
    ]);
}

if (empty($owned_products)) {
    echo '<div class="text-center text-gray-500 py-8">هیچ بازی‌ای برای نمایش وجود ندارد.</div>';
    return;
}

// Build conditions for cancellation requests
$conditions = [
    "product_id" => $owned_products,
    "ORDER" => ["created_at" => "DESC"],
    "LIMIT" => [$offset, $requests_per_page]
];

// Add status filter
if ($status !== 'all') {
    $conditions["status"] = $status;
}

// Add search filter
if (!empty($search)) {
    // Search in order ID, product title, or customer name
    $search_conditions = [
        "OR" => [
            "order_id[~]" => $search,
            "product_id[IN]" => get_posts([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                's' => $search
            ])
        ]
    ];
    $conditions = array_merge($conditions, $search_conditions);
}

$requests = $medoo->select("cancellation_requests", "*", $conditions);

// Count total for pagination
$count_conditions = ["product_id" => $owned_products];
if ($status !== 'all') {
    $count_conditions["status"] = $status;
}
if (!empty($search)) {
    $count_conditions = array_merge($count_conditions, $search_conditions);
}

$total_requests = $medoo->count("cancellation_requests", $count_conditions);
$total_pages = ceil($total_requests / $requests_per_page);
?>

<!-- Desktop Table View -->
<section id="ancellTableContainer" class="desktop-table mt-4">
    <div class="w-full py-4 rounded-t-12 bg-slate-105 grid">
        <div class="gap-2 grid grid-cols-[3fr,2fr,4fr,2fr,3fr,3fr,3fr] text-sm font-bold text-grayy text-center">
            <p>نوع درخواست</p>
            <p>تاریخ درخواست</p>
            <p>نام پلیر</p>
            <p>کد رزرو</p>
            <p>نام بازی</p>
            <p>سانس لغو شده</p>
            <p>وضعیت</p>
        </div>
    </div>

    <div id="tableBody" class="w-full h-full">
        <?php foreach ($requests as $index => $request) :
            $effective_status = $request['status'];
            $is_auto = false;

            if ($effective_status === 'expired' && isset($request['auto_status']) && in_array($request['auto_status'], ['approved', 'rejected'])) {
                $effective_status = $request['auto_status'];
                $is_auto = true;
            }

            $status_class = match ($effective_status) {
                'approved'  => $is_auto ? 'text-green-600' : 'text-green-600',
                'rejected'  => $is_auto ? 'text-red-500' : 'text-red-500',
                'pending'   => 'text-orange-500',
                'cancelled' => 'text-gray-500',
                'expired'   => 'text-purple-800',
                default     => '',
            };

            $status_text = match ($effective_status) {
                'approved'  => $is_auto ? 'تایید سیستمی' : 'تایید و لغو سانس',
                'rejected'  => $is_auto ? 'رد سیستمی' : 'رد شد',
                'pending'   => 'در انتظار تایید',
                'cancelled' => 'لغو شد',
                'expired'   => 'موعد بررسی گذشت',
                default     => '',
            };

            if ($request['sans_time'] - $request['created_at'] > 86400) {
                $request_info1_text     = 'بالای 24';
                $request_info1_class    = 'text-orangee';
            } else {
                $request_info1_text     = 'زیر 24';
                $request_info1_class    = 'text-pinkk';
            }

            if ($request['requester_type'] === 'customer') {
                $request_info2_text     = 'پلیر';
                $request_info2_class    = 'text-blueEscape';
            } else {
                $request_info2_text     = 'مجموعه';
                $request_info2_class    = 'text-red-500';
            }

            $order = wc_get_order($request['order_id']);
            if (!$order) continue;

            if ($order->get_billing_first_name() || $order->get_billing_last_name()) {
                $buyer = trim(sprintf(_x('%1$s %2$s', 'full name', 'woocommerce'), $order->get_billing_first_name(), $order->get_billing_last_name()));
            } else {
                $buyer = 'نامشخص';
            }

            $product_title = get_the_title($request['product_id']);
            $brand_data = get_the_terms($request['product_id'], 'product_brand')[0];
            $brand_name = $brand_data ? $brand_data->name : 'نامشخص';

            $sans_time_txt = parsidate('l j F', (int) $request['sans_time']) . ' - ' . date('H:i', (int) $request['sans_time']);
            $created_time_txt = parsidate('Y.m.d H:i', (int) $request['created_at']);

            // Get who processed the request
            $processed_by = 'سیستم';
            if ($request['updated_at'] != $request['created_at']) {
                $log = $medoo->get('cancellation_log', '*', [
                    'request_id' => $request['ID'],
                    'action[!]' => 'create',
                    'ORDER' => ['action_time' => 'DESC']
                ]);
                if ($log && $log['user_id'] > 0) {
                    $processor = get_userdata($log['user_id']);
                    if ($processor) {
                        $processed_by = $processor->display_name;
                    }
                }
            }
        ?>

            <div class="w-full py-4 rounded-2.5xl grid grid-cols-[3fr,2fr,4fr,2fr,3fr,3fr,3fr] items-center text-center <?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                <div class="flex items-center justify-center gap-2">
                    <span class="<?php echo $request_info1_class ?> text-sm font-bold"><?php echo $request_info1_text ?></span>
                    <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                    <span class="<?php echo $request_info2_class ?> text-sm font-bold"><?php echo $request_info2_text ?></span>
                </div>
                <div class="text-sm font-bold text-gray-700">
                    <?php echo $created_time_txt; ?>
                </div>
                <div class="text-sm font-bold text-gray-700">
                    <?php echo $buyer; ?>
                </div>
                <div class="text-sm font-bold text-gray-700">
                    <?php echo $request['order_id']; ?>
                </div>
                <div class="text-sm font-bold text-gray-700">
                    <?php echo $product_title; ?>
                </div>
                <div class="text-sm font-bold text-gray-700">
                    <?php echo $sans_time_txt; ?>
                </div>
                <div class="text-sm font-bold <?php echo $status_class; ?>">
                    <?php echo $status_text; ?>
                </div>
            </div>

        <?php endforeach; ?>

        <?php if (empty($requests)) : ?>
            <div class="w-full text-center text-gray-500 py-8 bg-white rounded-2.5xl">
                <?php if ($status === 'approved') : ?>
                    درخواست تایید شده‌ای برای نمایش وجود ندارد.
                <?php elseif ($status === 'pending') : ?>
                    درخواست در انتظار بررسی‌ای برای نمایش وجود ندارد.
                <?php elseif ($status === 'rejected') : ?>
                    درخواست رد شده‌ای برای نمایش وجود ندارد.
                <?php elseif ($status === 'expired') : ?>
                    درخواست منقضی‌ای برای نمایش وجود ندارد.
                <?php else : ?>
                    درخواست لغوی برای نمایش وجود ندارد.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Mobile Card View -->
<section class="mobile-cards mt-4">
    <?php foreach ($requests as $request) :
        $effective_status = $request['status'];
        $is_auto = false;

        if ($effective_status === 'expired' && isset($request['auto_status']) && in_array($request['auto_status'], ['approved', 'rejected'])) {
            $effective_status = $request['auto_status'];
            $is_auto = true;
        }

        $status_class = match ($effective_status) {
            'approved'  => $is_auto ? 'text-green-600' : 'text-green-600',
            'rejected'  => $is_auto ? 'text-red-500' : 'text-red-500',
            'pending'   => 'text-orange-500',
            'cancelled' => 'text-gray-500',
            'expired'   => 'text-purple-800',
            default     => '',
        };

        $status_text = match ($effective_status) {
            'approved'  => $is_auto ? 'تایید سیستمی' : 'تایید و لغو سانس',
            'rejected'  => $is_auto ? 'رد سیستمی' : 'رد شد',
            'pending'   => 'در انتظار تایید',
            'cancelled' => 'لغو شد',
            'expired'   => 'موعد بررسی گذشت',
            default     => '',
        };

        if ($request['sans_time'] - $request['created_at'] > 86400) {
            $request_info1_text     = 'بالای 24';
            $request_info1_class    = 'text-orangee';
        } else {
            $request_info1_text     = 'زیر 24';
            $request_info1_class    = 'text-pinkk';
        }

        if ($request['requester_type'] === 'customer') {
            $request_info2_text     = 'پلیر';
            $request_info2_class    = 'text-blueEscape';
        } else {
            $request_info2_text     = 'مجموعه';
            $request_info2_class    = 'text-red-500';
        }

        $order = wc_get_order($request['order_id']);
        if (!$order) continue;

        if ($order->get_billing_first_name() || $order->get_billing_last_name()) {
            $buyer = trim(sprintf(_x('%1$s %2$s', 'full name', 'woocommerce'), $order->get_billing_first_name(), $order->get_billing_last_name()));
        } else {
            $buyer = 'نامشخص';
        }

        $product_title = get_the_title($request['product_id']);
        $sans_time_txt = parsidate('l j F', (int) $request['sans_time']) . ' - ' . date('H:i', (int) $request['sans_time']);
        $created_time_txt = parsidate('Y.m.d', (int) $request['created_at']);
        $game_date_txt = parsidate('Y.m.d H:i', (int) $request['sans_time']);
    ?>

        <section class="request-card flex justify-center items-center mt-5">
            <div class="w-full pt-d20 border-t border-slate-105 bg-white">
                <div class="lg:px-d20">
                    <!-- Main info row with flex-wrap -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-4 mb-4">
                        <div class="flex items-center gap-x-2">
                            <span class="text-sm font-bold text-grayy mb-1">نوع درخواست</span>
                            <div class="flex items-center gap-d5">
                                <p class="<?php echo $request_info1_class ?> text-base font-black"><?php echo $request_info1_text ?></p>
                                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                <p class="<?php echo $request_info2_class ?> text-base font-black"><?php echo $request_info2_text ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-x-2">
                            <span class="text-sm font-bold text-grayy mb-1">تاریخ درخواست</span>
                            <span class="text-sm font-bold text-gray-700"><?php echo $created_time_txt; ?></span>
                        </div>
                        <div class="flex items-center gap-x-2">
                            <span class="text-sm font-bold text-grayy mb-1">کد رزرو</span>
                            <span class="text-sm font-bold text-gray-700"><?php echo $request['order_id']; ?></span>
                        </div>
                        <div class="flex items-center gap-x-2">
                            <span class="text-sm font-bold text-grayy mb-1">تاریخ بازی</span>
                            <span class="text-sm font-bold text-gray-700"><?php echo $game_date_txt; ?></span>
                        </div>
                    </div>

                    <!-- Status row -->
                    <?php
                    // Map status class to background color with 20% opacity
                    $bg_class = '';
                    switch ($effective_status) {
                        case 'approved':
                            $bg_class = 'bg-green-100'; // Tailwind green-100 is ~20% opacity
                            break;
                        case 'rejected':
                            $bg_class = 'bg-red-100';
                            break;
                        case 'pending':
                            $bg_class = 'bg-orange-100';
                            break;
                        case 'cancelled':
                            $bg_class = 'bg-gray-100';
                            break;
                        case 'expired':
                            $bg_class = 'bg-purple-100';
                            break;
                        default:
                            $bg_class = 'bg-gray-100';
                    }
                    ?>
                    <div class="w-full text-center py-1 rounded-md text-base font-bold <?php echo $status_class . ' ' . $bg_class; ?>">
                        <?php echo $status_text; ?>
                    </div>
                </div>

                <div class="flex items-center justify-center p-2 text-sm font-extrabold text-grayy gap-2 bg-gray-20 rounded-b-2.5xl cursor-pointer showDetailsBtn">
                    مشاهده جزئیات بیشتر
                    <svg xmlns="http://www.w3.org/2000/svg" class="-mt-1" width="10" height="6" viewBox="0 0 10 6" fill="none">
                        <path d="M9 1L5.70711 4.29289C5.31658 4.68342 4.68342 4.68342 4.29289 4.29289L1 1" stroke="#0F172B" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>

                <div class="flex-col items-center justify-center bg-gray-20 px-d20 pt-2 pb-d20 description hidden">
                    <div class="flex items-center justify-center p-d2 text-sm font-extrabold text-grayy gap-d10 text-right cursor-pointer mt-2 hideDetailsBtn">
                        مشاهده جزئیات کمتر
                        <svg xmlns="http://www.w3.org/2000/svg" class="-mt-1" width="10" height="6" viewBox="0 0 10 6" fill="none">
                            <path d="M1 5L4.29289 1.70711C4.68342 1.31658 5.31658 1.31658 5.70711 1.70711L9 5" stroke="#0F172B" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <hr class="h-d3 text-slate-105 my-d14 mx-d20" />

                    <div class="space-y-4">
                        <div class="flex items-center gap-x-4">
                            <span class="text-sm font-bold text-grayy mb-1">نام پلیر</span>
                            <span class="text-base font-bold text-gray-700"><?php echo $buyer; ?></span>
                        </div>
                        <div class="flex items-center gap-x-4">
                            <span class="text-sm font-bold text-grayy mb-1">نام بازی</span>
                            <span class="text-base font-bold text-gray-700"><?php echo $product_title; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <?php endforeach; ?>

    <?php if (empty($requests)) : ?>
        <div class="text-center py-12">
            <div class="bg-gray-50 rounded-lg p-8">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-bold text-gray-600 mb-2">
                    <?php if ($status === 'approved') : ?>
                        درخواست تایید شده‌ای برای نمایش وجود ندارد.
                    <?php elseif ($status === 'pending') : ?>
                        درخواست در انتظار بررسی‌ای برای نمایش وجود ندارد.
                    <?php elseif ($status === 'rejected') : ?>
                        درخواست رد شده‌ای برای نمایش وجود ندارد.
                    <?php elseif ($status === 'expired') : ?>
                        درخواست منقضی‌ای برای نمایش وجود ندارد.
                    <?php else : ?>
                        درخواست لغوی برای نمایش وجود ندارد.
                    <?php endif; ?>
                </h3>
                <p class="text-gray-500">هنگامی که درخواست جدیدی دریافت شود، در اینجا نمایش داده خواهد شد.</p>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($total_pages > 1) : ?>
    <div class="flex justify-center mt-8">
        <div class="pagination flex gap-2">
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <a href="?page=<?php echo $i; ?>" class="px-3 py-2 border rounded <?php echo $i == $page_num ? 'bg-primaryColor text-white' : 'bg-white text-gray-700'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
<?php endif; ?>