<?php
// دریافت پارامترها
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$sort_type = isset($_POST['sort_type']) ? sanitize_text_field($_POST['sort_type']) : 'newest';
$product_type = isset($_POST['product_type']) ? sanitize_text_field($_POST['product_type']) : '';

if (!$product_id) {
    echo '<div class="text-center text-red-500 py-8">خطا: شناسه محصول یافت نشد</div>';
    return;
}

// تنظیمات pagination (هم‌راستا با single-product.php)
$comments_per_page = (int) EZ_SINGLE_PRODUCT_COMMENTS_PER_PAGE;

// آرگومان‌های query
$args = [
    'post_type' => 'product',
    'post_id'   => $product_id,
    'status'    => 'approve',
    'number'    => $comments_per_page,
    'offset'    => ($page - 1) * $comments_per_page,
    'parent'    => 0,
];

// سورت بر اساس نوع
if ($sort_type === 'pro') {
    // نظرات حرفه‌ای (کاربران با level 3 و 4 - با تجربه و کارکشته)
    // برای فیلتر صحیح، ابتدا همه کامنت‌های حرفه‌ای را می‌گیریم
    $args['meta_key'] = 'user_level';
    $args['orderby'] = 'meta_value_num';
    $args['order'] = 'DESC';
    // گرفتن تعداد بیشتر برای اطمینان از داشتن کامنت‌های کافی بعد از فیلتر
    $args['number'] = 0; // 0 یعنی همه کامنت‌ها
    $args['offset'] = 0; // reset offset
} else {
    // جدیدترین‌ها (پیش‌فرض)
    $args['orderby'] = 'comment_date';
    $args['order'] = 'DESC';
}

// دریافت کامنت‌ها
$comments_query = new WP_Comment_Query;
$all_comments = $comments_query->query($args);

// فیلتر کردن کامنت‌های حرفه‌ای (فقط level 3 و 4) و اعمال pagination
$total_pages = 1; // مقدار پیش‌فرض
if ($sort_type === 'pro') {
    $filtered_comments = [];
    foreach ($all_comments as $comment) {
        $user_level = get_comment_meta($comment->comment_ID, 'user_level', true);
        $user_level = intval($user_level) ?: 1;
        
        // level 3، 4، و 10 (مجموعه‌دار؛ 10 >= 3 در مقایسه عددی)
        if ($user_level >= 3) {
            $filtered_comments[] = $comment;
        }
    }

    // سورت: 10 مثل 3 (زیر 4)، نه بالاتر از کارکشته
    if ( function_exists( 'ez_comment_pro_sort_key_from_stored_level' ) ) {
        usort(
            $filtered_comments,
            static function ( $a, $b ) {
                $la = (int) get_comment_meta( $a->comment_ID, 'user_level', true );
                $lb = (int) get_comment_meta( $b->comment_ID, 'user_level', true );
                $ka = ez_comment_pro_sort_key_from_stored_level( $la );
                $kb = ez_comment_pro_sort_key_from_stored_level( $lb );
                return $kb <=> $ka;
            }
        );
    }
    
    // محاسبه total_pages برای کامنت‌های حرفه‌ای
    $total_pro_comments = count($filtered_comments);
    $total_pages = ($total_pro_comments > 0) ? ceil($total_pro_comments / $comments_per_page) : 1;
    
    // اعمال pagination روی کامنت‌های فیلتر شده
    $offset = ($page - 1) * $comments_per_page;
    $comments = array_slice($filtered_comments, $offset, $comments_per_page);
} else {
    $comments = $all_comments;
    // محاسبه total_pages برای کامنت‌های جدید
    $total_comments_count = wp_count_comments($product_id);
    $total_comments_approved = isset($total_comments_count->approved) ? $total_comments_count->approved : 0;
    // فقط کامنت‌های والد (بدون replies)
    $parent_comments_count = get_comments([
        'post_id' => $product_id,
        'status' => 'approve',
        'parent' => 0,
        'count' => true
    ]);
    $total_pages = ($parent_comments_count > 0) ? ceil($parent_comments_count / $comments_per_page) : 1;
}

