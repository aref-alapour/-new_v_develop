<?php
/**
 * Template Name: aref-test-8
 *
 * ارسال مستقیم SMS تأیید رزرو برای لیست سفارش‌ها (بدون درج در sms_sending_queue).
 * فقط مدیرکل. slug پیشنهادی: aref-test-8
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

/**
 * شناسه سفارش‌ها — این آرایه را ویرایش کنید.
 *
 * @var int[]
 */
$ORDER_IDS = array(
	823207,
	823206,
	823205,
	823204,
	823201,
	823200,
	823198,
	823197,
	823195,
	823193,
	823192,
	823191,
	823190,
	823189,
	823188,
	823187,
	823186,
	823185,
	823182,
	823181,
	823178,
	823177,
	823175,
	823174,
	823172,
	823117,
	823118,
	823119,
	823120,
	823161,
	823160,
	823159,
	823157,
	823156,
	823154,
	823153,
	823152,
	823141,
	823134,
	823132,
	823130,
	823127,
	823126,
	823123,
);

/**
 * @return array{ok:bool,ret_status:?int,raw:string,error:string}
 */
function ez_aref8_send_sms_now( string $phone, string $text, $token ): array {
	if ( ! function_exists( 'smsPattern' ) ) {
		return array(
			'ok'         => false,
			'ret_status' => null,
			'raw'        => '',
			'error'      => 'تابع smsPattern موجود نیست.',
		);
	}

	$raw      = (string) smsPattern( $phone, $text, $token );
	$response = json_decode( $raw );
	$ok       = isset( $response->RetStatus ) && (int) $response->RetStatus === 1;

	if ( ! $ok ) {
		$raw2      = (string) smsPattern( $phone, $text, $token );
		$response2 = json_decode( $raw2 );
		if ( isset( $response2->RetStatus ) && (int) $response2->RetStatus === 1 ) {
			$ok       = true;
			$raw      = $raw2;
			$response = $response2;
		}
	}

	return array(
		'ok'         => $ok,
		'ret_status' => isset( $response->RetStatus ) ? (int) $response->RetStatus : null,
		'raw'        => $raw,
		'error'      => $ok ? '' : 'ارسال ناموفق',
	);
}

/**
 * همان منطق ez_queue_reservation_confirmation_sms_bundle — فقط لیست پیام‌ها را برمی‌گرداند.
 *
 * @return array{ok:bool,error:string,items:array<int,array{type:string,token:int,phone:string,text:string}>,meta:array<string,mixed>}
 */
