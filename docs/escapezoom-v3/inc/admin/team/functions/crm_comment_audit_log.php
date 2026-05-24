<?php
/**
 * CRM audit log for team comment moderation (team/comments).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @return string Table name with prefix.
 */
function ez_crm_comment_audit_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'ez_crm_comment_audit';
}

/**
 * Rating meta keys => Persian labels (same as CRM selects / comments_actions).
 *
 * @return array<string, string>
 */
function ez_crm_comment_audit_rating_param_labels() {
    return array(
        '1094' => 'فضاسازی',
        '1095' => 'کیفیت معما',
        '1098' => 'تازگی و خلاقیت',
        '1096' => 'بازیگردانی‌واکت',
        '1097' => 'برخورد پرسنل',
    );
}

/**
 * @param string $approved comment_approved raw value.
 */
function ez_crm_comment_audit_format_approved_status( $approved ) {
    $map = array(
        '1'     => 'تایید / منتشر شده',
        '0'     => 'عدم نمایش / در انتظار',
        'hold'  => 'در انتظار بررسی',
        'spam'  => 'هرزنامه',
        'trash' => 'سطل زباله',
    );
    return isset( $map[ $approved ] ) ? $map[ $approved ] : (string) $approved;
}

/**
 * @param mixed $comment_rating comment_rating meta.
 * @return string[]
 */
function ez_crm_comment_audit_meta_to_display_score_lines( $comment_rating ) {
    $labels = ez_crm_comment_audit_rating_param_labels();
    $lines  = array();
    if ( ! is_array( $comment_rating ) ) {
        foreach ( $labels as $label ) {
            $lines[] = $label . ': —';
        }
        return $lines;
    }
    foreach ( $labels as $key => $label ) {
        $raw = null;
        if ( isset( $comment_rating[ $key ] ) ) {
            $raw = $comment_rating[ $key ];
        } elseif ( isset( $comment_rating[ (string) $key ] ) ) {
            $raw = $comment_rating[ (string) $key ];
        }
        if ( $raw === null || $raw === '' ) {
            $lines[] = $label . ': —';
            continue;
        }
        $n = (int) round( (float) $raw / 20 );
        if ( $n < 1 ) {
            $n = 1;
        }
        if ( $n > 5 ) {
            $n = 5;
        }
        $lines[] = $label . ': ' . $n;
    }
    return $lines;
}

/**
 * دو خط وضعیت comment_approved قبل و بعد (برای لاگ انتشار/عدم‌نمایش/حذف).
 *
 * @param string|int $old_approved
 * @param string|int $new_approved
 */
function ez_crm_comment_audit_build_status_transition_text( $old_approved, $new_approved ) {
    $old_raw = (string) $old_approved;
    $new_raw = (string) $new_approved;
    return 'وضعیت قبلی (comment_approved): ' . $old_raw . ' — ' . ez_crm_comment_audit_format_approved_status( $old_raw ) . "\n"
        . 'وضعیت جدید (comment_approved): ' . $new_raw . ' — ' . ez_crm_comment_audit_format_approved_status( $new_raw );
}

/**
 * فقط متن کامنت و امتیازها (بدون شناسه، محصول، کاربر، تاریخ).
 *
 * @param WP_Comment|null $comment
 */
function ez_crm_comment_audit_build_comment_text_and_scores_text( $comment ) {
    if ( ! $comment || ! ( $comment instanceof WP_Comment ) ) {
        return '';
    }
    $rating_meta = get_comment_meta( $comment->comment_ID, 'comment_rating', true );
    $avg_meta    = get_comment_meta( $comment->comment_ID, 'rating', true );
    $score_lines = ez_crm_comment_audit_meta_to_display_score_lines( $rating_meta );
    $out         = "متن کامنت:\n"
        . $comment->comment_content . "\n\n"
        . "امتیازها (۱ تا ۵؛ برچسب پارامترها):\n"
        . implode( "\n", $score_lines );
    if ( $avg_meta !== '' && $avg_meta !== null ) {
        $out .= "\n\nمیانگین متا (rating): " . $avg_meta;
    }
    return $out;
}

/**
 * متن details برای عملیات ویرایش: وضعیت قبل/بعد + بلوک قبل/بعد متن و امتیاز.
 *
 * @param WP_Comment|null $comment_before
 * @param WP_Comment|null $comment_after
 */
function ez_crm_comment_audit_build_edit_details_text( $comment_before, $comment_after ) {
    if ( ! $comment_before || ! ( $comment_before instanceof WP_Comment ) ) {
        return '';
    }
    if ( ! $comment_after || ! ( $comment_after instanceof WP_Comment ) ) {
        $comment_after = $comment_before;
    }
    $old_ap = (string) $comment_before->comment_approved;
    $new_ap = (string) $comment_after->comment_approved;
    $header  = 'وضعیت قبلی (comment_approved): ' . $old_ap . ' — ' . ez_crm_comment_audit_format_approved_status( $old_ap ) . "\n";
    $header .= 'وضعیت جدید (comment_approved): ' . $new_ap . ' — ' . ez_crm_comment_audit_format_approved_status( $new_ap );
    return $header . "\n\n"
        . "--- قبل از ویرایش ---\n"
        . ez_crm_comment_audit_build_comment_text_and_scores_text( $comment_before ) . "\n\n"
        . "--- بعد از ویرایش ---\n"
        . ez_crm_comment_audit_build_comment_text_and_scores_text( $comment_after );
}

