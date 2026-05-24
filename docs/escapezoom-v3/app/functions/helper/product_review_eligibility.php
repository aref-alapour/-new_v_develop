<?php
/**
 * Eligibility, timing, and shared helpers for product (WooCommerce) reviews.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR' ) ) {
	define( 'EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR', 10 );
}

if ( ! defined( 'EZ_SINGLE_PRODUCT_COMMENTS_PER_PAGE' ) ) {
	define( 'EZ_SINGLE_PRODUCT_COMMENTS_PER_PAGE', 10 );
}

/**
 * Stored `user_level` on product reviews: 10 = «مجموعه‌دار» at submit time; rating power same as level 3 (7).
 *
 * @param int $stored Value from comment_meta `user_level`.
 */
function ez_comment_stored_user_level_to_rating_power( int $stored ): int {
	$map = ez_product_review_power_map();
	return $map[ (int) $stored ] ?? 1;
}

/**
 * What to save as comment_meta `user_level` for a new/edited review.
 */
function ez_comment_stored_user_level_for_new_review( int $user_id ): int {
	if ( $user_id <= 0 ) {
		return 1;
	}
	if ( function_exists( 'ez_user_should_show_mojavezedar_badge' ) && ez_user_should_show_mojavezedar_badge( $user_id ) ) {
		return (int) EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR;
	}
	if ( function_exists( 'ez_user_effective_feature_level' ) ) {
		return (int) ez_user_effective_feature_level( $user_id );
	}
	return (int) get_user_level( $user_id );
}

/**
 * For sorting «حرفه‌ای» lists: 10 behaves like 3 for order (below 4).
 */
function ez_comment_pro_sort_key_from_stored_level( int $stored ): int {
	if ( (int) $stored === (int) EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR ) {
		return 3;
	}
	return (int) $stored;
}

/**
 * @return int[]
 */
function ez_get_product_review_rate_keys(): array {
	return [ 1098, 1097, 1096, 1095, 1094 ];
}

/**
 * Normalize rate array from POST (string/int keys).
 *
 * @param array|string|null $rate Raw rate from request.
 * @return int[] keyed by 1098..1094
 */
function ez_normalize_review_rate_array( $rate ): array {
	if ( ! is_array( $rate ) ) {
		$rate = [];
	}
	$out = [];
	foreach ( ez_get_product_review_rate_keys() as $key ) {
		$v     = $rate[ $key ] ?? $rate[ (string) $key ] ?? 0;
		$out[ $key ] = (int) $v;
	}
	return $out;
}

/**
 * True if raw POST rate omitted key 1097 (legacy average scaling).
 *
 * @param array|string|null $rate Raw rate from request.
 */
function ez_review_rate_missing_1097( $rate ): bool {
	if ( ! is_array( $rate ) ) {
		return true;
	}
	return ! array_key_exists( 1097, $rate ) && ! array_key_exists( '1097', $rate );
}

/**
 * POST preview average on scale ~0–5 from raw scores (20–100 style integers).
 *
 * When legacy forms omit axis 1097, that axis is excluded from the divisor so the preview is not artificially low.
 *
 * @param int[] $rate Normalized keys 1098..1094
 */
function ez_compute_review_rate_average( array $rate, bool $missing_1097 = false ): float {
	$keys = ez_get_product_review_rate_keys();
	$sum  = 0;
	$n    = 0;
	foreach ( $keys as $k ) {
		if ( $missing_1097 && (int) $k === 1097 ) {
			continue;
		}
		$sum += (int) ( $rate[ $k ] ?? 0 );
		++$n;
	}
	if ( $n < 1 ) {
		return 0.0;
	}

	return (float) ( ( $sum / $n ) / 20 );
}

/**
 * Map legacy comment_rating meta keys to criterion IDs for rollup deltas.
 *
 * @param array<int|string,int|float|string> $rating_legacy
 * @return array<int,int> criterion_id => score_raw
 */