function ez_aref8_collect_reservation_sms_items( int $order_id ): array {
	global $wldb;

	$empty = array(
		'ok'    => false,
		'error' => '',
		'items' => array(),
		'meta'  => array(),
	);

	if ( $order_id <= 0 ) {
		$empty['error'] = 'order_id نامعتبر';
		return $empty;
	}

	if ( ( ! isset( $wldb ) || ! is_object( $wldb ) || ! method_exists( $wldb, 'get_balance' ) ) && class_exists( 'EZ_Transaction_CRUD' ) ) {
		$wldb = new EZ_Transaction_CRUD();
	}

	if ( ! function_exists( 'ez_order_primary_bookable_line_item' ) ) {
		$empty['error'] = 'ez_order_primary_bookable_line_item موجود نیست';
		return $empty;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		$empty['error'] = 'سفارش یافت نشد';
		return $empty;
	}

	list( $product_id, $product_quantity ) = ez_order_primary_bookable_line_item( $order );
	$product_id       = $product_id ? (int) $product_id : 0;
	$product_quantity = max( 1, (int) $product_quantity );
	if ( $product_id <= 0 ) {
		$empty['error'] = 'محصول بوکینگ یافت نشد';
		return $empty;
	}

	$user_id        = (int) $order->get_user_id();
	$sans_time      = (int) get_post_meta( $order_id, 'sans_time', true );
	$prepaid        = (int) get_post_meta( $order_id, 'prepaid', true );
	$player_fname   = $order->get_billing_first_name();
	$player_name    = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$total_amount   = (int) $order->get_total();
	$item_total_approx = null;

	foreach ( $order->get_items() as $item ) {
		if ( ! $item instanceof WC_Order_Item_Product ) {
			continue;
		}
		$item_total_approx = max( $item_total_approx, (float) $item->get_total() + (float) $item->get_total_tax() );
	}
	if ( $item_total_approx === null ) {
		$item_total_approx = (float) $total_amount;
	}

	$coupon_amount = 0.0;
	if ( function_exists( 'ez_get_coupon_discount_amount' ) ) {
		foreach ( $order->get_coupon_codes() as $code ) {
			$coupon_amount += ez_get_coupon_discount_amount( $code, (float) $item_total_approx );
		}
	}

	$user_level_discount = 0.0;
	if ( $user_id && function_exists( 'get_user_discount' ) && ( in_array( $user_id, array( 3325, 2, 80 ), true ) ) ) {
		$discount            = get_user_discount( $order_id, $user_id );
		$pct                 = isset( $discount['percentage'] ) ? (float) $discount['percentage'] : 0.0;
		$user_level_discount = (float) $item_total_approx * $pct / 100;
	}

	$online_paid_raw = get_post_meta( $order_id, '_order_total_2', true );
	if ( ! $online_paid_raw ) {
		$online_paid_raw = get_post_meta( $order_id, '_order_total', true );
	}
	$online_paid = max( 0, (float) $online_paid_raw );

	if ( ! isset( $wldb ) || ! is_object( $wldb ) || ! method_exists( $wldb, 'get_balance' ) ) {
		$empty['error'] = 'wldb در دسترس نیست';
		return $empty;
	}

	$wallet_share = max(
		0.0,
		(float) $prepaid - ( $online_paid + $coupon_amount + $user_level_discount )
	);
	$send_guard = (bool) $total_amount || $coupon_amount > 0 || $wallet_share > 0;

	if ( ! $send_guard ) {
		$empty['error'] = 'send_guard=false — شرط ارسال SMS برقرار نیست';
		return $empty;
	}

	$order_date = function_exists( 'jdate' ) ? jdate( 'l j F', $sans_time ) : '';
	$order_time = function_exists( 'jdate' ) ? jdate( 'H:i', $sans_time ) : '';

	$user_phone_raw = $order->get_billing_phone();
	$persian        = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	$english        = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$user_phone_no  = str_replace( $persian, $english, ltrim( (string) $user_phone_raw, '0' ) );
	$user_phone_no  = preg_replace( '/^\+?98|\|98|\D/', '', ( $user_phone_no ) );
	$user_phone_no  = ltrim( $user_phone_no, '0' );

	if ( strlen( $user_phone_no ) !== 10 || $user_phone_no[0] !== '9' ) {
		$empty['error'] = 'شماره پلیر نامعتبر: ' . (string) $user_phone_raw;
		return $empty;
	}

	$user_phone_number = $user_phone_no;
	$product_title     = get_the_title( $product_id );
	$product_phone     = function_exists( 'get_field' ) ? get_field( 'room_phone', $product_id ) : '';
	$owner_id          = (int) get_post_meta( $product_id, 'user_ebtal', true );
	$sans_manager_id   = (int) get_post_meta( $product_id, 'sans_manager', true );
	$sms2player__text  = "{$player_fname};{$product_title};{$order_date};{$order_time};{$product_phone};{$order_id}";

	$owner_ud = $owner_id ? get_userdata( $owner_id ) : null;
	if ( ! $owner_ud || empty( $owner_ud->user_login ) ) {
		$empty['error'] = 'مالک (user_ebtal) یافت نشد';
		return $empty;
	}

	$formatted_prepaid = function_exists( 'englishToPersian' ) ? englishToPersian( number_format( $prepaid ) ) : number_format( $prepaid );
	$sms2maj__text     = "{$product_title};{$order_date};{$order_time};{$player_name};{$formatted_prepaid}";

	$items = array(
		array(
			'type'  => 'user',
			'token' => 434387,
			'phone' => $user_phone_number,
			'text'  => $sms2player__text,
		),
		array(
			'type'  => 'owner',
			'token' => 434389,
			'phone' => (string) $owner_ud->user_login,
			'text'  => $sms2maj__text,
		),
	);

	$owner2_phone = function_exists( 'get_field' ) ? get_field( 'payamak_2', $product_id ) : '';
	if ( $owner2_phone ) {
		$items[] = array(
			'type'  => 'owner2',
			'token' => 434389,
			'phone' => (string) $owner2_phone,
			'text'  => $sms2maj__text,
		);
	}

	if ( $sans_manager_id ) {
		$sm_ud = get_userdata( $sans_manager_id );
		if ( $sm_ud && ! empty( $sm_ud->user_login ) ) {
			$items[] = array(
				'type'  => 'manager',
				'token' => 434389,
				'phone' => (string) $sm_ud->user_login,
				'text'  => $sms2maj__text,
			);
		}
	}

	return array(
		'ok'    => true,
		'error' => '',
		'items' => $items,
		'meta'  => array(
			'product_id'       => $product_id,
			'product_title'    => $product_title,
			'product_quantity' => $product_quantity,
			'sans_time'        => $sans_time,
			'player_name'      => $player_name,
		),
	);
}

