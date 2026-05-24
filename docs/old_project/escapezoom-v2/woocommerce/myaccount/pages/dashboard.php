<?php
global $wldb, $wpdb;

$user_id = get_current_user_id();

// استفاده از medoo برای query از wp_markting
$medoo = medoo();
if (!$medoo) {
    $in_progress_orders = [];
} else {
    // دریافت سفارشات partially-paid کاربر
    $where_conditions = [
        'customer_id' => $user_id,
        'order_status' => 'wc-partially-paid'
    ];

    try {
        $orders = $medoo->select('wp_markting', [
            'order_id',
            'game_id',
            'game_name',
            'order_tickets_quantity',
            'order_created_at',
            'order_sans_date',
            'order_sans_time'
        ], $where_conditions);
    } catch (Exception $e) {
        error_log('Error in dashboard.php: ' . $e->getMessage());
        $orders = [];
    }

    $in_progress_orders = [];

    // اطمینان از اینکه orders یک array است
    if (!is_array($orders)) {
        $orders = [];
    }

    foreach ($orders as $order_data) {
        $order_id = $order_data['order_id'];
        $product_id = $order_data['game_id'];
        $product_quantity = $order_data['order_tickets_quantity'];

        // تبدیل order_sans_date و order_sans_time به timestamp برای sans_time
        $sans_time = 0;
        if (!empty($order_data['order_sans_date']) && !empty($order_data['order_sans_time'])) {
            $sans_datetime = $order_data['order_sans_date'] . ' ' . $order_data['order_sans_time'];
            $sans_time = strtotime($sans_datetime);
        }

        // اگر sans_time نباشد یا بیش از 90 دقیقه گذشته باشد، skip کن
        if (!$sans_time || (time() - $sans_time > 90 * 60)) {
            continue;
        }

        $order_status = 'در راه بازی';
        $color = "#FD7013";

        // Check cancellation status
        $cancellation_query = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM cancellation_requests WHERE order_id = %d AND requester_type = 'customer' ORDER BY created_at DESC LIMIT 1",
            $order_id
        ));

        // Check if game is within 3 hours (3 * 60 * 60 = 10800 seconds)
        $game_time = $sans_time;
        $time_until_game = $game_time - time();
        $can_cancel = $time_until_game > 10800; // More than 3 hours

        $cancellation_status = 'cancel'; // Default state
        if ($cancellation_query) {
            $cancellation_status = $cancellation_query->status;
        } elseif (!$can_cancel) {
            $cancellation_status = 'not_allowed';
        }

        // تبدیل order_created_at به timestamp برای purchase_time
        $purchase_time = 0;
        if (!empty($order_data['order_created_at'])) {
            $purchase_time = strtotime($order_data['order_created_at']);
        }

        $in_progress_orders[] = [
            'order_id'      => $order_id,
            'product_title' => $order_data['game_name'] ?: get_the_title($product_id),
            'image'         => get_post_thumbnail_id($product_id),
            'tickets_count' => $product_quantity,
            'purchase_time' => $purchase_time,
            'status'        => $order_status,
            'color'         => $color,
            'product_url'   => get_permalink($product_id),
            'cancellation_status' => $cancellation_status,
        ];
    }
}

$user = wp_get_current_user();

// اطلاعیهٔ پرداخت اعتباری: فقط برای مجموعه‌دار؛ اگر یکبار غیرفعالسازی زد دیگر نشان ندهیم
$show_credit_notification = false;
$credit_notification_is_new = false;
if ( function_exists( 'has_role' ) && has_role( 'compiler' ) ) {
	$credit_table = $wpdb->prefix . 'creadit_form';
	$credit_form_record = $wpdb->get_row( $wpdb->prepare(
		"SELECT id, is_view, canceled FROM `{$credit_table}` WHERE owner_id = %d",
		$user_id
	) );
	// اگر canceled=1 باشد اطلاعیه را اصلاً نشان ندهیم
	if ( ! $credit_form_record || (int) $credit_form_record->canceled !== 1 ) {
		$show_credit_notification = true;
		// «جدید» فقط وقتی که هنوز مودال را نبسته (is_view=0 یا رکورد نداشته)
		$credit_notification_is_new = ! $credit_form_record || (int) $credit_form_record->is_view !== 1;
	}
}
if ( $show_credit_notification ) {
	$first_name = get_user_meta( $user->ID, 'first_name', true );
	$last_name  = get_user_meta( $user->ID, 'last_name', true );
	$credit_display_name = trim( $first_name . ' ' . $last_name ) ?: $user->display_name;
	$credit_phone = get_user_meta( $user->ID, 'billing_phone', true ) ?: $user->user_login;
}

$query = $wpdb->get_results("SELECT * FROM notifications WHERE `users` LIKE '%" . $user_id . "%' ORDER BY `id` DESC LIMIT 5");

$notifications = [];
foreach ($query as $item) {
    $data = [
        'id'          => $item->id,
        'title'       => $item->title,
        'description' => $item->content,
        'status'      => 0,
        'date'        => strtotime($item->created_at),
        'type'        => $item->type,
    ];

    $read = $item->read !== null ? unserialize($item->read) : [];

    if (in_array($user->ID, $read)) {
        $data['status'] = 1;
    }

    $notifications[] = $data;
}

$balance       = $wldb->get_balance($user_id);
$coupon_credit = 0;

$invitations      = $wpdb->get_results($wpdb->prepare("SELECT * FROM invitations WHERE invited_id LIKE %d ORDER BY created_at DESC LIMIT %d, %d", $user_id, 0, 3));
$last_invitations = [];
foreach ($invitations as $invitation) {
    $product = $invitation->product_id;

    $person = get_user_by('id', $invitation->inviter_id);

    $last_invitations[] = [
        'ID'         => (int) $invitation->ID,
        'product'    => [
            'title' => get_the_title($product),
            'image' => get_post_thumbnail_id($product),
            'url'   => get_the_permalink($product),
        ],
        'invitation' => [
            'title' => $person->display_name,
            'url'   => site_url('profile/' . $person->ID),
        ],
    ];
}

