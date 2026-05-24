<?php
/**
 * Brands directory: pagination URLs use `brands?page=2`; HTMX fetches fragment via same path + ez_brands_hx (never wp-admin).
 *
 * Profiling (optional): define( 'EZ_BRANDS_DIRECTORY_PROFILE_HX', true ); in wp-config.php then run one HTMX request;
 * timings + query counts appear in PHP error_log as [EZ Brands HX] …
 * The TOTAL line includes wp_bootstrap_to_nonce_ok≈… (time from PHP request start through core+plugins until profiling starts).
 */
defined( 'ABSPATH' ) || exit;

/**
 * Whether HTMX profiling to error_log is enabled.
 */
function ez_brands_directory_profile_hx_enabled(): bool {
	return defined( 'EZ_BRANDS_DIRECTORY_PROFILE_HX' ) && EZ_BRANDS_DIRECTORY_PROFILE_HX;
}

/**
 * Start profile state for this request (nonce must already be validated).
 */
function ez_brands_directory_profile_hx_begin(): void {
	if ( ! ez_brands_directory_profile_hx_enabled() ) {
		return;
	}

	$bootstrap_ms = null;
	if ( isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ) {
		$bootstrap_ms = round( ( microtime( true ) - (float) $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000, 1 );
	}

	$GLOBALS['ez_brands_profile_hx_request'] = true;
	$GLOBALS['ez_brands_hx_profile_state']   = array(
		't0'            => microtime( true ),
		'q0'            => class_exists( \EscapeZoom\Core\Database\WordPressQueryCounter::class )
			? \EscapeZoom\Core\Database\WordPressQueryCounter::current()
			: 0,
		'm0'            => memory_get_usage( true ),
		'marks'         => array(),
		'bootstrap_ms'  => $bootstrap_ms,
	);

	if ( empty( $GLOBALS['ez_brands_hx_profile_shutdown_registered'] ) ) {
		$GLOBALS['ez_brands_hx_profile_shutdown_registered'] = true;
		register_shutdown_function( 'ez_brands_directory_profile_hx_log_shutdown' );
	}

	ez_brands_directory_profile_hx_mark( 'after_nonce' );
}

/**
 * Record a milestone (delta since previous mark + cumulative since begin; query count after step).
 */
function ez_brands_directory_profile_hx_mark( string $step ): void {
	if ( ! ez_brands_directory_profile_hx_enabled() ) {
		return;
	}
	if ( empty( $GLOBALS['ez_brands_hx_profile_state'] ) ) {
		return;
	}

	$GLOBALS['ez_brands_hx_profile_state']['marks'][] = array(
		'step' => $step,
		't'    => microtime( true ),
		'q'    => class_exists( \EscapeZoom\Core\Database\WordPressQueryCounter::class )
			? \EscapeZoom\Core\Database\WordPressQueryCounter::current()
			: 0,
	);
}

/** @return bool */
function ez_brands_directory_profile_hx_is_fragment(): bool {
	return ! empty( $GLOBALS['ez_brands_profile_hx_request'] );
}

/**
 * Shutdown: one line per mark + totals.
 */
function ez_brands_directory_profile_hx_log_shutdown(): void {
	if ( ! ez_brands_directory_profile_hx_enabled() ) {
		return;
	}
	unset( $GLOBALS['ez_brands_profile_hx_request'] );

	if ( empty( $GLOBALS['ez_brands_hx_profile_state'] ) ) {
		return;
	}

	$qend = class_exists( \EscapeZoom\Core\Database\WordPressQueryCounter::class )
		? \EscapeZoom\Core\Database\WordPressQueryCounter::current()
		: 0;
	$st     = $GLOBALS['ez_brands_hx_profile_state'];
	$t0     = $st['t0'];
	$q0     = $st['q0'];
	$m0     = $st['m0'];
	$marks  = $st['marks'];
	$prev_t = $t0;
	$prev_q = $q0;
	$lines  = array();

	foreach ( $marks as $m ) {
		$dt    = round( ( $m['t'] - $prev_t ) * 1000, 1 );
		$cum   = round( ( $m['t'] - $t0 ) * 1000, 1 );
		$dq    = $m['q'] - $prev_q;
		$lines[] = $m['step'] . ': +' . $dt . 'ms (cum ' . $cum . 'ms, +' . $dq . ' queries)';
		$prev_t = $m['t'];
		$prev_q = $m['q'];
	}

	$wall = round( ( microtime( true ) - $t0 ) * 1000, 1 );
	$q_end = $qend;
	$mem_mb = round( ( memory_get_usage( true ) - $m0 ) / 1048576, 2 );

	$summary = '[EZ Brands HX] ' . implode( ' | ', $lines );
	$totals  = 'TOTAL wall=' . $wall . 'ms | queries_total+' . ( $q_end - $q0 ) . ' | memΔ=' . $mem_mb . 'MiB';
	$totals .= ' | queries_before_timed_region=' . $q0;

	if ( isset( $st['bootstrap_ms'] ) && is_numeric( $st['bootstrap_ms'] ) ) {
		$totals .= ' | wp_bootstrap_to_nonce_ok≈' . $st['bootstrap_ms'] . 'ms (plugins+core before fragment timer)';
	}

	if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES && isset( $GLOBALS['wpdb']->queries ) && is_array( $GLOBALS['wpdb']->queries ) ) {
		$n = count( $GLOBALS['wpdb']->queries );
		$totals .= ' | SAVEQUERIES=' . $n;
	}

	error_log( $summary . ' || ' . $totals );

	unset( $GLOBALS['ez_brands_hx_profile_state'] );
}