// اگر کامنتی نبود
if (empty($comments)) {
    if ($page === 1) {
        if ($sort_type === 'pro') {
            echo '<div class="text-center text-gray-500 py-8">کامنتی از کاربران حرفه‌ای ثبت نشده است</div>';
        } else {
            echo '<div class="text-center text-gray-500 py-8">هنوز نظری ثبت نشده است</div>';
        }
    }
    // آپدیت total_pages به 0 یا 1 برای مخفی کردن دکمه مشاهده بیشتر
    $total_pages = 0;
    ?>
    <script>
        (function() {
            if (typeof jQuery !== 'undefined') {
                jQuery('#comments-total-pages').val(0);
                var $loadMoreBtn = jQuery('#load-more-comments');
                if ($loadMoreBtn.length) {
                    $loadMoreBtn.attr('data-total-pages', 0).hide();
                }
            }
        })();
    </script>
    <?php
    return;
}

// نمایش کامنت‌ها (استایل .user-profile-link در single-product.php)

foreach ($comments as $comment) {
    $comment_id = $comment->comment_ID;

    // دریافت replies
    $replies_args = [
        'parent' => $comment_id,
        'status' => 'approve',
        'type'   => 'comment',
    ];
    $replies = get_comments($replies_args);
    $reply_content = !empty($replies[0]) ? $replies[0]->comment_content : '';

    // اطلاعات نویسنده
    $author_title = '';
    $user = get_user_by('id', $comment->user_id);

    if ($user && $user->user_firstname) {
        $author_title = $user->user_firstname;
        if ($user->user_lastname) {
            $author_title .= ' ' . $user->user_lastname;
        }
    } elseif (ctype_digit($comment->comment_author)) {
        $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);
    } else {
        $author_title = $comment->comment_author;
    }

    // دریافت rating items
    $comment_ratings = [];
    $comment_rating_meta = get_comment_meta($comment_id, 'comment_rating', true);
    if (is_array($comment_rating_meta)) {
        foreach ($comment_rating_meta as $key => $comment_rating) {
            $comment_ratings[$key] = intval($comment_rating) / 20;
        }
    }

    // محاسبه rate
    $user_feeling = get_comment_meta($comment_id, "rating", true);
    $rate = $user_feeling;

    if ($product_type !== 'اتاق فرار') {
        $rate = max($rate, 0.2);
        $rate = ceil((float) $rate * 5);
    } else {
        $rate = max($rate, 1);
        $rate = ceil((float) $rate);
    }

    $rate_str = match ((string) $rate) {
        '1' => 'خیلی بد',
        '2' => 'خوب نبود',
        '3' => 'معمولی بود',
        '4' => 'خوب بود',
        default => 'عالی بود',
    };

    $rate_color = match ((string) $rate) {
        '1' => '#F21543',
        '2' => '#FD7013',
        '3' => '#BF9A00',
        '4' => '#3F7FF5',
        default => '#049654',
    };

    $rate_img = match ((string) $rate) {
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        default => '5',
    };

    $comment_date = strtotime($comment->comment_date);

