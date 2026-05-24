<?php

global $wpdb;

$user_id = get_current_user_id();

$start      = floor( sanitize_text_field( $_POST['start'] ) / 1000 );
$end        = floor( sanitize_text_field( $_POST['end'] ) / 1000 );
$date_range = "$end,$start";

$totals = [
    "total_tickets" => 0,
    "total_income"  => 0,
    "total_prepaid" => 0,
    "total_credit"  => 0,
];

$order_statuses = [ 'wc-partially-paid', 'wc-walletx', 'wc-completed' ];

// دریافت محصولات کاربر (این بخش تغییر نمی‌کند)
$user_role = get_user_role( $user_id );
$products_id = [];
if ( $user_role == 'sans_manager' ) {
    $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A );
} elseif ( $user_role == 'compiler' ) {
    $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A );
}

foreach ( $user_products as $user_product ) {
    $products_id[] = $user_product['post_id'];
}

if ( empty( $date_range ) || ! $date_range || empty($products_id) ) {
    wp_send_json_error( 'تاریخ حتما باید وارد شود.', 400 );
}

$date_range = explode( ',', $date_range );
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

try {
    // دریافت تمام سفارشات برای محاسبه خلاصه
    $orders = $medoo->select('wp_markting', [
        'order_id',
        'game_id',
        'order_tickets_quantity',
        'order_paid',
        'order_finall_price'
    ], $where_conditions);
} catch (Exception $e) {
    error_log('Error in panel_sells_get_summary: ' . $e->getMessage());
    wp_send_json_error('خطا در دریافت خلاصه فروشات: ' . $e->getMessage());
    return;
}

// اطمینان از اینکه orders یک array است
if (!is_array($orders)) {
    $orders = [];
}

$quantities = [];
$incomes = [];
$prepaids = [];
$product_id = null; // برای استفاده در tax_free check

foreach ( $orders as $order_data ) {
    $order_id = $order_data['order_id'];
    $product_id = $order_data['game_id']; // آخرین product_id برای tax_free check
    $quantity = $order_data['order_tickets_quantity'];
    
    // دریافت pish_pardakht_per_person از محصول
    $pish_per_person = get_post_meta($product_id, 'pish_pardakht_per_person', true);
    $pish_per_person = !empty($pish_per_person) ? $pish_per_person : 1;

    // استفاده از order_paid برای prepaid
    $prepaid = (float)($order_data['order_paid'] ?: 0);
    
    // استفاده از order_finall_price برای item_total، اگر موجود نبود محاسبه کن
    $item_total = 0;
    if (!empty($order_data['order_finall_price'])) {
        $item_total = (float)$order_data['order_finall_price'];
    } else {
        // محاسبه از order_paid
        $item_total = $prepaid / $pish_per_person * $quantity;
    }

    $quantities[$order_id][]    = $quantity;
    $incomes[$order_id][]       = $item_total;
    $prepaids[$order_id][]      = $prepaid;

    $totals['total_tickets'] += $quantity;
    $totals['total_income']  += $item_total;
    $totals['total_prepaid'] += $prepaid;
}

if ( get_current_user_id() == 32815 ) {
    saeed_store([$quantities, $incomes, $prepaids]);
}

$commission = 10;
$tax        = 10;
$tax_free   = [ 2762, 21755, 353952, 87471, 145024 ];

if ( in_array( $product_id, $tax_free ) ) {
    $tax = 0;
}

$totals['total_credit'] = ceil( $totals['total_prepaid'] - ( $totals['total_income'] * ( $commission / 100 ) * ( 1 + $tax / 100 ) ) );

?>
<div class="my-6 flex items-stretch justify-between max-lg:border-b max-lg:mb-0 max-lg:pb-6 lg:justify-around lg:rounded-xl lg:border lg:border-edge lg:py-1 lg:shadow-13">
    <div>
        <div class="text-sm font-bold text-gray-ui">تیکت</div>
        <div class="text-lg font-bold max-lg:text-2xl"><?php echo esc_html( $totals['total_tickets'] ); ?></div>
    </div>
    <div>
        <div class="text-sm font-bold text-gray-ui">درآمد کل (تومان)</div>
        <div class="text-lg font-bold max-lg:text-2xl"><?php echo number_format( esc_html( $totals['total_income'] ) ); ?></div>
    </div>
    <div class="lg:hidden border-r"></div>
    <div>
        <div class="text-sm font-bold text-gray-ui">پیش پرداخت</div>
        <div class="text-lg font-bold max-lg:text-2xl text-green-500"><?php echo number_format( esc_html( $totals['total_prepaid'] ) ); ?></div>
    </div>
    <div>
        <div class="text-sm font-bold text-gray-ui">بستانکاری</div>
        <div class="text-lg font-bold max-lg:text-2xl"><?php echo number_format( esc_html( $totals['total_credit'] ) ); ?></div>
    </div>
</div>
