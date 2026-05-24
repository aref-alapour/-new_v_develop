<?php
/**
 * GET: get_games_points_list, p
 *
 * با باز شدن URL و پارامتر کوئری اجرا می‌شود؛ برای نگهداری/تست/مهاجرت داده. پارامترها: get_games_points_list, p
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5070-5163)
 * نوع: ابزار یک‌باره (GET)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET: get_games_points_list
 *
 * هدف: خروجی CSV امتیاز بازی‌ها
 * استفاده: دستی + پارامتر p
 * وابستگی: comments, get_the_title
 * امنیت: بدون احراز هویت
 * وضعیت: بررسی حذف
 * منبع: saeed-legacy/051-get-get_games_points_list-p.php:14
 */
if ( isset( $_GET['get_games_points_list'] ) and isset( $_GET['p'] ) ) {

    $power_map = [
        1 => 1,
        2 => 2,
        3 => 7,
        4 => 20,
    ];

    $args = array (
        'post_type'         => 'product',
        'posts_per_page'    => -1,
        'post__in'          => [$_GET['p']],
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
        $product_id = get_the_ID();

        $args = array (
            'post__in'      => $product_id,
            'date_query'    => array (
                array(
                    'after'     => '2023/04/15',
                    'inclusive' => true,
                ),
            ),
        );
        $comments_query = new WP_Comment_Query;
        $comments       = $comments_query->query($args);

        if ($comments) {
            $comment_list = [];
            foreach ($comments as $comment) {
                $comment_rating = get_comment_meta($comment->comment_ID, "comment_rating", true);

                if ( !empty ( $comment_rating ) ) {
                    if ( $comment->comment_type == 'review' && $comment->comment_approved == 1 ) {

                        $user_level = get_comment_meta($comment->comment_ID, "user_level", true);
                        $user_power = $power_map[$user_level] ?? 1;

                        $comment_rate = 0;
                        foreach ( [1098, 1097, 1096, 1095, 1094] as $item_list )
                            $comment_rate += (int)$comment_rating[$item_list];

                        (float)$comment_rate /= 100;

                        $comment_list[] = [
                            'rate'  => $comment_rate,
                            'power' => $user_power,
                            'id'    => $comment->comment_ID,
                        ];
                    }
                }
            }
        }
        wp_reset_query();

    endwhile;

    $sum_rate_x_power = 0;
    $sum_power = 0;
    foreach ($comment_list as $row) {
        $sum_rate_x_power += $row['rate'] * $row['power'];
        $sum_power += $row['power'];
    }
    $weighted_average = $sum_power > 0 ? ($sum_rate_x_power / $sum_power) : 0;

    $filename = mb_substr(trim(preg_replace('/[^\w\-_\.ء-ي0-9]/u', '', preg_replace('/[\/\\\?%\*:"<>|()\s]+/u', '_', str_replace("\0", '', get_the_title(isset($_GET['p']) ? intval($_GET['p']) : 0)))), '_-'), 0, 100) . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

    fputcsv($output, ['', '', 'average: ', $weighted_average]);

    foreach ($comment_list as $row)
//        fputcsv($output, [$row['rate'], $row['power'], '']);
        fputcsv($output, [$row['id'],$row['rate'], $row['power'], '']);

    fclose($output);
    exit;
}