?>
    <div class="border border-slate-105 flex justify-between items-start rounded-2xl max-lg:pt-7 lg:p-7 mt-8">
        <div class="flex flex-col w-full">
            <div class="flex justify-between items-center max-lg:px-7">
                <?php if ($comment->user_id) : ?>
                    <a href="<?php echo esc_url(home_url('/profile/' . $comment->user_id)); ?>" class="user-profile-link flex items-center gap-3 relative">
                        <div class="w-d40 h-d40 rounded-xl overflow-hidden bg-secondary-100 flex items-center justify-center">
                            <?php
                            echo get_avatar(
                                $comment->user_id ?: $comment->comment_author_email,
                                64,
                                '',
                                $author_title,
                                ['class' => 'w-full h-full object-cover rounded-xl']
                            );
                            ?>
                        </div>
                        <p class="text-sm font-bold"><?php echo esc_html($author_title); ?></p>
                    </a>
                <?php else : ?>
                    <div class="flex items-center gap-3">
                        <div class="w-d40 h-d40 rounded-xl overflow-hidden bg-secondary-100 flex items-center justify-center">
                            <?php
                            echo get_avatar(
                                $comment->comment_author_email,
                                64,
                                '',
                                $author_title,
                                ['class' => 'w-full h-full object-cover rounded-xl']
                            );
                            ?>
                        </div>
                        <p class="text-sm font-bold"><?php echo esc_html($author_title); ?></p>
                    </div>
                <?php endif; ?>
                <span class="max-lg:text-base">
                    <?php
                    if ($comment->user_id) {
                        if ($comment_date > COMMENT_NEW_VER_TIMESTAMP) {
                            $stored_ul = (int) get_comment_meta( $comment->comment_ID, 'user_level', true );
                            if ( function_exists( 'ez_comment_badge_by_stored_level' ) ) {
                                ez_comment_badge_by_stored_level( (int) $comment->user_id, 'px-2 py-0.5 text-xs font-bold mr-2.5 rounded-xl', $stored_ul );
                            } else {
                                user_badge_by_level( 1, 'px-2 py-0.5 text-xs font-bold mr-2.5 rounded-xl', 'user_level' );
                            }
                        } else {
                            user_badge_by_level(0, 'px-2 py-0.5 text-xs font-bold text-white mr-2.5 rounded-xl');
                        }
                    }
                    ?>
                </span>
            </div>

            <div class="w-full h-d1 bg-slate-105 my-3"></div>

            <div class="comment-item max-lg:px-7">
                <?php
                // محاسبه هوشمند طول نمایش
                $content_length = mb_strlen($comment->comment_content);

                // Desktop: base 500, threshold 530, min hidden 30%
                $desktop_base = 500;
                $desktop_threshold = 530;
                $desktop_show_length = $desktop_base;
                $desktop_has_long_text = false;
                $desktop_needs_toggle = false;

                if ($content_length > $desktop_threshold) {
                    $desktop_show_length = floor($content_length * 0.7); // نمایش 70%، مخفی 30%
                    $desktop_has_long_text = true;
                    $desktop_needs_toggle = true;
                }

                // Mobile: base 200, threshold 230, min hidden 30%
                // در موبایل همیشه دکمه بیشتر داریم (برای نمایش scores)
                $mobile_base = 200;
                $mobile_threshold = 230;
                $mobile_show_length = $mobile_base;
                $mobile_has_long_text = false;

                if ($content_length > $mobile_threshold) {
                    $mobile_show_length = floor($content_length * 0.7); // نمایش 70%، مخفی 30%
                    $mobile_has_long_text = true;
                }

                // در موبایل همیشه دکمه نمایش داده میشه (برای scores)
                $mobile_needs_toggle = ($product_type == 'اتاق فرار');
                ?>

                <!-- Desktop version -->
                <p class="comment-text-content hidden lg:block text-base leading-9"
                    data-full-text="<?php echo esc_attr($comment->comment_content); ?>"
                    data-show-length="<?php echo $desktop_show_length; ?>"
                    data-has-long-text="<?php echo $desktop_has_long_text ? 'true' : 'false'; ?>">
                    <span class="comment-text-display">
                        <?php
                        if ($desktop_needs_toggle) {
                            echo esc_html(mb_substr($comment->comment_content, 0, $desktop_show_length)) . '...';
                        } else {
                            echo esc_html($comment->comment_content);
                        }
                        ?>
                    </span>
                    <?php if ($desktop_needs_toggle) { ?>
                        <button class="toggle-comment-btn inline-flex items-center gap-1 text-text-3 font-medium text-sm transition-colors ml-1 focus:outline-none hover:text-blue">
                            <span class="toggle-text">بیشتر</span>
                            <svg class="chevron-icon w-3 h-3 transition-transform duration-300" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.75 0.75L4.15 3.3C4.50556 3.56667 4.99444 3.56667 5.35 3.3L8.75 0.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </button>
                    <?php } ?>
                </p>

                <!-- Mobile version -->
                <p class="comment-text-content lg:hidden text-sm leading-9"
                    data-full-text="<?php echo esc_attr($comment->comment_content); ?>"
                    data-show-length="<?php echo $mobile_show_length; ?>"
                    data-has-long-text="<?php echo $mobile_has_long_text ? 'true' : 'false'; ?>">
                    <span class="comment-text-display">
                        <?php
                        if ($mobile_has_long_text) {
                            echo esc_html(mb_substr($comment->comment_content, 0, $mobile_show_length)) . '...';
                        } else {
                            echo esc_html($comment->comment_content);
                        }
                        ?>
                    </span>
                    <?php if ($mobile_needs_toggle) { ?>
                        <button class="toggle-comment-btn inline-flex items-center gap-1 text-text-3 font-medium text-sm transition-colors ml-1 focus:outline-none hover:text-blue">
                            <span class="toggle-text">بیشتر</span>
                            <svg class="chevron-icon w-3 h-3 transition-transform duration-300" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.75 0.75L4.15 3.3C4.50556 3.56667 4.99444 3.56667 5.35 3.3L8.75 0.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </button>
                    <?php } ?>
                </p>

                <?php if ($product_type == 'اتاق فرار') { ?>
                    <div class="scores-section lg:hidden overflow-hidden transition-all duration-500 ease-in-out max-h-0 opacity-0">
                        <div class="w-full h-d1 bg-slate-105 mb-4"></div>
                        <div class="flex flex-col space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs lg:text-sm whitespace-nowrap">فضاسازی</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1094]) ? $comment_ratings[1094] : 0; ?></span>
                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                            <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1094]) ? $comment_ratings[1094] * 20 : 0; ?>%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs lg:text-sm whitespace-nowrap">کیفیت معما</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1095]) ? $comment_ratings[1095] : 0; ?></span>
                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                            <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1095]) ? $comment_ratings[1095] * 20 : 0; ?>%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs lg:text-sm whitespace-nowrap">تازگی و خلاقیت</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1098]) ? $comment_ratings[1098] : 0; ?></span>
                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                            <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1098]) ? $comment_ratings[1098] * 20 : 0; ?>%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs lg:text-sm whitespace-nowrap">بازیگردانی و اکت</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1096]) ? $comment_ratings[1096] : 0; ?></span>
                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                            <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1096]) ? $comment_ratings[1096] * 20 : 0; ?>%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs lg:text-sm whitespace-nowrap">برخورد پرسنل</span>
                                <span class="min-w-36">
                                    <div class="flex items-center justify-center" dir="ltr">
                                        <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1097]) ? $comment_ratings[1097] : 0; ?></span>
                                        <div class="w-full h-1 rounded-full bg-slate-110">
                                            <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1097]) ? $comment_ratings[1097] * 20 : 0; ?>%;"></div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="flex justify-between items-center border-t border-t-slate-105 lg:border lg:border-slate-105 lg:rounded-xl p-2 lg:my-3">
                <div class="flex items-center gap-3">
                    <img src="<?php bloginfo('template_url'); ?>/assets/images/emojis/<?= $rate_img ?>.png" draggable="false" alt="<?php echo $rate; ?>" class="w-8 h-auto">
                    <p class="text-sm font-bold" style="color: <?= esc_attr($rate_color) ?>"><?php echo esc_html($rate_str) ?></p>
                </div>
                <p class="text-xs font-bold text-text-3"><?php echo human_time_diff($comment_date, current_time('U')) ?> پیش</p>
            </div>

            <?php if ($reply_content) { ?>
                <div class="bg-gray-50 rounded-lg flex flex-col p-2 max-lg:hidden">
                    <p class="text-sm font-black">پاسخ مجموعه</p>
                    <div class="flex mt-3">
                        <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="ml-3 w-4 h-d14">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.707C5.89426 13.5194 5.99957 13.2651 5.99957 13C5.99957 12.7348 5.89426 12.4805 5.70679 12.293L3.41379 9.99997H8.99979C10.8563 9.99997 12.6368 9.26247 13.9495 7.94972C15.2623 6.63696 15.9998 4.85649 15.9998 2.99997V0.999969C15.9998 0.734753 15.8944 0.480398 15.7069 0.292862C15.5194 0.105326 15.265 -3.05176e-05 14.9998 -3.05176e-05C14.7346 -3.05176e-05 14.4802 0.105326 14.2927 0.292862C14.1051 0.480398 13.9998 0.734753 13.9998 0.999969V2.99997C13.9998 4.32605 13.473 5.59782 12.5353 6.5355C11.5976 7.47319 10.3259 7.99997 8.99979 7.99997H3.41379L5.70679 5.70697C5.8023 5.61472 5.87848 5.50438 5.93089 5.38237C5.9833 5.26037 6.01088 5.12915 6.01204 4.99637C6.01319 4.86359 5.98789 4.73191 5.93761 4.60902C5.88733 4.48612 5.81307 4.37447 5.71918 4.28057C5.62529 4.18668 5.51364 4.11243 5.39074 4.06215C5.26784 4.01187 5.13616 3.98656 5.00339 3.98772C4.87061 3.98887 4.73939 4.01646 4.61738 4.06887C4.49538 4.12128 4.38503 4.19746 4.29279 4.29297L0.292786 8.29297C0.105315 8.4805 0 8.73481 0 8.99997C0 9.26513 0.105315 9.51944 0.292786 9.70697L4.29279 13.707C4.48031 13.8944 4.73462 13.9998 4.99979 13.9998C5.26495 13.9998 5.51926 13.8944 5.70679 13.707Z" fill="#889BAD"></path>
                        </svg>
                        <p class="text-steel"><?= esc_html($reply_content); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if ($product_type == 'اتاق فرار') { ?>
            <div class="h-full w-d1 bg-slate-105 mx-7 max-lg:hidden"></div>

            <div class="flex flex-col max-lg:hidden">
                <div class="max-lg:w-full lg:w-72">
                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                        <span class="text-xs nowrap lg:text-sm">فضاسازی</span>
                        <span class="min-w-36">
                            <div class="flex items-center justify-center" dir="ltr">
                                <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1094]) ? $comment_ratings[1094] : 0; ?></span>
                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                    <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1094]) ? $comment_ratings[1094] * 20 : 0; ?>%;"></div>
                                </div>
                            </div>
                        </span>
                    </div>
                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                        <span class="text-xs nowrap lg:text-sm">کیفیت معما</span>
                        <span class="min-w-36">
                            <div class="flex items-center justify-center" dir="ltr">
                                <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1095]) ? $comment_ratings[1095] : 0; ?></span>
                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                    <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1095]) ? $comment_ratings[1095] * 20 : 0; ?>%;"></div>
                                </div>
                            </div>
                        </span>
                    </div>
                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                        <span class="text-xs nowrap lg:text-sm">تازگی و خلاقیت</span>
                        <span class="min-w-36">
                            <div class="flex items-center justify-center" dir="ltr">
                                <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1098]) ? $comment_ratings[1098] : 0; ?></span>
                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                    <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1098]) ? $comment_ratings[1098] * 20 : 0; ?>%;"></div>
                                </div>
                            </div>
                        </span>
                    </div>
                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                        <span class="text-xs nowrap lg:text-sm">بازیگردانی و اکت</span>
                        <span class="min-w-36">
                            <div class="flex items-center justify-center" dir="ltr">
                                <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1096]) ? $comment_ratings[1096] : 0; ?></span>
                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                    <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1096]) ? $comment_ratings[1096] * 20 : 0; ?>%;"></div>
                                </div>
                            </div>
                        </span>
                    </div>
                    <div class="mb-2 flex items-center justify-between last:mb-0 lg:mb-3.5">
                        <span class="text-xs nowrap lg:text-sm">برخورد پرسنل</span>
                        <span class="min-w-36">
                            <div class="flex items-center justify-center" dir="ltr">
                                <span class="mr-2.5 min-w-9 text-lg"><?php echo isset($comment_ratings[1097]) ? $comment_ratings[1097] : 0; ?></span>
                                <div class="w-full h-1 rounded-full bg-slate-110 dark:bg-gray-700">
                                    <div class="h-1 rounded-full bg-focus-blue" style="width: <?php echo isset($comment_ratings[1097]) ? $comment_ratings[1097] * 20 : 0; ?>%;"></div>
                                </div>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if ($reply_content) { ?>
        <div class="bg-gray-50 rounded-lg flex flex-col p-2 lg:hidden max-lg:mt-5">
            <p class="text-sm font-black">پاسخ مجموعه</p>
            <div class="flex mt-3">
                <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="ml-3 w-4 h-d14">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.707C5.89426 13.5194 5.99957 13.2651 5.99957 13C5.99957 12.7348 5.89426 12.4805 5.70679 12.293L3.41379 9.99997H8.99979C10.8563 9.99997 12.6368 9.26247 13.9495 7.94972C15.2623 6.63696 15.9998 4.85649 15.9998 2.99997V0.999969C15.9998 0.734753 15.8944 0.480398 15.7069 0.292862C15.5194 0.105326 15.265 -3.05176e-05 14.9998 -3.05176e-05C14.7346 -3.05176e-05 14.4802 0.105326 14.2927 0.292862C14.1051 0.480398 13.9998 0.734753 13.9998 0.999969V2.99997C13.9998 4.32605 13.473 5.59782 12.5353 6.5355C11.5976 7.47319 10.3259 7.99997 8.99979 7.99997H3.41379L5.70679 5.70697C5.8023 5.61472 5.87848 5.50438 5.93089 5.38237C5.9833 5.26037 6.01088 5.12915 6.01204 4.99637C6.01319 4.86359 5.98789 4.73191 5.93761 4.60902C5.88733 4.48612 5.81307 4.37447 5.71918 4.28057C5.62529 4.18668 5.51364 4.11243 5.39074 4.06215C5.26784 4.01187 5.13616 3.98656 5.00339 3.98772C4.87061 3.98887 4.73939 4.01646 4.61738 4.06887C4.49538 4.12128 4.38503 4.19746 4.29279 4.29297L0.292786 8.29297C0.105315 8.4805 0 8.73481 0 8.99997C0 9.26513 0.105315 9.51944 0.292786 9.70697L4.29279 13.707C4.48031 13.8944 4.73462 13.9998 4.99979 13.9998C5.26495 13.9998 5.51926 13.8944 5.70679 13.707Z" fill="#889BAD"></path>
                </svg>
                <p class="text-steel"><?= esc_html($reply_content); ?></p>
            </div>
        </div>
    <?php } ?>

<?php
} // end foreach

// آپدیت total_pages در JavaScript
?>
<script>
    (function() {
        if (typeof jQuery !== 'undefined') {
            jQuery('#comments-total-pages').val(<?php echo $total_pages; ?>);
            // آپدیت data attribute در دکمه load-more
            var $loadMoreBtn = jQuery('#load-more-comments');
            if ($loadMoreBtn.length) {
                $loadMoreBtn.attr('data-total-pages', <?php echo $total_pages; ?>);
                // اگر total_pages کمتر یا مساوی 1 باشد، دکمه را مخفی کن
                if (<?php echo $total_pages; ?> <= 1) {
                    $loadMoreBtn.hide();
                }
            }
        }
    })();
</script>
