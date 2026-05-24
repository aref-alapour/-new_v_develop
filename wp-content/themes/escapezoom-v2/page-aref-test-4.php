<?php
/**
 * Template Name: aref-test-4
 *
 * دریافت تا ۱۰۰ تراکنش unverified زرین‌پال (unVerified.json — حداکثر ۱۰۰ مورد طبق داکیومنت).
 * فقط مدیرکل. برگه با slug پیشنهادی: aref-test-4
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

const EZ_AREF4_MAX_ITEMS = 100;

/**
 * @return array{label:string,settings_key:string,merchant:string,access_token:string,sandbox:bool,terminal_id:int}
 */
function ez_aref4_gateway( string $gateway ): array {
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
		'access_token'  => isset( $settings['access_token'] ) ? trim( (string) $settings['access_token'] ) : '',
		'sandbox'       => isset( $settings['sandbox'] ) && $settings['sandbox'] === 'yes',
		'terminal_id'   => (int) apply_filters( 'ez_zp_page_terminal_id', $terminal_id, $gateway === 'co' ? 'WC_ZPal_co' : 'WC_ZPal' ),
	);
}

function ez_aref4_mask( string $value, int $visible = 8 ): string {
	$value = trim( $value );
	if ( $value === '' ) {
		return '—';
	}
	if ( strlen( $value ) <= $visible ) {
		return $value;
	}
	return substr( $value, 0, $visible ) . '…';
}

function ez_aref4_format_rial( ?int $rial ): string {
	if ( $rial === null || $rial <= 0 ) {
		return '—';
	}
	$toman = (int) floor( $rial / 10 );
	return number_format( $rial ) . ' ریال (' . number_format( $toman ) . ' تومان)';
}

