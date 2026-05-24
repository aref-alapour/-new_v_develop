<?php
/**
 * Template Name: aref-test-7
 *
 * استعلام وضعیت یک سفارش: درگاه (inquiry) + WooCommerce + markting + booking.
 * فقط مدیرکل. slug پیشنهادی: aref-test-7
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

/**
 * @return array<string, array{name:string,color:string}>
 */
function ez_aref7_status_labels(): array {
	return array(
		'pending'           => array( 'name' => 'در حال پرداخت', 'color' => '#FD7013' ),
		'processing'        => array( 'name' => 'در حال بستن سانس', 'color' => '#3F7FF5' ),
		'cancelled'         => array( 'name' => 'لغو شده', 'color' => '#F21543' ),
		'partially-paid'    => array( 'name' => 'پیش پرداخت', 'color' => '#049654' ),
		'completed-paid'    => array( 'name' => 'پرداخت کامل', 'color' => '#A020F0' ),
		'completed'         => array( 'name' => 'تکمیل شده', 'color' => '#049654' ),
	);
}

function ez_aref7_status_badge( string $status ): string {
	$labels = ez_aref7_status_labels();
	$key    = trim( $status );
	if ( $key === '' ) {
		return '<span class="text-gray-400">—</span>';
	}
	$norm = str_starts_with( $key, 'wc-' ) ? substr( $key, 3 ) : $key;
	$info = $labels[ $norm ] ?? array( 'name' => $key, 'color' => '#64748B' );
	return '<span class="font-bold" style="color:' . esc_attr( $info['color'] ) . '">' . esc_html( $info['name'] ) . '</span>'
		. ' <code dir="ltr" class="text-[11px] text-gray-500">' . esc_html( $key ) . '</code>';
}

function ez_aref7_zp_status_label( ?string $status ): string {
	$labels = array(
		'PAID'     => 'پرداخت شده (verify نشده در زرین‌پال)',
		'VERIFIED' => 'تأیید شده در زرین‌پال',
		'IN_BANK'  => 'در حال پردازش بانکی',
		'FAILED'   => 'ناموفق',
		'REVERSED' => 'برگشت‌خورده',
		'CANCELLED'=> 'لغو (زیبال)',
	);
	return isset( $labels[ $status ] ) ? $labels[ $status ] : ( $status ?: '—' );
}

/**
 * @return array{settings_key:string,merchant:string,sandbox:bool,access_token:string,label:string}
 */
function ez_aref7_gateway_from_order( WC_Order $order ): array {
	$pm = (string) $order->get_payment_method();
	if ( $pm === 'WC_ZPal_co' ) {
		$key   = 'woocommerce_WC_ZPal_co_settings';
		$label = 'ZarinPal co';
	} elseif ( $pm === 'WC_ZPal' ) {
		$key   = 'woocommerce_WC_ZPal_settings';
		$label = 'ZarinPal main';
	} else {
		return array(
			'settings_key' => '',
			'merchant'     => '',
			'sandbox'      => false,
			'access_token' => '',
			'label'        => $pm,
		);
	}
	$settings = get_option( $key, array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	return array(
		'settings_key' => $key,
		'merchant'     => isset( $settings['merchantcode'] ) ? trim( (string) $settings['merchantcode'] ) : '',
		'sandbox'      => isset( $settings['sandbox'] ) && $settings['sandbox'] === 'yes',
		'access_token' => isset( $settings['access_token'] ) ? trim( (string) $settings['access_token'] ) : '',
		'label'        => $label,
	);
}

/**
 * inquiry.json — فقط merchant_id + authority (داکیومنت زرین‌پال).
 *
 * @return array{ok:bool,http_code:int,raw:?array<string,mixed>,error:string}
 */
function ez_aref7_zp_inquiry_http( string $merchant_id, string $authority, bool $sandbox ): array {
	$merchant_id = trim( $merchant_id );
	$authority   = trim( $authority );
	if ( $merchant_id === '' || $authority === '' ) {
		return array( 'ok' => false, 'http_code' => 0, 'raw' => null, 'error' => 'merchant یا authority خالی است.' );
	}

	$base = function_exists( 'ez_zp_payment_api_base_url' )
		? ez_zp_payment_api_base_url( $sandbox )
		: ( $sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com' );

	$url      = rtrim( $base, '/' ) . '/pg/v4/payment/inquiry.json';
	$response = wp_remote_post(
		$url,
		array(
			'body'    => wp_json_encode(
				array(
					'merchant_id' => $merchant_id,
					'authority'   => $authority,
				)
			),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'User-Agent'   => 'EscapeZoom/aref-test-7-inquiry',
			),
			'timeout' => 25,
		)
	);

	if ( is_wp_error( $response ) ) {
		return array( 'ok' => false, 'http_code' => 0, 'raw' => null, 'error' => $response->get_error_message() );
	}

	$http_code = (int) wp_remote_retrieve_response_code( $response );
	$body      = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) ) {
		return array( 'ok' => false, 'http_code' => $http_code, 'raw' => null, 'error' => 'پاسخ JSON نامعتبر.' );
	}

	return array( 'ok' => true, 'http_code' => $http_code, 'raw' => $body, 'error' => '' );
}

