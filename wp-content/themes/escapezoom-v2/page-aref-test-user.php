<?php
/**
 * Template Name: ZarinPal Order Inquiry
 *
 * استعلام مبلغ و جزئیات پرداخت یک سفارش از زرین‌پال (inquiry + GraphQL Session + unVerified).
 * فقط مدیرکل. در وردپرس یک برگه بسازید و این قالب را انتخاب کنید.
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

/**
 * @return array{ok:bool,http_code:int,raw:array<string,mixed>|null,error:string}
 */
function ez_zp_page_inquiry_api( string $merchant_id, string $authority, bool $sandbox ): array {
	$merchant_id = trim( $merchant_id );
	$authority   = trim( $authority );
	if ( $merchant_id === '' || $authority === '' ) {
		return array( 'ok' => false, 'http_code' => 0, 'raw' => null, 'error' => 'merchant_id یا authority خالی است.' );
	}

	$base = function_exists( 'ez_zp_payment_api_base_url' )
		? ez_zp_payment_api_base_url( $sandbox )
		: ( $sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com' );

	$url  = $base . '/pg/v4/payment/inquiry.json';
	$args = array(
		'body'    => wp_json_encode(
			array(
				'merchant_id' => $merchant_id,
				'authority'     => $authority,
			)
		),
		'timeout' => 20,
		'headers' => array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
			'User-Agent'   => 'EscapeZoom/zp-order-inquiry-page',
		),
	);

	$response = wp_remote_post( $url, $args );
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
 * @return array{ok:bool,http_code:int,raw:array<string,mixed>|null,error:string}
 */
function ez_zp_page_zp_post_json( string $url, array $payload, array $headers = array() ): array {
	$args = array(
		'body'    => wp_json_encode( $payload ),
		'timeout' => 20,
		'headers' => array_merge(
			array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'User-Agent'   => 'EscapeZoom/zp-order-inquiry-page',
			),
			$headers
		),
	);
	$response = wp_remote_post( $url, $args );
	if ( is_wp_error( $response ) ) {
		return array( 'ok' => false, 'http_code' => 0, 'raw' => null, 'error' => $response->get_error_message() );
	}
	$http_code = (int) wp_remote_retrieve_response_code( $response );
	$body      = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) ) {
		return array( 'ok' => false, 'http_code' => $http_code, 'raw' => null, 'error' => 'پاسخ JSON نامعتبر.' );
	}
	if ( isset( $body['message'] ) && ! isset( $body['data'] ) && empty( $body['errors'] ) ) {
		return array(
			'ok'        => false,
			'http_code' => $http_code,
			'raw'       => $body,
			'error'     => (string) $body['message'],
		);
	}
	return array( 'ok' => true, 'http_code' => $http_code, 'raw' => $body, 'error' => '' );
}

function ez_zp_page_graphql_auth_header( string $access_token ): string {
	$access_token = trim( $access_token );
	if ( stripos( $access_token, 'bearer ' ) === 0 ) {
		return $access_token;
	}
	return 'Bearer ' . $access_token;
}

/**
 * @return array<int, array<string, mixed>>
 */
function ez_zp_page_graphql_normalize_sessions( array $raw ): array {
	if ( ! isset( $raw['data']['Session'] ) ) {
		return array();
	}
	$session = $raw['data']['Session'];
	if ( ! is_array( $session ) ) {
		return array();
	}
	if ( $session === array() ) {
		return array();
	}
	// یک سشن تکی (با Session(id:…)) آرایه associative است؛ لیست ترمینال لیست عددی.
	if ( isset( $session['id'] ) || isset( $session['description'] ) || isset( $session['amount'] ) ) {
		return array( $session );
	}
	return $session;
}

/**
 * GraphQL با query اینلاین (مثل cron در saeed-codes) — variables باعث Server Error می‌شود.
 *
 * @return array{ok:bool,sessions:array<int,array<string,mixed>>,raw:?array<string,mixed>,error:string,auth_mode:string}
 */