/**
 * Find the published Brands directory page ID.
 *
 * Uses `_wp_page_template` meta when set, plus fallback: slug `brands` uses theme `page-brands.php`
 * via the template hierarchy without requiring the template to be selected in the editor.
 *
 * Optional: `define( 'EZ_BRANDS_DIRECTORY_PAGE_ID', 123 );` to pin an ID when discovery fails.
 *
 * @return int 0 when not found.
 */
function ez_brands_directory_page_id(): int {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}

	$memo = 0;

	if ( defined( 'EZ_BRANDS_DIRECTORY_PAGE_ID' ) ) {
		$forced = (int) EZ_BRANDS_DIRECTORY_PAGE_ID;
		if ( $forced > 0 ) {
			$post = get_post( $forced );
			if ( $post instanceof WP_Post && 'page' === $post->post_type && 'publish' === $post->post_status ) {
				return $memo = $forced;
			}
		}
	}

	$page_id = ez_brands_directory_discover_page_id();

	$page_id = (int) apply_filters( 'ez_brands_directory_resolved_page_id', $page_id );

	if ( $page_id > 0 ) {
		$post = get_post( $page_id );
		if ( $post instanceof WP_Post && 'page' === $post->post_type && 'publish' === $post->post_status ) {
			return $memo = $page_id;
		}
	}

	return $memo = 0;
}

/**
 * @return int
 */
