<?php
/**
 * Prints import instructions when escapezo_queries is missing or empty (Task 1.4).
 *
 * Usage: php wp-content/mu-plugins/ez_core/bin/import-escapezo-queries-hint.php
 */

declare(strict_types=1);

$root = dirname( __DIR__, 4 );
$sql  = $root . '/docs/escapezo_queries.sql';

echo "=== escapezo_queries import hint ===\n\n";

if ( ! is_file( $sql ) ) {
	echo "SQL dump not found at: {$sql}\n";
	echo "Obtain a staging dump with tables: products_data, calendar_data, wp_zb_booking_history\n";
	exit( 1 );
}

$sizeMb = round( filesize( $sql ) / 1024 / 1024, 1 );
echo "Found dump: docs/escapezo_queries.sql ({$sizeMb} MB)\n\n";
echo "Docker example (adjust container/user/password):\n\n";
echo "  docker exec -i <mysql_container> mysql -uroot -p<password> -e \"CREATE DATABASE IF NOT EXISTS escapezo_queries CHARACTER SET utf8mb4;\"\n";
echo "  docker exec -i <mysql_container> mysql -uroot -p<password> escapezo_queries < docs/escapezo_queries.sql\n\n";
echo "Or import only booking tables from a partial dump if the full file is too large.\n\n";
echo "Then verify:\n";
echo "  php wp-content/mu-plugins/ez_core/bin/booking-db-health.php\n";
echo "  php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 692762 " . strtotime( 'today Asia/Tehran' ) . " 1\n\n";

exit( 0 );
