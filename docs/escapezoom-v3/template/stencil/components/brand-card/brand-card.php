<?php
/**
 * Brand card — server HTML uses a real <a> so links work before/without Stencil hydrating.
 * Stencil `ez-brand-card` remains for client-only / islands; keep markup in sync with `brand-card.tsx`.
 *
 * @package escapezoom
 */

if ( ! function_exists( 'ez_render_brand_card' ) ) {
	/**
	 * @param array<string,mixed> $props Keys: brand_id, slug, name, href, logo, initial, game_count, address.
	 *     Optional: score (int) for badge slot.
	 */
	function ez_render_brand_card( array $props ): string {
		$p = wp_parse_args(
			$props,
			array(
				'brand_id'   => 0,
				'slug'       => '',
				'name'       => '',
				'href'       => '#',
				'logo'       => '',
				'initial'    => '?',
				'game_count' => 0,
				'address'    => '',
				'score'      => 0,
			)
		);

		$href   = (string) $p['href'];
		$name   = (string) $p['name'];
		$logo   = (string) $p['logo'];
		$bid    = (int) $p['brand_id'];
		$slug   = (string) $p['slug'];
		$count  = (int) $p['game_count'];
		$addr   = (string) $p['address'];
		$init   = (string) $p['initial'];
		$score  = (int) $p['score'];

		ob_start();
		?>
		<a
			class="ez-brand-card group block rounded-xl no-underline text-inherit outline-none ring-primary-600 transition-transform duration-300 ease-out focus-visible:ring-2 focus-visible:ring-offset-2"
			href="<?php echo esc_url( $href ); ?>"
			<?php
			if ( '#' !== $href && '' !== $href ) :
				echo ' target="_blank" rel="noopener noreferrer"';
			endif;
			echo $bid > 0 ? ' data-brand-id="' . (int) $bid . '"' : '';
			echo $slug !== '' ? ' data-brand-slug="' . esc_attr( $slug ) . '"' : '';
			?>
		>
			<div class="flex flex-col gap-5 max-lg:gap-4 pt-0.5 transition-transform duration-300 ease-out group-hover:scale-105">
				<div class="relative block">
			<?php if ( $logo !== '' ) : ?>
					<img
						class="aspect-square w-full rounded-xl object-cover shadow-md transition-shadow duration-300 group-hover:shadow-lg"
						src="<?php echo esc_url( $logo ); ?>"
						loading="lazy"
						alt="<?php echo esc_attr( $name ); ?>"
					/>
			<?php else : ?>
					<div class="flex aspect-square items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 shadow-md">
						<span class="text-lg font-medium text-slate-400"><?php echo esc_html( $init !== '' ? $init : '?' ); ?></span>
					</div>
			<?php endif; ?>

			<?php if ( $score > 0 ) : ?>
					<span class="absolute right-2 top-2 z-[1] inline-block rounded-lg bg-emerald-500 px-2 py-1 text-xs font-bold text-white shadow-md"><?php echo (int) $score; ?></span>
			<?php endif; ?>
				</div>

				<div class="flex flex-col gap-1.5 pt-3">
					<div class="flex items-center justify-between">
						<span class="line-clamp-1 text-sm font-semibold text-slate-800"><?php echo esc_html( $name ); ?></span>
				<?php if ( $count > 0 ) : ?>
						<span class="flex max-lg:hidden items-center gap-1.5 rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">
							<?php echo (int) $count; ?>
							<svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none" aria-hidden="true">
								<path d="M3.55248 5.52134C3.55248 4.71176 3.42316 3.46277 3.92084 2.73396C5.02103 1.12537 7.35366 1.33882 8.1766 2.90895C8.58023 3.68007 8.42838 4.75791 8.447 5.52134M3.55248 5.52134C2.28182 5.52134 2.02221 6.23477 1.82823 6.79533C1.64894 7.42511 1.46574 8.92985 1.74593 10.5788C1.95559 11.6288 2.77363 12.0903 3.47704 12.149C4.15009 12.2047 6.99118 12.1836 7.81314 12.1836C9.08771 12.1836 9.88322 11.9086 10.2575 10.6481C10.4367 9.66828 10.4857 7.91547 10.1869 6.79533C9.79113 5.67518 8.9917 5.52134 8.447 5.52134M3.55248 5.52134C4.89857 5.46846 7.67696 5.47904 8.447 5.52134" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M6 7.71875V9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
							</svg>
						</span>
				<?php endif; ?>
					</div>

			<?php if ( $addr !== '' ) : ?>
					<div class="flex items-center gap-1 text-xs text-slate-500">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
						</svg>
						<span class="line-clamp-1"><?php echo esc_html( $addr ); ?></span>
					</div>
			<?php endif; ?>
				</div>
			</div>
		</a>
		<?php
		return (string) ob_get_clean();
	}
}

if ( ! function_exists( 'ez_render_brand_card_term' ) ) {
	/**
	 * @param WP_Term $term product_brand term.
	 */
	function ez_render_brand_card_term( WP_Term $term ): string {
		$img_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		$logo   = '';
		if ( $img_id > 0 ) {
			$url = wp_get_attachment_image_url( $img_id, 'large' );
			if ( is_string( $url ) && $url !== '' ) {
				$logo = $url;
			}
		}
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			$link = '#';
		}
		$address = '';
		if ( defined( 'EZ_BRAND_META_ADDRESS' ) ) {
			$address = (string) get_term_meta( $term->term_id, EZ_BRAND_META_ADDRESS, true );
		}

		$name    = $term->name;
		$initial = function_exists( 'mb_substr' )
			? mb_substr( (string) $name, 0, 1, 'UTF-8' )
			: substr( (string) $name, 0, 1 );

		return ez_render_brand_card(
			array(
				'brand_id'   => (int) $term->term_id,
				'slug'       => (string) $term->slug,
				'name'       => (string) $name,
				'href'       => (string) $link,
				'logo'       => (string) $logo,
				'initial'    => $initial !== '' ? $initial : '?',
				'game_count' => (int) $term->count,
				'address'    => $address,
			)
		);
	}
}