function ez_product_rating_legacy_rating_to_crit_scores( array $rating_legacy ): array {
	if ( ! Ez_Product_Ratings_Schema::is_installed() ) {
		return [];
	}

	$slug_map = Ez_Product_Ratings_Schema::legacy_term_key_to_slug_map();
	$out      = [];

	foreach ( ez_get_product_review_rate_keys() as $legacy_key ) {
		$raw = (int) ( $rating_legacy[ $legacy_key ] ?? $rating_legacy[ (string) $legacy_key ] ?? 0 );
		if ( $raw <= 0 ) {
			continue;
		}
		$slug = $slug_map[ $legacy_key ] ?? '';
		if ( $slug === '' ) {
			continue;
		}
		$crit_id = Ez_Product_Rating_Criteria::id_for_slug( $slug );
		if ( $crit_id > 0 ) {
			$out[ $crit_id ] = $raw;
		}
	}

	return $out;
}

/**
 * نام دستهٔ والد product_cat (مثلاً «اتاق فرار») — منطق هم‌راستا با single-product.php.
 */
function ez_get_product_review_parent_type_name( int $product_id ): string {
	if ( $product_id < 1 ) {
		return 'نامشخص';
	}
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return 'نامشخص';
	}
	$n = count( $terms );
	if ( $n > 1 ) {
		foreach ( $terms as $term ) {
			if ( (int) $term->parent === 0 ) {
				return (string) $term->name;
			}
		}
	}
	if ( $n === 1 ) {
		$first = reset( $terms );
		if ( ! $first ) {
			return 'نامشخص';
		}
		$parent_id = (int) $first->parent;
		if ( $parent_id > 0 ) {
			$p = get_term( $parent_id, 'product_cat' );
			if ( $p && ! is_wp_error( $p ) ) {
				return (string) $p->name;
			}
		}
		return (string) $first->name;
	}
	return 'نامشخص';
}

/**
 * خطوط نمایش امتیازها از متا comment_rating (مقیاس ۲۰–۱۰۰).
 *
 * @param mixed $comment_rating_meta
 * @return string[]
 */
function ez_format_review_rates_display_lines( $comment_rating_meta, string $product_type_name ): array {
	$labels_escape = array(
		1094 => 'فضاسازی',
		1098 => 'تازگی و خلاقیت',
		1095 => 'کیفیت معما',
		1096 => 'بازیگردانی و اکت',
		1097 => 'برخورد پرسنل',
	);
	$lines         = array();
	if ( $product_type_name === 'اتاق فرار' ) {
		$order = array( 1094, 1098, 1095, 1096, 1097 );
		foreach ( $order as $key ) {
			$raw = null;
			if ( is_array( $comment_rating_meta ) ) {
				$raw = $comment_rating_meta[ $key ] ?? $comment_rating_meta[ (string) $key ] ?? null;
			}
			$label = $labels_escape[ $key ];
			if ( $raw === null || $raw === '' ) {
				$lines[] = $label . ': —';
				continue;
			}
			$n = (int) round( (float) $raw / 20 );
			$n = max( 1, min( 5, $n ) );
			$lines[] = $label . ': ' . $n;
		}
	} else {
		$raw = null;
		if ( is_array( $comment_rating_meta ) ) {
			$raw = $comment_rating_meta[1098] ?? $comment_rating_meta['1098'] ?? null;
		}
		if ( $raw === null || $raw === '' ) {
			$lines[] = 'امتیاز: —';
		} else {
			$n       = (int) round( (float) $raw / 20 );
			$n       = max( 1, min( 5, $n ) );
			$lines[] = 'امتیاز: ' . $n;
		}
	}
	return $lines;
}

/**
 * مقدار ذخیره‌شده متا را به data-rate دکمه (۲۰، ۴۰، …، ۱۰۰) نگاشت می‌کند.
 *
 * @param mixed $raw
 */
