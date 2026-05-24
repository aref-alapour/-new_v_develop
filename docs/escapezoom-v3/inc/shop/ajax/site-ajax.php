<?php
/** lines 6079-6489 → shop/ajax/site-ajax.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_ez_site_ajax_handler',        'ez_site_ajax_handler_callback' );
add_action( 'wp_ajax_nopriv_ez_site_ajax_handler', 'ez_site_ajax_handler_callback' );
function ez_site_ajax_handler_callback() {
    global $wpdb;

    $user_id = get_current_user_id();
    //=====================/
    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce'))
        wp_send_json('');
    //=====================/
    if (isset($_POST["_checkout_sessions_"])) :

        if ( isset( $_POST['book'] ) || !empty( $_POST['book'] )  ) {

            $_SESSION['book']       = $_POST['book'];
            $_SESSION['quantity']   = $_POST['quantity'];
            $_SESSION['product_id'] = $_POST['product_id'];
            $_SESSION['user_id']    = $_POST['user_id'];
            $_SESSION['c_time']     = $_POST['c_time'];

            wp_send_json(true);
        }

        wp_send_json(false);

    endif;
    //=====================/
    if (isset($_POST["_product_comment_ajax_"])) :
        ob_start();

        $product_id     = $_POST['product_id'];
        $post_per_page  = $_POST['limit'];
        $page           = (int)$_POST['page'];

        $args = array (
            'post__in'  => $product_id,
            'number'    => $post_per_page,
            'offset'    => ($page - 1) * $post_per_page,
        );

        $comments_query = new WP_Comment_Query;
        $comments       = $comments_query->query($args);

        if ($comments) {
            foreach ($comments as $comment) {
                $c_author   = $comment->comment_author;
                $comment_id = $comment->comment_ID;

                $cld_like_count     = get_comment_meta($comment_id, "cld_like_count", true);
                $cld_dislike_count  = get_comment_meta($comment_id, "cld_dislike_count", true);
                $cm_rating          = get_comment_meta($comment_id, "rating", true);
                $comment_rating     = get_comment_meta($comment_id, "comment_rating", true);

                if ( !is_array($comment_rating) ) {
                    $comment_rating = str_replace("}", "", $comment_rating);
                    $comment_rating = explode('i:', $comment_rating);
                    array_shift($comment_rating);

                    $arr = [];
                    foreach ( $comment_rating as $elm ) {
                        $elm = explode(';s:', $elm);
                        $arr[$elm[0]] = (int) filter_var(explode(':', $elm[1])[1], FILTER_SANITIZE_NUMBER_INT);
                    }
                    $comment_rating = $arr;
                }

                $likefinal = (int)$cld_like_count - (int)$cld_dislike_count;

                if ($comment->comment_parent == 0 || $comment->comment_parent == "0" || 1)
                    if ($comment->comment_approved == 1): ?>
                        <?php if ($comment->comment_parent == 0 || 1): ?>
                            <li class="listing-item"
                                data-status="<?php echo $comment->comment_parent; ?>"
                                data-cm-id="<?php echo $comment_id; ?>"
                                data-cm-score="<?php if ($_GET['commentby'] == 'date' || $_GET['commentby'] == ''): ?> <?php echo $comment_id; ?> <?php else: ?> <?php echo $likefinal; ?>.<?php echo $cld_dislike_count; ?> <?php endif ?>">

                                <?php
                                switch ($cm_rating) {
                                    case 3:
                                        echo "<i class='face-icon face-icon-normal desktop-show'></i>";
                                        break;

                                    case  2:
                                        echo "<i class='face-icon face-icon-sad desktop-show'></i>";
                                        break;

                                    case  1:
                                        echo "<i class='face-icon face-icon-sad desktop-show'></i>";
                                        break;

                                    default:
                                        echo "<i class='face-icon face-icon-smile desktop-show'></i>";
                                        break;
                                }
                                ?>
                                <span class="fw-bold comment_details_wrapper"><?php if (is_numeric($c_author)) {
                                        $phone_mask = substr("$c_author", 3, 5);
                                        echo '<span class="d-block ltr tar">' . str_replace("$phone_mask", "×××××", "$c_author") . '</span>';
                                        echo '<span class="comment_date_x">[' . wp_date('Y-m-d', strtotime($comment->comment_date)) . ']</span>';
                                    } elseif ($c_author == "" || $c_author == null) {
                                        echo '<span class="d-block ltr tar">کاربر ' . $comment->comment_ID . '</span>';
                                    } else {
                                        echo '<span class="d-block rtl tar">' . $c_author . '</span>';
                                    } ?></span>
                                <p><?php comment_text($comment_id); ?></p>
                                <?php //comments_like_dislike($comment_id);

                                $user_ebtal     = get_post_meta($product_id, 'user_ebtal', true);
                                $owner_report   = get_comment_meta($comment_id, 'owner_report');

                                if ($user_ebtal): ?>

                                    <?php
                                    if (get_current_user_id() == $user_ebtal): ?>
                                        <p class="reply-p">
                                            <?php
                                            if ( empty($owner_report) ) : // the owner hasn't reported this comment yet. so he still can report it.?>

                                                <!--                                                                        <button class="btn btn-secondary btn-sm" type="button"-->
                                                <!--                                                                                data-bs-toggle="collapse"-->
                                                <!--                                                                                data-bs-target="#commentReport---><?//= $comment_id; ?><!--">-->
                                                <!--                                                                            گزارش-->
                                                <!--                                                                        </button>-->

                                            <?php
                                            endif; ?>

                                            <button class="btn btn-primary btn-sm" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#commentReplay-<?= $comment_id; ?>"
                                                    aria-expanded="false"
                                                    aria-controls="commentReplay-<?= $comment_id; ?>">
                                                پاسخ
                                            </button>
                                        </p>

                                        <div class="collapse" id="commentReplay-<?= $comment_id; ?>">
                                            <form action="https://escapezoom.ir/comment-reply/"
                                                  method="post" enctype="multipart/form-data">
                                                <input type="hidden" id="cm_parent" name="cm_parent"
                                                       value="<?= $comment_id; ?>">
                                                <input type="hidden" id="cm_roomid" name="cm_roomid"
                                                       value="<?php echo $product_id; ?>">

                                                <textarea class="textarea-review" name="w3review"
                                                          rows="4" cols="50"></textarea>
                                                <div class="new-comment-form-col new-comment-form-col-submit submit-reply">
                                                    <button name="submit" id="wp-submit"
                                                            class="btn-default submit-reply"
                                                            type="submit">ارسال پاسخ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="collapse" id="commentReport-<?= $comment_id; ?>">
                                            <form action="" method="post" enctype="multipart/form-data">
                                                <input type="hidden" id="cm_parent" name="cm_id" value="<?= $comment_id; ?>">
                                                <input type="hidden" id="cm_roomid" name="cm_roomid" value="<?php echo$product_id; ?>">

                                                <select name="cm_report_subject">
                                                    <option name="4545">4545</option>
                                                    <option name="4646">4646</option>
                                                    <option name="4747">4747</option>
                                                    <option name="4848">4848</option>
                                                </select>

                                                <textarea class="textarea-review" name="cm_report_text" rows="4" cols="50"></textarea>
                                                <div class="new-comment-form-col new-comment-form-col-submit submit-reply">
                                                    <button name="submit" id="wp-submit" class="btn-default submit-reply" type="submit">ارسال گزارش</button>
                                                </div>
                                            </form>
                                        </div>

                                    <?php endif ?>

                                    <?php
                                    $replies = array(
                                        'status' => 'approve',
                                        'number' => '1',
                                        'parent' => $comment_id
                                    );


                                    $replies_comments = get_comments($replies);
                                    if ($replies_comments): ?>

                                        <ul class="comment-replay-ul">

                                            <?php foreach ($replies_comments as $replie_comment): ?>
                                                <?php
                                                $mm2 = $replie_comment->user_id;
                                                $user__crInfo = get_userdata($mm2);
                                                $user_cr_name = $user__crInfo->display_name;
                                                ?>
                                                <li>
                                                    <p class="py-3 tar"><span class="fw-bold">پاسخ <?= $user_cr_name; ?>:</span>
                                                        <span><?php echo $replie_comment->comment_content; ?></span>
                                                    </p>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    <?php endif ?>
                                <?php endif ?>
                            </li>
                        <?php endif ?>
                    <?php endif ?>
            <?php }
        }

        $res = ob_get_clean();

        wp_send_json($res);
    endif;
    //=====================/
    if (isset($_POST["_load_plp_days_"])) :
        ob_start();
        $product_id     = $_POST['product_id'];
        $wp_is_mobile   = $_POST['wp_is_mobile']; ?>


        <div data-time="<?php echo zr_get_todaytimestamp(); ?>" data-ezservice="<?php echo $product_id; ?>"
             class="day-itemx active-day">
            <?php
            if ($wp_is_mobile): ?>

                <a class="day-num-view" data-time="<?php echo zr_get_todaytimestamp(); ?>" data-ezservice="<?php echo $product_id; ?>" href="#">
                    <p class="month-name-view">
                        <span class='frist-day-view today'>امروز</span>
                    </p>
                </a>

            <?php
            else: ?>

                <a class="day-num-view " data-time="<?php echo zr_get_todaytimestamp(); ?>"  data-ezservice="<?php echo $product_id; ?>" href="#">
                    <span class='frist-day-view today'>امروز</span>
                </a>

            <?php
            endif ?>
        </div>

        <?php
        $x = 1;
        while ($x <= 21) {

            if ($x == 1)
                $get_d = zr_get_todaytimestamp();;

            if ($x != 1) { ?>

                <div data-time="<?php echo $get_d; ?>" data-ezservice="<?php echo $product_id; ?>"
                     class="day-itemx d-<?php echo $x; ?>">
                    <?php
                    if ($wp_is_mobile): ?>
                        <a class="day-num-view" href="#" data-ezservice="<?php echo $product_id; ?>" data-time="<?php echo $get_d; ?>">
                            <p class="month-name-view">
                                <span class='frist-day-view'><?php echo jdate('j', $get_d) ?></span>
                                <span class='mobile-m-view'><?= jdate('l', $get_d) ?></span>
                            </p>
                        </a>

                    <?php
                    else: ?>
                        <a class="day-num-view" data-time="<?php echo $get_d; ?>" data-ezservice="<?php echo $product_id; ?>" href="#">
                            <span class='frist-day-view'><?= jdate('l', $get_d) ?></span>
                            <p class="month-name-view"><?= jdate('j', $get_d) ?></p>
                            <small class="day-name-view"><?= jdate('F', $get_d) ?></small>
                        </a>
                    <?php
                    endif ?>
                </div>
            <?php }
            $get_d = $get_d + 86400;
            $x++;
        }

        $res = ob_get_clean();

        wp_send_json($res);

    endif;
    //=====================/
    if ( isset($_POST["_new_msg_"]) && isset($_POST["msg_content"]) && isset($_POST["ticket_id"]) ) :

        $ticket_id      = $_POST['ticket_id'];
        $msg            = $_POST['msg_content'];
        $user_type      = $_POST['user_type'];
//        $admin_name     = isset ($_POST['admin_name']) ? $_POST['admin_name'] : '';
//        $uploadedfile   = $_FILES['file'];

        // if there is no message neither text or media
        if ( empty($msg) && empty($uploadedfile) ) return;

        // if requested ticket is not belong to the current user
        if ( $user_type && !ticket_verify($ticket_id, $user_id) ) return;

        $messages = get_post_meta($ticket_id, 'messages', true);

        if ( !empty($msg) ) {
            $messages[] = [
                'body'          => $msg,
                'user_type'     => 'admin',
                'date'          => time(),
                'attachment'    => time(),
            ];
        }

        // upload the file and get the uploaded url
//        if ( !empty($uploadedfile) ) {
//
//            if ( in_array( $uploadedfile['type'], ["image/jpg", "image/jpeg", "application/pdf"] ) === -1 || $uploadedfile['size'] > 2000000 ) return;
//
//            if (!function_exists('wp_handle_upload'))
//                require_once(ABSPATH . 'wp-admin/includes/file.php');
//
//            add_filter( 'upload_dir', 'change_default_upload_dir_for_ticketing_files' );
//            $movefile = wp_handle_upload($uploadedfile, array('test_form' => false, 'unique_filename_callback' => 'customers_ticketing_files_folder'));
//            remove_filter( 'upload_dir', 'change_default_upload_dir_for_ticketing_files' );
//
//            if ($movefile && !isset($movefile['error'])) {
//                $uploadedfile = $movefile['url'];
//            } else {
//                wp_send_json_error($movefile['error']);
//            }
//
//            $temp = new stdClass();
//            $temp->content  = $uploadedfile;
//            $temp->type     = $user_type;
//            $temp->time     = current_time("H:i");
//
//            $messages[] = $temp;
//        }

        update_post_meta($ticket_id, 'messages', $messages);
        update_post_meta($ticket_id, 'respond_user_role', 'admin');
        update_post_meta($ticket_id, 'admin_seen', 0);
        update_post_meta($ticket_id, 'ticket_closed', 0);

        wp_update_post(array(
            'ID'            =>  $ticket_id,
            'post_status'   =>  'publish'
        ));

        add_post_meta($ticket_id, 'user_seen', 1, true);

        wp_send_json_success(true);

    endif;
    //=====================/
    if ( isset($_POST["_ticket_closed_"]) && isset($_POST["ticket_closing_state"]) && isset($_POST["ticket_id"]) ) :
        update_post_meta($_POST["ticket_id"], 'ticket_closed', $_POST["ticket_closing_state"] == 'true' ? 1 : 0);
    endif;
    //=====================/
    if ( isset($_POST["_supporting_control_"]) && isset($_POST["order_id"]) && isset($_POST["status"]) ) :

        $order_id   = $_POST['order_id'];
        $status     = $_POST['status'];

        $order = wc_get_order( $order_id );

        if ( !$order )
            return;

        $username = (wp_get_current_user())->user_login;

        $order->update_status( $status, "کاربر $username : " );
    endif;
    //=====================/
    if ( isset($_POST["_supporting_change_quantity_"]) && isset($_POST["order_id"]) && isset($_POST["new_quantity"]) ) :

        $order_id       = $_POST['order_id'];
        $new_quantity   = $_POST['new_quantity'];

        $order = wc_get_order( $order_id );

        if ( !$order )
            return;

        foreach ($order->get_items() as $item) {
            $old_quantity = $item->get_quantity();

            $item->set_quantity($new_quantity);
            $item->save();
            break;
        }

        $username = (wp_get_current_user())->user_login;

        $order->add_order_note( 'کاربر ' . $username . ' تعداد را از ' . $old_quantity . ' به ' . $new_quantity . ' تغییر داد.' );

        $order->calculate_totals();

    endif;
    //=====================/
    if ( isset($_POST["_supporting_happycall_"]) && isset($_POST["order_id"]) && isset($_POST["state"]) ) :

        $order_id   = $_POST['order_id'];
        $state      = $_POST['state'];

        if ( $state === 'true' )
            update_post_meta($order_id, 'supporting_happycall', 1);
        else
            update_post_meta($order_id, 'supporting_happycall', 0);

    endif;
    //=====================/
    //=====================/
    //=====================/
}
