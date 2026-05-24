<?php
global $wpdb;

$user_id = get_current_user_id();

$start      = floor(sanitize_text_field($_POST['start']) / 1000);
$end        = floor(sanitize_text_field($_POST['end']) / 1000);
$date_range = "$end,$start";

$status   = -1;
$page_num = sanitize_text_field($_POST['page']) ?: 1;

$items_per_page = 10;

// تعیین وضعیت‌های سفارش
if ($status == -1) {
    $order_statuses = ['wc-partially-paid', 'wc-walletx', 'wc-completed', 'wc-completed-paid'];
} elseif ($status == 'holding') {
    $order_statuses = ['wc-partially-paid'];
} elseif ($status == 'held') {
    $order_statuses = ['wc-walletx', 'wc-completed', 'wc-completed-paid'];
} else {
    $order_statuses = ['wc-partially-paid', 'wc-walletx', 'wc-completed', 'wc-completed-paid'];
}

// دریافت محصولات کاربر (این بخش تغییر نمی‌کند)
$user_role = get_user_role($user_id);
$products_id = [];
if ($user_role == 'sans_manager') {
    $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
} elseif ($user_role == 'compiler') {
    $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);
}

foreach ($user_products as $user_product) {
    $products_id[] = $user_product['post_id'];
}

if (empty($date_range) || empty($products_id)) {
    wp_send_json_error('تاریخ حتما باید وارد شود.', 400);
}

$date_range = explode(',', $date_range);
$start_date = date('Y-m-d H:i:s', (int)$date_range[0]);
$end_date = date('Y-m-d H:i:s', (int)$date_range[1]);

// استفاده از medoo برای query از wp_markting
$medoo = medoo();
if (!$medoo) {
    wp_send_json_error('خطا در اتصال به دیتابیس');
    return;
}

// ساخت شرط‌های فیلتر
$where_conditions = [
    'game_id' => $products_id,
    'order_status' => $order_statuses,
    'order_created_at[>=]' => $start_date,
    'order_created_at[<=]' => $end_date
];

// محاسبه offset برای pagination
$offset = ($page_num - 1) * $items_per_page;

try {
    // دریافت تعداد کل سفارشات
    $orders_count = $medoo->count('wp_markting', $where_conditions);
    $total_pages = ceil($orders_count / $items_per_page) ?: 1;

    // دریافت سفارشات با pagination
    $orders = $medoo->select('wp_markting', [
        'order_id',
        'game_id',
        'game_name',
        'order_tickets_quantity',
        'order_created_at',
        'order_sans_date',
        'order_sans_time',
        'order_paid',
        'order_finall_price',
        'order_status',
        'order_coupon_used'
    ], array_merge($where_conditions, [
        'ORDER' => ['order_created_at' => 'DESC'],
        'LIMIT' => [$offset, $items_per_page]
    ]));
} catch (Exception $e) {
    error_log('Error in panel_sells_get_tables: ' . $e->getMessage());
    wp_send_json_error('خطا در دریافت فروشات: ' . $e->getMessage());
    return;
}

$items = [];

// اطمینان از اینکه orders یک array است
if (!is_array($orders)) {
    $orders = [];
}

