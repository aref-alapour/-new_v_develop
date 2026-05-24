<?php
/**
 * Template Name: Brands
 * آرشیو لیست برندها — WooCommerce product_brand + HTMX pagination
 */
defined( 'ABSPATH' ) || exit;

get_header();

$ctx = ez_brands_directory_get_list_context();
?>
	<section class="mb-12 mt-8 max-lg:mb-8">
		<nav class="flex" aria-label="Breadcrumb">
			<ol class="inline-flex items-center">
				<li class="group">
					<div class="flex items-center">
						<a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'صفحه اصلی', 'escapezoom' ); ?></a>
					</div>
				</li>
				<li class="group">
					<div class="flex items-center">
						<div class="mx-5 h-2 w-px bg-slate-110" role="presentation"></div>
						<span class="text-2xs font-medium text-slate-310"><?php esc_html_e( 'برندها', 'escapezoom' ); ?></span>
					</div>
				</li>
			</ol>
		</nav>
	</section>

	<section
		id="ez-brands-page-root"
		class="relative pb-16"
		x-data="ezBrandsPageState"
		x-bind:class="busy ? 'pointer-events-none' : ''"
		aria-busy="false"
		x-bind:aria-busy="busy ? 'true' : 'false'">

		<header class="mb-8 flex flex-col gap-3 border-b border-slate-100 pb-6 md:flex-row md:items-center md:justify-between">
			<div>
				<h1 class="text-2xl font-bold tracking-tight text-navyBlue md:text-3xl">
					<?php esc_html_e( 'برندها', 'escapezoom' ); ?>
				</h1>
			</div>
		</header>

		<?php if ( $ctx === null ) : ?>
			<div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
				<?php esc_html_e( 'تاکسونومی برند محصول (WooCommerce) فعال نیست.', 'escapezoom' ); ?>
			</div>
		<?php else : ?>
			<div class="relative">
				<?php echo ez_brands_directory_hx_skeleton_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php require Theme_PATH . 'template/parts/brands-directory-swap.php'; ?>
			</div>
		<?php endif; ?>
	</section>

<?php
get_footer();
