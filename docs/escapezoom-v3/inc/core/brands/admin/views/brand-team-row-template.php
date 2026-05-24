<?php
/**
 * `<template>` for one empty team row — cloned in `brand-term-admin.js` (avoids duplicating row HTML in JS).
 */
defined( 'ABSPATH' ) || exit;
?>
<template id="ez-brand-team-row-template">
	<tr>
		<td class="ez-brand-team__handle ez-brand-team-sort-handle" title="<?php echo esc_attr__( 'برای مرتب‌سازی بکشید', 'escapezoom' ); ?>">
			<span class="dashicons dashicons-menu-alt" aria-hidden="true"></span>
		</td>
		<td>
			<input type="text" name="brand_team_name[]" value="" class="regular-text" placeholder="<?php echo esc_attr__( 'نام و نام خانوادگی', 'escapezoom' ); ?>">
		</td>
		<td>
			<input type="text" name="brand_team_position[]" value="" class="regular-text" placeholder="<?php echo esc_attr__( 'موقعیت شغلی', 'escapezoom' ); ?>">
		</td>
		<td>
			<input type="hidden" class="ez-brand-team-image-id" name="brand_team_image_id[]" value="0">
			<button type="button" class="button-link ez-brand-team-pick-image ez-brand-team__pick-image" title="<?php echo esc_attr__( 'انتخاب/تغییر تصویر', 'escapezoom' ); ?>">
				<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
			</button>
			<img class="ez-brand-team-thumb ez-brand-team__thumb is-hidden" src="" alt="">
		</td>
		<td>
			<button type="button" class="button-link-delete ez-brand-team-remove" title="<?php echo esc_attr__( 'حذف ردیف', 'escapezoom' ); ?>">
				<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
			</button>
		</td>
	</tr>
</template>
