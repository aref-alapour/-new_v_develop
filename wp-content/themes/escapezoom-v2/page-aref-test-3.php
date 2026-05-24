<?php
/**
 * Template Name: aref-test-3
 *
 * چک وضعیت سفارش: WooCommerce + wp_markting + booking + لاگ تغییر وضعیت.
 * فقط مدیرکل. در وردپرس یک برگه بسازید و این قالب را انتخاب کنید (slug پیشنهادی: aref-test-3).
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

/**
 * @return array<string, array{name:string,color:string}>
 */
function ez_aref3_status_labels(): array {
	return array(
		'pending'           => array( 'name' => 'در حال پرداخت', 'color' => '#FD7013' ),
		'wc-pending'        => array( 'name' => 'در حال پرداخت', 'color' => '#FD7013' ),
		'on-hold'           => array( 'name' => 'در حال پرداخت', 'color' => '#FD7013' ),
		'wc-on-hold'        => array( 'name' => 'در حال پرداخت', 'color' => '#FD7013' ),
		'processing'        => array( 'name' => 'در حال بستن سانس', 'color' => '#3F7FF5' ),
		'wc-processing'     => array( 'name' => 'در حال بستن سانس', 'color' => '#3F7FF5' ),
		'cancelled'         => array( 'name' => 'لغو شده', 'color' => '#F21543' ),
		'wc-cancelled'      => array( 'name' => 'لغو شده', 'color' => '#F21543' ),
		'refunded'          => array( 'name' => 'مسترد شده', 'color' => '#F21543' ),
		'wc-refunded'       => array( 'name' => 'مسترد شده', 'color' => '#F21543' ),
		'conflict'          => array( 'name' => 'تداخل', 'color' => '#F21543' ),
		'wc-conflict'       => array( 'name' => 'تداخل', 'color' => '#F21543' ),
		'partially-paid'    => array( 'name' => 'پیش پرداخت', 'color' => '#049654' ),
		'wc-partially-paid' => array( 'name' => 'پیش پرداخت', 'color' => '#049654' ),
		'completed-paid'    => array( 'name' => 'پرداخت کامل', 'color' => '#A020F0' ),
		'wc-completed-paid' => array( 'name' => 'پرداخت کامل', 'color' => '#A020F0' ),
		'completed'         => array( 'name' => 'تکمیل شده', 'color' => '#049654' ),
		'wc-completed'      => array( 'name' => 'تکمیل شده', 'color' => '#049654' ),
		'walletx'           => array( 'name' => 'واریز به کیف پول', 'color' => '#049654' ),
		'wc-walletx'        => array( 'name' => 'واریز به کیف پول', 'color' => '#049654' ),
	);
}

function ez_aref3_status_badge( string $status ): string {
	$labels = ez_aref3_status_labels();
	$key    = trim( $status );
	if ( $key === '' ) {
		return '<span class="text-gray-400">—</span>';
	}
	$info = $labels[ $key ] ?? array( 'name' => $key, 'color' => '#64748B' );
	$name = esc_html( $info['name'] );
	$col  = esc_attr( $info['color'] );

	return '<span class="font-bold" style="color:' . $col . '">' . $name . '</span>'
		. ' <code dir="ltr" class="text-[11px] text-gray-500">' . esc_html( $key ) . '</code>';
}

function ez_aref3_normalize_wc_slug( string $status ): string {
	$status = trim( $status );
	if ( $status === '' ) {
		return '';
	}
	return str_starts_with( $status, 'wc-' ) ? substr( $status, 3 ) : $status;
}

/**
 * @return array<int, string>
 */
