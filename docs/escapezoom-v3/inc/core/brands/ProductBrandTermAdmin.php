<?php
/**
 * Admin fields for WooCommerce brand terms.
 */
defined( 'ABSPATH' ) || exit;

const EZ_BRAND_META_ADDRESS              = 'brand_address';
const EZ_BRAND_META_GAME_TYPES         = 'brand_game_types';
const EZ_BRAND_META_GAME_IDENTITY_IDS  = '_ez_brand_game_identity_ids';
const EZ_BRAND_META_TEAM               = 'brand_team_members';
const EZ_BRAND_META_NONCE_ACTION       = 'ez_brand_term_meta_save';
const EZ_BRAND_META_NONCE_NAME         = 'ez_brand_term_meta_nonce';
/** Set to '1' to allow search engines to index this brand archive (default is noindex). */
const EZ_BRAND_META_ALLOW_INDEX        = 'ez_brand_allow_search_index';

/**
 * @return array<string,string>
 */
function ez_brand_game_type_options(): array {
	if ( taxonomy_exists( 'ez_game_identity' ) ) {
		$terms = get_terms(
			[
				'taxonomy'   => 'ez_game_identity',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			]
		);
		if ( ! is_wp_error( $terms ) && is_array( $terms ) && $terms !== [] ) {
			$out = [];
			foreach ( $terms as $term ) {
				$out[ (string) $term->slug ] = (string) $term->name;
			}
			return $out;
		}
	}

	$product_cats = get_terms(
		[
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		]
	);
	if ( ! is_wp_error( $product_cats ) && is_array( $product_cats ) && $product_cats !== [] ) {
		$out = [];
		foreach ( $product_cats as $cat ) {
			$out[ (string) $cat->slug ] = (string) $cat->name;
		}
		if ( $out !== [] ) {
			return $out;
		}
	}

	if ( function_exists( 'get_product_type_equivalent' ) ) {
		$slugs = [ 'cafegame', 'escaperoom', 'cinema', 'lasertag', 'rageroom', 'bubblefootball', 'paintball', 'haunted_house' ];
		$out   = [];
		foreach ( $slugs as $slug ) {
			$title        = get_product_type_equivalent( $slug );
			$out[ $slug ] = is_string( $title ) && $title !== '' ? $title : $slug;
		}
		return $out;
	}

	return [];
}

/**
 * @return array<int,array{name:string,position:string,image_id:int}>
 */
function ez_brand_team_members( int $term_id ): array {
	$raw = get_term_meta( $term_id, EZ_BRAND_META_TEAM, true );
	if ( ! is_array( $raw ) ) {
		return [];
	}
	$out = [];
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$out[] = [
			'name'     => (string) ( $row['name'] ?? '' ),
			'position' => (string) ( $row['position'] ?? '' ),
			'image_id' => (int) ( $row['image_id'] ?? 0 ),
		];
	}
	return $out;
}

/**
 * @param array<string,string> $game_types
 * @return string[]
 */
function ez_brand_selected_game_types( int $term_id, array $game_types ): array {
	$selected = get_term_meta( $term_id, EZ_BRAND_META_GAME_TYPES, true );
	$selected = is_array( $selected ) ? array_map( 'strval', $selected ) : [];

	$legacy_ids = get_term_meta( $term_id, EZ_BRAND_META_GAME_IDENTITY_IDS, true );
	if ( is_array( $legacy_ids ) && taxonomy_exists( 'ez_game_identity' ) ) {
		foreach ( $legacy_ids as $raw_id ) {
			$legacy_id = (int) $raw_id;
			if ( $legacy_id < 1 ) {
				continue;
			}
			$term = get_term( $legacy_id, 'ez_game_identity' );
			if ( $term && ! is_wp_error( $term ) && ! in_array( (string) $term->slug, $selected, true ) ) {
				$selected[] = (string) $term->slug;
			}
		}
	}

	return array_values( array_intersect( array_keys( $game_types ), array_unique( $selected ) ) );
}

/**
 * @return list<array{name:string,position:string,thumb:string}>
 */
