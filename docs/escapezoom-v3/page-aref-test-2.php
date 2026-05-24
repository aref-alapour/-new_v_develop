<?php
/**
 * Template Name: Aref brand migration (YITH → WooCommerce)
 *
 * اسکریپت مهاجرت: yith_product_brand → product_brand + متای product_brand روی محصول.
 * الگوی اجرا مانند page-aref-test-1: هر مرحله یک بار صفحه کامل، سپس پس از load + pause خودکار ادامه.
 *
 * برگه با اسلاگ aref-test-2 یا انتخاب دستی این قالب.
 *
 * @package escapezoom-v2
 */

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	auth_redirect();
	exit;
}

$page_url = get_permalink();
if ( ! $page_url ) {
	wp_die( esc_html__( 'صفحه نامعتبر است.', 'escapezoom-v2' ) );
}

if ( ! headers_sent() ) {
	nocache_headers();
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
}

$yith_tax   = 'yith_product_brand';
$wc_tax     = 'product_brand';
$state_key  = 'ez_aref2_brand_migration_state';

$nonce_control = 'ez_brand_mig_control';
$nonce_step    = 'ez_brand_mig_step';

$pause_sec = isset( $_GET['pause'] ) ? absint( $_GET['pause'] ) : 5;
$pause_sec = min( 120, max( 2, $pause_sec ) );

$batch = isset( $_GET['batch'] ) ? absint( $_GET['batch'] ) : 15;
$batch = min( 50, max( 5, $batch ) );

$auto = isset( $_GET['auto'] ) && (string) $_GET['auto'] === '1';

$step_url = static function ( string $base, string $step_nonce_action, bool $auto_run, int $pause, int $batch_size ) {
	return add_query_arg(
		[
			'_wpnonce'       => wp_create_nonce( $step_nonce_action ),
			'ez_brand_step'  => '1',
			'auto'           => $auto_run ? '1' : '0',
			'pause'          => $pause,
			'batch'          => $batch_size,
		],
		$base
	);
};

$bootstrap_taxonomies = static function () use ( $yith_tax, $wc_tax ): void {
	if ( ! taxonomy_exists( $yith_tax ) ) {
		register_taxonomy(
			$yith_tax,
			[ 'product' ],
			[
				'labels'       => [ 'name' => 'YITH Product Brand (legacy)' ],
				'public'       => false,
				'show_ui'      => false,
				'rewrite'      => false,
				'query_var'    => false,
				'hierarchical' => false,
				'show_in_rest' => false,
			]
		);
	}

	if ( ! taxonomy_exists( $wc_tax ) ) {
		register_taxonomy(
			$wc_tax,
			[ 'product' ],
			[
				'labels'       => [
					'name'          => 'Brands',
					'singular_name' => 'Brand',
				],
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => 'edit.php?post_type=product',
				'show_in_rest'      => true,
				'hierarchical'      => false,
				'rewrite'           => [ 'slug' => 'brand', 'with_front' => false, 'hierarchical' => false ],
				'query_var'         => true,
			]
		);
	}
};

$copy_termmeta = static function ( int $from_tid, int $to_tid ): void {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT meta_key, meta_value FROM {$wpdb->termmeta} WHERE term_id = %d",
			$from_tid
		)
	);
	if ( ! $rows ) {
		return;
	}
	foreach ( $rows as $row ) {
		$key = (string) $row->meta_key;
		if ( $key === '' ) {
			continue;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$n = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->termmeta} WHERE term_id = %d AND meta_key = %s",
				$to_tid,
				$key
			)
		);
		if ( $n > 0 ) {
			continue;
		}
		add_term_meta( $to_tid, $key, maybe_unserialize( $row->meta_value ) );
	}
};

