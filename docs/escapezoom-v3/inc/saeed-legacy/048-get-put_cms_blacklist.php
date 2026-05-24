<?php
/**
 * GET: put_cms_blacklist
 *
 * با باز شدن URL و پارامتر کوئری اجرا می‌شود؛ برای نگهداری/تست/مهاجرت داده. پارامترها: put_cms_blacklist
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 4906-4949)
 * نوع: ابزار یک‌باره (GET)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET: put_cms_blacklist
 *
 * هدف: پر کردن comments_blacklist از کامنت‌های محصولات
 * استفاده: مهاجرت یک‌بار
 * وابستگی: WP_Query, post meta
 * امنیت: بدون احراز هویت
 * وضعیت: حذف یا guard
 * منبع: saeed-legacy/048-get-put_cms_blacklist.php:14
 */
if ( isset($_GET['put_cms_blacklist']) ) {

    $args = array(
        'post_type'         => 'product',
        'post_status'       => 'publish',
        'posts_per_page'    => -1,
        'meta_query' => array (
            array(
                'key'     => 'product_state',
                'value'   => 'active',
                'compare' => 'LIKE',
            ),
        ),
    );
    $loop = new WP_Query( $args );

    while ( $loop->have_posts() ) : $loop->the_post();
        global $product;

        $product_id = get_the_ID();

        $args = array (
            'post__in' => $product_id,
        );
        $comments_query = new WP_Comment_Query;
        $comments       = $comments_query->query($args);

        if ($comments) {
            foreach ($comments as $comment) {
                $phone = $comment->comment_author;

                if ( !empty( $phone ) ) {
                    $temp1 = get_post_meta($product_id, 'comments_blacklist', true);
                    $comments_blacklist = !empty($temp1) ? $temp1 : [];

                    if ( !in_array($phone, $comments_blacklist) )
                        $comments_blacklist[] = $phone;

                    update_post_meta($product_id, 'comments_blacklist', $comments_blacklist);
                }
            }
        }
    endwhile;
}