/**
 * @return array{found:bool,amount:?int,item:?array<string,mixed>}
 */
function ez_aref7_in_unverified_list( string $merchant_id, string $authority, bool $sandbox ): array {
	$empty = array( 'found' => false, 'amount' => null, 'item' => null );
	if ( $merchant_id === '' || $authority === '' ) {
		return $empty;
	}
	$fetch = function_exists( 'ez_zp_fetch_unverified_authorities' )
		? ez_zp_fetch_unverified_authorities( $merchant_id, $sandbox )
		: null;
	if ( ! is_array( $fetch ) || empty( $fetch['ok'] ) ) {
		return $empty;
	}
	foreach ( (array) ( $fetch['authorities'] ?? array() ) as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$a = isset( $item['authority'] ) ? trim( (string) $item['authority'] ) : '';
		if ( $a === $authority ) {
			return array(
				'found'  => true,
				'amount' => isset( $item['amount'] ) ? (int) $item['amount'] : null,
				'item'   => $item,
			);
		}
	}
	return $empty;
}

/**
 * @return array<string, mixed>
 */
function ez_aref7_local_snapshot( int $order_id ): array {
	$out = array(
		'wc'       => array( 'found' => false ),
		'markting' => array( 'found' => false ),
		'booking'  => array( 'exists' => false ),
	);

	if ( $order_id <= 0 || ! function_exists( 'wc_get_order' ) ) {
		return $out;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return $out;
	}

	$out['wc'] = array(
		'found'          => true,
		'status'         => (string) $order->get_status(),
		'is_paid'        => (bool) $order->is_paid(),
		'payment_method' => (string) $order->get_payment_method(),
		'authority'      => (string) $order->get_meta( '_zarinpal_authority' ),
		'transaction_id' => (string) $order->get_transaction_id(),
	);

	if ( function_exists( 'medoo' ) ) {
		try {
			$crm = medoo();
			if ( $crm ) {
				$mrow = $crm->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );
				if ( is_array( $mrow ) && ! empty( $mrow ) ) {
					$slug = function_exists( 'ez_markting_status_slug' )
						? ez_markting_status_slug( $mrow )
						: (string) ( $mrow['order_status'] ?? '' );
					$out['markting'] = array(
						'found'  => true,
						'slug'   => $slug,
						'status' => (string) ( $mrow['order_status'] ?? '' ),
					);
				}
			}
		} catch ( Throwable $e ) {
			$out['markting']['error'] = $e->getMessage();
		}
	}

	if ( function_exists( 'ez_booking_exists_for_order' ) ) {
		$out['booking']['exists'] = (bool) ez_booking_exists_for_order( $order_id );
	}

	return $out;
}

/**
 * @param array<string, mixed> $inquiry
 * @return array<int, string>
 */