$delete_yith_term = static function ( int $term_id ) use ( $yith_tax ): void {
	if ( function_exists( 'wp_delete_term' ) ) {
		wp_delete_term( $term_id, $yith_tax );
		return;
	}
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$tt_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = %s LIMIT 1",
			$term_id,
			$yith_tax
		)
	);
	if ( $tt_id < 1 ) {
		return;
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->delete( $wpdb->term_relationships, [ 'term_taxonomy_id' => $tt_id ], [ '%d' ] );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->delete( $wpdb->termmeta, [ 'term_id' => $term_id ], [ '%d' ] );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->delete( $wpdb->term_taxonomy, [ 'term_taxonomy_id' => $tt_id ], [ '%d' ] );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$remaining = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE term_id = %d",
			$term_id
		)
	);
	if ( $remaining === 0 ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( $wpdb->terms, [ 'term_id' => $term_id ], [ '%d' ] );
	}
	clean_term_cache( $term_id, $yith_tax );
};

$bootstrap_taxonomies();

/** @var array<string, mixed>|null */
$mig_result = null;
/** @var string */
$auto_next_url = '';
$auto_pause_ms = $pause_sec * 1000;

/* ——— فرم کنترل (شروع / ریست) ——— */
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ez_brand_mig_reset'] ) ) {
	check_admin_referer( $nonce_control, 'ez_brand_mig_nonce' );
	delete_option( $state_key );
	wp_safe_redirect( remove_query_arg( [ '_wpnonce', 'ez_brand_step', 'auto', 'pause', 'batch' ], $page_url ) );
	exit;
}

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ez_brand_mig_start'] ) ) {
	check_admin_referer( $nonce_control, 'ez_brand_mig_nonce' );
	update_option(
		$state_key,
		[
			'phase'       => 'terms',
			'term_off'    => 0,
			'product_off' => 0,
			'iter'        => 0,
			'map'         => [],
		],
		false
	);
	wp_redirect( $step_url( $page_url, $nonce_step, true, $pause_sec, $batch ) );
	exit;
}

/* ——— هر مرحلهٔ migration ——— */
$step_ok = isset( $_GET['ez_brand_step'] ) && (string) $_GET['ez_brand_step'] === '1';
$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
$nonce_ok_step = $step_ok && $nonce !== '' && wp_verify_nonce( $nonce, $nonce_step );
$bad_nonce       = $step_ok && isset( $_GET['_wpnonce'] ) && ! $nonce_ok_step;