function ez_review_meta_raw_to_data_rate( $raw ): int {
	if ( $raw === null || $raw === '' ) {
		return 100;
	}
	$n = (int) round( (float) $raw / 20 );
	$n = max( 1, min( 5, $n ) );
	return $n * 20;
}

/**
 * خلاصه متن کامنت برای لیست «کامنت‌های من».
 */
function ez_my_reviews_excerpt( string $text, int $max_chars = 100 ): string {
	if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
		if ( mb_strlen( $text, 'UTF-8' ) <= $max_chars ) {
			return $text;
		}
		return mb_substr( $text, 0, $max_chars, 'UTF-8' ) . '…';
	}
	if ( strlen( $text ) <= $max_chars ) {
		return $text;
	}
	return substr( $text, 0, $max_chars ) . '…';
}

/**
 * @return array<int,int> preset data-rate per key 1094..1098
 */
function ez_my_reviews_build_preset_rates_from_meta( $comment_rating_meta ): array {
	$out = array();
	foreach ( ez_get_product_review_rate_keys() as $key ) {
		$raw     = null;
		if ( is_array( $comment_rating_meta ) ) {
			$raw = $comment_rating_meta[ $key ] ?? $comment_rating_meta[ (string) $key ] ?? null;
		}
		$out[ $key ] = ez_review_meta_raw_to_data_rate( $raw );
	}
	return $out;
}

/**
 * Absolute path to bad words JSON dictionary.
 */
function ez_product_review_bad_words_file_path(): string {
	return trailingslashit( get_template_directory() ) . 'app/config/bad-words.json';
}

/**
 * Split unicode string into characters.
 *
 * @return string[]
 */
function ez_product_review_mb_chars( string $text ): array {
	if ( $text === '' ) {
		return array();
	}
	if ( function_exists( 'mb_str_split' ) ) {
		return mb_str_split( $text, 1, 'UTF-8' );
	}
	$parts = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );
	return is_array( $parts ) ? $parts : array();
}

/**
 * Normalize text for moderation checks.
 */