function ez_zp_page_graphql_inline_query( string $access_token, string $query_string, string $auth_mode = 'bearer' ): array {
	$access_token = trim( $access_token );
	if ( $access_token === '' ) {
		return array( 'ok' => false, 'sessions' => array(), 'raw' => null, 'error' => 'access_token خالی', 'auth_mode' => $auth_mode );
	}

	$auth = ( $auth_mode === 'raw' )
		? $access_token
		: ez_zp_page_graphql_auth_header( $access_token );

	$call = ez_zp_page_zp_post_json(
		'https://next.zarinpal.com/api/v4/graphql',
		array( 'query' => $query_string ),
		array( 'Authorization' => $auth )
	);

	if ( ! $call['ok'] || ! is_array( $call['raw'] ) ) {
		return array(
			'ok'        => false,
			'sessions'  => array(),
			'raw'       => $call['raw'],
			'error'     => $call['error'] !== '' ? $call['error'] : 'GraphQL ناموفق',
			'auth_mode' => $auth_mode,
		);
	}

	if ( ! empty( $call['raw']['errors'] ) ) {
		$err = is_array( $call['raw']['errors'] ) ? wp_json_encode( $call['raw']['errors'], JSON_UNESCAPED_UNICODE ) : 'GraphQL error';
		return array( 'ok' => false, 'sessions' => array(), 'raw' => $call['raw'], 'error' => $err, 'auth_mode' => $auth_mode );
	}

	return array(
		'ok'        => true,
		'sessions'  => ez_zp_page_graphql_normalize_sessions( $call['raw'] ),
		'raw'       => $call['raw'],
		'error'     => '',
		'auth_mode' => $auth_mode,
	);
}

function ez_zp_page_session_matches_order( array $session, int $order_id, string $authority, string $ref_id ): bool {
	$oid  = (string) $order_id;
	$desc = isset( $session['description'] ) ? (string) $session['description'] : '';
	if ( $desc !== '' && ( strpos( $desc, $oid ) !== false || ( $authority !== '' && strpos( $desc, $authority ) !== false ) ) ) {
		return true;
	}
	$sid = isset( $session['id'] ) ? (string) $session['id'] : '';
	if ( $ref_id !== '' && $sid !== '' && $sid === $ref_id ) {
		return true;
	}
	return false;
}

/**
 * @param array<int, array<string, mixed>> $sessions
 * @return array<int, array<string, mixed>>
 */
function ez_zp_page_dedupe_sessions( array $sessions ): array {
	$out = array();
	$seen = array();
	foreach ( $sessions as $session ) {
		if ( ! is_array( $session ) ) {
			continue;
		}
		$key = isset( $session['id'] ) ? 'id:' . $session['id'] : 'd:' . ( $session['description'] ?? '' ) . ':' . ( $session['amount'] ?? '' );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$out[]        = $session;
	}
	return $out;
}

/**
 * @return array{sessions:array<int,array<string,mixed>>,calls:array<string,array<string,mixed>>}
 */
function ez_zp_page_fetch_all_zp_sessions( array $gw, WC_Order $order, string $authority ): array {
	$terminal_id = (int) $gw['terminal_id'];
	$token       = (string) $gw['access_token'];
	$order_id    = (int) $order->get_id();
	$ref_id      = preg_replace( '/\D/', '', (string) $order->get_transaction_id() );
	$sessions    = array();
	$calls       = array();

	$session_fields = 'description amount fee session_tries { id session_id payment_id payer_ip init_time verify_time status rrn card_pan created_at }';

	$queries = array(
		'cron_paid_bearer'     => "query {Session (terminal_id:{$terminal_id},filter:PAID,limit:80){{$session_fields}}}",
		'cron_verified_bearer' => "query {Session (terminal_id:{$terminal_id},filter:VERIFIED,limit:80){{$session_fields}}}",
		'cron_all_bearer'      => "query {Session (terminal_id:{$terminal_id},limit:80){{$session_fields}}}",
	);

	if ( $ref_id !== '' ) {
		$queries['session_id_bearer'] = "query {Session (id:{$ref_id}){{$session_fields}}}";
	}

	foreach ( $queries as $key => $query_string ) {
		$res = ez_zp_page_graphql_inline_query( $token, $query_string, 'bearer' );
		if ( ! $res['ok'] ) {
			$res_raw           = ez_zp_page_graphql_inline_query( $token, $query_string, 'raw' );
			$res_raw['note']   = 'fallback raw Authorization (مثل cron)';
			$res               = $res_raw['ok'] ? $res_raw : $res;
		}
		$calls[ $key ] = $res;
		if ( ! $res['ok'] ) {
			continue;
		}
		foreach ( $res['sessions'] as $session ) {
			if ( ! is_array( $session ) ) {
				continue;
			}
			if ( $key === 'session_id_bearer' || ez_zp_page_session_matches_order( $session, $order_id, $authority, $ref_id ) ) {
				$sessions[] = $session;
			}
		}
	}

	return array(
		'sessions' => ez_zp_page_dedupe_sessions( $sessions ),
		'calls'    => $calls,
	);
}