function ez_aref4_unverified_url( string $merchant_id, bool $sandbox ): string {
	$base = function_exists( 'ez_zp_payment_api_base_url' )
		? ez_zp_payment_api_base_url( $sandbox )
		: ( $sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com' );
	return rtrim( $base, '/' ) . '/pg/v4/payment/unVerified.json';
}

/**
 * unVerified.json — حداکثر ۱۰۰ authority (داکیومنت زرین‌پال).
 *
 * @return array{ok:bool,code:int,message:string,authorities:array<int,array<string,mixed>>,raw:?array<string,mixed>,http_error:string}
 */
function ez_aref4_fetch_unverified( string $merchant_id, bool $sandbox, bool $include_raw = false ): array {
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
		return array_merge( $fetch, array( 'raw' => null, 'http_error' => '' ) );
	}

	$url      = ez_aref4_unverified_url( $merchant_id, $sandbox );
	$response = wp_remote_post(
		$url,
		array(
			'body'    => wp_json_encode( array( 'merchant_id' => $merchant_id ) ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'User-Agent'   => 'EscapeZoom/aref-test-4-unverified-100',
			),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $fail( 0, $response->get_error_message(), $response->get_error_message() );
	}

	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
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

/**
 * GraphQL filter:PAID = پرداخت‌شده ولی verify نشده (مکمل unVerified.json).
 *
 * @return array{ok:bool,sessions:array<int,array<string,mixed>>,error:string}
 */
function ez_aref4_fetch_graphql_paid( string $access_token, int $terminal_id, int $limit = EZ_AREF4_MAX_ITEMS ): array {
	$access_token = trim( $access_token );
	if ( $access_token === '' ) {
		return array( 'ok' => false, 'sessions' => array(), 'error' => 'access_token خالی است.' );
	}

	$limit = max( 1, min( EZ_AREF4_MAX_ITEMS, $limit ) );
	$fields = 'id status amount description fee created_at session_tries { id session_id payment_id status rrn card_pan created_at verify_time }';

	$auth = ( stripos( $access_token, 'bearer ' ) === 0 )
		? $access_token
		: 'Bearer ' . $access_token;

	$query_string = "query {Session (terminal_id:{$terminal_id},filter:PAID,limit:{$limit}){{$fields}}}";

	$response = wp_remote_post(
		'https://next.zarinpal.com/api/v4/graphql',
		array(
			'body'    => wp_json_encode( array( 'query' => $query_string ) ),
			'timeout' => 30,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
				'User-Agent'    => 'EscapeZoom/aref-test-4-graphql-paid',
				'Authorization' => $auth,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array( 'ok' => false, 'sessions' => array(), 'error' => $response->get_error_message() );
	}

	$raw = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $raw ) ) {
		return array( 'ok' => false, 'sessions' => array(), 'error' => 'پاسخ GraphQL نامعتبر.' );
	}

	if ( ! empty( $raw['errors'] ) ) {
		$auth_raw = $access_token;
		$response = wp_remote_post(
			'https://next.zarinpal.com/api/v4/graphql',
			array(
				'body'    => wp_json_encode( array( 'query' => $query_string ) ),
				'timeout' => 30,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'User-Agent'    => 'EscapeZoom/aref-test-4-graphql-paid',
					'Authorization' => $auth_raw,
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return array( 'ok' => false, 'sessions' => array(), 'error' => $response->get_error_message() );
		}
		$raw = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	}

	if ( ! is_array( $raw ) || ! empty( $raw['errors'] ) ) {
		$err = isset( $raw['errors'] ) ? wp_json_encode( $raw['errors'], JSON_UNESCAPED_UNICODE ) : 'GraphQL error';
		return array( 'ok' => false, 'sessions' => array(), 'error' => (string) $err );
	}

	$sessions = array();
	if ( isset( $raw['data']['Session'] ) && is_array( $raw['data']['Session'] ) ) {
		$session = $raw['data']['Session'];
		if ( isset( $session['id'] ) || isset( $session['description'] ) ) {
			$sessions = array( $session );
		} else {
			$sessions = $session;
		}
	}

	return array( 'ok' => true, 'sessions' => $sessions, 'error' => '' );
}

function ez_aref4_parse_order_id_from_description( string $description ): int {
	if ( $description === '' ) {
		return 0;
	}
	if ( preg_match( '/شماره\s*سفارش\s*[:\-]?\s*([0-9]+)/u', $description, $m ) ) {
		return (int) $m[1];
	}
	if ( preg_match( '/order[_\s-]*id\s*[:\-]?\s*([0-9]+)/iu', $description, $m ) ) {
		return (int) $m[1];
	}
	return 0;
}

/**
 * @param array<string, mixed> $item
 * @return array{authority:string,amount:?int,date:string,order_id:int,wc_status:string,wc_paid:bool}
 */
function ez_aref4_enrich_authority_row( array $item ): array {
	$auth = isset( $item['authority'] ) ? trim( (string) $item['authority'] ) : '';
	$amount = isset( $item['amount'] ) ? (int) $item['amount'] : null;
	$date = isset( $item['date'] ) ? (string) $item['date'] : '';

	$order_id = 0;
	$wc_status = '';
	$wc_paid = false;

	if ( $auth !== '' && function_exists( 'get_order_id_by_authority' ) ) {
		$order_id = (int) get_order_id_by_authority( $auth );
	}

	if ( $order_id > 0 && function_exists( 'wc_get_order' ) ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$wc_status = (string) $order->get_status();
			$wc_paid   = (bool) $order->is_paid();
		}
	}

	return array(
		'authority'  => $auth,
		'amount'     => $amount,
		'date'       => $date,
		'order_id'   => $order_id,
		'wc_status'  => $wc_status,
		'wc_paid'    => $wc_paid,
		'raw'        => $item,
	);
}

/**
 * @param array<string, mixed> $session
 * @return array{session_id:string,amount:?int,description:string,order_id:int,wc_status:string,status:string}
 */
function ez_aref4_enrich_session_row( array $session ): array {
	$desc = isset( $session['description'] ) ? (string) $session['description'] : '';
	$order_id = ez_aref4_parse_order_id_from_description( $desc );
	$wc_status = '';
	if ( $order_id > 0 && function_exists( 'wc_get_order' ) ) {
		$o = wc_get_order( $order_id );
		if ( $o ) {
			$wc_status = (string) $o->get_status();
		}
	}
	$sid = isset( $session['id'] ) ? (string) $session['id'] : '';
	return array(
		'session_id'  => $sid,
		'amount'      => isset( $session['amount'] ) ? (int) $session['amount'] : null,
		'description' => $desc,
		'order_id'    => $order_id,
		'wc_status'   => $wc_status,
		'status'      => isset( $session['status'] ) ? (string) $session['status'] : '',
	);
}

$gateway       = isset( $_GET['gateway'] ) && $_GET['gateway'] === 'co' ? 'co' : 'main';
$sandbox_force = isset( $_GET['sandbox'] ) && $_GET['sandbox'] === '1';
$show_raw      = isset( $_GET['raw'] ) && $_GET['raw'] === '1';
$with_graphql  = ! isset( $_GET['graphql'] ) || $_GET['graphql'] !== '0';
$do_fetch      = isset( $_GET['fetch'] ) && $_GET['fetch'] === '1';

$gw      = ez_aref4_gateway( $gateway );
$sandbox = $sandbox_force ? true : $gw['sandbox'];
$merchant = $gw['merchant'];

$page_url = get_permalink();
if ( ! $page_url ) {
	$page_url = home_url( '/aref-test-4/' );
}

$fetch_result  = null;
$graphql_result = null;
$rows          = array();
$graphql_rows  = array();

if ( $do_fetch ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ez_aref4_unverified_100' ) ) {
		wp_die( 'Nonce نامعتبر است.', 'Forbidden', array( 'response' => 403 ) );
	}

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
		$fetch_result = ez_aref4_fetch_unverified( $merchant, $sandbox, $show_raw );
	}

	if ( is_array( $fetch_result ) && ! empty( $fetch_result['ok'] ) ) {
		$authorities = array_slice( (array) ( $fetch_result['authorities'] ?? array() ), 0, EZ_AREF4_MAX_ITEMS );
		foreach ( $authorities as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$rows[] = ez_aref4_enrich_authority_row( $item );
		}
	}

	if ( $with_graphql && $gw['access_token'] !== '' ) {
		$graphql_result = ez_aref4_fetch_graphql_paid( $gw['access_token'], $gw['terminal_id'], EZ_AREF4_MAX_ITEMS );
		if ( ! empty( $graphql_result['ok'] ) ) {
			foreach ( (array) ( $graphql_result['sessions'] ?? array() ) as $session ) {
				if ( ! is_array( $session ) ) {
					continue;
				}
				$graphql_rows[] = ez_aref4_enrich_session_row( $session );
			}
		}
	}
}

