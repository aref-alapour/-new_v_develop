<?php
/**
 * WooCommerce submenu: list checkout intents (CRM table wp_checkout_intent via Medoo).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'woocommerce',
			'سبد چک‌اوت',
			'سبد چک‌اوت',
			'manage_woocommerce',
			'ez_checkout_intent',
			'ez_checkout_intent_admin_page'
		);
	}
);

/**
 * @return void
 */
function ez_checkout_intent_admin_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'escapezoom-v2' ) );
	}

	$medoo = function_exists( 'medoo' ) ? medoo() : null;
	if ( ! $medoo ) {
		echo '<div class="wrap"><h1>سبد چک‌اوت</h1><p>خطا در اتصال به دیتابیس.</p></div>';
		return;
	}

	if ( ! function_exists( 'ez_checkout_intent_table_ready' ) || ! ez_checkout_intent_table_ready() ) {
		echo '<div class="wrap">';
		echo '<h1>سبد چک‌اوت</h1>';
		echo '<div class="notice notice-warning"><p>';
		echo esc_html( 'جدول پیدا نشد. DDL بخش ۴ را روی همان دیتابیس CRM که wp_markting در آن است اجرا کنید، سپس در MySQL دستور SHOW TABLES LIKE \'wp_checkout_intent\' را بزنید.' );
		echo '</p><p><code>escapezoom_ddl_manual_2026.sql</code> — SECTION 4</p></div>';
		echo '</div>';
		return;
	}

	$t = ez_checkout_intent_table();

	if ( isset( $_POST['ez_checkout_intent_action'] ) && $_POST['ez_checkout_intent_action'] === 'delete_row' ) {
		check_admin_referer( 'ez_checkout_intent_delete' );
		$row_id = isset( $_POST['row_id'] ) ? absint( $_POST['row_id'] ) : 0;
		if ( $row_id > 0 ) {
			try {
				$medoo->delete( $t, array( 'id' => $row_id ) );
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'ردیف حذف شد.', 'escapezoom-v2' ) . '</p></div>';
			} catch ( Throwable $e ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
			}
		}
	}

	$term = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

	$per_page = 25;
	$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$offset   = ( $paged - 1 ) * $per_page;

	$where_count = array();
	if ( $term !== '' && ctype_digit( $term ) ) {
		$where_count['OR'] = array(
			'id'         => absint( $term ),
			'user_id'    => absint( $term ),
			'product_id' => absint( $term ),
			'sans_ts'    => absint( $term ),
		);
	}

	try {
		$total = (int) $medoo->count( $t, $where_count );
	} catch ( Throwable $e ) {
		echo '<div class="wrap"><h1>سبد چک‌اوت</h1><p>' . esc_html( $e->getMessage() ) . '</p></div>';
		return;
	}

	$where_sel = $where_count;
	if ( empty( $where_sel ) ) {
		$where_sel = array();
	}
	$where_sel['ORDER'] = array( 'updated_at' => 'DESC' );
	$where_sel['LIMIT'] = array( $offset, $per_page );

	try {
		$rows = $medoo->select( $t, '*', $where_sel );
	} catch ( Throwable $e ) {
		echo '<div class="wrap"><h1>سبد چک‌اوت</h1><p>' . esc_html( $e->getMessage() ) . '</p></div>';
		return;
	}

	if ( ! is_array( $rows ) ) {
		$rows = array();
	}

	$base_args = array( 'page' => 'ez_checkout_intent' );
	if ( $term !== '' ) {
		$base_args['s'] = $term;
	}

	echo '<div class="wrap">';
	echo '<h1>سبد چک‌اوت (ردیف‌های باز)</h1>';
	echo '<p class="description">' . esc_html__( 'پس از ثبت سفارش، ردیف intent به‌صورت خودکار حذف می‌شود؛ این فقط سبد/چک‌اوت نیمه‌است.', 'escapezoom-v2' ) . '</p>';

	echo '<form method="get" class="ez-checkout-intent-filter" style="margin:1em 0;">';
	echo '<input type="hidden" name="page" value="ez_checkout_intent" />';
	echo '<label for="ez_ci_s">' . esc_html__( 'جستجو با شناسه عددی', 'escapezoom-v2' ) . ' </label>';
	echo '<input type="search" name="s" id="ez_ci_s" value="' . esc_attr( $term ) . '" placeholder="' . esc_attr__( 'شناسه کاربر، محصول، سانس یا id ردیف', 'escapezoom-v2' ) . '" /> ';
	submit_button( __( 'جستجو', 'escapezoom-v2' ), 'secondary', '', false );
	echo '</form>';

	$total_pages = max( 1, (int) ceil( $total / $per_page ) );
	echo '<p>' . esc_html( sprintf( 'تعداد کل ردیف‌ها: %d', $total ) ) . '</p>';

	if ( $total_pages > 1 ) {
		echo '<div class="tablenav top"><div class="tablenav-pages">';
		echo wp_kses_post(
			paginate_links(
				array(
					'base'      => add_query_arg( array_merge( $base_args, array( 'paged' => '%#%' ) ), admin_url( 'admin.php' ) ),
					'format'    => '',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'total'     => $total_pages,
					'current'   => $paged,
				)
			)
		);
		echo '</div></div>';
	}

	echo '<table class="widefat striped"><thead><tr>';
	$headers = array( 'id', 'به‌روزرسانی', 'کاربر', 'محصول', 'sans_ts', 'تعداد', 'SMS یادآوری', 'token', 'حذف' );
	foreach ( $headers as $h ) {
		echo '<th scope="col">' . esc_html( $h ) . '</th>';
	}
	echo '</tr></thead><tbody>';

	if ( empty( $rows ) ) {
		echo '<tr><td colspan="9">' . esc_html__( 'ردیفی نیست.', 'escapezoom-v2' ) . '</td></tr>';
	} else {
		foreach ( $rows as $row ) {
			$id     = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
			$uid    = isset( $row['user_id'] ) && $row['user_id'] !== null ? absint( $row['user_id'] ) : 0;
			$pid    = isset( $row['product_id'] ) ? absint( $row['product_id'] ) : 0;
			$sans   = isset( $row['sans_ts'] ) ? absint( $row['sans_ts'] ) : 0;
			$qty    = isset( $row['qty'] ) ? absint( $row['qty'] ) : 0;
			$tok    = isset( $row['intent_token'] ) ? (string) $row['intent_token'] : '';
			$tshort = strlen( $tok ) > 12 ? substr( $tok, 0, 8 ) . '…' : $tok;

			$title = $pid ? get_the_title( $pid ) : '';
			if ( $title === '' && $pid ) {
				$title = '#' . $pid;
			}

			$user_html = '&mdash;';
			if ( $uid > 0 ) {
				$user_html = '<a href="' . esc_url( get_edit_user_link( $uid ) ) . '">#' . esc_html( (string) $uid ) . '</a>';
			}

			echo '<tr>';
			echo '<td>' . esc_html( (string) $id ) . '</td>';
			echo '<td>' . esc_html( isset( $row['updated_at'] ) ? (string) $row['updated_at'] : '' ) . '</td>';
			echo '<td>' . wp_kses_post( $user_html ) . '</td>';
			echo '<td>';
			if ( $pid ) {
				$plink = get_edit_post_link( $pid, 'raw' );
				if ( $plink ) {
					echo '<a href="' . esc_url( $plink ) . '">' . esc_html( $title ) . '</a>';
				} else {
					echo esc_html( $title );
				}
				echo '<br/><small>' . esc_html( 'ID: ' . $pid ) . '</small>';
			} else {
				echo '&mdash;';
			}
			echo '</td>';
			echo '<td>' . esc_html( (string) $sans ) . '</td>';
			echo '<td>' . esc_html( (string) $qty ) . '</td>';
			$sms_rem = isset( $row['sms_reminder_sent_at'] ) && $row['sms_reminder_sent_at'] !== null && $row['sms_reminder_sent_at'] !== ''
				? (string) $row['sms_reminder_sent_at']
				: '';
			echo '<td>' . ( $sms_rem !== '' ? esc_html( $sms_rem ) : '&mdash;' ) . '</td>';
			echo '<td title="' . esc_attr( $tok ) . '"><code>' . esc_html( $tshort ) . '</code></td>';
			echo '<td>';
			if ( $id > 0 ) {
				echo '<form method="post" style="display:inline-block;" onsubmit="return confirm(\'' . esc_js( __( 'این ردیف حذف شود؟', 'escapezoom-v2' ) ) . '\');">';
				wp_nonce_field( 'ez_checkout_intent_delete' );
				echo '<input type="hidden" name="ez_checkout_intent_action" value="delete_row" />';
				echo '<input type="hidden" name="row_id" value="' . esc_attr( (string) $id ) . '" />';
				submit_button( __( 'حذف', 'escapezoom-v2' ), 'delete small', 'submit', false );
				echo '</form>';
			} else {
				echo '&mdash;';
			}
			echo '</td>';
			echo '</tr>';
		}
	}

	echo '</tbody></table>';

	if ( $total_pages > 1 ) {
		echo '<div class="tablenav bottom"><div class="tablenav-pages">';
		echo wp_kses_post(
			paginate_links(
				array(
					'base'      => add_query_arg( array_merge( $base_args, array( 'paged' => '%#%' ) ), admin_url( 'admin.php' ) ),
					'format'    => '',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'total'     => $total_pages,
					'current'   => $paged,
				)
			)
		);
		echo '</div></div>';
	}

	echo '</div>';
}
