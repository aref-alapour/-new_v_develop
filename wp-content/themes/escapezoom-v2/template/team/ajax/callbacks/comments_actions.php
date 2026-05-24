<?php

$operation  = sanitize_text_field( $_POST['operation'] );
$comment_id = intval( $_POST['comment_id'] );

/**
 * دلیل عملیات از POST: action_reason یا edit_reason (ویرایش).
 */
function ez_team_comments_resolve_action_reason() {
    if ( isset( $_POST['action_reason'] ) && is_string( $_POST['action_reason'] ) ) {
        $r = sanitize_textarea_field( wp_unslash( $_POST['action_reason'] ) );
        if ( $r !== '' ) {
            return $r;
        }
    }
    if ( isset( $_POST['edit_reason'] ) && is_string( $_POST['edit_reason'] ) ) {
        return sanitize_textarea_field( wp_unslash( $_POST['edit_reason'] ) );
    }
    return '';
}

/**
 * اطلاعات سفارش/پیامک؛ بدون fatal اگر سفارشی نبود.
 *
 * @return object{order_id: int, player_name: string, product_title: string, phone: string}
 */
function get_comment_user_info( $comment_id ) {

    $user_id       = 0;
    $product_title = '';
    $comment       = get_comment( $comment_id );
    if ( $comment ) {
        $user_id = intval( $comment->user_id );
        $product_title = get_the_title( $comment->comment_post_ID );
    }

    $obj                = new stdClass();
    $obj->order_id      = 0;
    $obj->player_name   = '';
    $obj->product_title = $product_title ? $product_title : '';
    $obj->phone         = '';

    if ( $user_id <= 0 ) {
        return $obj;
    }

    $customer_orders = get_posts(
        array(
            'numberposts' => 1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $user_id,
            'post_type'   => 'shop_order',
            'post_status' => array( 'wc-partially-paid', 'wc-walletx', 'wc-completed' ),
            'orderby'     => 'ID',
            'order'       => 'DESC',
        )
    );

    if ( empty( $customer_orders ) ) {
        return $obj;
    }

    $order_id = (int) $customer_orders[0]->ID;
    $order    = wc_get_order( $order_id );
    if ( ! $order ) {
        return $obj;
    }

    $obj->order_id    = $order_id;
    $obj->player_name = $order->get_billing_first_name();
    $obj->phone       = $order->get_billing_phone();

    return $obj;
}

