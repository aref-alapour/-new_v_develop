<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * products_data (escapezo_queries / connection external).
 */
class ProductData extends Model
{
	protected $connection = 'external';

	protected $table = 'products_data';

	protected $primaryKey = 'ID';

	public $timestamps = false;

	/** @var array<int, string> */
	protected $fillable = array(
		'product_id',
		'title',
		'price',
		'schedule',
		'discount_data',
		'instant_off',
		'auto_disable',
		'active',
	);

	/** @var array<string, string> */
	protected $casts = array(
		'product_id'    => 'integer',
		'price'         => 'float',
		'auto_disable'  => 'integer',
		'active'        => 'integer',
	);

	/**
	 * Legacy PHP-serialized schedule column.
	 *
	 * @return array<string, mixed>
	 */
	public function getScheduleDecoded(): array {
		$raw = $this->getAttribute( 'schedule' );
		if ( empty( $raw ) ) {
			return array();
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		$decoded = @unserialize( (string) $raw );
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		return $decoded;
	}

	/**
	 * Legacy unserialized discount_data object.
	 */
	public function getDiscountObject(): ?object {
		$raw = $this->getAttribute( 'discount_data' );
		if ( empty( $raw ) ) {
			return null;
		}
		$decoded = @unserialize( (string) $raw );

		return is_object( $decoded ) ? $decoded : null;
	}

	/**
	 * @return array<string, mixed>|object|null
	 */
	public function getInstantOffDecoded(): array|object|null {
		$raw = $this->getAttribute( 'instant_off' );
		if ( empty( $raw ) ) {
			return null;
		}
		$decoded = @unserialize( (string) $raw );

		return ( is_object( $decoded ) || is_array( $decoded ) ) ? $decoded : null;
	}

	/**
	 * Schedule as legacy get_sanses(): json_decode(json_encode(unserialize)).
	 *
	 * @return array<string, mixed>
	 */
	public function getScheduleForSans(): array {
		$decoded = $this->getScheduleDecoded();
		if ( array() === $decoded ) {
			return array();
		}
		$normalized = json_decode( json_encode( $decoded ), true );

		return is_array( $normalized ) ? $normalized : array();
	}
}
