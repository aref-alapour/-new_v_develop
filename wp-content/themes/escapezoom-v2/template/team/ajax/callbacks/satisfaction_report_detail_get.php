<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$detail_type = isset( $_POST['detail_type'] ) ? sanitize_key( wp_unslash( $_POST['detail_type'] ) ) : '';
if ( $detail_type === '' ) {
	wp_send_json_error( array( 'message' => 'نوع جزئیات مشخص نشده است.' ) );
}

if ( $detail_type === 'comment' ) {
	$comment_id = isset( $_POST['comment_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['comment_id'] ) ) : 0;
	if ( $comment_id < 1 ) {
		wp_send_json_error( array( 'message' => 'شناسه کامنت نامعتبر است.' ) );
	}

	$comment = get_comment( $comment_id );
	if ( ! ( $comment instanceof WP_Comment ) ) {
		wp_send_json_error( array( 'message' => 'کامنت مورد نظر پیدا نشد.' ) );
	}

	$wp_user     = get_user_by( 'id', (int) $comment->user_id );
	$author_name = '';
	if ( $wp_user && $wp_user->exists() ) {
		$author_name = trim( $wp_user->user_firstname . ' ' . $wp_user->user_lastname );
		if ( $author_name === '' ) {
			$author_name = (string) $wp_user->display_name;
		}
	}
	if ( $author_name === '' ) {
		$author_name = (string) $comment->comment_author;
	}

	$approved_raw = isset( $comment->comment_approved ) ? (string) $comment->comment_approved : '';
	$status_label = 'نامشخص';
	$status_class = 'text-slate-700 bg-slate-100';
	if ( $approved_raw === '1' ) {
		$status_label = 'منتشر شده';
		$status_class = 'text-emerald-700 bg-emerald-100';
	} elseif ( $approved_raw === '0' || $approved_raw === 'hold' ) {
		$status_label = 'عدم انتشار';
		$status_class = 'text-amber-700 bg-amber-100';
	} elseif ( $approved_raw === 'trash' ) {
		$status_label = 'حذف شده';
		$status_class = 'text-rose-700 bg-rose-100';
	}

	$rating_raw   = get_comment_meta( $comment_id, 'rating', true );
	$rating_value = is_numeric( $rating_raw ) ? (float) $rating_raw : null;
	$rating_label = $rating_value !== null ? rtrim( rtrim( number_format( $rating_value, 2, '.', '' ), '0' ), '.' ) : '-';

	$comment_ratings_raw = get_comment_meta( $comment_id, 'comment_rating', true );
	$pick_rate = static function ( $arr, $key ): float {
		if ( ! is_array( $arr ) ) {
			return 0.0;
		}
		if ( isset( $arr[ $key ] ) ) {
			return (float) intval( (string) $arr[ $key ] ) / 20;
		}
		$k = (string) $key;
		if ( isset( $arr[ $k ] ) ) {
			return (float) intval( (string) $arr[ $k ] ) / 20;
		}
		return 0.0;
	};

	$sub_ratings = array();
	if ( is_array( $comment_ratings_raw ) ) {
		$sub_ratings = array(
			array( 'label' => 'فضاسازی', 'value' => $pick_rate( $comment_ratings_raw, 1094 ) ),
			array( 'label' => 'تازگی و خلاقیت', 'value' => $pick_rate( $comment_ratings_raw, 1098 ) ),
			array( 'label' => 'کیفیت و معما', 'value' => $pick_rate( $comment_ratings_raw, 1095 ) ),
			array( 'label' => 'بازیگردانی و اکت', 'value' => $pick_rate( $comment_ratings_raw, 1096 ) ),
			array( 'label' => 'برخورد پرسنل', 'value' => $pick_rate( $comment_ratings_raw, 1097 ) ),
		);
	}

	$sub_ratings_html = '';
	if ( ! empty( $sub_ratings ) ) {
		$rows_html = '';
		foreach ( $sub_ratings as $item ) {
			$value = max( 0, min( 5, (float) ( $item['value'] ?? 0 ) ) );
			$rows_html .= sprintf(
				'<div class="flex items-center gap-3">
					<div class="w-40 text-xs font-bold text-slate-600">%1$s</div>
					<div class="flex-1 h-1.5 rounded-full bg-slate-200 overflow-hidden"><div class="h-1.5 rounded-full bg-[#2B7FFF]" style="width:%2$s%%;"></div></div>
					<div class="w-9 text-left text-xs font-bold text-slate-700">%3$s</div>
				</div>',
				esc_html( (string) ( $item['label'] ?? '' ) ),
				esc_attr( (string) round( $value * 20, 2 ) ),
				esc_html( rtrim( rtrim( number_format( $value, 2, '.', '' ), '0' ), '.' ) )
			);
		}
		$sub_ratings_html = '<div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3"><div class="text-xs font-bold text-slate-500 mb-3">ریز امتیازها</div><div class="space-y-3">' . $rows_html . '</div></div>';
	}

	$stored_level = (int) get_comment_meta( $comment_id, 'user_level', true );
	$level_badge_html = '';
	if ( function_exists( 'ez_comment_badge_by_stored_level' ) ) {
		ob_start();
		ez_comment_badge_by_stored_level( (int) $comment->user_id, 'text-xs font-bold px-2 py-0.5 rounded-lg', $stored_level );
		$level_badge_html = (string) ob_get_clean();
	}
	if ( $level_badge_html === '' ) {
		$level_text = 'نامشخص';
		if ( $stored_level === 10 ) {
			$level_text = 'مجموعه دار';
		} elseif ( $stored_level === 1 ) {
			$level_text = 'تازه وارد';
		} elseif ( $stored_level === 2 ) {
			$level_text = 'نوپا';
		} elseif ( $stored_level === 3 ) {
			$level_text = 'با تجربه';
		} elseif ( $stored_level >= 4 ) {
			$level_text = 'کارکشته';
		}
		$level_badge_html = '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold text-slate-700 bg-slate-100">' . esc_html( $level_text ) . '</span>';
	}

	$comment_date = function_exists( 'parsidate' ) ? parsidate( 'Y.m.d H:i', strtotime( (string) $comment->comment_date ), 'fa' ) : date_i18n( 'Y.m.d H:i', strtotime( (string) $comment->comment_date ) );
	$avatar_html  = get_avatar( (int) $comment->user_id ?: $comment->comment_author_email, 56, '', $author_name, array( 'class' => 'w-12 h-12 rounded-lg object-cover' ) );

	$reply_rows = get_comments(
		array(
			'parent' => $comment_id,
			'status' => 'approve',
			'type'   => 'comment',
			'number' => 1,
			'order'  => 'DESC',
		)
	);
	$reply = ! empty( $reply_rows[0] ) ? $reply_rows[0] : null;

	$reply_html = '';
	if ( $reply instanceof WP_Comment ) {
		$reply_user       = get_user_by( 'id', (int) $reply->user_id );
		$reply_author     = $reply_user && $reply_user->exists() ? ( $reply_user->display_name ?: (string) $reply->comment_author ) : (string) $reply->comment_author;
		$reply_date_label = function_exists( 'parsidate' ) ? parsidate( 'Y.m.d H:i', strtotime( (string) $reply->comment_date ), 'fa' ) : date_i18n( 'Y.m.d H:i', strtotime( (string) $reply->comment_date ) );

		$reply_html = sprintf(
			'<div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
				<div class="text-xs font-bold text-slate-500 mb-2">پاسخ مجموعه</div>
				<div class="text-sm font-bold text-slate-700">%1$s</div>
				<div class="text-xs text-slate-400 mt-1">%2$s</div>
				<div class="text-sm text-slate-700 mt-2 whitespace-pre-wrap">%3$s</div>
			</div>',
			esc_html( $reply_author ),
			esc_html( $reply_date_label ),
			esc_html( (string) $reply->comment_content )
		);
	}

	$html = sprintf(
		'<div class="rounded-xl border border-slate-200 p-4 bg-white">
			<div class="flex items-start gap-3">
				%1$s
				<div>
					<p class="text-sm font-extrabold text-slate-800">%2$s</p>
					<div class="flex items-center gap-2 mt-1">%3$s <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-bold %4$s">%5$s</span></div>
					<p class="text-xs text-slate-500 mt-1">%6$s</p>
				</div>
			</div>
			<div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
				<div class="text-xs text-slate-500">میانگین امتیاز</div>
				<div class="text-lg font-extrabold text-slate-800 mt-1">%7$s</div>
			</div>
			%8$s
			<div class="mt-4 text-sm leading-8 text-slate-700 whitespace-pre-wrap">%9$s</div>
			%10$s
		</div>',
		$avatar_html,
		esc_html( $author_name ),
		$level_badge_html,
		esc_attr( $status_class ),
		esc_html( $status_label ),
		esc_html( (string) $comment_date ),
		esc_html( $rating_label ),
		$sub_ratings_html,
		esc_html( (string) $comment->comment_content ),
		$reply_html
	);

	wp_send_json_success(
		array(
			'title' => 'جزئیات کامنت',
			'html'  => $html,
		)
	);
}

if ( $detail_type === 'owner_cancel' ) {
	$medoo      = medoo();
	$request_id = isset( $_POST['request_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['request_id'] ) ) : 0;
	$order_id   = isset( $_POST['order_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
	if ( ! $medoo ) {
		wp_send_json_error( array( 'message' => 'اتصال دیتابیس در دسترس نیست.' ) );
	}

	$request = null;
	if ( $request_id > 0 ) {
		$request = $medoo->get( 'cancellation_requests', '*', array( 'ID' => $request_id ) );
	}
	if ( ! $request && $order_id > 0 ) {
		$request = $medoo->get(
			'cancellation_requests',
			'*',
			array(
				'order_id'        => $order_id,
				'requester_type'  => 'owner',
				'ORDER'           => array( 'created_at' => 'DESC' ),
			)
		);
	}
	if ( ! $request ) {
		wp_send_json_error( array( 'message' => 'درخواست کنسلی مالک پیدا نشد.' ) );
	}

	$resolved_order_id = (int) ( $request['order_id'] ?? 0 );
	$order             = $resolved_order_id > 0 ? wc_get_order( $resolved_order_id ) : null;
	$product_id        = (int) ( $request['product_id'] ?? 0 );
	$product_title     = $product_id > 0 ? get_the_title( $product_id ) : '-';
	$created_at        = (int) ( $request['created_at'] ?? 0 );
	$sans_time         = (int) ( $request['sans_time'] ?? 0 );
	$created_label     = $created_at > 0 ? ( function_exists( 'parsidate' ) ? parsidate( 'Y.m.d H:i', $created_at, 'fa' ) : date_i18n( 'Y.m.d H:i', $created_at ) ) : '-';
	$sans_label        = $sans_time > 0 ? ( function_exists( 'parsidate' ) ? parsidate( 'Y.m.d H:i', $sans_time, 'fa' ) : date_i18n( 'Y.m.d H:i', $sans_time ) ) : '-';

	$buyer_name  = '-';
	$buyer_phone = '-';
	if ( $order ) {
		$buyer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		if ( $buyer_name === '' ) {
			$buyer_name = '-';
		}
		$buyer_phone = $order->get_billing_phone() ? (string) $order->get_billing_phone() : '-';
	}

	$reason_label = '-';
	if ( ! empty( $request['reason_id'] ) && function_exists( 'cancellation_reasons' ) ) {
		$reason_map = cancellation_reasons();
		$rid        = (int) $request['reason_id'];
		if ( isset( $reason_map[ $rid ] ) ) {
			$reason_label = (string) $reason_map[ $rid ];
		}
	}

	$html = sprintf(
		'<div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
				<div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">کد رزرو</p><p class="text-sm font-bold text-slate-800">%1$s</p></div>
				<div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">تاریخ درخواست</p><p class="text-sm font-bold text-slate-800">%2$s</p></div>
				<div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">سانس لغوشده</p><p class="text-sm font-bold text-slate-800">%3$s</p></div>
				<div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">نام بازی</p><p class="text-sm font-bold text-slate-800">%4$s</p></div>
				<div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">نام پلیر</p><p class="text-sm font-bold text-slate-800">%5$s</p></div>
				<div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">شماره تماس</p><p class="text-sm font-bold text-slate-800">%6$s</p></div>
			</div>
			<div class="rounded-lg border border-rose-200 bg-rose-50 p-3">
				<p class="text-xs text-rose-600">دلیل لغو توسط مجموعه</p>
				<p class="text-sm font-bold text-rose-700 mt-1">%7$s</p>
			</div>
		</div>',
		esc_html( (string) $resolved_order_id ),
		esc_html( $created_label ),
		esc_html( $sans_label ),
		esc_html( (string) $product_title ),
		esc_html( $buyer_name ),
		esc_html( $buyer_phone ),
		esc_html( $reason_label )
	);

	wp_send_json_success(
		array(
			'title' => 'جزئیات کنسلی مالک',
			'html'  => $html,
		)
	);
}

wp_send_json_error( array( 'message' => 'نوع جزئیات پشتیبانی نمی‌شود.' ) );