function ez_normalize_persian_text( string $text ): string {
	if ( $text === '' ) {
		return '';
	}

	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = mb_strtolower( $text, 'UTF-8' );

	$replacements = array(
		'ي' => 'ی',
		'ك' => 'ک',
		'ئ' => 'ی',
		'ؤ' => 'و',
		'أ' => 'ا',
		'إ' => 'ا',
		'ة' => 'ه',
		'ۀ' => 'ه',
		'ـ' => '',
		'ﻻ' => 'لا',
		'ﻷ' => 'لا',
		'ﻹ' => 'لا',
		'ﻵ' => 'لا',
		"\xE2\x80\x8C" => ' ',
		"\xE2\x80\x8D" => '',
		"\xE2\x80\x8E" => '',
		"\xE2\x80\x8F" => '',
		"\xEF\xBB\xBF" => '',
	);
	$text = strtr( $text, $replacements );

	$text = (string) preg_replace( '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $text );
	$text = (string) preg_replace( '/([[:punct:]\x{0600}-\x{06FF}])\1{2,}/u', '$1$1', $text );
	$text = (string) preg_replace( '/(.)\1{3,}/u', '$1$1', $text );
	$text = (string) preg_replace( '/\s+/u', ' ', $text );

	return trim( $text );
}

/**
 * Build loose regex pattern for an exact bad word token.
 */
function ez_build_bad_word_pattern( string $word ): string {
	$chars = ez_product_review_mb_chars( $word );
	if ( empty( $chars ) ) {
		return '//u';
	}
	$parts = array();
	foreach ( $chars as $char ) {
		$parts[] = preg_quote( $char, '/' );
	}
	$between = '[\s\.\-_،,:;!؟"\'\(\)\[\]\{\}\/\\\\|]*';
	$core    = implode( $between, $parts );
	// Enforce token boundaries to reduce false positives inside normal words.
	return '/(?<![\p{L}\p{N}_])' . $core . '(?![\p{L}\p{N}_])/ui';
}

/**
 * @return array<string,mixed>
 */
function ez_get_product_review_bad_words_dictionary(): array {
	static $cached = null;
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$cached = array(
		'fa'        => array( 'base' => array(), 'phrases' => array() ),
		'en'        => array( 'base' => array(), 'phrases' => array() ),
		'finglish'  => array( 'base' => array(), 'phrases' => array() ),
		'whitelist' => array(),
	);

	$file = ez_product_review_bad_words_file_path();
	if ( ! file_exists( $file ) ) {
		return $cached;
	}

	$json = file_get_contents( $file );
	if ( ! is_string( $json ) || $json === '' ) {
		return $cached;
	}

	$data = json_decode( $json, true );
	if ( ! is_array( $data ) ) {
		return $cached;
	}

	$cached = wp_parse_args( $data, $cached );
	return $cached;
}

/**
 * Load and flatten configured bad words list.
 *
 * @return string[] bad words list
 */
function ez_get_product_review_bad_words(): array {
	$dict  = ez_get_product_review_bad_words_dictionary();
	$words = array();
	$keys  = array( 'fa', 'en', 'finglish' );

	foreach ( $keys as $bucket ) {
		$group = $dict[ $bucket ] ?? array();
		if ( ! is_array( $group ) ) {
			continue;
		}
		$base = $group['base'] ?? array();
		if ( is_array( $base ) ) {
			foreach ( $base as $w ) {
				$w = trim( (string) $w );
				if ( $w !== '' ) {
					$words[] = $w;
				}
			}
		}
		$phrases = $group['phrases'] ?? array();
		if ( is_array( $phrases ) ) {
			foreach ( $phrases as $w ) {
				$w = trim( (string) $w );
				if ( $w !== '' ) {
					$words[] = $w;
				}
			}
		}
	}

	if ( isset( $dict['word'] ) && is_array( $dict['word'] ) ) {
		foreach ( $dict['word'] as $w ) {
			$w = trim( (string) $w );
			if ( $w !== '' ) {
				$words[] = $w;
			}
		}
	}

	$whitelist_lookup = array();
	$whitelist        = $dict['whitelist'] ?? array();
	if ( is_array( $whitelist ) ) {
		foreach ( $whitelist as $item ) {
			$normalized = ez_normalize_persian_text( (string) $item );
			if ( $normalized !== '' ) {
				$whitelist_lookup[ $normalized ] = true;
			}
		}
	}

	$final = array();
	foreach ( $words as $w ) {
		$normalized = ez_normalize_persian_text( $w );
		if ( $normalized === '' || mb_strlen( $normalized, 'UTF-8' ) < 2 ) {
			continue;
		}
		if ( isset( $whitelist_lookup[ $normalized ] ) ) {
			continue;
		}
		$final[ $normalized ] = true;
	}

	return array_keys( $final );
}

/**
 * Unified moderation pipeline.
 *
 * @return array{is_valid:bool,decision:string,issues:array<int,array<string,mixed>>,sanitized_text:string,normalized_text:string,risk_score:int}
 */
function ez_moderate_comment_text( string $raw_content ): array {
	$normalized_content = ez_normalize_persian_text( $raw_content );
	$sanitized_text     = sanitize_textarea_field( $raw_content );
	$issues             = array();
	$risk_score         = 0;

	$bad_words = ez_get_product_review_bad_words();
	foreach ( $bad_words as $word ) {
		$pattern = ez_build_bad_word_pattern( $word );
		if ( @preg_match( $pattern, $normalized_content ) ) {
			$issues[] = array(
				'type'      => 'profanity_detected',
				'matched'   => $word,
				'severity'  => 'high',
				'risk_gain' => 50,
			);
			$risk_score += 50;
			break;
		}
	}

	if ( preg_match( '/(?:(?:\+|00)98|0)?9\d{2}[\s\-\.]?\d{3}[\s\-\.]?\d{4}/u', $raw_content ) ) {
		$issues[] = array(
			'type'      => 'phone_number_detected',
			'severity'  => 'medium',
			'risk_gain' => 20,
		);
		$risk_score += 20;
	}

	if ( preg_match( '/\b(?:https?:\/\/|www\.)[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:\/[^\s]*)?\b/ui', $raw_content ) ) {
		$issues[] = array(
			'type'      => 'url_detected',
			'severity'  => 'medium',
			'risk_gain' => 20,
		);
		$risk_score += 20;
	}

	$decision = 'allow';
	if ( $risk_score >= 50 ) {
		$decision = 'hold';
	} elseif ( $risk_score > 0 ) {
		$decision = 'hold';
	}

	return array(
		'is_valid'       => ( $decision === 'allow' ),
		'decision'       => $decision,
		'issues'         => $issues,
		'sanitized_text' => $sanitized_text,
		'normalized_text'=> $normalized_content,
		'risk_score'     => $risk_score,
	);
}

/**
 * Backward-compatible wrapper for previous moderation API.
 *
 * @return array{flag:bool,moderation_details:array<string,mixed>}
 */
function ez_apply_swear_moderation( string $content ): array {
	$moderation_result = ez_moderate_comment_text( $content );
	return array(
		'flag'               => ! $moderation_result['is_valid'],
		'moderation_details' => $moderation_result,
	);
}

/**
 * Last 10 digits of billing phone for teammate matching.
 */
function ez_user_billing_phone_10( WP_User $user ): string {
	$user_phone = preg_replace( '/[^0-9]/', '', (string) $user->billing_phone );
	return ! empty( $user_phone ) ? substr( $user_phone, -10 ) : '';
}

/**
 * Resolve latest qualifying wp_markting row for this user and product (game_id).
 *
 * @return object|WP_Error Row with order_id, order_sans_date, order_sans_time, game_name, customer_id, order_phones
 */
function ez_resolve_user_latest_valid_markting_row( int $user_id, int $product_id, string $user_phone_10 ) {
	global $wpdb;

	$where_clause = $wpdb->prepare( 'customer_id = %d', $user_id );
	if ( ! empty( $user_phone_10 ) ) {
		$where_clause .= $wpdb->prepare( ' OR order_phones LIKE %s', '%' . $wpdb->esc_like( $user_phone_10 ) . '%' );
	}

	$potential_orders = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT order_id, order_sans_date, order_sans_time, game_name, customer_id, order_phones
			 FROM wp_markting
			 WHERE game_id = %d
			   AND order_status IN ('wc-completed', 'wc-completed-paid', 'wc-walletx', 'wc-partially-paid')
			   AND ($where_clause)
			 ORDER BY order_sans_date DESC, order_sans_time DESC",
			$product_id
		)
	);

	$valid_order = null;

	if ( $potential_orders ) {
		foreach ( $potential_orders as $row ) {
			if ( (int) $row->customer_id === $user_id ) {
				$valid_order = $row;
				break;
			}
			if ( ! empty( $row->order_phones ) && ! empty( $user_phone_10 ) ) {
				$phones_array = maybe_unserialize( $row->order_phones );
				if ( is_array( $phones_array ) ) {
					foreach ( $phones_array as $teammate ) {
						if ( ! empty( $teammate['phone'] ) ) {
							$teammate_phone    = preg_replace( '/[^0-9]/', '', (string) $teammate['phone'] );
							$teammate_phone_10 = substr( $teammate_phone, -10 );
							if ( $teammate_phone_10 === $user_phone_10 ) {
								$valid_order = $row;
								break 2;
							}
						}
					}
				}
			}
		}
	}

	if ( ! $valid_order ) {
		return new WP_Error(
			'not_buyer',
			'ثبت دیدگاه تنها برای خریداران این بازی (سرگروه یا هم‌تیمی) امکان پذیر است!'
		);
	}

	return $valid_order;
}