function ez_brand_team_members_for_modal_json( int $term_id ): array {
	$out     = array();
	$members = ez_brand_team_members( $term_id );
	foreach ( $members as $m ) {
		$thumb = '';
		if ( ! empty( $m['image_id'] ) ) {
			$url = wp_get_attachment_image_url( (int) $m['image_id'], 'thumbnail' );
			$thumb = is_string( $url ) ? $url : '';
		}
		$out[] = array(
			'name'     => (string) $m['name'],
			'position' => (string) $m['position'],
			'thumb'    => $thumb,
		);
	}
	return $out;
}

/**
 * @param string[] $columns
 * @return string[]
 */
function ez_brand_taxonomy_list_columns( array $columns ): array {
	unset( $columns['description'] );

	foreach ( array_keys( $columns ) as $key ) {
		if ( strpos( (string) $key, 'wpseo' ) === 0 || strpos( (string) $key, 'rank_math' ) === 0 ) {
			unset( $columns[ $key ] );
		}
	}

	foreach (
		array(
			'wpseo-score',
			'wpseo-score-readability',
			'wpseo-metadesc',
			'wpseo-focuskw',
			'wpseo-links',
			'wpseo_links',
			'wpseo_focuskw',
			'wpseo_metadesc',
		) as $legacy
	) {
		unset( $columns[ $legacy ] );
	}

	if ( isset( $columns['posts'] ) ) {
		$columns['posts'] = 'تعداد بازی';
	}

	foreach ( array( 'thumb', 'thumbnail', 'brand_thumb' ) as $wc_thumb_col ) {
		unset( $columns[ $wc_thumb_col ] );
	}

	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'cb' === $key ) {
			$new['cb']                = $label;
			$new['ez_brand_poster']    = 'پوستر';
			continue;
		}
		if ( 'name' === $key ) {
			$new['name']                = 'نام برند';
			$new['ez_brand_game_types'] = 'تایپ‌های بازی';
			$new['ez_brand_members']    = 'اعضای برند';
			continue;
		}
		$new[ $key ] = $label;
	}

	return $new;
}

/**
 * @param string $deprecated
 * @param string $column
 * @param int    $term_id
 */
function ez_brand_taxonomy_custom_column( $deprecated, $column, $term_id ): void {
	unset( $deprecated );
	$term_id = (int) $term_id;

	if ( 'ez_brand_poster' === $column ) {
		$term = get_term( $term_id, 'product_brand' );
		if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) {
			echo '&mdash;';
			return;
		}
		if ( function_exists( 'wc_get_brand_thumbnail_image' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce returns a full <img> tag.
			echo wc_get_brand_thumbnail_image( $term, 'thumbnail' );
			return;
		}
		$thumb_id = (int) get_term_meta( $term_id, 'thumbnail_id', true );
		if ( $thumb_id > 0 ) {
			echo wp_get_attachment_image(
				$thumb_id,
				'thumbnail',
				false,
				array(
					'alt'   => $term->name,
					'title' => $term->name,
					'style' => 'max-width:64px;height:auto;border-radius:4px;',
				)
			);
			return;
		}
		if ( function_exists( 'wc_placeholder_img' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wc_placeholder_img( 'thumbnail' );
			return;
		}
		echo '&mdash;';
		return;
	}

	if ( $column === 'ez_brand_game_types' ) {
		$opts     = ez_brand_game_type_options();
		$selected = ez_brand_selected_game_types( $term_id, $opts );
		if ( $selected === array() ) {
			echo '&mdash;';
			return;
		}
		$titles = array();
		foreach ( $selected as $slug ) {
			$titles[] = $opts[ $slug ] ?? $slug;
		}
		echo esc_html( implode( '، ', $titles ) );
		return;
	}

	if ( $column === 'ez_brand_members' ) {
		$payload = ez_brand_team_members_for_modal_json( $term_id );
		$json    = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE );
		echo '<button type="button" class="button ez-brand-view-members" data-members="' . esc_attr( $json ) . '" title="' . esc_attr__( 'مشاهده اعضا', 'escapezoom' ) . '"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'مشاهده اعضا', 'escapezoom' ) . '</span></button>';
	}
}

