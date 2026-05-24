<?php
/**
 * CRM order status log report helpers.
 *
 * @package escapezoom-v2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string,mixed>
 */
function ez_team_order_status_log_get_data( int $order_id = 0, int $user_id = 0, int $page = 1, int $per_page = 50 ): array {
	$medoo = medoo();
	if ( ! $medoo ) {
		return array(
			'html'       => '<tr><td colspan="6">خطا در اتصال به دیتابیس</td></tr>',
			'pagination' => '',
			'pagination_info' => array(
				'current_page' => 1,
				'total_pages'  => 1,
				'total_logs'   => 0,
				'per_page'     => $per_page,
			),
		);
	}

	$page     = max( 1, $page );
	$per_page = max( 1, min( 200, $per_page ) );
	$offset   = ( $page - 1 ) * $per_page;

	$where = array();
	if ( $order_id > 0 ) {
		$where['order_id'] = $order_id;
	}
	if ( $user_id > 0 ) {
		$where['user_id'] = $user_id;
	}

	$total_logs  = (int) $medoo->count( 'wp_order_status_log', $where );
	$total_pages = max( 1, (int) ceil( $total_logs / $per_page ) );
	if ( $page > $total_pages ) {
		$page   = $total_pages;
		$offset = ( $page - 1 ) * $per_page;
	}

	$where_with_limit = $where;
	$where_with_limit['ORDER'] = array( 'created_at' => 'DESC' );
	$where_with_limit['LIMIT'] = array( $offset, $per_page );

	$logs = $medoo->select( 'wp_order_status_log', '*', $where_with_limit );

	ob_start();
	if ( empty( $logs ) ) :
		?>
		<tr>
			<td colspan="6">لاگی یافت نشد</td>
		</tr>
		<?php
	else :
		$row_number = $offset + 1;
		foreach ( $logs as $log ) :
			$uid = isset( $log['user_id'] ) ? (int) $log['user_id'] : 0;
			?>
			<tr class="border-b border-slate-100">
				<td class="px-4 py-3 text-sm text-slate-700"><?php echo esc_html( (string) $row_number ); ?></td>
				<td class="px-4 py-3 text-sm text-slate-700"><?php echo esc_html( (string) ( $log['order_id'] ?? '' ) ); ?></td>
				<td class="px-4 py-3 text-sm text-slate-700">
					<?php
					if ( $uid > 0 ) {
						$user = get_user_by( 'ID', $uid );
						if ( $user ) {
							echo esc_html( $user->user_login . ' (ID: ' . $uid . ')' );
						} else {
							echo esc_html( 'کاربر (ID: ' . $uid . ')' );
						}
					} else {
						echo esc_html( 'سیستم' );
					}
					?>
				</td>
				<td class="px-4 py-3 text-sm text-slate-700"><?php echo esc_html( (string) ( $log['status_log'] ?? '' ) ); ?></td>
				<td class="px-4 py-3 text-sm text-slate-700"><?php echo esc_html( (string) ( $log['function_used'] ?? '' ) ); ?></td>
				<td class="px-4 py-3 text-sm text-slate-700"><?php echo esc_html( (string) ( $log['created_at'] ?? '' ) ); ?></td>
			</tr>
			<?php
			$row_number++;
		endforeach;
	endif;
	$html = (string) ob_get_clean();

	ob_start();
	if ( $total_pages > 1 ) :
		?>
		<div class="flex items-center justify-between flex-wrap gap-3">
			<span class="text-sm text-slate-500"><?php echo esc_html( number_format_i18n( $total_logs ) ); ?> مورد</span>
			<div class="flex items-center gap-1">
				<?php if ( $page > 1 ) : ?>
					<button type="button" class="ez-osl-page h-8 px-2 rounded border border-slate-300 text-sm" data-page="1">«</button>
					<button type="button" class="ez-osl-page h-8 px-2 rounded border border-slate-300 text-sm" data-page="<?php echo esc_attr( (string) ( $page - 1 ) ); ?>">‹</button>
				<?php else : ?>
					<span class="h-8 px-2 rounded border border-slate-200 text-slate-300 text-sm inline-flex items-center">«</span>
					<span class="h-8 px-2 rounded border border-slate-200 text-slate-300 text-sm inline-flex items-center">‹</span>
				<?php endif; ?>

				<span class="text-sm text-slate-600 px-2">صفحه</span>
				<input id="ez-osl-current-page" class="w-16 h-8 text-center border border-slate-300 rounded text-sm" type="number" min="1" max="<?php echo esc_attr( (string) $total_pages ); ?>" value="<?php echo esc_attr( (string) $page ); ?>">
				<span class="text-sm text-slate-600 px-1">از <?php echo esc_html( (string) $total_pages ); ?></span>

				<?php if ( $page < $total_pages ) : ?>
					<button type="button" class="ez-osl-page h-8 px-2 rounded border border-slate-300 text-sm" data-page="<?php echo esc_attr( (string) ( $page + 1 ) ); ?>">›</button>
					<button type="button" class="ez-osl-page h-8 px-2 rounded border border-slate-300 text-sm" data-page="<?php echo esc_attr( (string) $total_pages ); ?>">»</button>
				<?php else : ?>
					<span class="h-8 px-2 rounded border border-slate-200 text-slate-300 text-sm inline-flex items-center">›</span>
					<span class="h-8 px-2 rounded border border-slate-200 text-slate-300 text-sm inline-flex items-center">»</span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	endif;
	$pagination_html = (string) ob_get_clean();

	return array(
		'html'       => $html,
		'pagination' => $pagination_html,
		'pagination_info' => array(
			'current_page' => $page,
			'total_pages'  => $total_pages,
			'total_logs'   => $total_logs,
			'per_page'     => $per_page,
		),
	);
}

/**
 * @return array<string,mixed>
 */
function ez_team_order_status_log_delete_old(): array {
	$medoo = medoo();
	if ( ! $medoo ) {
		return array(
			'success'       => false,
			'message'       => 'خطا در اتصال به دیتابیس',
			'deleted_count' => 0,
		);
	}

	try {
		$three_months_ago = date( 'Y-m-d H:i:s', strtotime( '-3 months' ) );
		$delete_result = $medoo->delete(
			'wp_order_status_log',
			array(
				'created_at[<]' => $three_months_ago,
			)
		);
		$deleted_count = is_numeric( $delete_result ) ? (int) $delete_result : 0;

		return array(
			'success'       => true,
			'message'       => number_format_i18n( $deleted_count ) . ' لاگ قدیمی (قبل از 3 ماه پیش) حذف شد.',
			'deleted_count' => $deleted_count,
		);
	} catch ( Exception $e ) {
		return array(
			'success'       => false,
			'message'       => 'خطا در حذف لاگ‌های قدیمی: ' . $e->getMessage(),
			'deleted_count' => 0,
		);
	}
}
