<?php
global $wpdb;

$user_id = get_current_user_id();

$page_num = $_POST['page'] ? sanitize_text_field( $_POST['page'] ) : 1;

$items_per_page = 100;

$user_role = get_user_role( $user_id );

if ( $user_role == 'sans_manager' ) {
	$max_page_num  = ceil( (int) ( $wpdb->get_var( "SELECT COUNT(*) FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}" ) ) / $items_per_page );
	$offset        = ( $page_num - 1 ) * $items_per_page;
	$user_products = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id} ORDER BY `meta_value` DESC LIMIT {$offset}, {$items_per_page}" ) );
} else {
	$max_page_num  = ceil( (int) ( $wpdb->get_var( "SELECT COUNT(*) FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}" ) ) / $items_per_page );
	$offset        = ( $page_num - 1 ) * $items_per_page;
	$user_products = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id} ORDER BY `meta_value` DESC LIMIT {$offset}, {$items_per_page}" ) );
}

$items = [];

foreach ( $user_products as $user_product ) {
	$product_id = $user_product->post_id;

	$votes_count    = (int) get_post_meta( $product_id, 'comments_count_new', true );
	$pending_orders = json_decode( ez_reservation( [
		'type' => 'get_pending_sanses',
		'data' => [ 'product_id' => $product_id ],
	] ), true );

	$query = "
        SELECT COUNT(*)   
        FROM {$wpdb->prefix}woocommerce_order_items AS order_items  
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta   
        ON order_items.order_item_id = order_itemmeta.order_item_id  
        INNER JOIN {$wpdb->prefix}posts AS posts   
        ON order_items.order_id = posts.ID  
        WHERE order_itemmeta.meta_key = '_product_id'   
        AND order_itemmeta.meta_value = %d  
        AND posts.post_status IN ('wc-walletx')
    ";

    /*********************************************/
    // rates

    $product_rates              = get_post_meta($product_id, 'clone_product_rates', true);
    $comments_count             = get_post_meta($product_id, 'clone_comments_count_new', true);
    $comments_count_meta        = get_comments([
        'post_id' => $product_id,
        'status'  => 'approve',
        'parent'  => 0,
    ]);
    $comments_count_meta_number = count($comments_count_meta);
    $decor                      = (int) $comments_count !== 0 ? $product_rates[1094] / $comments_count / 20 : 0;
    $moaama                     = (int) $comments_count !== 0 ? $product_rates[1095] / $comments_count / 20 : 0;
    $tazegi                     = (int) $comments_count !== 0 ? $product_rates[1098] / $comments_count / 20 : 0;
    $act                        = (int) $comments_count !== 0 ? $product_rates[1096] / $comments_count / 20 : 0;
    $barkhord                   = (int) $comments_count !== 0 ? $product_rates[1097] / $comments_count / 20 : 0;

    $raw_rate = ($decor + $moaama + $tazegi + $act + $barkhord) / 5;

    if (ez_get_product_meta($product_id)->product_type != 'اتاق فرار')
        $raw_rate = $raw_rate * 5; // امتیاز غیر اتاق فرارهارو در 5 ضرب کن تا استاندارد بشه

    // مدیریت امتیازهایی مثل 4.995 که نباید 5 بشوند اما همچنان رند بودن حفظ بشه
    if ( $raw_rate == 5 )
        $rate_final = 5;
    elseif ( round($raw_rate, 2) == 5 ) {
        $factor     = pow(10, 2);
        $rate_final = floor($raw_rate * $factor) / $factor;
    } else
        $rate_final = number_format(round($raw_rate, 2), 2, '.', '');

    /*********************************************/


	$done_orders_count = 100;
	$items[]           = [
		'product_id'           => (int) $product_id,
		'title'                => get_the_title( $product_id ),
		'image'                => get_post_thumbnail_id( $product_id ),
		'done_orders_count'    => $done_orders_count,
		'pending_orders_count' => $pending_orders ? count( $pending_orders ) : 0,
		'total_income'         => (int) get_post_meta( $product_id, 'total_income', true ),
		'average_rate'         => $rate_final,
		'votes_count'          => $votes_count,
		'active'               => get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0,
		'url'                  => get_permalink( $product_id ),
	];
}

