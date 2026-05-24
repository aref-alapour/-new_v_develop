<?php
/**
 * Account: list product reviews (کامنت های من) by current user.
 *
 * @var WP_User $current_user
 */

$user_id = get_current_user_id();
$user    = wp_get_current_user();

$per_page  = 20;
$base_url  = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'my-reviews' ) : '';
$page      = isset( $_GET['mr_page'] ) ? max( 1, (int) $_GET['mr_page'] ) : 1;
$base_args = array(
	'user_id' => $user_id,
	'type'    => 'review',
	'status'  => array( 'approve', 'hold' ),
	'orderby' => 'comment_ID',
	'order'   => 'DESC',
);

$total = (int) get_comments( array_merge( $base_args, array( 'count' => true ) ) );

$total_pages = max( 1, (int) ceil( $total / $per_page ) );
if ( $page > $total_pages ) {
	$page = $total_pages;
}
$offset = ( $page - 1 ) * $per_page;

$comments = get_comments(
	array_merge(
		$base_args,
		array(
			'number' => $per_page,
			'offset' => $offset,
		)
	)
);

$default_preset = array();
foreach ( ez_get_product_review_rate_keys() as $k ) {
	$default_preset[ $k ] = 100;
}
?>
<div class="lg:col-span-8 2xl:col-span-9">
	<section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0 h-auto">

		<h2 class="text-base font-bold md:text-lg mb-[33px]">
			<span class="text-xl">کامنت های من</span>
		</h2>

		<?php if ( empty( $comments ) ) : ?>
			<div class="text-[22px] font-bold lg:text-lg text-center lg:my-19 text-gray-500">
				هنوز کامنتی ثبت نکرده‌اید.
			</div>
		<?php else : ?>
			<div class="flex flex-col gap-6">
				<?php foreach ( $comments as $comment ) : ?>
					<?php
					$cid          = (int) $comment->comment_ID;
					$pid          = (int) $comment->comment_post_ID;
					$post         = get_post( $pid );
					$title        = $post ? get_the_title( $post ) : 'محصول حذف‌شده';
					$product_link = $post ? get_permalink( $pid ) : '#';
					$thumb_url    = '';
					if ( $post && 'product' === get_post_type( $pid ) ) {
						$thumb_id = (int) get_post_thumbnail_id( $pid );
						if ( $thumb_id ) {
							$thumb_url = wp_get_attachment_image_url( $thumb_id, 'woocommerce_thumbnail' );
						}
					}
					$type_name = $post ? ez_get_product_review_parent_type_name( $pid ) : 'نامشخص';
					$can_edit  = $post
						&& function_exists( 'ez_user_may_review_product_in_window' )
						&& ! is_wp_error( ez_user_may_review_product_in_window( $user_id, $pid, $user ) );

					$rating_meta = get_comment_meta( $cid, 'comment_rating', true );
					$rating_avg  = get_comment_meta( $cid, 'rating', true );
					$rates_lines = ez_format_review_rates_display_lines( $rating_meta, $type_name );
					$preset_rates = ez_my_reviews_build_preset_rates_from_meta( $rating_meta );

					$status_label = ( '1' === (string) $comment->comment_approved ) ? 'منتشر شده' : 'در انتظار تایید';

					$payload = array(
						'comment_id'         => $cid,
						'product_id'         => $pid,
						'title'              => $title,
						'full_content'     => $comment->comment_content,
						'status_label'       => $status_label,
						'product_type_name'  => $type_name,
						'rates_lines'        => $rates_lines,
						'rating_avg'         => ( $rating_avg !== '' && $rating_avg !== null ) ? (string) $rating_avg : '',
						'can_edit'           => $can_edit,
						'preset_rates'       => $preset_rates,
					);
					$payload_json = wp_json_encode(
						$payload,
						JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
					);
					?>
					<div class="rounded-[14px] border border-[#dbe2ea] bg-[#F9F9F9] p-5 max-lg:p-4">
						<div class="flex gap-4 items-center justify-between">
							<?php if ( $thumb_url ) : ?>
								<a href="<?php echo esc_url( $product_link ); ?>" class="shrink-0 w-20 flex flex-col items-center ">
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" class="w-full object-cover aspect-square rounded-xl" loading="lazy" width="112" height="112">
									<?php echo esc_html( $title ); ?>
								</a>
							<?php else : ?>
								<div class="shrink-0 w-full sm:w-28 aspect-square rounded-xl border border-dashed border-[#dbe2ea] bg-[#eef1f5] flex items-center justify-center text-xs text-[#889BAD]">
									بدون تصویر
								</div>
							<?php endif; ?>
							<div class="flex-1">
								<p class="text-sm text-gray-700 leading-7 line-clamp-1"><?php echo esc_html( ez_my_reviews_excerpt( (string) $comment->comment_content, 100 ) ); ?></p>
								
							</div>
							<span class="text-xs text-[#889BAD]"><?php echo esc_html( $status_label ); ?></span>
						</div>
						<div class="flex justify-end gap-3">
							<button type="button" class="my-reviews-open-detail text-sm font-bold text-[#3F7FF5] hover:underline cursor-pointer bg-transparent border-0 p-0" data-review-json="<?php echo esc_attr( $payload_json ); ?>">
								مشاهده جزئیات
							</button>
							<?php if ( $can_edit && $post ) : ?>
								<button type="button" class="my-reviews-open-edit text-sm font-bold text-[#049654] hover:underline cursor-pointer bg-transparent border-0 p-0" data-review-json="<?php echo esc_attr( $payload_json ); ?>">
									ویرایش کامنت
								</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( $total_pages > 1 ) : ?>
				<nav class="flex flex-wrap justify-center items-center gap-2 mt-8" aria-label="صفحه‌بندی کامنت‌ها">
					<?php
					$prev = max( 1, $page - 1 );
					$next = min( $total_pages, $page + 1 );
					$url_prev = esc_url( add_query_arg( 'mr_page', $prev, $base_url ) );
					$url_next = esc_url( add_query_arg( 'mr_page', $next, $base_url ) );
					?>
					<?php if ( $page > 1 ) : ?>
						<a href="<?php echo $url_prev; ?>" class="px-3 py-1.5 rounded-lg border border-[#dbe2ea] text-sm font-bold text-[#09192D] hover:bg-white">قبلی</a>
					<?php endif; ?>
					<?php
					$start = max( 1, $page - 2 );
					$end   = min( $total_pages, $page + 2 );
					for ( $i = $start; $i <= $end; $i++ ) {
						if ( (int) $i === (int) $page ) {
							echo '<span class="px-3 py-1.5 rounded-lg bg-[#2B7FFF] text-white text-sm font-bold">' . (int) $i . '</span>';
						} else {
							echo '<a href="' . esc_url( add_query_arg( 'mr_page', $i, $base_url ) ) . '" class="px-3 py-1.5 rounded-lg border border-[#dbe2ea] text-sm font-bold text-[#09192D] hover:bg-white">' . (int) $i . '</a>';
						}
					}
					?>
					<?php if ( $page < $total_pages ) : ?>
						<a href="<?php echo $url_next; ?>" class="px-3 py-1.5 rounded-lg border border-[#dbe2ea] text-sm font-bold text-[#09192D] hover:bg-white">بعدی</a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>
		<?php endif; ?>

		<div id="my-reviews-rating-templates" class="hidden" aria-hidden="true">
			<div data-template="escape">
				<?php
				wc_get_template(
					'myaccount/partials/review-rating-buttons.php',
					array(
						'product_type_name' => 'اتاق فرار',
						'preset_rates'      => $default_preset,
					)
				);
				?>
			</div>
			<div data-template="simple">
				<?php
				wc_get_template(
					'myaccount/partials/review-rating-buttons.php',
					array(
						'product_type_name' => '',
						'preset_rates'      => $default_preset,
					)
				);
				?>
			</div>
		</div>

		<div id="my-reviews-detail-overlay" class="fixed inset-0 flex items-center justify-center p-4 bg-black/40" style="display: none; z-index: 100;" role="dialog" aria-modal="true" aria-labelledby="my-reviews-detail-title">
			<div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col">
				<div class="flex items-center justify-between px-4 py-3 border-b border-[#E4EBF0]">
					<h3 id="my-reviews-detail-title" class="text-lg font-bold text-[#09192D]"></h3>
					<button type="button" class="my-reviews-modal-close inline-flex items-center justify-center min-w-11 min-h-11 text-3xl font-light leading-none text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors" aria-label="بستن">&times;</button>
				</div>
				<div class="p-4 overflow-y-auto text-right space-y-3">
					<p class="text-xs text-[#889BAD]" id="my-reviews-detail-status"></p>
					<div class="text-sm text-gray-800 whitespace-pre-wrap break-words" id="my-reviews-detail-body"></div>
					<div>
						<p class="text-xs font-bold text-gray-700 mb-1">امتیازها</p>
						<ul class="text-sm text-gray-700 list-disc list-inside space-y-1" id="my-reviews-detail-rates"></ul>
					</div>
					<p class="text-sm text-gray-700" id="my-reviews-detail-avg-wrap" style="display: none;"><span class="font-bold">میانگین متا (rating):</span> <span id="my-reviews-detail-avg"></span></p>
				</div>
			</div>
		</div>

		<div id="my-reviews-edit-overlay" class="fixed inset-0 flex items-center justify-center p-4 bg-black/40" style="display: none; z-index: 100;" role="dialog" aria-modal="true">
			<div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col">
				<div class="flex items-center justify-between px-4 py-3 border-b border-[#E4EBF0]">
					<h3 class="text-lg font-bold text-[#09192D]">ویرایش نظر</h3>
					<button type="button" class="my-reviews-modal-close inline-flex items-center justify-center min-w-11 min-h-11 text-3xl font-light leading-none text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors" aria-label="بستن">&times;</button>
				</div>
				<div class="p-4 overflow-y-auto">
					<p class="text-sm text-[#62748E] mb-4" id="my-reviews-edit-product-title"></p>
					<form class="send-comment my-reviews-edit-form" method="post">
						<input type="hidden" name="review_comment_id" id="my-reviews-review_comment_id" value="0">
						<div class="mb-4">
							<textarea id="my-reviews-edit-content" name="content" rows="6" class="block w-full p-4 text-sm text-gray-900 border border-gray-200 outline-none rounded-xl placeholder:text-slate-300 focus:border-[#2B7FFF] focus:ring-2 focus:ring-[#2B7FFF]/20" required></textarea>
						</div>
						<div id="my-reviews-rating-root"></div>
						<button type="submit" class="my-reviews-submit w-full bg-[#2B7FFF] text-white py-3.5 rounded-xl font-bold text-base hover:bg-[#1e5fbf] transition-colors">
							ذخیره تغییرات
						</button>
					</form>
				</div>
			</div>
		</div>

	</section>
</div>