/**
 * @param object $row Row from ez_resolve_user_latest_valid_markting_row
 * @return int|false
 */
function ez_markting_row_booking_timestamp( object $row ) {
	return strtotime( $row->order_sans_date . ' ' . $row->order_sans_time );
}

/**
 * @param int $booking_ts Unix timestamp (site/local strtotime result for sans datetime string)
 * @return true|WP_Error
 */
function ez_product_review_time_window_ok( int $booking_ts ) {
	if ( ! $booking_ts ) {
		return new WP_Error( 'bad_booking', 'خطا در خواندن تاریخ سانس بازی.' );
	}
	$current_time = current_time( 'timestamp' );
	if ( $current_time - $booking_ts < HOUR_IN_SECONDS ) {
		return new WP_Error(
			'comment_too_soon',
			'برای ثبت کامنت باید حداقل 60 دقیقه پس از برگزاری سانس اقدام نمایید.'
		);
	}
	if ( $current_time - $booking_ts > 7 * DAY_IN_SECONDS ) {
		return new WP_Error(
			'comment_expired',
			'بیش از یک هفته از برگزاری این بازی گذشته است. دیگر نمی توانید برای این بازی کامنت ثبت کنید.'
		);
	}
	return true;
}

/**
 * First top-level product review by this user on this product (any status except spam/trash).
 *
 * @return int comment_ID or 0
 */
