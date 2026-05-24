<?php

/**
 * User Cancellation Requests Get Callback
 * 
 * Returns cancellation requests for the current user's owned games
 * Only shows requests related to games owned by the current user
 */

global $wpdb;
$medoo = medoo();

// Function to get user level display with colors (delegates to theme helper for «مجموعه دار» + points levels).
function get_user_level_display( $user_id ) {
    return function_exists( 'ez_user_level_badge_html' ) ? ez_user_level_badge_html( (int) $user_id ) : '';
}

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Check if user has permission
if (!current_user_can('administrator') && !has_role('compiler')) {
    wp_die('شما دسترسی لازم برای مشاهده این صفحه را ندارید.');
}

$status = sanitize_text_field($_POST['status']) ?: 'all';
$page_num = max(1, intval(sanitize_text_field($_POST['page'] ?? 1)));

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

// Build conditions for cancellation requests - show pending and expired requests
$conditions = [
    "product_id" => $owned_products,
    "ORDER" => ["created_at" => "DESC"],
    "LIMIT" => [$offset, $requests_per_page]
];

// Add status filter
if ($status === 'urgent') {
    // Urgent requests: less than 3 hours to game time and still pending
    $conditions["sans_time[<]"] = $now + (3 * 3600);
    $conditions["status"] = 'pending';
} elseif ($status === 'expired') {
    $conditions["status"] = 'expired';
} elseif ($status === 'all') {
    // Show both pending and expired requests
    $conditions["OR"] = [
        "status" => ['pending', 'expired']
    ];
} else {
    $conditions["status"] = $status;
}

$requests = $medoo->select("cancellation_requests", "*", $conditions);

// Count total for pagination - count pending and expired requests
$count_conditions = ["product_id" => $owned_products];
if ($status === 'urgent') {
    $count_conditions["sans_time[<]"] = $now + (3 * 3600);
    $count_conditions["status"] = 'pending';
} elseif ($status === 'expired') {
    $count_conditions["status"] = 'expired';
} elseif ($status === 'all') {
    // Count both pending and expired requests
    $count_conditions["OR"] = [
        "status" => ['pending', 'expired']
    ];
} else {
    $count_conditions["status"] = $status;
}

$total_requests = $medoo->count("cancellation_requests", $count_conditions);
$total_pages = ceil($total_requests / $requests_per_page);

foreach ($requests as $request) :
    $session_ts    = function_exists('ez_cancellation_session_ts') ? ez_cancellation_session_ts($request['sans_time']) : (int) strtotime($request['sans_time']);
    $hours_to_sans = ($session_ts - $now) / 3600;

    // Convert expired requests (فقط درخواست پلیر؛ درخواست مجموعه در این صفحه به‌خاطر رسیدگی ادمین منقضی نمی‌شود)
    if ($request['requester_type'] === 'customer' && $hours_to_sans <= 2 && $request['status'] === 'pending') {
        $medoo->update('cancellation_requests', [
            'status' => 'expired',
            'updated_at' => $now,
        ], ['ID' => $request['ID']]);

        $medoo->insert('cancellation_log', [
            'request_id' => $request['ID'],
            'product_id' => $request['product_id'],
            'user_id' => 0,
            'user_role' => 'system',
            'action' => 'expire',
            'action_time' => $now
        ]);

        continue; // Skip this request as it's now expired
    }

    // Check if urgent (less than 3 hours to game time AND status is pending)
    $is_urgent = $hours_to_sans < 3 && $request['status'] === 'pending';

    if (($request['sans_time'] - $request['created_at']) / 3600 > 24) {
        $request_info1_text     = 'لغوبالای 24 ساعت';
        $request_info1_class    = 'text-orangee';
        $under_24               = false;
    } else {
        $request_info1_text     = 'لغوزیر 24 ساعت';
        $request_info1_class    = 'text-pinkk';
        $under_24               = true;
    }
    
    if ($request['requester_type'] === 'customer') {
        $request_info2_text     = 'پلیر';
        $request_info2_class    = 'text-blueEscape';
        $request_cart_color     = 'bg-blue/10';
        $request_type           = 'customer';
    } else {
        $request_info2_text     = 'مجموعه';
        $request_info2_class    = 'text-red-500';
        $request_cart_color     = 'bg-primary-100';
        $request_type           = 'owner';
    }


    $order = wc_get_order($request['order_id']);
    if (!$order) continue;

    foreach ($order->get_items() as $item) {
        $product_id = $item['product_id'];
        $quantity = $item->get_quantity();
    }

    if ($order->get_billing_first_name() || $order->get_billing_last_name()) {
        $buyer = trim(sprintf(_x('%1$s %2$s', 'full name', 'woocommerce'), $order->get_billing_first_name(), $order->get_billing_last_name()));
    }

    $brand_data = get_the_terms($product_id, 'product_brand')[0];
    $cancellation_reasons = cancellation_reasons();