/**
 * جستجوی authority در لیست unVerified (مبلغ پرداخت‌شدهٔ همان authority).
 *
 * @return array{found:bool,amount:?int,item:?array<string,mixed>,raw:?array<string,mixed>}
 */
function ez_zp_page_unverified_lookup( string $merchant_id, string $authority, bool $sandbox ): array {
	$empty = array( 'found' => false, 'amount' => null, 'item' => null, 'raw' => null );
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
		$item_auth = isset( $item['authority'] ) ? trim( (string) $item['authority'] ) : '';
		if ( $item_auth === $authority ) {
			return array(
				'found'  => true,
				'amount' => isset( $item['amount'] ) ? (int) $item['amount'] : null,
				'item'   => $item,
				'raw'    => null,
			);
		}
	}
	return $empty;
}

/**
 * @return array{settings_key:string,merchant:string,sandbox:bool,access_token:string,fee_payer:string,terminal_id:int}
 */
function ez_zp_page_gateway_settings( string $payment_method ): array {
	$settings_key = ( $payment_method === 'WC_ZPal_co' )
		? 'woocommerce_WC_ZPal_co_settings'
		: 'woocommerce_WC_ZPal_settings';
	$settings     = get_option( $settings_key, array() );

	$terminal_id = ( $payment_method === 'WC_ZPal_co' ) ? 590543 : 534598;

	return array(
		'settings_key' => $settings_key,
		'merchant'     => isset( $settings['merchantcode'] ) ? (string) $settings['merchantcode'] : '',
		'sandbox'      => isset( $settings['sandbox'] ) && $settings['sandbox'] === 'yes',
		'access_token' => isset( $settings['access_token'] ) ? (string) $settings['access_token'] : '',
		'fee_payer'    => isset( $settings['fee_payer'] ) ? (string) $settings['fee_payer'] : 'merchant',
		'terminal_id'  => (int) apply_filters( 'ez_zp_page_terminal_id', $terminal_id, $payment_method ),
	);
}

function ez_zp_page_format_rial( ?int $rial ): string {
	if ( $rial === null || $rial <= 0 ) {
		return '—';
	}
	$toman = (int) floor( $rial / 10 );
	return number_format( $rial ) . ' ریال (' . number_format( $toman ) . ' تومان)';
}

/**
 * بهترین تخمین «مبلغی که کاربر پرداخت کرده» از چند منبع API.
 *
 * @param array<string,mixed> $sources
 * @return array{best_rial:?int,best_label:string,sources:array<string,mixed>}
 */
function ez_zp_page_resolve_paid_amount( array $sources ): array {
	$candidates = array();

	if ( ! empty( $sources['inquiry']['amount'] ) ) {
		$candidates[] = array(
			'rial'  => (int) $sources['inquiry']['amount'],
			'label' => 'inquiry.json',
		);
	}
	if ( ! empty( $sources['unverified']['amount'] ) ) {
		$candidates[] = array(
			'rial'  => (int) $sources['unverified']['amount'],
			'label' => 'unVerified.json',
		);
	}
	if ( ! empty( $sources['fee_suggested'] ) ) {
		$candidates[] = array(
			'rial'  => (int) $sources['fee_suggested'],
			'label' => 'متای _zarinpal_fee_data (زمان درخواست درگاه)',
		);
	}
	if ( ! empty( $sources['graphql_sessions'] ) && is_array( $sources['graphql_sessions'] ) ) {
		foreach ( $sources['graphql_sessions'] as $idx => $session ) {
			if ( ! is_array( $session ) ) {
				continue;
			}
			$amount = isset( $session['amount'] ) ? (int) $session['amount'] : 0;
			$fee    = isset( $session['fee'] ) ? (int) $session['fee'] : 0;
			if ( $amount > 0 ) {
				$candidates[] = array(
					'rial'  => $amount,
					'label' => 'GraphQL Session #' . ( $idx + 1 ) . ' (amount)',
				);
			}
			if ( $fee > 0 && $amount > 0 ) {
				$candidates[] = array(
					'rial'  => $amount + $fee,
					'label' => 'GraphQL Session #' . ( $idx + 1 ) . ' (amount+fee — اگر کارمزد با مشتری باشد)',
				);
			}
		}
	}

	$best_rial  = null;
	$best_label = '';
	if ( ! empty( $candidates ) ) {
		$best_rial  = (int) $candidates[0]['rial'];
		$best_label = (string) $candidates[0]['label'];
	}

	return array(
		'best_rial'  => $best_rial,
		'best_label' => $best_label,
		'sources'    => array(
			'candidates' => $candidates,
			'inquiry'    => $sources['inquiry'] ?? array(),
			'unverified' => $sources['unverified'] ?? array(),
			'graphql'    => $sources['graphql_sessions'] ?? array(),
		),
	);
}

