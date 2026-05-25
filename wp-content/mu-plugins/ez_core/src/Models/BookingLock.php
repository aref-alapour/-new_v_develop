<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * booking_lock_schedule (WordPress DB / connection default).
 */
class BookingLock extends Model
{
	protected $connection = 'default';

	protected $table = 'booking_lock_schedule';

	public $timestamps = false;

	public $incrementing = false;

	/** @var array<int, string> */
	protected $fillable = array(
		'product_id',
		'booking_time',
		'lock_time',
	);

	/** @var array<string, string> */
	protected $casts = array(
		'product_id'   => 'integer',
		'booking_time' => 'integer',
		'lock_time'    => 'integer',
	);
}