function ez_aref3_collect_issues( array $snapshot ): array {
	$issues = array();
	$wc     = $snapshot['wc'] ?? array();
	$mkt    = $snapshot['markting'] ?? array();

	if ( empty( $wc['found'] ) ) {
		$issues[] = 'سفارش در WooCommerce یافت نشد.';
		return $issues;
	}

	$wc_slug  = ez_aref3_normalize_wc_slug( (string) ( $wc['status'] ?? '' ) );
	$mkt_slug = '';
	if ( ! empty( $mkt['found'] ) && function_exists( 'ez_markting_status_slug' ) ) {
		$mkt_slug = ez_markting_status_slug( $mkt['row'] );
	} elseif ( ! empty( $mkt['order_status'] ) ) {
		$mkt_slug = ez_aref3_normalize_wc_slug( (string) $mkt['order_status'] );
	}

	if ( ! empty( $mkt['found'] ) && $mkt_slug !== '' && $wc_slug !== '' && $mkt_slug !== $wc_slug ) {
		$issues[] = "ناهمگامی وضعیت: WC={$wc_slug} در مقابل markting={$mkt_slug}";
	}

	if ( empty( $mkt['found'] ) && ! empty( $wc['is_paid'] ) ) {
		$issues[] = 'سفارش پرداخت‌شده است ولی ردیف wp_markting وجود ندارد.';
	}

	if ( ! empty( $wc['is_paid'] ) && empty( $snapshot['booking']['exists'] ) ) {
		if ( ! empty( $snapshot['diagnostics']['should_try_pipeline'] ) ) {
			$issues[] = 'پرداخت شده ولی سانس در wp_zb_booking_history ثبت نشده (pipeline ممکن است لازم باشد).';
		}
	}

	if ( ! empty( $wc['status'] ) && in_array( $wc_slug, array( 'processing' ), true ) && ! empty( $snapshot['booking']['exists'] ) ) {
		$issues[] = 'وضعیت WC هنوز processing است در حالی که بوکینگ ثبت شده (احتمالاً نیاز به upgrade به partially-paid/completed-paid).';
	}

	return $issues;
}

/**
 * @return array<string, mixed>
 */