function ez_brand_admin_footer_modal_and_templates(): void {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	$is_brand = ( isset( $screen->taxonomy ) && $screen->taxonomy === 'product_brand' )
		|| in_array( $screen->id, array( 'edit-product_brand', 'product_brand' ), true );
	if ( ! $is_brand ) {
		return;
	}

	if ( $screen->id === 'edit-product_brand' ) {
		require Theme_PATH . 'inc/core/brands/admin/views/members-modal.php';
	}

	if ( in_array( $screen->id, array( 'edit-product_brand', 'product_brand' ), true ) ) {
		require Theme_PATH . 'inc/core/brands/admin/views/brand-team-row-template.php';
	}
}

function ez_brand_render_team_rows( array $rows ): void {
	if ( $rows === [] ) {
		$rows = [ [ 'name' => '', 'position' => '', 'image_id' => 0 ] ];
	}
	foreach ( $rows as $row ) {
		$img     = $row['image_id'] > 0 ? wp_get_attachment_image_url( (int) $row['image_id'], 'thumbnail' ) : '';
		$img_out = (string) $img;
		?>
		<tr>
			<td class="ez-brand-team__handle ez-brand-team-sort-handle" title="<?php echo esc_attr__( 'برای مرتب‌سازی بکشید', 'escapezoom' ); ?>">
				<span class="dashicons dashicons-menu-alt" aria-hidden="true"></span>
			</td>
			<td>
				<input type="text" name="brand_team_name[]" value="<?php echo esc_attr( $row['name'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr__( 'نام و نام خانوادگی', 'escapezoom' ); ?>">
			</td>
			<td>
				<input type="text" name="brand_team_position[]" value="<?php echo esc_attr( $row['position'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr__( 'موقعیت شغلی', 'escapezoom' ); ?>">
			</td>
			<td>
				<input type="hidden" class="ez-brand-team-image-id" name="brand_team_image_id[]" value="<?php echo esc_attr( (string) ( (int) $row['image_id'] ) ); ?>">
				<button type="button" class="button-link ez-brand-team-pick-image ez-brand-team__pick-image" title="<?php echo esc_attr__( 'انتخاب/تغییر تصویر', 'escapezoom' ); ?>">
					<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
				</button>
				<img class="ez-brand-team-thumb ez-brand-team__thumb<?php echo $img ? '' : ' is-hidden'; ?>" src="<?php echo esc_url( $img_out ); ?>" alt="">
			</td>
			<td>
				<button type="button" class="button-link-delete ez-brand-team-remove" title="<?php echo esc_attr__( 'حذف ردیف', 'escapezoom' ); ?>">
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
			</td>
		</tr>
		<?php
	}
}

function ez_brand_term_add_fields(): void {
	$game_types = ez_brand_game_type_options();
	wp_nonce_field( EZ_BRAND_META_NONCE_ACTION, EZ_BRAND_META_NONCE_NAME );
	?>
	<div class="form-field">
		<label for="brand_address"><?php esc_html_e( 'آدرس', 'escapezoom' ); ?></label>
		<input type="text" name="brand_address" id="brand_address" value="" class="regular-text ez-brand-field-address">
	</div>
	<div class="form-field ez-brand-field-game-types">
		<label><?php esc_html_e( 'نوع بازی', 'escapezoom' ); ?></label>
		<?php foreach ( $game_types as $slug => $title ) : ?>
			<label><input type="checkbox" name="brand_game_types[]" value="<?php echo esc_attr( $slug ); ?>"> <?php echo esc_html( $title ); ?></label>
		<?php endforeach; ?>
	</div>
	<div class="form-field">
		<label>اعضای گروه</label>
		<table class="widefat striped ez-brand-team-table">
			<thead><tr><th scope="col" class="ez-brand-team-table__col-drag"><span class="screen-reader-text"><?php echo esc_html__( 'ترتیب', 'escapezoom' ); ?></span></th><th><?php echo esc_html__( 'نام و نام خانوادگی', 'escapezoom' ); ?></th><th><?php echo esc_html__( 'موقعیت شغلی', 'escapezoom' ); ?></th><th><?php echo esc_html__( 'تصویر', 'escapezoom' ); ?></th><th></th></tr></thead>
			<tbody id="ez-brand-team-rows">
				<?php ez_brand_render_team_rows( [] ); ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="ez-brand-team-add">افزودن عضو</button></p>
	</div>
	<div class="form-field">
		<label class="ez-brand-field-allow-index ez-brand-field-allow-index--flex">
			<input type="checkbox" name="ez_brand_allow_index" value="1">
			<span><?php esc_html_e( 'اجازه ایندکس در موتورهای جستجو (پیش‌فرض: noindex برای صفحهٔ این برند)', 'escapezoom' ); ?></span>
		</label>
	</div>
	<?php
}