function ez_get_user_product_review_comment_id( int $user_id, int $product_id ): int {
	if ( $user_id < 1 || $product_id < 1 ) {
		return 0;
	}
	$ids = get_comments(
		[
			'post_id' => $product_id,
			'user_id' => $user_id,
			'type'    => 'review',
			'status'  => [ 'approve', 'hold' ],
			'parent'  => 0,
			'number'  => 1,
			'orderby' => 'comment_ID',
			'order'   => 'ASC',
			'fields'  => 'ids',
		]
	);
	if ( empty( $ids ) || ! is_array( $ids ) ) {
		return 0;
	}
	$cid = (int) $ids[0];
	if ( $cid < 1 ) {
		return 0;
	}
	$c = get_comment( $cid );
	if ( ! $c || in_array( $c->comment_approved, [ 'spam', 'trash' ], true ) ) {
		return 0;
	}
	return $cid;
}

/**
 * Whether user may submit/edit review for this product (buyer + inside time window).
 *
 * @return true|WP_Error
 */
function ez_user_may_review_product_in_window( int $user_id, int $product_id, WP_User $user ) {
	$user_phone_10 = ez_user_billing_phone_10( $user );
	$row           = ez_resolve_user_latest_valid_markting_row( $user_id, $product_id, $user_phone_10 );
	if ( is_wp_error( $row ) ) {
		return $row;
	}
	$booking_ts = ez_markting_row_booking_timestamp( $row );
	$tw         = ez_product_review_time_window_ok( (int) $booking_ts );
	if ( is_wp_error( $tw ) ) {
		return $tw;
	}
	return true;
}

/**
 * JSON error helper for review AJAX.
 *
 * @param string               $code Machine-readable code.
 * @param string               $message Farsi message.
 * @param array<string, mixed> $extra Optional extra fields.
 */
function ez_send_product_review_error( string $code, string $message, array $extra = [] ): void {
	wp_send_json_error(
		array_merge(
			[
				'message' => $message,
				'code'    => $code,
			],
			$extra
		)
	);
}

/**
 * Power weights by stored user_level meta on comment (matches get_user_rating_power map).
 *
 * @return array<int, int>
 */
function ez_product_review_power_map(): array {
	return [
		1                                  => 1,
		2                                  => 2,
		3                                  => 7,
		4                                  => 20,
		(int) EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR => 7,
	];
}

