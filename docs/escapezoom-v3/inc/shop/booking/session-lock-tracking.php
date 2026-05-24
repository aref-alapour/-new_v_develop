<?php
/** lines 5672-5814 → shop/booking/session-lock-tracking.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_before_calculate_totals', 'controle_before_place_order' );
function controle_before_place_order() {
    if ( is_checkout() ) :

        if ( isset( $_GET['book'] ) || !empty( $_GET['book'] )  ) {

            $_SESSION['book']       = htmlspecialchars($_GET['book']);
            $_SESSION['quantity']   = htmlspecialchars($_GET['quantity']);
            $_SESSION['product_id'] = htmlspecialchars($_GET['add-to-cart']);;
            $_SESSION['user_id']    = get_current_user_id();
            $_SESSION['c_time']     = time();
        }

    endif;
}
/****************************************************************************************************************************************/
function ez_add_booking_lock ($product_id, $booking_time) {
    global $wpdb;

    $wpdb->insert( 'booking_lock_schedule', array(
        'product_id'    => $product_id,
        'booking_time'  => $booking_time,
        'lock_time'     => time(),
    ));

    return $wpdb->insert_id ? true : false;
}
/********************************************************************************************************************************/
function ez_remove_booking_lock ( $product_id, $booking_time ) {
    global $wpdb;

    $wpdb->get_results(
        $wpdb->prepare(
            'DELETE FROM booking_lock_schedule WHERE product_id = %s AND booking_time = %s',
            (string) $product_id,
            (string) $booking_time
        )
    );
}
/********************************************************************************************************************************/
function ez_get_booking_lock ( $product_id ) {
    global $wpdb;

    $res = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM booking_lock_schedule WHERE product_id = %s',
            (string) $product_id
        )
    );

    return $res;
}
/****************************************************************************************************************************************/
//add_action( 'woocommerce_checkout_order_processed', 'detect_zibal_payment_method_for_lock' );
function detect_zibal_payment_method_for_lock( $order_id ) {

    $order = wc_get_order( $order_id );

    $product_id = '';
    foreach ($order->get_items() as $product )
        $product_id = $product['product_id'];

    $payment_method = $order->get_payment_method_title();
    if ( $payment_method == 'پرداخت امن آنلاین' || $payment_method == 'پرداخت اینترنتی' || $payment_method == 'پرداخت آنلاین' )
        ez_reservation( array('type' => 'add_sans_lock', 'data' => array('product_id' => $product_id, 'booking_time' => $_SESSION['book'])) );
}
/****************************************************************************************************************************************/
add_action( 'wp', 'visit_single_room_unlock_booking' );
function visit_single_room_unlock_booking() {

    if ( get_post_type() == 'product' ) {
        $product_id = get_the_ID();
        $bookings   = ez_get_booking_lock($product_id);

        foreach ( $bookings as $booking ) {
            if ( $booking->booking_time < time() || $booking->lock_time + (60 * 5) < time() ) { // if 5 mins has finished
                ez_remove_booking_lock( $product_id, $booking->booking_time );
            }
        }
    }
}
/****************************************************************************************************************************************/
add_action( 'wp', 'tracking_back_btn_in_checkout_page' );
function tracking_back_btn_in_checkout_page() {

    if ( is_checkout() ) { ?>

        <script>
            // add parameter to url for detecting if the user pressed back btn
            window.addEventListener( "pageshow", function ( e ) {
                if ( e.persisted || ( typeof window.performance != "undefined" && window.performance.navigation.type === 2 ) ) {
                    var url = window.location.href;
                    if (url.indexOf('?') > -1)
                        url += '&back=1'
                    else
                        url += '?back=1'

                    window.location.href = url;
                }
            });
        </script>

        <?php
//        if ( isset( $_GET['back'] ) && !empty( $_GET['back'] ) ) {
//            ez_reservation( array('type' => 'remove_sans_lock', 'data' => array('product_id' => $_GET['add-to-cart'], 'booking_time' => $_GET['book'])) );
////            ez_remove_booking_lock( $_GET['add-to-cart'], $_GET['book'] );
//        }
    }
}
/****************************************************************************************************************************************/
//add_action( 'wp_footer', 'checkout_place_order_script' );
function checkout_place_order_script() {
    if( is_checkout() && ! is_wc_endpoint_url() ): ?>

        <script>
            var $ = jQuery;
            jQuery(document).ready(function () {
                var fc          = 'form.checkout';
                var pl          = 'button[type="submit"][name="woocommerce_checkout_place_order"]';
                var ajax_url    = needs_localize.ajaxurl;
                var nonce       = needs_localize.security;

                $(fc).on( 'submit', pl, function(e){
                    e.preventDefault(); // Disable "Place Order" button

                    $.ajax({
                        type    : 'POST',
                        url     : ajax_url,
                        data    : {
                            'action'                : 'ez_site_ajax_handler',
                            'nonce'                 : nonce,
                            '_checkout_sessions_'   : true,
                            'book'                  : <?php echo htmlspecialchars($_GET['book']); ?>,
                            'quantity'              : <?php echo htmlspecialchars($_GET['quantity']); ?>,
                            'product_id'            : <?php echo htmlspecialchars($_GET['add-to-cart']); ?>,
                            'user_id'               : <?php echo get_current_user_id(); ?>,
                            'c_time'                : <?php echo time(); ?>,
                        },
                        dataType: "json",
                        success: function(data) {
                            $('body').trigger('update_checkout');
                        },
                    });
                });
            });
        </script>
    <?php
    endif;
}
/****************************************************************************************************************************************/