function ez_brands_directory_discover_page_id(): int {
	$exact_templates = array(
		'page-brands.php',
		'templates/page-brands.php',
	);

	foreach ( array( false, true ) as $suppress ) {
		foreach ( $exact_templates as $tpl ) {
			$ids = get_posts(
				array(
					'post_type'              => 'page',
					'post_status'            => 'publish',
					'posts_per_page'         => 1,
					'orderby'                => 'ID',
					'order'                  => 'ASC',
					'suppress_filters'       => $suppress,
					'meta_key'               => '_wp_page_template',
					'meta_value'             => $tpl,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			if ( ! empty( $ids ) ) {
				return (int) $ids[0];
			}
		}

		$candidates = get_posts(
			array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'posts_per_page'         => 20,
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'suppress_filters'       => $suppress,
				'meta_query'             => array(
					array(
						'key'     => '_wp_page_template',
						'value'   => 'page-brands.php',
						'compare' => 'LIKE',
					),
				),
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);
		foreach ( $candidates as $cid ) {
			$raw = get_post_meta( (int) $cid, '_wp_page_template', true );
			if ( ! is_string( $raw ) || '' === $raw ) {
				continue;
			}
			$file = strtolower( basename( wp_normalize_path( str_replace( '\\', '/', $raw ) ) ) );
			if ( 'page-brands.php' === $file ) {
				return (int) $cid;
			}
		}
	}

	foreach ( ez_brands_directory_implicit_slug_paths() as $path ) {
		$page = get_page_by_path( $path, OBJECT, 'page' );
		if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
			continue;
		}
		if ( 'brands' !== strtolower( (string) $page->post_name ) ) {
			continue;
		}
		if ( '' === locate_template( array( 'page-brands.php' ), false ) ) {
			continue;
		}

		return (int) $page->ID;
	}

	return 0;
}

/**
 * Paths for {@see get_page_by_path()} when template meta is absent (implicit `page-{slug}.php`).
 *
 * @return list<string>
 */
function ez_brands_directory_implicit_slug_paths(): array {
	$paths = array( 'brands' );

	$filtered = apply_filters( 'ez_brands_directory_implicit_page_paths', $paths );

	if ( is_array( $filtered ) ) {
		$list = array();
		foreach ( $filtered as $p ) {
			$p = is_string( $p ) ? trim( $p ) : '';
			if ( '' === $p ) {
				continue;
			}
			$list[] = $p;
		}
		$list = array_values( array_unique( $list ) );
		if ( ! empty( $list ) ) {
			return $list;
		}
	}

	return $paths;
}

/**
 * Canonical URL path for comparisons (decoded, untrailed slash).
 *
 * @param string $relative_uri Path or URI (may include scheme or query).
 */
function ez_brands_request_path_normalized( string $relative_uri ): string {
	$frag_after_q = strtok( $relative_uri, '?' );
	$path_candidate = false !== $frag_after_q ? $frag_after_q : $relative_uri;
	$path           = wp_parse_url( $path_candidate, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path ) {
		$path = $path_candidate;
		if ( '' !== $path && '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}
	}
	return untrailingslashit( rawurldecode( $path ) );
}

/**
 * Path ends with canonical directory path (case-normalized ASCII); allows /page/N legacy.
 */
function ez_brands_paths_same_directory( string $path_a, string $path_b ): bool {
	$path_a = untrailingslashit( strtolower( $path_a ) );
	$path_b = untrailingslashit( strtolower( $path_b ) );
	return $path_a === $path_b;
}

/**
 * Normalize path segment for comparisons: lowercase, trailing slash trimmed, `/index.php` stripped (plain permalinks).
 */
function ez_brands_compare_path( string $path_or_uri ): string {
	$p = strtolower( ez_brands_request_path_normalized( $path_or_uri ) );
	// Plain permalinks: /index.php/brands or /index.php
	$p = preg_replace( '#^/+index\\.php(?=/|$)#i', '/', $p, 1 );
	$p = preg_replace( '#/{2,}#', '/', $p );
	if ( '' === $p || '/' === $p ) {
		return '/';
	}

	return untrailingslashit( $p );
}

/**
 * Whether REQUEST_URI path is this brands directory (case-insensitive) or `/brands/page/N/` legacy shape.
 *
 * Fragment flag `ez_brands_hx=1` is ignored for path comparison (only path must match).
 */
function ez_brands_request_matches_directory_route( int $page_id ): bool {
	if ( $page_id < 1 ) {
		return false;
	}

	/*
	 * After the main query is parsed, prefer query identity over raw REQUEST_URI. Proxies/CDNs sometimes
	 * rewrite paths so get_permalink() and $_SERVER['REQUEST_URI'] diverge while the main query matches.
	 */
	if ( did_action( 'wp' ) ) {
		if ( function_exists( 'is_page' ) && is_page( $page_id ) ) {
			return true;
		}
		if ( (int) get_query_var( 'page_id' ) === $page_id ) {
			return true;
		}
		$pagename_q = strtolower( trim( (string) get_query_var( 'pagename' ), '/' ) );
		if ( '' !== $pagename_q ) {
			$page_uri = strtolower( trim( (string) get_page_uri( $page_id ), '/' ) );
			if ( '' !== $page_uri && $pagename_q === $page_uri ) {
				return true;
			}
		}
	}

	$canonical = get_permalink( $page_id );
	if ( ! is_string( $canonical ) || $canonical === '' ) {
		return false;
	}

	$page_base = ez_brands_compare_path( $canonical );
	$uri       = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$req_path  = ez_brands_compare_path( $uri );

	if ( ez_brands_paths_same_directory( $req_path, $page_base ) ) {
		return true;
	}

	$qb = preg_quote( $page_base, '#' );

	return (bool) preg_match( '#^' . $qb . '/page/\d+$#u', $req_path );
}

/**
 * List index for fragment requests (`?page=`, legacy `/directory/page/N/` path).
 */
function ez_brands_fragment_request_list_page( int $directory_page_id ): int {
	$page_q = ez_brands_request_query_param( 'page' );
	if ( null !== $page_q ) {
		return max( 1, (int) $page_q );
	}

	$canonical = get_permalink( $directory_page_id );
	if ( ! is_string( $canonical ) || '' === $canonical ) {
		return 1;
	}
	$page_base = ez_brands_compare_path( $canonical );
	$uri       = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$req_path  = ez_brands_compare_path( $uri );

	$qb = preg_quote( $page_base, '#' );
	if ( preg_match( '#^' . $qb . '/page/(\d+)$#u', $req_path, $m ) ) {
		return max( 1, (int) $m[1] );
	}

	return 1;
}

/**
 * @deprecated Use {@see ez_brands_request_matches_directory_route()}.
 */
function ez_brands_request_matches_directory_page( int $page_id ): bool {
	return ez_brands_request_matches_directory_route( $page_id );
}

/**
 * Canonical public URL for list state: `/brands` (first page), `/brands?page=2`, … Query key `page` only when > 1.
 */
function ez_brands_directory_build_page_url( int $page_num ): string {
	$page_num = max( 1, $page_num );
	$pid      = ez_brands_directory_page_id();
	if ( $pid < 1 ) {
		return (string) get_pagenum_link( $page_num, false );
	}
	$base = get_permalink( $pid );
	if ( ! is_string( $base ) || $base === '' ) {
		return (string) get_pagenum_link( $page_num, false );
	}

	// Strip conflicting query args so links stay repeatable.
	$base = remove_query_arg( array( 'page', 'ez_brands_hx', '_wpnonce' ), $base );

	if ( $page_num <= 1 ) {
		$bare = explode( '#', esc_url_raw( $base ), 2 )[0];

		return user_trailingslashit( untrailingslashit( $bare ) );
	}

	return esc_url_raw( add_query_arg( 'page', $page_num, $base ) );
}

/**
 * HTMX loading overlay: skeleton grid aligned with the brands grid (no full-screen spinner).
 */
function ez_brands_directory_hx_skeleton_html(): string {
	ob_start();
	require Theme_PATH . 'template/parts/brands/brands-hx-skeleton.php';
	return (string) ob_get_clean();
}

/**
 * Whether the current singular page uses page-brands.php.
 */
function ez_is_brands_directory_page(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$slug = get_page_template_slug( (int) get_queried_object_id() );
	return $slug === 'page-brands.php';
}

/**
 * Enqueue helpers: same path as HTMX fragment matcher (main query can miss page template with `brands` rewrite).
 */
function ez_should_enqueue_brands_directory_scripts(): bool {
	$page_id = ez_brands_directory_page_id();
	if ( $page_id < 1 ) {
		return false;
	}
	if ( is_page_template( 'page-brands.php' ) ) {
		return true;
	}
	return ez_brands_request_matches_directory_page( $page_id );
}

/**
 * Bulk-prime caches before rendering directory cards — avoids per-term/per-attachment queries (N+1).
 *
 * @param WP_Term[] $terms
 */
function ez_brands_directory_prime_cards_for_terms( array $terms ): void {
	if ( empty( $terms ) ) {
		return;
	}

	$term_ids = array();
	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term ) {
			$tid = (int) $term->term_id;
			if ( $tid > 0 ) {
				$term_ids[] = $tid;
			}
		}
	}
	$term_ids = array_values( array_unique( $term_ids ) );
	if ( empty( $term_ids ) ) {
		return;
	}

	update_termmeta_cache( $term_ids );

	$attach_ids = array();
	foreach ( $term_ids as $tid ) {
		$thumb = (int) get_term_meta( $tid, 'thumbnail_id', true );
		if ( $thumb > 0 ) {
			$attach_ids[] = $thumb;
		}
	}
	$attach_ids = array_values( array_unique( array_filter( $attach_ids ) ) );
	if ( empty( $attach_ids ) ) {
		return;
	}

	// Post rows + attachment meta (`_wp_attachment_metadata`, …) for `wp_get_attachment_image_url( …, 'large' )`.
	if ( function_exists( '_prime_post_caches' ) ) {
		_prime_post_caches( $attach_ids, false, true );
	}
}

