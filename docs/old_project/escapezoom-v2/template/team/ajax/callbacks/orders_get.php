<?php

global $wpdb;

$medoo = medoo();

$state      = sanitize_text_field($_POST['state']) ?: 'all';
$order_ids  = isset($_POST['order_ids']) ? (array) $_POST['order_ids'] : false;
$page_num   = sanitize_text_field($_POST['page']) ?: 1;

$posts_per_page = 50;

if ($order_ids !== false) {

    $args = [
        'post_type'      => 'shop_order',
        'post_status'    => 'any',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page_num,
        'post__in'       => $order_ids,
    ];
} else {
    $args = [
        'post_type'         => 'shop_order',
        'post_status'       => 'any',
        'posts_per_page'    => $posts_per_page,
        'paged'             => $page_num,
    ];
}

$quantity_all = 0; ?>

<section class="justify-center items-center mt-7 mx-auto">
    <div class="w-full py-4 rounded-t-2.5xl bg-[#E4EBF0]">
        <div class="grid" style="grid-template-columns: 2fr 2fr 4fr 2fr 5fr 1fr 2fr 2fr 5fr 1fr 3fr 2fr">
            <p class="text-center mx-auto">کد رزرو</p>
            <p class="text-center mx-auto">تاریخ رزرو</p>
            <p class="text-center mx-auto">نام</p>
            <p class="text-center mx-auto">تماس</p>
            <p class="text-center mx-auto">بازی</p>
            <p class="text-center mx-auto">تعداد</p>
            <p class="text-center mx-auto">شماره تراکنش</p>
            <p class="text-center mx-auto">سپرده</p>
            <p class="text-center mx-auto">سانس</p>
            <p class="text-center mx-auto">هپی</p>
            <p class="text-center mx-auto">وضعیت</p>
            <p class="text-center mx-auto">عملیات</p>
        </div>
    </div>

    <div class="w-full h-full rounded-t-2.5xlb" id="ordersTable">

        <?php
        if ($state == 'all') :

            $the_query = new WP_Query($args);

            $orders_id_list = wp_list_pluck($the_query->posts, 'ID');
            $orders_id_text = implode(',', $orders_id_list);

            $sans_args = [
                "single_value"  => false,
                "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` IN ($orders_id_text) ORDER BY `wc_order_id` DESC",
            ];
            $sanses_obj = (array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $sans_args)));

            foreach ($sanses_obj as $sans_obj)
                $sanses_time[$sans_obj->wc_order_id] = $sans_obj->booking_time;

            if ($the_query->have_posts()) :
                while ($the_query->have_posts()) : $the_query->the_post();
                    static $row_index = 0;
                    $row_index++;
                    $row_bg_class = ($row_index % 2 == 0) ? 'bg-gray-100/50' : '';
                    $order_id = get_the_ID();

                    $is_completed_order = false;

                    $order = wc_get_order($order_id);

                    if ($order->get_billing_first_name() || $order->get_billing_last_name())
                        $buyer = trim(sprintf(_x('%1$s %2$s', 'full name', 'woocommerce'), $order->get_billing_first_name(), $order->get_billing_last_name()));

                    $order_date = human_time_diff(strtotime($order->get_date_created()), current_time('timestamp')) . ' قبل';

                    $order_status = $order->get_status();
                    if ($order_status == 'pending')               $order_status_color = "color: #FD7013;";
                    elseif ($order_status == 'cancelled')         $order_status_color = "color: #F21543;";
                    elseif ($order_status == 'refunded')          $order_status_color = "color: #F21543;";
                    elseif ($order_status == 'conflict')          $order_status_color = "color: #F21543;";
                    elseif ($order_status == 'admin-cancelled')   $order_status_color = "color: #F21543;";

                    elseif ($order_status == 'completed') {
                        $order_status_color = "color: #049654;";
                        $is_completed_order = true;
                    } elseif ($order_status == 'partially-paid') {
                        $order_status_color = "color: #049654;";
                        $is_completed_order = true;
                    } elseif ($order_status == 'walletx') {
                        $order_status_color = "color: #049654;";
                        $is_completed_order = true;
                    }

                    foreach ($order->get_items() as $item_id => $item) {
                        $product_id     = $item->get_product_id();
                        $product_name   = $item->get_name();
                        $quantity       = $item->get_quantity();
                    }

                    if ($is_completed_order)
                        $quantity_all += $quantity;

                    $prepaid = get_post_meta($order_id, "prepaid", true);

                    $sans_time = $sanses_time[$order_id];
                    if (!$sans_time) // اگه سانسی نداشت مبلغ باید از _order_total_2 خوانده شود.
                        $prepaid = false;

                    $pish_final = $prepaid ?: (get_post_meta($order_id, "_order_total_2", true) ?: get_post_meta($order_id, "_order_total", true));

                    $happy_call_status = get_post_meta($order_id, "supporting_happycall", true) == 1 ? '1' : '0';

                    $background = $happy_call_status ? '#5091FB' : '#fff';
                    $span_display = $happy_call_status ? 'flex' : 'none';
                    $checked_attr = $happy_call_status ? 'checked' : '';

        ?>

                    <div id="orders_table_row" class="grid <?= $row_bg_class ?> text-center px-4 py-2.5" data-id="<?php echo $order_id ?>"
                        style="grid-template-columns: 1fr 2fr 4fr 2fr 4fr 1fr 2fr 2fr 4fr 1fr 3fr 1fr">
                        <p class="text-base content-center text-[#889BAD]"><?php echo $order_id ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $order_date ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $buyer ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $order->billing_phone ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $product_name ?></p>
                        <p class="text-base content-center text-navyBlue quantity"><?php echo $quantity ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo get_post_meta($order_id, '_transaction_id', true) ?: '---' ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo number_format(intval($pish_final)); ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $sans_time ? wp_date('H:i___Y-m-d', $sans_time) : '---' ?></p>
                        <p class="text-base content-center text-navyBlue">
                            <label class="flex items-center justify-center relative" style="position: relative;">
                                <input type="checkbox" class="happycall" value="1"
                                    <?php echo $checked_attr; ?>
                                    style="appearance: none; width: 24px; height: 24px; border-radius: 50%; border: 2px solid #D1D5DB; background: <?php echo $background; ?>; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; position: relative; z-index: 1;">
                                <span style="pointer-events: none; position: absolute; width: 24px; height: 24px; display: <?php echo $span_display; ?>; align-items: center; justify-content: center; left: 10px; top: 0; z-index: 2;">
                                    <svg class="checkmark" style="width: 16px; height: 16px; margin: auto;" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 8.5L7 11.5L12 5.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </label>
                        </p>
                        <p class="text-base font-bold content-center" style="<?= $order_status_color ?>"><?php echo wc_get_order_status_name($order->get_status()) ?></p>
                        <div class="mainContent transition-all duration-300 flex gap-2">

                            <?php
                            if (array_intersect(['administrator', 'supervisor', 'poshtiban'], wp_get_current_user()->roles)) : ?>
                                <button alt="CRM" class="openCrmModal cursor-pointer hover:opacity-80 w-7 h-7 m-auto transition" data-id="<?= $order_id ?>" data-happy-call="<?= get_post_meta($order_id, "supporting_happycall", true) == 1 ? '1' : '0' ?>">
                                    <img src="<?= get_template_directory_uri() ?>/assets/images/crm-btn.png" alt="CRM" class="w-7 h-7">
                                </button>
                            <?php
                            endif;

                            if (array_intersect(['administrator', 'supervisor', 'accounting'], wp_get_current_user()->roles)) : ?>
                                <button alt="Mali" class="openMaliModal cursor-pointer hover:opacity-80 w-7 h-7 m-auto transition" data-id="<?= $order_id ?>">
                                    <img src="<?= get_template_directory_uri() ?>/assets/images/mali-btn.png" alt="Mali" class="w-7 h-7">
                                </button>
                            <?php
                            endif; ?>

                        </div>
                    </div>

                <?php
                endwhile;

                $total_pages = $the_query->max_num_pages;

                wp_reset_postdata();
            endif;

        elseif ($state == 'bad') :

            $orders_obj = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID, post_status FROM `wp_posts` WHERE `post_type` = %s AND `post_status` != %s ORDER BY `ID` DESC LIMIT 10000",
                    'shop_order',
                    'wc-walletx'
                )
            );

            $orders_id_list = implode(',', array_map(function ($item) {
                return $item->ID;
            }, $orders_obj));
            $bad_orders_id  = [];

            $sans_args = [
                "single_value"  => false,
                "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` IN ($orders_id_list) ORDER BY `wc_order_id` DESC",
            ];
            $sanses_time = (array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $sans_args)));

            foreach ($orders_obj as $order_obj) :
                $sans_time = false;
                foreach ($sanses_time as $object)
                    if (isset($object->wc_order_id) && $object->wc_order_id == $order_obj->ID)
                        $sans_time = $object;

                if ($sans_time)
                    continue;

                if ($order_obj->post_status != 'wc-partially-paid')
                    continue;

                $bad_orders_id[] = $order_obj;
            endforeach;

            $bad_orders_id_list = array_map(function ($item) {
                return $item->ID;
            }, $bad_orders_id);
            $args = [
                'post_type'     => 'shop_order',
                'post_status'   => 'any',
                'post__in'      => $bad_orders_id_list,
            ];
            $the_query = new WP_Query($args);
            if ($the_query->have_posts()) :
                while ($the_query->have_posts()) : $the_query->the_post();
                    $order_id = get_the_ID();

                    $status = $wpdb->get_var($wpdb->prepare(
                        "SELECT post_status FROM $wpdb->posts WHERE ID = %d AND post_type = 'shop_order'",
                        $order_id
                    ));

                    $sans_args = [
                        "single_value"  => true,
                        "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id ORDER BY `booking_id` DESC",
                    ];
                    $sans_time = ((array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $sans_args))))['booking_time'];

                    if ($sans_time) // اگه سانسی برای این سفارش بسته شده بود پس جز سفارش های بد نیست.
                        continue;

                    if ($status != 'wc-partially-paid') // فقط سفارش هایی که استتوس wc-partially-paid رو دارند میتونند جز سفارش های بد باشند.
                        continue;

                    $order = wc_get_order($order_id);

                    if ($order->get_billing_first_name() || $order->get_billing_last_name())
                        $buyer = trim(sprintf(_x('%1$s %2$s', 'full name', 'woocommerce'), $order->get_billing_first_name(), $order->get_billing_last_name()));

                    $order_date = human_time_diff(strtotime($order->get_date_created()), current_time('timestamp')) . ' قبل';

                    $order_status = $order->get_status();
                    if ($order_status == 'pending')               $order_status_color = "color: #FD7013;";
                    elseif ($order_status == 'cancelled')         $order_status_color = "color: #F21543;";
                    elseif ($order_status == 'refunded')          $order_status_color = "color: #F21543;";
                    elseif ($order_status == 'conflict')          $order_status_color = "color: #F21543;";
                    elseif ($order_status == 'admin-cancelled')   $order_status_color = "color: #F21543;";
                    elseif ($order_status == 'completed')         $order_status_color = "color: #049654;";
                    elseif ($order_status == 'partially-paid')    $order_status_color = "color: #049654;";
                    elseif ($order_status == 'walletx')           $order_status_color = "color: #049654;";

                    foreach ($order->get_items() as $item_id => $item) {
                        $product_name   = $item->get_name();
                        $quantity       = $item->get_quantity();
                    }

                    $pish_final = get_post_meta($order_id, "_order_total_2", true) ?: get_post_meta($order_id, "_order_total", true);
                    // تعیین کلاس بک‌گراند برای ردیف‌های زوج
                    static $row_index_1 = 0;
                    $row_index_1++;
                    $row_bg_class_1 = ($row_index_1 % 2 == 0) ? 'bg-gray-100/50' : '';
                ?>

                    <div class="grid grid-cols-[2fr,3fr,4fr,3fr,4fr,1fr,2fr,2fr,4fr,3fr,1fr] <?= $row_bg_class_1 ?> text-center px-4 py-2.5">
                        <p class="text-base content-center text-[#889BAD]"><?php echo $order_id ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $order_date ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $buyer ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $order->billing_phone ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $product_name ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $quantity ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo get_post_meta($order_id, '_transaction_id', true) ?: '---' ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo number_format(intval($pish_final)) . ' تومان'; ?></p>
                        <p class="text-base content-center text-navyBlue"><?php echo $sans_time ? wp_date('H:i  Y-m-d', $sans_time) : '---' ?></p>
                        <p class="text-base content-center" style="<?= $order_status_color ?>"><?php echo wc_get_order_status_name($order->get_status()) ?></p>
                        <div class="mainContent transition-all duration-300 flex">
                            <button alt="Click Me" class="openModal cursor-pointer hover:opacity-80 w-7 h-7 m-auto transition" data-id="<?= $order_id ?>">
                                <svg class="mx-0" width="27" height="28" viewBox="0 0 27 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect y="0.5" width="27" height="27" rx="6" fill="#FD7013" />
                                    <path d="M9.33333 10.6638H8.64667C7.39917 10.6638 6.775 10.6638 6.38833 10.2971C6 9.93293 6 9.34376 6 8.16626C6 6.98876 6 6.3996 6.3875 6.0346C6.775 5.66876 7.39917 5.66876 8.64667 5.66876H18.3525C19.6008 5.66876 20.225 5.66876 20.6125 6.0346C21 6.40043 21 6.98793 21 8.16543C21 9.34293 21 9.9321 20.6125 10.2979C20.225 10.6638 19.6008 10.6638 18.3525 10.6638H17.25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M18.5252 22.3263C18.4835 20.8979 18.6002 20.7096 18.7093 20.3929C18.8193 20.0746 19.4985 18.9471 19.7702 18.1279C20.6468 15.4796 19.976 15.0321 18.9252 14.2279C17.7218 13.3054 15.6835 12.8363 14.4127 12.9379V9.5296C14.4127 8.81043 13.7243 8.1846 12.9527 8.1846C12.181 8.1846 11.4977 8.81043 11.4977 9.5296V15.9896L9.85515 14.5913C9.41182 14.1438 8.70265 14.1846 8.18849 14.5238C8.02859 14.6295 7.90042 14.7767 7.81765 14.9496C7.58432 15.4404 7.65099 15.9954 8.01932 16.4496L8.95265 17.6538M8.95265 17.6538C9.17599 17.9238 9.40182 18.2388 9.68515 18.5971M8.95265 17.6538L9.68515 18.5971M11.4402 22.3313V21.5429C11.501 20.5738 10.621 19.7963 9.68515 18.5971M9.68515 18.5971C9.61765 18.5104 9.74849 18.6779 9.68515 18.5971ZM9.68515 18.5971L10.6077 19.7254" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

            <?php
                endwhile;
                wp_reset_postdata();
            endif; ?>

        <?php
        endif; ?>
    </div>