function ez_zp_page_mask( string $value, int $visible = 8 ): string {
	$value = trim( $value );
	if ( $value === '' ) {
		return '—';
	}
	if ( strlen( $value ) <= $visible ) {
		return $value;
	}
	return substr( $value, 0, $visible ) . '…';
}

function ez_zp_page_status_label( ?string $status ): string {
	$labels = array(
		'PAID'     => 'پرداخت شده (هنوز verify نشده در سایت)',
		'VERIFIED' => 'تأیید شده در زرین‌پال',
		'IN_BANK'  => 'در حال پردازش بانکی',
		'FAILED'   => 'ناموفق',
		'REVERSED' => 'برگشت‌خورده',
	);
	return isset( $labels[ $status ] ) ? $labels[ $status ] : ( $status ?: '—' );
}

$order_id  = isset( $_GET['order_id'] ) ? (int) $_GET['order_id'] : 0;
$authority = isset( $_GET['authority'] ) ? sanitize_text_field( wp_unslash( $_GET['authority'] ) ) : '';
$ran       = isset( $_GET['inquiry'] ) && $_GET['inquiry'] === '1';

$result       = null;
$order        = null;
$gw           = null;
$local        = array();
$api_call     = null;
$paid_resolve = null;
$graphql_calls = array();
$unverified    = null;

