<?php
global $wpdb;

$user = wp_get_current_user();

$page = sanitize_text_field( $_POST['page'] ) ?: 1;

$limit = 5;

$uid              = (int) $user->ID;
$active_products  = array();
$can_view_panel   = false;

if ( current_user_can( 'administrator' ) ) {
    $can_view_panel  = true;
    $active_products = function_exists( 'ez_account_get_managed_product_ids' )
        ? ez_account_get_managed_product_ids( $uid )
        : array();
    if ( empty( $active_products ) && function_exists( 'ez_account_get_admin_legacy_product_ids' ) ) {
        $active_products = ez_account_get_admin_legacy_product_ids( $uid );
    }
} elseif ( function_exists( 'ez_user_has_role' )
    && ( ez_user_has_role( $user, 'sans_manager' ) || ez_user_has_role( $user, 'compiler' ) ) ) {
    $can_view_panel  = true;
    $active_products = ez_account_get_managed_product_ids( $uid );
} else {
    $can_view_panel = false;
}

if ( ! $can_view_panel ) { ?>
    <div class="text-[22px] font-bold lg:text-lg text-center lg:my-19 text-gray-500">
        شما به این بخش دسترسی ندارید.
    </div>
    <?php wp_die();
}

if ( empty( $active_products ) ) { ?>
    <div class="text-[22px] font-bold lg:text-lg text-center lg:my-19 text-gray-500">
        شما هیچ اتاق فعالی ندارید.
    </div>
    <?php wp_die();
}

$active_products = array_values( array_unique( array_map( 'intval', $active_products ) ) );
$in_list         = implode( ',', $active_products );

$total_comments = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_post_ID IN ({$in_list}) AND comment_approved = '1' AND comment_parent = 0"
);
$total_pages    = $total_comments > 0 ? ceil( $total_comments / $limit ) : 1;

$query = new WP_Comment_Query( [
    'post_type' => 'product',
    'post__in'  => $active_products,
    'status'    => 'approve',
    'number'    => $limit,
    'paged'     => $page,
    'parent'    => 0,
] );

$data = [];

if ( ! empty( $query->comments ) ) {
    foreach ( $query->comments as $comment ) {

        $author      = $comment->comment_author;
        $author_user = get_user_by( 'login', $author );
        $id          = ( $author_user && $author_user->exists() ) ? (int) $author_user->ID : 0;

        if ( ctype_digit( $author ) ) {
            $author = str_replace( substr( $author, 3, 5 ), '*****', $author );
        }

        $rate_meta = get_comment_meta( $comment->comment_ID, 'comment_rating', true );
        $rate      = is_array( $rate_meta ) ? $rate_meta : array();
        $r         = static function ( $arr, $key ) {
            if ( isset( $arr[ $key ] ) ) {
                return (int) $arr[ $key ];
            }
            $sk = (string) $key;
            return isset( $arr[ $sk ] ) ? (int) $arr[ $sk ] : 0;
        };

        $replies = get_comments( [
            'parent' => $comment->comment_ID,
            'status' => 'approve',
            'type'   => 'comment',
        ] );

        $item = [
            'id'        => $id,
            'comment'   => $comment,
            'rating'    => [
                'فضاسازی'          => $r( $rate, 1094 ) / 20,
                'کیفیت معما'       => $r( $rate, 1095 ) / 20,
                'تازگی و خلاقیت'   => $r( $rate, 1098 ) / 20,
                'بازیگردانی و اکت' => $r( $rate, 1096 ) / 20,
                'برخورد پرسنل'     => $r( $rate, 1097 ) / 20,
            ],
            'product'   => get_post( $comment->comment_post_ID ),
            'reported'  => ! empty( get_comment_meta( $comment->comment_post_ID, 'report_reason', true ) ),
            'sans_time' => '',
            'replies'   => $replies,
        ];

        if ( ctype_digit( $comment->comment_author ) ) {
            $item['comment']->comment_author = str_replace( substr( $comment->comment_author, 3, 5 ), "×××××", $comment->comment_author );
        }

        $item['comment']->comment_author_level = "کارکشته";

        $data[] = $item;
    }
}