function ez_aref3_build_snapshot( int $order_id, string $authority = '' ): array {
	$snapshot = array(
		'order_id'    => $order_id,
		'authority'   => $authority,
		'wc'          => array( 'found' => false ),
		'post'        => array( 'found' => false ),
		'markting'    => array( 'found' => false ),
		'booking'     => array( 'exists' => false, 'rows' => array() ),
		'status_logs' => array(),
		'diagnostics' => array(),
		'issues'      => array(),
	);

	if ( $order_id <= 0 && $authority !== '' && function_exists( 'get_order_id_by_authority' ) ) {
		$found = (int) get_order_id_by_authority( $authority );
		if ( $found > 0 ) {
			$order_id              = $found;
			$snapshot['order_id']  = $order_id;
		}
	}

	if ( $order_id <= 0 ) {
		$snapshot['error'] = 'شناسه سفارش نامعتبر است.';
		return $snapshot;
	}

	if ( function_exists( 'wc_get_order' ) ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			if ( $authority === '' ) {
				$authority = (string) $order->get_meta( '_zarinpal_authority' );
				if ( $authority === '' && function_exists( 'ez_zibal_get_track_id' ) ) {
					$authority = (string) ez_zibal_get_track_id( $order );
				}
			}
			$snapshot['authority'] = $authority;
			$snapshot['wc']        = array(
				'found'                  => true,
				'status'                 => (string) $order->get_status(),
				'is_paid'                => (bool) $order->is_paid(),
				'payment_method'         => (string) $order->get_payment_method(),
				'payment_method_title'   => (string) $order->get_payment_method_title(),
				'date_created'           => $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : '',
				'date_paid'              => $order->get_date_paid() ? $order->get_date_paid()->date( 'Y-m-d H:i:s' ) : '',
				'total'                  => $order->get_total(),
				'currency'               => (string) $order->get_currency(),
				'transaction_id'         => (string) $order->get_transaction_id(),
				'authority'              => $authority,
				'ez_payment_type'        => (string) get_post_meta( $order_id, 'ez_payment_type', true ),
				'sans_time'              => (string) get_post_meta( $order_id, 'sans_time', true ),
				'booking_pipeline_done'  => (string) get_post_meta( $order_id, 'booking_pipeline_done_at', true ),
				'booking_pipeline_start' => (string) get_post_meta( $order_id, 'booking_pipeline_started_at', true ),
				'verify_lock'            => (string) $order->get_meta( '_ez_zp_verify_lock' ),
				'last_verify_attempt'    => (string) $order->get_meta( '_ez_last_verify_attempt' ),
			);

			$age_sec = null;
			if ( function_exists( 'ez_zp_order_age_seconds' ) ) {
				$age_sec = (int) ez_zp_order_age_seconds( $order );
			}
			$snapshot['diagnostics']['order_age_seconds'] = $age_sec;
			if ( function_exists( 'ez_order_should_try_booking_after_payment_pipeline' ) ) {
				$snapshot['diagnostics']['should_try_pipeline'] = (bool) ez_order_should_try_booking_after_payment_pipeline( $order );
			}
		}
	}

	if ( function_exists( 'medoo' ) ) {
		try {
			$crm = medoo();
			if ( $crm ) {
				$mrow = $crm->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );
				if ( is_array( $mrow ) && ! empty( $mrow ) ) {
					$mkt_slug = function_exists( 'ez_markting_status_slug' )
						? ez_markting_status_slug( $mrow )
						: ez_aref3_normalize_wc_slug( (string) ( $mrow['order_status'] ?? '' ) );
					$snapshot['markting'] = array(
						'found'        => true,
						'row'          => $mrow,
						'order_status' => (string) ( $mrow['order_status'] ?? '' ),
						'slug'         => $mkt_slug,
						'looks_paid'   => function_exists( 'ez_markting_row_looks_paid' )
							? (bool) ez_markting_row_looks_paid( $mrow )
							: null,
						'sans_date'    => (string) ( $mrow['order_sans_date'] ?? '' ),
						'sans_time'    => (string) ( $mrow['order_sans_time'] ?? '' ),
						'game_name'    => (string) ( $mrow['game_name'] ?? '' ),
					);
				}
			}
		} catch ( Throwable $e ) {
			$snapshot['markting']['error'] = $e->getMessage();
		}
	}

	if ( function_exists( 'medoo_queries' ) ) {
		try {
			$mq = medoo_queries();
			if ( $mq ) {
				$bookings = $mq->select(
					'wp_zb_booking_history',
					array(
						'booking_id',
						'wc_order_id',
						'room_id',
						'booking_time',
						'booked_time',
						'status',
						'quantity',
						'phone',
					),
					array(
						'wc_order_id' => $order_id,
						'ORDER'       => array( 'booking_time' => 'DESC' ),
						'LIMIT'       => 20,
					)
				);
				$snapshot['booking']['rows']  = is_array( $bookings ) ? $bookings : array();
				$snapshot['booking']['exists'] = ! empty( $snapshot['booking']['rows'] );
			}
		} catch ( Throwable $e ) {
			$snapshot['booking']['error'] = $e->getMessage();
		}
	}

	if ( empty( $snapshot['booking']['exists'] ) && function_exists( 'ez_booking_exists_for_order' ) ) {
		$snapshot['booking']['exists'] = (bool) ez_booking_exists_for_order( $order_id );
	}

	global $wpdb;
	if ( $wpdb instanceof wpdb ) {
		$post = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, post_status, post_date, post_modified FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'shop_order' LIMIT 1",
				$order_id
			),
			ARRAY_A
		);
		if ( is_array( $post ) && ! empty( $post['ID'] ) ) {
			$snapshot['post'] = array(
				'found'         => true,
				'post_status'   => (string) ( $post['post_status'] ?? '' ),
				'post_date'     => (string) ( $post['post_date'] ?? '' ),
				'post_modified' => (string) ( $post['post_modified'] ?? '' ),
			);
		}
	}

	if ( function_exists( 'medoo' ) ) {
		try {
			$crm = medoo();
			if ( $crm ) {
				$logs = $crm->select(
					'wp_order_status_log',
					array( 'order_id', 'user_id', 'status_log', 'function_used', 'created_at' ),
					array(
						'order_id' => $order_id,
						'ORDER'    => array( 'created_at' => 'DESC' ),
						'LIMIT'    => 30,
					)
				);
				$snapshot['status_logs'] = is_array( $logs ) ? $logs : array();
			}
		} catch ( Throwable $e ) {
			$snapshot['status_logs_error'] = $e->getMessage();
		}
	}

	$snapshot['issues'] = ez_aref3_collect_issues( $snapshot );

	return $snapshot;
}

