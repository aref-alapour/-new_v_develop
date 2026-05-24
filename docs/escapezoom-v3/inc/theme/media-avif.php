<?php
if (!defined('ABSPATH')) {
	exit;
}

/* =======================================================
    Dependency Func
========================================================= */
function add_avif_upload_mime_types($mimes)
{
    $mimes['avif'] = 'image/avif';
    return $mimes;
}
add_filter('upload_mimes', 'add_avif_upload_mime_types');
