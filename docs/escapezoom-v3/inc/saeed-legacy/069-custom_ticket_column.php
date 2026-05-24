<?php
/**
 * custom_ticket_column
 *
 * توابع: custom_ticket_column هوک‌ها: manage_ticketing_posts_custom_column
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5861-5912)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'manage_ticketing_posts_custom_column' , 'custom_ticket_column', 10, 2 );// Add the data to the custom columns for the teacher post type:
function custom_ticket_column( $column, $ticket_id ) {

    $messages = get_post_meta($ticket_id, 'messages', true);

    switch ( $column ) {

        case 'type' :
            $ticket_type = get_the_content($ticket_id);
            echo $ticket_type;
            break;

        case 'status' :

            if ( get_post_meta($ticket_id, 'ticket_closed', true) ): ?>
                <span class="ticket_status" style="background: #0a0a0a;color: #fff;padding: 5px 20px;border-radius: 4px;width: 110px;display: flex;justify-content: center;align-items: center;">بسته شده</span>

            <?php
            else :
                if ( get_post_meta($ticket_id, 'respond_user_role', true) == "admin" ) { ?>
                    <span class="ticket_status" style="background: #02ae02;color: #fff;padding: 5px 20px;border-radius: 4px;width: 110px;display: flex;justify-content: center;align-items: center;">پاسخ داده شده</span>

                    <?php
                } elseif ( get_post_meta($ticket_id, 'respond_user_role', true) == "user" && get_post_meta($ticket_id, 'admin_seen', true) ) { ?>

                    <span class="ticket_status" style="background: #ffb326;color: #fff;padding: 5px 20px;border-radius: 4px;width: 110px;display: flex;justify-content: center;align-items: center;">در حال بررسی</span>

                    <?php
                } else { ?>
                    <span class="ticket_status" style="background: #c90303;color: #fff;padding: 5px 20px;border-radius: 4px;width: 110px;display: flex;justify-content: center;align-items: center;">باز</span>
                    <?php
                }

            endif;

            break;

        case 'last_message' :
            $last_msg = end($messages)['body'];

            foreach (explode(PHP_EOL, $last_msg) as $msg)
                echo !empty($msg) ? $msg : '<br/>';

            break;

        case 'last_message_date' :

            $date = end($messages)['date'];
            echo date("H:i", $date) . " | " . jdate("Y/m/d", $date);
            break;
    }
}
