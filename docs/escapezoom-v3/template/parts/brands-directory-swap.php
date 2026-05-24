<?php
/**
 * Grid + pagination fragment (initial render + HTMX).
 * Optional: set $ctx before include to avoid a second query on the full page.
 */
defined( 'ABSPATH' ) || exit;

if ( ! isset( $ctx ) ) {
	$ctx = ez_brands_directory_get_list_context();
}

if ( $ctx === null ) {
	echo '<div id="brands-directory-swap" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">';
	echo esc_html__( 'تاکسونومی برند محصول (WooCommerce) فعال نیست.', 'escapezoom' );
	echo '</div>';
	return;
}

$brands      = $ctx['brands'];
$page_num    = (int) $ctx['page_num'];
$total_pages = (int) $ctx['total_pages'];

?>
<div id="brands-directory-swap" class="relative">
	<div class="relative w-full grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-x-8 gap-y-10">
		<?php
		if ( function_exists( 'ez_brands_directory_profile_hx_is_fragment' ) && ez_brands_directory_profile_hx_is_fragment() ) {
			ez_brands_directory_profile_hx_mark( 'swap_before_render_cards' );
		}
		foreach ( $brands as $brand ) :
			if ( $brand instanceof WP_Term ) :
				?>
		<div class="min-w-0">
				<?php echo ez_render_brand_card_term( $brand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
				<?php
			endif;
		endforeach;
		if ( function_exists( 'ez_brands_directory_profile_hx_is_fragment' ) && ez_brands_directory_profile_hx_is_fragment() ) {
			ez_brands_directory_profile_hx_mark( 'swap_after_render_cards' );
		}
		?>
	</div>

	<?php if ( empty( $brands ) ) : ?>
		<p class="mt-12 text-center text-slate-500">
			<?php esc_html_e( 'هنوز برندی ثبت نشده است.', 'escapezoom' ); ?>
		</p>
	<?php endif; ?>

	<?php
	if ( function_exists( 'ez_brands_directory_profile_hx_is_fragment' ) && ez_brands_directory_profile_hx_is_fragment() ) {
		ez_brands_directory_profile_hx_mark( 'swap_before_pagination_markup' );
	}
	echo ez_brands_directory_pagination_html( $page_num, $total_pages ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( function_exists( 'ez_brands_directory_profile_hx_is_fragment' ) && ez_brands_directory_profile_hx_is_fragment() ) {
		ez_brands_directory_profile_hx_mark( 'swap_after_pagination_markup' );
	}
	?>
</div>