foreach ($orders as $order_data) {
    $order_id = $order_data['order_id'];
    $product_id = $order_data['game_id'];
    $quantity = $order_data['order_tickets_quantity'];
    
    // دریافت pish_pardakht_per_person از محصول
    $pish_per_person = get_post_meta($product_id, 'pish_pardakht_per_person', true);
    $pish_per_person = !empty($pish_per_person) ? $pish_per_person : 1;

    // استفاده از order_paid برای prepaid
    $prepaid = (float)($order_data['order_paid'] ?: 0);
    
    // استفاده از order_finall_price برای total_payment، اگر موجود نبود محاسبه کن
    $item_total = 0;
    if (!empty($order_data['order_finall_price'])) {
        $item_total = (float)$order_data['order_finall_price'];
    } else {
        if ($order_data['order_status'] === 'wc-completed-paid') {
            $item_total = $prepaid;
        } else {
            $item_total = $prepaid / $pish_per_person * $quantity;
        }
    }

    // پرداخت کامل: پیش‌پرداخت و مبلغ کل یکسان، مانده صفر
    if ($order_data['order_status'] === 'wc-completed-paid') {
        $full_amount = $prepaid > 0 ? $prepaid : $item_total;
        $item_total  = $full_amount;
        $prepaid     = $full_amount;
    }

    // تبدیل order_created_at به timestamp برای purchase_time
    $purchase_time = 0;
    if (!empty($order_data['order_created_at'])) {
        $purchase_time = strtotime($order_data['order_created_at']);
    }

    // اگر purchase_time نباشد، skip کن (مشابه کد قبلی که booked_time را چک می‌کرد)
    if (!$purchase_time) {
        continue;
    }

    // تبدیل order_sans_date و order_sans_time به timestamp برای sans_time
    $sans_time = 0;
    if (!empty($order_data['order_sans_date']) && !empty($order_data['order_sans_time'])) {
        $sans_datetime = $order_data['order_sans_date'] . ' ' . $order_data['order_sans_time'];
        $sans_time = strtotime($sans_datetime);
    }

    // تعیین وضعیت و رنگ بر اساس order_status
    $order_status_raw = $order_data['order_status'];
    $order_status = '';
    $color = '#09192D';

    if ($order_status_raw === 'wc-partially-paid' || $order_status_raw === 'wc-completed-paid') {
        if ($sans_time) {
            if (time() - $sans_time > 90 * 60) {
                $order_status = 'برگزار شده';
                $color = '#049654';
            } else {
                $order_status = 'در راه بازی';
                $color = "#FD7013";
            }
        } else {
            $order_status = 'لغو شده';
            $color = '#F21543';
        }
    } elseif (in_array($order_status_raw, ['wc-walletx', 'wc-completed'])) {
        $order_status = 'برگزار شده';
        $color = '#049654';
    } elseif (in_array($order_status_raw, ['wc-admin-cancelled', 'wc-refunded', 'wc-conflict'])) {
        $order_status = 'لغو شده';
        $color = '#F21543';
    } else {
        $order_status = $order_status_raw;
        $color = '#09192D';
    }

    // Check if order has discount coupon and detect campaign title(s)
    $has_discount = !empty($order_data['order_coupon_used']);
    $campaign_title = '';
    
    if ($has_discount && !empty($order_data['order_coupon_used'])) {
        // order_coupon_used ممکن است comma-separated باشد
        $coupon_codes = explode(',', $order_data['order_coupon_used']);
        $campaign_titles = [];
        
        foreach ($coupon_codes as $coupon_code) {
            $coupon_code = trim($coupon_code);
            if (empty($coupon_code)) continue;
            
            $coupon = new WC_Coupon($coupon_code);
            $coupon_id = $coupon ? $coupon->get_id() : 0;
            if ($coupon_id) {
                $is_campaign = get_post_meta($coupon_id, '_is_discount_campaign', true) === 'yes';
                if ($is_campaign) {
                    $title = trim((string) get_post_meta($coupon_id, '_discount_campaign_title', true));
                    if ($title !== '') {
                        $campaign_titles[] = $title;
                    }
                }
            }
        }
        
        if (!empty($campaign_titles)) {
            $campaign_title = implode('، ', $campaign_titles);
        }
    }

    $items[] = [
        'order_id'      => (int) $order_id,
        'product_title' => $order_data['game_name'] ?: get_the_title($product_id),
        'tickets_count' => $quantity,
        'purchase_time' => $purchase_time,
        'sans_time'     => $sans_time,
        'total_payment' => (int) $item_total,
        'prepaid'       => (int) $prepaid,
        'has_discount'  => $has_discount,
        'status'        => [$order_status, $color],
        'campaign_title' => $campaign_title,
        'product_url'   => trim_home_url(get_permalink($product_id)),
    ];
}

$data['items'] = $items; ?>

