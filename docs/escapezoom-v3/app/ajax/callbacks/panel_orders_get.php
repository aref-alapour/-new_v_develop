<?php

global $wpdb;

// Get Current User ID
$user = get_current_user_id();

// Get Order Status
$status = sanitize_text_field($_POST['status']);
$page   = sanitize_text_field($_POST['page']) ?: 1;

$items_per_page = 10;

// استفاده از medoo برای query از wp_markting
$medoo = medoo();
if (!$medoo) {
    wp_send_json_error('خطا در اتصال به دیتابیس');
    return;
}

// ساخت شرط‌های فیلتر بر اساس status
$where_conditions = ['customer_id' => $user];

// فیلتر وضعیت سفارش
switch ($status) {
    case 'reserved':
        $where_conditions['order_status'] = ['wc-partially-paid', 'wc-completed-paid','wc-completed'];
        break;
    case 'held':
        $where_conditions['order_status'] = ['wc-walletx','wc-completed'];
        break;
    case 'cancelled':
        $where_conditions['order_status'] = ['wc-admin-cancelled', 'wc-refunded', 'wc-conflict'];
        break;
    default:
        // all - همه وضعیت‌ها
        $where_conditions['order_status'] = ['wc-partially-paid','wc-completed-paid', 'wc-walletx', 'wc-completed', 'wc-admin-cancelled', 'wc-refunded', 'wc-conflict'];
        break;
}

// محاسبه offset برای pagination
$offset = ($page - 1) * $items_per_page;

try {
    // دریافت تعداد کل سفارشات
    $orders_count = $medoo->count('wp_markting', $where_conditions);
    $total_pages = ceil($orders_count / $items_per_page);

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
        'order_status'
    ], array_merge($where_conditions, [
        'ORDER' => ['order_created_at' => 'DESC'],
        'LIMIT' => [$offset, $items_per_page]
    ]));
} catch (Exception $e) {
    error_log('Error in panel_orders_get: ' . $e->getMessage());
    wp_send_json_error('خطا در دریافت سفارشات: ' . $e->getMessage());
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
    $product_quantity = $order_data['order_tickets_quantity'];
    
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
        // محاسبه از order_paid
        if($order_data['order_status'] == 'wc-completed-paid'){
            $item_total = $prepaid;
        }else{
            $item_total = $prepaid / $pish_per_person * $product_quantity;
        }
    }

    // تبدیل order_created_at به timestamp برای purchase_time
    $purchase_time = 0;
    if (!empty($order_data['order_created_at'])) {
        $purchase_time = strtotime($order_data['order_created_at']);
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

    $items[] = [
        'order_id'      => $order_id,
        'product_id'    => $product_id,
        'product_title' => $order_data['game_name'] ?: get_the_title($product_id),
        'tickets_count' => $product_quantity,
        'purchase_time' => $purchase_time,
        'sans_time'     => $sans_time,
        'total_payment' => (int) $item_total,
        'prepaid'       => (int) $prepaid,
        'status'        => $order_status,
        'color'         => $color,
        'product_url'   => trim_home_url(get_permalink($product_id)),
    ];
}
?>

