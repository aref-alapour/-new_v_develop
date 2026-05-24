<?php
/**
 * WooCommerce product_brand tweaks.
 */
defined( 'ABSPATH' ) || exit;

add_filter(
	'register_taxonomy_product_brand',
	static function ( array $args ): array {
		$args['hierarchical'] = false;

		if ( isset( $args['rewrite'] ) && is_array( $args['rewrite'] ) ) {
			$args['rewrite']['slug']         = 'brands';
			$args['rewrite']['with_front']  = false;
			$args['rewrite']['hierarchical'] = false;
		}

		if ( isset( $args['labels'] ) && is_array( $args['labels'] ) ) {
			$args['labels']['parent_item']       = '';
			$args['labels']['parent_item_colon'] = '';
		}

		return $args;
	},
	20
);

/**
 * حذف برچسب «بایگانی‌ها …» یا «Archives …» از ابتدای رشتهٔ عنوان آرشیوی برند.
 *
 * Yoast عنوان را ابتدا با esc_html می‌سازد؛ با decode پیش از regex، entityها با متن فارسی واقعی همخوان می‌مانند.
 */
function ez_brand_strip_archives_prefix_from_title( string $title ): string {
	if ( '' === trim( $title ) ) {
		return $title;
	}

	$work = wp_specialchars_decode( $title, ENT_QUOTES | ENT_HTML5 );

	$t = preg_replace(
		'#^\s*بایگانی(?:\x{200C})?ها\s*[:\-\x{2013}\x{2014}]+\s*#u',
		'',
		$work
	);
	if ( null === $t ) {
		return $title;
	}

	$t = preg_replace(
		'#^\s*Archives\s*[:\-\x{2013}\x{2014}]+\s*#iu',
		'',
		$t
	);
	if ( null === $t ) {
		return $title;
	}

	$t = trim( $t );

	return '' !== $t ? $t : trim( $work );
}

/**
 * آرشیو تکی برند (<title>): حذف پیشوند «بایگانی‌ها» وقتی وردپرس بدون Yoast قطعات عنوان را می‌سازد.
 *
 * Yoast کل رشته را زود با `pre_get_document_title` جایگزین می‌کند؛ آن مسیر با فیلتر جدا پاک می‌شود.
 */
add_filter(
	'document_title_parts',
	static function ( array $parts ): array {
		if (
			! is_tax(
				array(
					'product_brand',
					'yith_product_brand',
				)
			)
		) {
			return $parts;
		}

		if ( empty( $parts['title'] ) || ! is_string( $parts['title'] ) ) {
			return $parts;
		}

		$stripped = ez_brand_strip_archives_prefix_from_title( $parts['title'] );
		if ( '' !== trim( $stripped ) ) {
			$parts['title'] = $stripped;
		}

		return $parts;
	},
	50,
	1
);

/**
 * Yoast SEO با priority ~15 روی `pre_get_document_title` کل عنوان را ست می‌کند؛ این فیلتر بعد از آن اجرا می‌شود تا پیشوند «بایگانی‌ها» حذف گردد.
 */
add_filter(
	'pre_get_document_title',
	static function ( $title ) {
		if (
			! is_tax(
				array(
					'product_brand',
					'yith_product_brand',
				)
			)
		) {
			return $title;
		}
		if ( ! is_string( $title ) || '' === $title ) {
			return $title;
		}

		$stripped = ez_brand_strip_archives_prefix_from_title( $title );

		return '' !== trim( $stripped ) ? $stripped : $title;
	},
	999,
	1
);

/**
 * عنوان خروجی Yoast پیش از ادغام در template — همان پیش‌فعال برای اطمینان.
 */
add_filter(
	'wpseo_title',
	static function ( $title ) {
		if (
			! is_tax(
				array(
					'product_brand',
					'yith_product_brand',
				)
			)
		) {
			return $title;
		}
		if ( ! is_string( $title ) || '' === $title ) {
			return $title;
		}

		$stripped = ez_brand_strip_archives_prefix_from_title( $title );

		return '' !== trim( $stripped ) ? $stripped : $title;
	},
	999,
	1
);

/**
 * Read a query arg from $_GET, falling back to parsing REQUEST_URI query string.
 *
 * @return non-falsy-string|null
 */
