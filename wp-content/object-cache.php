<?php
/**
 * Object cache drop-in: Redis when WP_REDIS_HOST is set, otherwise core default.
 *
 * Enable in Docker via env:
 *   WP_REDIS_HOST=redis
 *   WP_REDIS_PORT=6379
 */
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$redis_host = getenv( 'WP_REDIS_HOST' );
if ( false === $redis_host || '' === $redis_host ) {
	require_once ABSPATH . WPINC . '/cache.php';
	return;
}

if ( ! class_exists( 'Redis', false ) ) {
	require_once ABSPATH . WPINC . '/cache.php';
	return;
}

require_once __DIR__ . '/mu-plugins/ez_core/src/Infrastructure/Cache/RedisObjectCache.php';

$wp_object_cache = new \EscapeZoom\Core\Infrastructure\Cache\RedisObjectCache(
	(string) $redis_host,
	(int) ( getenv( 'WP_REDIS_PORT' ) ?: 6379 ),
	(int) ( getenv( 'WP_REDIS_DATABASE' ) ?: 0 )
);
