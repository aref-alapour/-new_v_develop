<?php
/**
 * Single brand archive (WooCommerce product_brand).
 */
defined( 'ABSPATH' ) || exit;

get_header();

$brand = get_queried_object();
$ez_brand_taxonomies = array( 'product_brand' );
if ( taxonomy_exists( 'yith_product_brand' ) ) {
	$ez_brand_taxonomies[] = 'yith_product_brand';
}
if ( ! $brand instanceof WP_Term || ! in_array( $brand->taxonomy, $ez_brand_taxonomies, true ) ) {
	get_footer();
	return;
}

$id       = (int) $brand->term_id;
$taxonomy = $brand->taxonomy;

$team = function_exists( 'get_field' ) ? get_field( 'team', "{$taxonomy}_{$id}" ) : null;
if ( ! is_array( $team ) || $team === [] ) {
	$team = [];
	if ( function_exists( 'ez_brand_team_members' ) ) {
		foreach ( ez_brand_team_members( $id ) as $row ) {
			$team[] = [
				'name'     => $row['name'],
				'position' => $row['position'],
				'avatar'   => ! empty( $row['image_id'] ) ? [ 'ID' => (int) $row['image_id'] ] : null,
			];
		}
	}
} else {
	$team = array_values( array_filter( (array) $team, 'is_array' ) );
}

$brand_type_games = function_exists( 'get_field' ) ? get_field( 'brand_type_games', "{$taxonomy}_{$id}" ) : null;
if ( ! is_array( $brand_type_games ) || $brand_type_games === [] ) {
	$brand_type_games = [];
	if ( function_exists( 'ez_brand_game_type_options' ) && function_exists( 'ez_brand_selected_game_types' ) ) {
		$opts  = ez_brand_game_type_options();
		$slugs = ez_brand_selected_game_types( $id, $opts );
		foreach ( $slugs as $slug ) {
			$brand_type_games[] = [
				'عنوان_تایپ' => isset( $opts[ $slug ] ) ? $opts[ $slug ] : $slug,
			];
		}
	}
}

$brands_location_add = function_exists( 'get_field' ) ? get_field( 'brands_location_add', "{$taxonomy}_{$id}" ) : '';
if ( $brands_location_add === '' || $brands_location_add === false || $brands_location_add === null ) {
	$brands_location_add = (string) get_term_meta( $id, EZ_BRAND_META_ADDRESS, true );
}

$image_ID = (int) get_term_meta( $id, 'thumbnail_id', true );
?>

<section class="my-12 max-lg:my-8">
	<nav class="flex" aria-label="Breadcrumb">
		<ol class="inline-flex items-center">
			<li class="group">
				<div class="flex items-center">
					<a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="<?php echo esc_url( home_url( '/' ) ); ?>">صفحه اصلی</a>
				</div>
			</li>
			<li class="group">
				<div class="flex items-center">
					<div class="mx-5 h-2 w-px bg-slate-110"></div>
					<a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="<?php echo esc_url( trailingslashit( home_url( 'brands' ) ) ); ?>">برندها</a>
				</div>
			</li>
			<li class="group">
				<div class="flex items-center">
					<div class="mx-5 h-2 w-px bg-slate-110"></div>
					<span class="text-2xs font-medium text-slate-310 cursor-text"><?php echo esc_html( $brand->name ); ?></span>
				</div>
			</li>
		</ol>
	</nav>
</section>

