<?php
/**
 * ticketing_messages_metabox_content_function
 *
 * توابع: ticketing_messages_metabox_content_function
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5543-5801)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ticketing_messages_metabox_content_function( $post ) { // function of content of added metabox
    wp_nonce_field( plugin_basename( __FILE__ ), 'program_price_box_content_nonce' );

    $ticket_id  = $post->ID;
    $messages   = get_post_meta($ticket_id, 'messages', true); ?>

    <div class="chat_content scrollbar" data-simplebar>

        <?php
        foreach ( $messages as $message ) :  ?>

            <div class="chat_date"><span><?php echo jdate("Y/m/d", $message['date']) ?></span></div>

            <div class="chat_message <?php echo $message['user_type']; ?>">
                <div class="msg_content">
                    <div class="bordered_content">
                        <?php
                        foreach ( explode(PHP_EOL, $message['body']) as $msg ) { ?>
                            <p><?php echo !empty($msg) ? $msg : '<br/>' ?></p>
                            <?php
                        } ?>
                    </div>
                    <div class="date"><?php echo date("H:i", $message['date']); ?></div>
                </div>
            </div>

        <?php
        endforeach; ?>

    </div>
    <?php
    $user = wp_get_current_user(); ?>

    <div class="send_message">
        <input type="hidden" id="ticket_id" value="<?php echo $ticket_id ?>">
        <input type="hidden" id="today" value="<?php echo parsidate("Y-m-d", 'now', "eng") ?>">
        <input type="hidden" id="admin_name" value="<?php echo $user->display_name ?>">

        <div class="input_group">
            <textarea id="new_message" cols="10" rows="1" placeholder="پیام مدنظر خودتون رو بنویسید .."></textarea>
        </div>

        <div class="button_group">
            <button type="button" id="new_message_submit" class="button green mb-0">ارسال</button>
        </div>

    </div>

    <style>
        .chat_date {
            position       : relative;
            display        : flex;
            align-items    : center;
            justify-content: center;
            margin         : 20px 0;
        }
        .chat_date:first-child {
            margin-top: 0;
        }
        .chat_date span {
            background: #fff;
            padding   : 0 10px;
            z-index   : 1;
            color     : #b2b3be;
        }
        .chat_date:before {
            content   : "";
            background: #d3d4d9;
            height    : 1px;
            width     : 100%;
            position  : absolute;
            right     : 0;
            top       : 0;
            bottom    : 0;
            margin    : auto;
            z-index   : 0;
        }
        .chat_message {
            display: flex;
            margin : 0 0 5px 0;
        }
        .chat_message.user {
            justify-content: flex-start;
        }
        .chat_message.admin {
            justify-content: flex-end;
        }
        .chat_message .msg_content {
            max-width: 80%;
        }
        .chat_message .msg_content img:not(:last-child) {
            margin: 0 0 5px 0;
        }
        .chat_message .msg_content img {
            border-radius: 15px;
            overflow     : hidden;
        }
        .chat_message .msg_content .bordered_content {
            padding  : 15px;
            font-size: 11pt;
        }
        .chat_message.user .msg_content .bordered_content {
            border       : 1px solid #e5ebf8;
            border-radius: 20px 20px 0 20px;
            background   : #fff;
        }
        .chat_message.admin .msg_content .bordered_content {
            background: #0040ff;
            border-radius: 20px 20px 20px 0;
            color: #fff;
        }
        .chat_message .msg_content .bordered_content p {
            margin: 0 0 5px 0;
        }

        .chat_message .msg_content .date {
            font-size: 9pt;
            color: #acaeb9;
            padding: 3px 15px 0;
        }
        .chat_message.user .msg_content .date {
            text-align: left;
        }
        .send_message {
            display      : flex;
            align-items  : flex-start;
            background   : #F2F2FB;
            padding      : 20px;
            border-radius: 20px;
        }
        .send_message .button_group {
            display: flex;

        }
        .send_message .button_group button {
            padding: 0;
            width: 61px;
            height: 61px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9700c;
            border: none;
            color: #fff;
            font-weight: bold;
        }
        .send_message .button_group button:hover {
            background: #ff9548;
        }
        .send_message .input_group {
            background-color: #fff;
            display         : flex;
            align-items     : flex-start;
            margin-left     : 15px;
            flex-grow       : 1;
            padding         : 3px 10px;
            border-radius   : 10px;
            overflow        : hidden;
            width: 100%;
        }
        .send_message .input_group .attachment {
            cursor         : pointer;
            display        : flex;
            align-items    : center;
            justify-content: center;
        }
        .send_message .input_group .attachment svg {
            width      : 30px;
            height     : 30px;
            margin-left: 10px;
        }
        .send_message .input_group textarea {
            height    : 55px;
            border    : 0 none !important;
            box-shadow: none !important;
            background: transparent;
            margin    : 0;
            padding   : 10px 0;
            resize    : vertical;
            width: 100%;
        }
        .admin_ticket_last_msg_preview {
            width: 30px;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            $('body').on('click', '#new_message_submit', function () {

                var new_message = $("#new_message").val();
                var today       = $("#today").val();
                var last_date   = $('.chat_date:last span').text();

                if ( new_message ) {
                    $("#new_message").val('');

                    var dt      = new Date();
                    var time    = dt.getHours() + ":" + dt.getMinutes();

                    var msg_html = '';
                    if ( last_date !== today ) {
                        msg_html += '<div class="chat_date">';
                        msg_html +=     '<span>' + today + '</span>';
                        msg_html += '</div>';
                    }

                    msg_html += '<div class="chat_message admin">';
                    msg_html +=     '<div class="msg_content">';
                    msg_html +=         '<div class="bordered_content">';
                    msg_html +=             '<p>' + new_message + '</p>';
                    msg_html +=         '</div>';
                    msg_html +=         '<div class="date">' + time + '</div>';
                    msg_html +=     '</div>';
                    msg_html += '</div>';

                    $('.chat_content').append(msg_html);

                    $.ajax({
                        type    : 'POST',
                        url     : "<?php echo admin_url('admin-ajax.php') ?>",
                        data    : {
                            'action'        : 'ez_site_ajax_handler',
                            'nonce'         : "<?php echo wp_create_nonce('ajax-nonce') ?>",
                            '_new_msg_'     : true,
                            'msg_content'   : new_message,
                            'ticket_id'     : $("#ticket_id").val(),
                            'admin_name'    : $("#admin_name").val(),
                        },
                        dataType: "json",
                        success: function(data) {
                        },
                    });
                }
            });
            /********************************************************************************************************************************/
            $('body').on('change', '#ticket_closed', function () {

                $.ajax({
                    type    : 'POST',
                    url     : "<?php echo admin_url('admin-ajax.php') ?>",
                    data    : {
                        'action'                : 'ez_site_ajax_handler',
                        'nonce'                 : "<?php echo wp_create_nonce('ajax-nonce') ?>",
                        '_ticket_closed_'       : true,
                        'ticket_id'             : $("#ticket_id").val(),
                        'ticket_closing_state'  : $(this).prop('checked') ? true : false,
                    },
                    dataType: "json",
                    success: function(data) {

                    },
                });
            });
        });
    </script>

    <?php
}
