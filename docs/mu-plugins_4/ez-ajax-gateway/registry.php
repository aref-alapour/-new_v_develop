<?php
/**
 * Static Action Registry.
 *
 * Source of truth used BEFORE WordPress loads (dispatcher reads `wp_level` before `wp-load.php`).
 *
 * Routing is STRICT: actions NOT listed here return `UNKNOWN_ACTION` immediately — no WP bootstrap,
 * no filter lookup.
 *
 * Filter `ez_ajax_actions` runs only when `wp_level` is NOT `none` (after conditional wp-load).
 * Actions with `wp_level => none` cannot be overridden via that filter unless you bootstrap WP.
 *
 * Schema per action:
 *  - handler  string  PHP callable in `Class::method` form (fully qualified).
 *  - wp_level 'none'|'shortinit'|'full'  Bootstrap level required.
 *  - auth     'public'|'user'|'admin'    Authorization model (phase 1: public only).
 *  - output   'json'|'html'              Default response renderer.
 *  - inputs   array<string,string>       Field name → "type|rule|default:V" mini-DSL.
 *  - rate     array{ip?:string,client?:string}  Token-bucket budgets ("60/m", "10/s", "1000/h").
 */

declare( strict_types = 1 );

return [
	'ping' => [
		'handler'  => '\\EZ\\Ajax\\Actions\\Ping::run',
		'wp_level' => 'none',
		'auth'     => 'public',
		'output'   => 'json',
		'inputs'   => [],
		'rate'     => [ 'ip' => '120/m' ],
	],
	'brands.count' => [
		'handler'  => '\\EscapeZoom\\Core\\Modules\\Brands\\Actions\\BrandsCount::run',
		'wp_level' => 'none',
		'auth'     => 'public',
		'output'   => 'json',
		'inputs'   => [],
		'rate'     => [ 'ip' => '60/m' ],
	],
	'brands.fragment' => [
		'handler'  => '\\EscapeZoom\\Core\\Modules\\Brands\\Actions\\BrandsFragment::run',
		'wp_level' => 'none',
		'auth'     => 'public',
		'output'   => 'html',
		'inputs'   => [
			'page' => 'int|min:1|default:1',
		],
		'rate'     => [ 'ip' => '120/m', 'client' => '60/m' ],
	],
	'penalty.list' => [
		'handler'  => '\\EscapeZoom\\Core\\Modules\\ProductRanking\\Admin\\Gateway\\PenaltyList::run',
		'wp_level' => 'none',
		'auth'     => 'public',
		'output'   => 'html',
		'inputs'   => [
			'paged' => 'int|min:1|default:1',
			'per_page' => 'int|min:10|max:50|default:20',
			's' => 'string|maxlen:200',
			'product_id' => 'int|min:0|default:0',
			'penalty_from' => 'string|maxlen:32',
			'penalty_until' => 'string|maxlen:32',
			'created_from' => 'string|maxlen:32',
			'created_until' => 'string|maxlen:32',
			'updated_from' => 'string|maxlen:32',
			'updated_until' => 'string|maxlen:32',
		],
		'rate'     => [ 'ip' => '120/m', 'client' => '120/m' ],
	],
	'penalty.product_search' => [
		'handler'  => '\\EscapeZoom\\Core\\Modules\\ProductRanking\\Admin\\Gateway\\PenaltyProductSearch::run',
		'wp_level' => 'none',
		'auth'     => 'public',
		'output'   => 'json',
		'inputs'   => [
			'q' => 'string|maxlen:120',
		],
		'rate'     => [ 'ip' => '180/m', 'client' => '180/m' ],
	],
	'penalty.form' => [
		'handler'  => '\\EscapeZoom\\Core\\Modules\\ProductRanking\\Admin\\Gateway\\PenaltyForm::run',
		'wp_level' => 'full',
		'auth'     => 'public',
		'output'   => 'html',
		'inputs'   => [
			'id' => 'int|min:0|default:0',
		],
		'rate'     => [ 'ip' => '60/m', 'client' => '60/m' ],
	],
	'penalty.save' => [
		'handler'  => '\\EscapeZoom\\Core\\Modules\\ProductRanking\\Admin\\Gateway\\PenaltySave::run',
		'wp_level' => 'full',
		'auth'     => 'public',
		'output'   => 'json',
		'inputs'   => [],
		'rate'     => [ 'ip' => '60/m', 'client' => '60/m' ],
	],
	'penalty.delete' => [
		'handler'  => '\\EscapeZoom\\Core\\Modules\\ProductRanking\\Admin\\Gateway\\PenaltyDelete::run',
		'wp_level' => 'full',
		'auth'     => 'public',
		'output'   => 'json',
		'inputs'   => [
			'id' => 'int|min:1|required',
		],
		'rate'     => [ 'ip' => '60/m', 'client' => '60/m' ],
	],
];