?>

    <section class="request-card flex justify-center items-center mt-5">
        <input type="hidden" name="request_id" value="<?php echo $request['ID']; ?>">
        <div class="rounded-20 overflow-hidden w-full pt-d20 border shadow-rail-lip <?php echo $request_cart_color ?>">
            <div class="flex justify-between px-d20">
                <div class="flex justify-start items-center gap-d5 w-d168">
                    <p class="<?php echo $request_info1_class ?> text-base font-black"><?php echo $request_info1_text ?></p>

                    <?php if ($is_urgent) : ?>
                        <div class="bg-pinkk rounded-md py-d2 px-d6 text-white text-sx font-extrabold">فوری</div>
                    <?php endif; ?>
                </div>
                <div class="flex justify-center items-center gap-d6 bg-white rounded-6 py-1 px-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <rect width="20" height="20" rx="4" fill="white" />
                        <path d="M15 3.875C15 6.0716 13.5833 7.9363 11.6139 8.60743C11.2232 8.74057 10.9258 9.08723 10.9258 9.5C10.9258 9.91277 11.2232 10.2594 11.6139 10.3926C13.5833 11.0637 15 12.9284 15 15.125V16.625C15 17.1773 14.5523 17.625 14 17.625H6C5.44772 17.625 5 17.1773 5 16.625V15.125C5 12.9289 6.41599 11.0644 8.38467 10.3929C8.77562 10.2595 9.07324 9.91257 9.07324 9.4995C9.07324 9.08643 8.77564 8.73951 8.38469 8.60616C6.41606 7.93471 5 6.07103 5 3.875V2.375C5 1.82272 5.44772 1.375 6 1.375H14C14.5523 1.375 15 1.82272 15 2.375V3.875Z" fill="#889BAD" />
                        <path d="M12.1738 5.125C12.3539 5.125 12.5 5.27108 12.5 5.45117C12.5 6.65172 11.5267 7.62495 10.3262 7.625C10.1918 7.625 10.0852 7.73841 10.0936 7.87256L10.4883 14.1874C10.5212 14.7144 10.9583 15.125 11.4863 15.125H12.6631C13.2634 15.125 13.75 15.6116 13.75 16.2119C13.75 16.302 13.677 16.375 13.5869 16.375H6.41309C6.32304 16.375 6.25 16.302 6.25 16.2119C6.25002 15.6116 6.73664 15.125 7.33691 15.125H8.51367C9.04174 15.125 9.47879 14.7144 9.51173 14.1874L9.9064 7.87256C9.91479 7.73841 9.80824 7.625 9.67383 7.625C8.47328 7.62495 7.50005 6.65172 7.5 5.45117C7.5 5.27108 7.64608 5.125 7.82617 5.125H12.1738Z" fill="white" />
                    </svg>
                    <p class="text-text-3 text-base font-extrabold"><?php echo floor(($now - (int)$request['created_at']) / 3600) ?: 'کمتر از 1 ' ?> ساعت پیش</p>
                </div>
            </div>
            <div class="flex justify-between max-lg:flex-col max-lg:gap-2 px-d20 my-d20">
                <p class="text-base font-extrabold max-lg:my-3">
                    درخواست لغو
                    <span class="text-text-3 mx-2">سانس</span>
                    <?php echo parsidate('l', (int) $request['sans_time']); ?>
                    <span class="text-base font-black mx-2 text-orangee"><?php echo parsidate('j', (int) $request['sans_time']); ?></span>
                    <?php echo parsidate('F', (int) $request['sans_time']) . '-' . date('H:i', (int) $request['sans_time']); ?>
                    <span class="text-text-3 mx-2"><?php echo ez_get_product_meta($product_id)->product_type; ?></span>
                    <?php echo get_the_title($product_id); ?>
                </p>

                <?php if ($request['status'] === 'pending') : ?>
                    <div class="max-lg:grid max-lg:grid-cols-3 lg:flex items-center gap-2">
                        <button class="reject-btn text-base font-extrabold text-slate-200 bg-white px-4 py-3 rounded-xl cursor-pointer hover:bg-slate-100" data-request-id="<?php echo $request['ID']; ?>">رد کردن</button>
                        <button class="approve-btn text-base font-extrabold text-white bg-accent-450 px-4 py-3 rounded-xl cursor-pointer hover:bg-accent-700 max-lg:col-span-2" data-request-id="<?php echo $request['ID']; ?>">تایید و لغو سانس</button>
                    </div>
                <?php else : ?>
                    <div class="text-lg font-extrabold text-gray-500">
                        <?php
                        $status_text = match ($request['status']) {
                            'approved' => 'تایید شده',
                            'rejected' => 'رد شده',
                            'cancelled' => 'لغو شده',
                            'expired' => 'موعد گذشت',
                            default => 'نامشخص'
                        };
                        echo $status_text;
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex items-center justify-center p-2 text-sm font-extrabold text-grayy gap-2 bg-gray-20 rounded-b-2.5xl cursor-pointer showDetailsBtn">
                مشاهده جزئیات
                <svg xmlns="http://www.w3.org/2000/svg" class="-mt-1" width="10" height="6" viewBox="0 0 10 6" fill="none">
                    <path d="M9 1L5.70711 4.29289C5.31658 4.68342 4.68342 4.68342 4.29289 4.29289L1 1" stroke="#0F172B" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>
            <div class="flex-col items-center justify-center bg-gray-20 px-d20 pt-2 pb-d20 description hidden">
                <div class="flex items-center justify-center p-d2 text-sm font-extrabold text-grayy gap-d10 text-right cursor-pointer mt-2 hideDetailsBtn">
                    بستن
                    <svg xmlns="http://www.w3.org/2000/svg" class="-mt-1" width="10" height="6" viewBox="0 0 10 6" fill="none">
                        <path d="M1 5L4.29289 1.70711C4.68342 1.31658 5.31658 1.31658 5.70711 1.70711L9 5" stroke="#0F172B" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
                <hr class="h-d3 text-slate-105 my-d14 mx-d20" />

                <div class="flex justify-between items-center mt-d14 mx-d20 max-lg:flex-wrap max-lg:gap-y-4">
                    <div class="flex justify-center gap-2">
                        <p class="text-sm font-extrabold text-grayy">کد رزرو</p>
                        <p class="text-base font-extrabold text-navyBlue"><?php echo $request['order_id'] ?></p>
                    </div>
                    <div class="flex justify-center gap-d13">
                        <p class="text-sm font-extrabold text-grayy">تاریخ رزرو</p>
                        <div class="flex justify-center gap-2">
                            <p class="text-base font-extrabold text-navyBlue"><?php echo parsidate('Y.m.d', $request['created_at'], 'fa') ?></p>
                            <p class="text-base font-extrabold text-navyBlue"><?php echo parsidate('H:i', $request['created_at'], 'fa') ?></p>
                        </div>
                    </div>
                    <div class="flex justify-center gap-2 max-lg:order-1">
                        <p class="text-sm font-extrabold text-grayy">تعداد</p>
                        <p class="text-base font-extrabold text-navyBlue"><?php echo $quantity ?> بلیت</p>
                    </div>
                    <div class="flex">
                        <div class="flex flex-col">
                            <p class="text-lg font-extrabold text-navyBlue text-start"><?php echo $buyer ?></p>
                            <p class="text-base font-extrabold text-navyBlue text-start"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
                        </div>
                        <div><?php echo get_user_level_display($order->get_user_id()) ?></div>
                    </div>
                </div>

                <?php if ($request['reason_id']) : ?>
                    <hr class="h-d3 text-slate-105 my-d14 mx-d20" />
                    <div class="flex items-center gap-2 mx-d20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                            <path d="M10 11.9L12.9 14.8C13.0833 14.9833 13.3167 15.075 13.6 15.075C13.8833 15.075 14.1167 14.9833 14.3 14.8C14.4833 14.6167 14.575 14.3833 14.575 14.1C14.575 13.8167 14.4833 13.5833 14.3 13.4L11.4 10.5L14.3 7.6C14.4833 7.41667 14.575 7.18333 14.575 6.9C14.575 6.61667 14.4833 6.38333 14.3 6.2C14.1167 6.01667 13.8833 5.925 13.6 5.925C13.3167 5.925 13.0833 6.01667 12.9 6.2L10 9.1L7.1 6.2C6.91667 6.01667 6.68333 5.925 6.4 5.925C6.11667 5.925 5.88333 6.01667 5.7 6.2C5.51667 6.38333 5.425 6.61667 5.425 6.9C5.425 7.18333 5.51667 7.41667 5.7 7.6L8.6 10.5L5.7 13.4C5.51667 13.5833 5.425 13.8167 5.425 14.1C5.425 14.3833 5.51667 14.6167 5.7 14.8C5.88333 14.9833 6.11667 15.075 6.4 15.075C6.68333 15.075 6.91667 14.9833 7.1 14.8L10 11.9ZM10 20.5C8.61667 20.5 7.31667 20.2373 6.1 19.712C4.88334 19.1867 3.825 18.4743 2.925 17.575C2.025 16.6757 1.31267 15.6173 0.788001 14.4C0.263335 13.1827 0.000667933 11.8827 1.26582e-06 10.5C-0.000665401 9.11733 0.262001 7.81733 0.788001 6.6C1.314 5.38267 2.02633 4.32433 2.925 3.425C3.82367 2.52567 4.882 1.81333 6.1 1.288C7.318 0.762667 8.618 0.5 10 0.5C11.382 0.5 12.682 0.762667 13.9 1.288C15.118 1.81333 16.1763 2.52567 17.075 3.425C17.9737 4.32433 18.6863 5.38267 19.213 6.6C19.7397 7.81733 20.002 9.11733 20 10.5C19.998 11.8827 19.7353 13.1827 19.212 14.4C18.6887 15.6173 17.9763 16.6757 17.075 17.575C16.1737 18.4743 15.1153 19.187 13.9 19.713C12.6847 20.239 11.3847 20.5013 10 20.5Z" fill="#F21543" />
                        </svg>
                        <div class="flex items-center gap-d2">
                            <p class="text-sm font-extrabold text-grayy">دلیل لغو توسط مجموعه:</p>
                            <p class="text-navyBlue text-base font-extrabold"><?php echo $cancellation_reasons[$request['reason_id']] ?></p>
                        </div>
                    </div>
                <?php endif; ?>
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
                <?php if ($status === 'urgent') : ?>
                    درخواست فوری‌ای برای نمایش وجود ندارد
                <?php elseif ($status === 'expired') : ?>
                    درخواست منقضی‌ای برای نمایش وجود ندارد
                <?php else : ?>
                    درخواست لغوی برای نمایش وجود ندارد
                <?php endif; ?>
            </h3>
            <p class="text-gray-500">هنگامی که درخواست جدیدی دریافت شود، در اینجا نمایش داده خواهد شد.</p>
        </div>
    </div>
<?php endif; ?>

<?php if ($total_pages > 1) : ?>
    <div class="flex justify-center mt-8">
        <div class="pagination flex gap-2">
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <a href="?page=<?php echo $i; ?>" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium <?php echo $i == $page_num ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
<?php endif; ?>