if ( $ran ) {
	if ( $order_id > 0 && function_exists( 'wc_get_order' ) ) {
		$order = wc_get_order( $order_id );
	}

	if ( ! $order && $authority !== '' && function_exists( 'get_order_id_by_authority' ) ) {
		$found = get_order_id_by_authority( $authority );
		if ( $found ) {
			$order_id = (int) $found;
			$order    = wc_get_order( $order_id );
		}
	}

	if ( $order ) {
		if ( $authority === '' ) {
			$authority = (string) $order->get_meta( '_zarinpal_authority' );
		}
		$pm = (string) $order->get_payment_method();
		$gw = ez_zp_page_gateway_settings( $pm );

		$verify_rial = function_exists( 'ez_order_verify_amount_rial' )
			? ez_order_verify_amount_rial( $order )
			: ( function_exists( 'ez_zp_order_total_rial' ) ? ez_zp_order_total_rial( $order ) : (int) $order->get_total() );

		$fee_data = $order->get_meta( '_zarinpal_fee_data' );
		$local    = array(
			'order_id'         => $order->get_id(),
			'status'           => $order->get_status(),
			'is_paid'          => $order->is_paid(),
			'payment_method'   => $pm,
			'currency'         => $order->get_currency(),
			'total'            => $order->get_total(),
			'verify_rial'      => $verify_rial,
			'authority'        => $authority,
			'transaction_id'   => (string) $order->get_transaction_id(),
			'ref_id'           => (string) $order->get_transaction_id(),
			'order_total_2'    => $order->get_meta( '_order_total_2' ),
			'fee_data'         => $fee_data,
			'fee_suggested'    => ( is_array( $fee_data ) && isset( $fee_data['suggested_amount'] ) ) ? (int) $fee_data['suggested_amount'] : null,
			'verify_lock'      => $order->get_meta( '_ez_zp_verify_lock' ),
			'last_verify_at'   => $order->get_meta( '_ez_last_verify_attempt' ),
		);

		$ledger_json = $order->get_meta( '_ez_payment_ledger_json' );
		if ( is_string( $ledger_json ) && $ledger_json !== '' ) {
			$decoded = json_decode( $ledger_json, true );
			if ( is_array( $decoded ) ) {
				$local['payment_ledger'] = $decoded;
			}
		}

		if ( $authority !== '' && $gw['merchant'] !== '' ) {
			$api_call = ez_zp_page_inquiry_api( $gw['merchant'], $authority, $gw['sandbox'] );
			$inquiry_amount = null;
			if ( $api_call['ok'] && is_array( $api_call['raw'] ) ) {
				$data = isset( $api_call['raw']['data'] ) && is_array( $api_call['raw']['data'] )
					? $api_call['raw']['data']
					: array();
				$zp_status = isset( $data['status'] ) ? (string) $data['status'] : '';
				$inquiry_amount = isset( $data['amount'] ) ? (int) $data['amount'] : null;
				$result    = array(
					'zp_status'      => $zp_status,
					'zp_status_fa'   => ez_zp_page_status_label( $zp_status ),
					'code'           => isset( $data['code'] ) ? (int) $data['code'] : null,
					'message'        => isset( $data['message'] ) ? (string) $data['message'] : '',
					'amount'         => $inquiry_amount,
					'data_all'       => $data,
					'errors'         => isset( $api_call['raw']['errors'] ) ? $api_call['raw']['errors'] : array(),
					'amount_match'   => null,
				);
				if ( $inquiry_amount !== null && $verify_rial > 0 ) {
					$result['amount_match'] = ( (int) $inquiry_amount === (int) $verify_rial );
				}
			}

			$unverified = ez_zp_page_unverified_lookup( $gw['merchant'], $authority, $gw['sandbox'] );

			$graphql_sessions = array();
			if ( $gw['access_token'] !== '' ) {
				$gql_pack         = ez_zp_page_fetch_all_zp_sessions( $gw, $order, $authority );
				$graphql_sessions = $gql_pack['sessions'];
				$graphql_calls    = $gql_pack['calls'];
			}

			$fee_suggested = ( is_array( $fee_data ) && isset( $fee_data['suggested_amount'] ) )
				? (int) $fee_data['suggested_amount']
				: null;

			$paid_resolve = ez_zp_page_resolve_paid_amount(
				array(
					'inquiry'          => array( 'amount' => $inquiry_amount ),
					'unverified'       => $unverified,
					'graphql_sessions' => $graphql_sessions,
					'fee_suggested'    => $fee_suggested,
				)
			);
		} else {
			$result = array( 'error' => 'authority یا merchant در تنظیمات درگاه موجود نیست.' );
		}
	} else {
		$result = array( 'error' => 'سفارش یافت نشد. order_id یا authority معتبر وارد کنید.' );
	}
}

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 960px;">
	<h1 class="text-2xl font-bold mb-2">مبلغ پرداختی کاربر در زرین‌پال</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		مبلغ از <strong>inquiry</strong>، <strong>unVerified</strong> و
		<strong>GraphQL Session</strong> (مبلغ + کارمزد + کارت/RRN) خوانده می‌شود.
		اگر فقط وضعیت می‌بینی و مبلغ خالی است، معمولاً تراکنش قبلاً verify شده —
		<code dir="ltr">access_token</code> درگاه را در ووکامرس پر کن.
	</p>

	<form method="get" class="mb-6 flex flex-wrap gap-4 items-end text-sm">
		<label>
			شناسه سفارش
			<input type="number" name="order_id" min="1" value="<?php echo $order_id > 0 ? (int) $order_id : ''; ?>"
				class="border rounded px-2 py-1 w-32 mr-1" dir="ltr" placeholder="12345">
		</label>
		<label>
			Authority (اختیاری)
			<input type="text" name="authority" value="<?php echo esc_attr( $authority ); ?>"
				class="border rounded px-2 py-1 w-72 mr-1 font-mono text-xs" dir="ltr"
				placeholder="A000000000000000000000000000...">
		</label>
		<input type="hidden" name="inquiry" value="1">
		<button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
			استعلام
		</button>
	</form>

	<?php if ( $ran ) : ?>

		<?php if ( is_array( $result ) && isset( $result['error'] ) ) : ?>
			<p class="text-red-600 mb-4"><?php echo esc_html( $result['error'] ); ?></p>
		<?php endif; ?>

		<?php if ( is_array( $paid_resolve ) ) : ?>
			<div class="mb-6 p-5 rounded-xl border-2 <?php echo $paid_resolve['best_rial'] ? 'border-green-500 bg-green-50' : 'border-amber-400 bg-amber-50'; ?>">
				<p class="text-sm text-gray-700 mb-1">مبلغ پرداختی کاربر (بهترین مقدار از API)</p>
				<p class="text-3xl font-bold text-gray-900 mb-2" dir="ltr">
					<?php echo esc_html( ez_zp_page_format_rial( $paid_resolve['best_rial'] ) ); ?>
				</p>
				<?php if ( $paid_resolve['best_label'] !== '' ) : ?>
					<p class="text-xs text-gray-600">منبع: <code dir="ltr"><?php echo esc_html( $paid_resolve['best_label'] ); ?></code></p>
				<?php else : ?>
					<p class="text-sm text-amber-900">
						مبلغ از API برنگشت.
						برای تراکنش <code dir="ltr">VERIFIED</code> فیلد <code dir="ltr">amount</code> در inquiry نیست.
						GraphQL باید با <code dir="ltr">Session(id:ref_id)</code> یا لیست ترمینال مبلغ را بدهد —
						اگر همه GraphQLها <code dir="ltr">Server Error</code> هستند، توکن GraphQL پنل را دوباره بسازید
						(در ووکامرس فقط <code dir="ltr">Bearer</code> معتبر است).
					</p>
				<?php endif; ?>
				<?php if ( $order && (string) $order->get_transaction_id() !== '' ) : ?>
					<p class="text-xs text-gray-600 mt-1">
						کد پیگیری (ref_id): <code dir="ltr"><?php echo esc_html( (string) $order->get_transaction_id() ); ?></code>
					</p>
				<?php endif; ?>
				<?php if ( $order && ! empty( $local['verify_rial'] ) ) : ?>
					<p class="text-sm mt-2 text-gray-700">
						مبلغ مورد انتظار verify سایت:
						<strong dir="ltr"><?php echo esc_html( ez_zp_page_format_rial( (int) $local['verify_rial'] ) ); ?></strong>
						<?php
						if ( $paid_resolve['best_rial'] && (int) $paid_resolve['best_rial'] !== (int) $local['verify_rial'] ) {
							echo '<span class="text-red-600"> — اختلاف با مبلغ API</span>';
						} elseif ( $paid_resolve['best_rial'] ) {
							echo '<span class="text-green-700"> — مطابق</span>';
						}
						?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $paid_resolve['sources']['candidates'] ) ) : ?>
				<h2 class="text-lg font-semibold mb-2">همهٔ مقادیر مبلغ از API</h2>
				<table class="min-w-full text-sm border border-gray-200 rounded-lg mb-6">
					<thead>
						<tr class="bg-gray-100">
							<th class="text-right py-2 px-3">منبع</th>
							<th class="text-right py-2 px-3">مبلغ</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $paid_resolve['sources']['candidates'] as $cand ) : ?>
							<tr class="border-t">
								<td class="py-2 px-3 font-mono text-xs" dir="ltr"><?php echo esc_html( (string) $cand['label'] ); ?></td>
								<td class="py-2 px-3" dir="ltr"><?php echo esc_html( ez_zp_page_format_rial( (int) $cand['rial'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( is_array( $unverified ) && $unverified['found'] && is_array( $unverified['item'] ) ) : ?>
			<div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded text-sm">
				<strong>unVerified:</strong> این authority هنوز در صف verify است —
				مبلغ <?php echo esc_html( ez_zp_page_format_rial( (int) ( $unverified['amount'] ?? 0 ) ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $order && is_array( $local ) ) : ?>
			<h2 class="text-lg font-semibold mt-4 mb-2">سفارش ووکامرس</h2>
			<table class="min-w-full text-sm border border-gray-200 rounded-lg mb-6">
				<tbody>
					<?php
					$rows = array(
						'order_id'       => (string) $local['order_id'],
						'status'         => $local['status'],
						'is_paid'        => $local['is_paid'] ? 'بله' : 'خیر',
						'payment_method' => $local['payment_method'],
						'currency'       => $local['currency'],
						'total'          => $local['total'],
						'verify_rial'    => number_format( (int) $local['verify_rial'] ),
						'authority'      => $local['authority'] ?: '—',
						'transaction_id' => $local['transaction_id'] ?: '—',
					);
					foreach ( $rows as $k => $v ) :
						?>
						<tr class="border-t border-gray-100">
							<th class="text-right py-2 px-3 bg-gray-50 w-40 font-mono" dir="ltr"><?php echo esc_html( $k ); ?></th>
							<td class="py-2 px-3 <?php echo $k === 'authority' ? 'font-mono text-xs break-all' : ''; ?>" dir="<?php echo $k === 'authority' ? 'ltr' : 'rtl'; ?>">
								<?php echo esc_html( (string) $v ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( is_array( $gw ) ) : ?>
				<h2 class="text-lg font-semibold mb-2">تنظیمات درگاه</h2>
				<ul class="text-sm mb-4 space-y-1 text-gray-700">
					<li><strong>option:</strong> <code dir="ltr"><?php echo esc_html( $gw['settings_key'] ); ?></code></li>
					<li><strong>sandbox:</strong> <?php echo $gw['sandbox'] ? 'بله' : 'خیر'; ?></li>
					<li><strong>merchant:</strong> <code dir="ltr"><?php echo esc_html( ez_zp_page_mask( $gw['merchant'] ) ); ?></code></li>
					<li><strong>access_token:</strong> <?php echo $gw['access_token'] !== '' ? 'تنظیم شده' : 'خالی'; ?></li>
					<li><strong>fee_payer:</strong> <?php echo esc_html( $gw['fee_payer'] ); ?></li>
					<li><strong>terminal_id:</strong> <code dir="ltr"><?php echo (int) $gw['terminal_id']; ?></code></li>
				</ul>
			<?php endif; ?>

			<?php if ( ! empty( $local['payment_ledger'] ) ) : ?>
				<h2 class="text-lg font-semibold mb-2">payment_ledger (محلی)</h2>
				<pre class="text-xs bg-gray-900 text-green-100 p-3 rounded overflow-x-auto mb-4" dir="ltr"><?php
					echo esc_html( wp_json_encode( $local['payment_ledger'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
				?></pre>
			<?php endif; ?>
		<?php endif; ?>

		<?php
		$gql_sessions = array();
		if ( is_array( $paid_resolve ) && is_array( $paid_resolve['sources']['graphql'] ?? null ) ) {
			$gql_sessions = $paid_resolve['sources']['graphql'];
		}
		?>
		<?php if ( ! empty( $gql_sessions ) ) : ?>
			<h2 class="text-lg font-semibold mb-2">جزئیات GraphQL (مبلغ، کارمزد، کارت)</h2>
			<?php foreach ( $gql_sessions as $gi => $session ) : ?>
				<?php if ( ! is_array( $session ) ) { continue; } ?>
				<div class="mb-4 p-4 border border-gray-200 rounded-lg text-sm">
					<table class="min-w-full">
						<tbody>
							<?php
							$grows = array(
								'id'          => $session['id'] ?? '—',
								'status'      => $session['status'] ?? '—',
								'amount'      => isset( $session['amount'] ) ? ez_zp_page_format_rial( (int) $session['amount'] ) : '—',
								'fee'         => isset( $session['fee'] ) ? ez_zp_page_format_rial( (int) $session['fee'] ) : '—',
								'description' => $session['description'] ?? '—',
								'created_at'  => $session['created_at'] ?? '—',
							);
							foreach ( $grows as $gk => $gv ) :
								?>
								<tr class="border-t border-gray-100">
									<th class="text-right py-1 px-2 bg-gray-50 w-28 font-mono" dir="ltr"><?php echo esc_html( $gk ); ?></th>
									<td class="py-1 px-2"><?php echo esc_html( (string) $gv ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php if ( ! empty( $session['session_tries'] ) && is_array( $session['session_tries'] ) ) : ?>
						<p class="mt-2 font-semibold text-xs">session_tries (آخرین تلاش پرداخت)</p>
						<pre class="text-xs bg-gray-900 text-gray-100 p-2 rounded overflow-x-auto mt-1" dir="ltr"><?php
							echo esc_html( wp_json_encode( $session['session_tries'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
						?></pre>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php elseif ( is_array( $gw ) && $gw['access_token'] === '' ) : ?>
			<p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-3 mb-4">
				برای دیدن مبلغ دقیق و کارت/RRN، در ووکامرس → درگاه زرین‌پال فیلد <code dir="ltr">access_token</code> را پر کنید.
			</p>
		<?php endif; ?>

		<?php if ( is_array( $result ) && ! isset( $result['error'] ) ) : ?>
			<h2 class="text-lg font-semibold mb-2">وضعیت inquiry (تأیید/رد — جدا از مبلغ)</h2>
			<table class="min-w-full text-sm border border-gray-200 rounded-lg mb-4">
				<tbody>
					<tr class="border-t">
						<th class="text-right py-2 px-3 bg-gray-50">status</th>
						<td class="py-2 px-3">
							<code dir="ltr"><?php echo esc_html( (string) $result['zp_status'] ); ?></code>
							— <?php echo esc_html( (string) $result['zp_status_fa'] ); ?>
						</td>
					</tr>
					<tr class="border-t">
						<th class="text-right py-2 px-3 bg-gray-50">code</th>
						<td class="py-2 px-3" dir="ltr"><?php echo esc_html( (string) $result['code'] ); ?></td>
					</tr>
					<tr class="border-t">
						<th class="text-right py-2 px-3 bg-gray-50">message</th>
						<td class="py-2 px-3"><?php echo esc_html( (string) $result['message'] ); ?></td>
					</tr>
					<tr class="border-t">
						<th class="text-right py-2 px-3 bg-gray-50">amount (inquiry)</th>
						<td class="py-2 px-3" dir="ltr">
							<?php
							echo $result['amount'] !== null
								? esc_html( ez_zp_page_format_rial( (int) $result['amount'] ) )
								: '<span class="text-amber-700">در پاسخ inquiry نیامد (معمولاً بعد از VERIFIED)</span>';
							if ( $result['amount_match'] === true ) {
								echo ' <span class="text-green-700">✓ مطابق verify_rial</span>';
							} elseif ( $result['amount_match'] === false ) {
								echo ' <span class="text-red-600">✗ با verify_rial فرق دارد</span>';
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php if ( ! empty( $result['data_all'] ) && is_array( $result['data_all'] ) ) : ?>
				<p class="text-xs text-gray-500 mb-1">فیلدهای اضافه inquiry:</p>
				<pre class="text-xs bg-gray-100 p-2 rounded mb-4 overflow-x-auto" dir="ltr"><?php
					echo esc_html( wp_json_encode( $result['data_all'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
				?></pre>
			<?php endif; ?>

			<?php if ( $result['zp_status'] === 'PAID' && $order && ! $order->is_paid() && function_exists( 'ez_zarinpal_try_verify_now' ) ) : ?>
				<p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-3 mb-4">
					در زرین‌پال <code dir="ltr">PAID</code> است ولی سفارش هنوز paid نیست.
					برای verify واقعی از CLI/cron یا تابع
					<code dir="ltr">ez_zarinpal_try_verify_now(<?php echo (int) $order->get_id(); ?>)</code>
					استفاده کنید — این صفحه عمداً verify نمی‌کند.
				</p>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( is_array( $api_call ) && is_array( $api_call['raw'] ) ) : ?>
			<h2 class="text-lg font-semibold mb-2">پاسخ خام inquiry</h2>
			<p class="text-xs text-gray-500 mb-1" dir="ltr">HTTP <?php echo (int) $api_call['http_code']; ?></p>
			<pre class="text-xs bg-gray-900 text-gray-100 p-3 rounded overflow-x-auto mb-4" dir="ltr"><?php
				echo esc_html( wp_json_encode( $api_call['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			?></pre>
		<?php elseif ( is_array( $api_call ) && $api_call['error'] !== '' ) : ?>
			<p class="text-red-600 text-sm mb-4">خطای inquiry: <?php echo esc_html( $api_call['error'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $graphql_calls ) ) : ?>
			<h2 class="text-lg font-semibold mb-2">پاسخ خام GraphQL (هر استراتژی)</h2>
			<?php foreach ( $graphql_calls as $gkey => $gcall ) : ?>
				<p class="text-xs font-mono text-gray-700 mb-1" dir="ltr">
					<?php
					$ok_label = ! empty( $gcall['ok'] ) ? 'OK' : 'FAIL';
					$auth_lbl = isset( $gcall['auth_mode'] ) ? (string) $gcall['auth_mode'] : '';
					$cnt      = is_array( $gcall['sessions'] ?? null ) ? count( $gcall['sessions'] ) : 0;
					echo esc_html( "{$gkey} [{$ok_label}] auth={$auth_lbl} sessions={$cnt}" );
					if ( ! empty( $gcall['error'] ) ) {
						echo ' — ' . esc_html( (string) $gcall['error'] );
					}
					?>
				</p>
				<pre class="text-xs bg-gray-900 text-gray-100 p-3 rounded overflow-x-auto mb-3" dir="ltr"><?php
					echo esc_html( wp_json_encode( $gcall['raw'] ?? $gcall, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
				?></pre>
			<?php endforeach; ?>
		<?php endif; ?>

	<?php endif; ?>
</main>

<?php
get_footer();
