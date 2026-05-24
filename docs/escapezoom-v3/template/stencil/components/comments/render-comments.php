<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a single comment as HTML string (for REST/HTMX responses).
 * Used by CommentsRestController and theme. No WooCommerce; verified = false.
 */
function ez_render_comment_item_html_string($comment, $post_type = 'post') {
    $rating = get_comment_meta($comment->comment_ID, 'rating', true);
    $verified = false;
    $date = get_comment_date('d F Y', $comment->comment_ID);
    $avatar = get_avatar_url($comment->comment_author_email);
    $author = $comment->comment_author;
    $content = wpautop($comment->comment_content);

    $children_args = [
        'parent' => $comment->comment_ID,
        'status' => 'approve',
        'number' => 3,
        'post_id' => $comment->comment_post_ID,
    ];
    $children = get_comments($children_args);
    $response_html = '';
    if ($children) {
        foreach ($children as $child) {
            $response_html .= '<div class="mt-4 bg-gray-50 p-4 rounded-lg border-r-4 border-secondary/50 text-sm">';
            $response_html .= '<div class="font-yekan-bold text-navyBlue mb-1 flex items-center gap-2">' . esc_html($child->comment_author) . '<span class="text-xs text-gray-400 font-yekan-regular">' . get_comment_date('d F Y', $child->comment_ID) . '</span></div>';
            $response_html .= '<div class="text-gray-600 leading-6">' . wpautop($child->comment_content) . '</div>';
            $response_html .= '</div>';
        }
    }

    ob_start();
    if ($post_type === 'product') {
        ?>
        <ez-comment-item-product rating="<?php echo (int) $rating; ?>" verified="<?php echo $verified ? 'true' : 'false'; ?>">
            <img slot="avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($author); ?>" class="w-12 h-12 rounded-full object-cover">
            <span slot="author"><?php echo esc_html($author); ?></span>
            <span slot="date"><?php echo esc_html($date); ?></span>
            <div slot="content"><?php echo $content; ?></div>
            <?php if (!empty($response_html)): ?>
                <div slot="response"><?php echo $response_html; ?></div>
            <?php endif; ?>
        </ez-comment-item-product>
        <?php
    } else {
        ?>
        <ez-comment-item-post>
            <img slot="avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($author); ?>" class="w-12 h-12 rounded-full object-cover">
            <span slot="author"><?php echo esc_html($author); ?></span>
            <span slot="date"><?php echo esc_html($date); ?></span>
            <div slot="content"><?php echo $content; ?></div>
            <?php if (!empty($response_html)): ?>
                <div slot="response"><?php echo $response_html; ?></div>
            <?php endif; ?>
        </ez-comment-item-post>
        <?php
    }
    return ob_get_clean();
}

function ez_render_comment_item($comment, $post_type = 'post') {
    $rating = get_comment_meta($comment->comment_ID, 'rating', true);
    // Verified purchase: no WooCommerce; can be extended via ez_orders later.
    $verified = false;
    $date = get_comment_date('d F Y', $comment->comment_ID);
    $avatar = get_avatar_url($comment->comment_author_email);
    $author = $comment->comment_author;
    $content = wpautop($comment->comment_content);

    $children_args = [
        'parent' => $comment->comment_ID,
        'status' => 'approve',
        'number' => 3,
        'post_id' => $comment->comment_post_ID
    ];
    $children = get_comments($children_args);

    ob_start();
    if ($children) {
        foreach ($children as $child) {
            ?>
            <div class="mt-4 bg-gray-50 p-4 rounded-lg border-r-4 border-secondary/50 text-sm">
                <div class="font-yekan-bold text-navyBlue mb-1 flex items-center gap-2">
                    <?php echo esc_html($child->comment_author); ?>
                    <span class="text-xs text-gray-400 font-yekan-regular"><?php echo get_comment_date('d F Y', $child->comment_ID); ?></span>
                </div>
                <div class="text-gray-600 leading-6">
                    <?php echo wpautop($child->comment_content); ?>
                </div>
            </div>
            <?php
        }
    }
    $response_html = ob_get_clean();

    if ($post_type === 'product') {
        ?>
        <ez-comment-item-product
            rating="<?php echo intval($rating); ?>"
            verified="<?php echo $verified ? 'true' : 'false'; ?>"
        >
            <img slot="avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($author); ?>" class="w-12 h-12 rounded-full object-cover">
            <span slot="author"><?php echo esc_html($author); ?></span>
            <span slot="date"><?php echo esc_html($date); ?></span>
            <div slot="content"><?php echo $content; ?></div>
            <?php if (!empty($response_html)): ?>
                <div slot="response"><?php echo $response_html; ?></div>
            <?php endif; ?>
        </ez-comment-item-product>
        <?php
    } else {
        ?>
        <ez-comment-item-post>
            <img slot="avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($author); ?>" class="w-12 h-12 rounded-full object-cover">
            <span slot="author"><?php echo esc_html($author); ?></span>
            <span slot="date"><?php echo esc_html($date); ?></span>
            <div slot="content"><?php echo $content; ?></div>
            <?php if (!empty($response_html)): ?>
                <div slot="response"><?php echo $response_html; ?></div>
            <?php endif; ?>
        </ez-comment-item-post>
        <?php
    }
}

function ez_the_comments($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $post_type = get_post_type($post_id);
    $per_page = 10;
    $args = [
        'post_id' => $post_id,
        'status' => 'approve',
        'type' => ($post_type === 'product') ? 'review' : 'comment',
        'number' => $per_page,
        'offset' => 0,
        'order' => 'DESC'
    ];
    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    if ($comments) {
        echo '<div class="space-y-4">';
        foreach ($comments as $comment) {
            ez_render_comment_item($comment, $post_type);
        }
        echo '</div>';
    } else {
        echo '<div class="text-center py-8 text-gray-500"><p>هنوز نظری ثبت نشده است.</p></div>';
    }
}