if ( $nonce_ok_step ) {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 );
	}
	$state = get_option( $state_key, null );
	if ( ! is_array( $state ) ) {
		$mig_result = [ 'error' => __( 'وضعیت مهاجرت نیست. دوباره «شروع» بزنید.', 'escapezoom-v2' ) ];
	} elseif ( ! taxonomy_exists( $yith_tax ) || ! taxonomy_exists( $wc_tax ) ) {
		$mig_result = [ 'error' => __( 'تاکسونومی برند آماده نیست.', 'escapezoom-v2' ) ];
	} else {
		$phase        = (string) ( $state['phase'] ?? 'terms' );
		$term_off     = (int) ( $state['term_off'] ?? 0 );
		$product_off  = (int) ( $state['product_off'] ?? 0 );
		$iter         = (int) ( $state['iter'] ?? 0 ) + 1;
		$map          = isset( $state['map'] ) && is_array( $state['map'] ) ? $state['map'] : [];
		$msg          = '';

		try {
			if ( 'terms' === $phase ) {
				$terms = get_terms(
					[
						'taxonomy'   => $yith_tax,
						'hide_empty' => false,
						'number'     => $batch,
						'offset'     => $term_off,
					]
				);
				if ( is_wp_error( $terms ) ) {
					throw new RuntimeException( $terms->get_error_message() );
				}
				if ( $terms === [] ) {
					$phase       = 'products';
					$product_off = 0;
					$msg         = __( 'فاز کپی ترم‌ها تمام شد.', 'escapezoom-v2' );
				} else {
					foreach ( $terms as $t ) {
						$new = get_term_by( 'slug', (string) $t->slug, $wc_tax );
						if ( $new && ! is_wp_error( $new ) ) {
							$new_id = (int) $new->term_id;
							/*
							 * اگر ترم WC از قبل وجود داشت، نام/توضیح را از YITH همگام کن.
							 * در غیر این صورت نام انگلیسی یا قدیمی روی همان slug باقی می‌مانَد.
							 */
							if ( (string) $new->name !== (string) $t->name || (string) $new->description !== (string) $t->description ) {
								wp_update_term(
									$new_id,
									$wc_tax,
									[
										'name'        => $t->name,
										'description' => $t->description,
									]
								);
							}
						} else {
							$ins = wp_insert_term(
								(string) $t->name,
								$wc_tax,
								[
									'slug'        => (string) $t->slug,
									'description' => (string) $t->description,
								]
							);
							if ( is_wp_error( $ins ) ) {
								if ( 'term_exists' === $ins->get_error_code() ) {
									$new_id = (int) $ins->get_error_data();
								} else {
									continue;
								}
							} else {
								$new_id = (int) $ins['term_id'];
							}
						}
						if ( $new_id > 0 ) {
							$copy_termmeta( (int) $t->term_id, $new_id );
							$map[ (string) (int) $t->term_id ] = $new_id;
						}
					}
					$term_off += count( $terms );
					$msg       = sprintf(
						/* translators: %d count */
						__( 'کپی برندها: %d ترم', 'escapezoom-v2' ),
						count( $terms )
					);
				}
			} elseif ( 'products' === $phase ) {
				$q = new WP_Query(
					[
						'post_type'              => 'product',
						'post_status'            => 'any',
						'posts_per_page'         => $batch,
						'offset'                 => $product_off,
						'orderby'                => 'ID',
						'order'                  => 'ASC',
						'fields'                 => 'ids',
						'no_found_rows'          => false,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => true,
						'tax_query'              => [
							[
								'taxonomy' => $yith_tax,
								'operator' => 'EXISTS',
							],
						],
					]
				);
				$ids = $q->posts;
				if ( $ids === [] ) {
					$phase = 'detach';
					$msg   = __( 'فاز اتصال محصولات تمام شد.', 'escapezoom-v2' );
				} else {
					$done = 0;
					foreach ( $ids as $pid ) {
						$pid = (int) $pid;
						$y_terms = get_the_terms( $pid, $yith_tax );
						if ( empty( $y_terms ) || is_wp_error( $y_terms ) ) {
							continue;
						}
						$wc_ids = [];
						foreach ( $y_terms as $yt ) {
							$wid = isset( $map[ (string) (int) $yt->term_id ] ) ? (int) $map[ (string) (int) $yt->term_id ] : 0;
							if ( $wid > 0 ) {
								$wc_ids[] = $wid;
							}
						}
						$wc_ids = array_values( array_unique( $wc_ids ) );
						if ( $wc_ids === [] ) {
							continue;
						}
						wp_set_object_terms( $pid, $wc_ids, $wc_tax, false );
						update_post_meta( $pid, 'product_brand', (string) $wc_ids[0] );
						++$done;
					}
					$product_off += count( $ids );
					$msg          = sprintf(
						__( 'محصولات: %1$d از %2$d (در این بسته اتصال زده شد)', 'escapezoom-v2' ),
						$done,
						count( $ids )
					);
				}
			} elseif ( 'detach' === $phase ) {
				$q = new WP_Query(
					[
						'post_type'      => 'product',
						'post_status'    => 'any',
						'posts_per_page' => $batch,
						'fields'         => 'ids',
						'orderby'        => 'ID',
						'order'          => 'ASC',
						'tax_query'      => [
							[
								'taxonomy' => $yith_tax,
								'operator' => 'EXISTS',
							],
						],
					]
				);
				$ids = $q->posts;
				if ( $ids === [] ) {
					$phase = 'delete_yith';
					$msg   = __( 'قطع YITH از محصولات تمام شد.', 'escapezoom-v2' );
				} else {
					foreach ( $ids as $pid ) {
						$yts = get_the_terms( (int) $pid, $yith_tax );
						if ( ! empty( $yts ) && ! is_wp_error( $yts ) ) {
							$tids = array_map( static fn ( $x ) => (int) $x->term_id, $yts );
							wp_remove_object_terms( (int) $pid, $tids, $yith_tax );
						}
					}
					$msg = sprintf( __( 'قطع YITH از %d محصول', 'escapezoom-v2' ), count( $ids ) );
				}
			} elseif ( 'delete_yith' === $phase ) {
				$terms = get_terms(
					[
						'taxonomy'   => $yith_tax,
						'hide_empty' => false,
						'number'     => $batch,
						'offset'     => 0,
					]
				);
				if ( is_wp_error( $terms ) ) {
					throw new RuntimeException( $terms->get_error_message() );
				}
				if ( $terms === [] ) {
					$phase = 'finished';
					$msg   = __( 'همهٔ ترم‌های YITH حذف شدند.', 'escapezoom-v2' );
				} else {
					foreach ( $terms as $t ) {
						$delete_yith_term( (int) $t->term_id );
					}
					$msg = sprintf( __( 'حذف ترم YITH: %d', 'escapezoom-v2' ), count( $terms ) );
				}
			}

			if ( 'finished' === $phase ) {
				delete_option( $state_key );
				$mig_result = [
					'finished' => true,
					'message'  => __( 'مهاجرت تمام شد. می‌توانید پلاگین YITH Brands را غیرفعال کنید.', 'escapezoom-v2' ),
				];
			} else {
				update_option(
					$state_key,
					[
						'phase'       => $phase,
						'term_off'    => $term_off,
						'product_off' => $product_off,
						'iter'        => $iter,
						'map'         => $map,
					],
					false
				);
				$mig_result = [
					'finished' => false,
					'phase'    => $phase,
					'iter'     => $iter,
					'message'  => $msg,
				];
				if ( $auto ) {
					$auto_next_url = $step_url( $page_url, $nonce_step, true, $pause_sec, $batch );
				}
			}
		} catch ( Throwable $e ) {
			$mig_result = [ 'error' => $e->getMessage() ];
		}
	}
}