/**
 * @param int|null $page_override If set (e.g. HTMX fragment), skip main-query page detection.
 *
 * @return array<string,mixed>|null
 */
function ez_brands_directory_get_list_context( ?int $page_override = null ): ?array {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return null;
	}
	$terms_per_page = 24;
	$order_mode     = 'popular';
	$page_num       = null !== $page_override
		? max( 1, $page_override )
		: ez_brands_directory_current_page();

	$query_args = ez_brands_directory_terms_query_args( $page_num, $terms_per_page, $order_mode );
	if ( ez_brands_directory_profile_hx_is_fragment() ) {
		ez_brands_directory_profile_hx_mark( 'ctx_before_get_terms' );
	}
	$brands     = get_terms( $query_args );

	if ( ez_brands_directory_profile_hx_is_fragment() ) {
		ez_brands_directory_profile_hx_mark( 'ctx_after_get_terms' );
	}

	if ( is_wp_error( $brands ) ) {
		$brands = array();
	}

	if ( ! empty( $brands ) ) {
		if ( ez_brands_directory_profile_hx_is_fragment() ) {
			ez_brands_directory_profile_hx_mark( 'ctx_before_prime_cards' );
		}
		ez_brands_directory_prime_cards_for_terms( $brands );
		if ( ez_brands_directory_profile_hx_is_fragment() ) {
			ez_brands_directory_profile_hx_mark( 'ctx_after_prime_cards' );
		}
	}

	if ( ez_brands_directory_profile_hx_is_fragment() ) {
		ez_brands_directory_profile_hx_mark( 'ctx_before_count_terms' );
	}
	$total_terms = ez_brands_directory_count_terms( $order_mode );
	if ( ez_brands_directory_profile_hx_is_fragment() ) {
		ez_brands_directory_profile_hx_mark( 'ctx_after_count_terms' );
	}
	$total_pages = $terms_per_page > 0 ? (int) ceil( $total_terms / $terms_per_page ) : 1;

	$pid = ez_brands_directory_page_id();
	if ( ez_brands_directory_profile_hx_is_fragment() ) {
		ez_brands_directory_profile_hx_mark( 'ctx_after_second_page_id_lookup' );
	}
	if ( $pid > 0 ) {
		$permalink = get_permalink( $pid );
		if ( ! is_string( $permalink ) || $permalink === '' ) {
			$permalink = trailingslashit( home_url( 'brands' ) );
		}
	} else {
		$permalink = get_permalink();
		if ( ! is_string( $permalink ) || $permalink === '' ) {
			$permalink = trailingslashit( home_url( 'brands' ) );
		}
	}

	return array(
		'terms_per_page' => $terms_per_page,
		'page_num'       => max( 1, $page_num ),
		'brands'         => $brands,
		'total_pages'    => max( 1, $total_pages ),
		'permalink'      => $permalink,
	);
}

