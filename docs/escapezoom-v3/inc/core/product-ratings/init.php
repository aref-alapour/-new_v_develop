<?php
/**
 * Product relational ratings module (DDL via ez_core SQL bootstrap; domain in ez_core).
 *
 * @package escapezoom-v3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-ez-product-ratings-schema.php';
require_once __DIR__ . '/class-ez-product-rating-criteria.php';
require_once __DIR__ . '/class-ez-product-rating-rollup-service.php';
require_once __DIR__ . '/class-ez-product-ratings-migration.php';
require_once __DIR__ . '/class-ez-product-ratings-public-helper.php';
