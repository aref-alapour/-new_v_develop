<?php
/**
 * Modal: brand members preview (product_brand taxonomy list screen).
 */
defined( 'ABSPATH' ) || exit;
?>
<div
	id="ez-brand-members-modal"
	class="ez-brand-members-modal"
	role="dialog"
	aria-modal="true"
	aria-labelledby="ez-brand-members-modal-title">
	<div class="ez-brand-members-modal__backdrop" tabindex="-1" aria-hidden="true"></div>
	<div class="ez-brand-members-modal__panel" role="document">
		<div class="ez-brand-members-modal__header">
			<h2 id="ez-brand-members-modal-title" class="ez-brand-members-modal__title">
				<?php esc_html_e( 'اعضای برند', 'escapezoom' ); ?>
			</h2>
			<button type="button" class="button ez-brand-members-modal__close" aria-label="<?php echo esc_attr__( 'بستن', 'escapezoom' ); ?>">&times;</button>
		</div>
		<div class="ez-brand-members-modal__body"></div>
	</div>
</div>