</section>

<?php if ($total_pages > 1) { ?>
    <div class="flex justify-between items-center mt-10">
        <div class="flex mx-auto gap-4 max-lg:gap-2 pagination">
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
<!-----------CRM Modal------------------------------------->
<section class="flex justify-center items-center fixed inset-0 modal-bg z-50 transition-opacity" id="crmModal" data-id="" style="display: none;">
    <div class="rounded-xl bg-white border border-[#DBE2EA] shadow-[0px_1px_0px_0px_#DBE2EA] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 absolute p-5 w-[442px]">
        <!--        <div class="flex justify-between items-center mb-4">-->
        <!--            <h4 class="text-navyBlue text-lg font-bold"> هپی کال </h4>-->
        <!--            <label class="flex items-center justify-center relative" style="position: relative;">-->
        <!--                <input-->
        <!--                    type="checkbox"-->
        <!--                    class="happycall"-->
        <!--                    value="1"-->
        <!--                    style="appearance: none; width: 24px; height: 24px; border-radius: 50%; border: 2px solid #D1D5DB; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; position: relative; z-index: 1;">-->
        <!--                <span style="-->
        <!--                    pointer-events: none;-->
        <!--                    position: absolute;-->
        <!--                    width: 24px;-->
        <!--                    height: 24px;-->
        <!--                    display: none;-->
        <!--                    align-items: center;-->
        <!--                    justify-content: center;-->
        <!--                    left: 0;-->
        <!--                    top: 0;-->
        <!--                    z-index: 2;-->
        <!--                ">-->
        <!--                    <svg class="checkmark" style="width: 16px; height: 16px; margin: auto;" viewBox="0 0 16 16" fill="none">-->
        <!--                        <path d="M4 8.5L7 11.5L12 5.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />-->
        <!--                    </svg>-->
        <!--                </span>-->
        <!--            </label>-->
        <!--        </div>-->

        <!--        <hr class="mb-4" />-->

        <div class="mb-4">
            <div class="flex justify-between items-center cursor-pointer" id="teammatesToggle">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 8C9.65685 8 11 6.65685 11 5C11 3.34315 9.65685 2 8 2C6.34315 2 5 3.34315 5 5C5 6.65685 6.34315 8 8 8Z" fill="#6B7280" />
                        <path d="M8 9C5.79086 9 4 10.7909 4 13V14H12V13C12 10.7909 10.2091 9 8 9Z" fill="#6B7280" />
                    </svg>
                    <span class="text-grayy text-sm">مشاهده هم تیمی ها</span>
                </div>
                <svg class="mx-0 transition-transform duration-300" id="teammatesArrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 6L8 10L12 6" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <!-- Teammates Dropdown -->
            <div id="teammatesDropdown" class="mt-2 hidden">
                <div class="max-h-48 overflow-y-auto">
                    <div class="space-y-1">

                        <div class="flex justify-between items-center py-2 px-3 border-b border-gray-200">
                            <span class="text-grayy text-sm">سید محمود اسکیپ مستر</span>
                            <span class="text-grayy text-sm">۰۹۱۲۳۳۵۷۸۹۵</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <hr class="mb-4" />

        <div class="grid grid-cols-2 gap-x-4">
            <button class="cancellation_request px-4 py-1.5 rounded-lg text-white cursor-pointer bg-gray-500 text-sm font-bold w-full h-12.5" data-type="customer">کنسلی برای پلیر</button>
            <button class="cancellation_request px-4 py-1.5 rounded-lg text-white cursor-pointer bg-gray-500 text-sm font-bold w-full h-12.5" data-type="owner">کنسلی برای مجموعه</button>
        </div>

        <!-- Owner Cancellation Reason Box (Sliding from bottom) -->
        <div id="ownerCancellationBox" class="mt-4 p-4 bg-white hidden">
            <h5 class="text-base font-bold mb-2">چرا می خواهید سانس را لغو کنید؟</h5>
            <p class="text-sm text-grayy mb-4">لطفاً یک گزینه را انتخاب کنید:</p>
            <hr class="mb-4 bg-gray-200 h-[2px]">
            <div class="space-y-3 mb-4">

                <?php
                foreach (cancellation_reasons() as $key => $reason) : ?>

                    <label class="flex items-center gap-x-2 cursor-pointer">
                        <div class="relative">
                            <input type="radio" name="cancellationReason" value="<?php echo $key ?>" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 opacity-0 absolute">
                            <div class="w-4 h-4 border-2 border-gray-300 rounded-full flex items-center justify-center radio-custom">
                                <div class="w-2 h-2 bg-blue-600 rounded-full hidden radio-check"></div>
                            </div>
                        </div>
                        <span class="text-sm"><?php echo $reason ?></span>
                    </label>

                <?php
                endforeach; ?>

            </div>

            <div class="flex items-start gap-2 mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm">در صورت لغو کمتر از ۲۴ ساعت پیش از سانس، امتیاز و عملکرد برند تحت تأثیر قرار می گیرد.</p>
            </div>

            <button id="submitCancellation" class="w-full bg-orange-500 text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-orange-600 h-12.5">ثبت</button>
        </div>
    </div>
</section>

<!-----------Mali Modal------------------------------------->
<section class="flex justify-center items-center fixed inset-0 modal-bg z-50 transition-opacity" id="maliModal" data-id="" style="display: none;">
    <div class="rounded-xl bg-white border border-[#DBE2EA] shadow-[0px_1px_0px_0px_#DBE2EA] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 absolute p-5">
        <div class="grid grid-cols-4 gap-x-2">
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-red-500 text-sm font-bold w-full" data-action="walletx">زباله دان</button>
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-[#1C398E] text-sm font-bold w-full" data-action="walletx">کیف پول</button>
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-[#FF6900] text-sm font-bold w-full" data-action="refunded">مسترد</button>
            <button class="order_status_change px-2 py-1.5 rounded-lg text-white cursor-pointer bg-[#F21543] text-sm font-bold w-full" data-action="admin-cancelled">لغو ادمین</button>
        </div>
    </div>
</section>