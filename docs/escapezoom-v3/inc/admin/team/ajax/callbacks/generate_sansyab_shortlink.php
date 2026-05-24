<?php
// Generate shortlink for sansyab page with filters using api-shortener.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Get the original_url parameter directly
$original_url = sanitize_text_field($_POST['original_url']);

if (empty($original_url)) {
    wp_send_json_error('Original URL is required');
    return;
}

// Extract query string for generating item_id
$parsed_url = parse_url($original_url);
$query_string = isset($parsed_url['query']) ? $parsed_url['query'] : '';

// Use the EZ_API_Shortener class to create shortlink
if (class_exists('EZ_API_Shortener')) {
    // Create a temporary item_id based on query string hash for sans type
    $item_id = abs(crc32($query_string));

    // Use the API shortener to create the shortlink
    $api_shortener = new EZ_API_Shortener();
    $shortlink = $api_shortener->create_sans_shortlink($original_url, $item_id);

    if ($shortlink) {
        // The API returns: http://localhost/eszm?s12345
        // We want to return exactly that for CRM panel display
        wp_send_json_success(array(
            'shortlink' => $shortlink
        ));
    } else {
        wp_send_json_error('Failed to generate shortlink');
    }
} else {
    wp_send_json_error('API Shortener class not available');
}
