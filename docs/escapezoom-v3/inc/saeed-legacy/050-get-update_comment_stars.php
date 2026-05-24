<?php
/**
 * GET: update_comment_stars
 *
 * با باز شدن URL و پارامتر کوئری اجرا می‌شود؛ برای نگهداری/تست/مهاجرت داده. پارامترها: update_comment_stars
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 4978-5069)
 * نوع: ابزار یک‌باره (GET)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET: update_comment_stars
 *
 * هدف: به‌روزرسانی ستاره کامنت
 * استفاده: دستی
 * وابستگی: wpdb, comments
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/050-get-update_comment_stars.php:14
 */
if ( isset( $_GET['update_comment_stars'] ) ) {

    $power_map = [
        1 => 1,
        2 => 2,
        3 => 7,
        4 => 20,
    ];

    $args = array (
        'post_type'         => 'product',
        'posts_per_page'    => -1,
//        'post__in'          => [5104],
        'meta_query'        => array (
            array(
                'key'     => 'product_state',
                'value'   => 'active',
                'compare' => 'LIKE',
            ),
        ),
    );
    $loop = new WP_Query( $args );

    $comment_rates = [];
    while ( $loop->have_posts() ) : $loop->the_post();

        $args = array (
            'post__in'      => get_the_ID(),
            'date_query'    => array (
                array(
                    'after'     => '2023/04/15',
                    'inclusive' => true,
                ),
            ),
        );
        $comments_query = new WP_Comment_Query;
        $comments       = $comments_query->query($args);

        $comments_count_new      = 0;
        $clone_comments_count_new      = 0;
        $comment_rating_1   = $comment_rating_2 = $comment_rating_3 = $comment_rating_4 = $comment_rating_5 = 0;
        $clone_comment_rating_1   = $clone_comment_rating_2 = $clone_comment_rating_3 = $clone_comment_rating_4 = $clone_comment_rating_5 = 0;

        if ($comments) {
            foreach ($comments as $comment) {
                $comment_rating = get_comment_meta($comment->comment_ID, "comment_rating", true);

                if ( !empty ( $comment_rating ) ) {
                    if ( $comment->comment_type == 'review' && $comment->comment_approved == 1 ) {

                        $user_level = get_comment_meta($comment->comment_ID, "user_level", true);
                        $user_power = $power_map[$user_level] ?? 1;

                        $comments_count_new++;
                        $clone_comments_count_new += $user_power;

                        $comment_rating_1 += (int)$comment_rating[1098];
                        $comment_rating_2 += (int)$comment_rating[1097];
                        $comment_rating_3 += (int)$comment_rating[1096];
                        $comment_rating_4 += (int)$comment_rating[1095];
                        $comment_rating_5 += (int)$comment_rating[1094];

                        $clone_comment_rating_1 += (int)$comment_rating[1098] * $user_power;
                        $clone_comment_rating_2 += (int)$comment_rating[1097] * $user_power;
                        $clone_comment_rating_3 += (int)$comment_rating[1096] * $user_power;
                        $clone_comment_rating_4 += (int)$comment_rating[1095] * $user_power;
                        $clone_comment_rating_5 += (int)$comment_rating[1094] * $user_power;
                    }
                }
            }
        }
        wp_reset_query();

        update_post_meta(get_the_ID(), 'comments_count_new', $comments_count_new);
        update_post_meta(get_the_ID(), 'clone_comments_count_new', $clone_comments_count_new);

        $comment_rates[1098] = $comment_rating_1;
        $comment_rates[1097] = $comment_rating_2;
        $comment_rates[1096] = $comment_rating_3;
        $comment_rates[1095] = $comment_rating_4;
        $comment_rates[1094] = $comment_rating_5;
        update_post_meta(get_the_ID(), 'product_rates', $comment_rates);

        $clone_comment_rates[1098] = $clone_comment_rating_1;
        $clone_comment_rates[1097] = $clone_comment_rating_2;
        $clone_comment_rates[1096] = $clone_comment_rating_3;
        $clone_comment_rates[1095] = $clone_comment_rating_4;
        $clone_comment_rates[1094] = $clone_comment_rating_5;
        update_post_meta(get_the_ID(), 'clone_product_rates', $clone_comment_rates);

    endwhile;
}