$current_page = $page_num;
$total_pages  = $max_page_num;

foreach ( $items as $item ) { ?>

    <div class="rounded-xl border border-slate-120 shadow-12 p-8 flex gap-8 max-lg:hidden">
        <div class="grow flex flex-col justify-between gap-6">
            <div class="font-bold flex justify-between">
                <a href="<?php echo $item['url']; ?>" class="text-xl text-textColor">
					<?php echo $item['title']; ?>
                </a>
                <button type="button"
                        class="text-12 font-bold flex items-center justify-center gap-5 text-[#5091FB]"
                        data-qrcode="<?php echo $item['url']; ?>">
                    دریافت QR کد این بازی
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none">
                        <path d="M1.5 2.375C1.5 2.14294 1.59219 1.92038 1.75628 1.75628C1.92038 1.59219 2.14294 1.5 2.375 1.5H5.875C6.10706 1.5 6.32962 1.59219 6.49372 1.75628C6.65781 1.92038 6.75 2.14294 6.75 2.375V5.875C6.75 6.10706 6.65781 6.32962 6.49372 6.49372C6.32962 6.65781 6.10706 6.75 5.875 6.75H2.375C2.14294 6.75 1.92038 6.65781 1.75628 6.49372C1.59219 6.32962 1.5 6.10706 1.5 5.875V2.375Z" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 15V15.01" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.25 2.375C10.25 2.14294 10.3422 1.92038 10.5063 1.75628C10.6704 1.59219 10.8929 1.5 11.125 1.5H14.625C14.8571 1.5 15.0796 1.59219 15.2437 1.75628C15.4078 1.92038 15.5 2.14294 15.5 2.375V5.875C15.5 6.10706 15.4078 6.32962 15.2437 6.49372C15.0796 6.65781 14.8571 6.75 14.625 6.75H11.125C10.8929 6.75 10.6704 6.65781 10.5063 6.49372C10.3422 6.32962 10.25 6.10706 10.25 5.875V2.375Z" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.125 4.125V4.135" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.5 11.125C1.5 10.8929 1.59219 10.6704 1.75628 10.5063C1.92038 10.3422 2.14294 10.25 2.375 10.25H5.875C6.10706 10.25 6.32962 10.3422 6.49372 10.5063C6.65781 10.6704 6.75 10.8929 6.75 11.125V14.625C6.75 14.8571 6.65781 15.0796 6.49372 15.2437C6.32962 15.4078 6.10706 15.5 5.875 15.5H2.375C2.14294 15.5 1.92038 15.4078 1.75628 15.2437C1.59219 15.0796 1.5 14.8571 1.5 14.625V11.125Z" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.875 4.125V4.135" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.25 10.25H12.875" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.5 10.25V10.26" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.25 10.25V12.875" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.25 15.5H12.875" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.875 12.875H15.5" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.5 12.875V15.5" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <table class="w-full text-right text-md">
                <thead class="text-lighterTextColor">
                    <tr>
                        <th class="py-1">مجموع بازی های انجام شده</th>
                        <th class="py-1">بازی های در راه</th>
                        <th class="py-1">درآمد این اتاق تاکنون</th>
                        <th class="py-1">امتیاز کاربران</th>
                    </tr>
                </thead>
                <tbody class="text-textColor font-bold">
                    <tr>
                        <td class="py-1"><?php echo $item['done_orders_count']; ?></td>
                        <td class="py-1"><?php echo $item['pending_orders_count']; ?></td>
                        <td class="py-1"><?php echo number_format( $item['total_income'], 0 ); ?> تومان</td>
                        <td class="py-1">
                            <span class="bg-[#EFC101] px-2 ml-2 rounded"><?php echo $item['average_rate']; ?></span>
                            میانگین <?php echo $item['votes_count']; ?> رای
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <a href="<?php echo $item['url']; ?>">
			<?php echo wp_get_attachment_image( $item['image'], 'large', false, [
				'class' => 'w-20 rounded-xl object-cover',
			] ); ?>
        </a>

    </div>

    <div  class="py-7 border-b flex flex-col gap-6 lg:hidden">
        <div class="flex items-center gap-4">

            <a href="<?php echo $item['url']; ?>">
	            <?php echo wp_get_attachment_image( $item['image'], 'large', false, [
		            'class' => 'w-12 rounded-xl',
		            'style' => 'height:56px',
	            ] ); ?>
            </a>

            <span class="grow text-lg font-bold"><?php echo $item['title']; ?></span>

            <div class="flex flex-col items-end">
                <span class="bg-[#EFC101] px-2 leading-6 rounded w-fit"><?php echo $item['average_rate']; ?></span>
                میانگین <?php echo $item['votes_count']; ?> رای
            </div>

        </div>
        <table class="w-full text-right text-md">
            <thead class="text-lighterTextColor">
                <tr>
                    <th class="py-1">بازی های انجام شده</th>
                    <th class="py-1">درآمد تا کنون</th>
                    <th class="py-1">بازی های در راه</th>
                </tr>
            </thead>
            <tbody class="text-textColor font-bold">
                <tr>
                    <td class="py-1"><?php echo $item['done_orders_count']; ?></td>
                    <td class="py-1"><?php echo number_format( $item['total_income'] ); ?> تومان</td>
                    <td class="py-1"><?php echo $item['pending_orders_count']; ?></td>
                </tr>
            </tbody>
        </table>
        <button type="button"
                class="text-12 font-bold w-full flex items-center justify-between gap-5 text-[#5091FB] bg-[#F2F6FA] rounded-xs py-1 px-2"
                data-qrcode="<?php echo $item['url']; ?>">
            دریافت QR کد این بازی
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" class="mx-0" fill="none">
                <path d="M1.5 2.375C1.5 2.14294 1.59219 1.92038 1.75628 1.75628C1.92038 1.59219 2.14294 1.5 2.375 1.5H5.875C6.10706 1.5 6.32962 1.59219 6.49372 1.75628C6.65781 1.92038 6.75 2.14294 6.75 2.375V5.875C6.75 6.10706 6.65781 6.32962 6.49372 6.49372C6.32962 6.65781 6.10706 6.75 5.875 6.75H2.375C2.14294 6.75 1.92038 6.65781 1.75628 6.49372C1.59219 6.32962 1.5 6.10706 1.5 5.875V2.375Z" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5 15V15.01" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10.25 2.375C10.25 2.14294 10.3422 1.92038 10.5063 1.75628C10.6704 1.59219 10.8929 1.5 11.125 1.5H14.625C14.8571 1.5 15.0796 1.59219 15.2437 1.75628C15.4078 1.92038 15.5 2.14294 15.5 2.375V5.875C15.5 6.10706 15.4078 6.32962 15.2437 6.49372C15.0796 6.65781 14.8571 6.75 14.625 6.75H11.125C10.8929 6.75 10.6704 6.65781 10.5063 6.49372C10.3422 6.32962 10.25 6.10706 10.25 5.875V2.375Z" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4.125 4.125V4.135" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M1.5 11.125C1.5 10.8929 1.59219 10.6704 1.75628 10.5063C1.92038 10.3422 2.14294 10.25 2.375 10.25H5.875C6.10706 10.25 6.32962 10.3422 6.49372 10.5063C6.65781 10.6704 6.75 10.8929 6.75 11.125V14.625C6.75 14.8571 6.65781 15.0796 6.49372 15.2437C6.32962 15.4078 6.10706 15.5 5.875 15.5H2.375C2.14294 15.5 1.92038 15.4078 1.75628 15.2437C1.59219 15.0796 1.5 14.8571 1.5 14.625V11.125Z" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12.875 4.125V4.135" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10.25 10.25H12.875" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15.5 10.25V10.26" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10.25 10.25V12.875" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10.25 15.5H12.875" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12.875 12.875H15.5" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15.5 12.875V15.5" stroke="#5091FB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>

<?php } ?>