$current_state = get_option( $state_key, null );

get_header();
?>

<main class="container mx-auto max-w-3xl px-4 py-10" dir="rtl">
	<h1 class="mb-4 text-xl font-bold">مهاجرت برند: YITH → WooCommerce (<code class="rounded bg-gray-100 px-1">product_brand</code>)</h1>

	<p class="mb-4 text-sm text-gray-600">
		فازها: کپی ترم‌ها و termmeta → اتصال محصول به <code class="rounded bg-gray-100 px-1">product_brand</code> + متای <code class="rounded bg-gray-100 px-1">product_brand</code> → حذف تاکسونومی YITH از محصولات → حذف ترم‌های YITH از دیتابیس.
		بعد از اتمام لود صفحه، <strong><?php echo (int) $pause_sec; ?></strong> ثانیه صبر و ادامهٔ خودکار (در صورت فعال بودن).
		اندازه بسته با <code class="rounded bg-gray-100 px-1">?batch=15</code> در لینک شروع (۵…۵۰). مکث با <code class="rounded bg-gray-100 px-1">?pause=8</code>.
	</p>

	<?php if ( $bad_nonce ) : ?>
		<p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-red-800"><?php esc_html_e( 'توکن امنیتی نامعتبر است.', 'escapezoom-v2' ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $mig_result ) && isset( $mig_result['error'] ) ) : ?>
		<p class="mb-4 rounded border border-red-200 bg-red-50 p-3"><?php echo esc_html( (string) $mig_result['error'] ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $mig_result ) && ! empty( $mig_result['finished'] ) ) : ?>
		<p class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-900"><?php echo esc_html( (string) ( $mig_result['message'] ?? '' ) ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $mig_result ) && empty( $mig_result['error'] ) && isset( $mig_result['finished'] ) && empty( $mig_result['finished'] ) ) : ?>
		<div class="mb-4 rounded border border-gray-200 bg-gray-50 p-4 text-sm">
			<p><strong><?php esc_html_e( 'فاز:', 'escapezoom-v2' ); ?></strong> <?php echo esc_html( (string) $mig_result['phase'] ); ?></p>
			<p><strong><?php esc_html_e( 'گام:', 'escapezoom-v2' ); ?></strong> <?php echo (int) $mig_result['iter']; ?></p>
			<p><?php echo esc_html( (string) $mig_result['message'] ); ?></p>
		</div>
		<?php if ( ! $auto ) : ?>
			<?php $manual = $step_url( $page_url, $nonce_step, false, $pause_sec, $batch ); ?>
			<p><a class="inline-block rounded bg-gray-800 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $manual ); ?>"><?php esc_html_e( 'مرحله بعد (دستی)', 'escapezoom-v2' ); ?></a></p>
		<?php endif; ?>
		<?php if ( $auto && $auto_next_url !== '' ) : ?>
			<p id="ez-brand-mig-countdown" class="rounded border border-blue-200 bg-blue-50 p-3 text-blue-900">
				<?php esc_html_e( 'صفحه که کامل لود شد، حدود', 'escapezoom-v2' ); ?> <strong><?php echo (int) $pause_sec; ?></strong> <?php esc_html_e( 'ثانیه دیگر ادامه می‌یابد…', 'escapezoom-v2' ); ?>
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( is_array( $current_state ) ) : ?>
		<p class="mb-4 text-amber-800"><?php esc_html_e( 'وضعیت نیمه‌تمام در دیتابیس هست. برای ادامهٔ خودکار از لینک زیر استفاده کن یا مرحلهٔ بعد دستی را بعد از اجرا بزن. برای شروع کامل از صفر اول «پاک کردن وضعیت» بزن.', 'escapezoom-v2' ); ?></p>
	<?php endif; ?>

	<form method="post" class="space-y-4">
		<?php wp_nonce_field( $nonce_control, 'ez_brand_mig_nonce' ); ?>
		<p class="flex flex-wrap gap-3">
			<button type="submit" name="ez_brand_mig_start" value="1" class="rounded bg-blue-600 px-4 py-2 text-white">
				<?php esc_html_e( 'شروع مهاجرت (ایجاد state جدید)', 'escapezoom-v2' ); ?>
			</button>
			<button type="submit" name="ez_brand_mig_reset" value="1" class="rounded border border-gray-400 bg-white px-4 py-2" onclick="return confirm('<?php echo esc_js( __( 'وضعیت مهاجرت پاک شود؟', 'escapezoom-v2' ) ); ?>');">
				<?php esc_html_e( 'پاک کردن وضعیت مهاجرت', 'escapezoom-v2' ); ?>
			</button>
		</p>
	</form>

	<?php
	$start_auto = $step_url( $page_url, $nonce_step, true, $pause_sec, $batch );
	if ( is_array( $current_state ) ) :
		?>
		<p class="mt-6"><a class="inline-block rounded bg-blue-600 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $start_auto ); ?>"><?php esc_html_e( 'ادامه خودکار از وضعیت ذخیره‌شده', 'escapezoom-v2' ); ?></a></p>
	<?php endif; ?>
</main>

<?php if ( $auto_next_url !== '' ) : ?>
	<script>
	(function () {
		var next = <?php echo wp_json_encode( $auto_next_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?>;
		var delayMs = <?php echo (int) $auto_pause_ms; ?>;
		var el = document.getElementById('ez-brand-mig-countdown');
		var left = Math.ceil(delayMs / 1000);
		window.addEventListener('load', function () {
			var tick = setInterval(function () {
				left--;
				if (el && left > 0) {
					var s = el.querySelector('strong');
					if (s) s.textContent = String(left);
				}
				if (left <= 0) clearInterval(tick);
			}, 1000);
			window.setTimeout(function () { window.location.assign(next); }, delayMs);
		});
	})();
	</script>
<?php endif; ?>

<?php
get_footer();