/**
 * @return array{actor_user_id: int, actor_user_login: string, actor_display_name: string}
 */
function ez_crm_comment_audit_actor_context() {
    $u = wp_get_current_user();
    return array(
        'actor_user_id'      => (int) $u->ID,
        'actor_user_login'   => (string) $u->user_login,
        'actor_display_name' => (string) $u->display_name,
    );
}

/**
 * @param array<string, mixed> $args
 */
function ez_crm_comment_audit_insert( $args ) {
    global $wpdb;
    $defaults = array(
        'comment_id'          => 0,
        'product_id'          => 0,
        'product_title'       => '',
        'comment_user_id'     => 0,
        'comment_author_name' => '',
        'actor_user_id'       => 0,
        'actor_user_login'    => '',
        'actor_display_name'  => '',
        'action'              => '',
        'approve_subtype'     => '',
        'comment_created_at'  => null,
        'operated_at'         => current_time( 'mysql' ),
        'reason'              => '',
        'details'             => '',
    );
    $args  = wp_parse_args( $args, $defaults );
    $table = ez_crm_comment_audit_table_name();
    $wpdb->insert(
        $table,
        array(
            'comment_id'          => (int) $args['comment_id'],
            'product_id'          => (int) $args['product_id'],
            'product_title'       => mb_substr( (string) $args['product_title'], 0, 500 ),
            'comment_user_id'     => (int) $args['comment_user_id'],
            'comment_author_name' => mb_substr( (string) $args['comment_author_name'], 0, 255 ),
            'actor_user_id'       => (int) $args['actor_user_id'],
            'actor_user_login'    => mb_substr( (string) $args['actor_user_login'], 0, 60 ),
            'actor_display_name'  => mb_substr( (string) $args['actor_display_name'], 0, 255 ),
            'action'              => mb_substr( (string) $args['action'], 0, 32 ),
            'approve_subtype'     => mb_substr( (string) $args['approve_subtype'], 0, 32 ),
            'comment_created_at'  => $args['comment_created_at'],
            'operated_at'         => $args['operated_at'],
            'reason'              => $args['reason'],
            'details'             => $args['details'],
        ),
        array( '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
    );
}

function ez_crm_comment_audit_install() {
    global $wpdb;
    $table   = ez_crm_comment_audit_table_name();
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        comment_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL DEFAULT 0,
        product_title varchar(500) NOT NULL DEFAULT '',
        comment_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
        comment_author_name varchar(255) NOT NULL DEFAULT '',
        actor_user_id bigint(20) unsigned NOT NULL,
        actor_user_login varchar(60) NOT NULL DEFAULT '',
        actor_display_name varchar(255) NOT NULL DEFAULT '',
        action varchar(32) NOT NULL DEFAULT '',
        approve_subtype varchar(32) NOT NULL DEFAULT '',
        comment_created_at datetime DEFAULT NULL,
        operated_at datetime NOT NULL,
        reason longtext,
        details longtext,
        PRIMARY KEY  (id),
        KEY comment_id (comment_id),
        KEY operated_at (operated_at),
        KEY actor_user_id (actor_user_id),
        KEY action (action)
    ) $charset;";
    dbDelta( $sql );
}

add_action( 'init', 'ez_crm_comment_audit_maybe_install', 30 );
function ez_crm_comment_audit_maybe_install() {
    if ( get_option( 'ez_crm_comment_audit_db_version', '' ) === '1' ) {
        return;
    }
    ez_crm_comment_audit_install();
    update_option( 'ez_crm_comment_audit_db_version', '1' );
}

/**
 * Shared row prefix for ez_crm_comment_audit_insert from a comment object.
 *
 * @param WP_Comment $comment
 * @return array<string, mixed>
 */
function ez_crm_comment_audit_row_from_comment( $comment ) {
    $actor = ez_crm_comment_audit_actor_context();
    $pid   = (int) $comment->comment_post_ID;
    return array_merge(
        $actor,
        array(
            'comment_id'          => (int) $comment->comment_ID,
            'product_id'          => $pid,
            'product_title'       => $pid ? get_the_title( $pid ) : '',
            'comment_user_id'     => (int) $comment->user_id,
            'comment_author_name' => (string) $comment->comment_author,
            'comment_created_at'  => $comment->comment_date,
        )
    );
}

/**
 * Shared row prefix for system-generated actions on a comment.
 *
 * @param WP_Comment $comment
 * @return array<string, mixed>
 */