function ez_aref7_summarize( WC_Order $order, array $local, array $inquiry, array $unverified ): array {
	$notes = array();
	$wc_paid = ! empty( $local['wc']['is_paid'] );
	$zp      = (string) ( $inquiry['status'] ?? '' );
	$gw      = (string) ( $inquiry['gateway'] ?? '' );

	if ( $zp === 'PAID' && ! $wc_paid ) {
		$notes[] = 'درگاه می‌گوید پرداخت شده (PAID) ولی WC هنوز unpaid است — verify لازم است.';
	}
	if ( $zp === 'VERIFIED' && ! $wc_paid ) {
		$notes[] = 'زرین‌پال VERIFIED است ولی WC unpaid — احتمال خطا در verify سایت.';
	}
	if ( $zp === 'FAILED' || $zp === 'REVERSED' ) {
		$notes[] = 'درگاه وضعیت ناموفق/برگشتی گزارش کرده است.';
	}
	if ( ! empty( $unverified['found'] ) ) {
		$notes[] = 'authority هنوز در لیست unVerified.json است (در صف verify زرین‌پال).';
	}
	if ( ! empty( $local['markting']['found'] ) && ! empty( $local['wc']['found'] ) ) {
		$wc_slug  = str_starts_with( $local['wc']['status'], 'wc-' ) ? substr( $local['wc']['status'], 3 ) : $local['wc']['status'];
		$mkt_slug = (string) ( $local['markting']['slug'] ?? '' );
		if ( $mkt_slug !== '' && $wc_slug !== '' && $mkt_slug !== $wc_slug ) {
			$notes[] = "ناهمگامی WC ({$wc_slug}) و markting ({$mkt_slug}).";
		}
	}
	if ( $wc_paid && empty( $local['booking']['exists'] ) ) {
		$notes[] = 'WC پرداخت‌شده است ولی بوکینگ ثبت نشده.';
	}
	if ( $gw === '' && in_array( $order->get_payment_method(), array( 'WC_ZPal', 'WC_ZPal_co', 'WC_Zibal' ), true ) === false ) {
		$notes[] = 'درگاه پرداخت این سفارش از نوع inquiry پشتیبانی‌شده نیست.';
	}

	return $notes;
}

/**
 * اجرای استعلام وضعیت — هستهٔ اسکریپت.
 *
 * @return array<string, mixed>
 */
function ez_aref7_run_inquiry( int $order_id, string $authority = '' ): array {
	$result = array(
		'order_id'   => $order_id,
		'authority'  => $authority,
		'order'      => null,
		'local'      => array(),
		'gateway'    => array(),
		'inquiry'    => array(),
		'unverified' => array(),
		'notes'      => array(),
		'error'      => '',
	);

	if ( $order_id <= 0 && $authority !== '' && function_exists( 'get_order_id_by_authority' ) ) {
		$order_id = (int) get_order_id_by_authority( $authority );
		$result['order_id'] = $order_id;
	}

	if ( $order_id <= 0 ) {
		$result['error'] = 'order_id یا authority معتبر وارد کنید.';
		return $result;
	}

	if ( ! function_exists( 'wc_get_order' ) ) {
		$result['error'] = 'WooCommerce در دسترس نیست.';
		return $result;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		$result['error'] = 'سفارش یافت نشد.';
		return $result;
	}

	$result['order'] = $order;
	if ( $authority === '' ) {
		$authority = (string) $order->get_meta( '_zarinpal_authority' );
		if ( $authority === '' && function_exists( 'ez_zibal_get_track_id' ) ) {
			$authority = (string) ez_zibal_get_track_id( $order );
		}
	}
	$result['authority'] = $authority;
	$result['local']     = ez_aref7_local_snapshot( $order_id );

	$pm = (string) $order->get_payment_method();

	if ( function_exists( 'ez_gateway_inquiry_for_order' ) ) {
		$gw_inq = ez_gateway_inquiry_for_order( $order );
		$result['inquiry'] = array(
			'gateway' => (string) ( $gw_inq['gateway'] ?? '' ),
			'paid'    => ! empty( $gw_inq['paid'] ),
			'status'  => (string) ( $gw_inq['status'] ?? '' ),
			'status_fa' => ez_aref7_zp_status_label( (string) ( $gw_inq['status'] ?? '' ) ),
			'source'  => 'ez_gateway_inquiry_for_order',
			'raw'     => $gw_inq['raw'] ?? null,
		);
	}

	if ( in_array( $pm, array( 'WC_ZPal', 'WC_ZPal_co' ), true ) && $authority !== '' ) {
		$gw = ez_aref7_gateway_from_order( $order );
		$result['gateway'] = $gw;

		if ( empty( $result['inquiry']['status'] ) && $gw['merchant'] !== '' ) {
			$http = ez_aref7_zp_inquiry_http( $gw['merchant'], $authority, $gw['sandbox'] );
			if ( $http['ok'] && is_array( $http['raw'] ) && isset( $http['raw']['data'] ) && is_array( $http['raw']['data'] ) ) {
				$data = $http['raw']['data'];
				$zp_status = isset( $data['status'] ) ? (string) $data['status'] : '';
				$result['inquiry'] = array(
					'gateway'     => 'zarinpal',
					'paid'        => ( $zp_status === 'PAID' || $zp_status === 'VERIFIED' ),
					'status'      => $zp_status,
					'status_fa'   => ez_aref7_zp_status_label( $zp_status ),
					'code'        => isset( $data['code'] ) ? (int) $data['code'] : null,
					'message'     => isset( $data['message'] ) ? (string) $data['message'] : '',
					'amount'      => isset( $data['amount'] ) ? (int) $data['amount'] : null,
					'source'      => 'inquiry.json (HTTP)',
					'raw'         => $http['raw'],
				);
			} elseif ( ! $http['ok'] ) {
				$result['inquiry']['http_error'] = $http['error'];
			}
		}

		if ( $gw['merchant'] !== '' ) {
			$result['unverified'] = ez_aref7_in_unverified_list( $gw['merchant'], $authority, $gw['sandbox'] );
		}
	}

	if ( $pm === 'WC_Zibal' && empty( $result['inquiry']['status'] ) && function_exists( 'ez_zibal_inquiry_for_order' ) ) {
		$zibal = ez_zibal_inquiry_for_order( $order );
		$result['inquiry'] = array(
			'gateway'   => 'zibal',
			'paid'      => ! empty( $zibal['paid'] ),
			'status'    => (string) ( $zibal['status'] ?? '' ),
			'status_fa' => ez_aref7_zp_status_label( (string) ( $zibal['status'] ?? '' ) ),
			'source'    => 'ez_zibal_inquiry_for_order',
			'raw'       => $zibal['raw'] ?? null,
		);
	}

	$result['notes'] = ez_aref7_summarize( $order, $result['local'], $result['inquiry'], $result['unverified'] );

	return $result;
}