/**
 * @param int[] $order_ids
 * @return array<int, array<string, mixed>>
 */
function ez_aref8_send_reservation_sms_for_orders( array $order_ids ): array {
	$results = array();

	foreach ( $order_ids as $order_id ) {
		$order_id = (int) $order_id;
		$row      = array(
			'order_id' => $order_id,
			'ok'       => false,
			'error'    => '',
			'sms'      => array(),
		);

		$bundle = ez_aref8_collect_reservation_sms_items( $order_id );
		if ( empty( $bundle['ok'] ) ) {
			$row['error'] = (string) ( $bundle['error'] ?? 'خطای نامشخص' );
			$results[]    = $row;
			continue;
		}

		$all_ok = true;
		foreach ( (array) $bundle['items'] as $item ) {
			$send = ez_aref8_send_sms_now(
				(string) $item['phone'],
				(string) $item['text'],
				$item['token']
			);

			$row['sms'][] = array(
				'type'       => (string) $item['type'],
				'token'      => (int) $item['token'],
				'phone'      => (string) $item['phone'],
				'text'       => (string) $item['text'],
				'ok'         => ! empty( $send['ok'] ),
				'ret_status' => $send['ret_status'],
				'raw'        => (string) $send['raw'],
			);

			if ( empty( $send['ok'] ) ) {
				$all_ok = false;
			}
		}

		$row['ok']    = $all_ok;
		$row['error'] = $all_ok ? '' : 'حداقل یک SMS ارسال نشد';
		$row['meta']  = $bundle['meta'] ?? array();
		$results[]    = $row;
	}

	return $results;
}

$run       = isset( $_POST['ez_aref8_send'] ) && $_POST['ez_aref8_send'] === '1';
$raw_ids   = isset( $_POST['order_ids'] ) ? sanitize_textarea_field( wp_unslash( $_POST['order_ids'] ) ) : '';
$show_raw  = isset( $_POST['show_raw'] ) && $_POST['show_raw'] === '1';
$results   = array();
$run_error = '';

if ( $run ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ez_aref8_send_sms' ) ) {
		wp_die( 'Nonce نامعتبر است.', 'Forbidden', array( 'response' => 403 ) );
	}

	$ids = array();
	if ( $raw_ids !== '' ) {
		foreach ( preg_split( '/[\s,;]+/', $raw_ids, -1, PREG_SPLIT_NO_EMPTY ) as $part ) {
			$id = (int) $part;
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}
	}
	if ( empty( $ids ) && ! empty( $ORDER_IDS ) ) {
		$ids = array_map( 'intval', $ORDER_IDS );
	}
	$ids = array_values( array_unique( array_filter( $ids ) ) );

	if ( empty( $ids ) ) {
		$run_error = 'هیچ order_id معتبری وارد نشده — آرایه $ORDER_IDS یا textarea را پر کنید.';
	} else {
		$results = ez_aref8_send_reservation_sms_for_orders( $ids );
	}
}