function ez_crm_comment_audit_row_from_comment_system( $comment ) {
    $row = ez_crm_comment_audit_row_from_comment( $comment );
    $row['actor_user_id']      = 0;
    $row['actor_user_login']   = 'system';
    $row['actor_display_name'] = 'سیستم';
    return $row;
}

/**
 * جستجوی کاربر برای فیلتر گزارش: شناسه، نام، نام خانوادگی، موبایل (ورود/متا).
 *
 * @param string $query
 * @return int[]|null null یعنی بدون فیلتر (رشته خالی).
 */
function ez_crm_comment_audit_resolve_user_ids( $query ) {
    global $wpdb;

    $q = trim( (string) $query );
    if ( $q === '' ) {
        return null;
    }

    if ( preg_match( '/^\d+$/', $q ) ) {
        $uid = (int) $q;
        if ( $uid > 0 && get_userdata( $uid ) ) {
            return array( $uid );
        }
        return array();
    }

    $like = '%' . $wpdb->esc_like( $q ) . '%';
    $uids = array();

    $user_cols = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->users} WHERE user_login LIKE %s OR user_email LIKE %s OR display_name LIKE %s",
            $like,
            $like,
            $like
        )
    );
    foreach ( $user_cols as $id ) {
        $uids[ (int) $id ] = true;
    }

    $meta_cols = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key IN ('first_name','last_name','billing_phone','nickname') AND meta_value LIKE %s",
            $like
        )
    );
    foreach ( $meta_cols as $id ) {
        $uids[ (int) $id ] = true;
    }

    return array_keys( $uids );
}

/**
 * Resolve comment author filter strictly by ID/name/lastname/nickname.
 * (No email/billing_phone lookup to reduce noisy matches.)
 *
 * @param string $query
 * @return int[]|null null means no filter.
 */
function ez_crm_comment_audit_resolve_comment_author_ids( $query ) {
    global $wpdb;

    $q = trim( (string) $query );
    if ( $q === '' ) {
        return null;
    }

    if ( preg_match( '/^\d+$/', $q ) ) {
        $uid = (int) $q;
        if ( $uid > 0 && get_userdata( $uid ) ) {
            return array( $uid );
        }
        return array();
    }

    $like = '%' . $wpdb->esc_like( $q ) . '%';
    $uids = array();

    $user_cols = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s",
            $like
        )
    );
    foreach ( $user_cols as $id ) {
        $uids[ (int) $id ] = true;
    }

    $meta_cols = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key IN ('first_name','last_name','nickname') AND meta_value LIKE %s",
            $like
        )
    );
    foreach ( $meta_cols as $id ) {
        $uids[ (int) $id ] = true;
    }

    return array_keys( $uids );
}

/**
 * @param string|null $mysql_datetime
 */
function ez_crm_comment_audit_format_jalali_datetime( $mysql_datetime ) {
    if ( $mysql_datetime === null || $mysql_datetime === '' || $mysql_datetime === '0000-00-00 00:00:00' ) {
        return '—';
    }
    $ts = strtotime( (string) $mysql_datetime );
    if ( ! $ts ) {
        return '—';
    }
    if ( function_exists( 'parsidate' ) ) {
        return parsidate( 'Y/m/d', $ts, 'fa' ) . ' ' . parsidate( 'H:i', $ts, 'fa' );
    }
    return (string) $mysql_datetime;
}

/**
 * استایل inline برای بج نوع عملیات در جدول گزارش.
 *
 * @param string $action
 */
function ez_crm_comment_audit_action_badge_style( $action ) {
    $map = array(
        'approve' => 'background-color:rgba(16,185,129,0.2);color:#047857;',
        'hold'    => 'background-color:rgba(251,191,36,0.25);color:#92400e;',
        'auto_hold' => 'background-color:rgba(245,158,11,0.25);color:#9a3412;',
        'trash'   => 'background-color:rgba(248,113,113,0.22);color:#b91c1c;',
        'edit'    => 'background-color:rgba(59,130,246,0.2);color:#1d4ed8;',
    );
    return isset( $map[ $action ] ) ? $map[ $action ] : 'background-color:rgba(148,163,184,0.25);color:#334155;';
}

/**
 * افزودن شرط comment_user_id یا actor_user_id بر اساس آرایه شناسه‌ها.
 *
 * @param string               $column comment_user_id|actor_user_id
 * @param int[]                $ids
 * @param array<string>        $w
 * @param array<int|string>    $p
 */
function ez_crm_comment_audit_where_user_ids( $column, array $ids, array &$w, array &$p ) {
    if ( ! in_array( $column, array( 'comment_user_id', 'actor_user_id' ), true ) ) {
        return;
    }
    if ( empty( $ids ) ) {
        $w[] = '0=1';
        return;
    }
    if ( count( $ids ) === 1 ) {
        $w[] = $column . ' = %d';
        $p[] = (int) $ids[0];
        return;
    }
    $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
    $w[]          = $column . ' IN (' . $placeholders . ')';
    foreach ( $ids as $id ) {
        $p[] = (int) $id;
    }
}