<div class="relative overflow-x-auto">
    <?php if (count($items) > 0) { ?>
        <div class="max-lg:hidden">
            <table class="w-full text-right text-sm">
                <thead class="border-b border-t border-slate-120 text-xs text-slate-350 max-lg:hidden">
                    <tr>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">کد رزرو</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">بازی</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">تعداد</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">تاریخ خرید</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">تاریخ بازی</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">مبلغ کل (تومان)</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">پیش پرداخت</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">مانده پرداخت</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">وضعیت بازی</th>
                        <th scope="col" class="text-nowrap px-4 py-6 first:pr-0 last:pl-0">شماره مجموعه</th>
                    </tr>
                </thead>
                <tbody class="max-lg:flex max-lg:flex-col">
                    <?php foreach ($items as $item) { ?>
                        <tr class="font-bold my_orders_res_row" data-orderid="<?php echo $item['order_id']; ?>">
                            <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                <a href="<?php echo site_url($item['product_url']); ?>"><?php echo $item['order_id']; ?></a>
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                <a href="<?php echo site_url($item['product_url']); ?>"><?php echo $item['product_title']; ?></a>
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                <?php echo $item['tickets_count']; ?> بلیت
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                <?php if ($item['purchase_time']) {
                                    echo esc_html(jdate('Y/m/d', $item['purchase_time']));
                                } ?>
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                <?php if ($item['sans_time']) {
                                    echo esc_html(jdate('Y/m/d', $item['sans_time']));
                                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                    echo esc_html(jdate('H:i', $item['sans_time']));
                                } ?>
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                <?php echo number_format($item['total_payment']); ?>
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 text-green-500 first:pr-0 last:pl-0">
                                <?php echo number_format($item['prepaid']); ?>
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 first:pr-0 last:pl-0">
                                <?php echo number_format($item['total_payment'] - $item['prepaid']); ?>
                            </td>
                            <td class="border-b border-slate-120 px-4 py-6 text-green-500 first:pr-0 last:pl-0" style="color: <?php echo $item['color']; ?>">
                                <?php echo $item['status']; ?>
                            </td>

                            <td class="border-b border-slate-120 px-4 py-6 text-green-500 first:pr-0 last:pl-0" style="color: <?php echo $item['color']; ?>">
                                <a href="tel:<?php echo get_field('room_phone', $item['product_id']); ?>"><?php echo get_field('room_phone', $item['product_id']); ?></a>
                            </td>

                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="lg:hidden flex flex-col">
            <?php foreach ($items as $item) { ?>
                <div class="border-b py-8 flex flex-col">
                    <div class="grid grid-cols-2 leading-3 gap-4">
                        <div class="flex gap-x-3 justify-start">
                            <span class="text-slate-350 text-md">بازی</span>
                            <span class="font-bold text-lg"><?php echo $item['product_title']; ?></span>
                        </div>
                        <div class="flex gap-x-3 justify-end">
                            <span class="text-slate-350 text-md">کد رزرو</span>
                            <span class="font-bold text-lg"><?php echo $item['order_id']; ?></span>
                        </div>
                        <div class="flex gap-x-3 justify-start">
                            <span class="font-bold text-lg"><?php echo $item['tickets_count']; ?> بلیت</span>
                        </div>
                        <div class="flex gap-x-3 justify-end">
                            <span class="text-slate-350 text-md">تاریخ بازی</span>
                            <span class="font-bold text-lg">
                                <?php
                                if ($item['sans_time']) {
                                    echo esc_html(jdate('Y/m/d', $item['sans_time']));
                                    echo "&nbsp;&nbsp;&nbsp;";
                                    echo esc_html(jdate('H:i', $item['sans_time']));
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <span class="text-lg text-center mt-4 p-2 rounded-md" style="color: <?php echo $item['color']; ?>;background: <?php echo $item['color']; ?>1A"><?php echo $item['status']; ?></span>
                    <button type="button" class="show-more text-slate-350 flex gap-3 mt-4 items-center justify-center w-fit mx-auto">
                        <span>مشاهده جزئیات بیشتر</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="5" viewBox="0 0 9 5" fill="none" class="transition-all duration-150">
                            <path d="M7.5 1L5.20711 3.29289C4.81658 3.68342 4.18342 3.68342 3.79289 3.29289L1.5 1" stroke="#09192D" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <div class="mt-5" style="display: none">
                        <div class="bg-slate-60 grid grid-cols-2 rounded-md p-4 gap-4">
                            <div class="flex gap-x-3 items-center justify-start">
                                <span class="text-slate-350 text-md">تاریخ خرید</span>
                                <span class="font-bold text-lg">
                                    <?php if ($item['purchase_time']) {
                                        echo esc_html(jdate('Y/m/d', $item['purchase_time']));
                                    } ?>
                                </span>
                            </div>
                            <div class="flex gap-x-3 items-center justify-start">
                                <span class="text-slate-350 text-md">مبلغ کل</span>
                                <span class="font-bold text-lg">
                                    <?php echo number_format($item['total_payment']); ?>
                                </span>
                            </div>
                            <div class="flex gap-x-3 items-center justify-start">
                                <span class="text-slate-350 text-md">پیش پرداخت</span>
                                <span class="font-bold text-lg text-green-500">
                                    <?php echo number_format($item['prepaid']); ?>
                                </span>
                            </div>
                            <div class="flex gap-x-3 items-center justify-start">
                                <span class="text-slate-350 text-md rounded-xl">مانده پرداخت</span>
                                <span class="font-bold text-lg">
                                    <?php echo number_format($item['total_payment'] - $item['prepaid']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="text-22 font-bold lg:text-lg text-center lg:my-19 text-gray-500">
            سوابقی یافت نشد.
        </div>
    <?php } ?>
</div>

<?php if ($total_pages > 1) { ?>
    <div class="mb-9 flex w-full items-center justify-center gap-4">
        <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
            <?php echo paginate_links([
                'mid_size'  => 1,
                'base'      => get_pagenum_link(1) . '%_%',
                'format'    => '?page=%#%',
                'current'   => max(1, $page),
                'total'     => $total_pages,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
            ]); ?>
        </div>
    </div>
<?php } ?>