$collections      = $wpdb->get_results($wpdb->prepare("SELECT * FROM collections WHERE active LIKE 1 AND user_id LIKE $user_id ORDER BY likes_count DESC LIMIT 3"));
$collection_items = [];
foreach ($collections as $collection) {

    $items = [];

    foreach (unserialize($collection->items) as $product_id) {
        $items[] = [
            'id'    => $product_id,
            'image' => get_post_thumbnail_id($product_id),
            'url'   => get_the_permalink($product_id),
        ];
    }

    $collection_items[] = [
        'title' => $collection->title,
        'url'   => "/profile/" . (int) $collection->user_id,
        'count' => count(unserialize($collection->items)),
        'items' => $items,
    ];
}
?>
<svg class="hidden">
    <symbol id="requests-icon" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M11 1L13.09 7.26L20 8L14.5 12.74L16.18 19.02L11 15.77L5.82 19.02L7.5 12.74L2 8L8.91 7.26L11 1Z" fill="currentColor" />
    </symbol>
    <symbol id="cancel-icon" xmlns="http://www.w3.org/2000/svg" width="50" height="42" viewBox="0 0 50 42" fill="none">
        <g filter="url(#filter0_d_47817_19429)">
            <circle cx="27" cy="19" r="19" fill="#FFB900" />
        </g>
        <g filter="url(#filter1_f_47817_19429)">
            <path d="M27 8.6051C27.0004 7.16658 28.5672 6 30.5 6C32.4327 6.00004 33.9996 7.1666 34 8.6051V24.3949C33.9999 25.8336 32.4329 27 30.5 27C28.5671 27 27.0001 25.8336 27 24.3949V8.6051Z" fill="black" fill-opacity="0.3" />
            <rect x="27" y="29" width="7" height="7" rx="3.5" fill="black" fill-opacity="0.3" />
        </g>
        <path d="M30.0852 6.69105C29.8406 4.3214 26.6605 2.95705 25.1928 3.24428C23.5369 3.24428 24.1835 4.77384 24.1835 6.23206L24.184 21.2971C24.184 22.7553 21.8975 24.263 26.1691 24.3789C27.6869 24.3348 30.2749 23.4991 30.2749 21.1662L30.0852 6.69105Z" fill="#C6C6C6" />
        <g filter="url(#filter2_i_47817_19429)">
            <path d="M22.3279 5.73362C22.3282 4.27563 23.6704 3.09326 25.3261 3.09326C26.9818 3.0933 28.3241 4.27565 28.3244 5.73362V21.7371C28.3243 23.1952 26.9819 24.3774 25.3261 24.3774C23.6703 24.3774 22.328 23.1952 22.3279 21.7371V5.73362Z" fill="url(#paint0_linear_47817_19429)" />
        </g>
        <g filter="url(#filter3_i_47817_19429)">
            <rect x="22.1689" y="26" width="8" height="8" rx="4" fill="url(#paint1_linear_47817_19429)" />
        </g>
        <defs>
            <filter id="filter0_d_47817_19429" x="0" y="0" width="70" height="72" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                <feOffset dx="8" dy="18" />
                <feGaussianBlur stdDeviation="8" />
                <feComposite in2="hardAlpha" operator="out" />
                <feColorMatrix type="matrix" values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0" />
                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_47817_19429" />
                <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_47817_19429" result="shape" />
            </filter>
            <filter id="filter1_f_47817_19429" x="24" y="3" width="13" height="36" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                <feGaussianBlur stdDeviation="1.5" result="effect1_foregroundBlur_47817_19429" />
            </filter>
            <filter id="filter2_i_47817_19429" x="22.3281" y="2.09375" width="5.99609" height="22.2832" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                <feOffset dy="-1" />
                <feGaussianBlur stdDeviation="0.5" />
                <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
                <feColorMatrix type="matrix" values="0 0 0 0 0.776471 0 0 0 0 0.776471 0 0 0 0 0.776471 0 0 0 1 0" />
                <feBlend mode="normal" in2="shape" result="effect1_innerShadow_47817_19429" />
            </filter>
            <filter id="filter3_i_47817_19429" x="22.1689" y="25" width="8" height="9" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                <feOffset dy="-1" />
                <feGaussianBlur stdDeviation="0.5" />
                <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
                <feColorMatrix type="matrix" values="0 0 0 0 0.776471 0 0 0 0 0.776471 0 0 0 0 0.776471 0 0 0 1 0" />
                <feBlend mode="normal" in2="shape" result="effect1_innerShadow_47817_19429" />
            </filter>
            <linearGradient id="paint0_linear_47817_19429" x1="29.2868" y1="4.41326" x2="26.4513" y2="4.30585" gradientUnits="userSpaceOnUse">
                <stop stop-color="#C6C6C6" />
                <stop offset="1" stop-color="white" />
            </linearGradient>
            <linearGradient id="paint1_linear_47817_19429" x1="30.1689" y1="29.5" x2="27.3864" y2="27.3689" gradientUnits="userSpaceOnUse">
                <stop stop-color="#C6C6C6" />
                <stop offset="1" stop-color="white" />
            </linearGradient>
        </defs>
    </symbol>
