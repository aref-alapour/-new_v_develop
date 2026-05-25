<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * wp_zb_booking_history (escapezo_queries / connection external).
 */
class BookingHistory extends Model
{
	protected $connection = 'external';

	protected $table = 'wp_zb_booking_history';

	protected $primaryKey = 'booking_id';

	public $timestamps = false;

	/** @var array<int, string> */
	protected $fillable = array(
		'customer_id',
		'wc_order_id',
		'status',
		'room_id',
		'booking_time',
		'booked_time',
		'name',
		'phone',
		'quantity',
	);

	/** @var array<string, string> */
	protected $casts = array(
		'customer_id'   => 'integer',
		'wc_order_id'   => 'integer',
		'status'        => 'integer',
		'room_id'       => 'integer',
		'booking_time'  => 'integer',
		'booked_time'   => 'integer',
		'quantity'      => 'integer',
	);
}
