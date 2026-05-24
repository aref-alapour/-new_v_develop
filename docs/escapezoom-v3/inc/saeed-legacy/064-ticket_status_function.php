<?php
/**
 * ticket_status_function
 *
 * توابع: ticket_status_function
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5802-5811)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ticket_status_function ( $post ) {
    wp_nonce_field( plugin_basename( __FILE__ ), 'program_price_box_content_nonce' );

    $ticket_id      = $post->ID;
    $ticked_closed  = get_post_meta($ticket_id, 'ticket_closed', true); ?>

    <label for="ticket_closed">بستن تیکت </label>
    <input type="checkbox" id="ticket_closed" name="ticket_closed" value="1" <?php echo checked( 1, $ticked_closed, false ) ?> />
    <?php
}