<section class="max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none max-lg:border-0 max-lg:py-0">
    <div class="relative">
        <div class="relative overflow-x-auto">
            <?php if (count($data['items']) > 0) : ?>

                <table class="w-full text-right text-sm max-lg:hidden">
                    <thead class="border-b border-t border-slate-120 text-xs text-slate-350 max-lg:hidden">
                        <tr>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">کد رزرو
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">اتاق</th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">تعداد
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">تاریخ
                                خرید
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">تاریخ
                                بازی
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">مبلغ کل
                                (تومان)
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">پیش
                                پرداخت
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">بستانکاری
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">تخفیف دار
                            </th>
                            <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">وضعیت
                                بازی
                            </th>
                        </tr>
                    </thead>
                    <tbody class="max-lg:flex max-lg:flex-col">
                        <?php foreach ($data['items'] as $sell): ?>
                            <tr class="font-bold">
                                <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                    <a href="<?php echo esc_url(site_url($sell['product_url'])); ?>">
                                        <?php echo esc_html($sell['order_id']); ?>
                                    </a>
                                </td>
                                <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0"><?php echo esc_html($sell['product_title']); ?></td>
                                <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0"><?php echo esc_html($sell['tickets_count']); ?>
                                    بلیت
                                </td>
                                <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0"><?php echo esc_html(jdate('Y/m/d', $sell['purchase_time'])); ?></td>
                                <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                    <?php echo esc_html(jdate('Y.m.d', $sell['sans_time'])); ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <?php echo esc_html(jdate('H:i', $sell['sans_time'])); ?>
                                </td>
                                <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0"><?php echo esc_html(number_format((int) $sell['total_payment'])); ?></td>
                                <td class="border-b border-slate-120 px-4 py-6 text-green-500 first:pr-0 last:pl-0"><?php echo esc_html(number_format((int) $sell['prepaid'])); ?></td>
                                <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0"><?php echo esc_html(number_format((int) $sell['total_payment'] - $sell['prepaid'])); ?></td>
                                <td class="border-b border-slate-120 px-4 py-6 text-center first:pr-0 last:pl-0">
                                    <span class="<?php echo $sell['has_discount'] ? 'text-green-500' : ''; ?>">
                                        <?php echo $sell['has_discount'] ? '✓' : '-'; ?>
                                    </span>
                                    <?php if (!empty($sell['campaign_title'])): ?>
                                        <span class="text-slate-500"><?php echo esc_html($sell['campaign_title']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="border-b border-slate-120 px-4 py-6 text-green-500 first:pr-0 last:pl-0" style="color: <?php echo esc_attr($sell['status'][1]); ?>">
                                    <?php echo esc_html($sell['status'][0]); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="lg:hidden flex flex-col">
                    <?php foreach ($data['items'] as $sell) { ?>
                        <div class="border-b py-8 flex flex-col">
                            <div class="grid grid-cols-2 leading-3 gap-4">
                                <div class="flex gap-x-3 justify-start">
                                    <span class="text-slate-350 text-md">سرگرمی</span>
                                    <span class="font-bold text-lg"><?php echo esc_html($sell['product_title']); ?></span>
                                </div>
                                <div class="flex gap-x-3 justify-end">
                                    <span class="text-slate-350 text-md">کد رزرو</span>
                                    <span class="font-bold text-lg"><?php echo $sell['order_id']; ?></span>
                                </div>
                                <div class="flex gap-x-3 justify-start">
                                    <span class="font-bold text-lg"><?php echo $sell['tickets_count']; ?> بلیت</span>
                                </div>
                                <div class="flex gap-x-3 justify-end">
                                    <span class="text-slate-350 text-md">تاریخ بازی</span>
                                    <span class="font-bold text-lg">
                                        <?php if ($sell['sans_time']) {
                                            echo esc_html(jdate('Y/m/d', $sell['sans_time']));
                                            echo "&nbsp;&nbsp;&nbsp;";
                                            echo esc_html(jdate('H:i', $sell['sans_time']));
                                        } ?>
                                    </span>
                                </div>
                            </div>
                            <span class="text-lg text-center mt-4 p-2 rounded-md" style="color: <?php echo $sell['status'][1]; ?>;background: <?php echo $sell['status'][1]; ?>1A"><?php echo $sell['status'][0]; ?></span>
                            <button type="button" class="show-more text-slate-350 flex gap-3 mt-4 items-center justify-center w-fit mx-auto">
                                <span>مشاهده جزئیات بیشتر</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="5" viewBox="0 0 9 5" fill="none" class="mx-0 transition-all duration-150">
                                    <path d="M7.5 1L5.20711 3.29289C4.81658 3.68342 4.18342 3.68342 3.79289 3.29289L1.5 1" stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </button>
                            <div class="mt-5" style="display: none">
                                <div class="bg-slate-60 grid grid-cols-2 rounded-md p-4 gap-4">
                                    <div class="flex gap-x-3 items-center justify-start">
                                        <span class="text-slate-350 text-md">تاریخ خرید</span>
                                        <span class="font-bold text-lg">
                                            <?php if ($sell['purchase_time']) {
                                                echo esc_html(jdate('Y/m/d', $sell['purchase_time']));
                                            } ?>
                                        </span>
                                    </div>
                                    <div class="flex gap-x-3 items-center justify-start">
                                        <span class="text-slate-350 text-md">مبلغ کل</span>
                                        <span class="font-bold text-lg">
                                            <?php echo number_format($sell['total_payment']); ?>
                                        </span>
                                    </div>
                                    <div class="flex gap-x-3 items-center justify-start">
                                        <span class="text-slate-350 text-md">پیش پرداخت</span>
                                        <span class="font-bold text-lg text-green-500">
                                            <?php echo number_format($sell['prepaid']); ?>
                                        </span>
                                    </div>
                                    <div class="flex gap-x-3 items-center justify-start">
                                        <span class="text-slate-350 text-md rounded-xl">مانده پرداخت</span>
                                        <span class="font-bold text-lg">
                                            <?php echo number_format($sell['total_payment'] - $sell['prepaid']); ?>
                                        </span>
                                    </div>
                                    <div class="flex gap-x-3 items-center justify-start">
                                        <span class="text-slate-350 text-md">تخفیف دار</span>
                                        <span class="font-bold text-lg <?php echo $sell['has_discount'] ? 'text-green-500' : ''; ?>">
                                            <?php echo $sell['has_discount'] ? '✓' : '-'; ?>
                                        </span>
                                        <?php if (!empty($sell['campaign_title'])): ?>
                                            <span class="text-slate-500"><?php echo esc_html($sell['campaign_title']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

            <?php else: ?>
                <div class="text-[22px] font-bold lg:text-lg text-center lg:my-19 text-gray-500">
                    تا این لحظه هیچ فروشی برای شما
                    <br>
                    ثبت نشده است.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($data['items']) > 0) : ?>
        <div class="mb-9 flex w-full items-center justify-center gap-4">
            <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
                <?php echo paginate_links([
                    'mid_size'  => 1,
                    'base'      => get_pagenum_link(1) . '%_%',
                    'format'    => '?page=%#%',
                    'current'   => $page_num,
                    'total'     => $total_pages,
                    'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                    'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                ]);
                ?>
            </div>
        </div>
    <?php endif; ?>

</section>