</svg>
<div class="lg:col-span-8 2xl:col-span-9">
    <section class="border-[#E8EDF1] lg:h-full lg:rounded-3xl lg:border lg:p-8">
        <div class="flex flex-col max-lg:gap-y-8 lg:flex-row lg:gap-x-6 lg:items-start">
            <div class="grid shrink-0 grid-cols-2 max-lg:gap-x-5 lg:order-1 lg:w-5/12 lg:grid-cols-1 lg:gap-y-6 items-start">
                <div class="rounded-xlh bg-[#FED4B8] p-4 lg:flex lg:items-center lg:justify-between lg:gap-x-12 lg:px-5 lg:py-6">
                    <div class="font-bold lg:text-lg flex items-center gap-3">موجودی کیف پول
                        <span class="help" data-help="مبلغ موجود در حساب کاربری که هم امکان استفاده برای رزرو بازی و هم امکان استرداد به حساب بانکی رو داره."></span>
                    </div>
                    <div class="space-x-2.5 space-x-reverse">
                        <span class="text-h4 font-bold lg:text-[40px]">
                            <?php echo number_format($balance) ?>
                        </span>
                        <span class="text-sm font-bold lg:text-xl">تومان</span>
                    </div>
                </div>
                <div class="rounded-xlh bg-[#CBDEFE] p-4 lg:flex lg:items-center lg:justify-between lg:gap-x-12 lg:px-5 lg:py-6">
                    <div class="font-bold lg:text-lg flex items-center gap-3">
                        اعتبار تخفیف
                        <span class="help" data-help="مقدار تخفیفی که با استفاده از اون امکان رزرو بازی فراهمه و امکان نقد شدن یا انتقال به کیف پول نداره."></span>
                    </div>
                    <div class="space-x-2.5 space-x-reverse">
                        <span class="text-h4 font-bold lg:text-[40px]"><?php echo number_format($coupon_credit) ?></span>
                        <span class="text-sm font-bold lg:text-xl">تومان</span>
                    </div>
                </div>
            </div>

            <div class="lg:w-7/12 flex flex-col">
                <div class="mb-6 flex items-center justify-between">
                    <div class="text-lg font-bold">سانس‌های پیش‌رو</div>
                    <a href="<?php echo get_permalink(wc_get_page_id('myaccount')); ?>orders" class="flex items-center gap-2 text-sm text-slate-350">
                        مشاهده لیست
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{1.5}" stroke-linecap="round" stroke-linejoin="round" class="mx-0">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke" />
                        </svg>
                    </a>
                </div>
                <?php if (! empty($in_progress_orders)) { ?>
                    <div class="space-y-4">
                        <?php foreach ($in_progress_orders as $order_data) { ?>
                            <div class="border-[#E8EDF1] lg:rounded-3xl lg:border lg:p-4.5 lg:shadow-13 max-lg:border-t border-t-2 pt-4">
                                <div class="flex justify-between">
                                    <div class="flex gap-x-4">
                                        <div class="max-lg:hidden cursor-pointer" onclick="window.location.href='<?php echo $order_data['product_url']; ?>'">
                                            <?php echo wp_get_attachment_image($order_data['image'], 'full', false, [
                                                'class' => 'h-[100px] w-[80px] rounded-lg object-cover',
                                            ]) ?>
                                        </div>
                                        <div class="flex lg:h-full flex-col lg:justify-between max-lg:gap-y-2 lg:gap-y-5">
                                            <div class="flex gap-x-1 cursor-pointer" onclick="window.location.href='<?php echo $order_data['product_url']; ?>'">
                                                <span class="font-bold text-[#889BAD]">اتاق فرار</span>
                                                <h3 class="font-bold"><?php echo $order_data['product_title'] ?></h3>
                                            </div>

                                            <div class="max-lg:flex max-lg:items-center max-lg:gap-x-6">
                                                <div class="space-x-2.5 space-x-reverse leading-normal lg:hidden">
                                                    <bdo dir="ltr"><?php echo jdate('Y.m.d', $order_data['purchase_time']); ?></bdo>
                                                    <bdo dir="ltr"><?php echo jdate('H:i', $order_data['purchase_time']); ?></bdo>
                                                </div>
                                                <div class="text-sm font-bold text-[#889BAD] max-lg:hidden">
                                                    تعداد بلیت
                                                </div>
                                                <div class="space-x-px space-x-reverse">
                                                    <span><?php echo $order_data['tickets_count'] ?></span>
                                                    <span>بلیت</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col lg:justify-between max-lg:gap-y-1 lg:flex-row lg:gap-x-13">
                                        <div class="font-bold leading-normal text-[#049654] max-lg:mr-auto max-lg:w-fit lg:hidden max-lg:p-2.5 max-lg:text-center" style="color: <?php echo $order_data['color'] ?>">
                                            <?php echo $order_data['status'] ?>
                                        </div>
                                        <div class="flex flex-col lg:justify-end">
                                            <div class="text-sm font-bold text-[#889BAD] max-lg:hidden">
                                                زمان رزرو شده
                                            </div>
                                            <div class="space-x-2.5 space-x-reverse leading-normal max-lg:hidden">
                                                <bdo dir="ltr"><?php echo jdate('Y.m.d', $order_data['purchase_time']); ?></bdo>
                                                <bdo dir="ltr"><?php echo jdate('H:i', $order_data['purchase_time']); ?></bdo>
                                            </div>

                                        </div>

                                        <div class="flex flex-col justify-between">
                                            <div class="flex">
                                                <?php
                                                $button_text = '';
                                                $button_class = '';
                                                $modal_id = '';

                                                switch ($order_data['cancellation_status']) {
                                                    case 'pending':
                                                        $button_text = 'در انتظار تایید ×';
                                                        $button_class = 'bg-[#EDF2F5] text-[#1447E6] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                                                        $modal_id = 'pending-cancel-modal-' . $order_data['order_id'];
                                                        break;
                                                    case 'approved':
                                                        $button_text = 'لغو شد ×';
                                                        $button_class = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                                                        $modal_id = 'approved-cancel-modal-' . $order_data['order_id'];
                                                        break;
                                                    case 'rejected':
                                                        $button_text = 'امکان لغو ندارد ×';
                                                        $button_class = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                                                        $modal_id = 'rejected-cancel-modal-' . $order_data['order_id'];
                                                        break;
                                                    case 'cancelled':
                                                        $button_text = 'امکان لغو ندارد ×';
                                                        $button_class = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                                                        $modal_id = 'cancelled-cancel-modal-' . $order_data['order_id'];
                                                        break;
                                                    case 'expired':
                                                        $button_text = 'امکان لغو ندارد ×';
                                                        $button_class = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                                                        $modal_id = 'expired-cancel-modal-' . $order_data['order_id'];
                                                        break;
                                                    case 'not_allowed':
                                                        $button_text = 'امکان لغو ندارد ×';
                                                        $button_class = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                                                        $modal_id = 'not-allowed-modal-' . $order_data['order_id'];
                                                        break;
                                                    default:
                                                        $button_text = 'لغو سانس ×';
                                                        $button_class = 'bg-[#EDF2F5] text-[#F21543] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                                                        $modal_id = 'cancel-modal-' . $order_data['order_id'];
                                                }
                                                ?>
                                                <button
                                                    class="<?php echo $button_class; ?>"
                                                    onclick="openModal('<?php echo $modal_id; ?>')">
                                                    <?php echo $button_text; ?>
                                                </button>
                                            </div>
                                            <div class="max-lg:hidden">
                                                <div class="text-sm font-bold text-[#889BAD] max-lg:hidden">
                                                    وضعیت
                                                </div>
                                                <div class="font-bold leading-normal text-[#049654] max-lg:mr-auto max-lg:w-fit max-lg:rounded-lg max-lg:bg-[#E6F4EE] max-lg:p-2.5 max-lg:text-center" style="color: <?php echo $order_data['color'] ?>">
                                                    <?php echo $order_data['status'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="border-[#E8EDF1] lg:rounded-3xl lg:border lg:p-4.5 lg:shadow-13 grow flex flex-col justify-center items-center">
                        <div class="text-lg text-slate-350 flex flex-col justify-center items-center gap-5">
                            شما هنوز بازی ای رزرو نکرده اید!
                            <a href="<?php echo site_url(); ?>" class="w-fit flex gap-1 items-center text-primaryColor">
                                مشاهده بازی ها
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
                                    <rect y="0.5" width="16" height="16" rx="4" fill="#FD7013" />
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.60389 7.87608C3.44371 7.87614 3.29011 7.94201 3.17686 8.05921C3.06362 8.17642 3 8.33536 3 8.50109L3.00027 10.2688C3.00305 10.4327 3.06792 10.5889 3.18091 10.7037C3.29389 10.8186 3.44596 10.8829 3.60435 10.8829C3.76274 10.8829 3.91479 10.8185 4.02774 10.7036C4.14069 10.5887 4.20551 10.4325 4.20824 10.2687L4.20806 9.12579L5.13582 9.12564L5.136 10.2685C5.13877 10.4324 5.20364 10.5885 5.31663 10.7034C5.42962 10.8182 5.58168 10.8826 5.74007 10.8825C5.89847 10.8825 6.05051 10.8181 6.16346 10.7032C6.27642 10.5883 6.34123 10.4322 6.34396 10.2683L6.34379 9.12544L7.24421 9.12529C7.39131 9.71444 7.74103 10.2279 8.22783 10.5693C8.71463 10.9108 9.30508 11.0568 9.8885 10.98C10.4719 10.9033 11.0083 10.609 11.397 10.1524C11.7857 9.69573 12.0001 9.10809 12 8.4996C11.9999 7.89111 11.7853 7.30354 11.3965 6.84704C11.0076 6.39054 10.4712 6.09644 9.88774 6.01988C9.30429 5.94331 8.71389 6.08954 8.22719 6.43114C7.7405 6.77275 7.39093 7.28628 7.24401 7.87548L3.60389 7.87608ZM10.4382 7.61598C10.2116 7.38159 9.90424 7.24995 9.58382 7.25C9.2634 7.25006 8.95612 7.3818 8.72958 7.61626C8.50305 7.85072 8.37581 8.16868 8.37586 8.5002C8.37591 8.83172 8.50325 9.14964 8.72986 9.38402C8.95647 9.6184 9.26378 9.75005 9.5842 9.75C9.90462 9.74994 10.2119 9.6182 10.4384 9.38374C10.665 9.14928 10.7922 8.83132 10.7922 8.4998C10.7921 8.16828 10.6648 7.85036 10.4382 7.61598Z" fill="white" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>

        </div>
        <div class="mt-10 flex flex-col max-lg:gap-y-8 lg:mt-7.5 lg:flex-row lg:gap-x-6 lg:items-start">
            <div class="max-lg:order-1 lg:w-7/12 flex flex-col">
                <div class="mb-6 text-xl font-bold">اطلاعیه‌ها</div>
                <div class="space-y-5 rounded-3xl border border-[#E8EDF1] bg-[#F2F6FA] p-4 shadow-13 lg:p-6 grow">
                    <?php if ( $show_credit_notification ) : ?>
                    <div id="ez-credit-notification-card" role="button" tabindex="0" class="ez-credit-notification-card notification-card cursor-pointer relative flex gap-x-2 rounded-xlh border-2 border-red-500 bg-red-50 p-3 shadow-13 font-bold lg:items-center lg:p-4.5 ring-2 ring-red-200">
                        <?php if ( $credit_notification_is_new ) : ?><span id="ez-credit-notification-dot" class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-red-500 shadow-md"></span><?php endif; ?>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-500/20 max-lg:mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="lg:flex lg:w-full lg:justify-between lg:gap-x-1">
                            <div>
                                <div class="leading-normal text-16 text-red-800">اطلاعیه مهم: درگاه پرداخت اعتباری (اقساطی)</div>
                                <div class="font-semibold text-red-700 text-12 line-clamp-1">برای مطالعه و درخواست غیرفعالسازی روی این اطلاعیه کلیک کنید.</div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php foreach ($notifications as $index => $notification) {
                        $color = match ($notification['type']) {
                            "collection" => "#F21543",
                            "reserve" => "#FD7013",
                            "ticket" => "#BF9A00",
                            default => "#3F7FF5",
                        }; ?>
                        <div
                            data-id="<?php echo esc_html($notification['id']) ?>"
                            data-color="<?php echo esc_html($color); ?>"
                            data-title="<?php echo esc_html($notification['title']); ?>"
                            data-content="<?php echo $notification['description']; ?>"
                            data-date="<?php echo jdate("Y.m.d", $notification['date']); ?>"
                            class="notification-card cursor-pointer relative flex gap-x-2 rounded-xlh border bg-white p-3 shadow-13 font-bold border-[#E8EDF1] lg:items-center lg:p-4.5 <?php echo $notification['status'] == 0 ? "before:absolute before:right-2 before:top-2 before:h-2.5 before:w-2.5 before:rounded-full before:bg-primary-500 before:shadow-3 before:content-['']" : ''; ?>">

                            <div class="notification-icon">
                                <?php switch ($notification['type']):
                                    case "collection": ?>
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#F215431A] max-lg:mt-1 lg:shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                                <path d="M8.99585 17.0707L6.22755 19.1953C6.03012 19.355 5.79431 19.4561 5.54546 19.4877C5.29662 19.5193 5.04413 19.4803 4.81512 19.3749C4.5861 19.2694 4.38922 19.1015 4.24561 18.8892C4.102 18.6769 4.01709 18.4281 4 18.1697V7.07372C4.01436 6.66486 4.10656 6.26292 4.27132 5.89089C4.43608 5.51885 4.67018 5.184 4.96023 4.90546C5.25029 4.62693 5.59063 4.41017 5.96179 4.26757C6.33296 4.12497 6.72769 4.05932 7.12342 4.07438H12.5793C13.3782 4.04508 14.1558 4.34443 14.7414 4.90673C15.3269 5.46903 15.6727 6.24836 15.7027 7.07372V18.1714C15.686 18.43 15.6013 18.679 15.4578 18.8916C15.3142 19.1042 15.1172 19.2722 14.888 19.3777C14.6588 19.4831 14.4061 19.5219 14.1572 19.49C13.9082 19.458 13.6724 19.3564 13.4752 19.1962L10.7069 17.0715C10.4584 16.8844 10.1588 16.7837 9.85136 16.7837C9.54389 16.7837 9.24435 16.8836 8.99585 17.0707Z" stroke="#F21543" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M7.34375 2.3399C7.63716 2.05913 7.98144 1.84062 8.3569 1.69687C8.73236 1.55312 9.13166 1.48695 9.53197 1.50213H15.051C15.8592 1.4726 16.6457 1.77435 17.2381 2.34118C17.8304 2.908 18.1802 3.6936 18.2106 4.5256V10.1191V15.7125C18.1937 15.9733 18.108 16.2243 17.9628 16.4386C17.8176 16.6529 17.6183 16.8223 17.3864 16.9286" stroke="#F21543" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    <?php break;
                                    case "reserve": ?>
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#FD70131A] max-lg:mt-1 lg:shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8075 17.1276L12.8129 15.375C12.8129 15.1424 12.908 14.9192 13.0772 14.7547C13.2464 14.5902 13.4758 14.4978 13.7151 14.4978C13.9544 14.4978 14.1838 14.5902 14.353 14.7547C14.5222 14.9192 14.6173 15.1424 14.6173 15.375V17.1049C14.6173 17.5257 14.6173 17.7366 14.7559 17.8661C14.8954 17.9947 15.1068 17.986 15.5325 17.9685C17.2091 17.8994 18.2395 17.6797 18.9649 16.9745C19.6938 16.2692 19.9197 15.2674 19.9908 13.6346C20.0043 13.3109 20.0115 13.1481 19.9494 13.0405C19.8864 12.9329 19.6389 12.7981 19.1422 12.5278C18.8623 12.3761 18.6292 12.1545 18.467 11.8859C18.3047 11.6173 18.2192 11.3115 18.2192 11C18.2192 10.6885 18.3047 10.3827 18.467 10.1141C18.6292 9.84551 18.8623 9.62393 19.1422 9.47225C19.6389 9.20275 19.8873 9.06712 19.9494 8.9595C20.0115 8.85187 20.0043 8.69 19.9899 8.36538C19.9197 6.73263 19.6929 5.73162 18.9649 5.0255C18.1756 4.259 17.0255 4.06563 15.0754 4.01663C15.0156 4.01511 14.9562 4.02523 14.9006 4.04641C14.845 4.06758 14.7943 4.09937 14.7515 4.1399C14.7087 4.18044 14.6747 4.22889 14.6515 4.28241C14.6283 4.33593 14.6164 4.39342 14.6164 4.4515V6.625C14.6164 6.85764 14.5213 7.08076 14.3521 7.24527C14.1829 7.40977 13.9535 7.50219 13.7142 7.50219C13.4749 7.50219 13.2455 7.40977 13.0763 7.24527C12.9071 7.08076 12.812 6.85764 12.812 6.625L12.8057 4.43663C12.8055 4.32074 12.758 4.20969 12.6736 4.12783C12.5892 4.04597 12.4749 4 12.3558 4H9.19518C5.79343 4 4.09255 4 3.03513 5.0255C2.30619 5.73075 2.0803 6.73263 2.00921 8.36538C1.99571 8.68913 1.98851 8.85187 2.0506 8.9595C2.1136 9.06712 2.36108 9.20275 2.85784 9.47225C3.13767 9.62393 3.37075 9.84551 3.53301 10.1141C3.69528 10.3827 3.78083 10.6885 3.78083 11C3.78083 11.3115 3.69528 11.6173 3.53301 11.8859C3.37075 12.1545 3.13767 12.3761 2.85784 12.5278C2.36108 12.7981 2.1127 12.9329 2.0506 13.0405C1.98851 13.1481 1.99571 13.31 2.01011 13.6338C2.0803 15.2674 2.30708 16.2692 3.03513 16.9745C4.09255 18 5.79343 18 9.19608 18H11.9049C12.3297 18 12.5411 18 12.6734 17.8723C12.8057 17.7445 12.8066 17.5397 12.8075 17.1276ZM14.6164 11.875V10.125C14.6164 9.89236 14.5213 9.66924 14.3521 9.50473C14.1829 9.34023 13.9535 9.24781 13.7142 9.24781C13.4749 9.24781 13.2455 9.34023 13.0763 9.50473C12.9071 9.66924 12.812 9.89236 12.812 10.125V11.875C12.812 12.1078 12.9071 12.331 13.0764 12.4956C13.2457 12.6602 13.4753 12.7526 13.7147 12.7526C13.954 12.7526 14.1836 12.6602 14.3529 12.4956C14.5222 12.331 14.6164 12.1078 14.6164 11.875Z" stroke="#FD7013" stroke-width="1.5" />
                                            </svg>
                                        </div>
                                    <?php break;
                                    case "ticket": ?>
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#EFC1011A] max-lg:mt-1 lg:shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3 11.5133C3 7.29822 6.36855 3.5 11.0173 3.5C15.562 3.5 19.0026 7.22621 19.0026 11.4893C19.0026 16.4341 14.9699 19.5026 11.0013 19.5026C9.68909 19.5026 8.23285 19.1505 7.06466 18.4608C6.6566 18.2128 6.31254 18.0288 5.87247 18.1728L4.2562 18.6529C3.84814 18.7809 3.48008 18.4608 3.6001 18.0288L4.13619 16.2333C4.2242 15.9852 4.2082 15.7204 4.08018 15.5116C3.39206 14.2457 3 12.8591 3 11.5133Z" stroke="#BF9A00" stroke-width="1.5" />
                                                <path d="M10.986 12.5477C10.4179 12.5397 9.96179 12.0829 9.96179 11.514C9.96179 10.9531 10.4259 10.4882 10.986 10.4962C11.251 10.5067 11.5017 10.6193 11.6855 10.8105C11.8693 11.0018 11.972 11.2567 11.972 11.522C11.972 11.7872 11.8693 12.0422 11.6855 12.2334C11.5017 12.4246 11.251 12.5373 10.986 12.5477Z" fill="#BF9A00" />
                                                <path d="M14.6746 12.5477C14.1065 12.5477 13.6504 12.0829 13.6504 11.522C13.6504 10.9531 14.1065 10.4962 14.6746 10.4962C14.9396 10.5067 15.1903 10.6193 15.3741 10.8105C15.5579 11.0018 15.6606 11.2567 15.6606 11.522C15.6606 11.7872 15.5579 12.0422 15.3741 12.2334C15.1903 12.4246 14.9396 12.5373 14.6746 12.5477Z" fill="#BF9A00" />
                                                <path d="M6.27319 11.522C6.27319 12.0837 6.73727 12.5477 7.29736 12.5477C7.56861 12.5456 7.82814 12.4368 8.0198 12.2449C8.21146 12.0529 8.31985 11.7932 8.32153 11.522C8.32153 10.9531 7.86545 10.4962 7.29736 10.4962C6.72927 10.4962 6.27319 10.9531 6.27319 11.522Z" fill="#BF9A00" />
                                            </svg>
                                        </div>
                                    <?php break;
                                    default: ?>
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#5091FB1A] max-lg:mt-1 lg:shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M9.81062 16.7163L9.81091 16.716C11.0736 15.7694 12.5248 15.4906 13.874 15.2607C14.4653 15.1599 14.7688 15.1401 15.1006 15.1184C15.1741 15.1136 15.2489 15.1087 15.3286 15.1028C15.7543 15.0714 16.3913 15.0078 17.8767 14.7602C19.1135 14.5541 19.8104 13.9107 20.3144 13.3155C20.7893 12.7547 21.0983 12.0727 21.2067 11.3459C21.3152 10.6192 21.2188 9.87664 20.9284 9.20169C20.638 8.52675 20.1651 7.94618 19.5628 7.52526C18.9605 7.10434 18.2528 6.85974 17.5192 6.81897L17.5191 6.81897L14.5012 6.65097C12.906 6.56236 11.3546 6.09621 9.97475 5.29087L7.63132 3.92321C6.79669 3.43707 5.7508 4.03847 5.7508 5.00312V17.2621C5.7508 18.2922 6.92635 18.8803 7.75087 18.2621L9.81062 16.7163Z" stroke="#3F7FF5" stroke-width="1.5" />
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M18 15L16.355 20.0792C16.2759 20.3734 16.0985 20.6275 15.8557 20.7945C15.6128 20.9615 15.321 21.0302 15.0339 20.9878C14.7469 20.9454 14.4841 20.7948 14.2941 20.5638C14.104 20.3329 13.9996 20.0371 14 19.7312V15.3604L16.6374 15.2052C17.0961 15.176 17.5518 15.1074 18 15Z" stroke="#3F7FF5" stroke-width="1.5" />
                                                <path d="M14 15.5V7" stroke="#3F7FF5" stroke-width="1.5" />
                                                <path d="M3.75 11C3.75 11.9797 4.37611 12.8131 5.25 13.122V8.87803C4.37611 9.18691 3.75 10.0203 3.75 11Z" stroke="#3F7FF5" stroke-width="1.5" />
                                            </svg>
                                        </div>
                                <?php break;
                                endswitch; ?>
                            </div>

                            <div class="lg:flex lg:w-full lg:justify-between lg:gap-x-1">
                                <div>
                                    <div class="leading-normal text-16">
                                        <?php echo esc_html($notification['title']); ?>
                                    </div>
                                    <div class="font-semibold text-[#4E5C6D] text-12 notification-excerpt line-clamp-1">
                                        <?php echo $notification['description']; ?>
                                    </div>
                                </div>
                                <div>
                                    <bdo dir="ltr" class="font-semibold text-[#889BAD] text-14">
                                        <?php echo jdate("Y.m.d", $notification['date']); ?>
                                    </bdo>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="shrink-0 lg:w-5/12">
                <div class="max-lg:border-b max-lg:border-b-4 max-lg:border-t max-lg:border-t-4 max-lg:border-b-slate-120 max-lg:border-t-slate-120 max-lg:pb-6 max-lg:pt-6">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="text-xl font-bold">دعوت‌ها</div>
                        <?php if (count($last_invitations) > 0) { ?>
                            <a href="<?php echo get_permalink(wc_get_page_id('myaccount')); ?>invitation" class="flex items-center gap-2 text-sm text-slate-350">
                                مشاهده لیست
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{1.5}" stroke-linecap="round" stroke-linejoin="round" class="mx-0">
                                    <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke" />
                                </svg>
                            </a>
                        <?php } ?>
                    </div>
                    <div class="space-y-5">
                        <?php if (count($last_invitations) > 0) { ?>
                            <?php foreach ($last_invitations as $invitation) { ?>
                                <a href="<?php echo get_permalink(wc_get_page_id('myaccount')); ?>invitation" class="rounded-2xl border items-center border-slate-120 shadow-12 p-4 flex gap-4">
                                    <?php echo wp_get_attachment_image($invitation['product']['image'], 'full', false, [
                                        'class' => 'w-12 rounded',
                                    ]) ?>
                                    <div class="grow flex flex-col justify-between">
                                        <div class="font-bold flex justify-between">
                                            <span class="text-lg text-textColor">
                                                <?php echo $invitation['product']['title']; ?>
                                            </span>
                                            <span class="bg-gray-50 px-4 rounded">
                                                <span class="text-gray-600">توسط</span>
                                                <span class="text-blue"><?php echo $invitation['invitation']['title']; ?></span>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="border-[#E8EDF1] rounded-3xl border p-4 shadow-13 h-[300px] flex flex-col justify-center items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34" fill="none">
                                    <g clip-path="url(#clip0_7961_636)">
                                        <path d="M17.0017 1.88867C14.013 1.88867 11.0915 2.77492 8.60646 4.43535C6.12145 6.09578 4.18462 8.45582 3.0409 11.217C1.89717 13.9782 1.59792 17.0165 2.18099 19.9478C2.76405 22.8791 4.20325 25.5716 6.31657 27.685C8.4299 29.7983 11.1224 31.2375 14.0537 31.8205C16.985 32.4036 20.0233 32.1044 22.7845 30.9606C25.5457 29.8169 27.9057 27.8801 29.5662 25.3951C31.2266 22.9101 32.1129 19.9885 32.1129 16.9998C32.1129 12.9921 30.5208 9.1485 27.6869 6.31461C24.853 3.48073 21.0095 1.88867 17.0017 1.88867ZM17.0017 30.222C14.3866 30.222 11.8303 29.4465 9.65587 27.9937C7.48149 26.5408 5.78676 24.4758 4.786 22.0597C3.78524 19.6437 3.5234 16.9851 4.03358 14.4203C4.54376 11.8554 5.80306 9.49942 7.65222 7.65026C9.50138 5.8011 11.8574 4.5418 14.4222 4.03162C16.9871 3.52144 19.6456 3.78328 22.0617 4.78404C24.4777 5.7848 26.5427 7.47953 27.9956 9.65391C29.4485 11.8283 30.224 14.3847 30.224 16.9998C30.224 20.5065 28.8309 23.8697 26.3513 26.3493C23.8716 28.829 20.5085 30.222 17.0017 30.222Z" fill="#889BAD" />
                                        <path d="M23.7625 15.1871C24.7014 15.1871 25.4625 14.426 25.4625 13.4871C25.4625 12.5482 24.7014 11.7871 23.7625 11.7871C22.8236 11.7871 22.0625 12.5482 22.0625 13.4871C22.0625 14.426 22.8236 15.1871 23.7625 15.1871Z" fill="#889BAD" />
                                        <path d="M10.7742 15.1871C11.7131 15.1871 12.4742 14.426 12.4742 13.4871C12.4742 12.5482 11.7131 11.7871 10.7742 11.7871C9.83533 11.7871 9.07422 12.5482 9.07422 13.4871C9.07422 14.426 9.83533 15.1871 10.7742 15.1871Z" fill="#889BAD" />
                                        <path d="M17.1525 18.8887C15.7976 18.8889 14.4623 19.213 13.2581 19.834C12.0539 20.455 11.0156 21.3549 10.2297 22.4587C10.0844 22.6628 10.0262 22.9163 10.0678 23.1634C10.1094 23.4105 10.2475 23.6309 10.4517 23.7762C10.6558 23.9215 10.9093 23.9797 11.1564 23.9381C11.4034 23.8964 11.6239 23.7584 11.7692 23.5542C12.373 22.706 13.1687 22.0125 14.0914 21.53C15.0141 21.0475 16.0376 20.7898 17.0788 20.7779C18.1199 20.7659 19.1492 21 20.0827 21.4611C21.0162 21.9223 21.8276 22.5974 22.4508 23.4314C22.6011 23.6318 22.8248 23.7643 23.0728 23.7997C23.3208 23.8352 23.5727 23.7706 23.773 23.6203C23.9734 23.47 24.1059 23.2463 24.1413 22.9983C24.1768 22.7504 24.1122 22.4985 23.9619 22.2981C23.1702 21.2394 22.1425 20.38 20.9604 19.7882C19.7783 19.1963 18.4745 18.8883 17.1525 18.8887Z" fill="#889BAD" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_7961_636">
                                            <rect width="34" height="34" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span class="text-slate-350 text-lg">تاکنون دعوت نشده اید</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="mt-10 lg:mt-8">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="text-xl font-bold">کالکشن‌های من</div>
                        <?php if (count($collection_items) > 0) { ?>
                            <a href="<?php echo get_permalink(wc_get_page_id('myaccount')); ?>my-collections" class="flex items-center gap-2 text-sm text-slate-350">
                                مشاهده لیست
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{1.5}" stroke-linecap="round" stroke-linejoin="round" class="mx-0">
                                    <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke" />
                                </svg>
                            </a>
                        <?php } ?>
                    </div>
                    <div class="space-y-5">
                        <?php if (count($collection_items) > 0) { ?>
                            <?php foreach ($collection_items as $collection) { ?>
                                <div class="flex items-center justify-between rounded-3xl border border-[#E8EDF1] p-4 shadow-13 lg:bg-[#09192D]">
                                    <h3 class="lg:text-white"><?php echo $collection['title']; ?></h3>
                                    <div class="flex items-center justify-end gap-x-5">

                                        <?php foreach (array_slice($collection['items'], 0, 3) as $item) { ?>
                                            <div href="<?php echo $item['url']; ?>">
                                                <?php echo wp_get_attachment_image($item['image'], 'large', false, [
                                                    'class' => 'h-[40px] w-[30px] rounded object-cover lg:h-[50px] lg:w-[40px]',
                                                ]) ?>
                                            </div>
                                        <?php } ?>

                                        <?php for ($i = 0; $i < 4 - count($collection['items']); $i++) { ?>
                                            <div class="h-[40px] w-[30px] rounded object-cover lg:h-[50px] lg:w-[40px]"></div>
                                        <?php } ?>

                                        <?php if (count($collection['items']) > 3) { ?>
                                            <div href="<?php echo $collection['url'] ?>" class="relative">
                                                <bdo dir="ltr" class="absolute bottom-0 left-0 right-0 top-0 content-center rounded text-center text-white" style="background: #FD7013E0;">
                                                    +<?php echo count($collection['items']) - 3; ?>
                                                </bdo>
                                                <?php echo wp_get_attachment_image($collection['items'][3]['image'], 'large', false, [
                                                    'class' => 'h-[40px] w-[30px] rounded object-cover lg:h-[50px] lg:w-[40px]',
                                                ]) ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <a href="<?php echo get_permalink(wc_get_page_id('myaccount')); ?>my-collections" class="border-[#E8EDF1] rounded-3xl border p-4 shadow-13 h-[200px] flex justify-center items-center gap-2">
                                ایجاد لیست
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                    <rect width="18" height="18" rx="4" fill="#1ED982" />
                                    <g filter="url(#filter0_d_5322_2235)">
                                        <path d="M4.62707 9C4.62717 8.83739 4.69181 8.68146 4.80679 8.56648C4.92178 8.45149 5.07771 8.38685 5.24032 8.38675L8.38701 8.38617L8.38759 5.23948C8.38759 5.15894 8.40345 5.0792 8.43427 5.0048C8.46509 4.93039 8.51026 4.86279 8.56721 4.80584C8.62415 4.7489 8.69176 4.70372 8.76616 4.6729C8.84056 4.64209 8.92031 4.62622 9.00084 4.62622C9.08138 4.62622 9.16112 4.64209 9.23552 4.6729C9.30993 4.70372 9.37753 4.7489 9.43448 4.80584C9.49142 4.86279 9.5366 4.93039 9.56741 5.0048C9.59823 5.0792 9.6141 5.15894 9.6141 5.23948L9.61467 8.38617L12.7614 8.38675C12.924 8.38675 13.08 8.45136 13.195 8.56636C13.31 8.68137 13.3746 8.83735 13.3746 9C13.3746 9.16265 13.31 9.31863 13.195 9.43364C13.08 9.54864 12.924 9.61325 12.7614 9.61325L9.61467 9.61383L9.6141 12.7605C9.6141 12.9232 9.54949 13.0792 9.43448 13.1942C9.31947 13.3092 9.16349 13.3738 9.00084 13.3738C8.8382 13.3738 8.68221 13.3092 8.56721 13.1942C8.4522 13.0792 8.38759 12.9232 8.38759 12.7605L8.38701 9.61383L5.24032 9.61325C5.07771 9.61315 4.92178 9.54851 4.80679 9.43352C4.69181 9.31854 4.62717 9.16261 4.62707 9Z" fill="white" />
                                        <path d="M4.62707 9C4.62717 8.83739 4.69181 8.68146 4.80679 8.56648C4.92178 8.45149 5.07771 8.38685 5.24032 8.38675L8.38701 8.38617L8.38759 5.23948C8.38759 5.15894 8.40345 5.0792 8.43427 5.0048C8.46509 4.93039 8.51026 4.86279 8.56721 4.80584C8.62415 4.7489 8.69176 4.70372 8.76616 4.6729C8.84056 4.64209 8.92031 4.62622 9.00084 4.62622C9.08138 4.62622 9.16112 4.64209 9.23552 4.6729C9.30993 4.70372 9.37753 4.7489 9.43448 4.80584C9.49142 4.86279 9.5366 4.93039 9.56741 5.0048C9.59823 5.0792 9.6141 5.15894 9.6141 5.23948L9.61467 8.38617L12.7614 8.38675C12.924 8.38675 13.08 8.45136 13.195 8.56636C13.31 8.68137 13.3746 8.83735 13.3746 9C13.3746 9.16265 13.31 9.31863 13.195 9.43364C13.08 9.54864 12.924 9.61325 12.7614 9.61325L9.61467 9.61383L9.6141 12.7605C9.6141 12.9232 9.54949 13.0792 9.43448 13.1942C9.31947 13.3092 9.16349 13.3738 9.00084 13.3738C8.8382 13.3738 8.68221 13.3092 8.56721 13.1942C8.4522 13.0792 8.38759 12.9232 8.38759 12.7605L8.38701 9.61383L5.24032 9.61325C5.07771 9.61315 4.92178 9.54851 4.80679 9.43352C4.69181 9.31854 4.62717 9.16261 4.62707 9Z" stroke="white" />
                                    </g>
                                    <defs>
                                        <filter id="filter0_d_5322_2235" x="3.12695" y="4.12598" width="11.748" height="11.748" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                            <feOffset dy="1" />
                                            <feGaussianBlur stdDeviation="0.5" />
                                            <feComposite in2="hardAlpha" operator="out" />
                                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5322_2235" />
                                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5322_2235" result="shape" />
                                        </filter>
                                    </defs>
                                </svg>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- Modals for cancellation states -->
<?php if (! empty($in_progress_orders)) { ?>
    <?php foreach ($in_progress_orders as $order_data) { ?>
        <!-- Cancel Modal -->
        <div id="cancel-modal-<?php echo $order_data['order_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="flex flex-col border rounded-xl w-[234px] p-8 bg-white">
                <img src="<?= Theme_ASSET_URL ?>images/cross-sign.avif" alt="" srcset="" class="w-10 h-10 mx-auto">

                <p class="text-base font-bold text-center mt-4 mb-6">
                    آیا از لغو رزرو سانس مطمئن هستید؟
                </p>

                <div class="flex gap-2">
                    <svg class="shrink-0" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path opacity="0.4" d="M8.35815 3.04953C9.07148 1.75953 10.9256 1.75953 11.639 3.04953L18.094 14.717C18.7856 15.967 17.8815 17.5004 16.4531 17.5004H3.54482C2.11565 17.5004 1.21148 15.967 1.90315 14.717L8.35815 3.04953Z" fill="#EFC101" />
                        <path d="M10.776 13.8404C10.8162 13.9444 10.8351 14.0555 10.8316 14.167C10.8247 14.3831 10.7341 14.5881 10.5788 14.7386C10.4235 14.889 10.2157 14.9732 9.99949 14.9732C9.78326 14.9732 9.57551 14.889 9.42021 14.7386C9.26491 14.5881 9.17425 14.3831 9.16741 14.167C9.16388 14.0555 9.18279 13.9444 9.22302 13.8404C9.26325 13.7364 9.32397 13.6415 9.40159 13.5614C9.4792 13.4813 9.57212 13.4176 9.67483 13.3741C9.77754 13.3306 9.88795 13.3081 9.99949 13.3081C10.111 13.3081 10.2214 13.3306 10.3241 13.3741C10.4269 13.4176 10.5198 13.4813 10.5974 13.5614C10.675 13.6415 10.7357 13.7364 10.776 13.8404Z" fill="#09192D" />
                        <path d="M10.3908 7.22305C10.5138 7.32298 10.5941 7.46587 10.6157 7.62282L10.6216 7.70699L10.6249 11.4587C10.6251 11.6171 10.5651 11.7697 10.457 11.8855C10.349 12.0014 10.201 12.0719 10.0429 12.0828C9.88488 12.0937 9.72858 12.0442 9.60565 11.9443C9.48273 11.8443 9.40234 11.7014 9.38074 11.5445L9.37491 11.4595L9.37158 7.70865C9.37141 7.55023 9.43142 7.39765 9.53946 7.28178C9.6475 7.16591 9.79551 7.09539 9.95356 7.08449C10.1116 7.07359 10.2679 7.12311 10.3908 7.22305Z" fill="#09192D" />
                    </svg>
                    <p class="text-xs font-bold leading-[20px] text-[#BF9A00]">
                        پلیر عزیز، در صورت لغو کمتر از ۲۴ ساعت پیش از سانس، لغو تنها در صورت تأیید مجموعه انجام خواهد شد.
                    </p>
                </div>

                <div class="flex justify-between mt-6">
                    <button
                        onclick="confirmCancellation(<?php echo $order_data['order_id']; ?>)"
                        class="bg-[#EDF2F5] px-7 rounded-lg border border-[#D5DCE1] w-[78px] h-[33px] text-base font-bold">
                        بله
                    </button>

                    <button
                        onclick="closeModal('cancel-modal-<?php echo $order_data['order_id']; ?>')"
                        class="bg-[#EDF2F5] rounded-lg border border-[#D5DCE1] w-[78px] h-[33px] text-base font-bold text-[#90A1B9]">
                        بستن
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Cancel Modal -->
        <div id="pending-cancel-modal-<?php echo $order_data['order_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="flex flex-col border rounded-xl max-w-[234px] p-6 bg-white">
                <img src="<?= Theme_ASSET_URL ?>images/exclamation-mark.avif" alt="" srcset="" class="w-10 h-10 mx-auto">
                <p class="text-base font-bold text-center my-3">
                    آیا مایلید رزروتان را حفظ کنید و لغو نکنید؟
                </p>

                <div class="flex justify-between mt-6">
                    <button onclick="cancelCancellationRequest(<?php echo $order_data['order_id']; ?>)" class="bg-[#EDF2F5] text-[#4E5C6D] rounded-lg border border-[#D5DCE1] w-[121px] h-[33px] text-base font-bold">
                        بله، رزرو را نگه‌دار!
                    </button>

                    <button onclick="closeModal('pending-cancel-modal-<?php echo $order_data['order_id']; ?>')" class="bg-[#EDF2F5] rounded-lg border border-[#D5DCE1] w-[53px] h-[33px] text-base font-bold text-[#90A1B9]">
                        خیر
                    </button>
                </div>
            </div>
        </div>

        <!-- Not Allowed Modal -->
        <div id="not-allowed-modal-<?php echo $order_data['order_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="flex flex-col border rounded-xl max-w-[234px] p-8 bg-white">
                <img src="<?= Theme_ASSET_URL ?>images/exclamation-mark.avif" alt="" srcset="" class="w-10 h-10 mx-auto">
                <p class="text-base font-bold text-center my-3">امکان لغو ندارد</p>
                <p class="text-sm text-gray-600 text-center mb-6">به دلیل نزدیک بودن زمان اجرای بازی (کمتر از 3 ساعت)، امکان لغو سانس وجود ندارد.</p>
                <div class="flex justify-center">
                    <button onclick="closeModal('not-allowed-modal-<?php echo $order_data['order_id']; ?>')" class="bg-[#EDF2F5] rounded-lg border border-[#D5DCE1] w-[78px] h-[33px] text-base font-bold text-[#90A1B9]">
                        متوجه شدم
                    </button>
                </div>
            </div>
        </div>

        <!-- Approved Cancel Modal -->
        <div id="approved-cancel-modal-<?php echo $order_data['order_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="flex flex-col border rounded-xl max-w-[234px] p-8 bg-white">
                <img src="<?= Theme_ASSET_URL ?>images/exclamation-mark.avif" alt="" srcset="" class="w-10 h-10 mx-auto">
                <p class="text-base font-bold text-center my-3">لغو شد</p>
                <p class="text-sm text-gray-600 text-center mb-6">این سانس لغو شده است.</p>
                <div class="flex justify-center">
                    <button onclick="closeModal('approved-cancel-modal-<?php echo $order_data['order_id']; ?>')" class="bg-[#EDF2F5] rounded-lg border border-[#D5DCE1] w-[78px] h-[33px] text-base font-bold text-[#90A1B9]">
                        متوجه شدم
                    </button>
                </div>
            </div>
        </div>

        <!-- Rejected Cancel Modal -->
        <div id="rejected-cancel-modal-<?php echo $order_data['order_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="flex flex-col border rounded-xl max-w-[234px] p-8 bg-white">
                <img src="<?= Theme_ASSET_URL ?>images/exclamation-mark.avif" alt="" srcset="" class="w-10 h-10 mx-auto">
                <p class="text-base font-bold text-center my-3">امکان لغو ندارد</p>
                <p class="text-sm text-gray-600 text-center">درخواست لغو شما برای این سانس رد شد.</p>
                <div class="flex justify-center">
                    <button onclick="closeModal('rejected-cancel-modal-<?php echo $order_data['order_id']; ?>')" class="bg-[#EDF2F5] rounded-lg border border-[#D5DCE1] w-[78px] h-[33px] text-base font-bold text-[#90A1B9]">
                        متوجه شدم
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancelled Cancel Modal -->
        <div id="cancelled-cancel-modal-<?php echo $order_data['order_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="flex flex-col border rounded-xl max-w-[234px] p-8 bg-white">
                <img src="<?= Theme_ASSET_URL ?>images/exclamation-mark.avif" alt="" srcset="" class="w-10 h-10 mx-auto">
                <p class="text-base font-bold text-center my-3">امکان لغو ندارد</p>
                <p class="text-sm text-gray-600 text-center mb-6">شما یکبار برای این سانس درخواست لغو ثبت کردید و درخواست خود را لغو کردید، مجدد امکان ثبت درخواست ندارید.</p>
                <div class="flex justify-center">
                    <button onclick="closeModal('cancelled-cancel-modal-<?php echo $order_data['order_id']; ?>')" class="bg-[#EDF2F5] rounded-lg border border-[#D5DCE1] w-[78px] h-[33px] text-base font-bold text-[#90A1B9]">
                        متوجه شدم
                    </button>
                </div>
            </div>
        </div>

        <!-- Expired Cancel Modal -->
        <div id="expired-cancel-modal-<?php echo $order_data['order_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="flex flex-col border rounded-xl max-w-[234px] p-8 bg-white">
                <img src="<?= Theme_ASSET_URL ?>images/exclamation-mark.avif" alt="" srcset="" class="w-10 h-10 mx-auto">
                <p class="text-base font-bold text-center my-3">امکان لغو ندارد</p>
                <p class="text-sm text-gray-600 text-center">کاربر گرامی این سانس توسط مجموعه لغو نشد.</p>
                <div class="flex justify-center">
                    <button onclick="closeModal('expired-cancel-modal-<?php echo $order_data['order_id']; ?>')" class="bg-[#EDF2F5] rounded-lg border border-[#D5DCE1] w-[78px] h-[33px] text-base font-bold text-[#90A1B9]">
                        متوجه شدم
                    </button>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function confirmCancellation(orderId) {
        // Show loading state with spinner
        const button = document.querySelector(`button[onclick="confirmCancellation(${orderId})"]`);
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        button.disabled = true;

        // Make AJAX request
        jQuery.ajax({
            type: 'POST',
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                'action': 'team_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                'callback': 'cancellation_actions',
                'function': 'create_cancellation_request',
                'order_id': orderId,
                'requester_type': 'customer'
            },
            success: function(response) {

                // Parse response if it's a string
                let data = response;
                if (typeof response === 'string') {
                    try {
                        data = JSON.parse(response);
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        data = {
                            success: false,
                            data: 'خطا در پردازش پاسخ سرور'
                        };
                    }
                }

                // Check if request was successful
                if (data.success === true) {
                    // Close the modal
                    closeModal('cancel-modal-' + orderId);

                    // Update button status to pending
                    updateButtonStatus(orderId, 'pending');

                    // Show success toast with server message
                    const message = data.data || 'درخواست لغو سانس با موفقیت ثبت شد و در انتظار تایید است.';
                    showToast(message, 'success');
                } else {
                    // Show error toast with server message
                    const message = data.data || 'خطا در ثبت درخواست لغو. لطفاً دوباره تلاش کنید.';
                    showToast(message, 'error');

                    // Restore button state
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showToast('خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.', 'error');

                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    }

    function showToast(message, type = 'success') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'fixed bottom-4 left-4 z-50 space-y-2';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'success' ?
            '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' :
            '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';

        toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 max-w-sm transform transition-all duration-300 translate-x-[-100%]`;
        toast.innerHTML = `${icon}<span class="text-sm font-medium">${message}</span>`;

        // Add to container
        toastContainer.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-[-100%]');
        }, 100);

        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-[-100%]');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 4000);
    }

    function updateButtonStatus(orderId, status) {
        const button = document.querySelector(`button[onclick*="cancel-modal-${orderId}"]`);
        if (!button) return;

        let buttonText = '';
        let buttonClass = '';
        let modalId = '';

        switch (status) {
            case 'pending':
                buttonText = 'در انتظار تایید ×';
                buttonClass = 'bg-[#EDF2F5] text-[#1447E6] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                modalId = 'pending-cancel-modal-' + orderId;
                break;
            case 'approved':
                buttonText = 'لغو شد ×';
                buttonClass = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                modalId = 'approved-cancel-modal-' + orderId;
                break;
            case 'rejected':
                buttonText = 'امکان لغو ندارد ×';
                buttonClass = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                modalId = 'rejected-cancel-modal-' + orderId;
                break;
            case 'cancelled':
                buttonText = 'امکان لغو ندارد ×';
                buttonClass = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                modalId = 'cancelled-cancel-modal-' + orderId;
                break;
            case 'expired':
                buttonText = 'امکان لغو ندارد ×';
                buttonClass = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                modalId = 'expired-cancel-modal-' + orderId;
                break;
            case 'not_allowed':
                buttonText = 'امکان لغو ندارد ×';
                buttonClass = 'bg-[#EDF2F5] text-[#D08700] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                modalId = 'not-allowed-modal-' + orderId;
                break;
            default:
                buttonText = 'لغو سانس ×';
                buttonClass = 'bg-[#EDF2F5] text-[#F21543] h-[18px] rounded px-2 text-xs font-bold flex items-center justify-center';
                modalId = 'cancel-modal-' + orderId;
        }

        // Update button
        button.innerHTML = buttonText;
        button.className = buttonClass;
        button.setAttribute('onclick', `openModal('${modalId}')`);
    }

    function cancelCancellationRequest(orderId) {
        // Show loading state with spinner
        const button = document.querySelector(`button[onclick="cancelCancellationRequest(${orderId})"]`);
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        button.disabled = true;

        // Make AJAX request to cancel the cancellation request
        jQuery.ajax({
            type: 'POST',
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                'action': 'team_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                'callback': 'cancellation_actions',
                'function': 'update_cancellation_status',
                'order_id': orderId,
                'new_status': 'cancelled'
            },
            success: function(response) {
                console.log(response);

                // Parse response if it's a string
                let data = response;
                if (typeof response === 'string') {
                    try {
                        data = JSON.parse(response);
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        data = {
                            success: false,
                            data: 'خطا در پردازش پاسخ سرور'
                        };
                    }
                }

                // Check if request was successful
                if (data.success === true) {
                    // Close the modal
                    closeModal('pending-cancel-modal-' + orderId);

                    // Update button status to cancelled
                    updateButtonStatus(orderId, 'cancelled');

                    // Show success toast with server message
                    const message = data.data || 'درخواست لغو با موفقیت لغو شد.';
                    showToast(message, 'success');
                } else {
                    // Show error toast with server message
                    const message = data.data || 'خطا در لغو درخواست. لطفاً دوباره تلاش کنید.';
                    showToast(message, 'error');

                    // Restore button state
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showToast('خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.', 'error');

                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('bg-black')) {
            event.target.classList.add('hidden');
        }
    });
</script>

<?php if ( $show_credit_notification ) : ?>
<div id="ez-credit-form-overlay" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50" style="display: none;">
	<div class="flex min-h-full w-full items-center justify-center p-4">
		<div id="ez-credit-form-modal" class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-slate-200 relative">
			<div class="p-6 font-yekan text-right">
				<h2 class="text-xl font-bold text-navyBlue mb-4">فرم غیرفعالسازی درگاه پرداخت اعتباری</h2>
				<div class="text-slate-700 text-sm leading-relaxed space-y-3">
					<p>مجموعه دار گرامی، به زودی درگاه پرداخت اعتباری (اقساطی) برای کلیه بازی ها با شرایط زیر فعال میشه:</p>
					<p>پرداخت اعتباری (اقساطی) به اینصورته که تعداد نفراتی که کاربر موقع رزرو روی سایت انتخاب می کنه رو از طریق درگاه های پرداخت اعتباری مثل «دیجی پی» رزرو میکنه و طی 4 قسط کل مبلغ رزرو شده رو به ما پرداخت میکنه.</p>
					<p>سانس هایی که از این طرف هر ماه فروخته بشن از 18 تا 22م ماه بعد به کیف پول شما اضافه میشن.</p>
					<p>دریافت اقساط از کاربر بر عهده درگاه پرداخت و اسکیپ زومه و شما بدون مشکل در بازه مشخص شده مبلغ معین شده رو دریافت می کنین.</p>
					<p>شما بابت این خدمات هیچ کارمزد اضافه ای پرداخت نمی کنید و پرداخت کامزد درگاه پرداخت اعتباری، کاملا بر عهده اسکیپ زومه.</p>
					<p>فعال بودن پرداخت اعتباری، شانس فروش شما رو نسبت به سایر بازی ها بالاتر می بره، خصوصا در شرایط فعلی توصیه می کنیم از این طرح استفاده کنین.</p>
					<p>در صورتیکه کاربر پس از رزرو، افزایش تعداد نفرات داشته باشه مابقی نفرات به صورت نقدی و حضوری تسویه میشن.</p>
					<p class="mt-4 pt-4 border-t border-slate-200" style="color: #1e40af; font-weight: bold;">در صورتیکه مایل هستین در این طرح حضور داشته باشین و از مزایاش بهره مند بشین این فرم رو ببندین، در غیراینصورت دکمه غیرفعالسازی رو بزنین و درخواست غیرفعالسازی پرداخت اعتباری رو برای ما بفرستین.</p>
					<p class="mt-4 pt-4 border-t border-slate-200">اینجانب <strong><?php echo esc_html( $credit_display_name ); ?></strong> با شماره همراه <strong><?php echo esc_html( $credit_phone ); ?></strong> درخواست کنسلی حضور در طرح درگاه پرداخت اعتباری اسکیپ زوم را دارم.</p>
				</div>
				<div class="flex gap-3 justify-center mt-6 flex-wrap">
					<button type="button" id="ez-credit-form-btn-canceled" class="px-6 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-yekan-bold text-sm">غیرفعالسازی</button>
					<button type="button" id="ez-credit-form-btn-close" class="px-6 py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white font-yekan-bold text-sm">بستن</button>
				</div>
			</div>
		</div>
	</div>
	<div id="ez-credit-confirm-box" class="absolute inset-0 z-10 flex items-center justify-center p-4 bg-black/40 rounded-2xl" style="display: none;">
		<div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 font-yekan text-right border border-slate-200" onclick="event.stopPropagation();">
			<p class="text-slate-700 text-sm leading-relaxed mb-6">برای غیرفعالسازی پرداخت اعتباری برای مجموعه تون مطمئن هستین؟<br>این باعث میشه مزایای فروش اعتباری رو از دست بدین.</p>
			<div class="flex gap-3 justify-center flex-wrap">
				<button type="button" id="ez-credit-confirm-yes" class="px-6 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-yekan-bold text-sm">بله</button>
				<button type="button" id="ez-credit-confirm-no" class="px-6 py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white font-yekan-bold text-sm">خیر</button>
			</div>
		</div>
	</div>
</div>
<script>
(function() {
	var overlay = document.getElementById('ez-credit-form-overlay');
	var modal = document.getElementById('ez-credit-form-modal');
	var card = document.getElementById('ez-credit-notification-card');
	var confirmBox = document.getElementById('ez-credit-confirm-box');
	if (!overlay || !modal) return;
	var btnClose = document.getElementById('ez-credit-form-btn-close');
	var btnCanceled = document.getElementById('ez-credit-form-btn-canceled');
	var btnConfirmYes = document.getElementById('ez-credit-confirm-yes');
	var btnConfirmNo = document.getElementById('ez-credit-confirm-no');
	var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
	var nonce = '<?php echo esc_js( wp_create_nonce( 'v2-ajax-nonce' ) ); ?>';

	function openModal() {
		overlay.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}
	function hideModal() {
		overlay.style.display = 'none';
		document.body.style.overflow = '';
		if (confirmBox) confirmBox.style.display = 'none';
	}

	if (card) {
		card.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			openModal();
		});
		card.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(); }
		});
	}

	function sendAction(actionParam, done) {
		var formData = new FormData();
		formData.append('action', 'v2_ajax_handler');
		formData.append('nonce', nonce);
		formData.append('callback', 'credit_form_action');
		formData.append('action_param', actionParam);
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxUrl, true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && done) done();
		};
		xhr.send(formData);
	}

	if (btnClose) {
		btnClose.addEventListener('click', function() {
			sendAction('mark_view', function() {
				hideModal();
				var dot = document.getElementById('ez-credit-notification-dot');
				if (dot) dot.style.display = 'none';
			});
		});
	}
	if (btnCanceled) {
		btnCanceled.addEventListener('click', function() {
			if (confirmBox) confirmBox.style.display = 'flex';
		});
	}
	if (btnConfirmYes) {
		btnConfirmYes.addEventListener('click', function() {
			if (confirmBox) confirmBox.style.display = 'none';
			sendAction('mark_canceled', function() {
				hideModal();
				if (card) card.style.display = 'none';
			});
		});
	}
	if (btnConfirmNo) {
		btnConfirmNo.addEventListener('click', function() {
			if (confirmBox) confirmBox.style.display = 'none';
		});
	}

	overlay.addEventListener('click', function(e) {
		if (e.target === overlay) hideModal();
	});
})();
</script>
<?php endif; ?>