/**
 * Whether pagination `hx-get` should target the signed `/ajax` gateway (`brands.fragment`).
 *
 * Transport is always `POST /ajax` with HMAC + nonce + timestamp when enabled; that is
 * **application-level** authenticity (only our front-end can forge valid requests for a short
 * window), not “HTTPS vs HTTP”. PHP does not branch on TLS here.
 *
 * @return bool
 */
function ez_brands_directory_htmx_should_use_ajax_gateway(): bool {
	$want = defined( 'EZ_BRANDS_USE_GATEWAY' ) && EZ_BRANDS_USE_GATEWAY;

	/**
	 * Override brands HTMX gateway usage (e.g. A/B or emergency fallback to nonce fragments).
	 *
	 * @param bool $want Value of `EZ_BRANDS_USE_GATEWAY`.
	 */
	return (bool) apply_filters( 'ez_brands_directory_use_ajax_gateway_for_htmx', $want );
}

/**
 * HTMX GET URL — signed gateway (`/ajax?action=brands.fragment&page=…`) when enabled, else
 * nonce fragment on the public brands path for environments that disable the gateway flag.
 */
function ez_brands_directory_hx_fetch_url( int $page_num, string $nonce ): string {
	$page_num = max( 1, $page_num );

	if ( ez_brands_directory_htmx_should_use_ajax_gateway() ) {
		return esc_url(
			add_query_arg(
				array(
					'action' => 'brands.fragment',
					'page'   => $page_num,
				),
				home_url( '/ajax' )
			)
		);
	}

	return esc_url(
		add_query_arg(
			array(
				'ez_brands_hx' => '1',
				'_wpnonce'    => $nonce,
			),
			ez_brands_directory_build_page_url( $page_num )
		)
	);
}

