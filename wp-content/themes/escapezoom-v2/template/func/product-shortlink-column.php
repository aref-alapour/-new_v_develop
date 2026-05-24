<?php

/**
 * Add Shortlink Column to WooCommerce Products List
 * 
 * این فایل یک ستون لینک کوتاه به لیست محصولات WooCommerce اضافه می‌کند
 * با آیکون کپی برای کپی سریع لینک
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add shortlink column to products list
 */
function ez_add_shortlink_column_to_products($columns)
{
    // Insert shortlink column before date column
    $new_columns = array();
    foreach ($columns as $key => $value) {
        if ($key === 'date') {
            $new_columns['ez_shortlink'] = 'لینک کوتاه';
        }
        $new_columns[$key] = $value;
    }
    return $new_columns;
}
add_filter('manage_product_posts_columns', 'ez_add_shortlink_column_to_products', 20);

/**
 * Render shortlink column content
 */
function ez_render_shortlink_column($column, $post_id)
{
    if ($column !== 'ez_shortlink') {
        return;
    }

    $shortlink = get_post_meta($post_id, '_ez_shortlink', true);
    
    if (!$shortlink) {
        // Try to generate if not exists
        $shortlink = ez_generate_product_shortlink($post_id);
        if ($shortlink) {
            update_post_meta($post_id, '_ez_shortlink', $shortlink);
        }
    }

    if ($shortlink) {
        // Prepare full URL with https://
        $full_url = 'https://' . $shortlink;
        
        echo '<div class="ez-shortlink-column-wrapper" style="display: flex; align-items: center; gap: 8px;">';
        echo '<a href="' . esc_url($full_url) . '" target="_blank" rel="noopener noreferrer" class="ez-shortlink-text" style="font-family: monospace; font-size: 12px; color: #2271b1; text-decoration: none; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;">' . esc_html($shortlink) . '</a>';
        echo '<button type="button" class="ez-copy-shortlink-btn" data-shortlink="' . esc_attr($shortlink) . '" style="background: none; border: none; cursor: pointer; padding: 4px; color: #2271b1; font-size: 16px; line-height: 1;" title="کپی لینک">';
        echo '<span class="dashicons dashicons-admin-page"></span>';
        echo '</button>';
        echo '</div>';
    } else {
        echo '<span style="color: #999;">—</span>';
    }
}
add_action('manage_product_posts_custom_column', 'ez_render_shortlink_column', 10, 2);

/**
 * Generate shortlink for product
 */
function ez_generate_product_shortlink($product_id)
{
    $base_url = 'eszm.ir';
    return $base_url . '?r=' . $product_id;
}

/**
 * Enqueue scripts and styles for shortlink column
 */
function ez_enqueue_shortlink_column_scripts($hook)
{
    // Only load on products list page
    if ($hook !== 'edit.php' || !isset($_GET['post_type']) || $_GET['post_type'] !== 'product') {
        return;
    }

    // Add inline script for copy functionality
    wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            $(document).on("click", ".ez-copy-shortlink-btn", function(e) {
                e.preventDefault();
                var shortlink = $(this).data("shortlink");
                var $btn = $(this);
                
                // Ensure shortlink has https:// prefix
                var fullLink = shortlink;
                if (shortlink.indexOf("http") !== 0) {
                    fullLink = "https://" + shortlink;
                }
                
                // Copy to clipboard using modern API
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(fullLink).then(function() {
                        // Visual feedback
                        var $icon = $btn.find(".dashicons");
                        $icon.removeClass("dashicons-admin-page").addClass("dashicons-yes-alt");
                        $btn.css("color", "#00a32a");
                        
                        setTimeout(function() {
                            $icon.removeClass("dashicons-yes-alt").addClass("dashicons-admin-page");
                            $btn.css("color", "#2271b1");
                        }, 2000);
                    }).catch(function(err) {
                        console.error("Failed to copy: ", err);
                        // Fallback to old method
                        var $temp = $("<input>");
                        $("body").append($temp);
                        $temp.val(fullLink).select();
                        document.execCommand("copy");
                        $temp.remove();
                    });
                } else {
                    // Fallback for older browsers
                    var $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val(fullLink).select();
                    document.execCommand("copy");
                    $temp.remove();
                    
                    // Visual feedback
                    var $icon = $btn.find(".dashicons");
                    $icon.removeClass("dashicons-admin-page").addClass("dashicons-yes-alt");
                    $btn.css("color", "#00a32a");
                    
                    setTimeout(function() {
                        $icon.removeClass("dashicons-yes-alt").addClass("dashicons-admin-page");
                        $btn.css("color", "#2271b1");
                    }, 2000);
                }
            });
        });
    ');
}
add_action('admin_enqueue_scripts', 'ez_enqueue_shortlink_column_scripts');