<section class="flex items-center justify-between">
	<div>
		<h1 class="font-black lg:mt-8 text-2xl lg:text-4xl"><?php echo esc_html( $brand->name ); ?></h1>
		<?php if ( $brand_type_games ) : ?>
			<div class="text-primaryColor text-base lg:text-lg font-bold my-4 lg:my-8">
				<?php
				$titles = [];
				foreach ( $brand_type_games as $game ) {
					if ( is_array( $game ) && isset( $game['عنوان_تایپ'] ) && $game['عنوان_تایپ'] !== '' ) {
						$titles[] = esc_html( (string) $game['عنوان_تایپ'] );
					}
				}
				echo $titles !== [] ? implode( '<span class="bg-muted-blue mx-2 w-d3 h-d3 inline-block rounded-full"></span>', $titles ) : '';
				?>
			</div>
		<?php endif; ?>
		<?php if ( $brands_location_add !== '' ) : ?>
			<div class="flex items-center gap-2">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none" aria-hidden="true">
					<path d="M9.445 17.18C11.6238 15.2625 15.5 11.345 15.5 7.75C15.5 5.76088 14.7098 3.85322 13.3033 2.4467C11.8968 1.04018 9.98912 0.25 8 0.25C6.01088 0.25 4.10322 1.04018 2.6967 2.4467C1.29018 3.85322 0.5 5.76088 0.5 7.75C0.5 11.345 4.375 15.2625 6.555 17.18C6.95264 17.5349 7.467 17.7311 8 17.7311C8.533 17.7311 9.04736 17.5349 9.445 17.18ZM5.5 7.75C5.5 7.08696 5.76339 6.45107 6.23223 5.98223C6.70107 5.51339 7.33696 5.25 8 5.25C8.66304 5.25 9.29893 5.51339 9.76777 5.98223C10.2366 6.45107 10.5 7.08696 10.5 7.75C10.5 8.41304 10.2366 9.04893 9.76777 9.51777C9.29893 9.98661 8.66304 10.25 8 10.25C7.33696 10.25 6.70107 9.98661 6.23223 9.51777C5.76339 9.04893 5.5 8.41304 5.5 7.75Z"
						fill="#90A1B9" />
				</svg>
				<span class="text-steel">
					<?php echo esc_html( $brands_location_add ); ?>
				</span>
			</div>
		<?php endif; ?>
	</div>
	<div>
		<?php
		if ( $image_ID > 0 ) {
			echo wp_get_attachment_image(
				$image_ID,
				'full',
				false,
				[
					'class' => 'max-lg:w-25 max-lg:h-25 lg:w-46 lg:h-46 shadow-101 rounded-2xl',
				]
			);
		}
		?>
	</div>