function ez_brand_term_edit_fields( WP_Term $term ): void {
	$term_id              = (int) $term->term_id;
	$address              = (string) get_term_meta( $term_id, EZ_BRAND_META_ADDRESS, true );
	$game_types           = ez_brand_game_type_options();
	$selected_game_types  = ez_brand_selected_game_types( $term_id, $game_types );
	$team_members         = ez_brand_team_members( $term_id );
	$allow_index          = get_term_meta( $term_id, EZ_BRAND_META_ALLOW_INDEX, true ) === '1';
	wp_nonce_field( EZ_BRAND_META_NONCE_ACTION, EZ_BRAND_META_NONCE_NAME );
	?>
	<tr class="form-field">
		<th scope="row"><label for="brand_address"><?php esc_html_e( 'آدرس', 'escapezoom' ); ?></label></th>
		<td><input type="text" name="brand_address" id="brand_address" value="<?php echo esc_attr( $address ); ?>" class="regular-text ez-brand-field-address"></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><?php esc_html_e( 'نوع بازی', 'escapezoom' ); ?></th>
		<td class="ez-brand-field-game-types">
			<?php foreach ( $game_types as $slug => $title ) : ?>
				<label><input type="checkbox" name="brand_game_types[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $selected_game_types, true ) ); ?>> <?php echo esc_html( $title ); ?></label>
			<?php endforeach; ?>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">اعضای گروه</th>
		<td>
			<table class="widefat striped ez-brand-team-table">
				<thead><tr><th scope="col" class="ez-brand-team-table__col-drag"><span class="screen-reader-text"><?php echo esc_html__( 'ترتیب', 'escapezoom' ); ?></span></th><th><?php echo esc_html__( 'نام و نام خانوادگی', 'escapezoom' ); ?></th><th><?php echo esc_html__( 'موقعیت شغلی', 'escapezoom' ); ?></th><th><?php echo esc_html__( 'تصویر', 'escapezoom' ); ?></th><th></th></tr></thead>
				<tbody id="ez-brand-team-rows">
					<?php ez_brand_render_team_rows( $team_members ); ?>
				</tbody>
			</table>
			<p><button type="button" class="button" id="ez-brand-team-add">افزودن عضو</button></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">موتورهای جستجو</th>
		<td>
			<label>
				<input type="checkbox" name="ez_brand_allow_index" value="1" <?php checked( $allow_index ); ?>>
				اجازه ایندکس صفحهٔ آرشیو این برند (در غیر این صورت noindex است)
			</label>
		</td>
	</tr>
	<?php
}