if ( $operation === 'approve_actions' ) {
    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        return;
    }

    $action_reason = ez_team_comments_resolve_action_reason();
    if ( $action_reason === '' ) {
        wp_send_json_error(
            array(
                'message' => 'وارد کردن دلیل عملیات الزامی است.',
            )
        );
    }

    $old_approved = $comment->comment_approved;
    $approve_type = sanitize_text_field( $_POST['approve_type'] );

    wp_set_comment_status( $comment_id, $approve_type );

    if ( $approve_type === 'approve' && function_exists( 'ez_order_satisfaction_sync_comment_effect' ) ) {
        $c_ap = get_comment( $comment_id );
        if ( $c_ap && $c_ap->comment_type === 'review' && (int) $c_ap->comment_post_ID > 0 && get_post_type( (int) $c_ap->comment_post_ID ) === 'product' ) {
            ez_order_satisfaction_sync_comment_effect( $comment_id, 'crm_comment_approve' );
        }
    }

    $new_approved = ( $approve_type === 'approve' ) ? '1' : '0';
    $is_approve   = ( $approve_type === 'approve' );

    $details = ez_crm_comment_audit_build_status_transition_text( $old_approved, $new_approved );

    $actor_row = ez_crm_comment_audit_row_from_comment( $comment );
    ez_crm_comment_audit_insert(
        array_merge(
            $actor_row,
            array(
                'action'          => $is_approve ? 'approve' : 'hold',
                'approve_subtype' => $approve_type,
                'reason'          => $action_reason,
                'details'         => $details,
            )
        )
    );

    if ( $approve_type === 'approve' ) {
        $comment_user_info = get_comment_user_info( $comment_id );
        $order_id          = $comment_user_info->order_id;
        $player_name       = $comment_user_info->player_name;
        $product_title     = $comment_user_info->product_title;
        $phone             = $comment_user_info->phone;

        if ( $phone && ! get_comment_meta( $comment_id, 'ez_comment_approve_sms_done', true ) ) {
            if ( add_comment_meta( $comment_id, 'ez_comment_approve_sms_done', time(), true ) ) {
                $text = "$player_name;$product_title";
                //add_to_sms_queue( 434802, $phone, $text, $order_id, 'comment_action' );
            }
        }
    }
} elseif ( $operation === 'trash' ) {
    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        return;
    }

    $action_reason = ez_team_comments_resolve_action_reason();
    if ( $action_reason === '' ) {
        wp_send_json_error(
            array(
                'message' => 'وارد کردن دلیل عملیات الزامی است.',
            )
        );
    }

    $old_approved   = $comment->comment_approved;
    $radio_reason   = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
    $delete_meta    = $radio_reason !== '' ? $radio_reason . ' | ' . $action_reason : $action_reason;

    update_comment_meta( $comment_id, 'delete_reason', $delete_meta );
    wp_trash_comment( $comment_id );

    $actor_row = ez_crm_comment_audit_row_from_comment( $comment );
    $details   = ez_crm_comment_audit_build_status_transition_text( $old_approved, 'trash' );
    if ( $radio_reason !== '' ) {
        $details .= "\nدسته حذف (فرم): " . $radio_reason;
    }

    ez_crm_comment_audit_insert(
        array_merge(
            $actor_row,
            array(
                'action'  => 'trash',
                'reason'  => $action_reason,
                'details' => $details,
            )
        )
    );
} elseif ( $operation === 'edit' ) {
    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        return;
    }

    $edit_reason = ez_team_comments_resolve_action_reason();
    if ( $edit_reason === '' ) {
        wp_send_json_error(
            array(
                'message' => 'وارد کردن دلیل ویرایش الزامی است.',
            )
        );
    }

    $ratings = isset( $_POST['ratings'] ) && is_array( $_POST['ratings'] ) ? $_POST['ratings'] : array();
    $clamp   = static function ( $v ) {
        $n = (int) $v;
        if ( $n < 1 ) {
            $n = 1;
        }
        if ( $n > 5 ) {
            $n = 5;
        }
        return $n;
    };
    $fazasazi = $clamp( $ratings['fazasazi'] ?? 1 );
    $moama    = $clamp( $ratings['moama'] ?? 1 );
    $tazegi   = $clamp( $ratings['tazegi'] ?? 1 );
    $act      = $clamp( $ratings['act'] ?? 1 );
    $personel = $clamp( $ratings['personel'] ?? 1 );

    $comment_data = array(
        'comment_ID'      => $comment_id,
        'comment_content' => sanitize_textarea_field( wp_unslash( $_POST['content'] ?? '' ) ),
        'comment_author'  => sanitize_text_field( wp_unslash( $_POST['author'] ?? '' ) ),
    );

    wp_update_comment( $comment_data );

    update_comment_meta(
        $comment_id,
        'comment_rating',
        array(
            '1094' => $fazasazi * 20,
            '1095' => $moama * 20,
            '1098' => $tazegi * 20,
            '1096' => $act * 20,
            '1097' => $personel * 20,
        )
    );

    update_comment_meta( $comment_id, 'rating', round( ( $fazasazi + $moama + $tazegi + $act + $personel ) / 5, 1 ) );

    $comment_after_chk = get_comment( $comment_id );
    if ( $comment_after_chk && (string) $comment_after_chk->comment_approved === '1' && function_exists( 'ez_order_satisfaction_sync_comment_effect' ) && $comment_after_chk->comment_type === 'review' && get_post_type( (int) $comment_after_chk->comment_post_ID ) === 'product' ) {
        ez_order_satisfaction_sync_comment_effect( $comment_id, 'crm_comment_edit' );
    }

    $comment_after = get_comment( $comment_id );
    $details       = $comment_after
        ? ez_crm_comment_audit_build_edit_details_text( $comment, $comment_after )
        : ez_crm_comment_audit_build_edit_details_text( $comment, $comment );

    $actor_row = ez_crm_comment_audit_row_from_comment( $comment );
    ez_crm_comment_audit_insert(
        array_merge(
            $actor_row,
            array(
                'action'  => 'edit',
                'reason'  => $edit_reason,
                'details' => $details,
            )
        )
    );

    $comment_user_info = get_comment_user_info( $comment_id );
    $order_id          = $comment_user_info->order_id;
    $player_name       = $comment_user_info->player_name;
    $product_title     = $comment_user_info->product_title;
    $phone             = $comment_user_info->phone;

    if ( $phone && ! get_comment_meta( $comment_id, 'ez_comment_edit_sms_done', true ) ) {
        if ( add_comment_meta( $comment_id, 'ez_comment_edit_sms_done', time(), true ) ) {
            $text = "$player_name;$product_title";
           // add_to_sms_queue( 434805, $phone, $text, $order_id, 'comment_action' );
        }
    }
}