</section>
<div class="border-b my-10"></div>
<?php
if ( function_exists( 'ez_products_snapshot_swiper' ) ) {
	$posts_per_page = 100;
	$sort_type      = 'popular';
	$params         = [
		'brand_id' => $id,
	];
	$args           = [
		'params'        => $params,
		'image_type'    => 'url',
		'limit'         => $posts_per_page,
		'page'          => 1,
		'max_num_pages' => false,
		'format'        => 'html_swiper',
		'sort_type'     => $sort_type,
		'unpin_ads'     => false,
		'badge_ads'     => false,
		'random'        => false,
		'random_memory' => '',
		'show_more'     => 0,
	];
	$brand_products = ez_products_snapshot_swiper( $args );
	if ( ! empty( $brand_products->products ) ) :
		?>
	<section class="max-w-full py-4 md:py-5 lg:py-9 md:mt-7.5">
		<div class="mb-6 md:mb-8">
			<div class="flex justify-between">
				<div class="items-center gap-6 md:flex">
					<h2 class="flex items-center gap-4">
						<div class="hidden md:block">
							<div class="mb-1 rounded-xl bg-primaryColor text-white shadow-4 w-8 h-8 flex items-center justify-center">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="9" viewBox="0 0 16 9" fill="none" aria-hidden="true">
									<path d="M3.7998 0.777344C4.64134 0.670404 5.49292 0.87568 6.19531 1.35352C6.89777 1.83142 7.40287 2.54978 7.61523 3.375L7.76074 3.93848H8.3418L14.7266 3.93945C14.8079 3.93955 14.8862 3.97197 14.9443 4.03027C15.0026 4.08871 15.0361 4.16844 15.0361 4.25195L15.0352 7.24414C15.0337 7.32654 15.0003 7.40473 14.9424 7.46191C14.8843 7.5191 14.8061 7.55079 14.7256 7.55078C14.6452 7.55071 14.5677 7.51904 14.5098 7.46191C14.4662 7.41898 14.4366 7.36421 14.4238 7.30469L14.416 7.24414L14.417 5.31445V4.56445L13.667 4.56348H11.29V5.31348L11.2891 7.24316C11.2876 7.32575 11.2544 7.40466 11.1963 7.46191C11.1383 7.51904 11.0599 7.55079 10.9795 7.55078C10.8991 7.55068 10.8216 7.51806 10.7637 7.46094C10.7073 7.40525 10.674 7.32999 10.6709 7.25V4.56348H8.3418L7.75977 4.5625L7.61523 5.12598C7.40263 5.95122 6.89702 6.66975 6.19434 7.14746C5.49182 7.625 4.64027 7.82888 3.79883 7.72168C2.95725 7.61437 2.18244 7.20351 1.62109 6.56445C1.05969 5.92525 0.749864 5.10151 0.75 4.24902C0.750237 3.39677 1.06067 2.57444 1.62207 1.93555C2.18363 1.29658 2.95812 0.884397 3.7998 0.777344ZM4.23828 1.375C3.47668 1.37488 2.74598 1.67846 2.20801 2.21777C1.67032 2.75698 1.36829 3.48835 1.36816 4.25C1.36816 5.01165 1.67038 5.74292 2.20801 6.28223C2.74578 6.82152 3.47585 7.12488 4.2373 7.125C4.99883 7.12507 5.72867 6.82149 6.2666 6.28223C6.80445 5.74299 7.10632 5.01178 7.10645 4.25C7.10645 3.48835 6.8052 2.75708 6.26758 2.21777C5.72984 1.6784 4.99975 1.37519 4.23828 1.375Z"
										fill="#09192D" stroke="white" stroke-width="1.5" />
								</svg>
							</div>
						</div>
						<span class="text-base font-bold md:text-lg">
							<span><b>رزرو</b> بازی ها</span>
						</span>
					</h2>
				</div>
			</div>
		</div>
		<div class="relative overflow-hidden embla_normal horizontal dragFree">
			<div class="embla__viewport">
				<div id="brand-slider" class="embla__container first-child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-d200 child:box-content lg:min-h-d300 flex child:ml-7 md:child:ml-12  last-child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-d156 md:child:w-d200 child:py-2.5"><?php echo $brand_products->products; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
			<button class="embla__button embla__button--prev brand-btn absolute right-0 top-1/2 -translate-y-115 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
				<svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113" aria-hidden="true">
					<g clip-path="url(#arrow_aa)">
						<path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
						<path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
						<path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
					</g>
					<defs>
						<clipPath id="arrow_aa">
							<path fill="#fff" d="M0 0h30v113H0z"></path>
						</clipPath>
					</defs>
				</svg>
			</button>
			<button class="embla__button embla__button--next brand-btn absolute left-0 top-1/2 -translate-y-115 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
				<svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113" aria-hidden="true">
					<g clip-path="url(#arrow_aa)">
						<path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
						<path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
						<path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
					</g>
					<defs>
						<clipPath id="arrow_aa">
							<path fill="#fff" d="M0 0h30v113H0z"></path>
						</clipPath>
					</defs>
				</svg>
			</button>
		</div>
	</section>
		<?php
	endif;
}
?>
<div class="border-b my-10"></div>
<?php if ( $brand->description ) : ?>
	<div class="container">
		<h2 class="font-black text-3xl mb-8 max-lg:text-2xl">معرفی</h2>
		<div class="text-justify mt-8"><?php echo wp_kses_post( $brand->description ); ?></div>
	</div>
<?php endif; ?>
<?php if ( $team ) : ?>
	<section class="flex flex-col mt-10">
		<h2 class="font-black text-3xl mb-8 max-lg:text-2xl">اعضاء</h2>
		<div class="members flex gap-9 overflow-x-auto no-scrollbar">
			<?php foreach ( $team as $member ) : ?>
				<?php
				if ( ! is_array( $member ) ) {
					continue;
				}
				$avatar_id = 0;
				if ( ! empty( $member['avatar']['ID'] ) ) {
					$avatar_id = (int) $member['avatar']['ID'];
				} elseif ( ! empty( $member['image_id'] ) ) {
					$avatar_id = (int) $member['image_id'];
				}
				?>
				<div class="flex flex-col text-center shrink-0 max-lg:w-d124 w-d154">
					<?php
					if ( $avatar_id > 0 ) {
						echo wp_get_attachment_image(
							$avatar_id,
							'large',
							false,
							[
								'class' => 'aspect-square rounded-full object-cover mb-3',
							]
						);
					}
					?>
					<span class="text-xl"><?php echo esc_html( (string) ( $member['name'] ?? '' ) ); ?></span>
					<span class="text-gray-400"><?php echo esc_html( (string) ( $member['position'] ?? '' ) ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
