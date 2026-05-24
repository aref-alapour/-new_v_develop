<?php
/**
 * program_date_box_save
 *
 * توابع: program_date_box_save هوک‌ها: save_post
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5825-5841)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'save_post', 'program_date_box_save' );
function program_date_box_save( $ticket_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;
    if ( !wp_verify_nonce( $_POST['program_price_box_content_nonce'], plugin_basename( __FILE__ ) ) ) return; // CSRD token validation

    if ( 'page' == $_POST['post_type'] )  // whether we are in related post_type page or not
        if ( !current_user_can( 'edit_page', $ticket_id ) ) return;
        else
            if ( !current_user_can( 'edit_post', $ticket_id ) ) return;

    $data_pack['ticket_closed'] = $_POST['ticket_closed']; // get posted data of meta box fields

    foreach ($data_pack as $key => $value)
        update_post_meta( $ticket_id, $key, $value );

}
