<?php
/**
 * Brands directory pagination (HTMX + progressive enhancement).
 *
 * @var int        $ez_brands_current_page
 * @var int        $ez_brands_total_pages
 * @var callable(int): string $ez_brands_resolve_page_url
 * @var string     $ez_brands_hx_nonce  Nonce for {@see ez_brands_directory_hx_fetch_url()}.
 * @var string     $ez_brands_hx_ind    Space-prefixed hx-indicator attribute fragment.
 */
defined( 'ABSPATH' ) || exit;

$ez_brands_current_page = isset( $ez_brands_current_page ) ? (int) $ez_brands_current_page : 1;
$ez_brands_total_pages  = isset( $ez_brands_total_pages ) ? (int) $ez_brands_total_pages : 1;
$ez_brands_hx_ind       = isset( $ez_brands_hx_ind ) ? (string) $ez_brands_hx_ind : '';
$ez_brands_hx_nonce     = isset( $ez_brands_hx_nonce ) ? (string) $ez_brands_hx_nonce : wp_create_nonce( 'ez_brands_hx' );

$ez_brands_resolve_page_url = isset( $ez_brands_resolve_page_url )
	&& is_callable( $ez_brands_resolve_page_url )
	? $ez_brands_resolve_page_url
	: static function ( int $p ): string {
		return ez_brands_directory_build_page_url( $p );
	};

ob_start();

if ( $ez_brands_current_page > 1 ) :
	$prev_page = $ez_brands_current_page - 1;
	$prev_url  = $ez_brands_resolve_page_url( $prev_page );
	?>
<a class="ez-brands-pagination__arrow ez-brands-pagination__arrow-prev inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-navyBlue transition hover:border-primary-500 hover:text-primary-600"
   href="<?php echo esc_url( $prev_url ); ?>"
   hx-get="<?php echo esc_url( ez_brands_directory_hx_fetch_url( $prev_page, $ez_brands_hx_nonce ) ); ?>"
   hx-target="#brands-directory-swap"
   hx-swap="outerHTML"
	<?php echo $ez_brands_hx_ind; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
   aria-label="<?php esc_attr_e( 'یک صفحه قبل', 'escapezoom' ); ?>"
   title="<?php echo esc_attr__( 'یک صفحه قبل', 'escapezoom' ); ?>">
	<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180" aria-hidden="true"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
</a>
	<?php
endif;

$ez_brands_pg_prev = (string) ob_get_clean();

ob_start();

if ( $ez_brands_current_page < $ez_brands_total_pages ) :
	$next_page = $ez_brands_current_page + 1;
	$next_url  = $ez_brands_resolve_page_url( $next_page );
	?>
<a class="ez-brands-pagination__arrow ez-brands-pagination__arrow-next inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-navyBlue transition hover:border-primary-500 hover:text-primary-600"
   href="<?php echo esc_url( $next_url ); ?>"
   hx-get="<?php echo esc_url( ez_brands_directory_hx_fetch_url( $next_page, $ez_brands_hx_nonce ) ); ?>"
   hx-target="#brands-directory-swap"
   hx-swap="outerHTML"
	<?php echo $ez_brands_hx_ind; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
   aria-label="<?php esc_attr_e( 'یک صفحه بعد', 'escapezoom' ); ?>"
   title="<?php echo esc_attr__( 'یک صفحه بعد', 'escapezoom' ); ?>">
	<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
</a>
	<?php
endif;

$ez_brands_pg_next = (string) ob_get_clean();

ob_start();
foreach ( ez_brands_directory_pagination_numbers( $ez_brands_current_page, $ez_brands_total_pages ) as $slot ) {
	if ( $slot === null ) {
		echo '<span class="shrink-0 px-1 text-slate-400" aria-hidden="true">…</span>';
		continue;
	}
	$i          = (int) $slot;
	$page_url   = $ez_brands_resolve_page_url( $i );
	$is_current = ( $i === $ez_brands_current_page );
	$current_classes = 'min-w-9 shrink-0 rounded-lg bg-primary-600 px-2.5 py-1.5 text-center text-sm font-bold text-white shadow-md shadow-primary-600/25 pointer-events-none';
	$link_classes    = 'min-w-9 shrink-0 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-center text-sm font-medium text-navyBlue transition hover:border-primary-500 hover:text-primary-600';
	$edge_label      = '';
	if ( 1 === $i ) {
		$edge_label = __( 'اولین صفحه', 'escapezoom' );
	} elseif ( (int) $i === (int) $ez_brands_total_pages ) {
		$edge_label = __( 'آخرین صفحه', 'escapezoom' );
	}
	if ( $is_current ) {
		echo '<span class="' . esc_attr( $current_classes ) . '" aria-current="page" title="' . esc_attr(
			sprintf(
				/* translators: %d: current page number */
				__( 'صفحه %d (فعلی)', 'escapezoom' ),
				$i
			)
		) . '">' . (int) $i . '</span>';
		continue;
	}
	echo '<a class="' . esc_attr( $link_classes ) . '" href="' . esc_url( $page_url ) . '" hx-get="' . esc_url( ez_brands_directory_hx_fetch_url( $i, $ez_brands_hx_nonce ) ) . '" hx-target="#brands-directory-swap" hx-swap="outerHTML"' . $ez_brands_hx_ind // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. ( '' !== $edge_label ? ' aria-label="' . esc_attr( $edge_label ) . '" title="' . esc_attr( $edge_label ) . '"' : '' )
			. '>' . (int) $i . '</a>';
}
$ez_brands_pg_slots = (string) ob_get_clean();
?>
<nav
	class="mt-10 flex w-full max-w-full flex-nowrap items-center justify-center gap-1.5 overflow-x-auto border-t border-slate-100 pt-8 [scrollbar-width:thin]"
	aria-label="<?php echo esc_attr__( 'صفحه‌بندی برندها', 'escapezoom' ); ?>">
	<?php echo $ez_brands_pg_prev; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<div class="flex min-w-0 shrink flex-nowrap items-center justify-center gap-1.5">
		<?php echo $ez_brands_pg_slots; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php echo $ez_brands_pg_next; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</nav>