$order_id  = isset( $_GET['order_id'] ) ? (int) $_GET['order_id'] : 0;
$authority = isset( $_GET['authority'] ) ? sanitize_text_field( wp_unslash( $_GET['authority'] ) ) : '';
$do_check  = isset( $_GET['check'] ) && $_GET['check'] === '1';
$do_reconcile = isset( $_GET['reconcile'] ) && $_GET['reconcile'] === '1';

$page_url = get_permalink();
if ( ! $page_url ) {
	$page_url = home_url( '/aref-test-3/' );
}

$snapshot      = null;
$reconcile_msg = '';

if ( $do_check || $do_reconcile ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ez_aref3_order_status' ) ) {
		wp_die( 'Nonce نامعتبر است.', 'Forbidden', array( 'response' => 403 ) );
	}

	if ( $do_reconcile && $order_id > 0 && function_exists( 'ez_reconcile_single_order_wp_markting_wc_booking' ) ) {
		$ok              = ez_reconcile_single_order_wp_markting_wc_booking( $order_id );
		$reconcile_msg   = $ok
			? 'همگام‌سازی (reconcile) اجرا شد — وضعیت را دوباره ببینید.'
			: 'reconcile اجرا شد ولی تغییری گزارش نشد یا سفارش نامعتبر بود.';
		$order_after = wc_get_order( $order_id );
		if ( $order_after ) {
			$reconcile_msg .= ' وضعیت فعلی WC: ' . $order_after->get_status();
		}
	}

	$snapshot = ez_aref3_build_snapshot( $order_id, $authority );
	if ( ! empty( $snapshot['order_id'] ) && $order_id <= 0 ) {
		$order_id = (int) $snapshot['order_id'];
	}
}

$base_args = array();
if ( $order_id > 0 ) {
	$base_args['order_id'] = $order_id;
}
if ( $authority !== '' ) {
	$base_args['authority'] = $authority;
}

$check_url = add_query_arg(
	array_merge(
		$base_args,
		array(
			'check'    => '1',
			'_wpnonce' => wp_create_nonce( 'ez_aref3_order_status' ),
		)
	),
	$page_url
);