$base_args = array(
	'gateway' => $gateway,
	'sandbox' => $sandbox_force ? '1' : '0',
	'raw'     => $show_raw ? '1' : '0',
	'graphql' => $with_graphql ? '1' : '0',
);

$fetch_url = add_query_arg(
	array_merge(
		$base_args,
		array(
			'fetch'    => '1',
			'_wpnonce' => wp_create_nonce( 'ez_aref4_unverified_100' ),
		)
	),
	$page_url
);

$api_url = $merchant !== '' ? ez_aref4_unverified_url( $merchant, $sandbox ) : '';

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 1200px;">
	<h1 class="text-2xl font-bold mb-2">زرین‌پال — تا ۱۰۰ سفارش unverified</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		طبق <a href="https://www.zarinpal.com/docs/paymentGateway/otherMethods/unVerified.html" target="_blank" rel="noopener">داکیومنت unVerified</a>،
		متد <code dir="ltr">unVerified.json</code> حداکثر <strong>۱۰۰ تراکنش آخر</strong> را که پرداخت شده‌اند ولی
		<strong>verify</strong> نشده‌اند برمی‌گرداند. وضعیت معادل در GraphQL: <code dir="ltr">filter:PAID</code>.
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
			اجبار sandbox
		</label>
		<label class="flex items-center gap-1">
			<input type="checkbox" name="graphql" value="1" <?php checked( $with_graphql ); ?>>
			هم‌زمان GraphQL PAID (limit=<?php echo (int) EZ_AREF4_MAX_ITEMS; ?>)
		</label>
		<label class="flex items-center gap-1">
			<input type="checkbox" name="raw" value="1" <?php checked( $show_raw ); ?>>
			JSON خام unVerified
		</label>
		<button type="submit" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">تنظیمات</button>
	</form>

	<ul class="mb-4 text-sm space-y-1 bg-gray-50 border border-gray-100 rounded p-3">
		<li><strong>درگاه:</strong> <?php echo esc_html( $gw['label'] ); ?></li>
		<li><strong>merchant:</strong> <code dir="ltr"><?php echo esc_html( ez_aref4_mask( $merchant, 12 ) ); ?></code></li>
		<li><strong>terminal_id:</strong> <code dir="ltr"><?php echo (int) $gw['terminal_id']; ?></code></li>
		<li><strong>محیط:</strong> <?php echo $sandbox ? 'sandbox' : 'production'; ?></li>
		<?php if ( $api_url !== '' ) : ?>
			<li><strong>URL:</strong> <code dir="ltr" class="text-xs break-all"><?php echo esc_html( $api_url ); ?></code></li>
		<?php endif; ?>
	</ul>

	<p class="mb-4">
		<a href="<?php echo esc_url( $fetch_url ); ?>"
			class="inline-block px-5 py-2 rounded font-medium bg-indigo-600 text-white hover:bg-indigo-700">
			دریافت لیست unverified (تا <?php echo (int) EZ_AREF4_MAX_ITEMS; ?>)
		</a>
	</p>

	<?php if ( is_array( $fetch_result ) ) : ?>
		<div class="mb-4 p-3 rounded text-sm <?php echo ! empty( $fetch_result['ok'] ) ? 'bg-green-50 border border-green-200 text-green-900' : 'bg-red-50 border border-red-200 text-red-900'; ?>">
			<?php if ( ! empty( $fetch_result['ok'] ) ) : ?>
				<strong>unVerified.json موفق</strong> — code=<?php echo (int) $fetch_result['code']; ?> |
				تعداد: <?php echo count( $rows ); ?>
				<?php if ( count( (array) ( $fetch_result['authorities'] ?? array() ) ) > EZ_AREF4_MAX_ITEMS ) : ?>
					(API برگرداند <?php echo count( (array) $fetch_result['authorities'] ); ?> — نمایش <?php echo (int) EZ_AREF4_MAX_ITEMS; ?> اول)
				<?php endif; ?>
			<?php else : ?>
				<strong>خطا</strong> — <?php echo esc_html( (string) $fetch_result['message'] ); ?>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $fetch_result['ok'] ) ) : ?>
			<?php if ( empty( $rows ) ) : ?>
				<p class="text-gray-600 text-sm mb-6">لیست خالی است — تراکنش unverified در صف verify نیست.</p>
			<?php else : ?>
				<h2 class="text-lg font-semibold mb-2">unVerified.json — <?php echo count( $rows ); ?> مورد</h2>
				<div class="overflow-x-auto border border-gray-200 rounded-lg mb-8">
					<table class="min-w-full text-xs border-collapse">
						<thead>
							<tr class="bg-gray-100">
								<th class="text-right py-2 px-2">#</th>
								<th class="text-right py-2 px-2">authority</th>
								<th class="text-right py-2 px-2">amount</th>
								<th class="text-right py-2 px-2">date</th>
								<th class="text-right py-2 px-2">order_id</th>
								<th class="text-right py-2 px-2">WC</th>
								<th class="text-right py-2 px-2">is_paid</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $i => $row ) : ?>
								<tr class="border-t border-gray-100">
									<td class="py-1 px-2"><?php echo (int) ( $i + 1 ); ?></td>
									<td class="py-1 px-2 font-mono text-[11px]" dir="ltr"><?php echo esc_html( $row['authority'] ); ?></td>
									<td class="py-1 px-2"><?php echo esc_html( ez_aref4_format_rial( $row['amount'] ) ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( $row['date'] !== '' ? $row['date'] : '—' ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr">
										<?php if ( ! empty( $row['order_id'] ) ) : ?>
											<a class="text-blue-600 underline" href="<?php echo esc_url( admin_url( 'post.php?post=' . (int) $row['order_id'] . '&action=edit' ) ); ?>" target="_blank" rel="noopener">
												<?php echo (int) $row['order_id']; ?>
											</a>
										<?php else : ?>
											<span class="text-gray-400">—</span>
										<?php endif; ?>
									</td>
									<td class="py-1 px-2"><?php echo esc_html( $row['wc_status'] !== '' ? $row['wc_status'] : '—' ); ?></td>
									<td class="py-1 px-2"><?php echo $row['wc_paid'] ? 'بله' : ( $row['order_id'] ? 'خیر' : '—' ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $show_raw && ! empty( $fetch_result['raw'] ) ) : ?>
			<h2 class="text-lg font-semibold mb-2">پاسخ خام unVerified.json</h2>
			<pre class="text-[11px] bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto mb-6" dir="ltr"><?php
				echo esc_html( wp_json_encode( $fetch_result['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			?></pre>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $with_graphql && $do_fetch ) : ?>
		<?php if ( $gw['access_token'] === '' ) : ?>
			<p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-3 mb-6">
				GraphQL رد شد: access_token در تنظیمات درگاه خالی است.
			</p>
		<?php elseif ( is_array( $graphql_result ) ) : ?>
			<div class="mb-4 p-3 rounded text-sm <?php echo ! empty( $graphql_result['ok'] ) ? 'bg-slate-50 border border-slate-200' : 'bg-red-50 border border-red-200 text-red-900'; ?>">
				<?php if ( ! empty( $graphql_result['ok'] ) ) : ?>
					<strong>GraphQL Session filter:PAID</strong> — <?php echo count( $graphql_rows ); ?> سشن
				<?php else : ?>
					<strong>GraphQL خطا:</strong> <?php echo esc_html( (string) $graphql_result['error'] ); ?>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $graphql_rows ) ) : ?>
				<h2 class="text-lg font-semibold mb-2">GraphQL PAID — <?php echo count( $graphql_rows ); ?> مورد</h2>
				<div class="overflow-x-auto border border-gray-200 rounded-lg mb-6">
					<table class="min-w-full text-xs border-collapse">
						<thead>
							<tr class="bg-gray-100">
								<th class="text-right py-2 px-2">#</th>
								<th class="text-right py-2 px-2">session_id</th>
								<th class="text-right py-2 px-2">status</th>
								<th class="text-right py-2 px-2">amount</th>
								<th class="text-right py-2 px-2">order_id</th>
								<th class="text-right py-2 px-2">description</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $graphql_rows as $i => $grow ) : ?>
								<tr class="border-t border-gray-100">
									<td class="py-1 px-2"><?php echo (int) ( $i + 1 ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( $grow['session_id'] ); ?></td>
									<td class="py-1 px-2"><?php echo esc_html( $grow['status'] ); ?></td>
									<td class="py-1 px-2"><?php echo esc_html( ez_aref4_format_rial( $grow['amount'] ) ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo $grow['order_id'] > 0 ? (int) $grow['order_id'] : '—'; ?></td>
									<td class="py-1 px-2 text-[11px] max-w-xs truncate" title="<?php echo esc_attr( $grow['description'] ); ?>">
										<?php echo esc_html( $grow['description'] !== '' ? $grow['description'] : '—' ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>

	<div class="text-xs text-gray-500 border-t pt-4 mt-8">
		<p class="mb-1">مرجع: <code dir="ltr">POST /pg/v4/payment/unVerified.json</code> با <code dir="ltr">merchant_id</code> — حداکثر ۱۰۰ authority.</p>
		<p>کد production: <code dir="ltr">ez_zp_fetch_unverified_authorities</code> — UI ساده‌تر: <code dir="ltr">page-aref-test-2.php</code></p>
	</div>
</main>

<?php
get_footer();
