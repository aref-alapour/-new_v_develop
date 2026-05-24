<?php
/**
 * Template Name: aref-test-2
 *
 * استعلام لیست تراکنش‌های در صف verify از ZarinPal: unVerified.json
 * فقط مدیرکل. در وردپرس یک برگه بسازید و این قالب را انتخاب کنید (slug پیشنهادی: aref-test-2).
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

/**
 * @return array{label:string,settings_key:string,merchant:string,sandbox:bool,terminal_id:int}
 */
function ez_aref2_gateway( string $gateway ): array {
	$gateway = $gateway === 'co' ? 'co' : 'main';

	if ( $gateway === 'co' ) {
		$settings_key = 'woocommerce_WC_ZPal_co_settings';
		$label        = 'ZarinPal co (WC_ZPal_co)';
		$terminal_id  = 590543;
	} else {
		$settings_key = 'woocommerce_WC_ZPal_settings';
		$label        = 'ZarinPal main (WC_ZPal)';
		$terminal_id  = 534598;
	}

	$settings = get_option( $settings_key, array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	return array(
		'label'         => $label,
		'settings_key'  => $settings_key,
		'merchant'      => isset( $settings['merchantcode'] ) ? trim( (string) $settings['merchantcode'] ) : '',
		'sandbox'       => isset( $settings['sandbox'] ) && $settings['sandbox'] === 'yes',
		'terminal_id'   => (int) apply_filters( 'ez_zp_page_terminal_id', $terminal_id, $gateway === 'co' ? 'WC_ZPal_co' : 'WC_ZPal' ),
	);
}

function ez_aref2_mask( string $value, int $visible = 8 ): string {
	$value = trim( $value );
	if ( $value === '' ) {
		return '—';
	}
	if ( strlen( $value ) <= $visible ) {
		return $value;
	}
	return substr( $value, 0, $visible ) . '…';
}

function ez_aref2_format_rial( ?int $rial ): string {
	if ( $rial === null || $rial <= 0 ) {
		return '—';
	}
	$toman = (int) floor( $rial / 10 );
	return number_format( $rial ) . ' ریال (' . number_format( $toman ) . ' تومان)';
}

function ez_aref2_unverified_url( string $merchant_id, bool $sandbox ): string {
	$base = function_exists( 'ez_zp_payment_api_base_url' )
		? ez_zp_payment_api_base_url( $sandbox )
		: ( $sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com' );
	return rtrim( $base, '/' ) . '/pg/v4/payment/unVerified.json';
}

/**
 * @return array{ok:bool,code:int,message:string,authorities:array<int,array<string,mixed>>,raw:?array<string,mixed>,http_error:string}
 */
function ez_aref2_fetch_unverified( string $merchant_id, bool $sandbox, bool $include_raw = false ): array {
	$fail = static function ( int $code, string $message, string $http_error = '' ) use ( $include_raw ): array {
		return array(
			'ok'          => false,
			'code'        => $code,
			'message'     => $message,
			'authorities' => array(),
			'raw'         => $include_raw ? null : null,
			'http_error'  => $http_error,
		);
	};

	$merchant_id = trim( $merchant_id );
	if ( $merchant_id === '' ) {
		return $fail( 0, 'merchant_id خالی است.' );
	}

	if ( function_exists( 'ez_zp_fetch_unverified_authorities' ) && ! $include_raw ) {
		$fetch = ez_zp_fetch_unverified_authorities( $merchant_id, $sandbox );
		return array_merge(
			$fetch,
			array( 'raw' => null, 'http_error' => '' )
		);
	}

	$url      = ez_aref2_unverified_url( $merchant_id, $sandbox );
	$response = wp_remote_post(
		$url,
		array(
			'body'    => wp_json_encode( array( 'merchant_id' => $merchant_id ) ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'User-Agent'   => 'EscapeZoom/aref-test-2-unverified',
			),
			'timeout' => 25,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $fail( 0, $response->get_error_message(), $response->get_error_message() );
	}

	$raw_body = (string) wp_remote_retrieve_body( $response );
	$body     = json_decode( $raw_body, true );
	if ( ! is_array( $body ) || ! isset( $body['data'] ) || ! is_array( $body['data'] ) ) {
		return $fail( 0, 'پاسخ JSON نامعتبر.', 'invalid json' );
	}

	$data = $body['data'];
	$code = (int) ( $data['code'] ?? 0 );
	$msg  = (string) ( $data['message'] ?? '' );

	if ( $code !== 100 ) {
		return array(
			'ok'          => false,
			'code'        => $code,
			'message'     => $msg,
			'authorities' => array(),
			'raw'         => $include_raw ? $body : null,
			'http_error'  => '',
		);
	}

	$authorities = isset( $data['authorities'] ) && is_array( $data['authorities'] )
		? $data['authorities']
		: array();

	return array(
		'ok'          => true,
		'code'        => $code,
		'message'     => $msg,
		'authorities' => $authorities,
		'raw'         => $include_raw ? $body : null,
		'http_error'  => '',
	);
}

$gateway       = isset( $_GET['gateway'] ) && $_GET['gateway'] === 'co' ? 'co' : 'main';
$sandbox_force = isset( $_GET['sandbox'] ) && $_GET['sandbox'] === '1';
$filter_auth   = isset( $_GET['authority'] ) ? sanitize_text_field( wp_unslash( $_GET['authority'] ) ) : '';
$do_fetch      = isset( $_GET['fetch'] ) && $_GET['fetch'] === '1';
$show_raw      = isset( $_GET['raw'] ) && $_GET['raw'] === '1';
$do_cron       = isset( $_GET['run_cron'] ) && $_GET['run_cron'] === '1';

$gw       = ez_aref2_gateway( $gateway );
$sandbox  = $sandbox_force ? true : $gw['sandbox'];
$merchant = $gw['merchant'];

$page_url = get_permalink();
if ( ! $page_url ) {
	$page_url = home_url( '/aref-test-2/' );
}

$fetch_result = null;
$cron_log     = '';

if ( $do_fetch || $do_cron ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ez_aref2_unverified' ) ) {
		wp_die( 'Nonce نامعتبر است.', 'Forbidden', array( 'response' => 403 ) );
	}
}

if ( $do_cron && $merchant !== '' ) {
	if ( function_exists( 'ez_zp_process_unverified_for_settings' ) ) {
		ob_start();
		ez_zp_process_unverified_for_settings( $gw['settings_key'], $gateway );
		$cron_log = trim( (string) ob_get_clean() );
		if ( $cron_log === '' ) {
			$cron_log = 'ez_zp_process_unverified_for_settings اجرا شد. لاگ‌ها در saeed_store (zp_unverified_' . $gateway . ') ثبت می‌شوند.';
		}
	} else {
		$cron_log = 'تابع ez_zp_process_unverified_for_settings یافت نشد.';
	}
	$do_fetch = true;
}

if ( $do_fetch ) {
	if ( $merchant === '' ) {
		$fetch_result = array(
			'ok'          => false,
			'code'        => 0,
			'message'     => 'merchantcode در تنظیمات درگاه خالی است.',
			'authorities' => array(),
			'raw'         => null,
			'http_error'  => '',
		);
	} else {
		$fetch_result = ez_aref2_fetch_unverified( $merchant, $sandbox, $show_raw );
	}
}

$base_args = array(
	'gateway' => $gateway,
	'sandbox' => $sandbox_force ? '1' : '0',
	'raw'     => $show_raw ? '1' : '0',
);
if ( $filter_auth !== '' ) {
	$base_args['authority'] = $filter_auth;
}

$fetch_url = add_query_arg(
	array_merge(
		$base_args,
		array(
			'fetch'    => '1',
			'_wpnonce' => wp_create_nonce( 'ez_aref2_unverified' ),
		)
	),
	$page_url
);

$cron_url = add_query_arg(
	array_merge(
		$base_args,
		array(
			'run_cron' => '1',
			'fetch'    => '1',
			'_wpnonce' => wp_create_nonce( 'ez_aref2_unverified' ),
		)
	),
	$page_url
);

$api_url = $merchant !== '' ? ez_aref2_unverified_url( $merchant, $sandbox ) : '';

$rows = array();
if ( is_array( $fetch_result ) && ! empty( $fetch_result['ok'] ) ) {
	foreach ( (array) ( $fetch_result['authorities'] ?? array() ) as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$auth = isset( $item['authority'] ) ? trim( (string) $item['authority'] ) : '';
		if ( $filter_auth !== '' && $auth !== $filter_auth ) {
			continue;
		}
		$order_id = null;
		$status   = '';
		if ( $auth !== '' && function_exists( 'get_order_id_by_authority' ) ) {
			$order_id = get_order_id_by_authority( $auth );
			if ( $order_id && function_exists( 'wc_get_order' ) ) {
				$o = wc_get_order( $order_id );
				if ( $o ) {
					$status = (string) $o->get_status();
				}
			}
		}
		$rows[] = array(
			'item'     => $item,
			'authority'=> $auth,
			'amount'   => isset( $item['amount'] ) ? (int) $item['amount'] : null,
			'order_id' => $order_id,
			'status'   => $status,
		);
	}
}

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 1100px;">
	<h1 class="text-2xl font-bold mb-2">زرین‌پال — unVerified.json</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		لیست authorityهایی که در زرین‌پال <strong>پرداخت شده</strong> ولی هنوز
		<strong>verify</strong> نشده‌اند (همان منبعی که cron لایه ۳ استفاده می‌کند).
	</p>

	<form method="get" class="mb-6 flex flex-wrap gap-4 items-end text-sm border border-gray-200 rounded-lg p-4">
		<label>
			درگاه
			<select name="gateway" class="border rounded px-2 py-1 mr-1">
				<option value="main" <?php selected( $gateway, 'main' ); ?>>main — WC_ZPal</option>
				<option value="co" <?php selected( $gateway, 'co' ); ?>>co — WC_ZPal_co</option>
			</select>
		</label>
		<label class="flex items-center gap-1">
			<input type="checkbox" name="sandbox" value="1" <?php checked( $sandbox_force ); ?>>
			اجبار sandbox (حتی اگر درگاه production باشد)
		</label>
		<label class="flex items-center gap-1">
			<input type="checkbox" name="raw" value="1" <?php checked( $show_raw ); ?>>
			نمایش JSON خام
		</label>
		<label>
			فیلتر authority (اختیاری)
			<input type="text" name="authority" value="<?php echo esc_attr( $filter_auth ); ?>"
				class="border rounded px-2 py-1 w-72 mr-1 font-mono text-xs" dir="ltr"
				placeholder="A000000000000000000000000000...">
		</label>
		<button type="submit" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">بروزرسانی تنظیمات</button>
	</form>

	<ul class="mb-4 text-sm space-y-1 bg-gray-50 border border-gray-100 rounded p-3">
		<li><strong>درگاه:</strong> <?php echo esc_html( $gw['label'] ); ?></li>
		<li><strong>merchant:</strong> <code dir="ltr"><?php echo esc_html( ez_aref2_mask( $merchant, 12 ) ); ?></code>
			<?php if ( $merchant === '' ) : ?>
				<span class="text-red-600"> — خالی؛ WooCommerce → Payments را پر کنید.</span>
			<?php endif; ?>
		</li>
		<li><strong>محیط API:</strong> <?php echo $sandbox ? 'sandbox' : 'production'; ?>
			<?php if ( ! $sandbox_force && $gw['sandbox'] ) : ?>
				<span class="text-gray-500">(از تنظیمات درگاه)</span>
			<?php elseif ( $sandbox_force ) : ?>
				<span class="text-amber-700">(اجبار با چک‌باکس)</span>
			<?php endif; ?>
		</li>
		<?php if ( $api_url !== '' ) : ?>
			<li><strong>URL:</strong> <code dir="ltr" class="text-xs break-all"><?php echo esc_html( $api_url ); ?></code></li>
		<?php endif; ?>
	</ul>

	<p class="mb-4 flex flex-wrap gap-3">
		<a href="<?php echo esc_url( $fetch_url ); ?>"
			class="inline-block px-5 py-2 rounded font-medium bg-indigo-600 text-white hover:bg-indigo-700">
			فراخوانی unVerified.json
		</a>
		<?php if ( $merchant !== '' && function_exists( 'ez_zp_process_unverified_for_settings' ) ) : ?>
			<a href="<?php echo esc_url( $cron_url ); ?>"
				class="inline-block px-5 py-2 rounded font-medium bg-amber-600 text-white hover:bg-amber-700"
				onclick="return confirm('اجرای همان منطق cron (verify دسته‌ای تا ۲۰ authority)؟');">
				اجرای cron verify (<?php echo esc_html( $gateway ); ?>)
			</a>
		<?php endif; ?>
	</p>

	<?php if ( $cron_log !== '' ) : ?>
		<p class="mb-4 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-3"><?php echo esc_html( $cron_log ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $fetch_result ) ) : ?>
		<div class="mb-4 p-3 rounded text-sm <?php echo ! empty( $fetch_result['ok'] ) ? 'bg-green-50 border border-green-200 text-green-900' : 'bg-red-50 border border-red-200 text-red-900'; ?>">
			<?php if ( ! empty( $fetch_result['ok'] ) ) : ?>
				<strong>موفق</strong> — code=<?php echo (int) $fetch_result['code']; ?>:
				<?php echo esc_html( (string) $fetch_result['message'] ); ?>
				| تعداد authority: <?php echo count( $rows ); ?>
				<?php if ( $filter_auth !== '' ) : ?>
					(بعد از فیلتر «<?php echo esc_html( ez_aref2_mask( $filter_auth, 16 ) ); ?>»)
				<?php else : ?>
					(کل لیست: <?php echo count( (array) ( $fetch_result['authorities'] ?? array() ) ); ?>)
				<?php endif; ?>
			<?php else : ?>
				<strong>خطا</strong> — code=<?php echo (int) $fetch_result['code']; ?>:
				<?php echo esc_html( (string) $fetch_result['message'] ); ?>
				<?php if ( ! empty( $fetch_result['http_error'] ) ) : ?>
					<br><span dir="ltr"><?php echo esc_html( (string) $fetch_result['http_error'] ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $fetch_result['ok'] ) ) : ?>
			<?php if ( empty( $rows ) ) : ?>
				<p class="text-gray-600 text-sm mb-4">لیست خالی است — تراکنش در صف verify نیست (یا فیلتر authority چیزی برنگرداند).</p>
			<?php else : ?>
				<div class="overflow-x-auto border border-gray-200 rounded-lg mb-6">
					<table class="min-w-full text-xs border-collapse">
						<thead>
							<tr class="bg-gray-100">
								<th class="text-right py-2 px-2">#</th>
								<th class="text-right py-2 px-2">authority</th>
								<th class="text-right py-2 px-2">amount</th>
								<th class="text-right py-2 px-2">order_id</th>
								<th class="text-right py-2 px-2">وضعیت WC</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $i => $row ) : ?>
								<tr class="border-t border-gray-100">
									<td class="py-1 px-2"><?php echo (int) ( $i + 1 ); ?></td>
									<td class="py-1 px-2 font-mono text-[11px]" dir="ltr"><?php echo esc_html( $row['authority'] ); ?></td>
									<td class="py-1 px-2"><?php echo esc_html( ez_aref2_format_rial( $row['amount'] ) ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr">
										<?php if ( ! empty( $row['order_id'] ) ) : ?>
											<a class="text-blue-600 underline" href="<?php echo esc_url( admin_url( 'post.php?post=' . (int) $row['order_id'] . '&action=edit' ) ); ?>" target="_blank" rel="noopener">
												<?php echo (int) $row['order_id']; ?>
											</a>
										<?php else : ?>
											<span class="text-gray-400">—</span>
										<?php endif; ?>
									</td>
									<td class="py-1 px-2"><?php echo esc_html( $row['status'] !== '' ? $row['status'] : '—' ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $show_raw && ! empty( $fetch_result['raw'] ) ) : ?>
			<h2 class="text-lg font-semibold mb-2">پاسخ خام API</h2>
			<pre class="text-[11px] bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto mb-6" dir="ltr"><?php
				echo esc_html( wp_json_encode( $fetch_result['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			?></pre>
		<?php elseif ( $show_raw && $do_fetch ) : ?>
			<p class="text-sm text-gray-500 mb-4">برای JSON خام، درخواست مستقیم HTTP انجام شد؛ اگر خالی است از مسیر ez_zp_fetch_unverified_authorities بدون raw استفاده شده.</p>
			<?php
			if ( $merchant !== '' ) {
				$raw_fetch = ez_aref2_fetch_unverified( $merchant, $sandbox, true );
				if ( ! empty( $raw_fetch['raw'] ) ) :
					?>
					<pre class="text-[11px] bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto mb-6" dir="ltr"><?php
						echo esc_html( wp_json_encode( $raw_fetch['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
					?></pre>
					<?php
				endif;
			}
			?>
		<?php endif; ?>
	<?php endif; ?>

	<div class="text-xs text-gray-500 border-t pt-4 mt-8">
		<p class="mb-1">مرجع کد: <code dir="ltr">ez_zp_fetch_unverified_authorities</code> در
			<code dir="ltr">inc/saeed-codes.php</code> — cron:
			<code dir="ltr">zarinpal_paid_transactions_process</code> /
			<code dir="ltr">zarinpal_co_paid_transactions_process</code>.
		</p>
		<p>استعلام تک‌سفارش با inquiry/GraphQL: قالب <strong>ZarinPal Order Inquiry</strong> (<code dir="ltr">page-aref-test-user.php</code>).</p>
	</div>
</main>

<?php
get_footer();