$default_ids_text = ! empty( $ORDER_IDS ) ? implode( "\n", array_map( 'intval', $ORDER_IDS ) ) : $raw_ids;

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 960px;">
	<h1 class="text-2xl font-bold mb-2">aref-test-8 — ارسال مستقیم SMS رزرو</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		پیام‌های تأیید رزرو (پلیر + ماج + owner2 + manager) با همان منطق
		<code dir="ltr">ez_queue_reservation_confirmation_sms_bundle</code>
		ساخته می‌شوند ولی <strong>مستقیم</strong> با
		<code dir="ltr">smsPattern()</code> ارسال می‌شوند —
		<strong>بدون درج</strong> در جدول <code dir="ltr">sms_sending_queue</code>.
	</p>

	<form method="post" class="mb-6 border border-gray-200 rounded-lg p-4 text-sm space-y-3">
		<?php wp_nonce_field( 'ez_aref8_send_sms' ); ?>
		<input type="hidden" name="ez_aref8_send" value="1">

		<label class="block">
			<span class="font-semibold">شناسه سفارش‌ها</span>
			<span class="text-gray-500 text-xs">(هر خط، یا با کاما/فاصله جدا)</span>
			<textarea name="order_ids" rows="6" dir="ltr"
				class="mt-1 w-full border rounded px-3 py-2 font-mono text-sm"
				placeholder="123456&#10;123457"><?php echo esc_textarea( $default_ids_text ); ?></textarea>
		</label>

		<label class="flex items-center gap-2">
			<input type="checkbox" name="show_raw" value="1" <?php checked( $show_raw ); ?>>
			نمایش پاسخ خام API
		</label>

		<button type="submit"
			class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
			onclick="return confirm('SMS واقعی ارسال می‌شود (بدون صف). ادامه؟');">
			ارسال مستقیم SMS
		</button>
	</form>

	<?php if ( $run_error !== '' ) : ?>
		<p class="text-sm text-red-800 bg-red-50 border border-red-200 rounded p-3 mb-4"><?php echo esc_html( $run_error ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $results ) ) : ?>
		<h2 class="text-lg font-semibold mb-3">نتیجه</h2>

		<?php foreach ( $results as $result ) : ?>
			<div class="mb-4 border rounded-lg overflow-hidden <?php echo ! empty( $result['ok'] ) ? 'border-green-200' : 'border-red-200'; ?>">
				<div class="px-4 py-2 <?php echo ! empty( $result['ok'] ) ? 'bg-green-50' : 'bg-red-50'; ?> flex flex-wrap gap-3 items-center text-sm">
					<strong>order_id:</strong>
					<code dir="ltr"><?php echo (int) $result['order_id']; ?></code>
					<span><?php echo ! empty( $result['ok'] ) ? '✓ همه SMS ارسال شد' : '✗ ' . esc_html( (string) $result['error'] ); ?></span>
					<?php if ( ! empty( $result['meta']['product_title'] ) ) : ?>
						<span class="text-gray-600">| <?php echo esc_html( (string) $result['meta']['product_title'] ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $result['sms'] ) ) : ?>
					<div class="overflow-x-auto">
						<table class="min-w-full text-xs">
							<thead class="bg-gray-100">
								<tr>
									<th class="py-2 px-2 text-right">type</th>
									<th class="py-2 px-2 text-right">token</th>
									<th class="py-2 px-2 text-right">phone</th>
									<th class="py-2 px-2 text-right">text</th>
									<th class="py-2 px-2 text-right">status</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( (array) $result['sms'] as $sms ) : ?>
									<tr class="border-t">
										<td class="py-1 px-2"><?php echo esc_html( (string) $sms['type'] ); ?></td>
										<td class="py-1 px-2 font-mono" dir="ltr"><?php echo (int) $sms['token']; ?></td>
										<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) $sms['phone'] ); ?></td>
										<td class="py-1 px-2 font-mono max-w-xs truncate" dir="ltr" title="<?php echo esc_attr( (string) $sms['text'] ); ?>">
											<?php echo esc_html( (string) $sms['text'] ); ?>
										</td>
										<td class="py-1 px-2">
											<?php if ( ! empty( $sms['ok'] ) ) : ?>
												<span class="text-green-700 font-bold">OK</span>
											<?php else : ?>
												<span class="text-red-700 font-bold">FAIL</span>
												<?php if ( $sms['ret_status'] !== null ) : ?>
													<code dir="ltr">(<?php echo (int) $sms['ret_status']; ?>)</code>
												<?php endif; ?>
											<?php endif; ?>
										</td>
									</tr>
									<?php if ( $show_raw && ! empty( $sms['raw'] ) ) : ?>
										<tr class="border-t bg-gray-50">
											<td colspan="5" class="py-2 px-2">
												<pre class="text-[10px] overflow-x-auto" dir="ltr"><?php echo esc_html( (string) $sms['raw'] ); ?></pre>
											</td>
										</tr>
									<?php endif; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<div class="text-xs text-gray-500 border-t pt-4 mt-8">
		<p class="mb-1">مسیر عادی سفارش: <code dir="ltr">add_to_sms_queue()</code> → جدول <code dir="ltr">sms_sending_queue</code> → cron <code dir="ltr">ez_sms_sending_queue_schedule</code></p>
		<p>پردازش صف: <code dir="ltr">page-aref-test-5.php</code></p>
	</div>
</main>

<?php
get_footer();