function ez_product_review_comment_applies_to_totals( $comment ): bool {
	if ( ! $comment instanceof WP_Comment ) {
		return false;
	}
	return get_post_type( (int) $comment->comment_post_ID ) === 'product' && $comment->comment_type === 'review';
}

/**
 * Add aggregates when a product review is approved (insert-as-approved or transition to approved).
 */
function ez_product_review_add_totals_for_comment( int $comment_id ): void {
	$c = get_comment( $comment_id );
	if ( ! $c || ! ez_product_review_comment_applies_to_totals( $c ) ) {
		return;
	}
	$comment_rating = get_comment_meta( $comment_id, 'comment_rating', true );
	if ( ! is_array( $comment_rating ) ) {
		return;
	}
	$user_level = (int) get_comment_meta( $comment_id, 'user_level', true );
	$user_power = ez_comment_stored_user_level_to_rating_power( $user_level );
	$product_id = (int) $c->comment_post_ID;

	$temp  = get_post_meta( $product_id, 'product_rates', true );
	$temp4 = get_post_meta( $product_id, 'clone_product_rates', true );
	$keys  = ez_get_product_review_rate_keys();
	$product_rates       = ! empty( $temp ) && is_array( $temp ) ? $temp : array_fill_keys( $keys, 0 );
	$clone_product_rates = ! empty( $temp4 ) && is_array( $temp4 ) ? $temp4 : array_fill_keys( $keys, 0 );

	foreach ( $keys as $k ) {
		$v = (int) ( $comment_rating[ $k ] ?? 0 );
		$product_rates[ $k ]       += $v;
		$clone_product_rates[ $k ] += $v * $user_power;
	}
	update_post_meta( $product_id, 'product_rates', $product_rates );
	update_post_meta( $product_id, 'clone_product_rates', $clone_product_rates );

	$t2 = (int) get_post_meta( $product_id, 'comments_count_new', true );
	update_post_meta( $product_id, 'comments_count_new', $t2 + 1 );

	$t3 = (int) get_post_meta( $product_id, 'clone_comments_count_new', true );
	update_post_meta( $product_id, 'clone_comments_count_new', $t3 + $user_power );

	if ( class_exists( Ez_Product_Rating_Rollup_Service::class ) ) {
		$rollup = Ez_Product_Rating_Rollup_Service::instance();
		$rollup->sync_storage_from_comment_meta( $comment_id );
		$rollup->apply_totals_if_counted( $comment_id );
	}

	ez_product_ranking_sync_after_review_change( $product_id );
}

/**
 * Remove aggregates (unapprove transition or manual rollback).
 */
