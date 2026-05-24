<?php

namespace EscapeZoom\Core\Database;

/**
 * Read `$wpdb->num_queries` for diagnostics only (not domain state).
 */
final class WordPressQueryCounter
{
    public static function current(): int
    {
        global $wpdb;

        return isset($wpdb->num_queries) ? (int) $wpdb->num_queries : 0;
    }
}
