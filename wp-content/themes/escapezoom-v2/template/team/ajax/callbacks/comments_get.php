<?php
global $wpdb, $wldb;

$medoo = medoo();

$status     = sanitize_text_field($_POST['status']) ?: 'all';
$product_id = sanitize_text_field($_POST['product_id']);
$term       = sanitize_text_field($_POST['term']);
$page_num   = max(1, intval(sanitize_text_field($_POST['page'] ?? 1)));

$comments_per_page  = 25;
$offset             = ($page_num - 1) * $comments_per_page;

$args = [
    "comment_type"      => "review",
    "ORDER"             => ["comment_date" => "DESC"],
];

if ($product_id)
    $args['comment_post_ID'] = $product_id;

if ($term)
    $args['comment_content[~]'] = $term;

if ($status === 'trash')
    $args['comment_approved'] = 'trash';
elseif ($status === 'moderated')
    $args['comment_approved'] = '0';
else
    $args['comment_approved'] = ['0', '1'];

$total_pages = ceil($medoo->count("wp_comments", $args) / $comments_per_page);

$args['LIMIT'] = [$offset, $comments_per_page];

$comments = $medoo->select("wp_comments", "*", $args);

if (!$comments)
    exit('<p>هیچ نتیجه ای یافت نشد!</p>'); ?>

<div class="bg-gray-100 font-bold text-[#90A1B9] text-sm font-yekan-bold rounded-t-xl px-4 py-3 border-b border-gray-200 mt-6 grid gap-x-8"
     style="grid-template-columns: 5fr 1fr 6fr 3fr 4fr 1fr 1fr">
    <div class="text-center mx-auto">نویسنده</div>
    <div class="text-center mx-auto">امتیاز</div>
    <div class="text-center mx-auto">دیدگاه</div>
    <div class="">بازی</div>
    <div class="">ارسال شده در</div>
    <div class="text-center mx-auto">تماس</div>
    <div class="text-center mx-auto">عملیات</div>
</div>
<?php
$comment_ids = array_map(
    static function ( $row ) {
        return (int) $row['comment_ID'];
    },
    $comments
);
if ( ! empty( $comment_ids ) && function_exists( 'update_meta_cache' ) ) {
    update_meta_cache( 'comment', $comment_ids );
}

$reply_by_parent = [];
if ( ! empty( $comment_ids ) ) {
    $reply_rows = $medoo->select(
        'wp_comments',
        '*',
        [
            'comment_parent'   => $comment_ids,
            'comment_type'     => 'comment',
            'comment_approved' => '1',
            'ORDER'            => [ 'comment_date' => 'DESC' ],
        ]
    );
    if ( is_array( $reply_rows ) ) {
        foreach ( $reply_rows as $reply_row ) {
            $parent_id = (int) $reply_row['comment_parent'];
            if ( ! isset( $reply_by_parent[ $parent_id ] ) ) {
                $reply_by_parent[ $parent_id ] = $reply_row;
            }
        }
    }
}

// Display name for a wp_comments row (review or reply).
$ez_crm_comment_row_author_title = static function ( array $comment_row ) {
    $wp_user = get_user_by( 'id', (int) $comment_row['user_id'] );
    if ( $wp_user && $wp_user->exists() && $wp_user->user_firstname ) {
        $author_title = $wp_user->user_firstname;
        if ( $wp_user->user_lastname ) {
            $author_title .= ' ' . $wp_user->user_lastname;
        }
        return $author_title;
    }
    if ( $wp_user && $wp_user->exists() ) {
        return $wp_user->display_name ? $wp_user->display_name : (string) $comment_row['comment_author'];
    }
    return (string) $comment_row['comment_author'];
};

