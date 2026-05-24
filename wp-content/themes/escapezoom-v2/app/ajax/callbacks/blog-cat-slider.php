<?php
if (!$_POST['data-source']) {
    return false;
}
$cat_id = intval($_POST['data-source']);
$cat_title = null;
switch ($cat_id) {
    case 1:
        $cat_title = 'بلاگ';
        break;
    case 953:
        $cat_title = 'مجله خبری';
        break;
    case 954:
        $cat_title = 'نقد و بررسی اتاق‌ها';
        break;
}

$args = array(
    'posts_per_page' => 10,
    'tax_query' => array(
        array(
            'taxonomy' => 'category', // نام دسته‌بندی استاندارد وردپرس
            'field' => 'term_id', // نوع شناسه: می‌تواند 'term_id'، 'slug' یا 'name' باشد
            'terms' => $cat_id, // آیدی دسته‌بندی که می‌خواهید پست‌ها را از آن بگیرید
        ),
    ),
);

$query = new WP_Query($args);
$items = [];
if ($query->have_posts()) :
    while ($query->have_posts()) : $query->the_post();
        global $post;
        $comments = wp_count_comments($post->ID);
        $items[] = [
            'image_url' => get_the_post_thumbnail_url($post->ID),
            'url' => get_the_permalink(),
            'title' => get_the_title(),
            'excerpt' => get_the_excerpt(),
            'cat_title' => $cat_title,
            'comment_count' => $comments->approved,
            'author' => get_the_author()
        ];
    endwhile;
endif;
wp_reset_postdata();
wp_send_json_success($items);