/**
 * Page numbers to show (with null = ellipsis).
 *
 * @return array<int|null>
 */
function ez_brands_directory_pagination_numbers( int $current, int $total ): array {
	$total   = max( 1, $total );
	$current = max( 1, min( $total, $current ) );

	if ( $total <= 7 ) {
		return range( 1, $total );
	}

	// Compact: first, last, current, and one page before/after only (plus ellipses).
	$include = array( 1, $total, $current );
	foreach ( array( $current - 1, $current + 1 ) as $neighbor ) {
		if ( $neighbor >= 2 && $neighbor <= $total - 1 ) {
			$include[] = $neighbor;
		}
	}

	$include = array_values(
		array_unique(
			array_filter(
				$include,
				static function ( $n ) use ( $total ) {
					return (int) $n >= 1 && (int) $n <= $total;
				}
			)
		)
	);
	sort( $include, SORT_NUMERIC );

	$out  = array();
	$prev = 0;
	foreach ( $include as $p ) {
		if ( $prev && $p - $prev > 1 ) {
			$out[] = null;
		}
		$out[] = $p;
		$prev  = $p;
	}

	return $out;
}

/**
 * Markup for prev/next and numbered links (HTMX + progressive enhancement).
 */
function ez_brands_directory_pagination_html( int $current_page, int $total_pages ): string {
	if ( $total_pages <= 1 ) {
		return '';
	}

	$ez_brands_current_page       = $current_page;
	$ez_brands_total_pages       = $total_pages;
	$ez_brands_hx_ind             = ' hx-indicator="#ez-brands-htmx-skeleton"';
	$ez_brands_hx_nonce         = wp_create_nonce( 'ez_brands_hx' );
	$ez_brands_resolve_page_url   = static function ( int $page_no ): string {
		return ez_brands_directory_build_page_url( $page_no );
	};

	ob_start();
	require Theme_PATH . 'template/parts/brands/brands-pagination-nav.php';
	return (string) ob_get_clean();
}

/**
 * Force the brands directory Page main query when its public path matches the request.
 *
 * `product_brand` uses rewrite slug `brands` (see ProductBrandTaxonomyTweaks); WordPress often prefers
 * taxonomy rules over the Page at the same path, so `/brands/` never becomes `is_page()` and the theme
 * falls through to the wrong template (empty taxonomy shell or redirect quirks).
 *
 * @param array<string,mixed> $query_vars Parsed query variables.
 * @return array<string,mixed>
 */
function ez_brands_directory_filter_request( array $query_vars ): array {
	if ( is_admin() ) {
		return $query_vars;
	}
	if ( wp_doing_ajax() ) {
		return $query_vars;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return $query_vars;
	}
	if ( ! empty( $query_vars['preview'] ) ) {
		return $query_vars;
	}

	$pid = ez_brands_directory_page_id();
	if ( $pid < 1 ) {
		return $query_vars;
	}

	$permalink = get_permalink( $pid );
	if ( ! is_string( $permalink ) || '' === $permalink ) {
		return $query_vars;
	}

	$base_path = ez_brands_compare_path( $permalink );
	if ( '/' === $base_path ) {
		return $query_vars;
	}

	$uri      = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$req_path = ez_brands_compare_path( $uri );

	if ( $req_path !== $base_path && ! preg_match( '#^' . preg_quote( $base_path, '#' ) . '/page/\d+$#u', $req_path ) ) {
		return $query_vars;
	}

	return ez_brands_directory_force_page_query_vars( $pid, $query_vars );
}

/**
 * Replace stealing vars (taxonomy / stray singles) with a singular Page query.
 *
 * @param array<string,mixed> $query_vars Parsed query variables.
 * @return array<string,mixed>
 */