function ez_brands_request_query_param( string $key ): ?string {
	if ( isset( $_GET[ $key ] ) ) {
		$v = wp_unslash( $_GET[ $key ] );
		if ( is_array( $v ) ) {
			$v = reset( $v );
		}
		return is_string( $v ) && $v !== '' ? $v : null;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$qs  = wp_parse_url( $uri, PHP_URL_QUERY );
	if ( ! is_string( $qs ) || '' === $qs ) {
		return null;
	}
	parse_str( $qs, $out );
	if ( ! isset( $out[ $key ] ) ) {
		return null;
	}
	$v = $out[ $key ];
	if ( is_array( $v ) ) {
		$v = reset( $v );
	}

	return is_string( $v ) && $v !== '' ? $v : null;
}

/**
 * Query args for the public brands directory (page + REST) — WooCommerce `product_brand`.
 *
 * @param 'popular'|'new' $order_mode popular: order by product count (stable; all brands). new: newest term_id.
 * @return array<string,mixed>
 */
function ez_brands_directory_terms_query_args( int $page, int $per_page, string $order_mode ): array {
	$page     = max( 1, $page );
	$per_page = max( 1, $per_page );

	$args = array(
		'taxonomy'                 => 'product_brand',
		'hide_empty'               => false,
		'number'                   => $per_page,
		'offset'                   => $per_page * ( $page - 1 ),
		'update_term_meta_cache'   => true,
	);

	if ( 'new' === $order_mode ) {
		$args['orderby'] = 'term_id';
		$args['order']   = 'DESC';
	} else {
		// Do not use meta_value_num brand_reputation here: terms without that meta are excluded entirely.
		$args['orderby'] = 'count';
		$args['order']   = 'DESC';
	}

	return $args;
}

/**
 * Brands list pagination index (`?page=2`, legacy `/brands/page/2/`, or `/page/` for other WP screens).
 */
function ez_brands_directory_current_page(): int {
	$pid = function_exists( 'ez_brands_directory_page_id' ) ? (int) ez_brands_directory_page_id() : 0;
	if (
		$pid > 0
		&& function_exists( 'ez_brands_request_matches_directory_route' )
		&& ez_brands_request_matches_directory_route( $pid )
	) {
		$page_q = ez_brands_request_query_param( 'page' );
		if ( null !== $page_q ) {
			return max( 1, (int) $page_q );
		}
		$uri       = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$canonical = get_permalink( $pid );
		if (
			is_string( $canonical ) && '' !== $canonical
			&& function_exists( 'ez_brands_compare_path' )
		) {
			$page_base_lc = ez_brands_compare_path( $canonical );
			$req_lc       = ez_brands_compare_path( $uri );
			$qb           = preg_quote( $page_base_lc, '#' );
			if ( preg_match( '#^' . $qb . '/page/(\\d+)$#u', $req_lc, $m ) ) {
				return max( 1, (int) $m[1] );
			}
		}
		return 1;
	}

	$p = (int) get_query_var( 'paged' );
	if ( $p > 0 ) {
		return $p;
	}
	$p = (int) get_query_var( 'page' );
	if ( $p > 0 ) {
		return $p;
	}
	if ( isset( $_SERVER['REQUEST_URI'] ) && preg_match( '#/page/(\\d+)/?#', (string) wp_unslash( $_SERVER['REQUEST_URI'] ), $m ) ) {
		return max( 1, (int) $m[1] );
	}
	return 1;
}

function ez_brands_directory_term_total_cache_group(): string {
	return 'ez_brands_directory';
}

function ez_brands_directory_flush_term_total_cache(): void {
	$g = ez_brands_directory_term_total_cache_group();
	foreach ( array( 'popular', 'new' ) as $mode ) {
		wp_cache_delete( 'terms_total_' . $mode, $g );
		delete_transient( 'ez_br_dir_total_v1_' . $mode );
	}
}

/**
 * Total terms matching the same ordering/filter as the directory list.
 *
 * Cached (object cache preferred) — cleared when product_brand terms change.
 */
function ez_brands_directory_count_terms( string $order_mode ): int {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return 0;
	}
	$allowed = array( 'popular', 'new' );
	if ( ! in_array( $order_mode, $allowed, true ) ) {
		$order_mode = 'popular';
	}

	$cache_group = ez_brands_directory_term_total_cache_group();
	$cache_key   = 'terms_total_' . $order_mode;
	$cached      = wp_cache_get( $cache_key, $cache_group );

	if ( false !== $cached ) {
		return (int) $cached;
	}

	$tkey     = 'ez_br_dir_total_v1_' . $order_mode;
	$t_cached = get_transient( $tkey );

	if ( false !== $t_cached ) {
		$n = (int) $t_cached;
		wp_cache_set( $cache_key, $n, $cache_group, MINUTE_IN_SECONDS * 15 );

		return $n;
	}

	$args       = ez_brands_directory_terms_query_args( 1, 1, $order_mode );
	$count_args = $args;
	unset( $count_args['offset'], $count_args['number'] );
	$n = wp_count_terms( $count_args );
	$n = is_wp_error( $n ) ? 0 : (int) $n;

	wp_cache_set( $cache_key, $n, $cache_group, MINUTE_IN_SECONDS * 15 );
	set_transient( $tkey, $n, HOUR_IN_SECONDS );

	return $n;
}

add_action(
	'created_term',
	static function ( $term_id, $tt_id, $taxonomy ): void {
		if ( 'product_brand' === $taxonomy ) {
			ez_brands_directory_flush_term_total_cache();
		}
	},
	10,
	3
);

add_action(
	'edited_term',
	static function ( $term_id, $tt_id, $taxonomy ): void {
		if ( 'product_brand' === $taxonomy ) {
			ez_brands_directory_flush_term_total_cache();
		}
	},
	10,
	3
);

add_action(
	'delete_term',
	static function ( $term_id, $tt_id, $taxonomy, $deleted_term, $object_ids ): void {
		if ( 'product_brand' === $taxonomy ) {
			ez_brands_directory_flush_term_total_cache();
		}
	},
	10,
	5
);

add_action(
	'set_object_terms',
	static function ( $object_id, $terms, $tt_ids, $taxonomy ): void {
		if ( 'product_brand' === $taxonomy ) {
			ez_brands_directory_flush_term_total_cache();
		}
	},
	10,
	4
);
