<?php
/**
 * Plugin Name: EscapeZoom Frontend De-bloat
 * Description: Disables wp-embed, emoji scripts, and wp-block-library on frontend; optionally dequeue jQuery. Single place, conditional. Remove or disable this file to revert.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'ez_frontend_debloat', 9999 );

function ez_frontend_debloat() {
	// Only on frontend
	if ( is_admin() ) {
		return;
	}

	// 1. Disable wp-embed (oEmbed script)
	wp_dequeue_script( 'wp-embed' );
	wp_deregister_script( 'wp-embed' );

	// 2. Disable emoji scripts and styles
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// 3. Disable block library on frontend (non-Gutenberg pages; Gutenberg editor is admin)
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );

	// 4. jQuery: set to true only when theme/plugins do not depend on jQuery on frontend
	if ( defined( 'EZ_DEBLOAT_REMOVE_JQUERY' ) && EZ_DEBLOAT_REMOVE_JQUERY ) {
		wp_dequeue_script( 'jquery' );
		wp_deregister_script( 'jquery' );
	}
}