function ez_brands_directory_force_page_query_vars( int $pid, array $query_vars ): array {
	$page = get_post( $pid );
	if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
		return $query_vars;
	}

	$strip = array(
		'error',
		'attachment',
		'attachment_id',
		'category_name',
		'name',
		'pagename',
		'page_id',
		'product_brand',
		'taxonomy',
		'term',
		'term_taxonomy_id',
		'post_type',
	);

	foreach ( $strip as $key ) {
		unset( $query_vars[ $key ] );
	}

	$query_vars['page_id'] = $pid;

	return $query_vars;
}

add_filter( 'request', 'ez_brands_directory_filter_request', 20 );

/**
 * HTMX fragment — only `#brands-directory-swap`; exits before theme template (never `admin-ajax.php`).
 */
function ez_brands_directory_serve_hx_fragment(): void {
	if ( is_admin() ) {
		return;
	}

	if ( ez_brands_request_query_param( 'ez_brands_hx' ) !== '1' ) {
		return;
	}

	$nonce = ez_brands_request_query_param( '_wpnonce' );
	if ( null === $nonce || ! wp_verify_nonce( $nonce, 'ez_brands_hx' ) ) {
		status_header( 403 );
		exit;
	}

	ez_brands_directory_profile_hx_begin();

	$brands_page_id = ez_brands_directory_page_id();
	ez_brands_directory_profile_hx_mark( 'after_page_id_resolve' );

	if ( ! taxonomy_exists( 'product_brand' ) ) {
		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		status_header( 200 );
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo '<div id="brands-directory-swap" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">';
		echo esc_html__( 'تاکسونومی برند محصول (WooCommerce) فعال نیست.', 'escapezoom' );
		echo '</div>';
		exit;
	}

	if ( ! $brands_page_id ) {
		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		status_header( 200 );
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo '<div id="brands-directory-swap" class="relative rounded-2xl border border-red-200 bg-red-50 p-6 text-red-900">';
		echo esc_html__( 'صفحهٔ «برندها» با قالب Brands در سایت یافت نشد. در پیشخوان یک صفحه بسازید و قالب «Brands» را به آن نسبت دهید.', 'escapezoom' );
		echo '</div>';
		exit;
	}

	if ( ! ez_brands_request_matches_directory_route( $brands_page_id ) ) {
		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		status_header( 200 );
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo '<div id="brands-directory-swap" class="relative rounded-2xl border border-red-200 bg-red-50 p-6 text-red-900">';
		echo esc_html__( 'آدرس درخواست با صفحهٔ لیست برندها هم‌خوان نیست. صفحه را با همان نشانی عمومی باز کنید یا یک‌بار بارگذاری مجدد انجام دهید.', 'escapezoom' );
		echo '</div>';
		exit;
	}

	ez_brands_directory_profile_hx_mark( 'route_and_guards_ok' );

	$list_page = ez_brands_fragment_request_list_page( $brands_page_id );

	$ctx = ez_brands_directory_get_list_context( $list_page );

	ez_brands_directory_profile_hx_mark( 'after_get_list_context' );

	nocache_headers();
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'Vary: HX-Request, Accept' );

	// Let HTMX adopt the canonical public URL reliably (fixes wrong history URL when xhr / push-url disagree).
	header( 'HX-Push-Url: ' . esc_url_raw( ez_brands_directory_build_page_url( $list_page ) ) );

	if ( $ctx === null ) {
		echo '<div id="brands-directory-swap" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">';
		echo esc_html__( 'تاکسونومی برند محصول (WooCommerce) فعال نیست.', 'escapezoom' );
		echo '</div>';
		exit;
	}

	ez_brands_directory_profile_hx_mark( 'before_brands_swap_template' );

	require Theme_PATH . 'template/parts/brands-directory-swap.php';
	ez_brands_directory_profile_hx_mark( 'after_brands_swap_template' );
	exit;
}

// Run before most `template_redirect` handlers (canonical, security) and skip slow theme work via early exit.
add_action( 'wp', 'ez_brands_directory_serve_hx_fragment', -1000 );
add_action( 'template_redirect', 'ez_brands_directory_serve_hx_fragment', -1000 );