foreach ($comments as $comment) :
    static $row_index = 0;
    $row_index++;
    $comment_id   = (int) $comment['comment_ID'];
    $approved_raw = isset( $comment['comment_approved'] ) ? (string) $comment['comment_approved'] : '';

    $row_inline_bg = '';
    if ( $approved_raw === '1' ) {
        $row_inline_bg = 'background-color:rgba(16,185,129,0.10);';
    } elseif ( $approved_raw === 'trash' ) {
        $row_inline_bg = 'background-color:rgba(248,113,113,0.12);';
    } elseif ( $approved_raw === '0' || $approved_raw === 'hold' ) {
        $row_inline_bg = 'background-color:rgba(251,191,36,0.14);';
    } elseif ( $row_index % 2 === 0 ) {
        $row_inline_bg = 'background-color:rgba(243,244,246,0.55);';
    }

    $row_style = 'grid-template-columns: 5fr 1fr 6fr 3fr 4fr 1fr 1fr';
    if ( $row_inline_bg !== '' ) {
        $row_style .= ';' . $row_inline_bg;
    }

    $wp_user       = get_user_by( 'id', (int) $comment['user_id'] );
    $author_title  = $ez_crm_comment_row_author_title( $comment );

    $comment_ratings_raw = get_comment_meta( $comment_id, 'comment_rating', true );
    $comment_ratings     = [];

    $pick_rate = static function ( $arr, $key ) {
        if ( ! is_array( $arr ) ) {
            return 0;
        }
        if ( isset( $arr[ $key ] ) ) {
            return (int) $arr[ $key ] / 20;
        }
        $sk = (string) $key;
        if ( isset( $arr[ $sk ] ) ) {
            return (int) $arr[ $sk ] / 20;
        }
        return 0;
    };

    if ( is_array( $comment_ratings_raw ) ) {
        $comment_ratings = [
            'acting'     => $pick_rate( $comment_ratings_raw, 1096 ),
            'puzzle'     => $pick_rate( $comment_ratings_raw, 1095 ),
            'atmosphere' => $pick_rate( $comment_ratings_raw, 1094 ),
            'creativity' => $pick_rate( $comment_ratings_raw, 1098 ),
            'staff'      => $pick_rate( $comment_ratings_raw, 1097 ),
        ];
    } else {
        $comment_ratings = [
            'acting'     => 0,
            'puzzle'     => 0,
            'atmosphere' => 0,
            'creativity' => 0,
            'staff'      => 0,
        ];
    }

    if ( $comment['comment_date'] > COMMENT_NEW_VER_TIMESTAMP ) {
        $user_level = get_comment_meta( $comment_id, 'user_level', true ) ?: 1;
    } else {
        $user_level = 1;
    }

    $uid_for_badge = (int) $comment['user_id'];
    $moj_level_const = defined( 'EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR' ) ? (int) EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR : 10;
    if ( (int) $user_level === $moj_level_const && function_exists( 'ez_get_mojavezedar_badge_display_parts' ) ) {
        $m_parts         = ez_get_mojavezedar_badge_display_parts();
        $user_color      = $m_parts['color'];
        $user_background = $m_parts['background'];
        $user_text       = $m_parts['text'];
    } elseif ( $uid_for_badge > 0 && function_exists( 'ez_user_should_show_mojavezedar_badge' ) && ez_user_should_show_mojavezedar_badge( $uid_for_badge ) && function_exists( 'ez_get_mojavezedar_badge_display_parts' ) ) {
        $m_parts         = ez_get_mojavezedar_badge_display_parts();
        $user_color      = $m_parts['color'];
        $user_background = $m_parts['background'];
        $user_text       = $m_parts['text'];
    } elseif ($user_level == 1) {
        $user_color      = '#959798';
        $user_background = '#2527281A';
        $user_text       = 'تازه وارد';
    } elseif ($user_level == 2) {
        $user_color      = '#049654';
        $user_background = '#02C96F4D';
        $user_text       = 'نوپا';
    } elseif ($user_level == 3) {
        $user_color      = '#3F7FF5';
        $user_background = '#5091FB4D';
        $user_text       = 'با تجربه';
    } else {
        $user_color      = '#FD7013';
        $user_background = '#FD701338';
        $user_text       = 'کارکشته';
    }

    $phone_display = '';
    if ( $wp_user && $wp_user->exists() && $wp_user->user_login ) {
        $phone_display = '0' . $wp_user->user_login;
    }

    $reply_row = $reply_by_parent[ $comment_id ] ?? null;
    ?>

    <div class="border-b border-[#E4EBF0]">
    <div class="grid gap-x-8 gap-y-0 px-4 py-4 pb-3 border-b-[#E8EDF1]" style="<?php echo esc_attr( $row_style ); ?>">
        <div class="flex items-start gap-2">
            <div class="font-bold text-base "><?php echo esc_html( $author_title ); ?></div>
            <span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded" style="color:<?php echo esc_attr( $user_color ); ?>;background:<?php echo esc_attr( $user_background ); ?>;"><?php echo esc_html( $user_text ); ?></span>
        </div>
        <button type="button" class="openModalLevel cursor-pointer flex gap-x-1" data-comment-id="<?= (int) $comment_id ?>" data-acting="<?= (int) ( $comment_ratings['acting'] ?? 0 ) ?>" data-puzzle="<?= (int) ( $comment_ratings['puzzle'] ?? 0 ) ?>" data-atmosphere="<?= (int) ( $comment_ratings['atmosphere'] ?? 0 ) ?>" data-creativity="<?= (int) ( $comment_ratings['creativity'] ?? 0 ) ?>" data-staff="<?= (int) ( $comment_ratings['staff'] ?? 0 ) ?>">
            <span class="font-extrabold text-base" data-subpoints='<?php echo esc_attr( wp_json_encode( $comment_ratings ) ); ?>'><?php echo esc_html( (string) get_comment_meta( $comment_id, 'rating', true ) ); ?></span>
            <svg class="w-4 h-4 text-yellow-400 mx-0 mr-1 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.028 3.176a1 1 0 00.95.69h3.347c.969 0 1.371 1.24.588 1.81l-2.708 1.967a1 1 0 00-.364 1.118l1.028 3.176c.3.921-.755 1.688-1.538 1.118L10 13.347l-2.708 1.967c-.783.57-1.838-.197-1.538-1.118l1.028-3.176a1 1 0 00-.364-1.118L3.71 8.603c-.783-.57-.38-1.81.588-1.81h3.347a1 1 0 00.95-.69l1.028-3.176z" />
            </svg>
        </button>
        <div class="leading-relaxed space-y-1 text-sm font-bold text-[#5E6B77]"><?php echo esc_html( $comment['comment_content'] ); ?></div>
        <div class="text-base font-bold"><?php echo esc_html( get_the_title( $comment['comment_post_ID'] ) ); ?></div>
        <div class="text-base font-bold text-navyBlue flex gap-4">
            <?php
            $timestamp = strtotime($comment['comment_date']);
            $time = parsidate('H:i', $timestamp, 'fa');
            $date = parsidate('Y.m.d', $timestamp, 'fa');
            echo '<span>' . esc_html( $date ) . '</span> <span>' . esc_html( $time ) . '</span>';
            ?>
        </div>

        <div class="text-base font-bold"><?php echo esc_html( $phone_display ); ?></div>

        <div class="flex justify-center items-start">
            <button type="button" class="openModal cursor-pointer hover:opacity-80 w-7 h-7 mx-auto transition"
                    data-id="<?= (int) $comment_id ?>"
                    data-approved="<?= esc_attr( $approved_raw ); ?>"
                    data-username="<?= esc_attr( $author_title ); ?>"
                    data-comment="<?= esc_attr( $comment['comment_content'] ); ?>"
                    data-acting="<?= (int) $comment_ratings['acting'] ?>" data-puzzle="<?= (int) $comment_ratings['puzzle'] ?>" data-atmosphere="<?= (int) $comment_ratings['atmosphere'] ?>" data-creativity="<?= (int) $comment_ratings['creativity'] ?>" data-staff="<?= (int) $comment_ratings['staff'] ?>">
                <svg class="mx-0" width="27" height="28" viewBox="0 0 27 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect y="0.5" width="27" height="27" rx="6" fill="#FD7013" />
                    <path d="M9.33333 10.6638H8.64667C7.39917 10.6638 6.775 10.6638 6.38833 10.2971C6 9.93293 6 9.34376 6 8.16626C6 6.98876 6 6.3996 6.3875 6.0346C6.775 5.66876 7.39917 5.66876 8.64667 5.66876H18.3525C19.6008 5.66876 20.225 5.66876 20.6125 6.0346C21 6.40043 21 6.98793 21 8.16543C21 9.34293 21 9.9321 20.6125 10.2979C20.225 10.6638 19.6008 10.6638 18.3525 10.6638H17.25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M18.5252 22.3263C18.4835 20.8979 18.6002 20.7096 18.7093 20.3929C18.8193 20.0746 19.4985 18.9471 19.7702 18.1279C20.6468 15.4796 19.976 15.0321 18.9252 14.2279C17.7218 13.3054 15.6835 12.8363 14.4127 12.9379V9.5296C14.4127 8.81043 13.7243 8.1846 12.9527 8.1846C12.181 8.1846 11.4977 8.81043 11.4977 9.5296V15.9896L9.85515 14.5913C9.41182 14.1438 8.70265 14.1846 8.18849 14.5238C8.02859 14.6295 7.90042 14.7767 7.81765 14.9496C7.58432 15.4404 7.65099 15.9954 8.01932 16.4496L8.95265 17.6538M8.95265 17.6538C9.17599 17.9238 9.40182 18.2388 9.68515 18.5971M8.95265 17.6538L9.68515 18.5971M11.4402 22.3313V21.5429C11.501 20.5738 10.621 19.7963 9.68515 18.5971M9.68515 18.5971C9.61765 18.5104 9.74849 18.6779 9.68515 18.5971ZM9.68515 18.5971L10.6077 19.7254" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    <?php
    if ( $reply_row ) {
        $reply_author_title = $ez_crm_comment_row_author_title( $reply_row );
        $reply_ts           = strtotime( $reply_row['comment_date'] );
        $reply_time         = parsidate( 'H:i', $reply_ts, 'fa' );
        $reply_date         = parsidate( 'Y.m.d', $reply_ts, 'fa' );
        ?>
    <div class="col-span-full mt-4 mb-5 mr-5 sm:mr-10 justify-self-start min-w-0">
        <div class="text-[11px] font-bold text-[#90A1B9] mb-1.5 font-yekan-bold">پاسخ مجموعه</div>
        <div
            class="rounded-xl border border-[#E4EBF0] bg-white p-3 shadow-[0_2px_10px_rgba(15,23,42,0.08)] border-r-[3px] border-r-[#FD7013]"
            role="region"
            aria-label="<?php echo esc_attr( 'پاسخ مجموعه به این دیدگاه' ); ?>"
        >
            <div class="text-xs font-bold text-navyBlue font-yekan-bold mb-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="inline-flex items-center gap-1 rounded-md bg-[#FFF4ED] px-2 py-0.5 text-[11px] text-[#C2410C]">پاسخ</span>
                <span><?php echo esc_html( $reply_author_title ); ?></span>
                <span class="text-[#90A1B9] font-normal" aria-hidden="true">·</span>
                <span class="flex gap-2 text-[#64748B]"><span><?php echo esc_html( $reply_date ); ?></span><span><?php echo esc_html( $reply_time ); ?></span></span>
            </div>
            <div class="text-sm leading-relaxed font-normal text-[#5E6B77] border-t border-dashed border-[#E8EDF1] pt-2 mt-0.5"><?php echo esc_html( $reply_row['comment_content'] ); ?></div>
        </div>
    </div>
        <?php
    }
    ?>
    </div>
    </div>

<?php endforeach; ?>

<?php if ($total_pages > 1) { ?>
    <div class="mb-9 flex w-full items-center justify-center gap-4">
        <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
            <?php
            echo paginate_links([
                'mid_size'  => 1,
                'base'      => get_pagenum_link(1) . '%_%',
                'format'    => '?page=%#%',
                'current'   => max(1, $page_num),
                'total'     => $total_pages,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
            ]); ?>
        </div>
    </div>
<?php } ?>