$reconcile_url = add_query_arg(
	array_merge(
		$base_args,
		array(
			'reconcile' => '1',
			'check'     => '1',
			'_wpnonce'  => wp_create_nonce( 'ez_aref3_order_status' ),
		)
	),
	$page_url
);

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 1000px;">
	<h1 class="text-2xl font-bold mb-2">چک وضعیت سفارش</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		وضعیت را از <strong>WooCommerce</strong>، <strong>wp_markting</strong>،
		<strong>wp_zb_booking_history</strong> و <strong>wp_order_status_log</strong> کنار هم می‌بینید.
		برای استعلام زرین‌پال از قالب <code dir="ltr">ZarinPal Order Inquiry</code> استفاده کنید.
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
		<input type="hidden" name="check" value="1">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'ez_aref3_order_status' ) ); ?>">
		<button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
			چک وضعیت
		</button>
	</form>

	<?php if ( $do_check && is_array( $snapshot ) ) : ?>

		<?php if ( ! empty( $reconcile_msg ) ) : ?>
			<p class="mb-4 text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded p-3"><?php echo esc_html( $reconcile_msg ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $snapshot['error'] ) ) : ?>
			<p class="mb-4 text-red-700 bg-red-50 border border-red-200 rounded p-3 text-sm"><?php echo esc_html( (string) $snapshot['error'] ); ?></p>
		<?php else : ?>

			<?php if ( ! empty( $snapshot['issues'] ) ) : ?>
				<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded text-sm text-amber-900">
					<strong>هشدار / ناهمگامی:</strong>
					<ul class="list-disc mr-5 mt-1 space-y-1">
						<?php foreach ( $snapshot['issues'] as $issue ) : ?>
							<li><?php echo esc_html( $issue ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php else : ?>
				<p class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded p-3">از نظر همگامی پایه، مشکل آشکاری دیده نشد.</p>
			<?php endif; ?>

			<p class="mb-4 flex flex-wrap gap-3">
				<?php if ( ! empty( $snapshot['wc']['found'] ) && function_exists( 'ez_reconcile_single_order_wp_markting_wc_booking' ) ) : ?>
					<a href="<?php echo esc_url( $reconcile_url ); ?>"
						class="inline-block px-5 py-2 rounded font-medium bg-amber-600 text-white hover:bg-amber-700"
						onclick="return confirm('اجرای reconcile (همگام مارکتینگ + pipeline بوکینگ)؟');">
						همگام‌سازی (reconcile)
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $snapshot['order_id'] ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'post.php?post=' . (int) $snapshot['order_id'] . '&action=edit' ) ); ?>"
						class="inline-block px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm" target="_blank" rel="noopener">
						ویرایش در ادمین WC
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $check_url ); ?>" class="inline-block px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-sm">
					بروزرسانی
				</a>
			</p>

			<div class="grid gap-4 mb-6 md:grid-cols-2">
				<section class="border border-gray-200 rounded-lg p-4 text-sm">
					<h2 class="font-semibold mb-2">WooCommerce</h2>
					<?php if ( ! empty( $snapshot['wc']['found'] ) ) : ?>
						<ul class="space-y-1">
							<li><strong>order_id:</strong> <span dir="ltr"><?php echo (int) $snapshot['order_id']; ?></span></li>
							<li><strong>وضعیت:</strong> <?php echo ez_aref3_status_badge( (string) $snapshot['wc']['status'] ); ?></li>
							<li><strong>is_paid:</strong> <?php echo ! empty( $snapshot['wc']['is_paid'] ) ? 'بله' : 'خیر'; ?></li>
							<li><strong>درگاه:</strong> <?php echo esc_html( (string) $snapshot['wc']['payment_method'] ); ?>
								<?php if ( ! empty( $snapshot['wc']['payment_method_title'] ) ) : ?>
									(<?php echo esc_html( (string) $snapshot['wc']['payment_method_title'] ); ?>)
								<?php endif; ?>
							</li>
							<li><strong>مبلغ:</strong> <span dir="ltr"><?php echo esc_html( (string) $snapshot['wc']['total'] . ' ' . (string) $snapshot['wc']['currency'] ); ?></span></li>
							<li><strong>تاریخ ایجاد:</strong> <span dir="ltr"><?php echo esc_html( (string) $snapshot['wc']['date_created'] ); ?></span></li>
							<li><strong>تاریخ پرداخت:</strong> <span dir="ltr"><?php echo esc_html( (string) ( $snapshot['wc']['date_paid'] ?: '—' ) ); ?></span></li>
							<li><strong>ref / transaction:</strong> <span dir="ltr" class="font-mono text-xs"><?php echo esc_html( (string) ( $snapshot['wc']['transaction_id'] ?: '—' ) ); ?></span></li>
							<li><strong>authority:</strong> <span dir="ltr" class="font-mono text-[11px] break-all"><?php echo esc_html( (string) ( $snapshot['wc']['authority'] ?: '—' ) ); ?></span></li>
							<li><strong>ez_payment_type:</strong> <span dir="ltr"><?php echo esc_html( (string) ( $snapshot['wc']['ez_payment_type'] ?: '—' ) ); ?></span></li>
							<li><strong>sans_time (meta):</strong> <span dir="ltr"><?php echo esc_html( (string) ( $snapshot['wc']['sans_time'] ?: '—' ) ); ?></span></li>
							<li><strong>pipeline:</strong>
								<span dir="ltr">start=<?php echo esc_html( (string) ( $snapshot['wc']['booking_pipeline_start'] ?: '—' ) ); ?></span>,
								<span dir="ltr">done=<?php echo esc_html( (string) ( $snapshot['wc']['booking_pipeline_done'] ?: '—' ) ); ?></span>
							</li>
						</ul>
					<?php else : ?>
						<p class="text-red-600">سفارش WC یافت نشد.</p>
					<?php endif; ?>
				</section>

				<section class="border border-gray-200 rounded-lg p-4 text-sm">
					<h2 class="font-semibold mb-2">wp_markting (CRM)</h2>
					<?php if ( ! empty( $snapshot['markting']['found'] ) ) : ?>
						<ul class="space-y-1">
							<li><strong>وضعیت:</strong> <?php echo ez_aref3_status_badge( (string) ( $snapshot['markting']['slug'] ?? $snapshot['markting']['order_status'] ) ); ?></li>
							<li><strong>order_status خام:</strong> <code dir="ltr"><?php echo esc_html( (string) $snapshot['markting']['order_status'] ); ?></code></li>
							<?php if ( isset( $snapshot['markting']['looks_paid'] ) ) : ?>
								<li><strong>looks_paid:</strong> <?php echo $snapshot['markting']['looks_paid'] ? 'بله' : 'خیر'; ?></li>
							<?php endif; ?>
							<li><strong>بازی:</strong> <?php echo esc_html( (string) ( $snapshot['markting']['game_name'] ?: '—' ) ); ?></li>
							<li><strong>سانس:</strong>
								<span dir="ltr"><?php echo esc_html( (string) ( $snapshot['markting']['sans_date'] ?: '—' ) ); ?></span>
								<span dir="ltr"><?php echo esc_html( (string) ( $snapshot['markting']['sans_time'] ?: '' ) ); ?></span>
							</li>
						</ul>
					<?php elseif ( ! empty( $snapshot['markting']['error'] ) ) : ?>
						<p class="text-red-600"><?php echo esc_html( (string) $snapshot['markting']['error'] ); ?></p>
					<?php else : ?>
						<p class="text-amber-700">ردیف مارکتینگ وجود ندارد.</p>
					<?php endif; ?>
				</section>

				<section class="border border-gray-200 rounded-lg p-4 text-sm">
					<h2 class="font-semibold mb-2">wp_posts (legacy)</h2>
					<?php if ( ! empty( $snapshot['post']['found'] ) ) : ?>
						<ul class="space-y-1">
							<li><strong>post_status:</strong> <code dir="ltr"><?php echo esc_html( (string) $snapshot['post']['post_status'] ); ?></code></li>
							<li><strong>post_date:</strong> <span dir="ltr"><?php echo esc_html( (string) $snapshot['post']['post_date'] ); ?></span></li>
							<li><strong>post_modified:</strong> <span dir="ltr"><?php echo esc_html( (string) $snapshot['post']['post_modified'] ); ?></span></li>
						</ul>
					<?php else : ?>
						<p class="text-gray-500">—</p>
					<?php endif; ?>
				</section>

				<section class="border border-gray-200 rounded-lg p-4 text-sm">
					<h2 class="font-semibold mb-2">تشخیص</h2>
					<ul class="space-y-1">
						<li><strong>بوکینگ ثبت شده:</strong> <?php echo ! empty( $snapshot['booking']['exists'] ) ? 'بله' : 'خیر'; ?></li>
						<?php if ( isset( $snapshot['diagnostics']['should_try_pipeline'] ) ) : ?>
							<li><strong>should_try_pipeline:</strong> <?php echo $snapshot['diagnostics']['should_try_pipeline'] ? 'بله' : 'خیر'; ?></li>
						<?php endif; ?>
						<?php if ( isset( $snapshot['diagnostics']['order_age_seconds'] ) && $snapshot['diagnostics']['order_age_seconds'] !== null ) : ?>
							<li><strong>عمر سفارش:</strong> <span dir="ltr"><?php echo (int) $snapshot['diagnostics']['order_age_seconds']; ?> ثانیه</span></li>
						<?php endif; ?>
					</ul>
				</section>
			</div>

			<?php if ( ! empty( $snapshot['booking']['rows'] ) ) : ?>
				<h2 class="text-lg font-semibold mb-2">wp_zb_booking_history</h2>
				<div class="overflow-x-auto border border-gray-200 rounded-lg mb-6">
					<table class="min-w-full text-xs border-collapse">
						<thead>
							<tr class="bg-gray-100">
								<th class="text-right py-2 px-2">booking_id</th>
								<th class="text-right py-2 px-2">room_id</th>
								<th class="text-right py-2 px-2">booking_time</th>
								<th class="text-right py-2 px-2">status</th>
								<th class="text-right py-2 px-2">qty</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $snapshot['booking']['rows'] as $bk ) : ?>
								<tr class="border-t border-gray-100">
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $bk['booking_id'] ?? '' ) ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $bk['room_id'] ?? '' ) ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $bk['booking_time'] ?? '' ) ); ?></td>
									<td class="py-1 px-2"><?php echo esc_html( (string) ( $bk['status'] ?? '' ) ); ?></td>
									<td class="py-1 px-2"><?php echo esc_html( (string) ( $bk['quantity'] ?? '' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php elseif ( ! empty( $snapshot['booking']['error'] ) ) : ?>
				<p class="text-sm text-red-600 mb-4">خطای booking: <?php echo esc_html( (string) $snapshot['booking']['error'] ); ?></p>
			<?php endif; ?>

			<h2 class="text-lg font-semibold mb-2">آخرین لاگ‌های wp_order_status_log</h2>
			<?php if ( ! empty( $snapshot['status_logs_error'] ) ) : ?>
				<p class="text-sm text-red-600 mb-4"><?php echo esc_html( (string) $snapshot['status_logs_error'] ); ?></p>
			<?php elseif ( empty( $snapshot['status_logs'] ) ) : ?>
				<p class="text-sm text-gray-500 mb-6">لاگی ثبت نشده.</p>
			<?php else : ?>
				<div class="overflow-x-auto border border-gray-200 rounded-lg mb-6">
					<table class="min-w-full text-xs border-collapse">
						<thead>
							<tr class="bg-gray-100">
								<th class="text-right py-2 px-2">زمان</th>
								<th class="text-right py-2 px-2">status_log</th>
								<th class="text-right py-2 px-2">function</th>
								<th class="text-right py-2 px-2">user_id</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $snapshot['status_logs'] as $log ) : ?>
								<tr class="border-t border-gray-100">
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $log['created_at'] ?? '' ) ); ?></td>
									<td class="py-1 px-2"><?php echo esc_html( (string) ( $log['status_log'] ?? '' ) ); ?></td>
									<td class="py-1 px-2 font-mono text-[11px]" dir="ltr"><?php echo esc_html( (string) ( $log['function_used'] ?? '' ) ); ?></td>
									<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $log['user_id'] ?? '0' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

		<?php endif; ?>
	<?php endif; ?>

	<div class="text-xs text-gray-500 border-t pt-4 mt-8">
		<p class="mb-1">reconcile: <code dir="ltr">ez_reconcile_single_order_wp_markting_wc_booking</code> در <code dir="ltr">functions.php</code></p>
		<p>استعلام زرین‌پال: <code dir="ltr">page-aref-test-user.php</code> — unVerified: <code dir="ltr">page-aref-test-2.php</code></p>
	</div>
</main>

<?php
get_footer();