if ( ! empty( $data ) ) :

    foreach ( $data as $comment ) : ?>

        <div class="hidden lg:flex flex-col rounded-[14px] bg-[#F9F9F9] mb-[30px]" style="border: 1px solid #dbe2ea">
            <div class="flex flex-row items-center justify-between px-[30px] py-4">
                <div class="flex justify-center items-center">
                    <div class="flex flex-row items-center gap-[8px]">
                        <span><?php
                        $bid = (int) $comment['id'];
                        if ( $bid > 0 ) {
                            $ud = get_userdata( $bid );
                            echo $ud ? esc_html( $ud->display_name ) : esc_html( $comment['comment']->comment_author );
                        } else {
                            echo esc_html( $comment['comment']->comment_author );
                        }
                        ?></span>
                        <?php user_badge_by_level( $comment['id'] ); ?>
                    </div>
                    <div class="w-[4px] h-[4px] bg-[#D5DCE1] rounded-[50%] mx-4"></div>
                    <p><?php echo jdate( "Y.m.d", strtotime( $comment['comment']->comment_date ) ) ?></p>
                </div>

                <div class="flex gap-[73px]">
                    <div class="flex justify-between items-center">
                        <p class="text-[#889BAD]">
                            بازی
                            <span class="mr-2 text-[#09192D]">
								<?php echo esc_html( $comment['product']->post_title ); ?>
                            </span>
                        </p>
                    </div>
                    <div class="flex justify-between items-center hidden">
                        <p class="text-[#889BAD]">
                            سانس
                            <span class="mr-2 text-[#09192D]">00:0 1401/01/01</span>
                        </p>
                    </div>
                </div>

            </div>

            <div class="rounded-[14px] bg-white px-[30px] pt-4 pb-[30px]" style="border: 1px solid #dbe2ea">
                <p class="text-[14px] font-bold mb-3"><?php echo $comment['comment']->comment_content ?></p>

                <?php

                $rate = 0;
                foreach ( $comment['rating'] as $label => $value ) {
                    $rate += $value;
                }

                if ( $rate !== 0 ) {
                    $rate = floor( $rate / 5 );
                } else {
                    $rate = 1;
                }

                $rate_str   = match ( (string) $rate ) {
                    '1' => 'خیلی بد',
                    '2' => 'خوب نبود',
                    '3' => 'معمولی بود',
                    '4' => 'خوب بود',
                    '5' => 'عالی بود',
                };
                $rate_color = match ( (string) $rate ) {
                    '1' => '#F21543',
                    '2' => '#FD7013',
                    '3' => '#BF9A00',
                    '4' => '#3F7FF5',
                    '5' => '#049654',
                } ?>
                <div class="flex justify-between items-center bg-[#F7FAFA] py-2 px-4 rounded-[12px]">

                    <div class="flex gap-2 items-center">
                        <p class="text-[14px] font-bold" style="color: <?php echo esc_attr( $rate_color ) ?>">
                            <?php echo esc_html( $rate_str ) ?>
                        </p>
                        <img src="<?php bloginfo( 'template_url' ); ?>/assets/images/emojis/<?php echo $rate; ?>.png" draggable="false" alt="<?php echo $rate; ?>" class="w-8 h-auto">
                    </div>

                    <div class="flex justify-between gap-[44px] ">

                        <?php foreach ( $comment['rating'] as $label => $value ) { ?>
                            <div class="flex flex-col items-center">
                                <div class="flex flex-col justify-center">
                                    <div class="flex items-center" dir="ltr">
                                        <span class="min-w-4 text-[14px]"><?php echo esc_html( $value ) ?></span>
                                        <div class="w-[61px] rounded-full bg-slate-110 dark:bg-gray-700 h-1">
                                            <div class="rounded-full bg-accent-420 h-1" style="width: <?php echo $value * 20 ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="nowrap text-[14px] font-bold lg:text-sm text-[#4E5C6D]">
									<?php echo esc_html( $label ); ?>
                                </span>
                            </div>
                        <?php } ?>

                    </div>
                </div>

                <hr class="my-[20px]"/>

                <?php if ( count( $comment['replies'] ) > 0 ) { ?>
                    <div class="flex flex-col gap-4">
                        <div class="text-black font-bold text-lg">پاسخ شما</div>
                        <div class="flex items-start gap-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14" fill="none" class="mx-0 mt-2">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.7072C5.89426 13.5197 5.99957 13.2654 5.99957 13.0002C5.99957 12.735 5.89426 12.4807 5.70679 12.2932L3.41379 10.0002H8.99979C10.8563 10.0002 12.6368 9.26272 13.9495 7.94996C15.2623 6.63721 15.9998 4.85673 15.9998 3.00021V1.00021C15.9998 0.734997 15.8944 0.480642 15.7069 0.293106C15.5194 0.10557 15.265 0.000213623 14.9998 0.000213623C14.7346 0.000213623 14.4802 0.10557 14.2927 0.293106C14.1051 0.480642 13.9998 0.734997 13.9998 1.00021V3.00021C13.9998 4.3263 13.473 5.59807 12.5353 6.53575C11.5976 7.47343 10.3259 8.00021 8.99979 8.00021H3.41379L5.70679 5.70721C5.8023 5.61497 5.87848 5.50462 5.93089 5.38262C5.9833 5.26061 6.01088 5.12939 6.01204 4.99661C6.01319 4.86384 5.98789 4.73216 5.93761 4.60926C5.88733 4.48636 5.81307 4.37471 5.71918 4.28082C5.62529 4.18693 5.51364 4.11267 5.39074 4.06239C5.26784 4.01211 5.13616 3.98681 5.00339 3.98796C4.87061 3.98912 4.73939 4.0167 4.61738 4.06911C4.49538 4.12152 4.38503 4.1977 4.29279 4.29321L0.292786 8.29321C0.105315 8.48074 0 8.73505 0 9.00021C0 9.26538 0.105315 9.51969 0.292786 9.70721L4.29279 13.7072C4.48031 13.8947 4.73462 14 4.99979 14C5.26495 14 5.51926 13.8947 5.70679 13.7072Z" fill="#889BAD"/>
                            </svg>
                            <span class="text-gray-500"><?php echo $comment['replies'][0]->comment_content; ?></span>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="flex justify-between">
                        <div class="flex">
                            <p class="reply-button text-[14px] font-bold text-[#3F7FF5] cursor-pointer flex items-center gap-2" data-target-form="<?php echo esc_attr( $comment['comment']->comment_ID ) ?>">
                                پاسخ به این دیدگاه
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none">
                                    <path d="M5.625 10.125L2.5 7L5.625 3.875" stroke="#3F7FF5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12.5 10.75V9.5C12.5 8.83696 12.2366 8.20107 11.7678 7.73223C11.2989 7.26339 10.663 7 10 7H2.5" stroke="#3F7FF5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </p>
                            <p class="close-reply-button hidden text-[14px] font-bold  text-[#3F7FF5] cursor-pointer flex items-center gap-2" data-target-form="<?php echo esc_attr( $comment['comment']->comment_ID ) ?>">
                                بستن
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="10" viewBox="0 0 11 10" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.50024 6.32601L8.81462 9.64039C8.99049 9.81626 9.22902 9.91506 9.47774 9.91506C9.72646 9.91506 9.96499 9.81626 10.1409 9.64039C10.3167 9.46452 10.4155 9.22598 10.4155 8.97726C10.4155 8.72854 10.3167 8.49001 10.1409 8.31414L6.82524 4.99976L10.1402 1.68539C10.2273 1.59831 10.2963 1.49493 10.3434 1.38117C10.3905 1.2674 10.4147 1.14548 10.4147 1.02236C10.4147 0.899231 10.3904 0.777317 10.3432 0.663576C10.2961 0.549835 10.227 0.446493 10.1399 0.359451C10.0528 0.272409 9.94947 0.203372 9.83571 0.156281C9.72194 0.10919 9.60002 0.0849678 9.47689 0.0849968C9.35377 0.0850258 9.23186 0.109306 9.11812 0.15645C9.00437 0.203595 8.90103 0.272681 8.81399 0.359764L5.50024 3.67414L2.18587 0.359764C2.09943 0.270183 1.99601 0.198714 1.88166 0.149528C1.7673 0.100341 1.6443 0.0744215 1.51982 0.0732815C1.39534 0.0721415 1.27188 0.0958038 1.15664 0.142888C1.0414 0.189972 0.936696 0.259535 0.84863 0.347517C0.760565 0.4355 0.690903 0.54014 0.643711 0.655333C0.596518 0.770525 0.572739 0.893963 0.573762 1.01844C0.574785 1.14292 0.600588 1.26595 0.649667 1.38036C0.698746 1.49476 0.770117 1.59824 0.859616 1.68476L4.17524 4.99976L0.860241 8.31476C0.770742 8.40129 0.699371 8.50477 0.650292 8.61917C0.601213 8.73357 0.57541 8.8566 0.574387 8.98108C0.573364 9.10556 0.597143 9.229 0.644336 9.34419C0.691528 9.45939 0.76119 9.56403 0.849255 9.65201C0.937321 9.73999 1.04203 9.80956 1.15726 9.85664C1.2725 9.90372 1.39596 9.92739 1.52044 9.92625C1.64492 9.92511 1.76793 9.89919 1.88228 9.85C1.99664 9.80081 2.10005 9.72934 2.18649 9.63976L5.50024 6.32601Z" fill="#3F7FF5"/>
                                </svg>
                            </p>
                        </div>
                    </div>
                    <div class="hidden mt-4" data-form="<?php echo esc_attr( $comment['comment']->comment_ID ) ?>">
                        <form method="post" class="submit-reply-form">
                            <input type="hidden" name="product" value="<?php echo esc_attr( $comment['product']->ID ); ?>">
                            <input type="hidden" name="comment" value="<?php echo esc_attr( $comment['comment']->comment_ID ); ?>">
                            <textarea name="comment_content" class="w-full min-h-[100px] rounded-[14px] border border-[#E4EBF0] p-2 resize-y" placeholder="پاسخ خود را اینجا بنویسید..."></textarea>
                            <button type="submit" class="btn rounded-[8px] px-[33px] py-2 text-white mt-2" style="box-shadow: 0 2px 0 0 #CA5608; background: #FD7013;">
                                ارسال
                            </button>
                        </form>
                    </div>
                <?php } ?>

            </div>

        </div>

        <div class="lg:hidden flex flex-col border-b pb-7 mb-7">
            <div class="flex justify-between">
                <div class="flex flex-row items-center gap-[8px]">
                    <span class="font-bold"><?php
                    $bid_m = (int) $comment['id'];
                    if ( $bid_m > 0 ) {
                        $ud_m = get_userdata( $bid_m );
                        echo $ud_m ? esc_html( $ud_m->display_name ) : esc_html( $comment['comment']->comment_author );
                    } else {
                        echo esc_html( $comment['comment']->comment_author );
                    }
                    ?></span>
                    <?php user_badge_by_level( $comment['id'] ); ?>
                </div>
                <div><?php echo jdate( "Y.m.d", strtotime( $comment['comment']->comment_date ) ) ?></div>
            </div>
            <div class="flex justify-between mb-4">
                <div class="flex flex-row items-center gap-[8px]">
                    <div style="width: 16px; height: 20px">
                        <?php echo get_the_post_thumbnail( $comment['product']->ID, 'small', [
                            'class' => 'object-fit w-full h-full rounded',
                        ] ) ?>
                    </div>
                    <span class="text-[#889BAD]">
						<?php echo esc_html( $comment['product']->post_title ); ?>
                    </span>
                </div>
            </div>

            <div class="flex flex-col bg-slate-50 rounded-xl mb-4">
                <div class="p-4 comment-content" data-full-text="<?php echo $comment['comment']->comment_content ?>">
                    <?php if ( strlen( $comment['comment']->comment_content ) > 200 ) {
                        echo substr( $comment['comment']->comment_content, 0, 200 ) . '<button type="button" class="show-more text-blue mr-2">ادامه</button>';
                    } else {
                        echo $comment['comment']->comment_content;
                    } ?>
                </div>
                <div class="flex gap-2 items-center p-4 pt-0">
                    <p class="text-[14px] font-bold" style="color: <?php echo esc_attr( $rate_color ) ?>">
                        <?php echo esc_html( $rate_str ) ?>
                    </p>
                    <img src="<?php bloginfo( 'template_url' ); ?>/assets/images/emojis/<?php echo $rate; ?>.png" draggable="false" alt="<?php echo $rate; ?>" class="w-8 h-auto">
                </div>
                <div class="flex flex-col justify-between gap-1 p-4 pt-0" style="display: none">
                    <?php foreach ( $comment['rating'] as $label => $value ) { ?>
                        <div class="flex flex-row-reverse items-center justify-between">
                            <div class="flex items-center grow" dir="ltr">
                                <span class="min-w-4 text-[14px]"><?php echo esc_html( $value ) ?></span>
                                <div class="grow rounded-full bg-slate-110 dark:bg-gray-700 h-1">
                                    <div class="rounded-full bg-accent-420 h-1" style="width: <?php echo $value * 20 ?>%"></div>
                                </div>
                            </div>
                            <span class="nowrap text-[14px] font-bold lg:text-sm w-[132px] text-[#4E5C6D]">
								<?php echo esc_html( $label ); ?>
                            </span>
                        </div>
                    <?php } ?>
                </div>
                <button type="button" class="more-details bg-black/5 border-t justify-center flex items-center gap-x-3">
                    <span>مشاهده بیشتر</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" class="mx-0 transition duration-300">
                        <path d="M9 1L5.70711 4.29289C5.31658 4.68342 4.68342 4.68342 4.29289 4.29289L1 1" stroke="#09192D" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <?php if ( count( $comment['replies'] ) > 0 ) { ?>
                <div class="flex flex-col gap-4">
                    <div class="text-black font-bold text-lg">پاسخ شما</div>
                    <div class="flex items-start gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14" fill="none" class="mx-0 mt-2">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.7072C5.89426 13.5197 5.99957 13.2654 5.99957 13.0002C5.99957 12.735 5.89426 12.4807 5.70679 12.2932L3.41379 10.0002H8.99979C10.8563 10.0002 12.6368 9.26272 13.9495 7.94996C15.2623 6.63721 15.9998 4.85673 15.9998 3.00021V1.00021C15.9998 0.734997 15.8944 0.480642 15.7069 0.293106C15.5194 0.10557 15.265 0.000213623 14.9998 0.000213623C14.7346 0.000213623 14.4802 0.10557 14.2927 0.293106C14.1051 0.480642 13.9998 0.734997 13.9998 1.00021V3.00021C13.9998 4.3263 13.473 5.59807 12.5353 6.53575C11.5976 7.47343 10.3259 8.00021 8.99979 8.00021H3.41379L5.70679 5.70721C5.8023 5.61497 5.87848 5.50462 5.93089 5.38262C5.9833 5.26061 6.01088 5.12939 6.01204 4.99661C6.01319 4.86384 5.98789 4.73216 5.93761 4.60926C5.88733 4.48636 5.81307 4.37471 5.71918 4.28082C5.62529 4.18693 5.51364 4.11267 5.39074 4.06239C5.26784 4.01211 5.13616 3.98681 5.00339 3.98796C4.87061 3.98912 4.73939 4.0167 4.61738 4.06911C4.49538 4.12152 4.38503 4.1977 4.29279 4.29321L0.292786 8.29321C0.105315 8.48074 0 8.73505 0 9.00021C0 9.26538 0.105315 9.51969 0.292786 9.70721L4.29279 13.7072C4.48031 13.8947 4.73462 14 4.99979 14C5.26495 14 5.51926 13.8947 5.70679 13.7072Z" fill="#889BAD"/>
                        </svg>
                        <span class="text-gray-500"><?php echo $comment['replies'][0]->comment_content; ?></span>
                    </div>
                </div>
            <?php } else { ?>
                <div class="flex justify-between">
                    <div class="flex">
                        <p class="reply-button text-[14px] font-bold text-[#3F7FF5] cursor-pointer flex items-center gap-2" data-target-form="<?php echo esc_attr( $comment['comment']->comment_ID ) ?>">
                            پاسخ به این دیدگاه
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none">
                                <path d="M5.625 10.125L2.5 7L5.625 3.875" stroke="#3F7FF5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12.5 10.75V9.5C12.5 8.83696 12.2366 8.20107 11.7678 7.73223C11.2989 7.26339 10.663 7 10 7H2.5" stroke="#3F7FF5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </p>
                        <p class="close-reply-button hidden text-[14px] font-bold  text-[#3F7FF5] cursor-pointer flex items-center gap-2" data-target-form="<?php echo esc_attr( $comment['comment']->comment_ID ) ?>">
                            بستن
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="10" viewBox="0 0 11 10" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.50024 6.32601L8.81462 9.64039C8.99049 9.81626 9.22902 9.91506 9.47774 9.91506C9.72646 9.91506 9.96499 9.81626 10.1409 9.64039C10.3167 9.46452 10.4155 9.22598 10.4155 8.97726C10.4155 8.72854 10.3167 8.49001 10.1409 8.31414L6.82524 4.99976L10.1402 1.68539C10.2273 1.59831 10.2963 1.49493 10.3434 1.38117C10.3905 1.2674 10.4147 1.14548 10.4147 1.02236C10.4147 0.899231 10.3904 0.777317 10.3432 0.663576C10.2961 0.549835 10.227 0.446493 10.1399 0.359451C10.0528 0.272409 9.94947 0.203372 9.83571 0.156281C9.72194 0.10919 9.60002 0.0849678 9.47689 0.0849968C9.35377 0.0850258 9.23186 0.109306 9.11812 0.15645C9.00437 0.203595 8.90103 0.272681 8.81399 0.359764L5.50024 3.67414L2.18587 0.359764C2.09943 0.270183 1.99601 0.198714 1.88166 0.149528C1.7673 0.100341 1.6443 0.0744215 1.51982 0.0732815C1.39534 0.0721415 1.27188 0.0958038 1.15664 0.142888C1.0414 0.189972 0.936696 0.259535 0.84863 0.347517C0.760565 0.4355 0.690903 0.54014 0.643711 0.655333C0.596518 0.770525 0.572739 0.893963 0.573762 1.01844C0.574785 1.14292 0.600588 1.26595 0.649667 1.38036C0.698746 1.49476 0.770117 1.59824 0.859616 1.68476L4.17524 4.99976L0.860241 8.31476C0.770742 8.40129 0.699371 8.50477 0.650292 8.61917C0.601213 8.73357 0.57541 8.8566 0.574387 8.98108C0.573364 9.10556 0.597143 9.229 0.644336 9.34419C0.691528 9.45939 0.76119 9.56403 0.849255 9.65201C0.937321 9.73999 1.04203 9.80956 1.15726 9.85664C1.2725 9.90372 1.39596 9.92739 1.52044 9.92625C1.64492 9.92511 1.76793 9.89919 1.88228 9.85C1.99664 9.80081 2.10005 9.72934 2.18649 9.63976L5.50024 6.32601Z" fill="#3F7FF5"/>
                            </svg>
                        </p>
                    </div>
                </div>
                <div class="hidden mt-4" data-form="<?php echo esc_attr( $comment['comment']->comment_ID ) ?>">
                    <form method="post" class="submit-reply-form flex items-end gap-4">
                        <input type="hidden" name="product" value="<?php echo esc_attr( $comment['product']->ID ); ?>">
                        <input type="hidden" name="comment" value="<?php echo esc_attr( $comment['comment']->comment_ID ); ?>">
                        <textarea name="comment_content" class="w-full min-h-full rounded-[14px] border border-[#E4EBF0] p-2 resize-y" rows="1" placeholder="پاسخ خود را اینجا بنویسید..."></textarea>
                        <button type="submit" class="btn rounded-2xl p-4 text-white mt-2" style="box-shadow: 0 2px 0 0 #CA5608; background: #FD7013;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="17" viewBox="0 0 21 17" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M19.6595 4.59923C20.8815 2.59379 19.428 0.0248262 17.0788 0.0393534L3.86266 0.119491C1.33133 0.134071 -0.044711 3.08501 1.57123 5.03349L10.0046 15.21C11.5035 17.019 14.4048 16.4807 15.1565 14.2559L16.5505 10.1306L9.19314 6.69977C8.95277 6.58768 8.76678 6.3847 8.67607 6.13548C8.58536 5.88626 8.59737 5.61121 8.70945 5.37084C8.82154 5.13047 9.02452 4.94448 9.27374 4.85377C9.52296 4.76306 9.79801 4.77507 10.0384 4.88715L17.3949 8.31755L19.6595 4.59923Z" fill="white"/>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php } ?>

        </div>

    <?php endforeach;

    if ( $total_pages > 1 ) : ?>
        <div class="mb-9 flex w-full items-center justify-center gap-4">
            <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
                <?php echo paginate_links( [
                    'mid_size'  => 1,
                    'base'      => get_pagenum_link( 1 ) . '%_%',
                    'format'    => '?page=%#%',
                    'current'   => $page,
                    'total'     => $total_pages,
                    'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                    'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                ] ); ?>
            </div>
        </div>
    <?php endif;
else: ?>
    <div class="text-[22px] font-bold lg:text-lg text-center lg:my-19 text-gray-500">
        در حال حاضر دیدگاهی برای نمایش وجود ندارد.
    </div>
<?php endif;

wp_die();