<?php
/**
 * disable_gutenberg_for_pages
 *
 * توابع: disable_gutenberg_for_pages هوک‌ها: use_block_editor_for_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6478-6484)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_pages', 10, 2);
function disable_gutenberg_for_pages($use_block_editor, $post_type) {
    if ($post_type === 'page')
        return false;

    return $use_block_editor;
}
