<?php
/**
 * Renders the `embla__slide` HTML for snapshot rows, mirroring the legacy
 * web-service `standardization_products_html_swiper` output so existing
 * carousel wrappers (`embla__container`, `swiper-wrapper`) can drop it in.
 *
 * Card HTML is intentionally kept in this single file (modular: one
 * implementation per UI primitive). Do NOT add a second slide template;
 * extend this one via parameters instead.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render `embla__slide` markup for an array of snapshot rows.
 *
 * @param array<int,array<string,mixed>> $rows
 * @param array<string,mixed>            $options Reserved for future tweaks (badge_ads, ...).
 */
function ez_products_snapshot_render_swiper_slides( array $rows, array $options = array() ): string {
	if ( $rows === array() ) {
		return '';
	}

	$home = home_url();

	ob_start();
	foreach ( $rows as $row ) {
		$pid    = (int) ( $row['product_id'] ?? 0 );
		$title  = (string) ( $row['product_name'] ?? '' );
		$type   = (string) ( $row['product_type'] ?? '' );
		$status = (string) ( $row['product_status'] ?? 'active' );
		$url    = (string) ( $row['product_url'] ?? '' );
		$img    = (string) ( $row['product_image_url'] ?? '' );
		$hood   = (string) ( $row['product_hood'] ?? '' );

		$city_name = '';
		if ( ! empty( $row['product_city'] ) && is_array( $row['product_city'] ) ) {
			$city_name = (string) ( $row['product_city']['name'] ?? '' );
		}

		$rate           = isset( $row['rate'] ) ? (float) $row['rate'] : 0.0;
		$min_price      = isset( $row['min_price'] ) ? (int) $row['min_price'] : 0;
		$is_escaperoom  = $type === 'اتاق فرار';
		$rate_display   = $is_escaperoom ? number_format( $rate, 1 ) : number_format( $rate * 5, 1 );

		// permalink
		if ( $url !== '' && $url[0] !== '/' ) {
			$url = '/' . ltrim( $url, '/' );
		}
		$href = $url !== '' ? esc_url( $home . $url ) : '#';

		if ( $img === '' && function_exists( 'wc_placeholder_img_src' ) ) {
			$img = (string) wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}

		$addr_parts = array();
		if ( $hood !== '' ) {
			$addr_parts[] = $hood;
		}
		if ( $city_name !== '' ) {
			$addr_parts[] = $city_name;
		}
		$addr = implode( ' . ', $addr_parts );

		$alt = trim( $type . ' ' . $title );

		$status_overlay = '';
		$status_badge   = '';
		switch ( $status ) {
			case 'temp':
			case 'deactivated':
				$status_overlay = '<span class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">غیرفعال</span>';
				break;
			case 'expired':
				$status_overlay = '<span class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">اکسپایر شده</span>';
				break;
			case 'soon':
				$status_badge = '<span class="bg-[#2B7FFF] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">به زودی</span>';
				break;
			case 'updated':
				$status_badge = '<span class="bg-[#F21543] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">آپدیت شد</span>';
				break;
		}

		$discount = ez_products_snapshot_get_discount( $row );

		$is_active = ! in_array( $status, array( 'temp', 'deactivated', 'expired', 'soon' ), true );
		?>
<article class="embla__slide" name="product-card" data-product-id="<?php echo esc_attr( (string) $pid ); ?>">
	<div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
		<div class="relative">
			<a href="<?php echo $href; ?>" class="relative block<?php echo $is_active ? '' : ' after:bg-white/60 after:absolute after:top-0 after:left-0 after:bottom-0 after:right-0'; ?>">
				<img alt="<?php echo esc_attr( $alt ); ?>"
					loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
					class="h-[192px] lg:h-[236px] object-cover"
					src="<?php echo esc_url( $img ); ?>"
					style="color: transparent;">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $status_badge;
				?>
			</a>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $status_overlay;
			?>
		</div>
	</div>
	<div class="flex items-center justify-between my-3">
		<span class="text-base font-medium text-[#62748E] leading-none"><?php echo esc_html( $type ); ?></span>
		<?php if ( $is_active && $rate > 0 ) : ?>
		<span class="text-sm rounded-[4px] flex items-center justify-center leading-none pt-px bg-yellow-400 text-slate-900 w-[31px] h-[18.5px]" name="rate"><?php echo esc_html( $rate_display ); ?></span>
		<?php endif; ?>
	</div>
	<div class="flex items-center justify-between">
		<h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?php echo esc_attr( $title ); ?>" name="title">
			<a href="<?php echo $href; ?>"><?php echo esc_html( $title ); ?></a>
		</h3>
	</div>
	<p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
		<svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0" aria-hidden="true">
			<path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374Z" fill="#90A1B9" />
		</svg>
		<span class="text-2xs pt-1" name="address"><?php echo esc_html( $addr ); ?></span>
	</p>
	<?php if ( $is_active && $min_price > 0 ) : ?>
	<div class="flex items-center justify-center gap-x-2 bg-[#ECECEE] px-2 rounded-[6px] mt-3">
		<?php if ( $discount['off_percentage'] > 0 && $discount['expire_date'] > time() ) : ?>
		<span class="bg-[#F21543] text-white rounded-[40px] w-8 h-4 flex items-center justify-center">
			<span class="text-heavy text-md pt-1"><?php echo (int) $discount['off_percentage']; ?></span>
			<span class="text-heavy text-md pt-1">%</span>
		</span>
		<?php endif; ?>
		<div>
			<span class="text-[#62748E] ml-1">از</span>
			<span>
				<span class="ml-px text-md font-bold" name="price"><?php echo esc_html( number_format( $min_price ) ); ?></span>
				<span class="text-[#62748E]">تومان</span>
			</span>
		</div>
	</div>
	<?php endif; ?>
</article>
		<?php
	}

	return (string) ob_get_clean();
}

/**
 * Get discount info for a snapshot row, falling back to post_meta when snapshot.discount_data is null.
 *
 * @param array<string,mixed> $row
 * @return array{off_percentage:int,expire_date:int}
 */
function ez_products_snapshot_get_discount( array $row ): array {
	$pct  = 0;
	$exp  = 0;

	if ( ! empty( $row['discount_data'] ) ) {
		$dd = is_string( $row['discount_data'] ) ? maybe_unserialize( $row['discount_data'] ) : $row['discount_data'];
		if ( is_object( $dd ) ) {
			$pct = (int) ( $dd->special_discount_percentage ?? 0 );
			$exp = (int) ( $dd->special_discount_date ?? 0 );
		} elseif ( is_array( $dd ) ) {
			$pct = (int) ( $dd['special_discount_percentage'] ?? 0 );
			$exp = (int) ( $dd['special_discount_date'] ?? 0 );
		}
	}

	if ( $pct === 0 && ! empty( $row['product_id'] ) ) {
		$pid = (int) $row['product_id'];
		$exp = (int) get_post_meta( $pid, 'special_discount_date', true );
		if ( $exp > time() ) {
			$pct = (int) get_post_meta( $pid, 'special_discount_percentage', true );
		} else {
			$exp = 0;
		}
	}

	return array(
		'off_percentage' => max( 0, $pct ),
		'expire_date'    => max( 0, $exp ),
	);
}
