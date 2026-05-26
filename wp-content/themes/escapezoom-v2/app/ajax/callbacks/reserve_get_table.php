<?php
/**
 * Admin-ajax week grid for reserve.php (legacy fallback).
 */
$time    = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['time'] ) ) : '';
$product = isset( $_POST['product'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['product'] ) ) : '';

if ( ! function_exists( 'ez_render_reserve_week_table' ) ) {
	require_once get_template_directory() . '/inc/theme/booking-reserve-week.php';
}

echo ez_render_reserve_week_table( (int) $product, (int) $time );
