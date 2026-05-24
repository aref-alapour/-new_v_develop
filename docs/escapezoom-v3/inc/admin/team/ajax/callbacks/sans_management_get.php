<?php
global $wpdb, $wldb;

$term       = sanitize_text_field($_POST['term']);
$user_type  = sanitize_text_field($_POST['user_type']) ? : 'compiler';
$status     = sanitize_text_field($_POST['status']) ? : 'pending';
$page_num   = sanitize_text_field($_POST['page']) ?: 1;
