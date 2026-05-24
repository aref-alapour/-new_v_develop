<?php
if (!defined('ABSPATH')) {
	exit;
}

function ez_theme_package_uri(string $relative): string
{
    return Theme_URL . 'node_modules/' . ltrim($relative, '/');
}

function ez_theme_theme_uri(string $relative): string
{
    return Theme_URL . ltrim($relative, '/');
}

function ez_theme_dist_uri(string $relative): string
{
    return Theme_URL . 'dist/' . ltrim($relative, '/');
}
