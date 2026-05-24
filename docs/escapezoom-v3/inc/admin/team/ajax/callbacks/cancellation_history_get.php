<?php
$medoo = medoo();

$search     = sanitize_text_field($_POST['term'] ?? '');
$status     = sanitize_text_field($_POST['status'] ?? 'all');
$page_num   = max(1, intval($_POST['page'] ?? 1));

$requests_per_page  = 50;
$offset             = ($page_num - 1) * $requests_per_page;

$where_select = [
    "ORDER" => ["cancellation_requests.created_at" => "DESC"],
    "LIMIT" => [$offset, $requests_per_page]
];

if ($status !== 'all') {
    $where_select["cancellation_requests.status"] = $status;
}

if ($search) {
    $where_select["OR"] = [
        "wp_users.display_name[~]" => $search,
        "wp_posts.post_title[~]" => $search,
        "cancellation_requests.order_id[~]" => $search
    ];
}

$requests = $medoo->select("cancellation_requests", [
    "[>]wp_posts" => ["product_id" => "ID"],
    "[>]wp_users" => ["requester_id" => "ID"]
], [
    "cancellation_requests.id",
    "cancellation_requests.order_id",
    "cancellation_requests.product_id",
    "cancellation_requests.requester_id",
    "cancellation_requests.status",
    "cancellation_requests.created_at",
    "cancellation_requests.sans_time",
    "cancellation_requests.requester_type",
    "wp_posts.post_title(product_name)",
    "wp_users.display_name(player_name)"
], $where_select);

$where_count = [];

if ($status !== 'all') {
    $where_count["status"] = $status;
}

if ($search) {
    // جستجو در کد رزرو
    $where_count["order_id[~]"] = $search;
}

$total_requests = $medoo->count("cancellation_requests", $where_count);
$total_pages = ceil($total_requests / $requests_per_page);
 ?>

<section id="ancellTableContainer" class="mt-7">
    <div class="w-full py-4 rounded-t-2.5xl bg-slate-105 grid">
        <div class="grid text-sm font-bold text-grayy text-center" style="grid-template-columns: 3fr 2fr 3fr 4fr 1fr 3fr 2fr 3fr 3fr;">
            <p>نوع درخواست</p>
            <p>تاریخ درخواست</p>
            <p>نام پلیر</p>
            <p>نام بازی</p>
            <p>کد رزرو</p>
            <p>سانس لغوشده</p>
            <p>موبایل</p>
            <p>توسط</p>
            <p>وضعیت</p>
        </div>
    </div>

    <div id="tableBody" class="w-full h-full py-4 rounded-t-2.5xlb grid gap-4">
        <?php
        foreach ($requests as $request):

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

            foreach ($order->get_items() as $item)
                $product_id = $item['product_id'];

//            $brand_data = get_the_terms($product_id, 'product_brand')[0]; ?>

            <?php
            // تعیین کلاس بک‌گراند برای ردیف‌های زوج
            static $row_index = 0;
            $row_index++;
            $row_bg_class = ($row_index % 2 == 0) ? 'bg-gray-100/50' : '';
            ?>
            <div class="gap-2 px-2 grid text-sm font-yekan-bold text-grayy text-center py-2.5 <?php echo $row_bg_class; ?>"
                 style="grid-template-columns: 3fr 2fr 3fr 4fr 1fr 3fr 2fr 3fr 3fr;">
                <p class="text-base font-bold text-text-3 flex gap-2 text-start items-center">
                    <span class="<?php echo $request_info1_class ?>"><?php echo $request_info1_text ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="6" viewBox="0 0 6 6" fill="none">
                        <circle cx="3" cy="3" r="3" fill="#62748E" />
                    </svg>
                    <span class="<?php echo $request_info2_class ?>"><?php echo $request_info2_text ?></span>
                </p>
                <p class="text-base font-bold text-navyBlue"><?php echo wp_date('Y.m.d', $request['created_at']) ?></p>
                <p class="text-base font-bold text-navyBlue"><?php echo $order->get_billing_first_name() . " " . $order->get_billing_last_name() ?></p>
<!--                <p class="text-base font-bold text-navyBlue">--><?php //echo $brand_data->name ?><!--</p>-->
                <p class="text-base font-bold text-navyBlue"><?php echo get_the_title($product_id); ?></p>
                <p class="text-base font-bold text-navyBlue"><?php echo htmlspecialchars($request['order_id']) ?></p>
                <p class="text-base font-bold text-navyBlue"><?php echo wp_date('H:i___Y.m.d', $request['sans_time']) ?></p>
                <p class="text-base font-bold text-navyBlue"><?php echo $order->get_billing_phone() ?></p>
                <p class="text-base font-bold text-navyBlue"><?php echo get_user_by('id', $request['requester_id'])->data->display_name ?></p>
                <p class="text-base font-bold <?php echo $status_class ?>"><?php echo $status_text ?></p>
            </div>
        <?php endforeach; ?>

    </div>

</section>

<?php if ($total_pages > 1) { ?>
    <div class="mb-9 flex w-full items-center justify-center gap-4">
        <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
            <?php echo paginate_links([
                'mid_size'  => 1,
                'base'      => get_pagenum_link(1) . '%_%',
                'format'    => '?page=%#%',
                'current'   => max(1, $page_num),
                'total'     => $total_pages,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
            ]); ?>
        </div>
    </div>
<?php } ?>