function ez_product_review_remove_totals_for_comment( int $comment_id ): void {
	$c = get_comment( $comment_id );
	if ( ! $c || ! ez_product_review_comment_applies_to_totals( $c ) ) {
		return;
	}

	if ( class_exists( Ez_Product_Rating_Rollup_Service::class ) ) {
		Ez_Product_Rating_Rollup_Service::instance()->remove_totals_if_counted( $comment_id );
	}

	$product_id = (int) $c->comment_post_ID;
	$comment_rating = get_comment_meta( $comment_id, 'comment_rating', true );
	if ( ! is_array( $comment_rating ) ) {
		if ( class_exists( Ez_Product_Rating_Rollup_Service::class ) ) {
			Ez_Product_Rating_Rollup_Service::instance()->sync_storage_from_comment_meta( $comment_id );
		}
		ez_product_ranking_sync_after_review_change( $product_id );

		return;
	}
	$user_level = (int) get_comment_meta( $comment_id, 'user_level', true );
	$user_power = ez_comment_stored_user_level_to_rating_power( $user_level );

	$temp  = get_post_meta( $product_id, 'product_rates', true );
	$temp4 = get_post_meta( $product_id, 'clone_product_rates', true );
	if ( ! is_array( $temp ) || ! is_array( $temp4 ) ) {
		if ( class_exists( Ez_Product_Rating_Rollup_Service::class ) ) {
			Ez_Product_Rating_Rollup_Service::instance()->sync_storage_from_comment_meta( $comment_id );
		}
		ez_product_ranking_sync_after_review_change( $product_id );

		return;
	}
	$keys = ez_get_product_review_rate_keys();
	foreach ( $keys as $k ) {
		$v = (int) ( $comment_rating[ $k ] ?? 0 );
		$temp[ $k ]  -= $v;
		$temp4[ $k ] -= $v * $user_power;
	}
	update_post_meta( $product_id, 'product_rates', $temp );
	update_post_meta( $product_id, 'clone_product_rates', $temp4 );

	$t2 = (int) get_post_meta( $product_id, 'comments_count_new', true );
	update_post_meta( $product_id, 'comments_count_new', max( 0, $t2 - 1 ) );

	$t3 = (int) get_post_meta( $product_id, 'clone_comments_count_new', true );
	update_post_meta( $product_id, 'clone_comments_count_new', max( 0, $t3 - $user_power ) );

	if ( class_exists( Ez_Product_Rating_Rollup_Service::class ) ) {
		Ez_Product_Rating_Rollup_Service::instance()->sync_storage_from_comment_meta( $comment_id );
	}

	ez_product_ranking_sync_after_review_change( $product_id );
}

/**
 * Delta update for an already-approved review when ratings or user power change.
 */
function ez_product_review_apply_rating_delta_approved( int $product_id, array $old_rating, int $old_power, array $new_rating, int $new_power ): void {
	$keys = ez_get_product_review_rate_keys();
	$temp  = get_post_meta( $product_id, 'product_rates', true );
	$temp4 = get_post_meta( $product_id, 'clone_product_rates', true );
	$product_rates       = ! empty( $temp ) && is_array( $temp ) ? $temp : array_fill_keys( $keys, 0 );
	$clone_product_rates = ! empty( $temp4 ) && is_array( $temp4 ) ? $temp4 : array_fill_keys( $keys, 0 );

	foreach ( $keys as $k ) {
		$o = (int) ( $old_rating[ $k ] ?? 0 );
		$n = (int) ( $new_rating[ $k ] ?? 0 );
		$product_rates[ $k ]       += ( $n - $o );
		$clone_product_rates[ $k ] += ( $n * $new_power ) - ( $o * $old_power );
	}
	update_post_meta( $product_id, 'product_rates', $product_rates );
	update_post_meta( $product_id, 'clone_product_rates', $clone_product_rates );

	$t3 = (int) get_post_meta( $product_id, 'clone_comments_count_new', true );
	update_post_meta( $product_id, 'clone_comments_count_new', max( 0, $t3 + ( $new_power - $old_power ) ) );

	if ( Ez_Product_Ratings_Schema::is_installed() ) {
		$svc        = Ez_Product_Rating_Rollup_Service::instance();
		$old_scores = ez_product_rating_legacy_rating_to_crit_scores( $old_rating );
		$new_scores = ez_product_rating_legacy_rating_to_crit_scores( $new_rating );
		$svc->apply_delta_approved_edit( $product_id, $old_scores, $old_power, $new_scores, $new_power );
	}

	ez_product_ranking_sync_after_review_change( $product_id );
}

/**
 * Sync overall rating meta + incremental popular/hottest scores (ez_core ProductRanking).
 */
function ez_product_ranking_sync_after_review_change( int $product_id ): void {
	if ( $product_id < 1 ) {
		return;
	}

	if ( class_exists( '\EscapeZoom\Core\Modules\ProductRanking\Services\ProductRatingScoreWriter' ) ) {
		\EscapeZoom\Core\Modules\ProductRanking\Services\ProductRatingScoreWriter::syncProduct( $product_id );

		return;
	}

	do_action( 'ez_ranking_recalculate', $product_id, [ 'popular', 'hottest' ] );
}