$order_id  = isset( $_GET['order_id'] ) ? (int) $_GET['order_id'] : 0;
$authority = isset( $_GET['authority'] ) ? sanitize_text_field( wp_unslash( $_GET['authority'] ) ) : '';
$do_inquiry = isset( $_GET['inquiry'] ) && $_GET['inquiry'] === '1';
$show_raw   = isset( $_GET['raw'] ) && $_GET['raw'] === '1';

$page_url = get_permalink();
if ( ! $page_url ) {
	$page_url = home_url( '/aref-test-7/' );
}

$report = null;

if ( $do_inquiry ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ez_aref7_order_inquiry' ) ) {
		wp_die( 'Nonce نامعتبر است.', 'Forbidden', array( 'response' => 403 ) );
	}
	$report = ez_aref7_run_inquiry( $order_id, $authority );
	if ( ! empty( $report['order_id'] ) && $order_id <= 0 ) {
		$order_id = (int) $report['order_id'];
	}
}

$inquiry_url = add_query_arg(
	array_filter(
		array(
			'order_id'  => $order_id > 0 ? $order_id : null,
			'authority' => $authority !== '' ? $authority : null,
			'inquiry'   => '1',
			'raw'       => $show_raw ? '1' : null,
			'_wpnonce'  => wp_create_nonce( 'ez_aref7_order_inquiry' ),
		)
	),
	$page_url
);

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 1000px;">
	<h1 class="text-2xl font-bold mb-2">استعلام وضعیت سفارش</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		وضعیت را از <strong>inquiry درگاه</strong> (زرین‌پال / زیبال)،
		<strong>WooCommerce</strong>، <strong>wp_markting</strong> و
		<strong>unVerified</strong> (در صورت زرین‌پال) کنار هم می‌بینید.
	</p>

	<form method="get" class="mb-6 flex flex-wrap gap-4 items-end text-sm border border-gray-200 rounded-lg p-4">
		<label>
			شناسه سفارش
			<input type="number" name="order_id" min="1" value="<?php echo $order_id > 0 ? (int) $order_id : ''; ?>"
				class="border rounded px-2 py-1 w-32 mr-1" dir="ltr" placeholder="12345">
		</label>
		<label>
			Authority / Track (اختیاری)
			<input type="text" name="authority" value="<?php echo esc_attr( $authority ); ?>"
				class="border rounded px-2 py-1 w-72 mr-1 font-mono text-xs" dir="ltr"
				placeholder="A000... یا track_id زیبال">
		</label>
		<label class="flex items-center gap-1">
			<input type="checkbox" name="raw" value="1" <?php checked( $show_raw ); ?>>
			JSON خام درگاه
		</label>
		<input type="hidden" name="inquiry" value="1">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'ez_aref7_order_inquiry' ) ); ?>">
		<button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
			استعلام وضعیت
		</button>
	</form>

	<?php if ( $do_inquiry && is_array( $report ) ) : ?>

		<?php if ( ! empty( $report['error'] ) ) : ?>
			<p class="mb-4 text-red-700 bg-red-50 border border-red-200 rounded p-3 text-sm"><?php echo esc_html( (string) $report['error'] ); ?></p>
		<?php else : ?>

			<?php
			$inq    = (array) ( $report['inquiry'] ?? array() );
			$local  = (array) ( $report['local'] ?? array() );
			$wc     = (array) ( $local['wc'] ?? array() );
			$mkt    = (array) ( $local['markting'] ?? array() );
			$unv    = (array) ( $report['unverified'] ?? array() );
			$notes  = (array) ( $report['notes'] ?? array() );
			?>

			<div class="mb-6 grid gap-3 md:grid-cols-2">
				<div class="p-4 rounded-lg border border-indigo-200 bg-indigo-50 text-sm">
					<p class="font-semibold text-indigo-900 mb-2">وضعیت درگاه (inquiry)</p>
					<?php if ( ! empty( $inq['status'] ) ) : ?>
						<p><code dir="ltr" class="text-base font-bold"><?php echo esc_html( (string) $inq['status'] ); ?></code></p>
						<p class="text-gray-700 mt-1"><?php echo esc_html( (string) ( $inq['status_fa'] ?? '' ) ); ?></p>
						<p class="text-xs text-gray-500 mt-2">منبع: <?php echo esc_html( (string) ( $inq['source'] ?? '—' ) ); ?></p>
						<?php if ( isset( $inq['paid'] ) ) : ?>
							<p class="mt-1">paid: <strong><?php echo ! empty( $inq['paid'] ) ? 'بله' : 'خیر'; ?></strong></p>
						<?php endif; ?>
					<?php elseif ( ! empty( $inq['http_error'] ) ) : ?>
						<p class="text-red-700"><?php echo esc_html( (string) $inq['http_error'] ); ?></p>
					<?php else : ?>
						<p class="text-amber-800">استعلام درگاه انجام نشد (merchant/authority/توکن).</p>
					<?php endif; ?>
				</div>

				<div class="p-4 rounded-lg border border-gray-200 bg-gray-50 text-sm">
					<p class="font-semibold mb-2">وضعیت سایت</p>
					<ul class="space-y-1">
						<li><strong>WC:</strong>
							<?php echo ! empty( $wc['found'] ) ? ez_aref7_status_badge( (string) $wc['status'] ) : '—'; ?>
							<?php if ( ! empty( $wc['found'] ) ) : ?>
								| is_paid: <?php echo ! empty( $wc['is_paid'] ) ? 'بله' : 'خیر'; ?>
							<?php endif; ?>
						</li>
						<li><strong>markting:</strong>
							<?php
							if ( ! empty( $mkt['found'] ) ) {
								echo ez_aref7_status_badge( (string) ( $mkt['slug'] ?? $mkt['status'] ) );
							} else {
								echo '<span class="text-amber-700">ردیف ندارد</span>';
							}
							?>
						</li>
						<li><strong>booking:</strong> <?php echo ! empty( $local['booking']['exists'] ) ? 'ثبت شده' : 'ندارد'; ?></li>
						<li><strong>unVerified صف ZP:</strong>
							<?php echo ! empty( $unv['found'] ) ? '<span class="text-purple-700 font-bold">بله — در صف verify</span>' : 'خیر'; ?>
						</li>
					</ul>
				</div>
			</div>

			<?php if ( ! empty( $notes ) ) : ?>
				<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded text-sm text-amber-900">
					<strong>جمع‌بندی:</strong>
					<ul class="list-disc mr-5 mt-1 space-y-1">
						<?php foreach ( $notes as $note ) : ?>
							<li><?php echo esc_html( $note ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php else : ?>
				<p class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded p-3">ناهمگامی آشکاری در استعلام دیده نشد.</p>
			<?php endif; ?>

			<div class="mb-4 flex flex-wrap gap-3 text-sm">
				<a href="<?php echo esc_url( $inquiry_url ); ?>" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200">بروزرسانی</a>
				<?php if ( ! empty( $report['order_id'] ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'post.php?post=' . (int) $report['order_id'] . '&action=edit' ) ); ?>"
						class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300" target="_blank" rel="noopener">ادمین WC</a>
				<?php endif; ?>
			</div>

			<h2 class="text-lg font-semibold mb-2">جزئیات</h2>
			<table class="min-w-full text-sm border border-gray-200 rounded-lg mb-6">
				<tbody>
					<tr class="border-t">
						<th class="text-right py-2 px-3 bg-gray-50 w-44">order_id</th>
						<td class="py-2 px-3 font-mono" dir="ltr"><?php echo (int) $report['order_id']; ?></td>
					</tr>
					<tr class="border-t">
						<th class="text-right py-2 px-3 bg-gray-50">authority / track</th>
						<td class="py-2 px-3 font-mono text-xs break-all" dir="ltr"><?php echo esc_html( (string) ( $report['authority'] ?: '—' ) ); ?></td>
					</tr>
					<?php if ( ! empty( $wc['payment_method'] ) ) : ?>
						<tr class="border-t">
							<th class="text-right py-2 px-3 bg-gray-50">payment_method</th>
							<td class="py-2 px-3 font-mono" dir="ltr"><?php echo esc_html( (string) $wc['payment_method'] ); ?></td>
						</tr>
					<?php endif; ?>
					<?php if ( ! empty( $inq['code'] ) ) : ?>
						<tr class="border-t">
							<th class="text-right py-2 px-3 bg-gray-50">inquiry code</th>
							<td class="py-2 px-3" dir="ltr"><?php echo (int) $inq['code']; ?></td>
						</tr>
					<?php endif; ?>
					<?php if ( ! empty( $inq['message'] ) ) : ?>
						<tr class="border-t">
							<th class="text-right py-2 px-3 bg-gray-50">inquiry message</th>
							<td class="py-2 px-3"><?php echo esc_html( (string) $inq['message'] ); ?></td>
						</tr>
					<?php endif; ?>
					<?php if ( isset( $inq['amount'] ) && $inq['amount'] !== null ) : ?>
						<tr class="border-t">
							<th class="text-right py-2 px-3 bg-gray-50">amount (inquiry)</th>
							<td class="py-2 px-3" dir="ltr"><?php echo number_format( (int) $inq['amount'] ); ?> ریال</td>
						</tr>
					<?php endif; ?>
					<?php if ( ! empty( $wc['transaction_id'] ) ) : ?>
						<tr class="border-t">
							<th class="text-right py-2 px-3 bg-gray-50">ref / transaction</th>
							<td class="py-2 px-3 font-mono text-xs" dir="ltr"><?php echo esc_html( (string) $wc['transaction_id'] ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $show_raw && ! empty( $inq['raw'] ) ) : ?>
				<h2 class="text-lg font-semibold mb-2">پاسخ خام درگاه</h2>
				<pre class="text-[11px] bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto mb-6" dir="ltr"><?php
					echo esc_html( wp_json_encode( $inq['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
				?></pre>
			<?php endif; ?>

		<?php endif; ?>
	<?php endif; ?>

	<div class="text-xs text-gray-500 border-t pt-4 mt-8">
		<p class="mb-1">هسته: <code dir="ltr">ez_aref7_run_inquiry()</code> — درگاه: <code dir="ltr">ez_gateway_inquiry_for_order</code> یا <code dir="ltr">inquiry.json</code></p>
		<p>چک کامل WC/markting/booking: <code dir="ltr">page-aref-test-3.php</code> — مبلغ پرداختی: <code dir="ltr">page-aref-test-user.php</code></p>
	</div>
</main>

<?php
get_footer();