function ez_brand_term_save_meta( int $term_id ): void {
	if ( empty( $_POST[ EZ_BRAND_META_NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ EZ_BRAND_META_NONCE_NAME ] ) ), EZ_BRAND_META_NONCE_ACTION ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$address = isset( $_POST['brand_address'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_address'] ) ) : '';
	update_term_meta( $term_id, EZ_BRAND_META_ADDRESS, $address );

	$game_types = [];
	if ( ! empty( $_POST['brand_game_types'] ) && is_array( $_POST['brand_game_types'] ) ) {
		foreach ( wp_unslash( $_POST['brand_game_types'] ) as $raw ) {
			$slug = sanitize_key( (string) $raw );
			if ( $slug !== '' ) {
				$game_types[] = $slug;
			}
		}
	}
	update_term_meta( $term_id, EZ_BRAND_META_GAME_TYPES, array_values( array_unique( $game_types ) ) );

	$legacy_ids = [];
	if ( taxonomy_exists( 'ez_game_identity' ) ) {
		foreach ( $game_types as $slug ) {
			$term = get_term_by( 'slug', $slug, 'ez_game_identity' );
			if ( $term && ! is_wp_error( $term ) ) {
				$legacy_ids[] = (int) $term->term_id;
			}
		}
	}
	update_term_meta( $term_id, EZ_BRAND_META_GAME_IDENTITY_IDS, array_values( array_unique( $legacy_ids ) ) );

	$names     = isset( $_POST['brand_team_name'] ) && is_array( $_POST['brand_team_name'] ) ? wp_unslash( $_POST['brand_team_name'] ) : [];
	$positions = isset( $_POST['brand_team_position'] ) && is_array( $_POST['brand_team_position'] ) ? wp_unslash( $_POST['brand_team_position'] ) : [];
	$images    = isset( $_POST['brand_team_image_id'] ) && is_array( $_POST['brand_team_image_id'] ) ? wp_unslash( $_POST['brand_team_image_id'] ) : [];

	$count   = max( count( $names ), count( $positions ), count( $images ) );
	$members = [];
	for ( $i = 0; $i < $count; $i++ ) {
		$name      = sanitize_text_field( (string) ( $names[ $i ] ?? '' ) );
		$position  = sanitize_text_field( (string) ( $positions[ $i ] ?? '' ) );
		$image_id  = (int) ( $images[ $i ] ?? 0 );
		if ( $name === '' && $position === '' && $image_id < 1 ) {
			continue;
		}
		$members[] = [
			'name'     => $name,
			'position' => $position,
			'image_id' => $image_id,
		];
	}
	update_term_meta( $term_id, EZ_BRAND_META_TEAM, $members );

	if ( ! empty( $_POST['ez_brand_allow_index'] ) && (string) wp_unslash( $_POST['ez_brand_allow_index'] ) === '1' ) {
		update_term_meta( $term_id, EZ_BRAND_META_ALLOW_INDEX, '1' );
	} else {
		delete_term_meta( $term_id, EZ_BRAND_META_ALLOW_INDEX );
	}
}

function ez_brand_term_admin_enqueue( string $hook ): void {
	if ( ! in_array( $hook, array( 'term.php', 'edit-tags.php' ), true ) ) {
		return;
	}
	$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( (string) wp_unslash( $_GET['taxonomy'] ) ) : '';
	if ( $taxonomy !== 'product_brand' ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style(
		'ez-product-brand-term-admin',
		Theme_URL . 'assets/css/admin/product-brand-term.css',
		array(),
		filemtime( Theme_PATH . 'assets/css/admin/product-brand-term.css' )
	);
}

/**
 * اسکریپت‌های اعضای برند داخل dist/admin.js هستند؛ jquery-ui-sortable باید قبل از آن بارگذاری شود.
 */
function ez_brand_patch_admin_js_sortable_dep( string $hook ): void {
	if ( ! in_array( $hook, array( 'term.php', 'edit-tags.php' ), true ) ) {
		return;
	}
	if ( ! isset( $_GET['taxonomy'] ) || sanitize_key( (string) wp_unslash( $_GET['taxonomy'] ) ) !== 'product_brand' ) {
		return;
	}
	wp_enqueue_script( 'jquery-ui-sortable' );
	$scripts = wp_scripts();
	if ( isset( $scripts->registered['admin-js'] ) && ! in_array( 'jquery-ui-sortable', $scripts->registered['admin-js']->deps, true ) ) {
		$scripts->registered['admin-js']->deps[] = 'jquery-ui-sortable';
	}
}

add_action( 'product_brand_add_form_fields', 'ez_brand_term_add_fields' );
add_action( 'product_brand_edit_form_fields', 'ez_brand_term_edit_fields', 10, 1 );
add_action( 'created_product_brand', 'ez_brand_term_save_meta', 10, 1 );
add_action( 'edited_product_brand', 'ez_brand_term_save_meta', 10, 1 );
add_action( 'admin_enqueue_scripts', 'ez_brand_term_admin_enqueue' );
add_action( 'admin_enqueue_scripts', 'ez_brand_patch_admin_js_sortable_dep', 15 );
add_filter( 'manage_edit-product_brand_columns', 'ez_brand_taxonomy_list_columns', 999 );
add_action( 'manage_product_brand_custom_column', 'ez_brand_taxonomy_custom_column', 10, 3 );
add_action( 'admin_footer', 'ez_brand_admin_footer_modal_and_templates', 20 );
