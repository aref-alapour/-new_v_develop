<?php

namespace EscapeZoom\Core\Database;

/**
 * Canonical WordPress-prefixed table names from `$wpdb`.
 * Use this at the boundary instead of scattering `global $wpdb` for table-name resolution.
 * Data access should use Capsule / Eloquent — this class only exposes names.
 */
final class WordPressCoreTables
{
    public static function prefix(): string
    {
        global $wpdb;

        return $wpdb->prefix;
    }

    public static function comments(): string
    {
        global $wpdb;

        return $wpdb->comments;
    }

    public static function commentmeta(): string
    {
        global $wpdb;

        return $wpdb->commentmeta;
    }

    public static function posts(): string
    {
        global $wpdb;

        return $wpdb->posts;
    }

    public static function postmeta(): string
    {
        global $wpdb;

        return $wpdb->postmeta;
    }

    public static function term_relationships(): string
    {
        global $wpdb;

        return $wpdb->term_relationships;
    }
}
